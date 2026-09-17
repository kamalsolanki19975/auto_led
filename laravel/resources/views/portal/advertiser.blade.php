@extends('layouts.portal')
@section('title','Advertiser')
@section('portal','Advertiser')
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
@php($activeCount=$campaigns->whereIn('status',['active','under_delivery'])->count())
@php($totalPaid=$payments->sum('amount'))
@php($totalPlays=$campaigns->sum(fn($c)=>$cs->delivery($c)['valid_plays']))

<div class="mb-6">
  <h1 class="text-3xl font-bold">{{ $advertiser->company_name }}</h1>
  <p class="text-slate-400 text-sm">{{ $advertiser->contact_person }} · {{ $advertiser->email }}</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="card p-4" data-testid="kpi-active-campaigns"><span class="text-xs text-slate-400 uppercase">Active Campaigns</span><div class="mono text-3xl font-bold mt-1">{{ $activeCount }}</div></div>
  <div class="card p-4" data-testid="kpi-total-plays"><span class="text-xs text-slate-400 uppercase">Valid Plays</span><div class="mono text-3xl font-bold mt-1">{{ number_format($totalPlays) }}</div></div>
  <div class="card p-4" data-testid="kpi-total-paid"><span class="text-xs text-slate-400 uppercase">Total Paid</span><div class="mono text-3xl font-bold mt-1 text-emerald-400">{{ \App\Support\Fmt::moneyShort($totalPaid) }}</div></div>
  <div class="card p-4 {{ $outstanding>0?'border-amber-500/40':'' }}" data-testid="kpi-outstanding"><span class="text-xs text-slate-400 uppercase">Outstanding</span><div class="mono text-3xl font-bold mt-1 {{ $outstanding>0?'text-amber-400':'' }}">{{ \App\Support\Fmt::moneyShort($outstanding) }}</div></div>
</div>

<div class="card p-6 mb-6">
  <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">My Campaigns</h3>
  <table class="grid"><thead><tr><th>Campaign</th><th>Period</th><th>Budget</th><th>Delivery</th><th>Status</th></tr></thead><tbody>
  @forelse($campaigns as $c)
    @php($d=$cs->delivery($c))
    <tr data-testid="adv-campaign-{{ $c->id }}">
      <td class="font-semibold text-white">{{ $c->name }}<div class="mono text-xs text-slate-500">{{ $c->code }}</div></td>
      <td class="text-xs text-slate-400">{{ $c->start_date?->format('d M') }} – {{ $c->end_date?->format('d M Y') }}</td>
      <td class="mono">{{ $m($c->budget) }}</td>
      <td>
        <div class="flex items-center gap-2">
          <div class="w-24 h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full bg-amber-500" style="width: {{ min(100,$d['delivery_percent']) }}%"></div></div>
          <span class="mono text-xs text-slate-400">{{ $d['delivery_percent'] }}%</span>
        </div>
        <div class="text-[.65rem] text-slate-500 mt-0.5">{{ number_format($d['valid_plays']) }} / {{ number_format($d['expected_plays']) }} plays</div>
      </td>
      <td><x-badge :status="$c->status"/></td>
    </tr>
  @empty
    <tr><td colspan="5" class="text-center py-10 text-slate-500">No campaigns yet.</td></tr>
  @endforelse
  </tbody></table>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="card p-6">
    <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">Invoices</h3>
    <table class="grid"><thead><tr><th>Invoice</th><th>Total</th><th>Due</th><th>Status</th></tr></thead><tbody>
    @forelse($invoices as $i)
      <tr data-testid="adv-invoice-{{ $i->id }}"><td class="mono text-xs">{{ $i->number }}</td><td class="mono">{{ $m($i->total) }}</td><td class="text-xs text-slate-400">{{ $i->due_date?->format('d M Y') }}</td><td><x-badge :status="$i->status"/></td></tr>
    @empty
      <tr><td colspan="4" class="text-center py-8 text-slate-500">No invoices.</td></tr>
    @endforelse
    </tbody></table>
  </div>
  <div class="card p-6">
    <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">Recent Payments</h3>
    <table class="grid"><thead><tr><th>Reference</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead><tbody>
    @forelse($payments as $p)
      <tr data-testid="adv-payment-{{ $p->id }}"><td class="mono text-xs">{{ $p->code }}</td><td class="mono text-emerald-400">{{ $m($p->amount) }}</td><td class="capitalize text-slate-300">{{ str_replace('_',' ',$p->method) }}</td><td class="text-xs text-slate-400">{{ $p->paid_at?->format('d M Y') }}</td></tr>
    @empty
      <tr><td colspan="4" class="text-center py-8 text-slate-500">No payments recorded.</td></tr>
    @endforelse
    </tbody></table>
  </div>
</div>
@endsection
