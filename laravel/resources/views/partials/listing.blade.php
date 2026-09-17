@extends('layouts.app')
@section('title',$title??'List')
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><h1 class="text-3xl font-bold">@yield('h1')</h1>@yield('actions')</div>
@hasSection('cards')<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">@yield('cards')</div>@endif
<div class="card overflow-hidden">@yield('table')</div>
@isset($rows)<div class="mt-4">{{ $rows->links() }}</div>@endisset
@endsection
