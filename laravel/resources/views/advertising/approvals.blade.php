@extends('layouts.app')
@section('title','Approval Queue')
@section('content')
<h1 class="text-3xl font-bold mb-6">Advertisement Approval Queue</h1>
<div class="card overflow-hidden"><table class="grid"><thead><tr><th>Code</th><th>Title</th><th>Advertiser</th><th>Type</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
@forelse($rows as $ad)<tr><td class="mono">{{ $ad->code }}</td><td class="font-semibold">{{ $ad->title }}</td><td>{{ $ad->advertiser?->company_name }}</td><td>{{ $ad->content_type }}</td><td><x-badge :status="$ad->approval_status"/></td>
<td class="text-right whitespace-nowrap"><a href="{{ route('advertisements.show',$ad) }}" class="text-amber-400 text-xs mr-2">Review</a>
<form method="POST" action="{{ route('advertisements.approve',$ad) }}" class="inline">@csrf<button class="text-emerald-400 text-xs">Approve</button></form></td></tr>
@empty<tr><td colspan="6" class="text-center py-12 text-slate-500">Approval queue is empty. 🎉</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
