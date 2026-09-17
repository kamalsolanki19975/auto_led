<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Device;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(DashboardService $dash)
    {
        $user = auth()->user();
        // Route portal users to their own dashboards
        return match ($user->primaryPortal()) {
            'advertiser' => redirect()->route('portal.advertiser'),
            'driver' => redirect()->route('portal.driver'),
            'owner' => redirect()->route('portal.owner'),
            'technician' => redirect()->route('portal.technician'),
            default => view('dashboard.index', [
                'kpis' => $dash->kpis(),
                'series' => $dash->revenueVsExpenseSeries(),
                'alerts' => $dash->alerts(),
                'activeCampaigns' => Campaign::with('advertiser')->whereIn('status', ['active', 'under_delivery'])->latest()->limit(6)->get(),
                'deliveryService' => app(\App\Services\CampaignService::class),
            ]),
        };
    }
}
