@php
    $b_name = \App\Support\Brand::name();
    $b_logo = \App\Support\Brand::logo();
    $b_mark = \App\Support\Brand::wordmark();
    $b_social = \App\Support\Brand::social();
    $seo = \App\Models\SystemSetting::group('seo');
    $site = \App\Models\SystemSetting::group('website');
    $navLinks = [
        ['How It Works', route('site.how')],
        ['Advertisers', route('site.advertisers')],
        ['Auto Owners', route('site.owners')],
        ['Technology', route('site.technology')],
        ['Analytics', route('site.analytics')],
        ['API', route('site.developers')],
        ['Pricing', route('site.pricing')],
        ['About', route('site.about')],
        ['Contact', route('site.contact')],
    ];
    $metaTitle = trim($__env->yieldContent('meta_title')) ?: ($seo['default_title'] ?? $b_name);
    $metaDesc = trim($__env->yieldContent('meta_description')) ?: ($seo['default_description'] ?? '');
@endphp
<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $b_name }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($seo['og_image']))<meta property="og:image" content="{{ $seo['og_image'] }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { darkMode:'class', theme:{ extend:{
        fontFamily:{ heading:["'Barlow Condensed'","sans-serif"], body:["'Plus Jakarta Sans'","sans-serif"], mono:["'JetBrains Mono'","monospace"] },
        colors:{ ink:'#070A10', surface:'#0B0F17', card:'#111827', brand:{DEFAULT:'#F59E0B',600:'#D97706',700:'#B45309'} }
      }}}
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      body{font-family:'Plus Jakarta Sans',sans-serif;background:#070A10;color:#F9FAFB;-webkit-font-smoothing:antialiased}
      h1,h2,h3,.font-heading{font-family:'Barlow Condensed',sans-serif}
      .mono{font-family:'JetBrains Mono',monospace}
      ::-webkit-scrollbar{width:9px;height:9px}::-webkit-scrollbar-track{background:#0B0F17}::-webkit-scrollbar-thumb{background:#1F2937;border-radius:8px}
      ::selection{background:#F59E0B;color:#070A10}
      a{color:inherit}
      .eyebrow{font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.18em;text-transform:uppercase;color:#F59E0B}
      .btn{display:inline-flex;align-items:center;gap:.5rem;font-weight:700;border-radius:.75rem;transition:transform .15s,background-color .15s,border-color .15s,color .15s}
      .btn:hover{transform:translateY(-1px)}
      .btn-primary{background:#F59E0B;color:#070A10;padding:.7rem 1.25rem;box-shadow:0 10px 30px -10px rgba(245,158,11,.5)}
      .btn-primary:hover{background:#D97706}
      .btn-ghost{border:1px solid rgba(255,255,255,.15);color:#F9FAFB;padding:.7rem 1.25rem}
      .btn-ghost:hover{border-color:#F59E0B;color:#F59E0B}
      .card{background:#111827;border:1px solid rgba(255,255,255,.08);border-radius:1rem;transition:border-color .3s,transform .3s}
      .card-hover:hover{border-color:rgba(245,158,11,.4);transform:translateY(-4px)}
      .glass{background:rgba(11,15,23,.85);backdrop-filter:blur(16px)}
      .grid-bg{background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:44px 44px}
      .amber-glow{background:radial-gradient(60% 60% at 50% 0%,rgba(245,158,11,.18),transparent 70%)}
      .reveal{opacity:0;transform:translateY(18px);transition:opacity .7s ease,transform .7s ease}
      .reveal.in{opacity:1;transform:none}
      [x-cloak]{display:none!important}
      .link-underline{position:relative}
      .link-underline::after{content:'';position:absolute;left:0;bottom:-4px;height:2px;width:0;background:#F59E0B;transition:width .25s}
      .link-underline:hover::after{width:100%}
    </style>
    @stack('head')
</head>
<body x-data="{ mobile:false }">
<!-- Header -->
<header class="fixed top-0 inset-x-0 z-50 glass border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
    <a href="{{ route('site.home') }}" class="flex items-center gap-2 shrink-0" data-testid="brand-home-link">
      @if($b_logo)
        <img src="{{ $b_logo }}" alt="{{ $b_name }}" class="h-8 max-w-[160px] object-contain">
      @else
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand text-ink font-extrabold">{{ \App\Support\Brand::initials() }}</span>
        <span class="font-heading text-xl font-extrabold tracking-wide">{{ $b_mark[0] }}<span class="text-brand">·</span>{{ $b_mark[1] }}</span>
      @endif
    </a>
    <nav class="hidden lg:flex items-center gap-6 text-sm text-slate-300">
      @foreach($navLinks as [$label,$href])
        <a href="{{ $href }}" data-testid="nav-{{ \Illuminate\Support\Str::slug($label) }}-link" class="link-underline hover:text-white {{ url()->current()===$href ? 'text-white' : '' }}">{{ $label }}</a>
      @endforeach
    </nav>
    <div class="hidden lg:flex items-center gap-3">
      <a href="{{ route('login') }}" data-testid="header-login-link" class="text-sm text-slate-300 hover:text-white px-3 py-2">Login</a>
      <a href="{{ route('site.contact', ['type'=>'advertiser']) }}" data-testid="header-start-advertising-btn" class="btn btn-primary text-sm">Start Advertising</a>
    </div>
    <button @click="mobile=true" class="lg:hidden p-2 rounded-lg hover:bg-white/5" data-testid="mobile-menu-open" aria-label="Open menu"><i data-lucide="menu" class="w-6 h-6"></i></button>
  </div>
</header>

<!-- Mobile drawer -->
<div x-show="mobile" x-cloak class="fixed inset-0 z-[60] lg:hidden" @keydown.escape.window="mobile=false">
  <div class="absolute inset-0 bg-black/70" @click="mobile=false"></div>
  <div class="absolute right-0 top-0 h-full w-80 max-w-[85%] bg-surface border-l border-white/10 p-6 overflow-y-auto"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
    <div class="flex items-center justify-between mb-6">
      <span class="font-heading text-lg font-bold">{{ $b_name }}</span>
      <button @click="mobile=false" class="p-2 rounded-lg hover:bg-white/5" data-testid="mobile-menu-close" aria-label="Close menu"><i data-lucide="x" class="w-6 h-6"></i></button>
    </div>
    <nav class="flex flex-col gap-1 text-slate-200">
      <a href="{{ route('site.home') }}" class="py-2.5 border-b border-white/5">Home</a>
      @foreach($navLinks as [$label,$href])
        <a href="{{ $href }}" class="py-2.5 border-b border-white/5">{{ $label }}</a>
      @endforeach
    </nav>
    <div class="mt-6 flex flex-col gap-3">
      <a href="{{ route('login') }}" class="btn btn-ghost justify-center">Login</a>
      <a href="{{ route('site.contact', ['type'=>'advertiser']) }}" class="btn btn-primary justify-center">Start Advertising</a>
    </div>
  </div>
</div>

<main class="pt-16">
  @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
      <div data-testid="site-flash-success" class="card border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300 flex items-center gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ session('success') }}
      </div>
    </div>
  @endif
  @yield('content')
</main>

<!-- Footer -->
<footer class="border-t border-white/10 bg-ink">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <div class="grid grid-cols-2 md:grid-cols-6 gap-8">
      <div class="col-span-2">
        <div class="flex items-center gap-2">
          @if($b_logo)<img src="{{ $b_logo }}" alt="{{ $b_name }}" class="h-8 max-w-[160px] object-contain">
          @else<span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand text-ink font-extrabold">{{ \App\Support\Brand::initials() }}</span>
          <span class="font-heading text-xl font-extrabold tracking-wide">{{ $b_mark[0] }}<span class="text-brand">·</span>{{ $b_mark[1] }}</span>@endif
        </div>
        <p class="text-sm text-slate-400 mt-4 max-w-xs">{{ \App\Support\Brand::tagline() }}</p>
        @php
          $socialSvg = [
            'twitter'   => '<svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.66l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'linkedin'  => '<svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            'facebook'  => '<svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.012-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
            'youtube'   => '<svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
          ];
        @endphp
        <div class="flex items-center gap-3 mt-5">
          @foreach($socialSvg as $k=>$svg)
            @if(!empty($b_social[$k]))<a href="{{ $b_social[$k] }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-lg bg-white/5 hover:bg-brand hover:text-ink flex items-center justify-center transition" aria-label="{{ $k }}" data-testid="social-{{ $k }}-link">{!! $svg !!}</a>@endif
          @endforeach
        </div>
      </div>
      <div>
        <div class="text-xs font-mono uppercase tracking-widest text-slate-500 mb-3">Product</div>
        <ul class="space-y-2 text-sm text-slate-400">
          <li><a href="{{ route('site.how') }}" class="hover:text-white">How It Works</a></li>
          <li><a href="{{ route('site.technology') }}" class="hover:text-white">Technology</a></li>
          <li><a href="{{ route('site.analytics') }}" class="hover:text-white">Analytics</a></li>
          <li><a href="{{ route('site.pricing') }}" class="hover:text-white">Pricing</a></li>
        </ul>
      </div>
      <div>
        <div class="text-xs font-mono uppercase tracking-widest text-slate-500 mb-3">Advertisers</div>
        <ul class="space-y-2 text-sm text-slate-400">
          <li><a href="{{ route('site.advertisers') }}" class="hover:text-white">For Advertisers</a></li>
          <li><a href="{{ route('site.solutions') }}" class="hover:text-white">Ad Solutions</a></li>
          <li><a href="{{ route('site.network') }}" class="hover:text-white">Network</a></li>
          <li><a href="{{ route('site.contact', ['type'=>'advertiser']) }}" class="hover:text-white">Start Advertising</a></li>
        </ul>
      </div>
      <div>
        <div class="text-xs font-mono uppercase tracking-widest text-slate-500 mb-3">Auto Network</div>
        <ul class="space-y-2 text-sm text-slate-400">
          <li><a href="{{ route('site.owners') }}" class="hover:text-white">For Auto Owners</a></li>
          <li><a href="{{ route('site.contact', ['type'=>'auto_owner']) }}" class="hover:text-white">Join the Network</a></li>
          <li><a href="{{ route('site.developers') }}" class="hover:text-white">Developers / API</a></li>
          <li><a href="{{ route('login') }}" class="hover:text-white">Login</a></li>
        </ul>
      </div>
      <div>
        <div class="text-xs font-mono uppercase tracking-widest text-slate-500 mb-3">Company</div>
        <ul class="space-y-2 text-sm text-slate-400">
          <li><a href="{{ route('site.about') }}" class="hover:text-white">About</a></li>
          <li><a href="{{ route('site.faq') }}" class="hover:text-white">FAQ</a></li>
          <li><a href="{{ route('site.contact') }}" class="hover:text-white">Contact</a></li>
        </ul>
      </div>
    </div>
    <div class="mt-12 pt-6 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
      <p>© {{ date('Y') }} {{ $b_name }}. All rights reserved.</p>
      <div class="flex items-center gap-5">
        <a href="{{ route('site.legal','privacy') }}" class="hover:text-white">Privacy Policy</a>
        <a href="{{ route('site.legal','terms') }}" class="hover:text-white">Terms</a>
        <a href="{{ route('site.legal','cookie') }}" class="hover:text-white">Cookie Policy</a>
      </div>
    </div>
  </div>
</footer>

<script>
  lucide.createIcons();
  document.addEventListener('alpine:initialized',()=>lucide.createIcons());
  // CSRF injection for public POST forms
  document.addEventListener('DOMContentLoaded',()=>{
    const t=document.querySelector('meta[name=csrf-token]').content;
    document.querySelectorAll('form[method=POST],form[method=post]').forEach(f=>{if(!f.querySelector('input[name=_token]')){const i=document.createElement('input');i.type='hidden';i.name='_token';i.value=t;f.appendChild(i);}});
    // Scroll reveal
    const io=new IntersectionObserver((es)=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target);}}),{threshold:.12});
    document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
    // Count-up stats
    const cio=new IntersectionObserver((es)=>es.forEach(e=>{
      if(!e.isIntersecting) return; const el=e.target; cio.unobserve(el);
      const target=parseFloat(el.dataset.count||'0'); const dur=1200; const start=performance.now();
      const suffix=el.dataset.suffix||''; const dec=parseInt(el.dataset.dec||'0');
      function step(now){ const p=Math.min((now-start)/dur,1); const val=target*(1-Math.pow(1-p,3));
        el.textContent=val.toLocaleString('en-IN',{maximumFractionDigits:dec,minimumFractionDigits:dec})+suffix; if(p<1) requestAnimationFrame(step); }
      requestAnimationFrame(step);
    }),{threshold:.4});
    document.querySelectorAll('[data-count]').forEach(el=>cio.observe(el));
  });
</script>
@stack('scripts')
</body>
</html>
