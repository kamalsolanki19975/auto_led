@extends('public.layout')
@section('meta_title','Analytics — '.\App\Support\Brand::name())
@section('meta_description','Playback analytics, runtime, campaign delivery, proof-of-play, network performance, device health and campaign profitability.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'Analytics 📈',
  'title' => 'Decisions backed by <span class="text-brand">real data</span>',
  'subtitle' => 'From individual playback events to campaign profitability, every layer is measured and visualised.',
  'image' => $img['analytics'],
])
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid md:grid-cols-3 gap-6">
      <div class="card p-6 reveal">
        <div class="eyebrow mb-3">Delivery Quality</div>
        <div class="flex justify-between text-sm mb-1"><span class="text-slate-300">Valid vs total plays</span><span class="mono text-white">{{ $stats['plays_total'] ? round($stats['plays_valid']/$stats['plays_total']*100) : 0 }}%</span></div>
        <div class="h-2 rounded-full bg-white/10 overflow-hidden"><div class="h-full bg-brand" style="width: {{ $stats['plays_total'] ? min(100, round($stats['plays_valid']/$stats['plays_total']*100)) : 0 }}%"></div></div>
        <div class="grid grid-cols-2 gap-3 mt-5">
          <div class="rounded-xl bg-white/5 p-4"><div class="mono text-2xl font-bold text-white" data-count="{{ $stats['plays_total'] }}">0</div><div class="text-xs text-slate-400 mt-1">Total Plays</div></div>
          <div class="rounded-xl bg-white/5 p-4"><div class="mono text-2xl font-bold text-white" data-count="{{ $stats['plays_valid'] }}">0</div><div class="text-xs text-slate-400 mt-1">Valid Plays</div></div>
        </div>
      </div>
      <div class="card p-6 reveal lg:col-span-2">
        <div class="eyebrow mb-4">What you can measure</div>
        <div class="grid sm:grid-cols-2 gap-3">
          @foreach([['play-circle','Playback analytics'],['timer','Runtime & valid runtime'],['target','Campaign delivery %'],['shield-check','Proof of play'],['network','Network performance'],['heart-pulse','Device health'],['pie-chart','Campaign profitability'],['trending-up','Revenue vs expense']] as [$ic,$t])
            <div class="flex items-center gap-3 rounded-xl bg-white/5 border border-white/5 p-3">
              <span class="h-9 w-9 rounded-lg bg-brand/15 text-brand flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i></span>
              <span class="text-sm text-slate-200">{{ $t }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@include('public.partials.cta')
@endsection
