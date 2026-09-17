@extends('layouts.app')@section('title','Driver Earnings')@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><h1 class="text-3xl font-bold">Driver Earnings</h1><span class="mono text-xl font-bold text-emerald-400">{{ $m($total) }}</span></div>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>Driver</th><th>Period</th><th>Basis</th><th>Plays</th><th>Net</th><th>Status</th></tr></thead><tbody>
@forelse($rows as $e)<tr><td>{{ $e->driver?->name }}</td><td>{{ $e->period_start?->format('d M') }}–{{ $e->period_end?->format('d M') }}</td><td>{{ $e->calculation_basis }}</td><td>{{ $e->valid_plays }}</td><td class="mono">{{ $m($e->net_amount) }}</td><td><x-badge :status="$e->status"/></td></tr>@empty<tr><td colspan="6" class="text-center py-12 text-slate-500">No earnings</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $rows->links() }}</div>@endsection
