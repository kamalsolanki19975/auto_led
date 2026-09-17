<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $requestId = (string) Str::uuid();
        $request->headers->set('X-Request-Id', $requestId);

        $response = $next($request);

        try {
            ApiLog::create([
                'method' => $request->method(),
                'path' => Str::limit($request->path(), 500, ''),
                'status_code' => $response->getStatusCode(),
                'ip' => $request->ip(),
                'request_id' => $requestId,
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // never break the request because of logging
        }

        $response->headers->set('X-Request-Id', $requestId);
        return $response;
    }
}
