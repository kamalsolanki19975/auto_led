<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'company' => [
                'name' => 'AutoAds Network Pvt Ltd',
                'address' => 'MG Road, Bengaluru, Karnataka 560001',
                'gstin' => '29ABCDE1234F1Z5',
                'pan' => 'ABCDE1234F',
                'email' => 'hello@autoads.network',
                'phone' => '+91 80 4000 0000',
                'website' => 'https://autoads.network',
            ],
            'localization' => [
                'currency' => 'INR',
                'currency_symbol' => '₹',
                'timezone' => 'Asia/Kolkata',
                'date_format' => 'd M Y',
            ],
            'device' => [
                'heartbeat_interval' => '60',
                'offline_threshold' => '180',
                'sync_interval' => '300',
                'retry' => '3',
            ],
            'advertising' => [
                'minimum_valid_playback' => '70',
                'default_fallback_content' => 'house',
                'campaign_default_priority' => '2',
            ],
            'finance' => [
                'settlement_frequency' => 'monthly',
                'default_tax_percent' => '18',
                'currency' => 'INR',
            ],
            'email' => [
                'mailer' => 'log',
                'smtp_host' => '',
                'smtp_port' => '587',
                'smtp_username' => '',
                'smtp_password' => '',
                'encryption' => 'tls',
                'from_email' => 'noreply@autoads.network',
                'from_name' => 'AutoAds Network',
                'reply_to' => 'support@autoads.network',
                'provider' => 'smtp',
            ],
            'sms' => [
                'provider' => '',
                'api_url' => '',
                'api_key' => '',
                'sender_id' => 'AUTOAD',
            ],
            'whatsapp' => [
                'provider' => '',
                'api_url' => '',
                'api_key' => '',
                'phone_number_id' => '',
            ],
            'api' => [
                'rate_limit' => '120',
                'require_api_key' => '1',
            ],
        ];

        foreach ($defaults as $group => $items) {
            foreach ($items as $key => $value) {
                SystemSetting::firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'type' => 'string']
                );
            }
        }
    }
}
