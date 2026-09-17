@extends('layouts.app')@section('title','New Payment')@section('content')
<div class="mb-6"><a href="{{ route('payments.index') }}" class="text-sm text-slate-400">← Payments</a><h1 class="text-3xl font-bold mt-1">Record Payment</h1></div>
<form method="POST" action="{{ route('payments.store') }}" class="card p-6 max-w-xl space-y-3">@csrf
<div><label class="text-xs text-slate-400 uppercase">Type</label><select name="type" class="inp mt-1"><option value="received">Received</option><option value="made">Made (to vendor)</option></select></div>
<div><label class="text-xs text-slate-400 uppercase">Vendor (if made)</label><select name="vendor_id" class="inp mt-1"><option value="">—</option>@foreach($vendors as $id=>$n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
<div><label class="text-xs text-slate-400 uppercase">Amount *</label><input type="number" step="any" name="amount" class="inp mt-1" required></div>
<div><label class="text-xs text-slate-400 uppercase">Method</label><select name="method" class="inp mt-1"><option value="bank_transfer">Bank Transfer</option><option value="upi">UPI</option><option value="cash">Cash</option><option value="gateway">Gateway</option></select></div>
<div><label class="text-xs text-slate-400 uppercase">Reference</label><input name="reference" class="inp mt-1"></div>
<button class="btn btn-primary">Save Payment</button></form>@endsection
