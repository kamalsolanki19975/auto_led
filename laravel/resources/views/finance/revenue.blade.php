@extends('layouts.app')@section('title','Revenue')@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><div><h1 class="text-3xl font-bold">Revenue</h1><p class="text-slate-400 text-sm">Total {{ $m($total) }} · This month {{ $m($monthly) }}</p></div></div>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>Code</th><th>Source</th><th>Campaign</th><th>Amount</th><th>Date</th></tr></thead><tbody>
@forelse($rows as $r)<tr><td class="mono">{{ $r->code }}</td><td>{{ $r->source }}</td><td>{{ $r->campaign?->name ?? '—' }}</td><td class="mono text-emerald-400">{{ $m($r->amount) }}</td><td>{{ $r->date?->format('d M Y') }}</td></tr>@empty<tr><td colspan="5" class="text-center py-12 text-slate-500">No revenue</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $rows->links() }}</div>@endsection
