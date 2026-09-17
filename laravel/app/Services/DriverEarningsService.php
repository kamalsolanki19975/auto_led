<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverEarning;
use App\Models\RateCard;
use App\Models\RuntimeLog;
use Carbon\Carbon;

/**
 * Calculates driver earnings from validated advertising activity + applicable
 * rate card. Snapshots the rate & quantity so historical settlements never
 * change when a rate card is later edited.
 */
class DriverEarningsService
{
    public function resolveRateCard(Driver $driver, ?string $campaignType = 'paid'): ?RateCard
    {
        return RateCard::where('status', 'active')
            ->where(function ($q) use ($driver) {
                $q->whereNull('city_id')->orWhere('city_id', $driver->city_id);
            })
            ->where(function ($q) use ($campaignType) {
                $q->whereNull('campaign_type')->orWhere('campaign_type', $campaignType);
            })
            ->orderByRaw('city_id IS NULL')
            ->orderByDesc('valid_from')
            ->first();
    }

    /**
     * Calculate (and persist) driver earnings for a period from runtime_logs.
     * Returns the DriverEarning record.
     */
    public function calculate(Driver $driver, Carbon $start, Carbon $end, array $adjustments = []): DriverEarning
    {
        $logs = RuntimeLog::where('driver_id', $driver->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $validSeconds = (int) $logs->sum('valid_advertising_runtime_seconds');
        $validPlays = (int) $logs->sum('valid_plays');

        $rateCard = $this->resolveRateCard($driver);
        $rate = $rateCard?->rate ?? 0;
        $basis = $rateCard?->rate_basis ?? 'per_play';

        [$quantity, $gross] = $this->applyBasis($basis, $rate, $validSeconds, $validPlays, $start, $end);

        $minGuarantee = $rateCard?->minimum_guarantee ?? 0;
        if ($gross < $minGuarantee) {
            $gross = $minGuarantee;
        }

        $bonus = (float) ($adjustments['bonus'] ?? ($rateCard?->bonus ?? 0));
        $penalty = (float) ($adjustments['penalty'] ?? 0);
        $adjustment = (float) ($adjustments['adjustment'] ?? 0);
        $net = round($gross + $bonus - $penalty + $adjustment, 2);

        return DriverEarning::create([
            'driver_id' => $driver->id,
            'auto_id' => $driver->autos()->value('id'),
            'rate_card_id' => $rateCard?->id,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'calculation_basis' => $basis,
            'quantity' => $quantity,
            'rate' => $rate,
            'gross_amount' => $gross,
            'bonus' => $bonus,
            'penalty' => $penalty,
            'adjustment' => $adjustment,
            'net_amount' => $net,
            'valid_runtime_seconds' => $validSeconds,
            'valid_plays' => $validPlays,
            'calculation_version' => 1,
            'calculated_at' => now(),
            'status' => 'calculated',
        ]);
    }

    protected function applyBasis(string $basis, float $rate, int $validSeconds, int $validPlays, Carbon $start, Carbon $end): array
    {
        $hours = $validSeconds / 3600;
        $days = max(1, $start->diffInDays($end) + 1);

        return match ($basis) {
            'per_hour' => [round($hours, 2), round($hours * $rate, 2)],
            'per_day' => [$days, round($days * $rate, 2)],
            'per_campaign' => [1, round($rate, 2)],
            'hybrid' => [$validPlays, round(($validPlays * $rate) + ($hours * $rate * 0.5), 2)],
            default => [$validPlays, round($validPlays * $rate, 2)], // per_play
        };
    }
}
