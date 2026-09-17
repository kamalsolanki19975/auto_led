@extends('layouts.app')
@section('title',$model->company_name)
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><div><a href="{{ route('advertisers.index') }}" class="text-sm text-slate-400">← Advertisers</a><h1 class="text-3xl font-bold mt-1">{{ $model->company_name }}</h1></div>
<div class="text-right"><span class="text-xs text-slate-400 uppercase">Outstanding</span><div class="mono text-2xl font-bold text-amber-400">{{ $m($outstanding) }}</div></div></div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Campaigns</h3><table class="grid"><tbody>@forelse($campaigns as $c)<tr><td><a class="text-amber-400" href="{{ route('campaigns.show',$c) }}">{{ $c->name }}</a></td><td><x-badge :status="$c->status"/></td></tr>@empty<tr><td class="text-slate-500 py-3">None</td></tr>@endforelse</tbody></table></div>
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Advertisements</h3><table class="grid"><tbody>@forelse($ads as $a)<tr><td><a class="text-amber-400" href="{{ route('advertisements.show',$a) }}">{{ $a->title }}</a></td><td><x-badge :status="$a->approval_status"/></td></tr>@empty<tr><td class="text-slate-500 py-3">None</td></tr>@endforelse</tbody></table></div>
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Invoices</h3><table class="grid"><tbody>@forelse($invoices as $i)<tr><td><a class="text-amber-400" href="{{ route('invoices.show',$i) }}">{{ $i->number }}</a></td><td class="mono">{{ $m($i->total) }}</td><td><x-badge :status="$i->status"/></td></tr>@empty<tr><td class="text-slate-500 py-3">None</td></tr>@endforelse</tbody></table></div>
  <div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Payments</h3><table class="grid"><tbody>@forelse($payments as $p)<tr><td class="mono">{{ $p->code }}</td><td class="mono">{{ $m($p->amount) }}</td><td>{{ $p->method }}</td></tr>@empty<tr><td class="text-slate-500 py-3">None</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
