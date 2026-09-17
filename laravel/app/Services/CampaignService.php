<?php

namespace App\Services;

use App\Models\Auto;
use App\Models\Campaign;
use App\Models\CampaignAssignment;

class CampaignService
{
    public function __construct(protected NotificationService $notifications) {}

    /** Resolve target autos for a campaign based on its targeting config. */
    public function resolveTargetAutos(Campaign $campaign): \Illuminate\Support\Collection
    {
        $query = Auto::query()->whereNotIn('status', ['sold', 'decommissioned', 'suspended']);

        switch ($campaign->target_type) {
            case 'groups':
                $groupIds = (array) ($campaign->target_groups ?? []);
                $query->whereHas('groups', fn ($q) => $q->whereIn('auto_groups.id', $groupIds));
                break;
            case 'areas':
                $query->whereIn('area_id', (array) ($campaign->target_areas ?? []));
                break;
            case 'all':
            case 'city':
            default:
                // handled by explicit assignments or all-active
                break;
        }
        return $query->pluck('id');
    }

    public function assignAutos(Campaign $campaign, array $autoIds): int
    {
        $count = 0;
        foreach ($autoIds as $autoId) {
            $auto = Auto::find($autoId);
            // Business rule: cannot target inactive autos for paid campaigns
            if ($campaign->campaign_type === 'paid' && $auto && in_array($auto->status, ['inactive', 'sold', 'decommissioned', 'suspended'], true)) {
                continue;
            }
            CampaignAssignment::firstOrCreate(
                ['campaign_id' => $campaign->id, 'auto_id' => $autoId],
                ['status' => 'assigned', 'assigned_at' => now()]
            );
            $count++;
        }
        // recompute expected plays from assignment count if not set
        if ($campaign->expected_plays == 0) {
            $days = $campaign->start_date && $campaign->end_date ? max(1, $campaign->start_date->diffInDays($campaign->end_date) + 1) : 30;
            $campaign->update(['expected_plays' => $count * $days * 40]); // ~40 plays/auto/day baseline
        }
        return $count;
    }

    public function approve(Campaign $campaign, int $userId): void
    {
        $this->assertAdsApproved($campaign);
        $campaign->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
        AuditService::log('campaign.approved', $campaign);
        app(WebhookService::class)->dispatch('campaign.approved', ['code' => $campaign->code]);
    }

    public function activate(Campaign $campaign, int $userId): void
    {
        // Business rule 11: cannot activate if required ads not approved
        $this->assertAdsApproved($campaign);
        if ($campaign->assignments()->count() === 0) {
            abort(422, 'Assign at least one auto before activating the campaign.');
        }
        $campaign->update(['status' => 'active', 'activated_at' => now()]);
        $campaign->assignments()->update(['status' => 'active']);
        AuditService::log('campaign.activated', $campaign);
        app(WebhookService::class)->dispatch('campaign.started', ['code' => $campaign->code]);

        $this->notifications->notifyAdmins('campaign.activated', [
            'type' => 'success', 'category' => 'campaign',
            'title' => 'Campaign activated',
            'message' => $campaign->name.' ('.$campaign->code.') is now live.',
            'related_type' => 'Campaign', 'related_id' => $campaign->id,
            'link' => route('campaigns.show', $campaign),
        ]);

        if ($campaign->advertiser && $campaign->advertiser->email) {
            app(EmailService::class)->send('campaign.activated', $campaign->advertiser->email, [
                'advertiser_name' => $campaign->advertiser->company_name,
                'campaign_name' => $campaign->name,
                'campaign_id' => $campaign->code,
            ]);
        }
    }

    public function pause(Campaign $campaign): void
    {
        $campaign->update(['status' => 'paused']);
        AuditService::log('campaign.paused', $campaign);
    }

    protected function assertAdsApproved(Campaign $campaign): void
    {
        if ($campaign->campaign_type !== 'paid') {
            return;
        }
        $ads = $campaign->advertisements;
        if ($ads->isEmpty()) {
            abort(422, 'Attach at least one advertisement to the campaign.');
        }
        $unapproved = $ads->reject(fn ($a) => $a->isApproved());
        if ($unapproved->isNotEmpty()) {
            abort(422, 'All advertisements must be approved before the campaign can go live.');
        }
    }

    /** Compute delivery metrics for a campaign. */
    public function delivery(Campaign $campaign): array
    {
        $expected = (int) $campaign->expected_plays;
        $actual = (int) $campaign->playbackEvents()->count();
        $valid = (int) $campaign->proofOfPlays()->where('status', 'valid')->count();
        $pct = $expected > 0 ? round(($valid / $expected) * 100, 1) : 0;

        return [
            'expected_plays' => $expected,
            'actual_plays' => $actual,
            'valid_plays' => $valid,
            'delivery_percent' => $pct,
            'remaining' => max(0, $expected - $valid),
            'under_delivery' => $expected > 0 && $pct < 80,
            'runtime_seconds' => (int) $campaign->proofOfPlays()->where('status', 'valid')->sum('valid_duration'),
        ];
    }
}
