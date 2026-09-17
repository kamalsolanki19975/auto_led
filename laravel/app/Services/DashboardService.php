<?php

namespace App\Services;

use App\Models\Auto;
use App\Models\Campaign;
use App\Models\Device;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProofOfPlay;
use App\Models\Revenue;
use App\Models\Screen;
use App\Models\Sim;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function kpis(): array
    {
        $offlineThreshold = (int) SystemSetting::get('device', 'offline_threshold', 180);
        $onlineCutoff = now()->subSeconds($offlineThreshold);

        $devicesActive = Device::where('status', 'active')->where('last_heartbeat_at', '>=', $onlineCutoff)->count();
        $devicesOffline = Device::where('status', 'active')->where(function ($q) use ($onlineCutoff) {
            $q->whereNull('last_heartbeat_at')->orWhere('last_heartbeat_at', '<', $onlineCutoff);
        })->count();

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        return [
            'total_autos' => Auto::count(),
            'active_autos' => Auto::whereIn('status', ['active', 'screen_installed'])->count(),
            'screens_installed' => Screen::whereIn('status', ['installed', 'active'])->count(),
            'screens_online' => $devicesActive,
            'screens_offline' => $devicesOffline,
            'devices_active' => Device::where('status', 'active')->count(),
            'devices_offline' => $devicesOffline,
            'active_sims' => Sim::where('status', 'active')->count(),
            'active_campaigns' => Campaign::whereIn('status', ['active', 'under_delivery'])->count(),
            'today_revenue' => (float) Revenue::whereDate('date', $today)->sum('amount'),
            'monthly_revenue' => (float) Revenue::whereDate('date', '>=', $monthStart)->sum('amount'),
            'monthly_expenses' => (float) Expense::whereDate('date', '>=', $monthStart)->sum('amount'),
            'driver_payable' => (float) \App\Models\DriverSettlement::whereIn('status', ['draft', 'review', 'approved'])->sum('net_payable'),
            'outstanding_receivables' => (float) Invoice::whereIn('status', ['issued', 'partially_paid', 'overdue'])->sum(\DB::raw('total - amount_paid')),
            'payables' => (float) Expense::where('payment_status', 'unpaid')->sum('amount'),
            'current_profit' => round((float) Revenue::whereDate('date', '>=', $monthStart)->sum('amount') - (float) Expense::whereDate('date', '>=', $monthStart)->sum('amount'), 2),
        ];
    }

    public function revenueVsExpenseSeries(int $months = 6): array
    {
        $labels = [];
        $rev = [];
        $exp = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $labels[] = $m->format('M');
            $rev[] = round((float) Revenue::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount'), 2);
            $exp[] = round((float) Expense::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount'), 2);
        }
        return ['labels' => $labels, 'revenue' => $rev, 'expenses' => $exp];
    }

    public function alerts(): array
    {
        $offlineThreshold = (int) SystemSetting::get('device', 'offline_threshold', 180);
        $onlineCutoff = now()->subSeconds($offlineThreshold);

        return [
            'offline_devices' => Device::where('status', 'active')->where(function ($q) use ($onlineCutoff) {
                $q->whereNull('last_heartbeat_at')->orWhere('last_heartbeat_at', '<', $onlineCutoff);
            })->count(),
            'sim_expiring' => Sim::where('status', 'active')->whereBetween('renewal_date', [now(), now()->addDays(15)])->count(),
            'high_usage_sims' => Sim::whereColumn('data_used_mb', '>=', 'data_limit_mb')->where('data_limit_mb', '>', 0)->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            'pending_settlements' => \App\Models\DriverSettlement::whereIn('status', ['draft', 'review'])->count(),
            'maintenance_open' => \App\Models\MaintenanceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'warranty_expiring' => \App\Models\Warranty::whereBetween('end_date', [now(), now()->addDays(30)])->count(),
            'under_delivery' => Campaign::where('status', 'under_delivery')->count(),
        ];
    }
}
