<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $logId,
        public string $recipient,
        public string $subject,
        public string $body
    ) {}

    public function handle(): void
    {
        $log = EmailLog::find($this->logId);
        try {
            EmailService::applyRuntimeConfig();
            Mail::html($this->body, function ($m) {
                $m->to($this->recipient)->subject($this->subject);
            });
            $log?->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            $log?->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error' => substr($e->getMessage(), 0, 1000),
                'retry_count' => ($log->retry_count ?? 0) + 1,
            ]);
            throw $e;
        }
    }
}
