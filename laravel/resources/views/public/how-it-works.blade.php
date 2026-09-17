@extends('public.layout')
@section('meta_title','How It Works — '.\App\Support\Brand::name())
@section('meta_description','From campaign creation to smart targeting, cloud sync, passenger playback and verified proof-of-play analytics — see how the AutoAds network delivers.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'How It Works',
  'title' => 'A closed loop from <span class="text-brand">brand brief</span> to verified playback',
  'subtitle' => 'Every campaign flows through a transparent pipeline so advertiser spend maps to real, measured screen delivery on the road.',
])

<section class="border-b border-white/10">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="space-y-6">
      @foreach([
        ['megaphone','Advertiser creates a campaign','Define budget, upload creative, choose target cities, areas and schedule inside the advertiser portal.'],
        ['crosshair','Smart geo-targeting','Campaigns are matched to the right autos and routes so your brand appears where your audience commutes.'],
        ['cloud-download','Cloud sync to auto screens','Approved content is pushed to 4G-connected Android players and cached for reliable offline playback.'],
        ['play-circle','Passenger experience & playback','Full-HD creatives play on the passenger-facing screen throughout every ride.'],
        ['shield-check','Proof-of-play & real-time analytics','Each play is logged with device, screen, auto, timestamp & completion, then validated into delivery analytics.'],
      ] as $i => [$icon,$t,$d])
        <div class="flex gap-5 reveal">
          <div class="flex flex-col items-center">
            <span class="h-12 w-12 rounded-xl bg-brand/15 text-brand flex items-center justify-center shrink-0"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></span>
            @if(!$loop->last)<span class="w-px flex-1 bg-gradient-to-b from-brand/40 to-transparent my-2"></span>@endif
          </div>
          <div class="card p-6 flex-1 card-hover">
            <div class="flex items-center gap-3"><span class="mono text-brand text-sm">Step 0{{ $i+1 }}</span><h3 class="font-heading text-xl font-semibold">{{ $t }}</h3></div>
            <p class="text-slate-400 mt-2">{{ $d }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@include('public.partials.cta')
@endsection
