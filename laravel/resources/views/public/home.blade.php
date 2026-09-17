@extends('public.layout')
@section('meta_title', \App\Support\Brand::name().' — Smart DOOH Advertising on Auto Screens')
@section('meta_description', $site['hero_subheading'] ?? 'A smart digital advertising network connecting brands with passenger-facing screens installed across autos.')
@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden border-b border-white/10">
  <div class="absolute inset-0 grid-bg opacity-60"></div>
  <div class="absolute -top-24 left-1/3 h-96 w-96 rounded-full amber-glow"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 grid lg:grid-cols-2 gap-12 items-center">
    <div class="reveal">
      <div class="eyebrow mb-4">🚕 Digital Out-of-Home · On the Move</div>
      <h1 class="font-heading text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight uppercase leading-[0.95]">
        {{ $site['hero_headline'] ?? 'Turn Every Auto Ride Into an Advertising Opportunity' }}
      </h1>
      <p class="mt-6 text-lg text-slate-300 leading-relaxed max-w-xl">
        {{ $site['hero_subheading'] ?? 'A smart digital advertising network connecting brands with passenger-facing screens installed across autos — with verified proof-of-play and real-time analytics.' }}
      </p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('site.contact', ['type'=>'advertiser']) }}" class="btn btn-primary" data-testid="hero-advertise-btn">Advertise With Us <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        <a href="{{ route('site.contact', ['type'=>'auto_owner']) }}" class="btn btn-ghost" data-testid="hero-join-network-btn">Join Our Auto Network</a>
      </div>
      <div class="mt-8 flex items-center gap-6 text-sm text-slate-400">
        <span class="flex items-center gap-2"><i data-lucide="shield-check" class="w-4 h-4 text-brand"></i>Verified Proof-of-Play</span>
        <span class="flex items-center gap-2"><i data-lucide="wifi" class="w-4 h-4 text-brand"></i>Offline-first 4G Players</span>
      </div>
    </div>

    {{-- Hero visual: auto + digital screen HUD --}}
    <div class="reveal">
      <div class="relative rounded-2xl overflow-hidden border border-white/10 shadow-2xl">
        <img src="{{ $img['hero_auto'] }}" alt="Passenger auto-rickshaw with a digital advertising screen in city traffic at dusk" class="w-full h-[420px] object-cover">
        <div class="absolute inset-0 bg-gradient-to-tr from-ink via-ink/40 to-transparent"></div>
        {{-- HUD chips --}}
        <div class="absolute top-4 left-4 flex flex-col gap-2">
          <span class="mono text-[.7rem] px-2.5 py-1 rounded-md bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>ONLINE · 1080p 60FPS</span>
          <span class="mono text-[.7rem] px-2.5 py-1 rounded-md bg-black/50 border border-white/15 text-slate-200">📍 12.9716°N, 77.5946°E</span>
        </div>
        <div class="absolute bottom-4 left-4 right-4">
          <div class="glass rounded-xl border border-white/10 p-3 flex items-center justify-between">
            <div>
              <div class="text-[.65rem] uppercase tracking-widest text-slate-400 mono">Impressions today</div>
              <div class="font-heading text-2xl font-bold text-white"><span data-count="1420">0</span></div>
            </div>
            <div class="text-right">
              <div class="text-[.65rem] uppercase tracking-widest text-slate-400 mono">Proof of Play</div>
              <div class="text-emerald-400 text-sm font-semibold flex items-center gap-1"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>Syncing</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- STAT BAR --}}
