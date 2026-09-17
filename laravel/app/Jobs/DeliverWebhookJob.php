<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $deliveryId) {}

    public function backoff(): array
    {
        return [10, 30, 120, 300, 900]; // exponential-ish backoff
    }

    public function handle(): void
    {
        $delivery = WebhookDelivery::with('webhook')->find($this->deliveryId);
        if (! $delivery || ! $delivery->webhook || ! $delivery->webhook->active) {
            return;
        }

        $body = json_encode([
            'event' => $delivery->event,
            'data' => $delivery->payload,
            'delivered_at' => now()->toIso8601String(),
        ]);
        $signature = hash_hmac('sha256', $body, (string) $delivery->webhook->secret);

        $delivery->increment('attempts');

        try {
            $resp = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature' => $signature,
                'X-Webhook-Event' => $delivery->event,
            ])->timeout(15)->withBody($body, 'application/json')->post($delivery->webhook->url);

            if ($resp->successful()) {
                $delivery->update(['status' => 'delivered', 'response_code' => $resp->status(), 'delivered_at' => now()]);
            } else {
                $delivery->update(['status' => 'retrying', 'response_code' => $resp->status(), 'error' => 'HTTP '.$resp->status()]);
                throw new \RuntimeException('Webhook returned '.$resp->status());
            }
        } catch (\Throwable $e) {
            $delivery->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 1000)]);
            throw $e;
        }
    }
}
