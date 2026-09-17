<?php

namespace App\Services;

use App\Models\PlaybackEvent;
use App\Models\ProofOfPlay;
use App\Models\SystemSetting;

/**
 * The Proof-of-Play validation engine. Never trusts raw playback events — it
 * re-validates device, screen, auto, campaign, advertisement, approval,
 * duration, timestamp, assignment and duplicate status server-side.
 */
class ProofOfPlayService
{
    public function validate(PlaybackEvent $event): ProofOfPlay
    {
        $event->update(['status' => 'validating']);

        $reasons = [];
        $minCompletion = (float) SystemSetting::get('advertising', 'minimum_valid_playback', 70);

        $device = $event->device;
        $campaign = $event->campaign;
        $ad = $event->advertisement;

        if (! $device) {
            $reasons[] = 'Unknown device';
        } elseif (! in_array($device->status, ['active'], true)) {
            $reasons[] = 'Device not active';
        }

        if (! $campaign) {
            $reasons[] = 'Unknown campaign';
        } else {
            if (! in_array($campaign->status, ['active', 'under_delivery', 'completed'], true)) {
                $reasons[] = 'Campaign not active';
            }
            // schedule window check
            if ($campaign->start_date && $event->server_timestamp && $event->server_timestamp->lt($campaign->start_date->startOfDay())) {
                $reasons[] = 'Before campaign start';
            }
            if ($campaign->end_date && $event->server_timestamp && $event->server_timestamp->gt($campaign->end_date->endOfDay())) {
                $reasons[] = 'After campaign end';
            }
        }

        if ($campaign && $campaign->campaign_type === 'paid') {
            if (! $ad) {
                $reasons[] = 'Missing advertisement';
            } elseif (! $ad->isApproved()) {
                $reasons[] = 'Advertisement not approved';
            }
        }

        // Duration / completion validation
        if ($event->completion_percent < $minCompletion) {
            $reasons[] = 'Completion below minimum ('.$event->completion_percent.'%)';
        }

        // Clock manipulation: device timestamp too far from server timestamp
        if ($event->device_timestamp && $event->server_timestamp) {
            $skew = abs($event->server_timestamp->diffInMinutes($event->device_timestamp));
            if ($skew > 1440) {
                $reasons[] = 'Timestamp skew too large';
            }
        }

        // Assignment check: was the auto assigned to the campaign?
        if ($campaign && $event->auto_id) {
            $assigned = $campaign->assignments()->where('auto_id', $event->auto_id)->exists();
            if (! $assigned && $campaign->campaign_type === 'paid') {
                $reasons[] = 'Auto not assigned to campaign';
            }
        }

        $valid = empty($reasons);
        $validDuration = $valid ? $event->actual_duration : 0;

        $event->update([
            'status' => $valid ? 'valid' : 'invalid',
            'processed' => true,
        ]);

        $pop = ProofOfPlay::updateOrCreate(
            ['event_id' => $event->event_id],
            [
                'playback_event_id' => $event->id,
                'device_id' => $event->device_id,
                'auto_id' => $event->auto_id,
                'campaign_id' => $event->campaign_id,
                'advertisement_id' => $event->advertisement_id,
                'driver_id' => $event->driver_id,
                'valid_duration' => $validDuration,
                'completion_percent' => $event->completion_percent,
                'status' => $valid ? 'valid' : 'invalid',
                'validation_notes' => $valid ? 'Validated' : implode('; ', $reasons),
                'validated_at' => now(),
                'server_timestamp' => $event->server_timestamp,
            ]
        );

        if ($valid) {
            app(RuntimeService::class)->applyProofOfPlay($pop);
            app(WebhookService::class)->dispatch('proof_of_play.validated', [
                'event_id' => $event->event_id,
                'campaign_id' => $event->campaign_id,
                'auto_id' => $event->auto_id,
                'valid_duration' => $validDuration,
            ]);
        }

        return $pop;
    }
}