<section class="border-b border-white/10 bg-surface">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-2 md:grid-cols-4 gap-6">
    @foreach([
      ['🚗','Active Autos', $stats['autos_active']],
      ['📺','Screens Online', $stats['screens_online']],
      ['🏙️','Coverage Cities', $stats['cities_total']],
      ['📢','Active Campaigns', $stats['campaigns_active']],
    ] as [$emoji,$label,$val])
      <div class="text-center reveal">
        <div class="text-2xl mb-1">{{ $emoji }}</div>
        <div class="font-heading text-4xl font-extrabold text-white" data-count="{{ $val }}">0</div>
        <div class="text-xs uppercase tracking-widest text-slate-400 mono mt-1">{{ $label }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- HOW IT WORKS --}}
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="text-center max-w-2xl mx-auto reveal">
      <div class="eyebrow mb-3">How It Works</div>
      <h2 class="font-heading text-3xl sm:text-4xl font-bold uppercase tracking-tight">From brand brief to verified playback</h2>
      <p class="mt-4 text-slate-400">A closed loop that connects advertiser spend to real, measured screen delivery on the road.</p>
    </div>
    <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @foreach([
        ['megaphone','Create Campaign','Advertisers define budget, creative, target cities & schedule.'],
        ['crosshair','Smart Targeting','Campaigns are matched to autos by city, area & route.'],
        ['cloud-download','Cloud Sync','Content syncs to 4G Android players with offline caching.'],
        ['shield-check','Proof of Play','Every play is logged and validated into real analytics.'],
      ] as $i => [$icon,$t,$d])
        <div class="card card-hover p-6 reveal" style="transition-delay: {{ $i*80 }}ms">
          <div class="flex items-center justify-between mb-4">
            <span class="h-11 w-11 rounded-xl bg-brand/15 text-brand flex items-center justify-center"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></span>
            <span class="mono text-2xl font-bold text-white/10">0{{ $i+1 }}</span>
          </div>
          <h3 class="font-heading text-xl font-semibold">{{ $t }}</h3>
          <p class="text-sm text-slate-400 mt-2">{{ $d }}</p>
        </div>
      @endforeach
    </div>
    <div class="text-center mt-10"><a href="{{ route('site.how') }}" class="btn btn-ghost" data-testid="home-how-link">See the full flow <i data-lucide="arrow-right" class="w-4 h-4"></i></a></div>
  </div>
</section>

{{-- TWO AUDIENCES --}}
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 grid lg:grid-cols-2 gap-8">
    <div class="card card-hover overflow-hidden reveal">
      <img src="{{ $img['billboard'] }}" alt="Digital advertising screens in the city" class="w-full h-52 object-cover">
      <div class="p-8">
        <div class="eyebrow mb-2">For Advertisers 📢</div>
        <h3 class="font-heading text-2xl font-bold">Reach commuters where attention lives</h3>
        <ul class="mt-4 space-y-2 text-sm text-slate-300">
          <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5"></i>Geo-targeted campaigns by city, area & route</li>
          <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5"></i>Real-time monitoring & proof-of-play</li>
          <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5"></i>Transparent delivery & campaign analytics</li>
        </ul>
        <a href="{{ route('site.advertisers') }}" class="btn btn-primary mt-6" data-testid="home-advertisers-link">Start Advertising <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
      </div>
    </div>
    <div class="card card-hover overflow-hidden reveal">
      <img src="{{ $img['auto_motion'] }}" alt="Auto-rickshaw on the move" class="w-full h-52 object-cover">
      <div class="p-8">
        <div class="eyebrow mb-2">For Auto Owners & Drivers 🚗</div>
        <h3 class="font-heading text-2xl font-bold">Earn from a screen — at zero cost</h3>
        <ul class="mt-4 space-y-2 text-sm text-slate-300">
          <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5"></i>Free screen & device installation</li>
          <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5"></i>Earn on verified advertising runtime</li>
          <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5"></i>Transparent earnings & settlements</li>
        </ul>
        <a href="{{ route('site.owners') }}" class="btn btn-ghost mt-6" data-testid="home-owners-link">Join Our Auto Network</a>
      </div>
    </div>
  </div>
</section>

{{-- SOLUTIONS --}}
<section class="border-b border-white/10 bg-surface">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="text-center max-w-2xl mx-auto reveal">
      <div class="eyebrow mb-3">Advertising Solutions</div>
      <h2 class="font-heading text-3xl sm:text-4xl font-bold uppercase tracking-tight">Built for every campaign goal</h2>
    </div>
    <div class="mt-10 flex flex-wrap justify-center gap-3 reveal">
      @foreach(['Local Advertising 📍','Brand Awareness ✨','Retail Promotion 🛍️','Restaurant 🍔','Education 🎓','Automotive 🚙','FMCG 🧴','Events 🎫','Offers 🏷️','Product Launches 🚀','Hyperlocal 🎯'] as $sol)
        <span class="px-4 py-2 rounded-full border border-white/10 bg-white/5 text-sm text-slate-200 hover:border-brand/50 hover:text-white transition">{{ $sol }}</span>
      @endforeach
    </div>
    <div class="text-center mt-8"><a href="{{ route('site.solutions') }}" class="btn btn-ghost" data-testid="home-solutions-link">Explore solutions <i data-lucide="arrow-right" class="w-4 h-4"></i></a></div>
  </div>
