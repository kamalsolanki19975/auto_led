@extends('layouts.portal')
@section('title','Driver')
@section('portal','Driver')
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
@php($dur=fn($s)=>\App\Support\Fmt::duration((int)$s))

<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-3xl font-bold">Hi, {{ $driver->name }}</h1>
    <p class="text-slate-400 text-sm">{{ $driver->code }} · {{ $driver->mobile }}</p>
  </div>
  <x-badge :status="$driver->status"/>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="card p-4" data-testid="kpi-today-runtime"><span class="text-xs text-slate-400 uppercase">Today Runtime</span><div class="mono text-2xl font-bold mt-1">{{ $dur($todayRuntime) }}</div></div>
  <div class="card p-4" data-testid="kpi-today-plays"><span class="text-xs text-slate-400 uppercase">Today Plays</span><div class="mono text-2xl font-bold mt-1">{{ number_format($todayPlays) }}</div></div>
  <div class="card p-4" data-testid="kpi-month-runtime"><span class="text-xs text-slate-400 uppercase">This Month</span><div class="mono text-2xl font-bold mt-1">{{ $dur($monthRuntime) }}</div></div>
  <div class="card p-4" data-testid="kpi-my-auto"><span class="text-xs text-slate-400 uppercase">My Auto</span><div class="text-lg font-bold mt-1">{{ $auto?->registration_number ?? '—' }}</div>
    @if($auto)<div class="text-[.65rem] text-slate-500 mt-0.5">{{ $auto->device?->isOnline() ? 'Device Online' : 'Device Offline' }}</div>@endif
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="card p-6">
    <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">Recent Earnings</h3>
    <table class="grid"><thead><tr><th>Period</th><th>Valid Plays</th><th>Net</th><th>Status</th></tr></thead><tbody>
    @forelse($earnings as $e)
      <tr data-testid="drv-earning-{{ $e->id }}"><td class="text-xs">{{ $e->period_start?->format('d M') }} – {{ $e->period_end?->format('d M') }}</td><td class="mono">{{ number_format($e->valid_plays) }}</td><td class="mono text-emerald-400">{{ $m($e->net_amount) }}</td><td><x-badge :status="$e->status"/></td></tr>
    @empty
      <tr><td colspan="4" class="text-center py-8 text-slate-500">No earnings yet.</td></tr>
    @endforelse
    </tbody></table>
  </div>
  <div class="card p-6">
    <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">Payments</h3>
    <table class="grid"><thead><tr><th>Reference</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead><tbody>
    @forelse($payments as $p)
      <tr data-testid="drv-payment-{{ $p->id }}"><td class="mono text-xs">{{ $p->code }}</td><td class="mono text-emerald-400">{{ $m($p->amount) }}</td><td class="capitalize text-slate-300">{{ str_replace('_',' ',$p->method) }}</td><td class="text-xs text-slate-400">{{ $p->paid_at?->format('d M Y') }}</td></tr>
    @empty
      <tr><td colspan="4" class="text-center py-8 text-slate-500">No payments received yet.</td></tr>
    @endforelse
    </tbody></table>
  </div>
</div>

<div class="card p-6">
  <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">Settlements</h3>
  <table class="grid"><thead><tr><th>Code</th><th>Period</th><th>Net Payable</th><th>Status</th><th class="text-right">Action</th></tr></thead><tbody>
  @forelse($settlements as $s)
    <tr data-testid="drv-settlement-{{ $s->id }}" x-data="{open:false}">
      <td class="mono text-xs">{{ $s->code }}</td>
      <td class="text-xs">{{ $s->period_start?->format('d M') }} – {{ $s->period_end?->format('d M Y') }}</td>
      <td class="mono">{{ $m($s->net_payable) }}</td>
      <td><x-badge :status="$s->status"/></td>
      <td class="text-right">
        @if(!in_array($s->status,['paid']))
          <button @click="open=!open" class="text-amber-400 text-xs" data-testid="dispute-toggle-{{ $s->id }}">Raise dispute</button>
        @else<span class="text-slate-500 text-xs">—</span>@endif
      </td>
      <template x-if="open">
        <td colspan="5" class="bg-white/[.02]">
          <form method="POST" action="{{ route('portal.dispute',$s) }}" class="flex flex-wrap items-end gap-3 py-2">@csrf
            <div><label class="text-[.65rem] text-slate-400 uppercase">Disputed Amount</label><input type="number" step="any" name="disputed_amount" class="inp" style="width:140px" data-testid="dispute-amount-{{ $s->id }}"></div>
            <div class="flex-1 min-w-[200px]"><label class="text-[.65rem] text-slate-400 uppercase">Reason</label><input name="reason" class="inp" required data-testid="dispute-reason-{{ $s->id }}" placeholder="Describe the issue"></div>
            <button class="btn btn-primary" data-testid="dispute-submit-{{ $s->id }}">Submit</button>
          </form>
        </td>
      </template>
    </tr>
  @empty
    <tr><td colspan="5" class="text-center py-8 text-slate-500">No settlements yet.</td></tr>
  @endforelse
  </tbody></table>
  @if($disputes->count())
    <div class="mt-4 border-t border-white/10 pt-3">
      <div class="text-xs text-slate-400 uppercase mb-2">My Disputes</div>
      @foreach($disputes as $d)
        <div class="flex items-center justify-between text-sm py-1.5 border-b border-white/5">
          <span class="mono text-xs">{{ $d->code }}</span>
          <span class="text-slate-400 flex-1 px-3 truncate">{{ $d->reason }}</span>
          <span class="mono text-xs">{{ $m($d->disputed_amount) }}</span>
          <span class="ml-3"><x-badge :status="$d->status"/></span>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
