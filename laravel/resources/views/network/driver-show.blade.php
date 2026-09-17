@extends('layouts.app')
@section('title',$model->name)
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><div><a href="{{ route('drivers.index') }}" class="text-sm text-slate-400">← Drivers</a><h1 class="text-3xl font-bold mt-1">{{ $model->name }} <span class="mono text-slate-500 text-lg">{{ $model->code }}</span></h1></div><x-badge :status="$model->status"/></div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Valid Runtime</span><div class="mono text-2xl font-bold">{{ \App\Support\Fmt::duration($validRuntime) }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Valid Plays</span><div class="mono text-2xl font-bold">{{ number_format($validPlays) }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Auto</span><div class="text-lg font-bold">{{ $auto?->registration_number ?? '—' }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Total Paid</span><div class="mono text-2xl font-bold text-emerald-400">{{ $m($model->payments()->sum('amount')) }}</div></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Settlements</h3><table class="grid"><thead><tr><th>Code</th><th>Period</th><th>Net</th><th>Status</th></tr></thead><tbody>
    @forelse($settlements as $s)<tr><td><a class="text-amber-400" href="{{ route('settlements.show',$s) }}">{{ $s->code }}</a></td><td>{{ $s->period_start?->format('d M') }}–{{ $s->period_end?->format('d M') }}</td><td class="mono">{{ $m($s->net_payable) }}</td><td><x-badge :status="$s->status"/></td></tr>@empty<tr><td colspan="4" class="text-slate-500 py-3">None</td></tr>@endforelse
  </tbody></table></div>
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Earnings</h3><table class="grid"><thead><tr><th>Period</th><th>Plays</th><th>Net</th></tr></thead><tbody>
    @forelse($earnings as $e)<tr><td>{{ $e->period_start?->format('d M') }}</td><td>{{ $e->valid_plays }}</td><td class="mono">{{ $m($e->net_amount) }}</td></tr>@empty<tr><td colspan="3" class="text-slate-500 py-3">None</td></tr>@endforelse
  </tbody></table></div>
</div>
@endsection
