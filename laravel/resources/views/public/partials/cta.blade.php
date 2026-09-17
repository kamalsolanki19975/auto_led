@php
    $ctaTitle = $ctaTitle ?? 'Ready to put your brand on the move?';
    $ctaText = $ctaText ?? 'Launch a geo-targeted DOOH campaign across our auto network, or install a screen and start earning today.';
@endphp
<section class="relative overflow-hidden border-t border-white/10">
  <div class="absolute inset-0 amber-glow opacity-70"></div>
  <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 text-center reveal">
    <h2 class="font-heading text-3xl sm:text-4xl font-bold uppercase tracking-tight">{{ $ctaTitle }}</h2>
    <p class="mt-4 text-slate-300 max-w-2xl mx-auto">{{ $ctaText }}</p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
      <a href="{{ route('site.contact', ['type'=>'advertiser']) }}" class="btn btn-primary" data-testid="cta-advertise-btn">Advertise With Us <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
      <a href="{{ route('site.contact', ['type'=>'auto_owner']) }}" class="btn btn-ghost" data-testid="cta-join-network-btn">Join Our Auto Network</a>
    </div>
  </div>
</section>
