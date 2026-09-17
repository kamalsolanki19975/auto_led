<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\AuditService;
use App\Services\EmailService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected array $groups = ['company', 'branding', 'website', 'localization', 'seo', 'social', 'device', 'advertising', 'finance', 'email', 'sms', 'whatsapp', 'api'];

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'company');
        $settings = [];
        foreach ($this->groups as $g) {
            $settings[$g] = SystemSetting::group($g);
        }
        return view('admin.settings', ['settings' => $settings, 'tab' => $tab, 'groups' => $this->groups]);
    }

    public function update(Request $request, string $group)
    {
        abort_unless(in_array($group, $this->groups, true), 404);

        if ($group === 'branding' && $request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:png,jpg,jpeg,svg,webp|max:2048']);
            $path = $request->file('logo')->store('branding', 'public');
            SystemSetting::put('branding', 'logo_path', $path);
        }

        $data = $request->except(['_token', '_method', 'logo']);
        foreach ($data as $key => $value) {
            SystemSetting::put($group, $key, $value);
        }
        AuditService::log('settings.updated.'.$group);
        return back()->with('success', ucfirst($group).' settings saved.');
    }

    public function testEmail(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);
        app(EmailService::class)->send('auth.welcome', $request->test_email, ['user_name' => 'Test User'], 'AutoAds test email');
        return back()->with('success', 'Test email queued to '.$request->test_email.'. Check Email Logs.');
    }
}
