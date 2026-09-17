<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePortal
{
    public function handle(Request $request, Closure $next, string $portals)
    {
        $allowed = explode('|', $portals);
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }
        $portal = $user->primaryPortal();
        if ($user->isSuperAdmin() || in_array('admin', $allowed) && $portal === 'admin') {
            return $next($request);
        }
        if (! in_array($portal, $allowed, true)) {
            abort(403, 'This area is not available for your role.');
        }
        return $next($request);
    }
}
