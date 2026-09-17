<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Behind HTTPS ingress the proxy forwards X-Forwarded-Port: 80 which makes
        // Laravel emit "https://host:80/..." redirects — break the browser. Force HTTPS
        // and root URL from APP_URL so generated URLs never carry an explicit port.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
        }

        // Super admin bypasses all gates.
        Gate::before(function ($user) {
            return $user->isSuperAdmin() ? true : null;
        });

        Blade::if('permission', fn (string $p) => auth()->check() && auth()->user()->hasPermission($p));
        Blade::if('portal', fn (string $p) => auth()->check() && auth()->user()->primaryPortal() === $p);

        // Share sidebar nav + unread notifications with authenticated (admin-portal) views.
        View::composer(['layouts.app', 'layouts.portal'], function ($view) {
            $user = auth()->user();
            $nav = [];
            if ($user) {
                foreach (config('navigation') as $group) {
                    $items = array_values(array_filter($group['items'], fn ($it) => $user->hasPermission($it['perm'])));
                    if ($items) {
                        $nav[] = ['group' => $group['group'], 'items' => $items];
                    }
                }
            }
            $unread = $user ? Notification::where('user_id', $user->id)->whereNull('read_at')->count() : 0;
            $recent = $user ? Notification::where('user_id', $user->id)->latest()->limit(8)->get() : collect();
            $view->with(compact('nav', 'unread', 'recent'));
        });
    }
}
