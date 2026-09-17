<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Campaign;
use App\Models\Device;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\RuntimeLog;
use App\Models\Sim;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function show(Request $request, string $type)
    {
        $data = $this->dataset($type);
        if ($request->get('export') === 'csv') {
            return $this->csv($type, $data['columns'], $data['rows']);
        }
        return view('reports.show', array_merge(['type' => $type], $data));
    }

    protected function dataset(string $type): array
    {
        return match ($type) {
            'campaign' => [
                'title' => 'Campaign Performance',
                'columns' => ['Code', 'Campaign', 'Advertiser', 'Status', 'Expected', 'Valid Plays', 'Delivery %'],
                'rows' => Campaign::with('advertiser')->get()->map(function ($c) {
                    $valid = $c->proofOfPlays()->where('status', 'valid')->count();
                    $pct = $c->expected_plays > 0 ? round($valid / $c->expected_plays * 100, 1) : 0;
                    return [$c->code, $c->name, $c->advertiser?->company_name, $c->status, $c->expected_plays, $valid, $pct.'%'];
                })->toArray(),
            ],
            'runtime' => [
                'title' => 'Runtime Report',
                'columns' => ['Auto', 'Driver', 'Valid Plays', 'Valid Runtime (s)'],
                'rows' => RuntimeLog::with('auto', 'driver')->selectRaw('auto_id, driver_id, SUM(valid_plays) vp, SUM(valid_advertising_runtime_seconds) vr')->groupBy('auto_id', 'driver_id')->get()->map(fn ($r) => [$r->auto?->registration_number, $r->driver?->name, $r->vp, $r->vr])->toArray(),
            ],
            'sim' => [
                'title' => 'SIM Report',
                'columns' => ['Code', 'Number', 'Operator', 'Status', 'Data Used (MB)', 'Monthly Cost'],
                'rows' => Sim::get()->map(fn ($s) => [$s->code, $s->mobile_number, $s->operator, $s->status, $s->data_used_mb, $s->monthly_cost])->toArray(),
            ],
            'device' => [
                'title' => 'Device Health Report',
                'columns' => ['Code', 'Hardware', 'Status', 'Online', 'Last Heartbeat'],
                'rows' => Device::get()->map(fn ($d) => [$d->code, $d->hardware_model, $d->status, $d->isOnline() ? 'Yes' : 'No', $d->last_heartbeat_at?->format('d M H:i')])->toArray(),
            ],
            'revenue' => [
                'title' => 'Revenue Report',
                'columns' => ['Code', 'Source', 'Amount', 'Date'],
                'rows' => Revenue::get()->map(fn ($r) => [$r->code, $r->source, $r->amount, $r->date?->format('d M Y')])->toArray(),
            ],
            'expense' => [
                'title' => 'Expense Report',
                'columns' => ['Code', 'Category', 'Amount', 'Status', 'Date'],
                'rows' => Expense::get()->map(fn ($e) => [$e->code, $e->category, $e->amount, $e->payment_status, $e->date?->format('d M Y')])->toArray(),
            ],
            'auto' => [
                'title' => 'Auto Master Report',
                'columns' => ['Code', 'Registration', 'Owner', 'Status', 'City'],
                'rows' => Auto::with('owner', 'city')->get()->map(fn ($a) => [$a->code, $a->registration_number, $a->owner?->name, $a->status, $a->city?->name])->toArray(),
            ],
            'driver' => [
                'title' => 'Driver Earnings Report',
                'columns' => ['Code', 'Driver', 'Settlements', 'Total Paid'],
                'rows' => Driver::withCount('settlements')->get()->map(fn ($d) => [$d->code, $d->name, $d->settlements_count, $d->payments()->sum('amount')])->toArray(),
            ],
            default => ['title' => 'Report', 'columns' => [], 'rows' => []],
        };
    }

    protected function csv(string $type, array $columns, array $rows): StreamedResponse
    {
        $filename = 'report-'.$type.'-'.now()->format('Ymd-His').'.csv';
        return response()->streamDownload(function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
