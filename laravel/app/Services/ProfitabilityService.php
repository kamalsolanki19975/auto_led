<?php

namespace App\Services;

use App\Models\Auto;
use App\Models\Campaign;
use App\Models\DriverEarning;
use App\Models\Expense;
use App\Models\Revenue;

/**
 * Server-side profitability engine: campaign-, auto- and company-level P&L.
 */
class ProfitabilityService
{
    public function campaign(Campaign $campaign): array
    {
        $revenue = (float) Revenue::where('campaign_id', $campaign->id)->sum('amount');
        if ($revenue == 0) {
            // fall back to issued invoices total if no payments yet
            $revenue = (float) $campaign->invoices()->sum('total');
        }

        $driverCost = (float) DriverEarning::where('campaign_id', $campaign->id)->sum('net_amount');
        $expenses = Expense::where('campaign_id', $campaign->id)->get();
        $simCost = (float) $expenses->where('category', 'sim')->sum('amount');
        $hardwareCost = (float) $expenses->whereIn('category', ['hardware', 'installation'])->sum('amount');
        $maintenanceCost = (float) $expenses->where('category', 'maintenance')->sum('amount');
        $otherCost = (float) $expenses->whereNotIn('category', ['sim', 'hardware', 'installation', 'maintenance', 'driver_payment'])->sum('amount');

        // platform allocation: flat % of revenue
        $platformCost = round($revenue * 0.10, 2);

        $totalCost = $driverCost + $simCost + $hardwareCost + $maintenanceCost + $otherCost + $platformCost;
        $contribution = round($revenue - $totalCost, 2);
        $margin = $revenue > 0 ? round(($contribution / $revenue) * 100, 1) : 0;

        return compact('revenue', 'driverCost', 'simCost', 'hardwareCost', 'maintenanceCost', 'otherCost', 'platformCost', 'totalCost', 'contribution', 'margin');
    }

    public function auto(Auto $auto): array
    {
        $revenue = (float) Revenue::whereIn('campaign_id', $auto->campaigns()->pluck('campaigns.id'))->sum('amount');
        $driverCost = (float) DriverEarning::where('auto_id', $auto->id)->sum('net_amount');
        $expenses = Expense::where('auto_id', $auto->id)->get();
        $simCost = (float) $expenses->where('category', 'sim')->sum('amount');
        $maintenanceCost = (float) $expenses->where('category', 'maintenance')->sum('amount');
        $hardwareCost = (float) $expenses->whereIn('category', ['hardware', 'installation'])->sum('amount');
        $otherCost = (float) $expenses->whereNotIn('category', ['sim', 'maintenance', 'hardware', 'installation'])->sum('amount');

        $totalCost = $driverCost + $simCost + $maintenanceCost + $hardwareCost + $otherCost;
        $contribution = round($revenue - $totalCost, 2);

        return compact('revenue', 'driverCost', 'simCost', 'maintenanceCost', 'hardwareCost', 'otherCost', 'totalCost', 'contribution');
    }

    public function company(?string $from = null, ?string $to = null): array
    {
        $revQ = Revenue::query();
        $expQ = Expense::query();
        if ($from) { $revQ->whereDate('date', '>=', $from); $expQ->whereDate('date', '>=', $from); }
        if ($to) { $revQ->whereDate('date', '<=', $to); $expQ->whereDate('date', '<=', $to); }

        $revenue = (float) $revQ->sum('amount');
        $expenses = (float) $expQ->sum('amount');
        $net = round($revenue - $expenses, 2);

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net' => $net,
            'margin' => $revenue > 0 ? round(($net / $revenue) * 100, 1) : 0,
        ];
    }
}
