@extends('public.layout')
@section('meta_title','Network Coverage — '.\App\Support\Brand::name())
@section('meta_description','Explore live network coverage: autos, screens, devices, SIMs and active campaigns across cities and areas.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'Network',
  'title' => 'A living network, <span class="text-brand">measured in real time</span>',
  'subtitle' => 'Live coverage across autos, screens, devices and campaigns — pulled straight from the platform.',
])

<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      @foreach([
        ['🚗','Autos', $stats['autos_total'], $stats['autos_active'].' active'],
        ['📺','Screens', $stats['screens_total'], $stats['screens_online'].' online'],
        ['📱','Devices', $stats['devices_total'], $stats['devices_online'].' online'],
        ['📶','SIMs', $stats['sims_total'], $stats['sims_active'].' active'],
        ['🏙️','Cities', $stats['cities_total'], $stats['areas_total'].' areas'],
        ['📢','Campaigns', $stats['campaigns_total'], $stats['campaigns_active'].' active'],
      ] as [$e,$l,$v,$sub])
        <div class="card p-5 reveal">
          <div class="text-xl">{{ $e }}</div>
          <div class="font-heading text-3xl font-extrabold text-white mt-1" data-count="{{ $v }}">0</div>
          <div class="text-xs text-slate-400 mono mt-1">{{ $l }}</div>
          <div class="text-[.7rem] text-brand mono mt-1">{{ $sub }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section class="border-b border-white/10 bg-surface">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between mb-6">
      <h2 class="font-heading text-2xl font-bold uppercase tracking-tight">Coverage by city &amp; area</h2>
      <span class="mono text-xs text-slate-500 flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>GPS map-ready</span>
    </div>
    @if(count($coverage))
      <div class="grid md:grid-cols-2 gap-6">
        @foreach($coverage as $c)
          <div class="card p-6 reveal">
            <div class="flex items-center justify-between">
              <h3 class="font-heading text-xl font-semibold flex items-center gap-2"><i data-lucide="building-2" class="w-4 h-4 text-brand"></i>{{ $c['city'] ?: 'Unassigned' }}</h3>
              <span class="mono text-sm text-slate-300">{{ $c['autos'] }} autos</span>
            </div>
            <div class="mt-4 space-y-2">
              @forelse($c['areas'] as $a)
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-300">{{ $a['area'] ?: 'General' }}</span>
                  <div class="flex items-center gap-2 w-1/2">
                    <div class="h-1.5 flex-1 rounded-full bg-white/10 overflow-hidden"><div class="h-full bg-brand" style="width: {{ $c['autos'] ? min(100, round(($a['autos']/max($c['autos'],1))*100)) : 0 }}%"></div></div>
                    <span class="mono text-xs text-slate-400 w-8 text-right">{{ $a['autos'] }}</span>
                  </div>
                </div>
              @empty
                <p class="text-sm text-slate-500">No areas mapped yet.</p>
              @endforelse
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="card p-12 text-center text-slate-500"><i data-lucide="map" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><div>Coverage data will appear here as the network grows.</div></div>
    @endif
  </div>
</section>
@include('public.partials.cta')
@endsection
