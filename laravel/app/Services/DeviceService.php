<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Screen;
use App\Models\Sim;
use App\Support\Codes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DeviceService
{
    /**
     * Generate a fresh device auth token (returned plaintext once, stored hashed).
     */
    public function generateToken(Device $device): string
    {
        $token = Str::random(48);
        $device->update(['auth_token' => Hash::make($token)]);
        return $token;
    }

    /**
     * Activate a device against a screen/auto/sim, enforcing the exclusive-assignment
     * business rules (one active screen per device, one active device per SIM).
     */
    public function activate(Device $device, ?int $screenId, ?int $autoId, ?int $simId): void
    {
        \DB::transaction(function () use ($device, $screenId, $autoId, $simId) {
            // Close previous active assignments
            $device->assignments()->where('active', true)->update(['active' => false, 'unassigned_at' => now()]);

            $device->update([
                'current_screen_id' => $screenId,
                'current_auto_id' => $autoId,
                'current_sim_id' => $simId,
                'status' => 'active',
            ]);

            $device->assignments()->create([
                'auto_id' => $autoId,
                'screen_id' => $screenId,
                'sim_id' => $simId,
                'assigned_at' => now(),
                'active' => true,
            ]);

            if ($screenId) {
                Screen::where('id', $screenId)->update([
                    'current_auto_id' => $autoId,
                    'current_device_id' => $device->id,
                    'status' => 'active',
                ]);
            }
            if ($simId) {
                // one active device per SIM
                Sim::where('current_device_id', $device->id)->where('id', '!=', $simId)
                    ->update(['current_device_id' => null]);
                Sim::where('id', $simId)->update([
                    'current_device_id' => $device->id,
                    'current_auto_id' => $autoId,
                    'status' => 'active',
                ]);
            }
        });

        AuditService::log('device.activated', $device, [], ['screen' => $screenId, 'auto' => $autoId, 'sim' => $simId]);
    }

    public function setStatus(Device $device, string $status): void
    {
        $old = $device->status;
        $device->update(['status' => $status]);
        AuditService::log('device.status.'.$status, $device, ['status' => $old], ['status' => $status]);
    }

    public function configuration(Device $device): array
    {
        $settings = \App\Models\SystemSetting::group('device');
        return [
            'heartbeat_interval' => (int) ($settings['heartbeat_interval'] ?? 60),
            'sync_interval' => (int) ($settings['sync_interval'] ?? 300),
            'offline_threshold' => (int) ($settings['offline_threshold'] ?? 180),
            'kiosk_mode' => true,
            'watchdog' => true,
            'device' => [
                'code' => $device->code,
                'uuid' => $device->device_uuid,
                'status' => $device->status,
            ],
            'fallback_content' => \App\Models\SystemSetting::get('advertising', 'default_fallback_content', 'house'),
        ];
    }
}
