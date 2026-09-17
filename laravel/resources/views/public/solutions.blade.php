@extends('public.layout')
@section('meta_title','Advertising Solutions — '.\App\Support\Brand::name())
@section('meta_description','From hyperlocal offers to national brand awareness — DOOH advertising solutions for retail, restaurants, FMCG, automotive, education, events and more.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'Advertising Solutions',
  'title' => 'One network, <span class="text-brand">many goals</span>',
  'subtitle' => 'Whatever you are promoting, there is a campaign format built for it.',
])
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach([
      ['map-pin','Local Advertising','Own your neighbourhood with hyper-relevant messaging.'],
      ['sparkles','Brand Awareness','Build memorability with high-frequency exposure.'],
      ['shopping-bag','Retail Promotion','Drive footfall to stores and malls nearby.'],
      ['utensils','Restaurant Advertising','Promote menus and offers at meal times.'],
      ['graduation-cap','Education','Reach students and parents around campuses.'],
      ['car-front','Automotive','Showcase launches and dealership offers.'],
      ['package','FMCG','Mass reach for fast-moving consumer goods.'],
      ['ticket','Events','Fill seats with timely event promotion.'],
      ['badge-percent','Offers','Flash discounts and seasonal deals.'],
      ['rocket','Product Launches','Create buzz on launch day, city-wide.'],
      ['target','Hyperlocal','Pinpoint specific routes and micro-markets.'],
    ] as $i => [$ic,$t,$d])
      <div class="card card-hover p-6 reveal" style="transition-delay: {{ ($i%3)*70 }}ms">
        <span class="h-10 w-10 rounded-lg bg-brand/15 text-brand flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-5 h-5"></i></span>
        <h3 class="font-heading text-lg font-semibold mt-4">{{ $t }}</h3>
        <p class="text-sm text-slate-400 mt-2">{{ $d }}</p>
      </div>
    @endforeach
  </div>
</section>
@include('public.partials.cta')
@endsection
