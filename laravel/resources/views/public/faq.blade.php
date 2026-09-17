@extends('public.layout')
@section('meta_title','FAQ — '.\App\Support\Brand::name())
@section('meta_description','Answers for advertisers, auto owners and drivers about campaigns, hardware, payments, proof-of-play and APIs.')
@section('content')
@include('public.partials.page-hero', [
  'eyebrow' => 'FAQ',
  'title' => 'Questions? <span class="text-brand">Answered.</span>',
  'subtitle' => 'Everything you need to know about advertising, hardware, earnings and integrations.',
])
<section class="border-b border-white/10">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    @forelse($faqs as $category => $items)
      <div class="mb-10 reveal">
        <h2 class="font-heading text-xl font-bold uppercase tracking-tight text-brand mb-4">{{ $category }}</h2>
        <div class="space-y-3">
          @foreach($items as $faq)
            <div class="card overflow-hidden" x-data="{ open: false }">
              <button @click="open=!open" class="w-full flex items-center justify-between gap-4 p-5 text-left" :aria-expanded="open" data-testid="faq-{{ $faq->id }}">
                <span class="font-medium text-slate-100">{{ $faq->question }}</span>
                <i data-lucide="chevron-down" class="w-5 h-5 text-brand shrink-0 transition-transform" :class="open && 'rotate-180'"></i>
              </button>
              <div x-show="open" x-collapse x-cloak class="px-5 pb-5 -mt-1 text-sm text-slate-400 leading-relaxed">{{ $faq->answer }}</div>
            </div>
          @endforeach
        </div>
      </div>
    @empty
      <div class="card p-12 text-center text-slate-500">FAQs will appear here soon.</div>
    @endforelse
  </div>
</section>
@include('public.partials.cta', ['ctaTitle'=>'Still have questions?','ctaText'=>'Our team is happy to help you get started.'])
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
@endpush
@endsection
