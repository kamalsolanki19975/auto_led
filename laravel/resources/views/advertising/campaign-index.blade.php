@extends('layouts.app')
@section('title','Campaigns')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div class="flex items-center gap-3">
    <h1 class="font-heading text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">Campaigns</h1>
    <span class="pill bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2.5 py-1">{{ number_format($rows->total()) }} campaigns</span>
  </div>
  <a href="{{ route('campaigns.create') }}" class="btn btn-primary self-start sm:self-auto" data-testid="create-campaigns"><i data-lucide="plus" class="w-4 h-4"></i>New Campaign</a>
</div>

<div class="card p-4 mb-5">
  <form method="GET" class="flex flex-wrap gap-3 items-center">
    <div class="relative flex-1 min-w-[220px] max-w-sm">
      <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-500"></i>
      <input name="q" value="{{ request('q') }}" placeholder="Search campaigns…" class="inp pl-9" data-testid="search-campaigns">
    </div>
    <select name="status" class="inp max-w-[200px]" onchange="this.form.submit()" data-testid="filter-campaigns">
      <option value="all">All statuses</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach
    </select>
    <button class="btn btn-sec"><i data-lucide="sliders-horizontal" class="w-4 h-4"></i>Filter</button>
    @if(request('q') || (request('status') && request('status')!=='all'))<a href="{{ route('campaigns.index') }}" class="btn btn-ghost" data-testid="clear-campaigns"><i data-lucide="x" class="w-4 h-4"></i>Clear</a>@endif
  </form>
</div>

<div class="card overflow-hidden"><div class="overflow-x-auto">
  <table class="grid"><thead><tr><th>Code</th><th>Campaign</th><th>Advertiser</th><th>Delivery</th><th>Budget</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
  @forelse($rows as $c)@php($d=$service->delivery($c))
    <tr data-testid="row-campaigns-{{ $c->id }}" class="group">
      <td><span class="mono text-xs text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">{{ $c->code }}</span></td>
      <td class="font-semibold text-white">{{ $c->name }}</td>
      <td class="text-slate-400">{{ $c->advertiser?->company_name ?? '—' }}</td>
      <td><div class="flex items-center gap-2"><div class="w-24 h-2 bg-gray-800 rounded-full overflow-hidden"><div class="h-2 rounded-full bg-gradient-to-r from-amber-500 to-emerald-400" style="width:{{ min(100,$d['delivery_percent']) }}%"></div></div><span class="text-xs mono text-slate-300">{{ $d['delivery_percent'] }}%</span></div></td>
      <td class="mono text-slate-300">{{ \App\Support\Fmt::moneyShort($c->budget) }}</td>
      <td><x-badge :status="$c->status"/></td>
      <td class="text-right whitespace-nowrap">
        <div class="inline-flex items-center gap-1 justify-end">
          <a href="{{ route('campaigns.show',$c) }}" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition" title="View" data-testid="view-campaigns-{{ $c->id }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
          <a href="{{ route('campaigns.edit',$c) }}" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition" title="Edit" data-testid="edit-campaigns-{{ $c->id }}"><i data-lucide="pencil" class="w-4 h-4"></i></a>
          <form action="{{ route('campaigns.destroy',$c) }}" method="POST" class="inline" onsubmit="return confirm('Delete this campaign?')">@csrf @method('DELETE')<button class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition" title="Delete" data-testid="delete-campaigns-{{ $c->id }}"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
        </div>
      </td>
    </tr>
  @empty
    <tr><td colspan="7"><div class="text-center py-16"><span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10 text-amber-500 mb-4"><i data-lucide="megaphone" class="w-7 h-7"></i></span><div class="font-heading text-xl font-bold">No campaigns yet</div><p class="text-slate-500 text-sm mt-1">Launch your first ad campaign.</p><a href="{{ route('campaigns.create') }}" class="btn btn-primary mt-5"><i data-lucide="plus" class="w-4 h-4"></i>New Campaign</a></div></td></tr>
  @endforelse
  </tbody></table>
</div></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
