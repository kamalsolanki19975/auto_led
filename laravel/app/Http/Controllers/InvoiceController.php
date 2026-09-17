<?php

namespace App\Http\Controllers;

use App\Models\Advertiser;
use App\Models\Campaign;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request, InvoiceService $svc)
    {
        $svc->markOverdueInvoices();
        $q = Invoice::with('advertiser', 'campaign');
        if ($s = $request->get('q')) {
            $q->where('number', 'like', "%$s%");
        }
        if (($status = $request->get('status')) && $status !== 'all') {
            $q->where('status', $status);
        }
        if (auth()->user()->primaryPortal() === 'advertiser') {
            $q->where('advertiser_id', auth()->user()->advertiser_id);
        }
        return view('finance.invoice-index', [
            'rows' => $q->latest()->paginate(15)->withQueryString(),
            'statuses' => ['draft', 'issued', 'partially_paid', 'paid', 'overdue', 'cancelled'],
            'outstanding' => Invoice::whereIn('status', ['issued', 'partially_paid', 'overdue'])->sum(\DB::raw('total - amount_paid')),
        ]);
    }

    public function create()
    {
        return view('finance.invoice-form', [
            'advertisers' => Advertiser::orderBy('company_name')->pluck('company_name', 'id'),
            'campaigns' => Campaign::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'advertiser_id' => 'required|exists:advertisers,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'amount' => 'required|numeric',
            'tax_percent' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);
        $tax = $data['tax_percent'] ?? 18;
        $taxAmount = round($data['amount'] * $tax / 100, 2);
        $invoice = Invoice::create(array_merge($data, [
            'number' => \App\Support\Codes::next('invoices', 'INV', 5, 'number'),
            'tax_percent' => $tax,
            'tax_amount' => $taxAmount,
            'total' => round($data['amount'] + $taxAmount, 2),
            'status' => 'issued',
            'created_by' => auth()->id(),
        ]));
        $invoice->items()->create(['description' => $data['notes'] ?? 'Advertising services', 'quantity' => 1, 'rate' => $data['amount'], 'amount' => $data['amount']]);
        \App\Services\AuditService::log('invoice.created', $invoice);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice)
    {
        return view('finance.invoice-show', [
            'invoice' => $invoice->load('advertiser', 'campaign', 'items', 'payments'),
        ]);
    }

    public function recordPayment(Request $request, Invoice $invoice, PaymentService $svc)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'reference' => 'nullable|string',
        ]);
        $svc->recordReceived(array_merge($data, ['invoice_id' => $invoice->id]), auth()->id());
        return back()->with('success', 'Payment recorded against '.$invoice->number.'.');
    }
}
