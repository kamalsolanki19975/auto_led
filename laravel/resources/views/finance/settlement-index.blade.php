@extends('layouts.app')
@section('title','Settlements')
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div class="flex items-center gap-3">
    <h1 class="font-heading text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">Driver Settlements</h1>
    <span class="pill bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2.5 py-1">Payable {{ $m($payable) }}</span>
  </div>
  <a href="{{ route('settlements.create') }}" class="btn btn-primary self-start sm:self-auto" data-testid="create-settlements"><i data-lucide="plus" class="w-4 h-4"></i>New Settlement</a>
</div>

<div class="card p-4 mb-5">
  <form method="GET" class="flex flex-wrap gap-3 items-center">
    <div class="relative flex-1 min-w-[220px] max-w-sm">
      <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-500"></i>
      <input name="q" value="{{ request('q') }}" placeholder="Search settlements…" class="inp pl-9" data-testid="search-settlements">
    </div>
    <select name="status" class="inp max-w-[200px]" onchange="this.form.submit()" data-testid="filter-settlements">
      <option value="all">All statuses</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach
    </select>
    <button class="btn btn-sec"><i data-lucide="sliders-horizontal" class="w-4 h-4"></i>Filter</button>
    @if(request('q') || (request('status') && request('status')!=='all'))<a href="{{ route('settlements.index') }}" class="btn btn-ghost" data-testid="clear-settlements"><i data-lucide="x" class="w-4 h-4"></i>Clear</a>@endif
  </form>
</div>

<div class="card overflow-hidden"><div class="overflow-x-auto">
  <table class="grid"><thead><tr><th>Code</th><th>Driver</th><th>Period</th><th>Net Payable</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
  @forelse($rows as $s)
    <tr data-testid="row-settlements-{{ $s->id }}" class="group">
      <td><span class="mono text-xs text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">{{ $s->code }}</span></td>
      <td class="font-semibold text-white">{{ $s->driver?->name ?? '—' }}</td>
      <td class="text-slate-400">{{ $s->period_start?->format('d M') }}–{{ $s->period_end?->format('d M') }}</td>
      <td class="mono text-slate-300">{{ $m($s->net_payable) }}</td>
      <td><x-badge :status="$s->status"/></td>
      <td class="text-right whitespace-nowrap"><a href="{{ route('settlements.show',$s) }}" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition" title="View" data-testid="view-settlements-{{ $s->id }}"><i data-lucide="eye" class="w-4 h-4"></i></a></td>
    </tr>
  @empty
    <tr><td colspan="6"><div class="text-center py-16"><span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10 text-amber-500 mb-4"><i data-lucide="banknote" class="w-7 h-7"></i></span><div class="font-heading text-xl font-bold">No settlements yet</div><p class="text-slate-500 text-sm mt-1">Run a settlement to pay drivers.</p><a href="{{ route('settlements.create') }}" class="btn btn-primary mt-5"><i data-lucide="plus" class="w-4 h-4"></i>New Settlement</a></div></td></tr>
  @endforelse
  </tbody></table>
</div></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
