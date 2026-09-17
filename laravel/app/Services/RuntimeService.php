<?php

namespace App\Services;

use App\Models\ProofOfPlay;
use App\Models\RuntimeLog;

/**
 * Aggregates validated Proof-of-Play into daily runtime summary rows
 * (runtime_logs) — the scalable summary table used by dashboards/earnings,
 * never querying millions of raw playback rows directly.
 */
class RuntimeService
{
    public function applyProofOfPlay(ProofOfPlay $pop): void
    {
        if ($pop->status !== 'valid' || ! $pop->auto_id) {
            return;
        }
        $date = ($pop->server_timestamp ?? now())->toDateString();

        $log = RuntimeLog::firstOrCreate(
            [
                'auto_id' => $pop->auto_id,
                'campaign_id' => $pop->campaign_id,
                'driver_id' => $pop->driver_id,
                'date' => $date,
            ],
            ['device_id' => $pop->device_id]
        );

        $log->increment('valid_plays');
        $log->increment('total_plays');
        $log->increment('advertisement_runtime_seconds', $pop->valid_duration);
        $log->increment('valid_advertising_runtime_seconds', $pop->valid_duration);
    }

    /** Rebuild a day's runtime aggregate from raw PoP (idempotent). */
    public function rebuildForDate(string $date): int
    {
        RuntimeLog::whereDate('date', $date)->delete();

        $rows = ProofOfPlay::whereDate('server_timestamp', $date)
            ->selectRaw('auto_id, campaign_id, driver_id, device_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = "valid" THEN 1 ELSE 0 END) as valid_plays,
                SUM(CASE WHEN status = "valid" THEN valid_duration ELSE 0 END) as valid_seconds')
            ->whereNotNull('auto_id')
            ->groupBy('auto_id', 'campaign_id', 'driver_id', 'device_id')
            ->get();

        foreach ($rows as $r) {
            RuntimeLog::create([
                'auto_id' => $r->auto_id,
                'campaign_id' => $r->campaign_id,
                'driver_id' => $r->driver_id,
                'device_id' => $r->device_id,
                'date' => $date,
                'total_plays' => (int) $r->total,
                'valid_plays' => (int) $r->valid_plays,
                'advertisement_runtime_seconds' => (int) $r->valid_seconds,
                'valid_advertising_runtime_seconds' => (int) $r->valid_seconds,
            ]);
        }
        return $rows->count();
    }
}
