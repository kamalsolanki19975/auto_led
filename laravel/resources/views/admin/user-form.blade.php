@extends('layouts.app')
@section('title',($model->exists?'Edit':'New').' User')
@section('content')
<div class="mb-6"><a href="{{ route('users.index') }}" class="text-sm text-slate-400">← Users</a>
<h1 class="text-3xl font-bold mt-1">{{ $model->exists?'Edit':'New' }} User</h1></div>
<form method="POST" action="{{ $model->exists ? route('users.update',$model) : route('users.store') }}" class="card p-6 max-w-3xl">
  @csrf @if($model->exists)@method('PUT')@endif
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="text-xs text-slate-400 uppercase tracking-wide">Name <span class="text-rose-400">*</span></label>
      <input name="name" value="{{ old('name',$model->name) }}" class="inp mt-1" data-testid="field-name" required>
    </div>
    <div>
      <label class="text-xs text-slate-400 uppercase tracking-wide">Email <span class="text-rose-400">*</span></label>
      <input type="email" name="email" value="{{ old('email',$model->email) }}" class="inp mt-1" data-testid="field-email" required>
    </div>
    <div>
      <label class="text-xs text-slate-400 uppercase tracking-wide">Password @if(!$model->exists)<span class="text-rose-400">*</span>@else<span class="text-slate-500 normal-case">(leave blank to keep)</span>@endif</label>
      <input type="password" name="password" class="inp mt-1" data-testid="field-password" {{ $model->exists?'':'required' }}>
    </div>
    <div>
      <label class="text-xs text-slate-400 uppercase tracking-wide">Status <span class="text-rose-400">*</span></label>
      <select name="status" class="inp mt-1" data-testid="field-status">
        @foreach(['active','inactive'] as $st)<option value="{{ $st }}" @selected(old('status',$model->status ?? 'active')===$st)>{{ ucfirst($st) }}</option>@endforeach
      </select>
    </div>
  </div>
  <div class="mt-6">
    <label class="text-xs text-slate-400 uppercase tracking-wide">Roles</label>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2">
      @foreach($roles as $r)
        @php($checked = in_array($r->id, old('roles', $model->exists ? $model->roles->pluck('id')->toArray() : [])))
        <label class="flex items-center gap-2 card px-3 py-2 cursor-pointer hover:bg-white/5">
          <input type="checkbox" name="roles[]" value="{{ $r->id }}" @checked($checked) data-testid="role-{{ $r->slug }}" class="accent-amber-500">
          <span class="text-sm">{{ $r->name }} <span class="text-slate-500 text-xs">· {{ $r->portal }}</span></span>
        </label>
      @endforeach
    </div>
  </div>
  <div class="mt-6 flex gap-3"><button class="btn btn-primary" data-testid="save-users">Save User</button><a href="{{ route('users.index') }}" class="btn btn-sec">Cancel</a></div>
</form>
@endsection
