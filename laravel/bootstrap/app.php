<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PROTO
            | Request::HEADER_X_FORWARDED_AWS_ELB);

        $middleware->alias([
            'permission' => \App\Http\Middleware\EnsurePermission::class,
            'portal' => \App\Http\Middleware\EnsurePortal::class,
            'device.auth' => \App\Http\Middleware\AuthenticateDevice::class,
            'apikey' => \App\Http\Middleware\AuthenticateApiKey::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\LogApiRequest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            $requestId = (string) \Illuminate\Support\Str::uuid();
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode()
                    : ($e instanceof AuthenticationException ? 401 : 500);
                $payload = [
                    'success' => false,
                    'message' => $status >= 500 ? 'An unexpected error occurred.' : $e->getMessage(),
                    'request_id' => $requestId,
                ];
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                    $payload['message'] = 'The given data was invalid.';
                    $payload['errors'] = $e->errors();
                }
                if ($status >= 500) {
                    \Illuminate\Support\Facades\Log::error($e->getMessage(), ['request_id' => $requestId, 'exception' => $e]);
                }
                return response()->json($payload, $status);
            }
        });
    })->create();