</section>

{{-- TECHNOLOGY / ANALYTICS bento --}}
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 grid lg:grid-cols-3 gap-6">
    <div class="card p-8 lg:col-span-2 reveal relative overflow-hidden">
      <div class="eyebrow mb-3">Technology 🛰️</div>
      <h3 class="font-heading text-2xl font-bold">A resilient, offline-first player network</h3>
      <div class="mt-6 grid sm:grid-cols-2 gap-4">
        @foreach([['monitor-smartphone','Android Media Player'],['wifi','4G Connectivity'],['download-cloud','Offline Playback'],['activity','Device Monitoring'],['shield-check','Proof of Play'],['refresh-cw','Real-time Sync']] as [$ic,$t])
          <div class="flex items-center gap-3 rounded-xl bg-white/5 border border-white/5 p-3">
            <span class="h-9 w-9 rounded-lg bg-brand/15 text-brand flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i></span>
            <span class="text-sm text-slate-200">{{ $t }}</span>
          </div>
        @endforeach
      </div>
      <a href="{{ route('site.technology') }}" class="btn btn-ghost mt-6" data-testid="home-technology-link">How the tech works <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
    </div>
    <div class="card p-8 reveal relative overflow-hidden">
      <div class="absolute inset-0 amber-glow opacity-40"></div>
      <div class="relative">
        <div class="eyebrow mb-3">Analytics 📈</div>
        <h3 class="font-heading text-2xl font-bold">Every rupee, measured</h3>
        <div class="mt-6 space-y-4">
          <div>
            <div class="flex justify-between text-sm mb-1"><span class="text-slate-300">Valid Plays</span><span class="mono text-white"><span data-count="{{ $stats['plays_valid'] }}">0</span></span></div>
            <div class="h-2 rounded-full bg-white/10 overflow-hidden"><div class="h-full bg-brand" style="width: {{ $stats['plays_total'] ? min(100, round($stats['plays_valid']/$stats['plays_total']*100)) : 0 }}%"></div></div>
          </div>
          <div class="grid grid-cols-2 gap-4 pt-2">
            <div class="rounded-xl bg-white/5 p-4"><div class="mono text-2xl font-bold text-white" data-count="{{ $stats['plays_total'] }}">0</div><div class="text-xs text-slate-400 mt-1">Total Plays</div></div>
            <div class="rounded-xl bg-white/5 p-4"><div class="mono text-2xl font-bold text-white" data-count="{{ $stats['devices_online'] }}">0</div><div class="text-xs text-slate-400 mt-1">Devices Online</div></div>
          </div>
        </div>
        <a href="{{ route('site.analytics') }}" class="btn btn-ghost mt-6" data-testid="home-analytics-link">Explore analytics</a>
      </div>
    </div>
  </div>
</section>

{{-- PRICING PREVIEW --}}
@if($plans->count())
<section class="border-b border-white/10 bg-surface">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="text-center max-w-2xl mx-auto reveal">
      <div class="eyebrow mb-3">Pricing</div>
      <h2 class="font-heading text-3xl sm:text-4xl font-bold uppercase tracking-tight">Plans that scale with your reach</h2>
    </div>
    <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @foreach($plans as $plan)
        <div class="card {{ $plan->is_featured ? 'border-brand/60 ring-1 ring-brand/30' : '' }} card-hover p-6 flex flex-col reveal">
          @if($plan->is_featured)<span class="self-start mono text-[.6rem] uppercase tracking-widest bg-brand text-ink px-2 py-0.5 rounded mb-3">Most Popular</span>@endif
          <h3 class="font-heading text-xl font-bold">{{ $plan->name }}</h3>
          <p class="text-xs text-slate-400 mt-1">{{ $plan->tagline }}</p>
          <div class="mt-4"><span class="font-heading text-3xl font-extrabold text-white">{{ $plan->price_label }}</span><span class="text-xs text-slate-500 ml-1">{{ $plan->price_note }}</span></div>
          <ul class="mt-4 space-y-2 text-sm text-slate-300 flex-1">
            @foreach(($plan->features ?? []) as $f)<li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5 shrink-0"></i>{{ $f }}</li>@endforeach
          </ul>
          <a href="{{ $plan->cta_url ?: route('site.contact',['type'=>'advertiser']) }}" class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-ghost' }} justify-center mt-6">{{ $plan->cta_label }}</a>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@include('public.partials.cta')
@endsection
