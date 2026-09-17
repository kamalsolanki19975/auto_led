@php($sym = \App\Support\Fmt::symbol())
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') · AutoAds Network</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { darkMode:'class', theme:{ extend:{ fontFamily:{
        heading:["'Barlow Condensed'","sans-serif"], body:["'Plus Jakarta Sans'","sans-serif"], mono:["'JetBrains Mono'","monospace"] }}}}
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      body{font-family:'Plus Jakarta Sans',sans-serif;background:#0B0F17;color:#F3F4F6}
      h1,h2,h3,.font-heading{font-family:'Barlow Condensed',sans-serif}
      .mono{font-family:'JetBrains Mono',monospace}
      ::-webkit-scrollbar{width:8px;height:8px}::-webkit-scrollbar-thumb{background:#1F2937;border-radius:8px}
      .card{background:#111827;border:1px solid rgba(255,255,255,.08);border-radius:.6rem}
      .inp{width:100%;background:#172033;border:1px solid rgba(255,255,255,.1);border-radius:.4rem;padding:.5rem .75rem;font-size:.875rem;color:#F3F4F6}
      .inp:focus{outline:none;border-color:#F59E0B;box-shadow:0 0 0 1px #F59E0B}
      .btn{padding:.5rem 1rem;border-radius:.4rem;font-size:.875rem;font-weight:600;transition:background-color .15s,color .15s;display:inline-flex;align-items:center;gap:.4rem}
      .btn-primary{background:#F59E0B;color:#0B0F17}.btn-primary:hover{background:#D97706}
      .btn-sec{background:#1F2937;color:#E5E7EB;border:1px solid rgba(255,255,255,.1)}.btn-sec:hover{background:#374151}
      .btn-danger{background:#E11D48;color:#fff}.btn-danger:hover{background:#be123c}
      table.grid{width:100%;font-size:.85rem;border-collapse:collapse}
      table.grid thead{background:#172033;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#9CA3AF}
      table.grid th,table.grid td{text-align:left;padding:.65rem .75rem}
      table.grid tbody tr{border-top:1px solid rgba(255,255,255,.05)}table.grid tbody tr:hover{background:rgba(255,255,255,.02)}
      a{color:inherit}
      [x-cloak]{display:none!important}
    </style>
</head>
<body>
<header class="sticky top-0 z-30 h-16 bg-[#070A10]/95 backdrop-blur border-b border-white/10 px-6 flex items-center justify-between gap-4">
  <div class="flex items-center gap-3">
    <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-amber-500 text-[#0B0F17] font-bold">A</span>
    <span class="font-heading text-lg font-bold tracking-wide">AUTOADS<span class="text-amber-500">·</span>NET</span>
    <span class="ml-2 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold px-3 py-1 uppercase tracking-wide" data-testid="portal-badge">@yield('portal','Portal')</span>
  </div>
  <div x-data="{open:false}" class="relative">
    <button @click="open=!open" data-testid="user-menu" class="flex items-center gap-2 p-1 pr-2 rounded hover:bg-white/5">
      <span class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
      <span class="text-sm hidden sm:block">{{ auth()->user()->name }}</span>
      <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
    </button>
    <div x-show="open" @click.away="open=false" x-cloak class="absolute right-0 mt-2 w-48 card p-2 text-sm z-50">
      <div class="px-3 py-1 text-xs text-slate-500">{{ auth()->user()->roles->first()?->name }}</div>
      <div class="px-3 py-1 text-xs text-slate-400 mono truncate">{{ auth()->user()->email }}</div>
      <form action="{{ route('logout') }}" method="POST" class="mt-1">@csrf<button class="w-full text-left px-3 py-2 rounded hover:bg-white/5 text-rose-400" data-testid="logout-btn">Sign out</button></form>
    </div>
  </div>
</header>
<main class="max-w-6xl mx-auto p-6">
  @if(session('success'))<div data-testid="flash-success" class="mb-4 card border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>@endif
  @if(session('error'))<div data-testid="flash-error" class="mb-4 card border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">{{ session('error') }}</div>@endif
  @if($errors->any())<div class="mb-4 card border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  @yield('content')
</main>
<script>lucide.createIcons();document.addEventListener('alpine:initialized',()=>lucide.createIcons());
  document.addEventListener('DOMContentLoaded',()=>{const t=document.querySelector('meta[name=csrf-token]').content;
   document.querySelectorAll('form[method=POST],form[method=post]').forEach(f=>{if(!f.querySelector('input[name=_token]')){const i=document.createElement('input');i.type='hidden';i.name='_token';i.value=t;f.appendChild(i)}})});</script>
@stack('scripts')
</body>
</html>
