@extends('layouts.app')@section('title','Reports')@section('content')
<h1 class="text-3xl font-bold mb-6">Reports Hub</h1>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
@foreach(['campaign'=>'Campaign Performance','runtime'=>'Runtime','sim'=>'SIM','device'=>'Device Health','revenue'=>'Revenue','expense'=>'Expense','auto'=>'Auto Master','driver'=>'Driver Earnings'] as $k=>$l)
<a href="{{ route('reports.show',$k) }}" class="card p-6 hover:border-amber-500/40"><i data-lucide="bar-chart-3" class="w-6 h-6 text-amber-400 mb-2"></i><div class="font-semibold">{{ $l }}</div></a>@endforeach
</div>@endsection
