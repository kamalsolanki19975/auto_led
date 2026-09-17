<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\RuntimeLog;
use App\Services\SettlementService;
use Illuminate\Console\Command;

class GenerateSettlements extends Command
{
    protected $signature = 'settlements:generate {--from=} {--to=}';
    protected $description = 'Generate draft settlements for drivers with runtime in the period.';

    public function handle(SettlementService $svc): int
    {
        $from = $this->option('from') ? \Carbon\Carbon::parse($this->option('from')) : now()->startOfMonth();
        $to = $this->option('to') ? \Carbon\Carbon::parse($this->option('to')) : now();

        $driverIds = RuntimeLog::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->distinct()->pluck('driver_id')->filter();
        $count = 0;
        foreach ($driverIds as $id) {
            $driver = Driver::find($id);
            if ($driver) {
                $svc->create($driver, $from->copy(), $to->copy());
                $count++;
            }
        }
        $this->info("Generated {$count} settlements.");
        return self::SUCCESS;
    }
}
