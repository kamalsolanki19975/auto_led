<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public array $events = [
        'campaign.created', 'campaign.approved', 'campaign.started', 'campaign.completed',
        'device.online', 'device.offline', 'playback.received', 'proof_of_play.validated',
        'settlement.created', 'settlement.approved', 'payment.received', 'payment.completed',
    ];

    public function index()
    {
        return view('integrations.webhooks', [
            'rows' => Webhook::with('deliveries')->latest()->get(),
            'events' => $this->events,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['url' => 'required|url', 'events' => 'required|array']);
        Webhook::create([
            'url' => $data['url'],
            'events' => $data['events'],
            'secret' => 'whsec_'.Str::random(32),
            'active' => true,
            'status' => 'active',
        ]);
        return back()->with('success', 'Webhook endpoint registered.');
    }

    public function test(Webhook $webhook, WebhookService $svc)
    {
        $svc->dispatch($webhook->events[0] ?? 'campaign.created', ['test' => true, 'sent_at' => now()->toIso8601String()]);
        return back()->with('success', 'Test event dispatched.');
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();
        return back()->with('success', 'Webhook removed.');
    }
}
