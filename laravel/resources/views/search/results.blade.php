@extends('layouts.app')
@section('title','Search')
@section('content')
<h1 class="text-3xl font-bold mb-1">Search results</h1>
<p class="text-slate-400 text-sm mb-6">{{ count($results) }} results for “{{ $q }}”</p>
<div class="card divide-y divide-white/5">
  @forelse($results as $r)
    <a href="{{ $r['url'] }}" class="flex items-center justify-between px-5 py-3 hover:bg-white/5">
      <div><span class="text-xs font-mono text-amber-500 uppercase">{{ $r['type'] }}</span><div class="font-medium">{{ $r['label'] }}</div></div>
      <span class="text-sm text-slate-500 mono">{{ $r['sub'] }}</span>
    </a>
  @empty<p class="px-5 py-12 text-center text-slate-500">No matches found.</p>@endforelse
</div>
@endsection
