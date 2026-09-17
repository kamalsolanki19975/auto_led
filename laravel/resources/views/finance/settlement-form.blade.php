@extends('layouts.app')@section('title','New Settlement')@section('content')
<div class="mb-6"><a href="{{ route('settlements.index') }}" class="text-sm text-slate-400">← Settlements</a><h1 class="text-3xl font-bold mt-1">Generate Settlement</h1></div>
<form method="POST" action="{{ route('settlements.store') }}" class="card p-6 max-w-xl space-y-3">@csrf
<div><label class="text-xs text-slate-400 uppercase">Driver *</label><select name="driver_id" class="inp mt-1" required>@foreach($drivers as $id=>$n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
<div><label class="text-xs text-slate-400 uppercase">Period Start *</label><input type="date" name="period_start" value="{{ date('Y-m-01') }}" class="inp mt-1" required></div>
<div><label class="text-xs text-slate-400 uppercase">Period End *</label><input type="date" name="period_end" value="{{ date('Y-m-d') }}" class="inp mt-1" required></div>
<button class="btn btn-primary">Calculate & Create</button></form>@endsection
