<?php

namespace App\Console\Commands;

use App\Models\Sim;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckSimExpiry extends Command
{
    protected $signature = 'sims:check-expiry';
    protected $description = 'Notify about SIMs expiring soon and mark expired ones.';

    public function handle(NotificationService $notifier): int
    {
        Sim::where('status', 'active')->whereDate('renewal_date', '<', now())->update(['status' => 'expired']);
        $expiring = Sim::where('status', 'active')->whereBetween('renewal_date', [now(), now()->addDays(15)])->get();
        foreach ($expiring as $sim) {
            $notifier->notifyAdmins('sim.expiry', ['type' => 'warning', 'category' => 'sim', 'title' => 'SIM expiring', 'message' => 'SIM '.$sim->code.' renews on '.$sim->renewal_date?->format('d M Y'), 'related_type' => 'Sim', 'related_id' => $sim->id]);
        }
        $this->info($expiring->count().' SIMs expiring soon.');
        return self::SUCCESS;
    }
}
