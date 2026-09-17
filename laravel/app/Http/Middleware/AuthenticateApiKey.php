<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-Api-Key') ?: $request->bearerToken();
        if (! $key || ! str_contains($key, '.')) {
            abort(401, 'A valid API key is required.');
        }
        [$prefix] = explode('.', $key, 2);
        $apiKey = ApiKey::where('prefix', $prefix)->where('status', 'active')->first();
        if (! $apiKey || ! Hash::check($key, $apiKey->key_hash)) {
            abort(401, 'Invalid API key.');
        }
        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            abort(401, 'This API key has expired.');
        }
        $apiKey->update(['last_used_at' => now()]);
        $request->attributes->set('api_key', $apiKey);
        return $next($request);
    }
}
