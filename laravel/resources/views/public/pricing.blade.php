@extends('public.layout')
@section('meta_title','Pricing — '.\App\Support\Brand::name())
@section('meta_description','Flexible DOOH advertising plans by campaign duration, auto count, screen count or custom quote. Choose the plan that fits your reach and budget.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'Pricing',
  'title' => 'Plans that scale with your <span class="text-brand">reach</span>',
  'subtitle' => 'Transparent packages by autos, cities and duration — or a custom quote for agencies and national brands.',
])
<section class="border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    @if($plans->count())
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($plans as $plan)
          <div class="card {{ $plan->is_featured ? 'border-brand/60 ring-1 ring-brand/30' : '' }} card-hover p-6 flex flex-col reveal">
            @if($plan->is_featured)<span class="self-start mono text-[.6rem] uppercase tracking-widest bg-brand text-ink px-2 py-0.5 rounded mb-3">Most Popular</span>@endif
            <h3 class="font-heading text-xl font-bold">{{ $plan->name }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $plan->tagline }}</p>
            <div class="mt-4"><span class="font-heading text-3xl font-extrabold text-white">{{ $plan->price_label }}</span><span class="text-xs text-slate-500 ml-1">{{ $plan->price_note }}</span></div>
            <ul class="mt-4 space-y-2 text-sm text-slate-300 flex-1">
              @foreach(($plan->features ?? []) as $f)<li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5 shrink-0"></i>{{ $f }}</li>@endforeach
            </ul>
            <a href="{{ $plan->cta_url ?: route('site.contact',['type'=>'advertiser']) }}" class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-ghost' }} justify-center mt-6" data-testid="pricing-cta-{{ $plan->id }}">{{ $plan->cta_label }}</a>
          </div>
        @endforeach
      </div>
    @else
      <div class="card p-12 text-center text-slate-500">Pricing plans coming soon.</div>
    @endif
    <p class="text-center text-sm text-slate-500 mt-8">Need something custom? <a href="{{ route('site.contact',['type'=>'advertiser']) }}" class="text-brand hover:underline">Request a tailored quote →</a></p>
  </div>
</section>
@include('public.partials.cta')
@endsection
