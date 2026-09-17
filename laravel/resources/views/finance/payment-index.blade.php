@extends('layouts.app')@section('title','Payments')@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="flex items-center justify-between mb-6"><div><h1 class="text-3xl font-bold">Payments</h1><p class="text-slate-400 text-sm">Received {{ $m($received) }} · Made {{ $m($made) }}</p></div><a href="{{ route('payments.create') }}" class="btn btn-primary">New Payment</a></div>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>Code</th><th>Type</th><th>Party</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead><tbody>
@forelse($rows as $p)<tr><td class="mono">{{ $p->code }}</td><td><x-badge :status="$p->type"/></td><td>{{ $p->advertiser?->company_name ?? $p->vendor?->name ?? '—' }}</td><td class="mono">{{ $m($p->amount) }}</td><td>{{ $p->method }}</td><td>{{ $p->paid_at?->format('d M Y') }}</td></tr>@empty<tr><td colspan="6" class="text-center py-12 text-slate-500">No payments</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $rows->links() }}</div>@endsection
