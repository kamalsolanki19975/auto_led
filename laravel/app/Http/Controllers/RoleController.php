<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.role-index', ['rows' => Role::withCount('users', 'permissions')->get()]);
    }

    public function edit(Role $role)
    {
        return view('admin.role-form', [
            'model' => $role,
            'permissions' => Permission::orderBy('module')->get()->groupBy('module'),
            'assigned' => $role->permissions->pluck('id')->toArray(),
        ]);
    }

    public function create()
    {
        return view('admin.role-form', [
            'model' => new Role,
            'permissions' => Permission::orderBy('module')->get()->groupBy('module'),
            'assigned' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string', 'portal' => 'required|string', 'permissions' => 'array']);
        $role = Role::create([
            'name' => $data['name'], 'slug' => \Illuminate\Support\Str::slug($data['name']),
            'portal' => $data['portal'], 'is_system' => false,
        ]);
        $role->permissions()->sync($request->input('permissions', []));
        AuditService::log('role.created', $role);
        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate(['name' => 'required|string', 'portal' => 'required|string', 'permissions' => 'array']);
        $role->update(['name' => $data['name'], 'portal' => $data['portal']]);
        $role->permissions()->sync($request->input('permissions', []));
        AuditService::log('role.updated', $role);
        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }
}
