@extends('layouts.app')
@section('title',$title)
@section('content')
<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div class="flex items-center gap-3">
    <h1 class="font-heading text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">{{ $title }}</h1>
    <span class="pill bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2.5 py-1">{{ number_format($rows->total()) }} records</span>
  </div>
  @if($canCreate)<a href="{{ route($routeBase.'.create') }}" class="btn btn-primary self-start sm:self-auto" data-testid="create-{{ $routeBase }}"><i data-lucide="plus" class="w-4 h-4"></i>New {{ $singular }}</a>@endif
</div>

@if(!empty($extra['cards']))
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
  @foreach($extra['cards'] as $label=>$val)
  <div class="card p-4">
    <span class="text-[.68rem] font-mono uppercase tracking-wider text-slate-400">{{ $label }}</span>
    <div class="mono text-2xl font-bold mt-1">{{ $val }}</div>
  </div>
  @endforeach
</div>
@endif

<!-- Filter bar -->
<div class="card p-4 mb-5">
  <form method="GET" class="flex flex-wrap gap-3 items-center">
    <div class="relative flex-1 min-w-[220px] max-w-sm">
      <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-500"></i>
      <input name="q" value="{{ request('q') }}" placeholder="Search {{ strtolower($title) }}…" class="inp pl-9" data-testid="search-{{ $routeBase }}">
    </div>
    @if(!empty($statuses))
    <select name="status" class="inp max-w-[200px]" onchange="this.form.submit()" data-testid="filter-{{ $routeBase }}">
      <option value="all">All statuses</option>
      @foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach
    </select>
    @endif
    <button class="btn btn-sec"><i data-lucide="sliders-horizontal" class="w-4 h-4"></i>Filter</button>
    @if(request('q') || (request('status') && request('status')!=='all'))
      <a href="{{ route($routeBase.'.index') }}" class="btn btn-ghost" data-testid="clear-{{ $routeBase }}"><i data-lucide="x" class="w-4 h-4"></i>Clear</a>
    @endif
  </form>
</div>

<!-- Table -->
<div class="card overflow-hidden">
  <div class="overflow-x-auto">
  <table class="grid">
    <thead><tr>@foreach($columns as $col)<th>{{ $col['label'] }}</th>@endforeach<th class="text-right">Actions</th></tr></thead>
    <tbody>
    @forelse($rows as $row)
      <tr data-testid="row-{{ $routeBase }}-{{ $row->id }}" class="group">
        @foreach($columns as $col)
          <td class="{{ ($col['strong']??false)?'font-semibold text-white':'text-slate-300' }}">
            @if($col['badge']??false)<x-badge :status="$row->{$col['key']}"/>
            @elseif(isset($col['render'])){!! ($col['raw']??false) ? $col['render']($row) : e($col['render']($row)) !!}
            @else{{ $row->{$col['key']} ?? '—' }}@endif
          </td>
        @endforeach
        <td class="text-right whitespace-nowrap">
          <div class="inline-flex items-center gap-1 justify-end">
            <a href="{{ route($routeBase.'.show',$row) }}" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition" title="View" data-testid="view-{{ $routeBase }}-{{ $row->id }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
            @if($canCreate)
            <a href="{{ route($routeBase.'.edit',$row) }}" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition" title="Edit" data-testid="edit-{{ $routeBase }}-{{ $row->id }}"><i data-lucide="pencil" class="w-4 h-4"></i></a>
            <form action="{{ route($routeBase.'.destroy',$row) }}" method="POST" class="inline" onsubmit="return confirm('Delete this {{ $singular }}?')">@csrf @method('DELETE')<button class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition" title="Delete" data-testid="delete-{{ $routeBase }}-{{ $row->id }}"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
            @endif
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="{{ count($columns)+1 }}">
        <div class="text-center py-16">
          <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10 text-amber-500 mb-4"><i data-lucide="inbox" class="w-7 h-7"></i></span>
          <div class="font-heading text-xl font-bold">No {{ strtolower($title) }} yet</div>
          <p class="text-slate-500 text-sm mt-1">Records will appear here once created.</p>
          @if($canCreate)<a href="{{ route($routeBase.'.create') }}" class="btn btn-primary mt-5"><i data-lucide="plus" class="w-4 h-4"></i>New {{ $singular }}</a>@endif
        </div>
      </td></tr>
    @endforelse
    </tbody>
  </table>
  </div>
</div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
