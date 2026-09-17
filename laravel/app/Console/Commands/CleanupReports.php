<?php

namespace App\Console\Commands;

use App\Models\ApiLog;
use App\Models\Heartbeat;
use Illuminate\Console\Command;

class CleanupReports extends Command
{
    protected $signature = 'reports:cleanup {--days=90}';
    protected $description = 'Archive/prune high-volume logs older than N days.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);
        $api = ApiLog::where('created_at', '<', $cutoff)->delete();
        $hb = Heartbeat::where('created_at', '<', $cutoff)->delete();
        $this->info("Pruned {$api} api logs and {$hb} heartbeats older than {$days} days.");
        return self::SUCCESS;
    }
}
