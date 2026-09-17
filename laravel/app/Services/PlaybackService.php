<?php

namespace App\Services;

use App\Models\Device;
use App\Models\PlaybackEvent;
use App\Jobs\ProcessPlaybackEventJob;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PlaybackService
{
    /**
     * Ingest a single playback event from a device with idempotency (unique event_id).
     * Returns [status, playbackEvent|null].
     */
    public function ingest(Device $device, array $data): array
    {
        $eventId = $data['event_id'] ?? (string) Str::uuid();

        // Idempotency / duplicate prevention
        $existing = PlaybackEvent::where('event_id', $eventId)->first();
        if ($existing) {
            return ['duplicate', $existing];
        }

        $start = isset($data['start_time']) ? Carbon::parse($data['start_time']) : now();
        $end = isset($data['end_time']) ? Carbon::parse($data['end_time']) : $start->copy();
        $actual = (int) ($data['actual_duration'] ?? max(0, $end->diffInSeconds($start)));
        $expected = (int) ($data['expected_duration'] ?? $actual);
        $completion = $expected > 0 ? min(100, round(($actual / $expected) * 100, 2)) : 0;

        $event = PlaybackEvent::create([
            'event_id' => $eventId,
            'device_id' => $device->id,
            'screen_id' => $device->current_screen_id,
            'auto_id' => $device->current_auto_id,
            'campaign_id' => $data['campaign_id'] ?? null,
            'advertisement_id' => $data['advertisement_id'] ?? null,
            'driver_id' => $device->auto?->primary_driver_id,
            'start_time' => $start,
            'end_time' => $end,
            'duration' => $actual,
            'expected_duration' => $expected,
            'actual_duration' => $actual,
            'completion_percent' => $completion,
            'device_timestamp' => isset($data['device_timestamp']) ? Carbon::parse($data['device_timestamp']) : $start,
            'server_timestamp' => now(),
            'network_status' => $data['network_status'] ?? 'online',
            'player_version' => $data['player_version'] ?? $device->app_version,
            'sync_timestamp' => now(),
            'status' => 'received',
            'processed' => false,
        ]);

        ProcessPlaybackEventJob::dispatch($event->id);

        return ['received', $event];
    }

    /** Bulk sync of offline-queued events. */
    public function sync(Device $device, array $events): array
    {
        $results = ['received' => 0, 'duplicate' => 0];
        foreach ($events as $e) {
            [$status] = $this->ingest($device, $e);
            $results[$status] = ($results[$status] ?? 0) + 1;
        }
        return $results;
    }
}
