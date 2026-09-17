@extends('public.layout')
@section('meta_title','Technology — '.\App\Support\Brand::name())
@section('meta_description','Digital signage, 4G connectivity, Android media players, offline playback, device monitoring, proof-of-play, analytics and APIs — the technology behind the network.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'Technology 🛰️',
  'title' => 'Engineered for the <span class="text-brand">real world</span>',
  'subtitle' => 'A resilient, offline-first player network with remote monitoring and independent playback verification.',
  'image' => $img['tech_screen'],
])
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach([
      ['monitor','Digital Signage','Bright, Full-HD passenger-facing displays.'],
      ['wifi','4G Connectivity','Always-connected players with SIM management.'],
      ['smartphone','Android Player','Purpose-built media playback app.'],
      ['download-cloud','Offline Playback','Cached content keeps playing without signal.'],
      ['activity','Device Monitoring','Heartbeats for health, storage & temperature.'],
      ['shield-check','Proof of Play','Independent, timestamped play verification.'],
      ['bar-chart-3','Campaign Analytics','Delivery, runtime and reach in real time.'],
      ['code-2','APIs','REST + device APIs and webhooks for integration.'],
      ['refresh-cw','Real-time Sync','Content and events sync automatically.'],
    ] as $i => [$ic,$t,$d])
      <div class="card card-hover p-6 reveal" style="transition-delay: {{ ($i%3)*70 }}ms">
        <span class="h-10 w-10 rounded-lg bg-sky-500/15 text-sky-400 flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-5 h-5"></i></span>
        <h3 class="font-heading text-lg font-semibold mt-4">{{ $t }}</h3>
        <p class="text-sm text-slate-400 mt-2">{{ $d }}</p>
      </div>
    @endforeach
  </div>
</section>
<section class="border-b border-white/10 bg-surface">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
    <div class="eyebrow mb-3">Developer-friendly</div>
    <h2 class="font-heading text-2xl font-bold">Integrate with our REST & Device APIs</h2>
    <p class="text-slate-400 mt-3 max-w-2xl mx-auto">Authenticate, pull campaigns, push playback events and subscribe to webhooks.</p>
    <a href="{{ route('site.developers') }}" class="btn btn-primary mt-6" data-testid="tech-developers-link">Explore the API <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
  </div>
</section>
@include('public.partials.cta')
@endsection
