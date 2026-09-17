<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(string $action, ?Model $entity = null, array $old = [], array $new = []): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'entity_type' => $entity ? class_basename($entity) : null,
                'entity_id' => $entity?->getKey(),
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip' => Request::ip(),
                'user_agent' => substr((string) Request::userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            // auditing must never break the request
        }
    }
}
