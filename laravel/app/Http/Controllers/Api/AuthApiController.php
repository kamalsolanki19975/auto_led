<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password) || $user->status !== 'active') {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }
        $token = $user->createToken('api', $user->permissionSlugs() ?: ['*'])->plainTextToken;
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'portal' => $user->primaryPortal()],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json(['success' => true, 'user' => [
            'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
            'roles' => $user->roles->pluck('slug'), 'portal' => $user->primaryPortal(),
        ]]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }
}
