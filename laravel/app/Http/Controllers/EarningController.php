<?php

namespace App\Http\Controllers;

use App\Models\DriverEarning;
use App\Models\ProofOfPlay;
use App\Models\Revenue;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    public function index(Request $request)
    {
        $rows = DriverEarning::with('driver', 'campaign', 'settlement')->latest()->paginate(15);
        return view('finance.earnings', ['rows' => $rows, 'total' => DriverEarning::sum('net_amount')]);
    }
}
