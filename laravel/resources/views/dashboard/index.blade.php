@extends('layouts.app')
@section('title','Command Center · AutoAds Network')
@section('content')
@php
  $m=fn($n)=>\App\Support\Fmt::moneyShort($n);
  $pct=fn($a,$b)=> $b>0 ? round($a/$b*100,1) : 0;
  $onlineTotal = ($kpis['screens_online'] ?? 0) + ($kpis['screens_offline'] ?? 0);
  $devTotal = ($kpis['devices_active'] ?? 0) + ($kpis['devices_offline'] ?? 0);
@endphp

<!-- Page head -->
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
  <div>
    <div class="flex items-center gap-2 text-[.62rem] font-mono uppercase tracking-widest text-amber-500 mb-1"><span class="live-dot"></span>Live Network Feed</div>
    <h1 class="font-heading text-3xl sm:text-4xl font-extrabold uppercase tracking-tight leading-none">Command Center</h1>
    <p class="text-slate-400 text-sm mt-1.5">Real-time network, advertising &amp; financial telemetry</p>
  </div>
  <div class="flex items-center gap-2">
    <span class="pill bg-white/5 text-slate-400 border border-white/10 px-3 py-1.5">{{ now()->format('D, d M Y · H:i') }}</span>
    <a href="{{ route('reports.index') }}" class="btn btn-sec" data-testid="dash-reports-btn"><i data-lucide="bar-chart-3" class="w-4 h-4"></i>Reports</a>
  </div>
</div>

<!-- Operational KPIs -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
  @php
    $opCards = [
      ['Total Autos',$kpis['total_autos'],'car','sky','Registered fleet'],
      ['Active Autos',$kpis['active_autos'],'navigation','emerald',$pct($kpis['active_autos'],$kpis['total_autos']).'% on road'],
      ['Screens Online',$kpis['screens_online'],'monitor','emerald',($kpis['screens_offline']).' offline · '.$pct($kpis['screens_online'],$onlineTotal).'%'],
      ['Active Campaigns',$kpis['active_campaigns'],'target','amber','Running ad spots'],
      ['Active SIMs',$kpis['active_sims'],'wifi','sky','4G LTE connected'],
      ['Devices Active',$kpis['devices_active'],'cpu','emerald',$pct($kpis['devices_active'],$devTotal).'% uptime'],
      ['Devices Offline',$kpis['devices_offline'],'alert-triangle',($kpis['devices_offline']>0?'rose':'emerald'),$kpis['devices_offline']>0?'Needs attention':'All healthy'],
      ['Screens Installed',$kpis['screens_installed'],'hard-drive','sky','Total deployed'],
    ];
  @endphp
  @foreach($opCards as $k)
  <div class="card card-hover p-4" data-testid="kpi-{{ \Illuminate\Support\Str::slug($k[0]) }}">
    <div class="flex items-start justify-between">
      <span class="text-[.68rem] font-mono uppercase tracking-wider text-slate-400">{{ $k[0] }}</span>
      <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $k[3] }}-500/10 text-{{ $k[3] }}-400"><i data-lucide="{{ $k[2] }}" class="w-4 h-4"></i></span>
    </div>
    <div class="mono text-2xl sm:text-3xl font-extrabold mt-2 tracking-tight">{{ number_format($k[1]) }}</div>
    <div class="text-[.72rem] text-slate-500 mt-1 flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-{{ $k[3] }}-500"></span>{{ $k[4] }}</div>
  </div>
  @endforeach
</div>

<!-- Financial KPIs -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  @php
    $finCards = [
      ['Today Revenue',$m($kpis['today_revenue']),'indian-rupee','emerald'],
      ['Monthly Revenue',$m($kpis['monthly_revenue']),'trending-up','emerald'],
      ['Monthly Expenses',$m($kpis['monthly_expenses']),'receipt','rose'],
      ['Net Profit',$m($kpis['current_profit']),'pie-chart',($kpis['current_profit']>=0?'emerald':'rose')],
      ['Driver Payable',$m($kpis['driver_payable']),'wallet','amber'],
      ['Receivables',$m($kpis['outstanding_receivables']),'arrow-down-left','sky'],
      ['Payables',$m($kpis['payables']),'arrow-up-right','amber'],
      ['Profit Margin',($kpis['monthly_revenue']>0?round($kpis['current_profit']/$kpis['monthly_revenue']*100,1):0).'%','percent',($kpis['current_profit']>=0?'emerald':'rose')],
    ];
  @endphp
  @foreach($finCards as $k)
  <div class="card p-4" data-testid="fin-{{ \Illuminate\Support\Str::slug($k[0]) }}">
    <div class="flex items-center justify-between">
      <span class="text-[.68rem] font-mono uppercase tracking-wider text-slate-400">{{ $k[0] }}</span>
      <i data-lucide="{{ $k[2] }}" class="w-4 h-4 text-{{ $k[3] }}-400"></i>
    </div>
    <div class="mono text-xl sm:text-2xl font-bold mt-1.5 text-{{ $k[3] }}-400">{{ $k[1] }}</div>
  </div>
  @endforeach
</div>

