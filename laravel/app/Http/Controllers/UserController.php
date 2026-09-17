<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::with('roles');
        if ($s = $request->get('q')) {
            $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%");
        }
        return view('admin.user-index', ['rows' => $q->latest()->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.user-form', ['model' => new User, 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'status' => 'required|in:active,inactive',
            'roles' => 'array',
        ]);
        $user = User::create([
            'name' => $data['name'], 'email' => $data['email'],
            'password' => Hash::make($data['password']), 'status' => $data['status'],
            'email_verified_at' => now(),
        ]);
        $user->roles()->sync($request->input('roles', []));
        AuditService::log('user.created', $user);
        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.user-form', ['model' => $user, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'status' => 'required|in:active,inactive',
            'roles' => 'array',
            'password' => 'nullable|min:8',
        ]);
        $update = ['name' => $data['name'], 'email' => $data['email'], 'status' => $data['status']];
        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }
        $user->update($update);
        $user->roles()->sync($request->input('roles', []));
        AuditService::log('user.updated', $user);
        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deactivated.');
    }
}
