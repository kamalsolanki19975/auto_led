@php($image = $image ?? null)
<section class="relative overflow-hidden border-b border-white/10">
  <div class="absolute inset-0 grid-bg opacity-60"></div>
  <div class="absolute inset-x-0 top-0 h-64 amber-glow"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="max-w-3xl reveal">
      @if(!empty($eyebrow))<div class="eyebrow mb-3">{{ $eyebrow }}</div>@endif
      <h1 class="font-heading text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight uppercase leading-none">{!! $title !!}</h1>
      @if(!empty($subtitle))<p class="mt-5 text-lg text-slate-300 leading-relaxed max-w-2xl">{{ $subtitle }}</p>@endif
      @if(!empty($ctas))
        <div class="mt-8 flex flex-wrap gap-3">
          @foreach($ctas as $cta)
            <a href="{{ $cta['href'] }}" class="btn {{ $cta['primary'] ?? false ? 'btn-primary' : 'btn-ghost' }}" @if(!empty($cta['testid'])) data-testid="{{ $cta['testid'] }}" @endif>{{ $cta['label'] }}@if($cta['primary'] ?? false)<i data-lucide="arrow-right" class="w-4 h-4"></i>@endif</a>
          @endforeach
        </div>
      @endif
    </div>
    @if($image)
      <div class="mt-12 reveal">
        <div class="relative rounded-2xl overflow-hidden border border-white/10 max-h-80">
          <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-72 object-cover opacity-80">
          <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/30 to-transparent"></div>
        </div>
      </div>
    @endif
  </div>
</section>
