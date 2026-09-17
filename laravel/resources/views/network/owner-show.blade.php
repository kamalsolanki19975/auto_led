@extends('layouts.app')
@section('title',$model->name)
@section('content')
<div class="mb-6"><a href="{{ route('owners.index') }}" class="text-sm text-slate-400">← Owners</a><h1 class="text-3xl font-bold mt-1">{{ $model->name }} <span class="mono text-slate-500 text-lg">{{ $model->code }}</span></h1></div>
<div class="card p-6 mb-6"><div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
  @foreach(['mobile'=>'Mobile','email'=>'Email','pan'=>'PAN','upi'=>'UPI'] as $k=>$l)<div><span class="text-slate-400 text-xs uppercase">{{ $l }}</span><div>{{ $model->$k ?? '—' }}</div></div>@endforeach
</div></div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Autos ({{ $autos->count() }})</h3><table class="grid"><tbody>@forelse($autos as $a)<tr><td><a class="text-amber-400" href="{{ route('autos.show',$a) }}">{{ $a->registration_number }}</a></td><td>{{ $a->primaryDriver?->name }}</td><td><x-badge :status="$a->status"/></td></tr>@empty<tr><td class="text-slate-500 py-3">None</td></tr>@endforelse</tbody></table></div>
<div class="card p-6"><h3 class="font-semibold mb-3 text-sm uppercase">Settlements</h3><table class="grid"><tbody>@forelse($settlements as $s)<tr><td><a class="text-amber-400" href="{{ route('settlements.show',$s) }}">{{ $s->code }}</a></td><td class="mono">{{ \App\Support\Fmt::money($s->net_payable) }}</td><td><x-badge :status="$s->status"/></td></tr>@empty<tr><td class="text-slate-500 py-3">None</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
