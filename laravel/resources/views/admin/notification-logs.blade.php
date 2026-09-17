@extends('layouts.app')@section('title','Notification Logs')@section('content')
<h1 class="text-3xl font-bold mb-6">Notification Logs</h1>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>User</th><th>Event</th><th>Channel</th><th>Status</th><th>Time</th></tr></thead><tbody>
@forelse($rows as $l)<tr><td>{{ $l->user?->name }}</td><td class="mono text-xs">{{ $l->event }}</td><td>{{ $l->channel }}</td><td><x-badge :status="$l->status"/></td><td>{{ $l->created_at->format('d M H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center py-12 text-slate-500">No logs</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $rows->links() }}</div>@endsection
