@extends('layouts.app')
@section('title',($model->exists?'Edit':'New').' '.$singular)
@section('content')
<div class="mb-6"><a href="{{ route($routeBase.'.index') }}" class="text-sm text-slate-400">← {{ $title }}</a>
<h1 class="text-3xl font-bold mt-1">{{ $model->exists?'Edit':'New' }} {{ $singular }}</h1></div>
<form method="POST" action="{{ $model->exists ? route($routeBase.'.update',$model) : route($routeBase.'.store') }}" enctype="multipart/form-data" class="card p-6 max-w-3xl">
  @csrf @if($model->exists)@method('PUT')@endif
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($fields as $f)
      <div class="{{ ($f['type']??'')=='textarea'?'md:col-span-2':'' }}">
        <label class="text-xs text-slate-400 uppercase tracking-wide">{{ $f['label'] }} @if($f['required']??false)<span class="text-rose-400">*</span>@endif</label>
        @php($val=old($f['name'],$model->{$f['name']} ?? ''))
        @if(($f['type']??'text')=='select')
          <select name="{{ $f['name'] }}" class="inp mt-1" data-testid="field-{{ $f['name'] }}">
            <option value="">— Select —</option>
            @foreach(($options[$f['name']]??[]) as $k=>$lbl)<option value="{{ $k }}" @selected((string)$val===(string)$k)>{{ $lbl }}</option>@endforeach
          </select>
        @elseif(($f['type']??'')=='textarea')
          <textarea name="{{ $f['name'] }}" rows="3" class="inp mt-1" data-testid="field-{{ $f['name'] }}">{{ $val }}</textarea>
        @else
          <input type="{{ $f['type']=='number'?'number':($f['type']=='date'?'date':($f['type']=='email'?'email':'text')) }}" step="any" name="{{ $f['name'] }}" value="{{ $f['type']=='date' && $val ? \Illuminate\Support\Str::of($val)->substr(0,10) : $val }}" class="inp mt-1" data-testid="field-{{ $f['name'] }}">
        @endif
      </div>
    @endforeach
    @if($routeBase=='advertisements')<div class="md:col-span-2"><label class="text-xs text-slate-400 uppercase">Media File (optional)</label><input type="file" name="media" class="inp mt-1"></div>@endif
  </div>
  <div class="mt-6 flex gap-3"><button class="btn btn-primary" data-testid="save-{{ $routeBase }}">Save {{ $singular }}</button><a href="{{ route($routeBase.'.index') }}" class="btn btn-sec">Cancel</a></div>
</form>
@endsection
