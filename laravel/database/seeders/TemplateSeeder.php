<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $emails = [
            ['auth.welcome', 'Welcome', 'Welcome to AutoAds Network, {user_name}', '<h2>Welcome {user_name}!</h2><p>Your AutoAds Network account is ready.</p>'],
            ['auth.password_reset', 'Password Reset', 'Reset your AutoAds password', '<p>Hi {user_name}, use this link to reset your password: {reset_link}</p>'],
            ['auth.password_changed', 'Password Changed', 'Your password was changed', '<p>Hi {user_name}, your password was changed successfully.</p>'],
            ['advertisement.approved', 'Advertisement Approved', 'Your advertisement "{title}" was approved', '<p>Hi {advertiser_name}, your advertisement <b>{title}</b> has been approved.</p>'],
            ['advertisement.rejected', 'Advertisement Rejected', 'Your advertisement "{title}" needs changes', '<p>Hi {advertiser_name}, your advertisement <b>{title}</b> was rejected. {message}</p>'],
            ['campaign.activated', 'Campaign Activated', 'Campaign {campaign_name} is now live', '<p>Hi {advertiser_name}, your campaign <b>{campaign_name}</b> ({campaign_id}) is now live across the network.</p>'],
            ['campaign.under_delivery', 'Campaign Under-Delivery', 'Campaign {campaign_name} is under-delivering', '<p>Campaign <b>{campaign_name}</b> is currently under-delivering. Our team is on it.</p>'],
            ['invoice.created', 'Invoice Created', 'Invoice {invoice_number} from AutoAds Network', '<p>Hi {advertiser_name}, invoice <b>{invoice_number}</b> for {campaign_name} of amount ₹{amount} has been raised.</p>'],
            ['invoice.overdue', 'Invoice Overdue', 'Invoice {invoice_number} is overdue', '<p>Hi {advertiser_name}, invoice <b>{invoice_number}</b> of ₹{amount} is overdue. Please arrange payment.</p>'],
            ['payment.received', 'Payment Received', 'We received your payment', '<p>Hi {advertiser_name}, we have received your payment of ₹{amount}. Thank you.</p>'],
            ['settlement.created', 'Settlement Created', 'Your settlement {settlement_number} is ready', '<p>Hi {user_name}, settlement <b>{settlement_number}</b> of ₹{amount} has been prepared.</p>'],
            ['settlement.approved', 'Settlement Approved', 'Settlement {settlement_number} approved', '<p>Hi {user_name}, your settlement <b>{settlement_number}</b> of ₹{amount} is approved and will be paid soon.</p>'],
            ['device.offline', 'Device Offline', 'Device {device_id} is offline', '<p>Device <b>{device_id}</b> on auto {auto_number} has gone offline.</p>'],
            ['sim.expiry', 'SIM Expiry', 'SIM expiring soon', '<p>SIM {message} is expiring soon. Please renew.</p>'],
            ['maintenance.created', 'Maintenance Ticket', 'New maintenance ticket', '<p>A new maintenance ticket has been created: {message}</p>'],
        ];
        foreach ($emails as [$event, $name, $subject, $body]) {
            EmailTemplate::updateOrCreate(['event' => $event], [
                'name' => $name, 'subject' => $subject, 'body' => $body,
                'variables' => ['user_name', 'advertiser_name', 'campaign_name', 'campaign_id', 'invoice_number', 'settlement_number', 'amount', 'device_id', 'auto_number', 'title', 'message', 'reset_link'],
                'status' => true,
            ]);
        }

        $notifications = [
            ['device.offline', 'Device Offline', 'device', 'critical', ['in_app', 'email']],
            ['device.online', 'Device Online', 'device', 'success', ['in_app']],
            ['device.error', 'Device Error', 'device', 'warning', ['in_app']],
            ['sim.expiry', 'SIM Expiry', 'sim', 'warning', ['in_app', 'email']],
            ['sim.high_usage', 'SIM High Usage', 'sim', 'warning', ['in_app']],
            ['campaign.activated', 'Campaign Activated', 'campaign', 'success', ['in_app']],
            ['campaign.under_delivery', 'Campaign Under-Delivery', 'campaign', 'warning', ['in_app', 'email']],
            ['campaign.expiring', 'Campaign Expiring', 'campaign', 'warning', ['in_app']],
            ['campaign.completed', 'Campaign Completed', 'campaign', 'information', ['in_app']],
            ['advertisement.submitted', 'Advertisement Submitted', 'campaign', 'information', ['in_app']],
            ['invoice.overdue', 'Invoice Overdue', 'finance', 'critical', ['in_app', 'email']],
            ['payment.received', 'Payment Received', 'finance', 'success', ['in_app']],
            ['settlement.created', 'Settlement Created', 'finance', 'information', ['in_app']],
            ['settlement.pending', 'Settlement Pending', 'finance', 'warning', ['in_app']],
            ['maintenance.created', 'Maintenance Ticket Created', 'operations', 'warning', ['in_app', 'email']],
            ['warranty.expiring', 'Warranty Expiring', 'operations', 'warning', ['in_app']],
            ['installation.pending', 'Installation Pending', 'operations', 'information', ['in_app']],
        ];
        foreach ($notifications as [$event, $name, $category, $type, $channels]) {
            NotificationTemplate::updateOrCreate(['event' => $event], [
                'name' => $name, 'category' => $category, 'default_type' => $type,
                'channels' => $channels, 'status' => true,
            ]);
        }
    }
}
