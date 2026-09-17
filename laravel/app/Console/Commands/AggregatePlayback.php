<?php

namespace App\Console\Commands;

use App\Services\RuntimeService;
use Illuminate\Console\Command;

class AggregatePlayback extends Command
{
    protected $signature = 'playback:aggregate {date?}';
    protected $description = 'Rebuild runtime aggregates from validated proof-of-play for a date.';

    public function handle(RuntimeService $svc): int
    {
        $date = $this->argument('date') ?: now()->toDateString();
        $n = $svc->rebuildForDate($date);
        $this->info("Aggregated {$n} runtime rows for {$date}.");
        return self::SUCCESS;
    }
}
