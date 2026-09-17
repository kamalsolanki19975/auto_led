@extends('layouts.app')
@section('title','Proof of Play')
@section('content')
<h1 class="text-3xl font-bold mb-1">Proof of Play</h1><p class="text-slate-400 text-sm mb-6">Server-validated playback records</p>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Total</span><div class="mono text-2xl font-bold">{{ number_format($stats['total']) }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Valid</span><div class="mono text-2xl font-bold text-emerald-400">{{ number_format($stats['valid']) }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Invalid</span><div class="mono text-2xl font-bold text-rose-400">{{ number_format($stats['invalid']) }}</div></div>
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase">Valid Runtime</span><div class="mono text-2xl font-bold">{{ \App\Support\Fmt::duration($stats['runtime']) }}</div></div>
</div>
<div class="card p-4 mb-4"><form method="GET" class="flex gap-3"><select name="status" class="inp max-w-xs" onchange="this.form.submit()"><option value="all">All</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></form></div>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>Event</th><th>Campaign</th><th>Auto</th><th>Ad</th><th>Completion</th><th>Valid s</th><th>Status</th></tr></thead><tbody>
@forelse($rows as $p)<tr><td class="mono text-xs">{{ \Illuminate\Support\Str::limit($p->event_id,8,'') }}</td><td>{{ $p->campaign?->code }}</td><td>{{ $p->auto?->registration_number }}</td><td>{{ \Illuminate\Support\Str::limit($p->advertisement?->title,18) }}</td><td>{{ $p->completion_percent }}%</td><td class="mono">{{ $p->valid_duration }}</td><td><x-badge :status="$p->status"/></td></tr>@empty<tr><td colspan="7" class="text-center py-12 text-slate-500">No records</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
