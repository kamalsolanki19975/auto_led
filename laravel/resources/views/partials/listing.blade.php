@extends('layouts.app')
@section('title',$title??'List')
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <h1 class="font-heading text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">@yield('h1')</h1>
  <div class="flex items-center gap-2">@yield('actions')</div>
</div>
@hasSection('cards')<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">@yield('cards')</div>@endif
<div class="card overflow-hidden"><div class="overflow-x-auto">@yield('table')</div></div>
@isset($rows)<div class="mt-4">{{ $rows->links() }}</div>@endisset
@endsection
