<?php

namespace App\Console\Commands;

use App\Models\Warranty;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckWarrantyExpiry extends Command
{
    protected $signature = 'warranties:check-expiry';
    protected $description = 'Notify about warranties expiring in the next 30 days.';

    public function handle(NotificationService $notifier): int
    {
        $rows = Warranty::whereBetween('end_date', [now(), now()->addDays(30)])->get();
        foreach ($rows as $w) {
            $notifier->notifyAdmins('warranty.expiring', ['type' => 'warning', 'category' => 'operations', 'title' => 'Warranty expiring', 'message' => ucwords($w->asset_type).' warranty ('.$w->provider.') expires '.$w->end_date?->format('d M Y'), 'related_type' => 'Warranty', 'related_id' => $w->id]);
        }
        $this->info($rows->count().' warranties expiring soon.');
        return self::SUCCESS;
    }
}
