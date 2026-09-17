@extends('layouts.app')
@section('title',$model->registration_number)
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><div><a href="{{ route('autos.index') }}" class="text-sm text-slate-400">← Autos</a>
<h1 class="text-3xl font-bold mt-1">{{ $model->registration_number }} <span class="text-slate-500 text-lg mono">{{ $model->code }}</span></h1></div>
<x-badge :status="$model->status"/></div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Valid Runtime</span><div class="mono text-2xl font-bold">{{ \App\Support\Fmt::duration($runtimeSeconds) }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Valid Plays</span><div class="mono text-2xl font-bold">{{ number_format($validPlays) }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Revenue</span><div class="mono text-2xl font-bold text-emerald-400">{{ $m($profit['revenue']) }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Contribution</span><div class="mono text-2xl font-bold {{ $profit['contribution']>=0?'text-emerald-400':'text-rose-400' }}">{{ $m($profit['contribution']) }}</div></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Assignment Chain</h3><div class="space-y-2 text-sm">
    <div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">Owner</span><span>{{ $model->owner?->name ?? '—' }}</span></div>
    <div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">Driver</span><span>{{ $model->primaryDriver?->name ?? '—' }}</span></div>
    <div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">Screen</span><span>{{ $model->screen?->serial_number ?? '—' }}</span></div>
    <div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">Device</span><span>{{ $model->device?->code ?? '—' }}</span></div>
    <div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">SIM</span><span>{{ $model->sim?->mobile_number ?? '—' }}</span></div>
    <div class="flex justify-between py-1.5"><span class="text-slate-400">City / Area</span><span>{{ $model->city?->name }} / {{ $model->area?->name }}</span></div>
  </div></div>
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Auto Profitability</h3><div class="space-y-2 text-sm">
    @foreach(['revenue'=>'Ad Revenue','driverCost'=>'Driver Payment','simCost'=>'SIM Cost','maintenanceCost'=>'Maintenance','hardwareCost'=>'Hardware','totalCost'=>'Total Cost'] as $k=>$l)
    <div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">{{ $l }}</span><span class="mono">{{ $m($profit[$k]) }}</span></div>@endforeach
    <div class="flex justify-between py-1.5 font-semibold"><span>Contribution</span><span class="mono {{ $profit['contribution']>=0?'text-emerald-400':'text-rose-400' }}">{{ $m($profit['contribution']) }}</span></div>
  </div></div>
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Campaigns</h3><div class="space-y-2 text-sm">
    @forelse($campaigns as $c)<a href="{{ route('campaigns.show',$c) }}" class="flex justify-between border-b border-white/5 py-1.5 hover:bg-white/5"><span class="text-amber-400">{{ $c->name }}</span><x-badge :status="$c->status"/></a>@empty<p class="text-slate-500">None</p>@endforelse
  </div></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Maintenance</h3><table class="grid"><tbody>@forelse($maintenance as $t)<tr><td>{{ $t->code }}</td><td>{{ $t->issue }}</td><td><x-badge :status="$t->status"/></td></tr>@empty<tr><td class="text-slate-500 py-3">No tickets</td></tr>@endforelse</tbody></table></div>
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Audit History</h3><div class="space-y-1 text-xs">@forelse($audit as $a)<div class="flex justify-between border-b border-white/5 py-1.5"><span>{{ $a->action }}</span><span class="text-slate-500">{{ $a->created_at->diffForHumans() }}</span></div>@empty<p class="text-slate-500">No history</p>@endforelse</div></div>
</div>
@endsection
