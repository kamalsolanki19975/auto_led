@php
  $sym = \App\Support\Fmt::symbol();
  $activeLabel = 'Overview';
  $activeGroupLabel = 'Dashboard';
  foreach($nav as $g){ foreach($g['items'] as $it){ if(request()->routeIs(str_replace('.index','',$it['route']).'*')){ $activeLabel=$it['label']; $activeGroupLabel=$g['group']; } } }
  $groupIcons = ['Dashboard'=>'layout-dashboard','Network'=>'radio-tower','Advertising'=>'megaphone','Operations'=>'wrench','Finance'=>'dollar-sign','Reports'=>'bar-chart-3','Integrations'=>'code-2','CRM'=>'user-plus','Administration'=>'shield-alert'];
@endphp
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AutoAds Network')</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { darkMode:'class', theme:{ extend:{ fontFamily:{
        heading:["'Barlow Condensed'","sans-serif"], body:["'Plus Jakarta Sans'","sans-serif"], mono:["'JetBrains Mono'","monospace"] },
        colors:{ ink:'#070A10', surface:'#0B0F17', sidebar:'#090D14', card:'#111827', brand:{DEFAULT:'#F59E0B',600:'#D97706',700:'#B45309'} } }}}
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
      body{font-family:'Plus Jakarta Sans',sans-serif;background:#070A10;color:#F3F4F6;-webkit-font-smoothing:antialiased}
      h1,h2,h3,.font-heading{font-family:'Barlow Condensed',sans-serif}
      .mono{font-family:'JetBrains Mono',monospace}
      ::-webkit-scrollbar{width:8px;height:8px}::-webkit-scrollbar-track{background:#090D14}::-webkit-scrollbar-thumb{background:#1F2937;border-radius:8px}::-webkit-scrollbar-thumb:hover{background:#374151}
      ::selection{background:#F59E0B;color:#070A10}
      [x-cloak]{display:none!important}
      a{color:inherit}
      /* Cards */
      .card{background:#111827;border:1px solid rgba(255,255,255,.07);border-radius:1rem;transition:border-color .3s,transform .3s,box-shadow .3s}
      .card-hover:hover{border-color:rgba(245,158,11,.4);box-shadow:0 12px 40px -18px rgba(245,158,11,.35)}
      /* Inputs */
      .inp{width:100%;background:#0B0F17;border:1px solid rgba(255,255,255,.1);border-radius:.6rem;padding:.55rem .8rem;font-size:.875rem;color:#F3F4F6;transition:border-color .15s,box-shadow .15s}
      .inp::placeholder{color:#6B7280}
      .inp:focus{outline:none;border-color:#F59E0B;box-shadow:0 0 0 3px rgba(245,158,11,.18)}
      /* Buttons */
      .btn{padding:.55rem 1rem;border-radius:.6rem;font-size:.85rem;font-weight:700;transition:transform .15s,background-color .15s,border-color .15s,color .15s;display:inline-flex;align-items:center;gap:.45rem;white-space:nowrap}
      .btn:active{transform:translateY(1px)}
      .btn-primary{background:#F59E0B;color:#070A10;box-shadow:0 10px 26px -12px rgba(245,158,11,.65)}.btn-primary:hover{background:#D97706}
      .btn-sec{background:#1F2937;color:#E5E7EB;border:1px solid rgba(255,255,255,.1)}.btn-sec:hover{background:#374151;border-color:rgba(255,255,255,.18)}
      .btn-ghost{background:transparent;color:#9CA3AF;border:1px solid rgba(255,255,255,.1)}.btn-ghost:hover{color:#fff;border-color:rgba(245,158,11,.5)}
      .btn-danger{background:#E11D48;color:#fff}.btn-danger:hover{background:#be123c}
      /* Tables */
      table.grid{width:100%;font-size:.85rem;border-collapse:collapse}
      table.grid thead{background:#090D14;font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:#9CA3AF}
      table.grid thead th{font-family:'JetBrains Mono',monospace;font-weight:600}
      table.grid th,table.grid td{text-align:left;padding:.7rem .9rem}
      table.grid tbody tr{border-top:1px solid rgba(255,255,255,.05);transition:background-color .15s}
      table.grid tbody tr:hover{background:rgba(245,158,11,.04)}
      /* Sidebar nav */
      .nav-a{position:relative;border-radius:.55rem;transition:background-color .15s,color .15s}
      .nav-a.active{background:rgba(245,158,11,.12);color:#F59E0B;box-shadow:inset 2px 0 0 #F59E0B}
      .grp-btn:hover{color:#E5E7EB}
      .pill{display:inline-flex;align-items:center;gap:.35rem;border-radius:9999px;padding:.15rem .6rem;font-size:.68rem;font-weight:600;font-family:'JetBrains Mono',monospace}
      .live-dot{position:relative;display:inline-flex;height:.5rem;width:.5rem}
      .live-dot::before{content:'';position:absolute;inset:0;border-radius:9999px;background:#10B981;opacity:.75;animation:ping 1.6s cubic-bezier(0,0,.2,1) infinite}
      .live-dot::after{content:'';position:relative;display:inline-flex;border-radius:9999px;height:.5rem;width:.5rem;background:#10B981}
      @keyframes ping{75%,100%{transform:scale(2);opacity:0}}
      .fade-up{animation:fadeUp .5s ease both}
      @keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
    </style>
    @stack('head')
</head>
<body class="bg-[#070A10]">
<div class="flex min-h-screen" x-data="{ collapsed: JSON.parse(localStorage.getItem('aa_rail')||'false'), mobileOpen:false, help:false }"
     x-init="$watch('collapsed', v=>localStorage.setItem('aa_rail', JSON.stringify(v)))"
     @keydown.window.escape="mobileOpen=false; help=false"
     @keydown.window.ctrl.k.prevent="$refs.globalSearch && $refs.globalSearch.focus()"
     @keydown.window.meta.k.prevent="$refs.globalSearch && $refs.globalSearch.focus()"
     @keydown.window.alt.h.prevent="help=!help">

  <!-- Mobile overlay -->
  <div x-show="mobileOpen" x-cloak x-transition.opacity class="fixed inset-0 bg-black/60 z-40 lg:hidden" @click="mobileOpen=false"></div>

  <!-- Sidebar -->
  <aside class="fixed inset-y-0 left-0 z-50 flex flex-col bg-sidebar border-r border-gray-800/80 transition-[width,transform] duration-300 lg:sticky lg:top-0 lg:h-screen w-64 lg:translate-x-0"
         :class="[ collapsed ? 'lg:w-20' : 'lg:w-64', mobileOpen ? 'translate-x-0' : '-translate-x-full' ]" x-data="{}">
    <!-- Brand -->
    <div class="flex items-center gap-2.5 px-4 h-16 border-b border-gray-800/80 shrink-0">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0" data-testid="sidebar-brand">
        @if(\App\Support\Brand::logo())
          <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::name() }}" class="h-8 max-w-[150px] object-contain">
        @else
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500 text-[#070A10] font-extrabold shrink-0">{{ \App\Support\Brand::initials() }}</span>
          <span x-show="!collapsed" class="font-heading text-lg font-extrabold tracking-wide truncate leading-none">
            {{ \App\Support\Brand::wordmark()[0] }}<span class="text-amber-500">·</span>{{ \App\Support\Brand::wordmark()[1] }}
            <span class="block text-[.6rem] font-mono tracking-[.25em] text-slate-500 uppercase mt-0.5">DOOH Console</span>
          </span>
        @endif
      </a>
      <button @click="collapsed=!collapsed" class="ml-auto hidden lg:inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:text-white hover:bg-white/5" :class="collapsed && 'mx-auto ml-0'" data-testid="sidebar-collapse-toggle" aria-label="Collapse sidebar">
        <i data-lucide="chevrons-left" class="w-4 h-4 transition-transform" :class="collapsed && 'rotate-180'"></i>
      </button>
      <button @click="mobileOpen=false" class="ml-auto lg:hidden h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-white/5" aria-label="Close menu"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto py-3 px-2.5 space-y-1">
      @foreach($nav as $group)
        @php $groupActive = collect($group['items'])->contains(fn($it)=> request()->routeIs(str_replace('.index','',$it['route']).'*')); @endphp
        <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }" class="pt-1">
          <button x-show="!collapsed" @click="open=!open" class="grp-btn w-full flex items-center gap-2 px-2.5 py-1.5 text-[.62rem] font-mono uppercase tracking-widest text-slate-500" :aria-expanded="open" data-testid="nav-group-{{ \Illuminate\Support\Str::slug($group['group']) }}">
            <i data-lucide="{{ $groupIcons[$group['group']] ?? 'circle' }}" class="w-3.5 h-3.5 {{ $groupActive ? 'text-amber-500' : '' }}"></i>
            <span class="{{ $groupActive ? 'text-amber-500' : '' }}">{{ $group['group'] }}</span>
            <i data-lucide="chevron-down" class="w-3.5 h-3.5 ml-auto transition-transform" :class="open && 'rotate-180'"></i>
          </button>
          <div x-show="collapsed || open" x-collapse.duration.200ms class="mt-0.5 space-y-0.5">
            @foreach($group['items'] as $it)
              @php $isActive = request()->routeIs(str_replace('.index','',$it['route']).'*'); @endphp
              <a href="{{ route($it['route']) }}" data-testid="nav-{{ $it['route'] }}" title="{{ $it['label'] }}"
                 class="nav-a flex items-center gap-3 px-2.5 py-2 text-[.85rem] text-slate-400 hover:text-white hover:bg-white/5 {{ $isActive ? 'active' : '' }}"
                 :class="collapsed && 'justify-center px-0'">
                <i data-lucide="{{ $it['icon'] }}" class="w-[1.05rem] h-[1.05rem] shrink-0"></i>
                <span x-show="!collapsed" class="truncate">{{ $it['label'] }}</span>
                @if(($it['route'] ?? '')==='dashboard')<span x-show="!collapsed" class="ml-auto pill bg-emerald-500/15 text-emerald-400"><span class="live-dot"></span>LIVE</span>@endif
              </a>
            @endforeach
          </div>
        </div>
      @endforeach
    </nav>

    <!-- Sidebar footer -->
    <div class="border-t border-gray-800/80 p-3 shrink-0">
      <a href="{{ route('search') }}" x-show="collapsed" class="hidden"></a>
      <div class="flex items-center gap-2.5 px-1" :class="collapsed && 'justify-center'">
        <span class="h-9 w-9 rounded-lg bg-slate-800 flex items-center justify-center text-sm font-semibold shrink-0">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
        <div x-show="!collapsed" class="min-w-0">
          <div class="text-sm font-semibold truncate leading-tight">{{ auth()->user()->name }}</div>
          <div class="text-[.62rem] font-mono uppercase tracking-wider text-amber-500 truncate">{{ auth()->user()->roles->first()?->name ?? 'User' }}</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main column -->
  <div class="flex-1 min-w-0 flex flex-col min-h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-30 h-16 bg-[#0B0F17]/90 backdrop-blur border-b border-gray-800/80 px-4 md:px-6 flex items-center gap-3">
      <button @click="mobileOpen=true" class="lg:hidden p-2 rounded-lg text-slate-300 hover:bg-white/5" data-testid="mobile-sidebar-open" aria-label="Open menu"><i data-lucide="menu" class="w-5 h-5"></i></button>

      <!-- Breadcrumb -->
      <div class="hidden md:flex items-center gap-2 text-sm shrink-0">
        <i data-lucide="{{ $groupIcons[$activeGroupLabel] ?? 'layout-dashboard' }}" class="w-4 h-4 text-amber-500"></i>
        <span class="text-slate-500">{{ $activeGroupLabel }}</span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600"></i>
        <span class="text-white font-semibold">{{ $activeLabel }}</span>
      </div>

      <!-- Search -->
      <form action="{{ route('search') }}" method="GET" class="flex-1 max-w-md ml-auto">
        <div class="relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-500"></i>
          <input name="q" value="{{ request('q') }}" x-ref="globalSearch" data-testid="global-search-input" placeholder="Search autos, drivers, campaigns…"
                 class="inp pl-9 pr-16" autocomplete="off">
          <kbd class="absolute right-2.5 top-2 hidden sm:inline-flex items-center gap-0.5 rounded border border-white/10 bg-white/5 px-1.5 py-0.5 text-[.6rem] font-mono text-slate-500">⌘K</kbd>
        </div>
      </form>

      <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
        <!-- Quick create -->
        <div x-data="{open:false}" class="relative">
          <button @click="open=!open" data-testid="quick-create-btn" class="btn btn-primary"><i data-lucide="plus" class="w-4 h-4"></i><span class="hidden sm:inline">Create</span></button>
          <div x-show="open" @click.away="open=false" x-cloak x-transition class="absolute right-0 mt-2 w-56 card p-2 text-sm z-50 shadow-2xl">
            <div class="px-2 pb-1.5 text-[.6rem] font-mono uppercase tracking-widest text-slate-500">Quick Actions</div>
            @foreach([['Auto','autos.create','car'],['Owner','owners.create','user-round'],['Driver','drivers.create','steering-wheel'],['Advertiser','advertisers.create','briefcase'],['Advertisement','advertisements.create','image'],['Campaign','campaigns.create','megaphone'],['Invoice','invoices.create','file-text'],['Expense','expenses.create','receipt'],['Maintenance Ticket','maintenance.create','life-buoy'],['Settlement','settlements.create','banknote']] as $qc)
              @if(Route::has($qc[1]))<a href="{{ route($qc[1]) }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg hover:bg-white/5 text-slate-300" data-testid="qc-{{ $qc[1] }}"><i data-lucide="{{ $qc[2] }}" class="w-4 h-4 text-slate-500"></i>{{ $qc[0] }}</a>@endif
            @endforeach
          </div>
        </div>

        <!-- Help drawer trigger -->
        <button @click="help=true" data-testid="help-drawer-trigger" class="hidden sm:inline-flex p-2 rounded-lg text-slate-300 hover:bg-white/5 hover:text-amber-400" aria-label="Help"><i data-lucide="life-buoy" class="w-5 h-5"></i></button>

        <!-- Bell -->
        <div x-data="{open:false,items:[],unread:{{ $unread }}}" class="relative">
          <button @click="open=!open; if(open){fetch('{{ route('notifications.dropdown') }}').then(r=>r.json()).then(d=>{items=d.items;unread=d.unread})}" data-testid="notif-bell" class="relative p-2 rounded-lg text-slate-300 hover:bg-white/5">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span x-show="unread>0" x-text="unread" class="absolute -top-0.5 -right-0.5 bg-rose-500 text-white text-[.6rem] rounded-full h-4 min-w-4 px-1 flex items-center justify-center"></span>
          </button>
          <div x-show="open" @click.away="open=false" x-cloak x-transition class="absolute right-0 mt-2 w-80 card z-50 shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2.5 border-b border-white/10 bg-[#090D14]"><span class="font-semibold text-sm">Notifications</span>
              <form action="{{ route('notifications.readAll') }}" method="POST">@csrf<button class="text-xs text-amber-500 hover:text-amber-400" data-testid="notif-readall">Mark all read</button></form></div>
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
            <a href="{{ route('notifications.index') }}" class="block text-center text-xs text-amber-500 py-2 border-t border-white/10 hover:text-amber-400">View all</a>
          </div>
        </div>

        <!-- User -->
        <div x-data="{open:false}" class="relative">
          <button @click="open=!open" data-testid="user-menu" class="flex items-center gap-2 p-1 pr-2 rounded-lg hover:bg-white/5">
            <span class="relative h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name,0,1)) }}<span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 border-2 border-[#0B0F17]"></span></span>
            <span class="text-sm hidden sm:block">{{ auth()->user()->name }}</span>
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 hidden sm:block"></i>
          </button>
          <div x-show="open" @click.away="open=false" x-cloak x-transition class="absolute right-0 mt-2 w-52 card p-2 text-sm z-50 shadow-2xl">
            <div class="px-3 py-2 border-b border-white/10 mb-1">
              <div class="font-semibold truncate">{{ auth()->user()->name }}</div>
              <div class="text-[.62rem] font-mono uppercase tracking-wider text-amber-500">{{ auth()->user()->roles->first()?->name }}</div>
            </div>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-white/5 text-slate-300"><i data-lucide="settings" class="w-4 h-4 text-slate-500"></i>Settings</a>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-rose-500/10 text-rose-400" data-testid="logout-btn"><i data-lucide="log-out" class="w-4 h-4"></i>Sign out</button></form>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-1 p-4 md:p-6 fade-up">
      @if(session('success'))<div data-testid="flash-success" class="mb-4 card border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300 flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ session('success') }}</div>@endif
      @if(session('error'))<div data-testid="flash-error" class="mb-4 card border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300 flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4"></i>{{ session('error') }}</div>@endif
      @if($errors->any())<div class="mb-4 card border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      @yield('content')
    </main>
  </div>

  <!-- Contextual Help Drawer -->
  <div x-show="help" x-cloak class="fixed inset-0 z-[70]" @keydown.escape.window="help=false">
    <div class="absolute inset-0 bg-black/60" x-transition.opacity @click="help=false"></div>
    <div class="absolute right-0 top-0 h-full w-96 max-w-full bg-[#0B0F17] border-l border-gray-800 shadow-2xl overflow-y-auto"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
      <div class="flex items-center justify-between px-5 h-16 border-b border-gray-800 sticky top-0 bg-[#0B0F17] z-10">
        <div class="flex items-center gap-2"><i data-lucide="life-buoy" class="w-5 h-5 text-amber-500"></i><span class="font-heading text-lg font-bold">Help & Shortcuts</span></div>
        <button @click="help=false" class="p-2 rounded-lg text-slate-400 hover:bg-white/5" data-testid="help-drawer-close" aria-label="Close help"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>
      <div class="p-5 space-y-6">
        <div>
          <div class="text-[.62rem] font-mono uppercase tracking-widest text-amber-500 mb-2">Module Guide — {{ $activeGroupLabel }}</div>
          <p class="text-sm text-slate-400 leading-relaxed">You're viewing <span class="text-slate-200 font-medium">{{ $activeLabel }}</span>. Use the filters and search to narrow records, the <span class="text-amber-400">Create</span> button to add entities, and row actions to view, edit or remove items.</p>
        </div>
        <div>
          <div class="text-[.62rem] font-mono uppercase tracking-widest text-amber-500 mb-2">Keyboard Shortcuts</div>
          <div class="space-y-1.5 text-sm">
            @foreach([['⌘ / Ctrl + K','Focus global search'],['Alt + H','Toggle this help drawer'],['Esc','Close menus & drawers']] as $sc)
              <div class="flex items-center justify-between py-1.5 border-b border-white/5"><span class="text-slate-300">{{ $sc[1] }}</span><kbd class="rounded border border-white/10 bg-white/5 px-2 py-0.5 text-[.65rem] font-mono text-slate-300">{{ $sc[0] }}</kbd></div>
            @endforeach
          </div>
        </div>
        <div>
          <div class="text-[.62rem] font-mono uppercase tracking-widest text-amber-500 mb-2">DOOH Glossary</div>
          <dl class="space-y-2.5 text-sm">
            @foreach([['Proof of Play','Cryptographically logged evidence that an ad played on a screen at a given time & location.'],['Impressions','Estimated number of passengers/pedestrians who viewed an ad spot.'],['Ad Loop','The rotating playlist of ads shown on a screen over a fixed cycle.'],['Settlement','Periodic payout run to drivers/owners based on verified playback.']] as $t)
              <div><dt class="text-slate-200 font-semibold">{{ $t[0] }}</dt><dd class="text-slate-400 text-[.82rem] leading-relaxed">{{ $t[1] }}</dd></div>
            @endforeach
          </dl>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-sec w-full justify-center" data-testid="help-support-btn"><i data-lucide="headset" class="w-4 h-4"></i>Contact NOC Support</a>
      </div>
    </div>
  </div>
</div>

<script>lucide.createIcons();document.addEventListener('alpine:initialized',()=>lucide.createIcons());
  document.addEventListener('DOMContentLoaded',()=>{const t=document.querySelector('meta[name=csrf-token]').content;
   document.querySelectorAll('form[method=POST],form[method=post]').forEach(f=>{if(!f.querySelector('input[name=_token]')){const i=document.createElement('input');i.type='hidden';i.name='_token';i.value=t;f.appendChild(i)}})});</script>
@stack('scripts')
</body>
</html>
