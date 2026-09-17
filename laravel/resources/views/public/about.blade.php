@extends('public.layout')
@section('meta_title','About — '.\App\Support\Brand::name())
@section('meta_description','Our mission is to turn everyday auto rides into a premium, measurable advertising medium that rewards brands, owners and drivers alike.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'About Us',
  'title' => 'We put <span class="text-brand">brands in motion</span>',
  'subtitle' => 'We are building the smart advertising layer for urban mobility — connecting brands with commuters through screens on the autos that move the city every day.',
  'image' => $img['city_traffic'],
])
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid md:grid-cols-3 gap-6">
    @foreach([
      ['flag','Our Vision','A city where every ride creates value — for advertisers, owners and drivers.'],
      ['target','Our Mission','Make out-of-home advertising measurable, accessible and fair through technology.'],
      ['cpu','Our Technology','An offline-first player network with verified proof-of-play at its core.'],
    ] as [$ic,$t,$d])
      <div class="card p-8 reveal">
        <span class="h-11 w-11 rounded-xl bg-brand/15 text-brand flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-5 h-5"></i></span>
        <h3 class="font-heading text-xl font-semibold mt-4">{{ $t }}</h3>
        <p class="text-slate-400 mt-2">{{ $d }}</p>
      </div>
    @endforeach
  </div>
</section>
<section class="border-b border-white/10 bg-surface">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
    @foreach([['Autos',$stats['autos_total']],['Screens',$stats['screens_total']],['Cities',$stats['cities_total']],['Advertisers',$stats['advertisers_total']]] as [$l,$v])
      <div class="reveal"><div class="font-heading text-4xl font-extrabold text-white" data-count="{{ $v }}">0</div><div class="text-xs uppercase tracking-widest text-slate-400 mono mt-1">{{ $l }}</div></div>
    @endforeach
  </div>
</section>
@include('public.partials.cta')
@endsection
