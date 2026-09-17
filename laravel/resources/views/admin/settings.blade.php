@extends('layouts.app')@section('title','Settings')@section('content')
<h1 class="text-3xl font-bold mb-1">System Settings</h1>
<p class="text-slate-400 text-sm mb-6">Configure branding, website content, integrations and platform behaviour. Changes apply instantly across the app and public website.</p>

<div class="flex gap-2 mb-6 flex-wrap text-sm">
  @foreach($groups ?? ['company','branding','website','localization','seo','social','device','advertising','finance','email','sms','whatsapp','api'] as $g)
    <a href="?tab={{ $g }}" data-testid="settings-tab-{{ $g }}" class="px-3 py-1.5 rounded {{ $tab==$g?'bg-amber-500 text-[#0B0F17] font-semibold':'bg-white/5 text-slate-300 hover:bg-white/10' }}">{{ ucfirst($g) }}</a>
  @endforeach
</div>

<form method="POST" action="{{ route('settings.update',$tab) }}" enctype="multipart/form-data" class="card p-6 max-w-3xl">
  @csrf @method('PUT')

  @if($tab==='branding')
    <div class="mb-6">
      <label class="text-xs text-slate-400 uppercase">Logo</label>
      <div class="flex items-center gap-4 mt-2">
        <div class="h-16 w-40 rounded bg-[#0B0F17] border border-white/10 flex items-center justify-center overflow-hidden">
          @if(\App\Support\Brand::logo())
            <img src="{{ \App\Support\Brand::logo() }}" alt="Current logo" class="h-12 max-w-[150px] object-contain">
          @else
            <span class="text-slate-500 text-xs">No logo set</span>
          @endif
        </div>
        <div>
          <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="text-sm text-slate-300" data-testid="set-logo">
          <p class="text-xs text-slate-500 mt-1">Recommended: <span class="text-slate-300">240 × 64 px</span> (max height 64px), transparent <span class="text-slate-300">PNG or SVG</span>, up to 2 MB. Leave blank to keep the current logo.</p>
        </div>
      </div>
    </div>
  @endif

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach(($settings[$tab]??[]) as $key=>$val)
      @continue(str_contains($key,'path'))
      <div class="{{ (strlen((string)$val) > 70 || in_array($key,['hero_subheading','default_description','contact_address'])) ? 'md:col-span-2' : '' }}">
        <label class="text-xs text-slate-400 uppercase">{{ str_replace('_',' ',$key) }}</label>
        @if(strlen((string)$val) > 70 || in_array($key,['hero_subheading','default_description','contact_address']))
          <textarea name="{{ $key }}" rows="3" class="inp mt-1" data-testid="set-{{ $key }}">{{ $val }}</textarea>
        @else
          <input name="{{ $key }}" value="{{ $val }}" type="{{ str_contains($key,'password')?'password':'text' }}" class="inp mt-1" data-testid="set-{{ $key }}">
        @endif
      </div>
    @endforeach
  </div>
  <button class="btn btn-primary mt-6" data-testid="save-settings">Save {{ ucfirst($tab) }}</button>
</form>

@if($tab=='email')
<form method="POST" action="{{ route('settings.testEmail') }}" class="card p-6 max-w-3xl mt-4 flex gap-3 items-end">@csrf
  <div class="flex-1"><label class="text-xs text-slate-400 uppercase">Send test email to</label><input name="test_email" class="inp mt-1" placeholder="you@company.com" data-testid="set-test-email"></div>
  <button class="btn btn-sec" data-testid="send-test-email">Send Test</button>
</form>
@endif
@endsection
