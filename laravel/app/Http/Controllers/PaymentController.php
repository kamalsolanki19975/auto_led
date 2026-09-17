<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Vendor;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $q = Payment::with('advertiser', 'invoice', 'vendor');
        if (($type = $request->get('type')) && $type !== 'all') {
            $q->where('type', $type);
        }
        return view('finance.payment-index', [
            'rows' => $q->latest()->paginate(15)->withQueryString(),
            'received' => Payment::where('type', 'received')->sum('amount'),
            'made' => Payment::where('type', 'made')->sum('amount'),
        ]);
    }

    public function create()
    {
        return view('finance.payment-form', ['vendors' => Vendor::pluck('name', 'id')]);
    }

    public function store(Request $request, PaymentService $svc)
    {
        $data = $request->validate([
            'type' => 'required|in:received,made',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'reference' => 'nullable|string',
            'vendor_id' => 'nullable|exists:vendors,id',
            'notes' => 'nullable|string',
        ]);
        if ($data['type'] === 'received') {
            $svc->recordReceived($data, auth()->id());
        } else {
            $svc->recordMade($data, auth()->id());
        }
        return redirect()->route('payments.index')->with('success', 'Payment recorded.');
    }
}
