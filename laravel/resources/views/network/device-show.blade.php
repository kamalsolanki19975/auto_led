@extends('layouts.app')
@section('title',$model->code)
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<div class="mb-6"><a href="{{ route('devices.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-amber-400 transition"><i data-lucide="arrow-left" class="w-4 h-4"></i>Devices</a>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-2">
  <div><h1 class="font-heading text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">{{ $model->code }}</h1><p class="mono text-xs text-slate-500 mt-1">{{ $model->device_uuid }}</p></div>
  <div class="flex items-center gap-3">
    <span class="{{ $online?'text-emerald-400':'text-rose-400' }} font-semibold flex items-center gap-2 text-sm"><span class="h-2 w-2 rounded-full {{ $online?'bg-emerald-500':'bg-rose-500' }}"></span>{{ $online?'Online':'Offline' }}</span>
    <a href="{{ route('devices.edit',$model) }}" class="btn btn-primary" data-testid="edit-devices-detail"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a>
  </div>
</div></div>
<div class="flex flex-wrap gap-2 mb-6">
  <form action="{{ route('devices.command',$model) }}" method="POST" class="inline">@csrf<input type="hidden" name="command" value="restart"><button class="btn btn-sec"><i data-lucide="power" class="w-4 h-4"></i>Restart</button></form>
  <form action="{{ route('devices.command',$model) }}" method="POST" class="inline">@csrf<input type="hidden" name="command" value="sync"><button class="btn btn-sec">Sync</button></form>
  <form action="{{ route('devices.command',$model) }}" method="POST" class="inline">@csrf<input type="hidden" name="command" value="update_playlist"><button class="btn btn-sec">Update Playlist</button></form>
  <form action="{{ route('devices.token',$model) }}" method="POST" class="inline">@csrf<button class="btn btn-sec">Regenerate Token</button></form>
  <form action="{{ route('devices.setStatus',$model) }}" method="POST" class="inline">@csrf<input type="hidden" name="status" value="blocked"><button class="btn btn-danger">Block</button></form>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="card p-6 lg:col-span-1"><h3 class="font-semibold mb-3 text-sm uppercase">Device</h3><div class="space-y-2 text-sm">
    @foreach(['hardware_model'=>'Hardware','android_version'=>'Android','app_version'=>'App','ram'=>'RAM','storage'=>'Storage','temperature'=>'Temp (°C)'] as $k=>$l)<div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">{{ $l }}</span><span>{{ $model->$k ?? '—' }}</span></div>@endforeach
    <div class="flex justify-between border-b border-white/5 py-1.5"><span class="text-slate-400">Auto</span><span>{{ $model->auto?->registration_number ?? '—' }}</span></div>
    <div class="flex justify-between py-1.5"><span class="text-slate-400">SIM</span><span>{{ $model->sim?->mobile_number ?? '—' }}</span></div>
  </div>
  @if(count($pendingCommands))<div class="mt-4 text-xs text-amber-400">Pending commands: {{ count($pendingCommands) }} (awaiting device sync)</div>@endif
  </div>
  <div class="card p-6 lg:col-span-2"><h3 class="font-semibold mb-3 text-sm uppercase">Recent Heartbeats</h3>
    <table class="grid"><thead><tr><th>Time</th><th>App</th><th>Temp</th><th>Network</th><th>Campaign</th></tr></thead><tbody>
    @forelse($heartbeats as $h)<tr><td>{{ $h->created_at?->format('d M H:i') }}</td><td>{{ $h->app_version }}</td><td>{{ $h->temperature }}</td><td>{{ $h->network }}</td><td>{{ $h->current_campaign_id }}</td></tr>@empty<tr><td colspan="5" class="text-slate-500 py-3">No heartbeats yet</td></tr>@endforelse
    </tbody></table>
  </div>
</div>
@endsection
