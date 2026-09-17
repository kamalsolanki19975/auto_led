<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverSettlement;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function __construct(protected SettlementService $service) {}

    public function index(Request $request)
    {
        $q = DriverSettlement::with('driver', 'owner');
        if (($status = $request->get('status')) && $status !== 'all') {
            $q->where('status', $status);
        }
        return view('finance.settlement-index', [
            'rows' => $q->latest()->paginate(15)->withQueryString(),
            'statuses' => ['draft', 'review', 'approved', 'paid'],
            'payable' => DriverSettlement::whereIn('status', ['draft', 'review', 'approved'])->sum('net_payable'),
        ]);
    }

    public function create()
    {
        return view('finance.settlement-form', ['drivers' => Driver::orderBy('name')->pluck('name', 'id')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
        ]);
        $settlement = $this->service->create(Driver::find($data['driver_id']), \Carbon\Carbon::parse($data['period_start']), \Carbon\Carbon::parse($data['period_end']));
        return redirect()->route('settlements.show', $settlement)->with('success', 'Settlement '.$settlement->code.' created.');
    }

    public function show(DriverSettlement $settlement)
    {
        return view('finance.settlement-show', [
            'settlement' => $settlement->load('driver', 'owner', 'earnings', 'payments', 'disputes'),
        ]);
    }

    public function approve(DriverSettlement $settlement)
    {
        $this->service->approve($settlement, auth()->id());
        return back()->with('success', 'Settlement approved.');
    }

    public function pay(Request $request, DriverSettlement $settlement)
    {
        $data = $request->validate(['amount' => 'nullable|numeric', 'method' => 'required|string', 'reference' => 'nullable|string']);
        $this->service->pay($settlement, $data, auth()->id());
        return back()->with('success', 'Driver payment processed and recorded as an expense.');
    }
}
