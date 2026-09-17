@extends('layouts.app')
@section('title',$title)
@section('content')
<div class="flex items-center justify-between mb-6">
  <div><h1 class="text-3xl font-bold">{{ $title }}</h1><p class="text-slate-400 text-sm">{{ $rows->total() }} records</p></div>
  @if($canCreate)<a href="{{ route($routeBase.'.create') }}" class="btn btn-primary" data-testid="create-{{ $routeBase }}"><i data-lucide="plus" class="w-4 h-4"></i>New {{ $singular }}</a>@endif
</div>
@if(!empty($extra['cards']))
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
  @foreach($extra['cards'] as $label=>$val)<div class="card p-4"><span class="text-xs text-slate-400 uppercase">{{ $label }}</span><div class="mono text-2xl font-bold mt-1">{{ $val }}</div></div>@endforeach
</div>
@endif
<div class="card p-4 mb-4"><form method="GET" class="flex flex-wrap gap-3 items-center">
  <input name="q" value="{{ request('q') }}" placeholder="Search {{ strtolower($title) }}…" class="inp max-w-xs" data-testid="search-{{ $routeBase }}">
  @if(!empty($statuses))<select name="status" class="inp max-w-xs" onchange="this.form.submit()" data-testid="filter-{{ $routeBase }}">
    <option value="all">All statuses</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>@endif
  <button class="btn btn-sec">Filter</button>
</form></div>
<div class="card overflow-hidden">
  <table class="grid"><thead><tr>@foreach($columns as $col)<th>{{ $col['label'] }}</th>@endforeach<th class="text-right">Actions</th></tr></thead>
  <tbody>
    @forelse($rows as $row)
      <tr data-testid="row-{{ $routeBase }}-{{ $row->id }}">
        @foreach($columns as $col)
          <td class="{{ ($col['strong']??false)?'font-semibold text-white':'text-slate-300' }}">
            @if($col['badge']??false)<x-badge :status="$row->{$col['key']}"/>
            @elseif(isset($col['render'])){!! ($col['raw']??false) ? $col['render']($row) : e($col['render']($row)) !!}
            @else{{ $row->{$col['key']} ?? '—' }}@endif
          </td>
        @endforeach
        <td class="text-right whitespace-nowrap">
          <a href="{{ route($routeBase.'.show',$row) }}" class="text-amber-400 text-xs mr-2" data-testid="view-{{ $routeBase }}-{{ $row->id }}">View</a>
          @if($canCreate)<a href="{{ route($routeBase.'.edit',$row) }}" class="text-slate-400 text-xs mr-2">Edit</a>
          <form action="{{ route($routeBase.'.destroy',$row) }}" method="POST" class="inline" onsubmit="return confirm('Delete this {{ $singular }}?')">@csrf @method('DELETE')<button class="text-rose-400 text-xs">Delete</button></form>@endif
        </td>
      </tr>
    @empty
      <tr><td colspan="{{ count($columns)+1 }}" class="text-center py-12 text-slate-500"><i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><div>No {{ strtolower($title) }} yet.</div></td></tr>
    @endforelse
  </tbody></table>
</div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
