<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateDevice
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?: $request->header('X-Device-Token');
        $uuid = $request->header('X-Device-Uuid') ?: $request->input('device_uuid');

        if (! $token || ! $uuid) {
            abort(401, 'Device authentication required.');
        }

        $device = Device::where('device_uuid', $uuid)->first();
        if (! $device || ! $device->auth_token || ! Hash::check($token, $device->auth_token)) {
            abort(401, 'Invalid device credentials.');
        }
        if (in_array($device->status, ['blocked', 'deactivated'], true)) {
            abort(403, 'This device has been blocked.');
        }

        $request->attributes->set('device', $device);
        return $next($request);
    }
}
