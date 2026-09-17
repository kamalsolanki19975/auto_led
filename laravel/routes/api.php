<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ResourceApiController;
use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\Api\ApiDocsController;

Route::prefix('v1')->group(function () {

    // ---- Auth ----
    Route::post('/auth/login', [AuthApiController::class, 'login'])->middleware('throttle:10,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthApiController::class, 'me']);
        Route::post('/auth/logout', [AuthApiController::class, 'logout']);

        // Generic REST resources
        Route::get('/{resource}', [ResourceApiController::class, 'index'])
            ->whereIn('resource', ['autos', 'owners', 'drivers', 'screens', 'devices', 'sims', 'advertisers', 'advertisements', 'campaigns', 'playlists', 'invoices', 'payments', 'expenses', 'settlements', 'proof-of-play', 'runtime']);
        Route::get('/{resource}/{id}', [ResourceApiController::class, 'show'])
            ->whereIn('resource', ['autos', 'owners', 'drivers', 'screens', 'devices', 'sims', 'advertisers', 'advertisements', 'campaigns', 'playlists', 'invoices', 'payments', 'expenses', 'settlements']);
    });

    // ---- Device API (separate device credentials) ----
    Route::prefix('device')->group(function () {
        Route::post('/authenticate', [DeviceApiController::class, 'authenticate'])->middleware('throttle:10,1');

        Route::middleware('device.auth')->group(function () {
            Route::post('/heartbeat', [DeviceApiController::class, 'heartbeat']);
            Route::get('/configuration', [DeviceApiController::class, 'configuration']);
            Route::get('/campaigns', [DeviceApiController::class, 'campaigns']);
            Route::get('/content', [DeviceApiController::class, 'content']);
            Route::post('/playback-events', [DeviceApiController::class, 'playbackEvents']);
            Route::post('/sync-events', [DeviceApiController::class, 'syncEvents']);
            Route::post('/errors', [DeviceApiController::class, 'errors']);
            Route::post('/status', [DeviceApiController::class, 'status']);
            Route::post('/acknowledgement', [DeviceApiController::class, 'acknowledgement']);
        });
    });
});
