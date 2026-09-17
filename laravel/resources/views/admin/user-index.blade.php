@extends('layouts.app')
@section('title','Users')
@section('content')
<div class="flex items-center justify-between mb-6">
  <div><h1 class="text-3xl font-bold">Users</h1><p class="text-slate-400 text-sm">{{ $rows->total() }} accounts</p></div>
  <a href="{{ route('users.create') }}" class="btn btn-primary" data-testid="create-users"><i data-lucide="user-plus" class="w-4 h-4"></i>New User</a>
</div>
<div class="card p-4 mb-4"><form method="GET" class="flex flex-wrap gap-3 items-center">
  <input name="q" value="{{ request('q') }}" placeholder="Search users…" class="inp max-w-xs" data-testid="search-users">
  <button class="btn btn-sec">Filter</button>
</form></div>
<div class="card overflow-hidden">
  <table class="grid"><thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th>Last Login</th><th class="text-right">Actions</th></tr></thead>
  <tbody>
  @forelse($rows as $u)
    <tr data-testid="row-users-{{ $u->id }}">
      <td class="font-semibold text-white">{{ $u->name }}</td>
      <td class="text-slate-300 mono text-xs">{{ $u->email }}</td>
      <td class="text-slate-300">
        @forelse($u->roles as $r)<span class="inline-block rounded bg-white/5 border border-white/10 px-2 py-0.5 text-xs mr-1 mb-1">{{ $r->name }}</span>@empty<span class="text-slate-500">—</span>@endforelse
      </td>
      <td><x-badge :status="$u->status"/></td>
      <td class="text-slate-400 text-xs">{{ $u->last_login_at?->format('d M H:i') ?? '—' }}</td>
      <td class="text-right whitespace-nowrap">
        <a href="{{ route('users.edit',$u) }}" class="text-amber-400 text-xs mr-2" data-testid="edit-users-{{ $u->id }}">Edit</a>
        <form action="{{ route('users.destroy',$u) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this user?')">@csrf @method('DELETE')<button class="text-rose-400 text-xs" data-testid="delete-users-{{ $u->id }}">Deactivate</button></form>
      </td>
    </tr>
  @empty
    <tr><td colspan="6" class="text-center py-12 text-slate-500"><i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><div>No users yet.</div></td></tr>
  @endforelse
  </tbody></table>
</div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
