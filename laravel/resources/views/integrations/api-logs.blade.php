@extends('layouts.app')
@section('title','API Logs')
@section('content')
<div class="mb-6"><a href="{{ route('api-apps.index') }}" class="text-sm text-slate-400">← API Applications</a>
<h1 class="text-3xl font-bold mt-1">API Request Logs</h1></div>
<div class="card overflow-hidden">
  <table class="grid"><thead><tr><th>Time</th><th>Method</th><th>Path</th><th>Status</th><th>Duration</th><th>IP</th><th>Request ID</th></tr></thead>
  <tbody>
  @forelse($rows as $l)
    <tr>
      <td class="text-xs text-slate-400">{{ $l->created_at?->format('d M H:i:s') }}</td>
      <td class="mono text-xs font-semibold {{ in_array($l->method,['POST','PUT','PATCH'])?'text-amber-400':'text-sky-400' }}">{{ $l->method }}</td>
      <td class="mono text-xs">{{ $l->path }}</td>
      <td><span class="mono text-xs {{ $l->status_code>=400?'text-rose-400':'text-emerald-400' }}">{{ $l->status_code }}</span></td>
      <td class="mono text-xs text-slate-400">{{ $l->duration_ms }}ms</td>
      <td class="mono text-xs text-slate-400">{{ $l->ip }}</td>
      <td class="mono text-[.65rem] text-slate-500">{{ $l->request_id }}</td>
    </tr>
  @empty
    <tr><td colspan="7" class="text-center py-12 text-slate-500">No API requests logged yet.</td></tr>
  @endforelse
  </tbody></table>
</div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
