@extends('public.layout')
@section('meta_title','For Advertisers — '.\App\Support\Brand::name())
@section('meta_description','Create geo-targeted DOOH campaigns on passenger auto screens with scheduling, real-time monitoring, verified proof-of-play and campaign analytics.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'For Advertisers 📢',
  'title' => 'Put your brand <span class="text-brand">on the move</span>',
  'subtitle' => 'Reach commuters across the city with measurable, geo-targeted advertising on passenger-facing auto screens.',
  'image' => $img['billboard'],
  'ctas' => [['label'=>'Start Advertising','href'=>route('site.contact',['type'=>'advertiser']),'primary'=>true,'testid'=>'advertisers-cta']],
])

<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach([
        ['layout-template','Campaign Creation','Set up campaigns with budget, creative and flight dates in minutes.'],
        ['crosshair','Audience Targeting','Target by city, area and auto-group to reach the right commuters.'],
        ['car','Auto Selection','Choose the autos and routes that best match your audience.'],
        ['calendar-clock','Scheduling','Dayparting and date ranges so your ad runs at peak attention.'],
        ['image','Advertisement Management','Manage multiple creatives with an approval workflow.'],
        ['activity','Real-time Monitoring','Watch delivery unfold live across the network.'],
        ['shield-check','Proof of Play','Independent, timestamped playback verification.'],
        ['bar-chart-3','Analytics','Delivery %, valid plays, runtime and reach.'],
        ['file-text','Campaign Reports','Exportable reports for every stakeholder.'],
      ] as $i => [$ic,$t,$d])
        <div class="card card-hover p-6 reveal" style="transition-delay: {{ ($i%3)*80 }}ms">
          <span class="h-10 w-10 rounded-lg bg-brand/15 text-brand flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-5 h-5"></i></span>
          <h3 class="font-heading text-lg font-semibold mt-4">{{ $t }}</h3>
          <p class="text-sm text-slate-400 mt-2">{{ $d }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section class="border-b border-white/10 bg-surface">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
    @foreach([['Autos in network',$stats['autos_total']],['Screens live',$stats['screens_total']],['Cities',$stats['cities_total']],['Valid plays',$stats['plays_valid']]] as [$l,$v])
      <div class="reveal"><div class="font-heading text-4xl font-extrabold text-white" data-count="{{ $v }}">0</div><div class="text-xs uppercase tracking-widest text-slate-400 mono mt-1">{{ $l }}</div></div>
    @endforeach
  </div>
</section>
@include('public.partials.cta', ['ctaTitle'=>'Launch your first campaign','ctaText'=>'Tell us your goals and our team will get you live on the network fast.'])
@endsection
