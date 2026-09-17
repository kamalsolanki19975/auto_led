<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use Illuminate\Console\Command;

class ProcessNotifications extends Command
{
    protected $signature = 'notifications:process';
    protected $description = 'Housekeeping: mark overdue invoices and emit finance alerts.';

    public function handle(InvoiceService $invoices): int
    {
        $n = $invoices->markOverdueInvoices();
        $this->info("Marked {$n} invoices overdue.");
        return self::SUCCESS;
    }
}
