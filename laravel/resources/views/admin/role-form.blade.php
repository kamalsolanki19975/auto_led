@extends('layouts.app')
@section('title',($model->exists?'Edit':'New').' Role')
@section('content')
<div class="mb-6"><a href="{{ route('roles.index') }}" class="text-sm text-slate-400">← Roles</a>
<h1 class="text-3xl font-bold mt-1">{{ $model->exists?'Edit':'New' }} Role</h1></div>
<form method="POST" action="{{ $model->exists ? route('roles.update',$model) : route('roles.store') }}" class="card p-6">
  @csrf @if($model->exists)@method('PUT')@endif
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
    <div>
      <label class="text-xs text-slate-400 uppercase tracking-wide">Role Name <span class="text-rose-400">*</span></label>
      <input name="name" value="{{ old('name',$model->name) }}" class="inp mt-1" data-testid="field-name" required @if($model->is_system) readonly @endif>
    </div>
    <div>
      <label class="text-xs text-slate-400 uppercase tracking-wide">Portal <span class="text-rose-400">*</span></label>
      <select name="portal" class="inp mt-1" data-testid="field-portal">
        @foreach(['admin','operations','advertising','finance','sales','advertiser','driver','owner','technician'] as $p)
          <option value="{{ $p }}" @selected(old('portal',$model->portal ?? 'admin')===$p)>{{ ucfirst($p) }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="mt-6">
    <div class="flex items-center justify-between mb-2">
      <label class="text-xs text-slate-400 uppercase tracking-wide">Permissions</label>
      <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer"><input type="checkbox" onclick="document.querySelectorAll('input[name=&quot;permissions[]&quot;]').forEach(c=>c.checked=this.checked)" class="accent-amber-500" data-testid="perm-select-all">Select all</label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      @foreach($permissions as $module => $perms)
        <div class="card p-4">
          <div class="font-heading text-sm font-semibold text-amber-400 uppercase tracking-wide mb-2">{{ str_replace('_',' ',$module) }}</div>
          <div class="space-y-1.5">
            @foreach($perms as $perm)
              <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-white">
                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" @checked(in_array($perm->id,$assigned)) class="accent-amber-500" data-testid="perm-{{ $perm->slug }}">
                <span class="text-slate-300">{{ $perm->action ?? $perm->name }}</span>
              </label>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  </div>
  <div class="mt-6 flex gap-3"><button class="btn btn-primary" data-testid="save-roles">Save Role</button><a href="{{ route('roles.index') }}" class="btn btn-sec">Cancel</a></div>
</form>
@endsection
