@extends('layouts.app')@section('title',$invoice->number)@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><div><a href="{{ route('invoices.index') }}" class="text-sm text-slate-400">← Invoices</a><h1 class="text-3xl font-bold mt-1">{{ $invoice->number }}</h1><p class="text-slate-500">{{ $invoice->advertiser?->company_name }}</p></div><x-badge :status="$invoice->status"/></div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<div class="card p-6 lg:col-span-2"><table class="grid"><thead><tr><th>Description</th><th>Qty</th><th>Rate</th><th class="text-right">Amount</th></tr></thead><tbody>
@foreach($invoice->items as $it)<tr><td>{{ $it->description }}</td><td>{{ $it->quantity }}</td><td class="mono">{{ $m($it->rate) }}</td><td class="text-right mono">{{ $m($it->amount) }}</td></tr>@endforeach
</tbody></table><div class="mt-4 text-sm space-y-1 text-right"><div>Subtotal: <span class="mono">{{ $m($invoice->amount) }}</span></div><div>Tax ({{ $invoice->tax_percent }}%): <span class="mono">{{ $m($invoice->tax_amount) }}</span></div><div class="font-bold text-lg">Total: <span class="mono">{{ $m($invoice->total) }}</span></div><div class="text-emerald-400">Paid: <span class="mono">{{ $m($invoice->amount_paid) }}</span></div></div></div>
<div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Record Payment</h3>
<form method="POST" action="{{ route('invoices.payment',$invoice) }}" class="space-y-3">@csrf
<input type="number" step="any" name="amount" value="{{ $invoice->balance() }}" class="inp" placeholder="Amount" required>
<select name="method" class="inp"><option value="bank_transfer">Bank Transfer</option><option value="upi">UPI</option><option value="cash">Cash</option><option value="gateway">Gateway</option></select>
<input name="reference" class="inp" placeholder="Reference"><button class="btn btn-primary w-full" data-testid="record-payment">Record Payment</button></form>
<h3 class="font-semibold mt-6 mb-2 text-sm uppercase">Payments</h3>@forelse($invoice->payments as $p)<div class="flex justify-between text-sm border-b border-white/5 py-1.5"><span class="mono">{{ $p->code }}</span><span class="mono">{{ $m($p->amount) }}</span></div>@empty<p class="text-slate-500 text-sm">None</p>@endforelse
</div></div>@endsection
