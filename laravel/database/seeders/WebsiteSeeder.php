<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\Faq;
use App\Models\NotificationTemplate;
use App\Models\PricingPlan;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'branding' => [
                'name' => 'AutoAds Network',
                'tagline' => 'Turn Every Auto Ride Into an Advertising Opportunity',
                'logo_path' => '',
            ],
            'social' => [
                'twitter' => 'https://twitter.com',
                'linkedin' => 'https://linkedin.com',
                'facebook' => 'https://facebook.com',
                'instagram' => 'https://instagram.com',
                'youtube' => '',
            ],
            'seo' => [
                'default_title' => 'AutoAds Network — Smart DOOH Advertising on Auto Screens',
                'default_description' => 'A smart digital advertising network connecting brands with passenger-facing screens installed across autos. Launch geo-targeted campaigns with verified proof-of-play and real-time analytics.',
                'og_image' => '',
            ],
            'website' => [
                'hero_headline' => 'Turn Every Auto Ride Into an Advertising Opportunity',
                'hero_subheading' => 'A smart digital advertising network connecting brands with passenger-facing screens installed across autos — with verified proof-of-play and real-time analytics.',
                'contact_email' => 'hello@autoads.network',
                'contact_phone' => '+91 80 4000 0000',
                'contact_address' => 'MG Road, Bengaluru, Karnataka 560001',
            ],
        ];

        foreach ($defaults as $group => $pairs) {
            foreach ($pairs as $key => $value) {
                SystemSetting::firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'type' => 'string']
                );
            }
        }

        $faqs = [
            ['For Advertisers', 'How do I launch an advertising campaign?', 'Create an advertiser account, upload your creative, choose target cities/routes and schedule, then submit for approval. Once approved, your campaign is synced to auto screens across the network within minutes.', 1],
            ['For Advertisers', 'How is my ad delivery verified?', 'Every play is logged as a Proof-of-Play event with device, screen, auto, timestamp and completion percentage. You get expected-vs-actual delivery, valid plays and delivery percentage in real time.', 2],
            ['For Advertisers', 'Can I target specific cities or routes?', 'Yes. Campaigns can be targeted by city, area and auto-group so your brand reaches exactly the neighbourhoods and commuter routes that matter to you.', 3],
            ['For Fleet Owners & Drivers', 'Does the screen cost me anything?', 'No. We install the digital screen and device at zero upfront cost. You simply keep it powered and on the road.', 1],
            ['For Fleet Owners & Drivers', 'How do I earn?', 'You earn based on verified advertising runtime and valid plays delivered on your auto. Earnings, settlements and payments are all transparent in your driver portal.', 2],
            ['For Fleet Owners & Drivers', 'What if the device has a problem?', 'Our field technicians handle installation, maintenance and replacements. Raise a request from your portal and we take care of the rest, under warranty.', 3],
            ['Hardware & Technology', 'What hardware is installed?', 'A passenger-facing Full-HD digital screen driven by a 4G-connected Android media player with offline playback, remote monitoring and automatic content sync.', 1],
            ['Hardware & Technology', 'Do ads still play without internet?', 'Yes. Content is cached on the device so playback continues offline. Proof-of-play events are buffered and synced automatically when connectivity returns.', 2],
            ['Hardware & Technology', 'How do you monitor device health?', 'Devices send regular heartbeats reporting connectivity, storage, temperature, app version and current content, so operations can detect and resolve issues proactively.', 3],
            ['Billing & Analytics', 'How is pricing calculated?', 'Pricing is flexible — by campaign duration, number of autos/screens, package or custom quote. Talk to our team for a plan that fits your budget.', 1],
            ['Billing & Analytics', 'What analytics do I get?', 'Playback analytics, runtime, campaign delivery, proof-of-play, network performance, device health and campaign profitability — all in interactive dashboards and exportable reports.', 2],
            ['API & Integrations', 'Do you offer APIs?', 'Yes. We provide REST APIs for advertisers, devices, campaigns, playback and reporting, plus webhooks and scoped API keys for secure integration with your own systems.', 1],
        ];
        foreach ($faqs as [$cat, $q, $a, $order]) {
            Faq::firstOrCreate(['question' => $q], [
                'category' => $cat, 'answer' => $a, 'sort_order' => $order, 'status' => true,
            ]);
        }

        $plans = [
            ['Starter', 'Local businesses testing DOOH', '₹25,000', 'per month', [
                'Up to 15 autos', 'Single city targeting', '1 active campaign', 'Weekly proof-of-play reports', 'Email support',
            ], false, 1],
            ['Growth', 'Growing brands scaling reach', '₹75,000', 'per month', [
                'Up to 60 autos', 'Multi-area targeting', '3 active campaigns', 'Daily proof-of-play + analytics', 'Priority support', 'API access',
            ], true, 2],
            ['City Domination', 'Maximum city-wide presence', '₹2,00,000', 'per month', [
                'Up to 200 autos', 'Full city + route targeting', 'Unlimited campaigns', 'Real-time analytics dashboard', 'Dedicated account manager', 'Full API + webhooks',
            ], false, 3],
            ['Enterprise', 'Agencies & national brands', 'Custom', 'tailored quote', [
                'Unlimited autos & cities', 'Custom targeting & SLAs', 'White-glove onboarding', 'Custom integrations & data feeds', '24/7 support',
            ], false, 4],
        ];
        foreach ($plans as [$name, $tagline, $price, $note, $features, $featured, $order]) {
            PricingPlan::firstOrCreate(['name' => $name], [
                'slug' => \Illuminate\Support\Str::slug($name),
                'tagline' => $tagline,
                'price_label' => $price,
                'price_note' => $note,
                'features' => $features,
                'is_featured' => $featured,
                'cta_label' => $name === 'Enterprise' ? 'Talk to Sales' : 'Start Advertising',
                'cta_url' => '/contact?type=advertiser',
                'sort_order' => $order,
                'status' => true,
            ]);
        }

        EmailTemplate::firstOrCreate(['event' => 'lead.received'], [
            'name' => 'Website Enquiry Received',
            'subject' => 'We received your enquiry — {title}',
            'body' => '<h2>Hi {user_name},</h2><p>{message}</p><p>— The AutoAds Network Team</p>',
            'variables' => ['user_name', 'title', 'message'],
            'status' => true,
        ]);

        NotificationTemplate::firstOrCreate(['event' => 'lead.created'], [
            'name' => 'New Website Lead',
            'category' => 'lead',
            'default_type' => 'information',
            'channels' => ['in_app'],
            'status' => true,
        ]);
    }
}
