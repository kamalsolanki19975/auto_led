<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\AdvertisementVersion;

class AdvertisementService
{
    public function submit(Advertisement $ad): void
    {
        $ad->update(['approval_status' => 'submitted']);
        AuditService::log('advertisement.submitted', $ad);
        app(NotificationService::class)->notifyRoles(['super-admin', 'administrator', 'advertising-manager'], 'advertisement.submitted', [
            'type' => 'information', 'category' => 'campaign',
            'title' => 'Advertisement submitted for review',
            'message' => $ad->title.' ('.$ad->code.') is awaiting approval.',
            'related_type' => 'Advertisement', 'related_id' => $ad->id,
            'link' => route('advertisements.show', $ad),
        ]);
    }

    public function approve(Advertisement $ad, int $userId): void
    {
        $ad->update(['approval_status' => 'approved', 'approved_by' => $userId, 'approved_at' => now(), 'rejection_reason' => null]);
        AuditService::log('advertisement.approved', $ad);
        if ($ad->advertiser && $ad->advertiser->email) {
            app(EmailService::class)->send('advertisement.approved', $ad->advertiser->email, [
                'advertiser_name' => $ad->advertiser->company_name,
                'title' => $ad->title,
            ]);
        }
    }

    public function reject(Advertisement $ad, string $reason, int $userId): void
    {
        $ad->update(['approval_status' => 'rejected', 'rejection_reason' => $reason, 'approved_by' => $userId]);
        AuditService::log('advertisement.rejected', $ad, [], ['reason' => $reason]);
        if ($ad->advertiser && $ad->advertiser->email) {
            app(EmailService::class)->send('advertisement.rejected', $ad->advertiser->email, [
                'advertiser_name' => $ad->advertiser->company_name,
                'title' => $ad->title,
                'message' => 'Reason: '.$reason,
            ]);
        }
    }

    public function newVersion(Advertisement $ad, array $data, int $userId): void
    {
        AdvertisementVersion::create([
            'advertisement_id' => $ad->id,
            'version' => $ad->version,
            'media_path' => $ad->media_path,
            'notes' => $data['notes'] ?? 'Superseded',
            'created_by' => $userId,
        ]);
        $ad->update([
            'version' => $ad->version + 1,
            'media_path' => $data['media_path'] ?? $ad->media_path,
            'approval_status' => 'draft',
        ]);
    }
}
