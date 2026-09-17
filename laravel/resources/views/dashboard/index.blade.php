@extends('layouts.app')
@section('title','Dashboard · AutoAds Network')
@section('content')
@php($m=fn($n)=>\App\Support\Fmt::moneyShort($n))
<div class="flex items-center justify-between mb-6">
  <div><h1 class="text-3xl font-bold">Command Center</h1><p class="text-slate-400 text-sm">Live network, advertising & finance overview</p></div>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-4 gap-4 mb-6">
  @foreach([
    ['Total Autos',$kpis['total_autos'],'car','sky'],['Active Autos',$kpis['active_autos'],'circle-check','emerald'],
    ['Screens Online',$kpis['screens_online'],'monitor','emerald'],['Screens Offline',$kpis['screens_offline'],'monitor-off','rose'],
    ['Active Campaigns',$kpis['active_campaigns'],'megaphone','amber'],['Active SIMs',$kpis['active_sims'],'signal','sky'],
    ['Devices Active',$kpis['devices_active'],'cpu','emerald'],['Devices Offline',$kpis['devices_offline'],'cpu','rose'],
  ] as $k)
  <div class="card p-4" data-testid="kpi-{{ \Illuminate\Support\Str::slug($k[0]) }}">
    <div class="flex items-center justify-between"><span class="text-xs text-slate-400 uppercase tracking-wide">{{ $k[0] }}</span><i data-lucide="{{ $k[2] }}" class="w-4 h-4 text-{{ $k[3] }}-400"></i></div>
    <div class="mono text-3xl font-bold mt-2">{{ number_format($k[1]) }}</div>
  </div>
  @endforeach
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  @foreach([['Today Revenue',$kpis['today_revenue']],['Monthly Revenue',$kpis['monthly_revenue']],['Monthly Expenses',$kpis['monthly_expenses']],['Current Profit',$kpis['current_profit']],['Driver Payable',$kpis['driver_payable']],['Receivables',$kpis['outstanding_receivables']],['Payables',$kpis['payables']],['Screens Installed',$kpis['screens_installed']]] as $i=>$k)
  <div class="card p-4"><span class="text-xs text-slate-400 uppercase tracking-wide">{{ $k[0] }}</span>
    <div class="mono text-2xl font-bold mt-1 {{ $k[0]=='Current Profit'?($k[1]>=0?'text-emerald-400':'text-rose-400'):'' }}">{{ $i==7?number_format($k[1]):$m($k[1]) }}</div></div>
  @endforeach
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <div class="card p-5 lg:col-span-2"><h3 class="font-semibold mb-4">Revenue vs Expenses (6 months)</h3><canvas id="revChart" height="90"></canvas></div>
  <div class="card p-5"><h3 class="font-semibold mb-4">Operational Alerts</h3>
    <div class="space-y-2 text-sm">
      @foreach([['Offline devices',$alerts['offline_devices'],'rose'],['SIM expiring (15d)',$alerts['sim_expiring'],'amber'],['High-usage SIMs',$alerts['high_usage_sims'],'amber'],['Overdue invoices',$alerts['overdue_invoices'],'rose'],['Pending settlements',$alerts['pending_settlements'],'amber'],['Open maintenance',$alerts['maintenance_open'],'amber'],['Under-delivery',$alerts['under_delivery'],'amber'],['Warranty expiring',$alerts['warranty_expiring'],'sky']] as $a)
      <div class="flex items-center justify-between py-1.5 border-b border-white/5"><span class="text-slate-300">{{ $a[0] }}</span><span class="mono font-semibold text-{{ $a[2] }}-400">{{ $a[1] }}</span></div>
      @endforeach
    </div>
  </div>
</div>
<div class="card p-5">
  <div class="flex items-center justify-between mb-4"><h3 class="font-semibold">Active Campaigns</h3><a href="{{ route('campaigns.index') }}" class="text-xs text-amber-500">View all</a></div>
  <table class="grid"><thead><tr><th>Code</th><th>Campaign</th><th>Advertiser</th><th>Delivery</th><th>Status</th></tr></thead><tbody>
    @foreach($activeCampaigns as $c)@php($d=$deliveryService->delivery($c))
    <tr><td class="mono">{{ $c->code }}</td><td><a href="{{ route('campaigns.show',$c) }}" class="text-amber-400">{{ $c->name }}</a></td><td>{{ $c->advertiser?->company_name }}</td>
      <td><div class="flex items-center gap-2"><div class="w-24 h-1.5 bg-white/10 rounded"><div class="h-1.5 rounded bg-amber-500" style="width:{{ min(100,$d['delivery_percent']) }}%"></div></div><span class="text-xs mono">{{ $d['delivery_percent'] }}%</span></div></td>
      <td><x-badge :status="$c->status"/></td></tr>
    @endforeach
  </tbody></table>
</div>
@push('scripts')<script>
new Chart(document.getElementById('revChart'),{type:'line',data:{labels:@json($series['labels']),datasets:[
{label:'Revenue',data:@json($series['revenue']),borderColor:'#10B981',backgroundColor:'rgba(16,185,129,.1)',fill:true,tension:.4},
{label:'Expenses',data:@json($series['expenses']),borderColor:'#EF4444',backgroundColor:'rgba(239,68,68,.1)',fill:true,tension:.4}]},
options:{plugins:{legend:{labels:{color:'#9CA3AF'}}},scales:{x:{ticks:{color:'#9CA3AF'},grid:{color:'rgba(255,255,255,.05)'}},y:{ticks:{color:'#9CA3AF'},grid:{color:'rgba(255,255,255,.05)'}}}}});
</script>@endpush
@endsection
