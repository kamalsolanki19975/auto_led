@extends('public.layout')
@section('meta_title', $title.' — '.\App\Support\Brand::name())
@section('meta_description', $title.' for '.\App\Support\Brand::name().'.')
@section('content')
<section class="border-b border-white/10">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
    <div class="eyebrow mb-3">Legal</div>
    <h1 class="font-heading text-4xl font-extrabold uppercase tracking-tight">{{ $title }}</h1>
    <p class="text-xs text-slate-500 mono mt-2">Last updated {{ now()->format('d M Y') }}</p>
    <div class="prose prose-invert mt-8 text-slate-300 leading-relaxed space-y-4 max-w-none">
      {!! $body !!}
    </div>
  </div>
</section>
@include('public.partials.cta')
@endsection
