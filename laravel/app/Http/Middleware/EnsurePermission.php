<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermission($permission)) {
            if ($request->is('api/*') || $request->expectsJson()) {
                abort(403, 'You do not have permission to perform this action.');
            }
            abort(403, 'You do not have permission to access this section.');
        }
        return $next($request);
    }
}
