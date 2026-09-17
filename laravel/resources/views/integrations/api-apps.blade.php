@extends('layouts.app')
@section('title','API Applications')
@section('content')
<div class="flex items-center justify-between mb-6">
  <div><h1 class="text-3xl font-bold">API Applications</h1><p class="text-slate-400 text-sm">Programmatic access & device integrations</p></div>
  <div class="flex gap-2">
    <a href="/api/docs" target="_blank" class="btn btn-sec" data-testid="api-docs-link"><i data-lucide="book-open" class="w-4 h-4"></i>API Docs</a>
    <a href="{{ route('api-logs.index') }}" class="btn btn-sec"><i data-lucide="scroll-text" class="w-4 h-4"></i>API Logs</a>
    <button onclick="document.getElementById('new-app').classList.toggle('hidden')" class="btn btn-primary" data-testid="new-api-app-btn"><i data-lucide="plus" class="w-4 h-4"></i>New Application</button>
  </div>
</div>

<div id="new-app" class="card p-6 mb-6 hidden">
  <form method="POST" action="{{ route('api-apps.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
    @csrf
    <div><label class="text-xs text-slate-400 uppercase">Name</label><input name="name" class="inp mt-1" required data-testid="field-app-name"></div>
    <div><label class="text-xs text-slate-400 uppercase">Description</label><input name="description" class="inp mt-1" data-testid="field-app-desc"></div>
    <div class="md:col-span-2"><button class="btn btn-primary" data-testid="save-api-app">Create Application</button></div>
  </form>
</div>

<div class="space-y-4">
@forelse($rows as $app)
  <div class="card p-5" data-testid="api-app-{{ $app->id }}">
    <div class="flex items-start justify-between">
      <div>
        <div class="flex items-center gap-2"><h3 class="text-lg font-semibold">{{ $app->name }}</h3><x-badge :status="$app->status"/></div>
        <p class="text-sm text-slate-400 mt-0.5">{{ $app->description ?? 'No description' }}</p>
      </div>
      <form method="POST" action="{{ route('api-apps.keys',$app) }}" class="flex items-end gap-2">
        @csrf
        <input name="name" placeholder="Key label" class="inp text-sm" style="width:150px">
        <button class="btn btn-sec text-sm" data-testid="gen-key-{{ $app->id }}"><i data-lucide="key" class="w-4 h-4"></i>Generate Key</button>
      </form>
    </div>
    <div class="mt-4 border-t border-white/10 pt-3">
      <table class="grid"><thead><tr><th>Prefix</th><th>Label</th><th>Scopes</th><th>Status</th><th>Last Used</th><th class="text-right">Action</th></tr></thead>
      <tbody>
      @forelse($app->keys as $k)
        <tr>
          <td class="mono text-xs">{{ $k->prefix }}…</td>
          <td>{{ $k->name }}</td>
          <td class="text-xs text-slate-400">{{ implode(', ', $k->scopes ?? []) }}</td>
          <td><x-badge :status="$k->status"/></td>
          <td class="text-xs text-slate-400">{{ $k->last_used_at?->diffForHumans() ?? 'never' }}</td>
          <td class="text-right">
            @if($k->status==='active')<form method="POST" action="{{ route('api-apps.revoke',$k) }}" class="inline">@csrf<button class="text-rose-400 text-xs" data-testid="revoke-key-{{ $k->id }}">Revoke</button></form>@else<span class="text-slate-500 text-xs">revoked</span>@endif
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center py-6 text-slate-500 text-sm">No keys generated yet.</td></tr>
      @endforelse
      </tbody></table>
    </div>
  </div>
@empty
  <div class="card p-12 text-center text-slate-500"><i data-lucide="plug" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><div>No API applications yet.</div></div>
@endforelse
</div>
@endsection
