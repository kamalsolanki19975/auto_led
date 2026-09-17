@extends('public.layout')
@section('meta_title','For Auto Owners & Drivers — '.\App\Support\Brand::name())
@section('meta_description','Get a free digital screen installed on your auto and earn from verified advertising activity with transparent settlements and full device support.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'For Auto Owners & Drivers 🚗',
  'title' => 'Earn from a screen — at <span class="text-brand">zero upfront cost</span>',
  'subtitle' => 'We install and maintain the digital screen. You keep it on the road and earn from verified advertising activity.',
  'image' => $img['auto_night'],
  'ctas' => [['label'=>'Join Our Auto Network','href'=>route('site.contact',['type'=>'auto_owner']),'primary'=>true,'testid'=>'owners-cta']],
])

<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach([
      ['gift','Free Screen Installation','We provide and install the digital screen and player.'],
      ['wallet','No Upfront Cost','Zero hardware cost to you — ever.'],
      ['hand-coins','Earn on Verified Activity','Income based on validated advertising runtime.'],
      ['line-chart','Transparent Earnings','See exactly what you earn, in your portal.'],
      ['banknote','Settlement Tracking','Track settlements and payments end to end.'],
      ['wrench','Device & Maintenance Support','Field technicians handle installs, service & swaps.'],
    ] as $i => [$ic,$t,$d])
      <div class="card card-hover p-6 reveal" style="transition-delay: {{ ($i%3)*80 }}ms">
        <span class="h-10 w-10 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-5 h-5"></i></span>
        <h3 class="font-heading text-lg font-semibold mt-4">{{ $t }}</h3>
        <p class="text-sm text-slate-400 mt-2">{{ $d }}</p>
      </div>
    @endforeach
  </div>
</section>

<section class="border-b border-white/10 bg-surface">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <h2 class="font-heading text-2xl font-bold text-center reveal">Getting started is simple</h2>
    <div class="mt-8 grid sm:grid-cols-3 gap-6">
      @foreach([['1','Apply','Register your auto in a couple of minutes.'],['2','Install','We fit the screen & 4G player — free.'],['3','Earn','Ads play, plays are verified, you get paid.']] as [$n,$t,$d])
        <div class="card p-6 text-center reveal"><div class="font-heading text-4xl font-extrabold text-brand">{{ $n }}</div><h3 class="font-heading text-lg font-semibold mt-2">{{ $t }}</h3><p class="text-sm text-slate-400 mt-1">{{ $d }}</p></div>
      @endforeach
    </div>
  </div>
</section>
@include('public.partials.cta', ['ctaTitle'=>'Ready to turn your auto into income?','ctaText'=>'Join a growing network of auto owners earning from every ride.'])
@endsection
