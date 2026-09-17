@extends('layouts.app')@section('title','Audit Logs')@section('content')
<h1 class="text-3xl font-bold mb-6">Audit Logs</h1>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead><tbody>
@forelse($rows as $a)<tr><td>{{ $a->created_at->format('d M H:i') }}</td><td>{{ $a->user?->name ?? 'system' }}</td><td class="mono">{{ $a->action }}</td><td>{{ $a->entity_type }} #{{ $a->entity_id }}</td><td class="mono text-xs">{{ $a->ip }}</td></tr>@empty<tr><td colspan="5" class="text-center py-12 text-slate-500">No logs</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $rows->links() }}</div>@endsection
