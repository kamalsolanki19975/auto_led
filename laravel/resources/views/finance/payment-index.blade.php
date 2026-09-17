@extends('layouts.app')
@section('title','Payments')
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div class="flex items-center gap-3">
    <h1 class="font-heading text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">Payments</h1>
    <span class="pill bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2.5 py-1">In {{ $m($received) }}</span>
    <span class="pill bg-rose-500/10 text-rose-400 border border-rose-500/20 px-2.5 py-1">Out {{ $m($made) }}</span>
  </div>
  <a href="{{ route('payments.create') }}" class="btn btn-primary self-start sm:self-auto" data-testid="create-payments"><i data-lucide="plus" class="w-4 h-4"></i>New Payment</a>
</div>

<div class="card overflow-hidden"><div class="overflow-x-auto">
  <table class="grid"><thead><tr><th>Code</th><th>Type</th><th>Party</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead><tbody>
  @forelse($rows as $p)
    <tr data-testid="row-payments-{{ $p->id }}" class="group">
      <td><span class="mono text-xs text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">{{ $p->code }}</span></td>
      <td><x-badge :status="$p->type"/></td>
      <td class="text-slate-300">{{ $p->advertiser?->company_name ?? $p->vendor?->name ?? '—' }}</td>
      <td class="mono text-slate-300">{{ $m($p->amount) }}</td>
      <td class="text-slate-400">{{ $p->method }}</td>
      <td class="text-slate-400">{{ $p->paid_at?->format('d M Y') }}</td>
    </tr>
  @empty
    <tr><td colspan="6"><div class="text-center py-16"><span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10 text-amber-500 mb-4"><i data-lucide="wallet" class="w-7 h-7"></i></span><div class="font-heading text-xl font-bold">No payments yet</div><p class="text-slate-500 text-sm mt-1">Record your first payment.</p><a href="{{ route('payments.create') }}" class="btn btn-primary mt-5"><i data-lucide="plus" class="w-4 h-4"></i>New Payment</a></div></td></tr>
  @endforelse
  </tbody></table>
</div></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
