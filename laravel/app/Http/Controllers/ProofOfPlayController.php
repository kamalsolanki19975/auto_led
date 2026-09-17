<?php

namespace App\Http\Controllers;

use App\Models\ProofOfPlay;
use Illuminate\Http\Request;

class ProofOfPlayController extends Controller
{
    public function index(Request $request)
    {
        $q = ProofOfPlay::with('campaign', 'auto', 'advertisement', 'driver');
        if (($status = $request->get('status')) && $status !== 'all') {
            $q->where('status', $status);
        }
        return view('advertising.pop', [
            'rows' => $q->latest()->paginate(20)->withQueryString(),
            'statuses' => ['received', 'validating', 'valid', 'invalid', 'duplicate', 'rejected'],
            'stats' => [
                'valid' => ProofOfPlay::where('status', 'valid')->count(),
                'invalid' => ProofOfPlay::where('status', 'invalid')->count(),
                'total' => ProofOfPlay::count(),
                'runtime' => (int) ProofOfPlay::where('status', 'valid')->sum('valid_duration'),
            ],
        ]);
    }
}
