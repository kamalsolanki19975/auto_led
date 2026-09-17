@extends('layouts.app')
@section('title','Roles & Permissions')
@section('content')
<div class="flex items-center justify-between mb-6">
  <div><h1 class="text-3xl font-bold">Roles &amp; Permissions</h1><p class="text-slate-400 text-sm">{{ $rows->count() }} roles</p></div>
  <a href="{{ route('roles.create') }}" class="btn btn-primary" data-testid="create-roles"><i data-lucide="shield-plus" class="w-4 h-4"></i>New Role</a>
</div>
<div class="card overflow-hidden">
  <table class="grid"><thead><tr><th>Role</th><th>Portal</th><th>Users</th><th>Permissions</th><th>Type</th><th class="text-right">Actions</th></tr></thead>
  <tbody>
  @forelse($rows as $r)
    <tr data-testid="row-roles-{{ $r->id }}">
      <td class="font-semibold text-white">{{ $r->name }}<div class="text-slate-500 mono text-xs">{{ $r->slug }}</div></td>
      <td class="text-slate-300 capitalize">{{ $r->portal }}</td>
      <td class="mono">{{ $r->users_count }}</td>
      <td class="mono">{{ $r->permissions_count }}</td>
      <td>@if($r->is_system)<span class="inline-flex items-center gap-1 text-xs text-sky-400"><i data-lucide="lock" class="w-3 h-3"></i>System</span>@else<span class="text-xs text-slate-400">Custom</span>@endif</td>
      <td class="text-right whitespace-nowrap">
        <a href="{{ route('roles.edit',$r) }}" class="text-amber-400 text-xs" data-testid="edit-roles-{{ $r->id }}">Edit</a>
      </td>
    </tr>
  @empty
    <tr><td colspan="6" class="text-center py-12 text-slate-500">No roles.</td></tr>
  @endforelse
  </tbody></table>
</div>
@endsection