<!-- Chart + Alerts -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <div class="card p-5 lg:col-span-2">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-heading text-lg font-bold uppercase tracking-tight">Revenue vs Operating Expenses</h3>
      <span class="pill bg-white/5 text-slate-400 border border-white/10 px-2.5 py-1">Past 6 months</span>
    </div>
    <div class="h-72"><canvas id="revChart"></canvas></div>
  </div>
  <div class="card p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-heading text-lg font-bold uppercase tracking-tight">Tactical Alerts</h3>
      <i data-lucide="siren" class="w-4 h-4 text-amber-500"></i>
    </div>
    <div class="space-y-1.5 text-sm">
      @php
        $alertRows = [
          ['Offline devices',$alerts['offline_devices'],'critical','cpu','devices.index'],
          ['SIM expiring (15d)',$alerts['sim_expiring'],'warning','wifi','sims.index'],
          ['High-usage SIMs',$alerts['high_usage_sims'],'warning','signal','sims.index'],
          ['Overdue invoices',$alerts['overdue_invoices'],'critical','file-text','invoices.index'],
          ['Pending settlements',$alerts['pending_settlements'],'warning','banknote','settlements.index'],
          ['Open maintenance',$alerts['maintenance_open'],'warning','life-buoy','maintenance.index'],
          ['Under-delivery',$alerts['under_delivery'],'warning','target','campaigns.index'],
          ['Warranty expiring',$alerts['warranty_expiring'],'info','shield-check','warranties.index'],
        ];
        $sevColor = ['critical'=>'rose','warning'=>'amber','info'=>'sky'];
      @endphp
      @foreach($alertRows as $a)
        @php $c = $a[1]>0 ? $sevColor[$a[2]] : 'slate'; @endphp
        <a href="{{ Route::has($a[4]) ? route($a[4]) : '#' }}" class="flex items-center gap-3 py-2 px-2 -mx-2 rounded-lg hover:bg-white/5 transition" data-testid="alert-{{ \Illuminate\Support\Str::slug($a[0]) }}">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $c }}-500/10 text-{{ $c }}-400 shrink-0"><i data-lucide="{{ $a[3] }}" class="w-4 h-4"></i></span>
          <span class="text-slate-300 flex-1 truncate">{{ $a[0] }}</span>
          <span class="mono font-semibold text-{{ $c }}-400 pill bg-{{ $c }}-500/10 px-2 py-0.5">{{ $a[1] }}</span>
          <i data-lucide="chevron-right" class="w-4 h-4 text-slate-600"></i>
        </a>
      @endforeach
    </div>
  </div>
</div>

<!-- Active campaigns -->
<div class="card overflow-hidden">
  <div class="flex items-center justify-between p-5 border-b border-white/5">
    <div class="flex items-center gap-2"><i data-lucide="radio" class="w-4 h-4 text-amber-500"></i><h3 class="font-heading text-lg font-bold uppercase tracking-tight">Live Campaign Delivery</h3></div>
    <a href="{{ route('campaigns.index') }}" class="text-xs text-amber-500 hover:text-amber-400 font-semibold" data-testid="dash-campaigns-viewall">View all →</a>
  </div>
  <div class="overflow-x-auto">
  <table class="grid"><thead><tr><th>Code</th><th>Campaign</th><th>Advertiser</th><th>Delivery Progress</th><th>Status</th></tr></thead><tbody>
    @forelse($activeCampaigns as $c)@php($d=$deliveryService->delivery($c))
    <tr>
      <td><span class="mono text-xs text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">{{ $c->code }}</span></td>
      <td><a href="{{ route('campaigns.show',$c) }}" class="font-semibold text-white hover:text-amber-400">{{ $c->name }}</a></td>
      <td class="text-slate-400">{{ $c->advertiser?->company_name ?? '—' }}</td>
      <td>
        <div class="flex items-center gap-3">
          <div class="w-32 h-2 bg-gray-800 rounded-full overflow-hidden"><div class="h-2 rounded-full bg-gradient-to-r from-amber-500 to-emerald-400 transition-all duration-500" style="width:{{ min(100,$d['delivery_percent']) }}%"></div></div>
          <span class="text-xs mono text-slate-300">{{ $d['delivery_percent'] }}%</span>
        </div>
      </td>
      <td><x-badge :status="$c->status"/></td>
    </tr>
    @empty
    <tr><td colspan="5" class="text-center py-12 text-slate-500"><i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><div>No active campaigns right now.</div></td></tr>
    @endforelse
  </tbody></table>
  </div>
</div>

@push('scripts')<script>
new Chart(document.getElementById('revChart'),{type:'line',data:{labels:@json($series['labels']),datasets:[
{label:'Revenue',data:@json($series['revenue']),borderColor:'#F59E0B',backgroundColor:'rgba(245,158,11,.15)',fill:true,tension:.4,pointRadius:3,pointBackgroundColor:'#F59E0B'},
{label:'Expenses',data:@json($series['expenses']),borderColor:'#EF4444',backgroundColor:'rgba(239,68,68,.12)',fill:true,tension:.4,pointRadius:3,pointBackgroundColor:'#EF4444'}]},
options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#9CA3AF',font:{family:'JetBrains Mono',size:11},usePointStyle:true,padding:18}},tooltip:{backgroundColor:'#0B0F17',borderColor:'#1F2937',borderWidth:1,titleColor:'#F9FAFB',bodyColor:'#9CA3AF',padding:12,cornerRadius:10}},
scales:{x:{ticks:{color:'#9CA3AF',font:{family:'JetBrains Mono',size:10}},grid:{color:'rgba(255,255,255,.05)'}},y:{ticks:{color:'#9CA3AF',font:{family:'JetBrains Mono',size:10}},grid:{color:'rgba(255,255,255,.05)'}}}}});
</script>@endpush
@endsection
