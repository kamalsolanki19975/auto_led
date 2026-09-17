@extends('layouts.app')@section('title',$title)@section('content')
<div class="flex items-center justify-between mb-6"><div><a href="{{ route('reports.index') }}" class="text-sm text-slate-400">← Reports</a><h1 class="text-3xl font-bold mt-1">{{ $title }}</h1></div>
<a href="{{ route('reports.show',$type) }}?export=csv" class="btn btn-sec"><i data-lucide="download" class="w-4 h-4"></i>Export CSV</a></div>
<div class="card overflow-hidden"><table class="grid"><thead><tr>@foreach($columns as $c)<th>{{ $c }}</th>@endforeach</tr></thead><tbody>
@forelse($rows as $r)<tr>@foreach($r as $cell)<td class="text-slate-300">{{ $cell }}</td>@endforeach</tr>@empty<tr><td colspan="{{ max(1,count($columns)) }}" class="text-center py-12 text-slate-500">No data</td></tr>@endforelse
</tbody></table></div>@endsection
