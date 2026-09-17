@extends('layouts.app')@section('title','Profitability')@section('content')
@php($m=fn($n)=>\App\Support\Fmt::money($n))
<h1 class="text-3xl font-bold mb-6">Profitability & MIS</h1>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
@foreach([['Revenue',$company['revenue'],'emerald'],['Expenses',$company['expenses'],'rose'],['Net',$company['net'],$company['net']>=0?'emerald':'rose'],['Margin',$company['margin'].'%','sky']] as $k)
<div class="card p-4"><span class="text-xs text-slate-400 uppercase">{{ $k[0] }}</span><div class="mono text-2xl font-bold text-{{ $k[2] }}-400">{{ is_numeric($k[1])?$m($k[1]):$k[1] }}</div></div>@endforeach
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Campaign Contribution</h3><table class="grid"><thead><tr><th>Campaign</th><th>Revenue</th><th>Cost</th><th>Contribution</th></tr></thead><tbody>
@foreach($campaigns as $c)<tr><td>{{ $c['campaign']->name }}</td><td class="mono">{{ $m($c['p']['revenue']) }}</td><td class="mono">{{ $m($c['p']['totalCost']) }}</td><td class="mono {{ $c['p']['contribution']>=0?'text-emerald-400':'text-rose-400' }}">{{ $m($c['p']['contribution']) }}</td></tr>@endforeach
</tbody></table></div>
<div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Auto Contribution</h3><table class="grid"><thead><tr><th>Auto</th><th>Revenue</th><th>Cost</th><th>Contribution</th></tr></thead><tbody>
@foreach($autos as $a)<tr><td>{{ $a['auto']->registration_number }}</td><td class="mono">{{ $m($a['p']['revenue']) }}</td><td class="mono">{{ $m($a['p']['totalCost']) }}</td><td class="mono {{ $a['p']['contribution']>=0?'text-emerald-400':'text-rose-400' }}">{{ $m($a['p']['contribution']) }}</td></tr>@endforeach
</tbody></table></div>
</div>@endsection
