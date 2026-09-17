<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Heartbeat;
use App\Services\DeviceService;
use App\Services\PlaybackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeviceApiController extends Controller
{
    /**
     * Pair/authenticate a device. Device supplies its UUID and pairing token;
     * returns a bearer token for subsequent calls.
     */
    public function authenticate(Request $request, DeviceService $svc)
    {
        $data = $request->validate(['device_uuid' => 'required', 'pairing_token' => 'required']);
        $device = Device::where('device_uuid', $data['device_uuid'])->first();
        if (! $device || ! $device->auth_token || ! Hash::check($data['pairing_token'], $device->auth_token)) {
            return response()->json(['success' => false, 'message' => 'Invalid device credentials.'], 401);
        }
        // Rotate to a session token
        $token = $svc->generateToken($device);
        return response()->json([
            'success' => true,
            'device_token' => $token,
            'device' => ['code' => $device->code, 'status' => $device->status],
            'configuration' => $svc->configuration($device),
        ]);
    }

    protected function device(Request $request): Device
    {
        return $request->attributes->get('device');
    }

    public function heartbeat(Request $request)
    {
        $device = $this->device($request);
        Heartbeat::create([
            'device_id' => $device->id,
            'auto_id' => $device->current_auto_id,
            'ip' => $request->ip(),
            'app_version' => $request->input('app_version', $device->app_version),
            'android_version' => $request->input('android_version', $device->android_version),
            'storage_free' => $request->input('storage_free'),
            'temperature' => $request->input('temperature'),
            'network' => $request->input('network'),
            'signal_strength' => $request->input('signal_strength'),
            'current_campaign_id' => $request->input('current_campaign_id'),
            'current_advertisement_id' => $request->input('current_advertisement_id'),
            'uptime' => $request->input('uptime'),
            'errors' => $request->input('errors'),
            'created_at' => now(),
        ]);
        $device->update([
            'last_heartbeat_at' => now(),
            'temperature' => $request->input('temperature', $device->temperature),
            'app_version' => $request->input('app_version', $device->app_version),
        ]);
        return response()->json(['success' => true, 'server_time' => now()->toIso8601String()]);
    }

    public function configuration(Request $request, DeviceService $svc)
    {
        return response()->json(['success' => true, 'configuration' => $svc->configuration($this->device($request))]);
    }

    public function campaigns(Request $request)
    {
        $device = $this->device($request);
        $auto = $device->auto;
        if (! $auto) {
            return response()->json(['success' => true, 'campaigns' => []]);
        }
        $campaigns = $auto->campaigns()->whereIn('campaigns.status', ['active', 'under_delivery'])
            ->with('advertisements')->get()->map(function ($c) {
                return [
                    'id' => $c->id, 'code' => $c->code, 'name' => $c->name,
                    'priority' => $c->priority, 'schedule' => $c->schedule,
                    'advertisements' => $c->advertisements->map(fn ($a) => [
                        'id' => $a->id, 'code' => $a->code, 'title' => $a->title,
                        'content_type' => $a->content_type, 'duration' => $a->duration,
                        'media_url' => $a->media_url ?: ($a->media_path ? asset('storage/'.$a->media_path) : null),
                        'text_content' => $a->text_content,
                    ]),
                ];
            });
        return response()->json(['success' => true, 'campaigns' => $campaigns, 'fallback' => 'house']);
    }

    public function content(Request $request)
    {
        // Content manifest for offline caching
        $device = $this->device($request);
        $auto = $device->auto;
        $items = collect();
        if ($auto) {
            foreach ($auto->campaigns()->whereIn('campaigns.status', ['active', 'under_delivery'])->with('advertisements')->get() as $c) {
                foreach ($c->advertisements as $a) {
                    $items->push(['advertisement_id' => $a->id, 'content_type' => $a->content_type, 'url' => $a->media_url ?: ($a->media_path ? asset('storage/'.$a->media_path) : null), 'checksum' => md5($a->code.$a->version)]);
                }
            }
        }
        return response()->json(['success' => true, 'content' => $items->unique('advertisement_id')->values()]);
    }

    public function playbackEvents(Request $request, PlaybackService $svc)
    {
        $device = $this->device($request);
        [$status, $event] = $svc->ingest($device, $request->all());
        return response()->json(['success' => true, 'status' => $status, 'event_id' => $event?->event_id]);
    }

    public function syncEvents(Request $request, PlaybackService $svc)
    {
        $device = $this->device($request);
        $events = $request->input('events', []);
        $result = $svc->sync($device, $events);
        return response()->json(['success' => true, 'result' => $result]);
    }

    public function errors(Request $request)
    {
        $device = $this->device($request);
        \Illuminate\Support\Facades\Log::warning('[device-error] '.$device->code.': '.json_encode($request->all()));
        return response()->json(['success' => true]);
    }

    public function status(Request $request)
    {
        $device = $this->device($request);
        return response()->json(['success' => true, 'status' => $device->status, 'online' => $device->isOnline()]);
    }

    public function acknowledgement(Request $request)
    {
        $device = $this->device($request);
        // Clear any pending commands the player acknowledges
        $config = $device->config ?? [];
        $config['pending_commands'] = [];
        $device->update(['config' => $config, 'last_sync_at' => now()]);
        return response()->json(['success' => true]);
    }
}
