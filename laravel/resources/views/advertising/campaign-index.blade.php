@extends('layouts.app')
@section('title','Campaigns')
@section('content')
<div class="flex items-center justify-between mb-6"><div><h1 class="text-3xl font-bold">Campaigns</h1><p class="text-slate-400 text-sm">{{ $rows->total() }} campaigns</p></div>
<a href="{{ route('campaigns.create') }}" class="btn btn-primary" data-testid="create-campaigns"><i data-lucide="plus" class="w-4 h-4"></i>New Campaign</a></div>
<div class="card p-4 mb-4"><form method="GET" class="flex gap-3"><input name="q" value="{{ request('q') }}" placeholder="Search…" class="inp max-w-xs">
<select name="status" class="inp max-w-xs" onchange="this.form.submit()"><option value="all">All statuses</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select><button class="btn btn-sec">Filter</button></form></div>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>Code</th><th>Campaign</th><th>Advertiser</th><th>Delivery</th><th>Budget</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($rows as $c)@php($d=$service->delivery($c))
<tr><td class="mono">{{ $c->code }}</td><td class="font-semibold">{{ $c->name }}</td><td>{{ $c->advertiser?->company_name }}</td>
<td><div class="flex items-center gap-2"><div class="w-20 h-1.5 bg-white/10 rounded"><div class="h-1.5 rounded bg-amber-500" style="width:{{ min(100,$d['delivery_percent']) }}%"></div></div><span class="text-xs mono">{{ $d['delivery_percent'] }}%</span></div></td>
<td class="mono">{{ \App\Support\Fmt::moneyShort($c->budget) }}</td><td><x-badge :status="$c->status"/></td>
<td class="text-right"><a href="{{ route('campaigns.show',$c) }}" class="text-amber-400 text-xs" data-testid="view-campaign-{{ $c->id }}">View</a></td></tr>
@empty<tr><td colspan="7" class="text-center py-12 text-slate-500">No campaigns yet.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
