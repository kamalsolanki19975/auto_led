<?php

namespace App\Services;

use App\Models\NotificationDelivery;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provider-independent WhatsApp adapter with a development (log) fallback.
 */
class WhatsAppService
{
    public function send(string $to, string $message): bool
    {
        $s = SystemSetting::group('whatsapp');
        $provider = $s['provider'] ?? null;

        if (empty($provider) || empty($s['api_url']) || empty($s['api_key'])) {
            Log::info('[WhatsApp:dev] to='.$to.' :: '.$message);
            $this->record($to, 'sent');
            return true;
        }

        try {
            Http::withToken($s['api_key'])->post($s['api_url'], [
                'phone_number_id' => $s['phone_number_id'] ?? null,
                'to' => $to,
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
            'event' => 'whatsapp',
            'channel' => 'whatsapp',
            'status' => $status,
            'sent_at' => $status === 'sent' ? now() : null,
            'failed_at' => $status === 'failed' ? now() : null,
            'error' => $error,
        ]);
    }
}
