<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\CampaignService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckCampaignDelivery extends Command
{
    protected $signature = 'campaigns:check-delivery';
    protected $description = 'Aggregate campaign delivery and flag under-delivery / expiry.';

    public function handle(CampaignService $service, NotificationService $notifier): int
    {
        $count = 0;
        foreach (Campaign::whereIn('status', ['active', 'under_delivery'])->get() as $campaign) {
            $d = $service->delivery($campaign);
            if ($campaign->end_date && $campaign->end_date->isPast()) {
                $campaign->update(['status' => 'completed', 'completed_at' => now()]);
                continue;
            }
            if ($d['under_delivery'] && $campaign->status !== 'under_delivery') {
                $campaign->update(['status' => 'under_delivery']);
                $notifier->notifyAdmins('campaign.under_delivery', [
                    'type' => 'warning', 'category' => 'campaign',
                    'title' => 'Campaign under-delivery', 'message' => $campaign->name.' is at '.$d['delivery_percent'].'% delivery.',
                    'related_type' => 'Campaign', 'related_id' => $campaign->id,
                ]);
                $count++;
            }
        }
        $this->info('Checked delivery; '.$count.' flagged under-delivery.');
        return self::SUCCESS;
    }
}
