<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\SystemSetting;
use App\Jobs\SendEmailJob;

class EmailService
{
    /**
     * Queue an email built from a DB template for a recipient.
     */
    public function send(string $event, string $recipient, array $vars = [], ?string $subjectOverride = null): ?EmailLog
    {
        $template = EmailTemplate::where('event', $event)->first();

        if ($template && ! $template->status) {
            return null; // template disabled by admin
        }

        $subject = $subjectOverride ?? ($template?->subject ?? ucfirst(str_replace(['.', '_'], ' ', $event)));
        $body = $template?->body ?? '<p>{message}</p>';

        $subject = $this->replace($subject, $vars);
        $body = $this->replace($body, $vars);

        $log = EmailLog::create([
            'recipient' => $recipient,
            'subject' => $subject,
            'template_event' => $event,
            'event' => $event,
            'status' => 'queued',
        ]);

        SendEmailJob::dispatch($log->id, $recipient, $subject, $body);

        return $log;
    }

    protected function replace(string $text, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $text = str_replace('{'.$k.'}', (string) $v, $text);
        }
        return $text;
    }

    /**
     * Apply admin-configured SMTP settings from the DB over the mail config at runtime.
     */
    public static function applyRuntimeConfig(): void
    {
        $s = SystemSetting::group('email');
        if (empty($s['smtp_host']) || ($s['mailer'] ?? 'log') === 'log') {
            config(['mail.default' => 'log']);
            return;
        }
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $s['smtp_host'],
            'mail.mailers.smtp.port' => (int) ($s['smtp_port'] ?? 587),
            'mail.mailers.smtp.username' => $s['smtp_username'] ?? null,
            'mail.mailers.smtp.password' => $s['smtp_password'] ?? null,
            'mail.mailers.smtp.encryption' => $s['encryption'] ?? 'tls',
            'mail.from.address' => $s['from_email'] ?? 'noreply@autoads.network',
            'mail.from.name' => $s['from_name'] ?? 'AutoAds Network',
        ]);
    }
}
