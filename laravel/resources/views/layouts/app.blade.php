@php($sym = \App\Support\Fmt::symbol())
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AutoAds Network')</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { darkMode:'class', theme:{ extend:{ fontFamily:{
        heading:["'Barlow Condensed'","sans-serif"], body:["'Plus Jakarta Sans'","sans-serif"], mono:["'JetBrains Mono'","monospace"] }}}}
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
      body{font-family:'Plus Jakarta Sans',sans-serif;background:#0B0F17;color:#F3F4F6}
      h1,h2,h3,.font-heading{font-family:'Barlow Condensed',sans-serif}
      .mono{font-family:'JetBrains Mono',monospace}
      ::-webkit-scrollbar{width:8px;height:8px}::-webkit-scrollbar-thumb{background:#1F2937;border-radius:8px}
      .card{background:#111827;border:1px solid rgba(255,255,255,.08);border-radius:.6rem}
      .inp{width:100%;background:#172033;border:1px solid rgba(255,255,255,.1);border-radius:.4rem;padding:.5rem .75rem;font-size:.875rem;color:#F3F4F6}
      .inp:focus{outline:none;border-color:#F59E0B;box-shadow:0 0 0 1px #F59E0B}
      .btn{padding:.5rem 1rem;border-radius:.4rem;font-size:.875rem;font-weight:600;transition:.15s;display:inline-flex;align-items:center;gap:.4rem}
      .btn-primary{background:#F59E0B;color:#0B0F17}.btn-primary:hover{background:#D97706}
      .btn-sec{background:#1F2937;color:#E5E7EB;border:1px solid rgba(255,255,255,.1)}.btn-sec:hover{background:#374151}
      .btn-danger{background:#E11D48;color:#fff}.btn-danger:hover{background:#be123c}
      table.grid{width:100%;font-size:.85rem;border-collapse:collapse}
      table.grid thead{background:#172033;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#9CA3AF}
      table.grid th,table.grid td{text-align:left;padding:.65rem .75rem}
      table.grid tbody tr{border-top:1px solid rgba(255,255,255,.05)}table.grid tbody tr:hover{background:rgba(255,255,255,.02)}
      a{color:inherit} .nav-a.active{background:rgba(245,158,11,.12);color:#F59E0B;border-left:2px solid #F59E0B}
    </style>
</head>
<body>
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="fixed left-0 top-0 h-screen w-60 bg-[#070A10] border-r border-white/10 flex flex-col z-40 overflow-y-auto" x-data="{}">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-5 h-16 border-b border-white/10 shrink-0">
      @if(\App\Support\Brand::logo())
        <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::name() }}" class="h-8 max-w-[160px] object-contain">
      @else
        <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-amber-500 text-[#0B0F17] font-bold">{{ \App\Support\Brand::initials() }}</span>
        <span class="font-heading text-lg font-bold tracking-wide">{{ \App\Support\Brand::wordmark()[0] }}<span class="text-amber-500">·</span>{{ \App\Support\Brand::wordmark()[1] }}</span>
      @endif
    </a>
    <nav class="flex-1 py-3 text-sm">
      @foreach($nav as $group)
        <div class="px-5 pt-4 pb-1 text-[.65rem] font-mono uppercase tracking-widest text-slate-500">{{ $group['group'] }}</div>
        @foreach($group['items'] as $it)
          <a href="{{ route($it['route']) }}" data-testid="nav-{{ $it['route'] }}"
             class="nav-a flex items-center gap-3 px-5 py-2 text-slate-300 hover:text-white hover:bg-white/5 {{ request()->routeIs(str_replace('.index','',$it['route']).'*') ? 'active' : '' }}">
            <i data-lucide="{{ $it['icon'] }}" class="w-4 h-4"></i>{{ $it['label'] }}
          </a>
        @endforeach
      @endforeach
    </nav>
  </aside>

  <!-- Main -->
  <div class="flex-1 ml-60 flex flex-col min-h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-30 h-16 bg-[#0B0F17]/90 backdrop-blur border-b border-white/10 px-6 flex items-center justify-between gap-4">
      <form action="{{ route('search') }}" method="GET" class="flex-1 max-w-lg">
        <div class="relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-500"></i>
          <input name="q" value="{{ request('q') }}" data-testid="global-search-input" placeholder="Search autos, drivers, campaigns, invoices…"
                 class="inp pl-9" autocomplete="off">
        </div>
      </form>
      <div class="flex items-center gap-3">
        <!-- Quick create -->
        <div x-data="{open:false}" class="relative">
          <button @click="open=!open" data-testid="quick-create-btn" class="btn btn-primary"><i data-lucide="plus" class="w-4 h-4"></i>Create</button>
          <div x-show="open" @click.away="open=false" x-cloak class="absolute right-0 mt-2 w-52 card p-2 text-sm z-50">
            @foreach([['Auto','autos.create'],['Owner','owners.create'],['Driver','drivers.create'],['Advertiser','advertisers.create'],['Advertisement','advertisements.create'],['Campaign','campaigns.create'],['Invoice','invoices.create'],['Expense','expenses.create'],['Maintenance Ticket','maintenance.create'],['Settlement','settlements.create']] as $qc)
              @if(Route::has($qc[1]))<a href="{{ route($qc[1]) }}" class="block px-3 py-2 rounded hover:bg-white/5" data-testid="qc-{{ $qc[1] }}">{{ $qc[0] }}</a>@endif
            @endforeach
          </div>
        </div>
        <!-- Bell -->
        <div x-data="{open:false,items:[],unread:{{ $unread }}}" class="relative">
          <button @click="open=!open; if(open){fetch('{{ route('notifications.dropdown') }}').then(r=>r.json()).then(d=>{items=d.items;unread=d.unread})}" data-testid="notif-bell" class="relative p-2 rounded hover:bg-white/5">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span x-show="unread>0" x-text="unread" class="absolute -top-0.5 -right-0.5 bg-rose-500 text-white text-[.6rem] rounded-full h-4 min-w-4 px-1 flex items-center justify-center"></span>
          </button>
          <div x-show="open" @click.away="open=false" x-cloak class="absolute right-0 mt-2 w-80 card z-50">
            <div class="flex items-center justify-between px-4 py-2 border-b border-white/10"><span class="font-semibold text-sm">Notifications</span>
              <form action="{{ route('notifications.readAll') }}" method="POST">@csrf<button class="text-xs text-amber-500" data-testid="notif-readall">Mark all read</button></form></div>
            <div class="max-h-96 overflow-y-auto">
              <template x-for="n in items" :key="n.id">
                <a :href="n.link || '{{ route('notifications.index') }}'" class="block px-4 py-3 border-b border-white/5 hover:bg-white/5" :class="!n.read && 'bg-amber-500/5'">
                  <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full" :class="{'bg-rose-500':n.type=='critical','bg-amber-500':n.type=='warning','bg-sky-500':n.type=='information','bg-emerald-500':n.type=='success'}"></span>
                  <span class="text-sm font-medium" x-text="n.title"></span></div>
                  <p class="text-xs text-slate-400 mt-1" x-text="n.message"></p><span class="text-[.65rem] text-slate-500" x-text="n.time"></span>
                </a>
              </template>
              <template x-if="items.length===0"><p class="px-4 py-6 text-center text-sm text-slate-500">No notifications</p></template>
            </div>
            <a href="{{ route('notifications.index') }}" class="block text-center text-xs text-amber-500 py-2 border-t border-white/10">View all</a>
          </div>
        </div>
        <!-- User -->
        <div x-data="{open:false}" class="relative">
          <button @click="open=!open" data-testid="user-menu" class="flex items-center gap-2 p-1 pr-2 rounded hover:bg-white/5">
            <span class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
            <span class="text-sm hidden sm:block">{{ auth()->user()->name }}</span>
          </button>
          <div x-show="open" @click.away="open=false" x-cloak class="absolute right-0 mt-2 w-48 card p-2 text-sm z-50">
            <div class="px-3 py-1 text-xs text-slate-500">{{ auth()->user()->roles->first()?->name }}</div>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="w-full text-left px-3 py-2 rounded hover:bg-white/5 text-rose-400" data-testid="logout-btn">Sign out</button></form>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-1 p-6">
      @if(session('success'))<div data-testid="flash-success" class="mb-4 card border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>@endif
      @if(session('error'))<div data-testid="flash-error" class="mb-4 card border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">{{ session('error') }}</div>@endif
      @if($errors->any())<div class="mb-4 card border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      @yield('content')
    </main>
  </div>
</div>
<script>lucide.createIcons();document.addEventListener('alpine:initialized',()=>lucide.createIcons());
  document.addEventListener('DOMContentLoaded',()=>{const t=document.querySelector('meta[name=csrf-token]').content;
   document.querySelectorAll('form[method=POST],form[method=post]').forEach(f=>{if(!f.querySelector('input[name=_token]')){const i=document.createElement('input');i.type='hidden';i.name='_token';i.value=t;f.appendChild(i)}})});</script>
@stack('scripts')
</body>
</html>
