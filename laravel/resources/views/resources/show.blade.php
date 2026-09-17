@extends('layouts.app')
@section('title',$singular.' Detail')
@section('content')
<div class="mb-6">
  <a href="{{ route($routeBase.'.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-amber-400 transition"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $title }}</a>
</div>

<!-- Hero -->
<div class="card p-6 mb-6 relative overflow-hidden">
  <div class="absolute inset-0 opacity-60" style="background:radial-gradient(60% 120% at 100% 0%,rgba(245,158,11,.10),transparent 60%)"></div>
  <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-4 min-w-0">
      <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/15 text-amber-400 shrink-0"><i data-lucide="file-text" class="w-6 h-6"></i></span>
      <div class="min-w-0">
        <div class="text-[.62rem] font-mono uppercase tracking-widest text-slate-500">{{ $singular }}</div>
        <h1 class="font-heading text-2xl sm:text-3xl font-extrabold tracking-tight truncate">{{ $model->code ?? $model->name ?? $model->title ?? ('#'.$model->id) }}</h1>
        @if(isset($model->status))<div class="mt-1.5"><x-badge :status="$model->status"/></div>@endif
      </div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <a href="{{ route($routeBase.'.edit',$model) }}" class="btn btn-primary" data-testid="edit-{{ $routeBase }}-detail"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a>
    </div>
  </div>
</div>

<div class="card p-6 max-w-4xl">
  <div class="flex items-center gap-2 mb-4"><i data-lucide="list" class="w-4 h-4 text-amber-500"></i><h3 class="font-heading text-lg font-bold uppercase tracking-tight">Details</h3></div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1 text-sm">
    @foreach($details as $label=>$value)
      <div class="flex justify-between gap-4 border-b border-white/5 py-2.5">
        <span class="text-slate-400">{{ $label }}</span>
        <span class="text-slate-100 font-medium text-right">{{ is_bool($value)?($value?'Yes':'No') : ($value instanceof \Carbon\Carbon ? $value->format('d M Y') : ($value ?: '—')) }}</span>
      </div>
    @endforeach
  </div>
</div>

@if(isset($autos))<div class="card p-6 mt-6"><h3 class="font-heading text-lg font-bold uppercase tracking-tight mb-3">Autos</h3><div class="overflow-x-auto"><table class="grid"><thead><tr><th>Reg</th><th>Driver</th><th>Status</th></tr></thead><tbody>@foreach($autos as $a)<tr><td><a class="text-amber-400 font-medium" href="{{ route('autos.show',$a) }}">{{ $a->registration_number }}</a></td><td class="text-slate-300">{{ $a->primaryDriver?->name }}</td><td><x-badge :status="$a->status"/></td></tr>@endforeach</tbody></table></div></div>@endif
@if(isset($items) && $items->count())<div class="card p-6 mt-6"><h3 class="font-heading text-lg font-bold uppercase tracking-tight mb-3">Playlist Items</h3><div class="overflow-x-auto"><table class="grid"><thead><tr><th>Order</th><th>Advertisement</th><th>Duration</th></tr></thead><tbody>@foreach($items as $it)<tr><td class="mono">{{ $it->order }}</td><td class="text-slate-300">{{ $it->advertisement?->title }}</td><td class="mono">{{ $it->duration }}s</td></tr>@endforeach</tbody></table></div></div>@endif
@endsection
