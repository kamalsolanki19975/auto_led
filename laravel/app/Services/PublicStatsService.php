<?php

namespace App\Services;

use App\Models\Advertiser;
use App\Models\Area;
use App\Models\Auto;
use App\Models\Campaign;
use App\Models\City;
use App\Models\Device;
use App\Models\Driver;
use App\Models\Owner;
use App\Models\PlaybackEvent;
use App\Models\Screen;
use App\Models\Sim;
use App\Models\SystemSetting;

class PublicStatsService
{
    /** Headline network + delivery statistics for the public website. */
    public function summary(): array
    {
        $threshold = now()->subSeconds((int) SystemSetting::get('device', 'offline_threshold', 180));
        $onlineIds = Device::whereNotNull('last_heartbeat_at')
            ->where('last_heartbeat_at', '>=', $threshold)
            ->pluck('id');

        return [
            'autos_total' => Auto::count(),
            'autos_active' => Auto::where('status', 'active')->count(),
            'owners_total' => Owner::count(),
            'drivers_total' => Driver::count(),
            'screens_total' => Screen::count(),
            'screens_online' => Screen::whereIn('current_device_id', $onlineIds)->count(),
            'devices_total' => Device::count(),
            'devices_online' => $onlineIds->count(),
            'sims_total' => Sim::count(),
            'sims_active' => Sim::where('status', 'active')->count(),
            'cities_total' => City::count(),
            'areas_total' => Area::count(),
            'advertisers_total' => Advertiser::count(),
            'campaigns_total' => Campaign::count(),
            'campaigns_active' => Campaign::whereIn('status', ['active', 'under_delivery'])->count(),
            'plays_total' => PlaybackEvent::count(),
            'plays_valid' => PlaybackEvent::where('status', 'valid')->count(),
            'plays_today' => PlaybackEvent::whereDate('created_at', today())->count(),
        ];
    }

    /** City → Area coverage breakdown (map-ready; lat/long stored on autos for future GPS view). */
    public function coverage(): array
    {
        try {
            return City::withCount('autos')
                ->with(['areas' => fn ($q) => $q->withCount('autos')])
                ->orderByDesc('autos_count')
                ->get()
                ->map(fn ($c) => [
                    'city' => $c->name,
                    'autos' => $c->autos_count,
                    'areas' => $c->areas->map(fn ($a) => [
                        'area' => $a->name,
                        'autos' => $a->autos_count,
                    ])->values()->all(),
                ])->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Campaigns grouped by status for the network/analytics pages. */
    public function campaignBreakdown(): array
    {
        return Campaign::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status')->toArray();
    }
}
