@extends('layouts.portal')
@section('title','Technician')
@section('portal','Field Technician')
@section('content')
<div class="mb-6">
  <h1 class="text-3xl font-bold">Field Operations</h1>
  <p class="text-slate-400 text-sm">Your assigned installations &amp; maintenance jobs</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
  <div class="card p-4" data-testid="kpi-my-installations"><span class="text-xs text-slate-400 uppercase">My Installations</span><div class="mono text-3xl font-bold mt-1">{{ $installations->count() }}</div></div>
  <div class="card p-4" data-testid="kpi-my-tickets"><span class="text-xs text-slate-400 uppercase">Open Tickets</span><div class="mono text-3xl font-bold mt-1 text-amber-400">{{ $tickets->count() }}</div></div>
  <div class="card p-4" data-testid="kpi-available-jobs"><span class="text-xs text-slate-400 uppercase">Available Jobs</span><div class="mono text-3xl font-bold mt-1">{{ $openInstallations->count() }}</div></div>
</div>

<div class="card p-6 mb-6">
  <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">My Installations</h3>
  <table class="grid"><thead><tr><th>Code</th><th>Auto</th><th>Scheduled</th><th>Status</th></tr></thead><tbody>
  @forelse($installations as $i)
    <tr data-testid="tech-install-{{ $i->id }}"><td class="mono text-xs">{{ $i->code }}</td><td class="text-white font-medium">{{ $i->auto?->registration_number ?? '—' }}</td><td class="text-xs text-slate-400">{{ $i->scheduled_at?->format('d M Y, H:i') ?? '—' }}</td><td><x-badge :status="$i->status"/></td></tr>
  @empty
    <tr><td colspan="4" class="text-center py-8 text-slate-500">No installations assigned to you.</td></tr>
  @endforelse
  </tbody></table>
</div>

<div class="card p-6 mb-6">
  <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-3">My Maintenance Tickets</h3>
  <table class="grid"><thead><tr><th>Code</th><th>Auto</th><th>Issue</th><th>Priority</th><th>SLA Due</th><th>Status</th></tr></thead><tbody>
  @forelse($tickets as $t)
    <tr data-testid="tech-ticket-{{ $t->id }}">
      <td class="mono text-xs">{{ $t->code }}</td>
      <td class="text-white font-medium">{{ $t->auto?->registration_number ?? '—' }}</td>
      <td class="text-slate-300">{{ \Illuminate\Support\Str::limit($t->issue, 40) }}</td>
      <td><x-badge :status="$t->priority"/></td>
      <td class="text-xs {{ $t->sla_due && $t->sla_due->isPast() ? 'text-rose-400' : 'text-slate-400' }}">{{ $t->sla_due?->format('d M H:i') ?? '—' }}</td>
      <td><x-badge :status="$t->status"/></td>
    </tr>
  @empty
    <tr><td colspan="6" class="text-center py-8 text-slate-500">No open tickets assigned to you.</td></tr>
  @endforelse
  </tbody></table>
</div>

<div class="card p-6">
  <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-slate-300 mb-3">Available Installation Jobs</h3>
  <table class="grid"><thead><tr><th>Code</th><th>Auto</th><th>Scheduled</th><th>Status</th></tr></thead><tbody>
  @forelse($openInstallations as $i)
    <tr data-testid="tech-open-{{ $i->id }}"><td class="mono text-xs">{{ $i->code }}</td><td class="text-white font-medium">{{ $i->auto?->registration_number ?? '—' }}</td><td class="text-xs text-slate-400">{{ $i->scheduled_at?->format('d M Y, H:i') ?? '—' }}</td><td><x-badge :status="$i->status"/></td></tr>
  @empty
    <tr><td colspan="4" class="text-center py-8 text-slate-500">No unassigned jobs in the queue.</td></tr>
  @endforelse
  </tbody></table>
</div>
@endsection
