@extends('layouts.app')@section('title','Settings')@section('content')
<h1 class="text-3xl font-bold mb-6">System Settings</h1>
<div class="flex gap-2 mb-6 flex-wrap text-sm">@foreach(['company','localization','device','advertising','finance','email','sms','whatsapp','api'] as $g)<a href="?tab={{ $g }}" class="px-3 py-1.5 rounded {{ $tab==$g?'bg-amber-500 text-[#0B0F17]':'bg-white/5 text-slate-300' }}">{{ ucfirst($g) }}</a>@endforeach</div>
<form method="POST" action="{{ route('settings.update',$tab) }}" class="card p-6 max-w-2xl">@csrf @method('PUT')
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
@foreach(($settings[$tab]??[]) as $key=>$val)
<div><label class="text-xs text-slate-400 uppercase">{{ str_replace('_',' ',$key) }}</label>
<input name="{{ $key }}" value="{{ $val }}" type="{{ str_contains($key,'password')?'password':'text' }}" class="inp mt-1" data-testid="set-{{ $key }}"></div>@endforeach
</div><button class="btn btn-primary mt-6" data-testid="save-settings">Save {{ ucfirst($tab) }}</button></form>
@if($tab=='email')<form method="POST" action="{{ route('settings.testEmail') }}" class="card p-6 max-w-2xl mt-4 flex gap-3 items-end">@csrf<div class="flex-1"><label class="text-xs text-slate-400 uppercase">Send test email to</label><input name="test_email" class="inp mt-1" placeholder="you@company.com"></div><button class="btn btn-sec">Send Test</button></form>@endif
@endsection
