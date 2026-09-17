@extends('layouts.portal')
@section('title','Fleet Owner')
@section('portal','Fleet Owner')
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
@php($activeAutos=$autos->where('status','active')->count())
@php($settlementTotal=$settlements->sum('net_payable'))
@php($pendingSettlements=$settlements->whereNotIn('status',['paid'])->count())

<div class="mb-6">
  <h1 class="text-3xl font-bold">{{ $owner->name }}</h1>
  <p class="text-slate-400 text-sm">{{ $owner->code }} · {{ $owner->mobile }} · {{ $owner->email }}</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="card p-4" data-testid="kpi-fleet-size"><span class="text-xs text-slate-400 uppercase">Fleet Size</span><div class="mono text-3xl font-bold mt-1">{{ $autos->count() }}</div></div>
  <div class="card p-4" data-testid="kpi-active-autos"><span class="text-xs text-slate-400 uppercase">Active Autos</span><div class="mono text-3xl font-bold mt-1 text-emerald-400">{{ $activeAutos }}</div></div>
  <div class="card p-4" data-testid="kpi-settlement-total"><span class="text-xs text-slate-400 uppercase">Settlements Value</span><div class="mono text-3xl font-bold mt-1">{{ \App\Support\Fmt::moneyShort($settlementTotal) }}</div></div>
  <div class="card p-4 {{ $pendingSettlements>0?'border-amber-500/40':'' }}" data-testid="kpi-pending-settlements"><span class="text-xs text-slate-400 uppercase">Pending</span><div class="mono text-3xl font-bold mt-1 {{ $pendingSettlements>0?'text-amber-400':'' }}">{{ $pendingSettlements }}</div></div>
</div>

<div class="card p-6 mb-6">
  <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">My Fleet</h3>
  <table class="grid"><thead><tr><th>Auto</th><th>Driver</th><th>Screen</th><th>Device</th><th>Status</th></tr></thead><tbody>
  @forelse($autos as $a)
    <tr data-testid="own-auto-{{ $a->id }}">
      <td class="font-semibold text-white">{{ $a->registration_number }}<div class="mono text-xs text-slate-500">{{ $a->manufacturer }} {{ $a->model }}</div></td>
      <td class="text-slate-300">{{ $a->primaryDriver?->name ?? '—' }}</td>
      <td class="text-slate-300">{{ $a->screen?->code ?? '—' }}</td>
      <td>@if($a->device)<span class="{{ $a->device->isOnline()?'text-emerald-400':'text-rose-400' }} text-xs font-medium">{{ $a->device->isOnline()?'Online':'Offline' }}</span>@else<span class="text-slate-500 text-xs">No device</span>@endif</td>
      <td><x-badge :status="$a->status"/></td>
    </tr>
  @empty
    <tr><td colspan="5" class="text-center py-10 text-slate-500">No autos in your fleet.</td></tr>
  @endforelse
  </tbody></table>
</div>

<div class="card p-6">
  <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">Settlements</h3>
  <table class="grid"><thead><tr><th>Code</th><th>Period</th><th>Total Earnings</th><th>Net Payable</th><th>Status</th></tr></thead><tbody>
  @forelse($settlements as $s)
    <tr data-testid="own-settlement-{{ $s->id }}"><td class="mono text-xs">{{ $s->code }}</td><td class="text-xs">{{ $s->period_start?->format('d M') }} – {{ $s->period_end?->format('d M Y') }}</td><td class="mono">{{ $m($s->total_earnings) }}</td><td class="mono text-emerald-400">{{ $m($s->net_payable) }}</td><td><x-badge :status="$s->status"/></td></tr>
  @empty
    <tr><td colspan="5" class="text-center py-8 text-slate-500">No settlements yet.</td></tr>
  @endforelse
  </tbody></table>
</div>
@endsection
