@extends('layouts.app')
@section('title',($model->exists?'Edit':'New').' '.$singular)
@section('content')
<div class="mb-6">
  <a href="{{ route($routeBase.'.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-amber-400 transition"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $title }}</a>
  <h1 class="font-heading text-2xl sm:text-3xl font-extrabold tracking-tight uppercase mt-2">{{ $model->exists?'Edit':'New' }} {{ $singular }}</h1>
</div>

<form method="POST" action="{{ $model->exists ? route($routeBase.'.update',$model) : route($routeBase.'.store') }}" enctype="multipart/form-data" class="card p-6 md:p-8 max-w-3xl">
  @csrf @if($model->exists)@method('PUT')@endif
  <div class="flex items-center gap-2 mb-5 pb-4 border-b border-white/5"><i data-lucide="clipboard-edit" class="w-4 h-4 text-amber-500"></i><h3 class="font-heading text-lg font-bold uppercase tracking-tight">{{ $singular }} Information</h3></div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    @foreach($fields as $f)
      <div class="{{ ($f['type']??'')=='textarea'?'md:col-span-2':'' }}">
        <label class="block text-[.68rem] font-mono uppercase tracking-wider text-slate-400 mb-1.5">{{ $f['label'] }} @if($f['required']??false)<span class="text-rose-400">*</span>@endif</label>
        @php($val=old($f['name'],$model->{$f['name']} ?? ''))
        @if(($f['type']??'text')=='select')
          <select name="{{ $f['name'] }}" class="inp" data-testid="field-{{ $f['name'] }}">
            <option value="">— Select —</option>
            @foreach(($options[$f['name']]??[]) as $k=>$lbl)<option value="{{ $k }}" @selected((string)$val===(string)$k)>{{ $lbl }}</option>@endforeach
          </select>
        @elseif(($f['type']??'')=='textarea')
          <textarea name="{{ $f['name'] }}" rows="3" class="inp" data-testid="field-{{ $f['name'] }}">{{ $val }}</textarea>
        @else
          <input type="{{ $f['type']=='number'?'number':($f['type']=='date'?'date':($f['type']=='email'?'email':'text')) }}" step="any" name="{{ $f['name'] }}" value="{{ $f['type']=='date' && $val ? \Illuminate\Support\Str::of($val)->substr(0,10) : $val }}" class="inp" data-testid="field-{{ $f['name'] }}">
        @endif
      </div>
    @endforeach
    @if($routeBase=='advertisements')<div class="md:col-span-2"><label class="block text-[.68rem] font-mono uppercase tracking-wider text-slate-400 mb-1.5">Media File (optional)</label><input type="file" name="media" class="inp file:mr-3 file:rounded-md file:border-0 file:bg-amber-500 file:px-3 file:py-1 file:text-black file:font-semibold"></div>@endif
  </div>
  <div class="mt-7 flex gap-3 pt-5 border-t border-white/5">
    <button class="btn btn-primary" data-testid="save-{{ $routeBase }}"><i data-lucide="check" class="w-4 h-4"></i>Save {{ $singular }}</button>
    <a href="{{ route($routeBase.'.index') }}" class="btn btn-sec">Cancel</a>
  </div>
</form>
@endsection
