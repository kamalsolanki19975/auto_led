<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckOfflineDevices extends Command
{
    protected $signature = 'devices:check-offline';
    protected $description = 'Detect devices that have missed their heartbeat and flag them offline.';

    public function handle(NotificationService $notifier): int
    {
        $threshold = (int) SystemSetting::get('device', 'offline_threshold', 180);
        $cutoff = now()->subSeconds($threshold);
        $devices = Device::where('status', 'active')
            ->where(fn ($q) => $q->whereNull('last_heartbeat_at')->orWhere('last_heartbeat_at', '<', $cutoff))
            ->get();
        foreach ($devices as $device) {
            app(\App\Services\WebhookService::class)->dispatch('device.offline', ['device' => $device->code]);
        }
        $this->info($devices->count().' device(s) currently offline.');
        return self::SUCCESS;
    }
}
