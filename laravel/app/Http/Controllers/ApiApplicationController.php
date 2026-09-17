<?php

namespace App\Http\Controllers;

use App\Models\ApiApplication;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiApplicationController extends Controller
{
    public function index()
    {
        return view('integrations.api-apps', ['rows' => ApiApplication::with('keys')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string', 'description' => 'nullable|string']);
        $app = ApiApplication::create(array_merge($data, ['owner_user_id' => auth()->id(), 'status' => 'active']));
        return back()->with('success', 'API application "'.$app->name.'" created.');
    }

    public function generateKey(Request $request, ApiApplication $app)
    {
        $prefix = 'ak_'.Str::lower(Str::random(8));
        $secret = Str::random(40);
        $full = $prefix.'.'.$secret;
        ApiKey::create([
            'api_application_id' => $app->id,
            'name' => $request->input('name', 'Key'),
            'key_hash' => Hash::make($full),
            'prefix' => $prefix,
            'scopes' => ['read'],
            'status' => 'active',
        ]);
        return back()->with('success', 'API key generated (shown once): '.$full);
    }

    public function revokeKey(ApiKey $key)
    {
        $key->update(['status' => 'revoked']);
        return back()->with('success', 'API key revoked.');
    }
}
