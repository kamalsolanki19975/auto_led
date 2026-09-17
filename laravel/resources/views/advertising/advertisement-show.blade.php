@extends('layouts.app')
@section('title',$model->title)
@section('content')
<div class="flex items-center justify-between mb-6"><div><a href="{{ route('advertisements.index') }}" class="text-sm text-slate-400">← Advertisements</a><h1 class="text-3xl font-bold mt-1">{{ $model->title }}</h1><p class="text-slate-500 text-sm">{{ $model->advertiser?->company_name }} · {{ $model->content_type }} · {{ $model->duration }}s</p></div><x-badge :status="$model->approval_status"/></div>
<div class="flex gap-2 mb-6">
  @if($model->approval_status=='draft')<form method="POST" action="{{ route('advertisements.submit',$model) }}">@csrf<button class="btn btn-primary">Submit for Review</button></form>@endif
  @if(in_array($model->approval_status,['submitted','under_review']))
    <form method="POST" action="{{ route('advertisements.approve',$model) }}">@csrf<button class="btn btn-primary" data-testid="approve-ad">Approve</button></form>
    <form method="POST" action="{{ route('advertisements.reject',$model) }}" class="flex gap-2">@csrf<input name="reason" placeholder="Rejection reason" class="inp" required><button class="btn btn-danger">Reject</button></form>
  @endif
</div>
<div class="card p-6 max-w-2xl"><div class="grid grid-cols-2 gap-4 text-sm">
  @foreach(['code'=>'Code','content_type'=>'Type','duration'=>'Duration','resolution'=>'Resolution','orientation'=>'Orientation','category'=>'Category','version'=>'Version'] as $k=>$l)<div><span class="text-slate-400 text-xs uppercase">{{ $l }}</span><div>{{ $model->$k ?? '—' }}</div></div>@endforeach
</div>
@if($model->rejection_reason)<div class="mt-4 rounded bg-rose-500/10 border border-rose-500/30 px-4 py-2 text-sm text-rose-300">Rejected: {{ $model->rejection_reason }}</div>@endif
@if($model->description)<p class="mt-4 text-slate-300 text-sm">{{ $model->description }}</p>@endif
</div>
@endsection
