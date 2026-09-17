<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(protected EmailService $email) {}

    /**
     * Send a notification for an event to a set of users across enabled channels.
     *
     * @param  iterable<User>  $users
     */
    public function notify($users, string $event, array $data = []): void
    {
        $template = NotificationTemplate::where('event', $event)->first();
        if ($template && ! $template->status) {
            return;
        }

        $type = $data['type'] ?? ($template?->default_type ?? 'information');
        $category = $data['category'] ?? ($template?->category ?? null);
        $title = $data['title'] ?? ucfirst(str_replace(['.', '_'], ' ', $event));
        $message = $data['message'] ?? '';
        $defaultChannels = $template?->channels ?? ['in_app'];

        foreach ($users as $user) {
            if (! $user) {
                continue;
            }
            $pref = NotificationPreference::where('user_id', $user->id)->where('event', $event)->first();

            $inApp = $pref ? $pref->in_app : in_array('in_app', $defaultChannels, true);
            $wantsEmail = $pref ? $pref->email : in_array('email', $defaultChannels, true);
            $wantsSms = $pref ? $pref->sms : in_array('sms', $defaultChannels, true);
            $wantsWa = $pref ? $pref->whatsapp : in_array('whatsapp', $defaultChannels, true);

            if ($inApp) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'category' => $category,
                    'event' => $event,
                    'title' => $title,
                    'message' => $message,
                    'related_type' => $data['related_type'] ?? null,
                    'related_id' => $data['related_id'] ?? null,
                    'link' => $data['link'] ?? null,
                ]);
                $this->logChannel($user, $event, 'in_app', 'sent');
            }

            if ($wantsEmail && $user->email) {
                $this->email->send($event, $user->email, array_merge($data, [
                    'user_name' => $user->name,
                    'message' => $message,
                    'title' => $title,
                ]));
                $this->logChannel($user, $event, 'email', 'sent');
            }

            if ($wantsSms && $user->phone) {
                app(SmsService::class)->send($user->phone, $message ?: $title);
                $this->logChannel($user, $event, 'sms', 'sent');
            }

            if ($wantsWa && $user->phone) {
                app(WhatsAppService::class)->send($user->phone, $message ?: $title);
                $this->logChannel($user, $event, 'whatsapp', 'sent');
            }
        }
    }

    /** Notify all users who hold any of the given role slugs. */
    public function notifyRoles(array $roleSlugs, string $event, array $data = []): void
    {
        $users = User::whereHas('roles', fn ($q) => $q->whereIn('slug', $roleSlugs))->get();
        $this->notify($users, $event, $data);
    }

    /** Notify all admin/ops staff (the Admin Notification Center audience). */
    public function notifyAdmins(string $event, array $data = []): void
    {
        $this->notifyRoles(['super-admin', 'administrator', 'operations-manager', 'advertising-manager', 'finance-manager'], $event, $data);
    }

    protected function logChannel(User $user, string $event, string $channel, string $status, ?string $error = null): void
    {
        NotificationLog::create([
            'user_id' => $user->id,
            'event' => $event,
            'channel' => $channel,
            'status' => $status,
            'error' => $error,
        ]);
    }
}
