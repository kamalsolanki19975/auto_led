<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Device;
use App\Models\Screen;
use App\Models\Sim;
use App\Services\AuditService;
use App\Services\DeviceService;
use Illuminate\Http\Request;

class DeviceController extends ResourceController
{
    protected string $modelClass = Device::class;
    protected string $routeBase = 'devices';
    protected string $title = 'Devices';
    protected string $singular = 'Device';
    protected ?string $codePrefix = 'DEV';
    protected ?string $codeTable = 'devices';
    protected array $searchable = ['device_uuid', 'code', 'serial_number', 'imei'];
    protected array $with = ['auto', 'screen', 'sim'];
    protected array $statuses = ['registered', 'pending_activation', 'active', 'offline', 'maintenance', 'blocked', 'deactivated', 'replaced'];

    protected function fields(): array
    {
        return [
            ['name' => 'serial_number', 'label' => 'Serial Number', 'type' => 'text'],
            ['name' => 'android_version', 'label' => 'Android Version', 'type' => 'text'],
            ['name' => 'app_version', 'label' => 'App Version', 'type' => 'text'],
            ['name' => 'hardware_model', 'label' => 'Hardware Model', 'type' => 'text'],
            ['name' => 'imei', 'label' => 'IMEI', 'type' => 'text'],
            ['name' => 'mac_address', 'label' => 'MAC Address', 'type' => 'text'],
            ['name' => 'ram', 'label' => 'RAM', 'type' => 'text'],
            ['name' => 'storage', 'label' => 'Storage', 'type' => 'text'],
            ['name' => 'cpu', 'label' => 'CPU', 'type' => 'text'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
        ];
    }

    protected function options(): array
    {
        return ['status' => array_combine($this->statuses, array_map(fn ($s) => ucwords(str_replace('_', ' ', $s)), $this->statuses))];
    }

    protected function transform(array $data, Request $request): array
    {
        if ($request->routeIs($this->routeBase.'.store')) {
            $data['device_uuid'] = (string) \Illuminate\Support\Str::uuid();
            $data['status'] = $data['status'] ?? 'registered';
        }
        return $data;
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code', 'strong' => true],
            ['key' => 'hardware_model', 'label' => 'Hardware'],
            ['key' => 'auto', 'label' => 'Auto', 'render' => fn ($r) => $r->auto?->registration_number ?? '—'],
            ['key' => 'heartbeat', 'label' => 'Heartbeat', 'render' => fn ($r) => $r->isOnline() ? '<span class="text-emerald-400">Online</span>' : '<span class="text-rose-400">Offline</span>', 'raw' => true],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }

    protected function showView(): string
    {
        return 'network.device-show';
    }

    protected function showExtra($model): array
    {
        return [
            'online' => $model->isOnline(),
            'heartbeats' => $model->heartbeats()->latest()->limit(20)->get(),
            'assignments' => $model->assignments()->latest()->get(),
            'currentCampaign' => $model->auto?->campaigns()->whereIn('campaigns.status', ['active', 'under_delivery'])->first(),
            'freeScreens' => Screen::whereIn('status', ['inventory', 'installation_pending'])->orWhere('current_device_id', $model->id)->get(),
            'freeAutos' => Auto::whereIn('status', ['available', 'installation_pending', 'screen_installed', 'active'])->get(),
            'freeSims' => Sim::whereIn('status', ['available', 'active'])->get(),
            'pendingCommands' => $model->config['pending_commands'] ?? [],
        ];
    }

    // ---- device lifecycle actions ----
    public function activateDevice(Request $request, Device $device, DeviceService $svc)
    {
        $data = $request->validate(['screen_id' => 'nullable|exists:screens,id', 'auto_id' => 'nullable|exists:autos,id', 'sim_id' => 'nullable|exists:sims,id']);
        $svc->activate($device, $data['screen_id'] ?? null, $data['auto_id'] ?? null, $data['sim_id'] ?? null);
        $device->update(['last_heartbeat_at' => now()]);
        return back()->with('success', 'Device activated and assigned.');
    }

    public function regenerateToken(Device $device, DeviceService $svc)
    {
        $token = $svc->generateToken($device);
        return back()->with('success', 'New device token generated: '.$token.' (store it securely — shown once).');
    }

    public function command(Request $request, Device $device)
    {
        $cmd = $request->input('command');
        $allowed = ['restart', 'sync', 'refresh', 'push_config', 'update_playlist'];
        abort_unless(in_array($cmd, $allowed, true), 422);
        $config = $device->config ?? [];
        $config['pending_commands'][] = ['command' => $cmd, 'queued_at' => now()->toIso8601String(), 'status' => 'queued'];
        $device->update(['config' => $config]);
        AuditService::log('device.command.'.$cmd, $device);
        return back()->with('success', ucfirst(str_replace('_', ' ', $cmd)).' command queued. It will apply on the next device sync (honest pending state — no live hardware connected).');
    }

    public function setStatus(Request $request, Device $device, DeviceService $svc)
    {
        $status = $request->input('status');
        abort_unless(in_array($status, ['active', 'blocked', 'deactivated', 'maintenance'], true), 422);
        $svc->setStatus($device, $status);
        return back()->with('success', 'Device status updated to '.$status.'.');
    }
}
