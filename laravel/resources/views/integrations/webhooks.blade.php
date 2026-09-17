@extends('layouts.app')
@section('title','Webhooks')
@section('content')
<div class="flex items-center justify-between mb-6">
  <div><h1 class="text-3xl font-bold">Webhooks</h1><p class="text-slate-400 text-sm">Outbound event subscriptions</p></div>
  <button onclick="document.getElementById('new-hook').classList.toggle('hidden')" class="btn btn-primary" data-testid="new-webhook-btn"><i data-lucide="plus" class="w-4 h-4"></i>New Endpoint</button>
</div>

<div id="new-hook" class="card p-6 mb-6 hidden">
  <form method="POST" action="{{ route('webhooks.store') }}">
    @csrf
    <div><label class="text-xs text-slate-400 uppercase">Endpoint URL</label><input name="url" type="url" placeholder="https://example.com/hooks/autoads" class="inp mt-1" required data-testid="field-webhook-url"></div>
    <div class="mt-4">
      <label class="text-xs text-slate-400 uppercase">Events</label>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2">
        @foreach($events as $e)
          <label class="flex items-center gap-2 card px-3 py-2 cursor-pointer hover:bg-white/5 text-sm">
            <input type="checkbox" name="events[]" value="{{ $e }}" class="accent-amber-500" data-testid="event-{{ $e }}">
            <span class="mono text-xs">{{ $e }}</span>
          </label>
        @endforeach
      </div>
    </div>
    <div class="mt-4"><button class="btn btn-primary" data-testid="save-webhook">Register Endpoint</button></div>
  </form>
</div>

<div class="space-y-4">
@forelse($rows as $w)
  <div class="card p-5" data-testid="webhook-{{ $w->id }}">
    <div class="flex items-start justify-between">
      <div class="min-w-0">
        <div class="flex items-center gap-2"><i data-lucide="webhook" class="w-4 h-4 text-amber-400"></i><span class="mono text-sm truncate">{{ $w->url }}</span><x-badge :status="$w->status"/></div>
        <div class="flex flex-wrap gap-1 mt-2">
          @foreach($w->events ?? [] as $e)<span class="mono text-[.65rem] rounded bg-white/5 border border-white/10 px-1.5 py-0.5 text-slate-400">{{ $e }}</span>@endforeach
        </div>
        <div class="text-xs text-slate-500 mt-2">Secret: <span class="mono">{{ \Illuminate\Support\Str::limit($w->secret, 14) }}</span> · {{ $w->deliveries->count() }} deliveries</div>
      </div>
      <div class="flex gap-2 shrink-0">
        <form method="POST" action="{{ route('webhooks.test',$w) }}" class="inline">@csrf<button class="btn btn-sec text-sm" data-testid="test-webhook-{{ $w->id }}"><i data-lucide="send" class="w-4 h-4"></i>Test</button></form>
        <form method="POST" action="{{ route('webhooks.destroy',$w) }}" class="inline" onsubmit="return confirm('Delete this webhook?')">@csrf @method('DELETE')<button class="btn btn-danger text-sm" data-testid="delete-webhook-{{ $w->id }}"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
      </div>
    </div>
    @if($w->deliveries->count())
      <div class="mt-3 border-t border-white/10 pt-3">
        <table class="grid"><thead><tr><th>Event</th><th>Status</th><th>Response</th><th>Attempts</th><th>Time</th></tr></thead><tbody>
        @foreach($w->deliveries->take(5) as $d)
          <tr><td class="mono text-xs">{{ $d->event }}</td><td><x-badge :status="$d->status"/></td><td class="mono text-xs">{{ $d->response_code ?? '—' }}</td><td class="mono text-xs">{{ $d->attempts }}</td><td class="text-xs text-slate-400">{{ $d->created_at?->format('d M H:i') }}</td></tr>
        @endforeach
        </tbody></table>
      </div>
    @endif
  </div>
@empty
  <div class="card p-12 text-center text-slate-500"><i data-lucide="webhook" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><div>No webhook endpoints registered.</div></div>
@endforelse
</div>
@endsection
