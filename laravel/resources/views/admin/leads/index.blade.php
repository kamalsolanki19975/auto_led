@extends('layouts.app')
@section('title','Website Leads')
@section('content')
@php($statusColors = ['new'=>'sky','contacted'=>'amber','qualified'=>'violet','proposal'=>'blue','converted'=>'emerald','lost'=>'rose'])

<div class="flex items-start justify-between mb-2">
  <div>
    <h1 class="font-heading text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">Website Leads</h1>
    <p class="text-slate-400 text-sm mt-1">Enquiries captured from the public website contact form.</p>
  </div>
  <span class="pill bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2.5 py-1">{{ $total }} total</span>
</div>

{{-- module help --}}
<div class="card p-4 mb-6 border-l-2 border-brand" x-data="{open:false}">
  <button @click="open=!open" class="flex items-center gap-2 text-sm text-slate-300" data-testid="leads-help-toggle">
    <i data-lucide="info" class="w-4 h-4 text-brand"></i><span class="font-medium">About Website Leads</span>
    <i data-lucide="chevron-down" class="w-4 h-4" :class="open && 'rotate-180'"></i>
  </button>
  <div x-show="open" x-cloak class="mt-3 text-sm text-slate-400 grid md:grid-cols-3 gap-4">
    <div><span class="text-slate-200 font-medium">What is this?</span><p class="mt-1">Every contact-form submission on the marketing website becomes a lead here.</p></div>
    <div><span class="text-slate-200 font-medium">What can I do?</span><p class="mt-1">Track status, assign an owner, add notes and follow-up dates through the sales pipeline.</p></div>
    <div><span class="text-slate-200 font-medium">Why it matters</span><p class="mt-1">It connects website demand to your sales team so no advertiser or auto-owner enquiry is lost.</p></div>
  </div>
</div>

{{-- status chips --}}
<div class="flex flex-wrap gap-2 mb-4 text-sm">
  <a href="{{ route('leads.index') }}" class="px-3 py-1.5 rounded {{ !request('status') ? 'bg-amber-500 text-[#0B0F17] font-semibold' : 'bg-white/5 text-slate-300' }}" data-testid="lead-filter-all">All</a>
  @foreach($statuses as $st)
    <a href="{{ route('leads.index',['status'=>$st]) }}" class="px-3 py-1.5 rounded {{ request('status')===$st ? 'bg-amber-500 text-[#0B0F17] font-semibold' : 'bg-white/5 text-slate-300' }}" data-testid="lead-filter-{{ $st }}">
      {{ ucfirst($st) }} <span class="opacity-70">({{ $counts[$st] ?? 0 }})</span>
    </a>
  @endforeach
</div>

<div class="card p-4 mb-4">
  <form method="GET" class="flex flex-wrap gap-3 items-center">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <input name="q" value="{{ request('q') }}" placeholder="Search name, email, company, code…" class="inp max-w-xs" data-testid="search-leads">
    <select name="type" class="inp max-w-[180px]" data-testid="lead-type-filter">
      <option value="">All types</option>
      @foreach(['advertiser'=>'Advertiser','auto_owner'=>'Auto Owner','general'=>'General'] as $v=>$l)<option value="{{ $v }}" @selected(request('type')===$v)>{{ $l }}</option>@endforeach
    </select>
    <button class="btn btn-sec">Filter</button>
  </form>
</div>

<div class="card overflow-hidden">
  <table class="grid"><thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Contact</th><th>Status</th><th>Owner</th><th>Received</th></tr></thead>
  <tbody>
  @forelse($rows as $lead)
    <tr class="cursor-pointer" onclick="window.location='{{ route('leads.show',$lead) }}'" data-testid="row-leads-{{ $lead->id }}">
      <td class="mono text-xs text-brand">{{ $lead->code }}</td>
      <td class="font-semibold text-white">{{ $lead->name }}<div class="text-xs text-slate-500">{{ $lead->company ?? '—' }}</div></td>
      <td class="text-xs text-slate-300 capitalize">{{ str_replace('_',' ',$lead->type) }}</td>
      <td class="text-xs text-slate-300">{{ $lead->email }}<div class="text-slate-500">{{ $lead->phone ?? '' }}</div></td>
      <td>@php($c=$statusColors[$lead->status] ?? 'slate')<span class="inline-block rounded-full px-2.5 py-0.5 text-xs bg-{{ $c }}-500/15 text-{{ $c }}-400 border border-{{ $c }}-500/30 capitalize">{{ $lead->status }}</span></td>
      <td class="text-xs text-slate-300">{{ $lead->assignee?->name ?? '—' }}</td>
      <td class="text-xs text-slate-400">{{ $lead->created_at?->diffForHumans() }}</td>
    </tr>
  @empty
    <tr><td colspan="7" class="text-center py-12 text-slate-500"><i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><div>No leads yet. They will appear here when visitors submit the website contact form.</div></td></tr>
  @endforelse
  </tbody></table>
</div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
