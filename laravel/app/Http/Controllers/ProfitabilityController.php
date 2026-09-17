<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Campaign;
use App\Services\ProfitabilityService;
use Illuminate\Http\Request;

class ProfitabilityController extends Controller
{
    public function index(Request $request, ProfitabilityService $svc)
    {
        $company = $svc->company($request->get('from'), $request->get('to'));
        $campaigns = Campaign::whereIn('status', ['active', 'under_delivery', 'completed'])->get()->map(function ($c) use ($svc) {
            $p = $svc->campaign($c);
            return ['campaign' => $c, 'p' => $p];
        });
        $autos = Auto::where('status', 'active')->limit(15)->get()->map(function ($a) use ($svc) {
            return ['auto' => $a, 'p' => $svc->auto($a)];
        });
        return view('finance.profitability', compact('company', 'campaigns', 'autos'));
    }
}
