@extends('layouts.app')@section('title','Settlement '.$settlement->code)@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><div><a href="{{ route('settlements.index') }}" class="text-sm text-slate-400">← Settlements</a><h1 class="text-3xl font-bold mt-1">{{ $settlement->code }}</h1><p class="text-slate-500">{{ $settlement->driver?->name }} · {{ $settlement->period_start?->format('d M') }}–{{ $settlement->period_end?->format('d M Y') }}</p></div><x-badge :status="$settlement->status"/></div>
<div class="flex gap-2 mb-6">
@if($settlement->status=='draft')<form method="POST" action="{{ route('settlements.approve',$settlement) }}">@csrf<button class="btn btn-primary" data-testid="approve-settlement">Approve</button></form>@endif
@if($settlement->status=='approved')<form method="POST" action="{{ route('settlements.pay',$settlement) }}" class="flex gap-2">@csrf<select name="method" class="inp"><option value="bank_transfer">Bank Transfer</option><option value="upi">UPI</option></select><input name="reference" class="inp" placeholder="Ref"><button class="btn btn-primary" data-testid="pay-settlement">Process Payment</button></form>@endif
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
@foreach([['Total Earnings',$settlement->total_earnings],['Adjustments',$settlement->total_adjustments],['Net Payable',$settlement->net_payable]] as $k)<div class="card p-4"><span class="text-xs text-slate-400 uppercase">{{ $k[0] }}</span><div class="mono text-2xl font-bold">{{ $m($k[1]) }}</div></div>@endforeach
</div>
<div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Calculation Snapshot (immutable)</h3><div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">@foreach(($settlement->calculation_snapshot??[]) as $k=>$v)<div><span class="text-slate-400 text-xs uppercase">{{ str_replace('_',' ',$k) }}</span><div class="mono">{{ is_numeric($v)?$v:$v }}</div></div>@endforeach</div></div>
@if($settlement->payments->count())<div class="card p-6 mt-6"><h3 class="font-semibold mb-3 text-sm uppercase">Payments</h3>@foreach($settlement->payments as $p)<div class="flex justify-between text-sm border-b border-white/5 py-1.5"><span class="mono">{{ $p->code }}</span><span>{{ $p->method }}</span><span class="mono">{{ $m($p->amount) }}</span></div>@endforeach</div>@endif
@endsection
