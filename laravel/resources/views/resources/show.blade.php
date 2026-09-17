@extends('layouts.app')
@section('title',$singular.' Detail')
@section('content')
<div class="flex items-center justify-between mb-6">
  <div><a href="{{ route($routeBase.'.index') }}" class="text-sm text-slate-400">← {{ $title }}</a>
  <h1 class="text-3xl font-bold mt-1">{{ $model->code ?? $model->name ?? $model->title ?? ('#'.$model->id) }}</h1></div>
  <a href="{{ route($routeBase.'.edit',$model) }}" class="btn btn-sec"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a>
</div>
<div class="card p-6 max-w-4xl">
  <h3 class="font-semibold mb-4 text-slate-200 uppercase text-sm tracking-wide">Details</h3>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
    @foreach($details as $label=>$value)
      <div class="flex justify-between border-b border-white/5 py-2"><span class="text-slate-400">{{ $label }}</span>
        <span class="text-slate-100 font-medium">{{ is_bool($value)?($value?'Yes':'No') : ($value instanceof \Carbon\Carbon ? $value->format('d M Y') : ($value ?: '—')) }}</span></div>
    @endforeach
  </div>
</div>
@if(isset($autos))<div class="card p-6 mt-6"><h3 class="font-semibold mb-3">Autos</h3><table class="grid"><thead><tr><th>Reg</th><th>Driver</th><th>Status</th></tr></thead><tbody>@foreach($autos as $a)<tr><td><a class="text-amber-400" href="{{ route('autos.show',$a) }}">{{ $a->registration_number }}</a></td><td>{{ $a->primaryDriver?->name }}</td><td><x-badge :status="$a->status"/></td></tr>@endforeach</tbody></table></div>@endif
@if(isset($items) && $items->count())<div class="card p-6 mt-6"><h3 class="font-semibold mb-3">Playlist Items</h3><table class="grid"><thead><tr><th>Order</th><th>Advertisement</th><th>Duration</th></tr></thead><tbody>@foreach($items as $it)<tr><td>{{ $it->order }}</td><td>{{ $it->advertisement?->title }}</td><td>{{ $it->duration }}s</td></tr>@endforeach</tbody></table></div>@endif
@endsection
