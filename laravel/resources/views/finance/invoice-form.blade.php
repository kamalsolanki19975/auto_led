@extends('layouts.app')@section('title','New Invoice')@section('content')
<div class="mb-6"><a href="{{ route('invoices.index') }}" class="text-sm text-slate-400">← Invoices</a><h1 class="text-3xl font-bold mt-1">New Invoice</h1></div>
<form method="POST" action="{{ route('invoices.store') }}" class="card p-6 max-w-2xl">@csrf<div class="grid grid-cols-2 gap-4">
<div><label class="text-xs text-slate-400 uppercase">Advertiser *</label><select name="advertiser_id" class="inp mt-1" required>@foreach($advertisers as $id=>$n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
<div><label class="text-xs text-slate-400 uppercase">Campaign</label><select name="campaign_id" class="inp mt-1"><option value="">—</option>@foreach($campaigns as $id=>$n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
<div><label class="text-xs text-slate-400 uppercase">Invoice Date *</label><input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" class="inp mt-1" required></div>
<div><label class="text-xs text-slate-400 uppercase">Due Date</label><input type="date" name="due_date" value="{{ date('Y-m-d',strtotime('+15 days')) }}" class="inp mt-1"></div>
<div><label class="text-xs text-slate-400 uppercase">Amount *</label><input type="number" step="any" name="amount" class="inp mt-1" required></div>
<div><label class="text-xs text-slate-400 uppercase">Tax %</label><input type="number" step="any" name="tax_percent" value="18" class="inp mt-1"></div>
<div class="col-span-2"><label class="text-xs text-slate-400 uppercase">Notes</label><textarea name="notes" class="inp mt-1"></textarea></div>
</div><div class="mt-6"><button class="btn btn-primary">Create Invoice</button></div></form>@endsection
