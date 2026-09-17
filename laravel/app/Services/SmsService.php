<?php

namespace App\Services;

use App\Models\NotificationDelivery;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provider-independent SMS adapter. Falls back to a development (log) adapter
 * when no provider credentials are configured, so functionality never breaks.
 */
class SmsService
{
    public function send(string $to, string $message): bool
    {
        $s = SystemSetting::group('sms');
        $provider = $s['provider'] ?? null;

        if (empty($provider) || empty($s['api_url']) || empty($s['api_key'])) {
            Log::info('[SMS:dev] to='.$to.' :: '.$message);
            $this->record($to, 'sent');
            return true;
        }

        try {
            Http::withToken($s['api_key'])->post($s['api_url'], [
                'to' => $to,
                'from' => $s['sender_id'] ?? 'AUTOAD',
                'message' => $message,
            ]);
            $this->record($to, 'sent');
            return true;
        } catch (\Throwable $e) {
            $this->record($to, 'failed', $e->getMessage());
            return false;
        }
    }

    protected function record(string $to, string $status, ?string $error = null): void
    {
        NotificationDelivery::create([
            'recipient' => $to,
            'event' => 'sms',
            'channel' => 'sms',
            'status' => $status,
            'sent_at' => $status === 'sent' ? now() : null,
            'failed_at' => $status === 'failed' ? now() : null,
            'error' => $error,
        ]);
    }
}
