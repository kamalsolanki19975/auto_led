@extends('public.layout')
@section('meta_title','Developers & API — '.\App\Support\Brand::name())
@section('meta_description','REST APIs, device APIs, campaign & playback APIs, reporting APIs, webhooks, scoped API keys and authentication for the AutoAds advertising network.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'Developers / API',
  'title' => 'Build on the <span class="text-brand">AutoAds API</span>',
  'subtitle' => 'Integrate advertiser systems, players and reporting with a clean REST API, device endpoints and webhooks.',
])
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid lg:grid-cols-2 gap-10 items-start">
    <div>
      <div class="grid sm:grid-cols-2 gap-4">
        @foreach([
          ['key-round','Authentication','Token-based auth via /api/v1/auth/login, scoped API keys per application.'],
          ['smartphone','Device APIs','Pairing, heartbeat, configuration, content & playback events.'],
          ['megaphone','Campaign APIs','List and read campaigns, delivery and assignments.'],
          ['play-circle','Playback APIs','Ingest and query proof-of-play events.'],
          ['bar-chart-3','Reporting APIs','Pull network, delivery and finance data.'],
          ['webhook','Webhooks','Subscribe to campaign, device & payment events.'],
        ] as [$ic,$t,$d])
          <div class="card p-5 reveal">
            <span class="h-9 w-9 rounded-lg bg-brand/15 text-brand flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i></span>
            <h3 class="font-heading text-base font-semibold mt-3">{{ $t }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $d }}</p>
          </div>
        @endforeach
      </div>
      <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('login') }}" class="btn btn-primary" data-testid="developers-login-docs">Login to view interactive docs <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
      </div>
    </div>
    <div class="card p-0 overflow-hidden reveal">
      <div class="flex items-center gap-2 px-4 py-3 border-b border-white/10 bg-white/5">
        <span class="h-3 w-3 rounded-full bg-rose-500/70"></span><span class="h-3 w-3 rounded-full bg-amber-500/70"></span><span class="h-3 w-3 rounded-full bg-emerald-500/70"></span>
        <span class="ml-3 mono text-xs text-slate-400">POST /api/v1/device/heartbeat</span>
      </div>
      <pre class="mono text-[.78rem] leading-relaxed text-slate-300 p-4 overflow-x-auto"><span class="text-slate-500"># Report a device heartbeat</span>
curl -X POST {{ url('/api/v1/device/heartbeat') }} \
  -H <span class="text-emerald-400">"Authorization: Bearer &lt;device_token&gt;"</span> \
  -H <span class="text-emerald-400">"X-Device-Uuid: &lt;uuid&gt;"</span> \
  -H <span class="text-emerald-400">"Content-Type: application/json"</span> \
  -d '{
    <span class="text-brand">"app_version"</span>: "1.0.0",
    <span class="text-brand">"storage_free"</span>: 32.5,
    <span class="text-brand">"temperature"</span>: 38,
    <span class="text-brand">"network"</span>: "4g"
  }'

<span class="text-slate-500"># Response</span>
{ <span class="text-brand">"success"</span>: <span class="text-emerald-400">true</span>, <span class="text-brand">"message"</span>: <span class="text-emerald-400">"Heartbeat received"</span> }</pre>
    </div>
  </div>
</section>
@include('public.partials.cta', ['ctaTitle'=>'Ready to integrate?','ctaText'=>'Create an API application from your dashboard and generate scoped keys in seconds.'])
@endsection
