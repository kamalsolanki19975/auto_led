@extends('layouts.app')@section('title','Email Logs')@section('content')
<h1 class="text-3xl font-bold mb-6">Email Logs</h1>
<div class="card p-4 mb-4"><form method="GET" class="flex gap-3"><select name="status" class="inp max-w-xs" onchange="this.form.submit()"><option value="all">All</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></form></div>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>Recipient</th><th>Subject</th><th>Event</th><th>Status</th><th>Sent</th></tr></thead><tbody>
@forelse($rows as $e)<tr><td>{{ $e->recipient }}</td><td>{{ \Illuminate\Support\Str::limit($e->subject,40) }}</td><td class="mono text-xs">{{ $e->event }}</td><td><x-badge :status="$e->status"/></td><td>{{ $e->sent_at?->format('d M H:i') ?? '—' }}</td></tr>@empty<tr><td colspan="5" class="text-center py-12 text-slate-500">No emails</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $rows->links() }}</div>@endsection
