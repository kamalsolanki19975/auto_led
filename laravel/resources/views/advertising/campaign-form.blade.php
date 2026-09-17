@extends('layouts.app')
@section('title',$model->exists?'Edit Campaign':'New Campaign')
@section('content')
<div class="mb-6"><a href="{{ route('campaigns.index') }}" class="text-sm text-slate-400">← Campaigns</a><h1 class="text-3xl font-bold mt-1">{{ $model->exists?'Edit':'New' }} Campaign</h1></div>
<form method="POST" action="{{ $model->exists?route('campaigns.update',$model):route('campaigns.store') }}" class="card p-6 max-w-3xl">@csrf @if($model->exists)@method('PUT')@endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <div><label class="text-xs text-slate-400 uppercase">Advertiser *</label><select name="advertiser_id" class="inp mt-1" required>@foreach($advertisers as $id=>$n)<option value="{{ $id }}" @selected($model->advertiser_id==$id)>{{ $n }}</option>@endforeach</select></div>
  <div><label class="text-xs text-slate-400 uppercase">Name *</label><input name="name" value="{{ old('name',$model->name) }}" class="inp mt-1" required></div>
  <div><label class="text-xs text-slate-400 uppercase">Type</label><select name="campaign_type" class="inp mt-1">@foreach(['paid','house','emergency'] as $t)<option value="{{ $t }}" @selected($model->campaign_type==$t)>{{ ucfirst($t) }}</option>@endforeach</select></div>
  <div><label class="text-xs text-slate-400 uppercase">Pricing Model</label><select name="pricing_model" class="inp mt-1">@foreach(['per_play','cpm','per_day','per_hour','fixed'] as $t)<option value="{{ $t }}" @selected($model->pricing_model==$t)>{{ ucwords(str_replace('_',' ',$t)) }}</option>@endforeach</select></div>
  <div><label class="text-xs text-slate-400 uppercase">Start Date</label><input type="date" name="start_date" value="{{ optional($model->start_date)->format('Y-m-d') }}" class="inp mt-1"></div>
  <div><label class="text-xs text-slate-400 uppercase">End Date</label><input type="date" name="end_date" value="{{ optional($model->end_date)->format('Y-m-d') }}" class="inp mt-1"></div>
  <div><label class="text-xs text-slate-400 uppercase">Budget</label><input type="number" step="any" name="budget" value="{{ old('budget',$model->budget) }}" class="inp mt-1"></div>
  <div><label class="text-xs text-slate-400 uppercase">Rate</label><input type="number" step="any" name="rate" value="{{ old('rate',$model->rate) }}" class="inp mt-1"></div>
  <div><label class="text-xs text-slate-400 uppercase">Target Type</label><select name="target_type" class="inp mt-1">@foreach(['autos','groups','areas','city','all'] as $t)<option value="{{ $t }}" @selected($model->target_type==$t)>{{ ucfirst($t) }}</option>@endforeach</select></div>
  <div><label class="text-xs text-slate-400 uppercase">Expected Plays</label><input type="number" name="expected_plays" value="{{ old('expected_plays',$model->expected_plays) }}" class="inp mt-1"></div>
  <div class="md:col-span-2"><label class="text-xs text-slate-400 uppercase">Advertisements (approved)</label>
    <select name="advertisements[]" multiple class="inp mt-1 h-28">@foreach($ads as $a)<option value="{{ $a->id }}" @selected($model->exists && $model->advertisements->contains($a->id))>{{ $a->title }} ({{ $a->advertiser?->company_name }})</option>@endforeach</select></div>
  <div class="md:col-span-2"><label class="text-xs text-slate-400 uppercase">Notes</label><textarea name="notes" class="inp mt-1" rows="2">{{ old('notes',$model->notes) }}</textarea></div>
</div>
<div class="mt-6 flex gap-3"><button class="btn btn-primary" data-testid="save-campaign">Save Campaign</button><a href="{{ route('campaigns.index') }}" class="btn btn-sec">Cancel</a></div>
</form>
@endsection
