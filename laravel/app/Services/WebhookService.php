<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Jobs\DeliverWebhookJob;

class WebhookService
{
    /** Fan out an event to all active webhooks that subscribe to it. */
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::where('active', true)->get()
            ->filter(fn ($w) => in_array($event, (array) ($w->events ?? []), true) || in_array('*', (array) ($w->events ?? []), true));

        foreach ($webhooks as $webhook) {
            $delivery = WebhookDelivery::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'status' => 'pending',
                'attempts' => 0,
            ]);
            DeliverWebhookJob::dispatch($delivery->id);
        }
    }
}
