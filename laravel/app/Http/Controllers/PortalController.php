<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Campaign;
use App\Models\Device;
use App\Models\Driver;
use App\Models\DriverSettlement;
use App\Models\Invoice;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\ProofOfPlay;
use App\Models\RuntimeLog;
use App\Services\ProfitabilityService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function advertiser(ProfitabilityService $profit)
    {
        $advertiser = auth()->user()->advertiser;
        abort_unless($advertiser, 403, 'No advertiser profile linked to your account.');
        $campaigns = $advertiser->campaigns()->latest()->get();
        $cs = app(\App\Services\CampaignService::class);
        return view('portal.advertiser', [
            'advertiser' => $advertiser,
            'campaigns' => $campaigns,
            'invoices' => $advertiser->invoices()->latest()->get(),
            'payments' => $advertiser->payments()->latest()->limit(10)->get(),
            'outstanding' => $advertiser->outstanding(),
            'cs' => $cs,
        ]);
    }

    public function driver()
    {
        $driver = auth()->user()->driver;
        abort_unless($driver, 403, 'No driver profile linked to your account.');
        $today = now()->toDateString();
        return view('portal.driver', [
            'driver' => $driver,
            'auto' => $driver->autos()->with(['device', 'screen', 'sim'])->first(),
            'todayRuntime' => (int) RuntimeLog::where('driver_id', $driver->id)->whereDate('date', $today)->sum('valid_advertising_runtime_seconds'),
            'monthRuntime' => (int) RuntimeLog::where('driver_id', $driver->id)->whereMonth('date', now()->month)->sum('valid_advertising_runtime_seconds'),
            'todayPlays' => (int) RuntimeLog::where('driver_id', $driver->id)->whereDate('date', $today)->sum('valid_plays'),
            'earnings' => $driver->earnings()->latest()->limit(6)->get(),
            'settlements' => $driver->settlements()->latest()->get(),
            'payments' => $driver->payments()->latest()->limit(10)->get(),
            'disputes' => $driver->disputes()->latest()->get(),
        ]);
    }

    public function owner()
    {
        $owner = auth()->user()->owner;
        abort_unless($owner, 403, 'No owner profile linked to your account.');
        return view('portal.owner', [
            'owner' => $owner,
            'autos' => $owner->autos()->with(['device', 'screen', 'primaryDriver'])->get(),
            'settlements' => DriverSettlement::where('owner_id', $owner->id)->latest()->get(),
        ]);
    }

    public function technician()
    {
        $user = auth()->user();
        return view('portal.technician', [
            'installations' => \App\Models\Installation::where('technician_id', $user->id)->whereIn('status', ['scheduled', 'in_progress'])->with('auto')->get(),
            'tickets' => MaintenanceTicket::where('technician_id', $user->id)->whereIn('status', ['open', 'assigned', 'in_progress'])->with('auto')->get(),
            'openInstallations' => \App\Models\Installation::whereIn('status', ['scheduled'])->with('auto')->limit(20)->get(),
        ]);
    }

    // Driver raises a dispute against a settlement
    public function raiseDispute(Request $request, DriverSettlement $settlement)
    {
        $data = $request->validate(['reason' => 'required|string', 'disputed_amount' => 'nullable|numeric']);
        $settlement->disputes()->create([
            'code' => \App\Support\Codes::next('driver_disputes', 'DIS'),
            'driver_id' => $settlement->driver_id,
            'owner_id' => $settlement->owner_id,
            'disputed_amount' => $data['disputed_amount'] ?? 0,
            'reason' => $data['reason'],
            'status' => 'open',
        ]);
        return back()->with('success', 'Dispute submitted. Our finance team will review it.');
    }
}
