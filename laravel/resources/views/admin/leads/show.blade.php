@extends('layouts.app')
@section('title','Lead '.$lead->code)
@section('content')
@php($statusColors = ['new'=>'sky','contacted'=>'amber','qualified'=>'violet','proposal'=>'blue','converted'=>'emerald','lost'=>'rose'])
<div class="mb-6"><a href="{{ route('leads.index') }}" class="text-sm text-slate-400">← Website Leads</a></div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">
    <div class="card p-6">
      <div class="flex items-start justify-between">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold">{{ $lead->name }}</h1>
            @php($c=$statusColors[$lead->status] ?? 'slate')
            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs bg-{{ $c }}-500/15 text-{{ $c }}-400 border border-{{ $c }}-500/30 capitalize" data-testid="lead-status-badge">{{ $lead->status }}</span>
          </div>
          <p class="text-slate-400 text-sm mt-1">{{ $lead->company ?? 'Individual' }} · <span class="capitalize">{{ str_replace('_',' ',$lead->type) }}</span> · {{ $lead->code }}</p>
        </div>
        <span class="text-xs text-slate-500">{{ $lead->created_at?->format('d M Y, H:i') }}</span>
      </div>
      <div class="grid sm:grid-cols-2 gap-4 mt-6 text-sm">
        <div class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-brand"></i><a href="mailto:{{ $lead->email }}" class="text-slate-200 hover:text-white">{{ $lead->email }}</a></div>
        <div class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-brand"></i><span class="text-slate-200">{{ $lead->phone ?? '—' }}</span></div>
        <div class="flex items-center gap-2"><i data-lucide="globe" class="w-4 h-4 text-brand"></i><span class="text-slate-200">Source: {{ $lead->source }} ({{ $lead->page }})</span></div>
      </div>
      @if($lead->message)
        <div class="mt-6 border-t border-white/10 pt-4">
          <div class="text-xs uppercase tracking-widest text-slate-500 mono mb-2">Message</div>
          <p class="text-slate-300 whitespace-pre-line">{{ $lead->message }}</p>
        </div>
      @endif
    </div>
  </div>

  <div class="space-y-6">
    <form method="POST" action="{{ route('leads.update',$lead) }}" class="card p-6">
      @csrf @method('PUT')
      <h3 class="font-heading text-sm font-semibold uppercase tracking-wide text-amber-400 mb-4">Manage Lead</h3>
      <div class="space-y-4">
        <div>
          <label class="text-xs text-slate-400 uppercase">Status</label>
          <select name="status" class="inp mt-1" data-testid="lead-status-select">
            @foreach($statuses as $st)<option value="{{ $st }}" @selected($lead->status===$st)>{{ ucfirst($st) }}</option>@endforeach
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-400 uppercase">Assign to</label>
          <select name="assigned_to" class="inp mt-1" data-testid="lead-assign-select">
            <option value="">Unassigned</option>
            @foreach($agents as $a)<option value="{{ $a->id }}" @selected($lead->assigned_to==$a->id)>{{ $a->name }}</option>@endforeach
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-400 uppercase">Follow-up date</label>
          <input type="date" name="followup_at" value="{{ $lead->followup_at?->format('Y-m-d') }}" class="inp mt-1" data-testid="lead-followup">
        </div>
        <div>
          <label class="text-xs text-slate-400 uppercase">Notes</label>
          <textarea name="notes" rows="4" class="inp mt-1" data-testid="lead-notes" placeholder="Internal notes…">{{ $lead->notes }}</textarea>
        </div>
      </div>
      <button class="btn btn-primary w-full justify-center mt-5" data-testid="lead-save">Save Changes</button>
    </form>
  </div>
</div>
@endsection
