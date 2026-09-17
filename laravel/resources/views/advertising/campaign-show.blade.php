@extends('layouts.app')
@section('title',$campaign->name)
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><div><a href="{{ route('campaigns.index') }}" class="text-sm text-slate-400">← Campaigns</a><h1 class="text-3xl font-bold mt-1">{{ $campaign->name }} <span class="mono text-slate-500 text-lg">{{ $campaign->code }}</span></h1><p class="text-slate-500 text-sm">{{ $campaign->advertiser?->company_name }}</p></div><x-badge :status="$campaign->status"/></div>
<div class="flex flex-wrap gap-2 mb-6">
  @if($campaign->status=='draft')<form method="POST" action="{{ route('campaigns.approve',$campaign) }}">@csrf<button class="btn btn-sec">Approve</button></form>@endif
  @if(in_array($campaign->status,['approved','scheduled','paused','draft']))<form method="POST" action="{{ route('campaigns.activate',$campaign) }}">@csrf<button class="btn btn-primary" data-testid="activate-campaign">Activate</button></form>@endif
  @if(in_array($campaign->status,['active','under_delivery']))<form method="POST" action="{{ route('campaigns.pause',$campaign) }}">@csrf<button class="btn btn-sec">Pause</button></form>@endif
  <form method="POST" action="{{ route('campaigns.invoice',$campaign) }}">@csrf<button class="btn btn-sec"><i data-lucide="file-text" class="w-4 h-4"></i>Generate Invoice</button></form>
</div>
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
  @foreach([['Expected',number_format($delivery['expected_plays'])],['Actual',number_format($delivery['actual_plays'])],['Valid',number_format($delivery['valid_plays'])],['Delivery',$delivery['delivery_percent'].'%'],['Runtime',\App\Support\Fmt::duration($delivery['runtime_seconds'])]] as $k)
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">{{ $k[0] }}</span><div class="mono text-2xl font-bold">{{ $k[1] }}</div></div>@endforeach
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Profitability</h3><div class="space-y-2 text-sm">
    @foreach(['revenue'=>'Revenue','driverCost'=>'Driver Cost','simCost'=>'SIM Cost','hardwareCost'=>'Hardware','platformCost'=>'Platform','totalCost'=>'Total Cost'] as $k=>$l)<div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">{{ $l }}</span><span class="mono">{{ $m($profit[$k]) }}</span></div>@endforeach
    <div class="flex justify-between py-1.5 font-semibold"><span>Contribution ({{ $profit['margin'] }}%)</span><span class="mono {{ $profit['contribution']>=0?'text-emerald-400':'text-rose-400' }}">{{ $m($profit['contribution']) }}</span></div>
  </div></div>
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Assign Autos ({{ $campaign->assignments->count() }})</h3>
    <form method="POST" action="{{ route('campaigns.assign',$campaign) }}">@csrf
      <select name="autos[]" multiple class="inp h-28">@foreach($availableAutos as $a)<option value="{{ $a->id }}">{{ $a->registration_number }}</option>@endforeach</select>
      <button class="btn btn-sec mt-2">Assign selected</button></form>
    <div class="mt-3 text-xs text-slate-400">Advertisements: {{ $campaign->advertisements->pluck('title')->join(', ') ?: 'none' }}</div>
  </div>
</div>
<div class="card p-6 mt-6"><h3 class="font-semibold mb-3 text-sm uppercase">Activity Timeline</h3><div class="space-y-1 text-xs">@forelse($audit as $a)<div class="flex justify-between border-b border-white/5 py-1.5"><span>{{ $a->action }} <span class="text-slate-500">by {{ $a->user?->name ?? 'system' }}</span></span><span class="text-slate-500">{{ $a->created_at->diffForHumans() }}</span></div>@empty<p class="text-slate-500">No activity</p>@endforelse</div></div>
@endsection
