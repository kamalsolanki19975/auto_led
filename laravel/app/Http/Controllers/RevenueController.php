<?php

namespace App\Http\Controllers;

use App\Models\Revenue;
use App\Support\Codes;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index()
    {
        return view('finance.revenue', [
            'rows' => Revenue::with('campaign', 'advertiser')->latest()->paginate(15),
            'total' => Revenue::sum('amount'),
            'monthly' => Revenue::whereMonth('date', now()->month)->sum('amount'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['source' => 'required|string', 'amount' => 'required|numeric', 'date' => 'required|date', 'notes' => 'nullable|string']);
        Revenue::create(array_merge($data, ['code' => Codes::next('revenues', 'REV')]));
        return back()->with('success', 'Revenue recorded.');
    }
}
