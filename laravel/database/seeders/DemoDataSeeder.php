<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Advertiser;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Auto;
use App\Models\AutoGroup;
use App\Models\Campaign;
use App\Models\City;
use App\Models\Company;
use App\Models\Device;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Installation;
use App\Models\MaintenanceTicket;
use App\Models\Owner;
use App\Models\PlaybackEvent;
use App\Models\RateCard;
use App\Models\Role;
use App\Models\Screen;
use App\Models\Sim;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Warranty;
use App\Services\CampaignService;
use App\Services\DeviceService;
use App\Services\InvoiceService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use App\Services\ProofOfPlayService;
use App\Services\SettlementService;
use App\Support\Codes;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Auto::count() > 0) {
            return; // idempotent guard
        }
        $faker = \Faker\Factory::create('en_IN');

        $company = Company::create(['name' => 'AutoAds Network Pvt Ltd', 'email' => 'hello@autoads.network', 'phone' => '+91 80 4000 0000']);

        $blr = City::create(['company_id' => $company->id, 'name' => 'Bengaluru', 'code' => 'BLR', 'state' => 'Karnataka']);
        $mys = City::create(['company_id' => $company->id, 'name' => 'Mysuru', 'code' => 'MYS', 'state' => 'Karnataka']);

        $areaNames = ['Koramangala', 'Indiranagar', 'Whitefield', 'Jayanagar', 'Electronic City', 'Hebbal', 'MG Road', 'HSR Layout'];
        $areas = [];
        foreach ($areaNames as $an) {
            $areas[] = Area::create(['city_id' => $blr->id, 'name' => $an, 'pincode' => (string) $faker->numberBetween(560001, 560100)]);
        }

        $groupNames = ['Bengaluru North', 'Bengaluru South', 'Bengaluru East', 'Bengaluru West', 'Airport Autos', 'Premium Autos', 'High Traffic Autos'];
        $groups = [];
        foreach ($groupNames as $gn) {
            $groups[] = AutoGroup::create(['code' => Codes::next('auto_groups', 'GRP'), 'name' => $gn, 'city_id' => $blr->id, 'type' => 'zone']);
        }

        // Owners
        $owners = collect();
        for ($i = 0; $i < 20; $i++) {
            $owners->push(Owner::create([
                'code' => Codes::next('owners', 'OWN'),
                'name' => $faker->name(),
                'mobile' => '9'.$faker->numberBetween(100000000, 999999999),
                'email' => $faker->unique()->safeEmail(),
                'address' => $faker->address(),
                'city_id' => $blr->id,
                'pan' => strtoupper($faker->bothify('?????####?')),
                'bank_name' => $faker->randomElement(['HDFC Bank', 'ICICI Bank', 'SBI', 'Axis Bank']),
                'bank_account' => (string) $faker->numberBetween(10000000000, 99999999999),
                'ifsc' => strtoupper($faker->bothify('????0######')),
                'upi' => Str::slug($faker->firstName()).'@okhdfcbank',
                'status' => 'active',
            ]));
        }

        // Drivers
        $drivers = collect();
        for ($i = 0; $i < 40; $i++) {
            $owner = $owners->random();
            $drivers->push(Driver::create([
                'code' => Codes::next('drivers', 'DRV'),
                'name' => $faker->name('male'),
                'mobile' => '9'.$faker->numberBetween(100000000, 999999999),
                'email' => $faker->unique()->safeEmail(),
                'address' => $faker->address(),
                'license_no' => strtoupper($faker->bothify('KA##########')),
                'license_expiry' => now()->addMonths($faker->numberBetween(-2, 40)),
                'owner_id' => $owner->id,
                'city_id' => $blr->id,
                'bank_name' => $faker->randomElement(['HDFC Bank', 'ICICI Bank', 'SBI']),
                'bank_account' => (string) $faker->numberBetween(10000000000, 99999999999),
                'ifsc' => strtoupper($faker->bothify('????0######')),
                'upi' => Str::slug($faker->firstName()).'@oksbi',
                'status' => 'active',
            ]));
        }

        // Rate cards
        RateCard::create(['code' => Codes::next('rate_cards', 'RC'), 'city_id' => $blr->id, 'auto_type' => 'auto_rickshaw', 'campaign_type' => 'paid', 'rate_basis' => 'per_play', 'rate' => 0.85, 'minimum_guarantee' => 1500, 'bonus' => 200, 'valid_from' => now()->subMonths(6), 'status' => 'active']);
        RateCard::create(['code' => Codes::next('rate_cards', 'RC'), 'city_id' => $blr->id, 'auto_type' => 'auto_rickshaw', 'campaign_type' => 'house', 'rate_basis' => 'per_hour', 'rate' => 12, 'minimum_guarantee' => 500, 'valid_from' => now()->subMonths(6), 'status' => 'active']);

        $deviceService = app(DeviceService::class);

        // 50 autos + screen + device + sim
        $autoStatuses = array_merge(array_fill(0, 42, 'active'), ['installation_pending', 'installation_pending', 'screen_installed', 'under_maintenance', 'available', 'available', 'suspended', 'inactive']);
        shuffle($autoStatuses);
        $autos = collect();
        $devices = collect();
        $operators = ['Jio', 'Airtel', 'Vi', 'BSNL'];

        for ($i = 1; $i <= 50; $i++) {
            $owner = $owners->random();
            $driver = $drivers->where('owner_id', $owner->id)->first() ?? $drivers->random();
            $status = $autoStatuses[$i - 1];
            $auto = Auto::create([
                'code' => Codes::next('autos', 'AUTO'),
                'registration_number' => sprintf('KA%02dAB%04d', $faker->numberBetween(1, 5), 1000 + $i),
                'vehicle_type' => 'auto_rickshaw',
                'manufacturer' => $faker->randomElement(['Bajaj', 'Piaggio', 'TVS', 'Mahindra']),
                'model' => $faker->randomElement(['RE Compact', 'Ape City', 'King Duramax']),
                'manufacturing_year' => $faker->numberBetween(2018, 2024),
                'chassis_number' => strtoupper($faker->bothify('MD2???########')),
                'engine_number' => strtoupper($faker->bothify('EN########')),
                'registration_date' => now()->subMonths($faker->numberBetween(6, 60)),
                'owner_id' => $owner->id,
                'primary_driver_id' => $driver->id,
                'city_id' => $blr->id,
                'area_id' => $faker->randomElement($areas)->id,
                'auto_group_id' => $faker->randomElement($groups)->id,
                'status' => $status,
                'latitude' => $faker->latitude(12.85, 13.10),
                'longitude' => $faker->longitude(77.50, 77.75),
            ]);
            $auto->groups()->attach($faker->randomElement($groups)->id);
            $autos->push($auto);

            $installed = in_array($status, ['active', 'screen_installed', 'under_maintenance']);

            $screen = Screen::create([
                'code' => Codes::next('screens', 'SCR'),
                'screen_type' => 'lcd', 'brand' => $faker->randomElement(['Samsung', 'LG', 'BOE']),
                'model' => $faker->bothify('DS-###'),
                'serial_number' => strtoupper($faker->bothify('SCR#########')),
                'size' => '15.6"', 'resolution' => '1920x1080', 'orientation' => 'landscape',
                'installation_date' => $installed ? now()->subMonths($faker->numberBetween(1, 18)) : null,
                'warranty_end' => now()->addMonths($faker->numberBetween(-3, 24)),
                'status' => $installed ? 'active' : 'inventory',
            ]);

            $sim = Sim::create([
                'code' => Codes::next('sims', 'SIM'),
                'mobile_number' => '9'.$faker->numberBetween(100000000, 999999999),
                'iccid' => (string) $faker->numerify('8991########0#######'),
                'imsi' => (string) $faker->numerify('40492##########'),
                'operator' => $faker->randomElement($operators),
                'plan' => $faker->randomElement(['5GB/month', '10GB/month', 'Unlimited']),
                'monthly_cost' => $faker->randomElement([199, 249, 299, 349]),
                'data_limit_mb' => $faker->randomElement([5120, 10240, 20480]),
                'data_used_mb' => $faker->numberBetween(500, 21000),
                'activation_date' => now()->subMonths($faker->numberBetween(1, 20)),
                'renewal_date' => now()->addDays($faker->numberBetween(-5, 45)),
                'status' => $installed ? 'active' : 'available',
            ]);

            $device = Device::create([
                'code' => Codes::next('devices', 'DEV'),
                'device_uuid' => (string) Str::uuid(),
                'serial_number' => strtoupper($faker->bothify('AND########')),
                'android_version' => $faker->randomElement(['11', '12', '13']),
                'app_version' => '2.4.'.$faker->numberBetween(0, 9),
                'hardware_model' => $faker->randomElement(['RK3566 Media Box', 'Amlogic S905', 'Generic A133']),
                'imei' => (string) $faker->numerify('35############'),
                'mac_address' => $faker->macAddress(),
                'ram' => '2GB', 'storage' => '32GB', 'cpu' => 'Quad-core',
                'temperature' => $faker->randomFloat(1, 32, 58),
                'status' => $installed ? 'active' : 'registered',
            ]);
            // auth token
            $device->update(['auth_token' => Hash::make('demo-'.$device->device_uuid)]);
            $devices->push($device);

            if ($installed) {
                $deviceService->activate($device, $screen->id, $auto->id, $sim->id);
                // heartbeat freshness: most online, some offline
                $online = $faker->boolean(80) && $status === 'active';
                $device->update([
                    'last_heartbeat_at' => $online ? now()->subSeconds($faker->numberBetween(5, 120)) : now()->subMinutes($faker->numberBetween(10, 2000)),
                    'last_sync_at' => now()->subMinutes($faker->numberBetween(1, 60)),
                ]);
                if ($status === 'under_maintenance') {
                    $device->update(['status' => 'maintenance']);
                }
            }
        }

        // Advertisers (+ portal user)
        $adCompanies = ['Zomato', 'Swiggy', 'CRED', 'Byju\'s', 'Ola Electric', 'Cult.fit', 'Nykaa', 'PhonePe', 'Urban Company', 'BigBasket'];
        $advertisers = collect();
        foreach ($adCompanies as $idx => $name) {
            $advertisers->push(Advertiser::create([
                'code' => Codes::next('advertisers', 'ADV'),
                'company_name' => $name,
                'contact_person' => $faker->name(),
                'mobile' => '9'.$faker->numberBetween(100000000, 999999999),
                'email' => 'ads@'.Str::slug($name).'.com',
                'address' => $faker->address(),
                'gstin' => strtoupper($faker->bothify('29?????####?#Z#')),
                'pan' => strtoupper($faker->bothify('?????####?')),
                'payment_terms' => $faker->randomElement(['Net 15', 'Net 30', 'Advance']),
                'credit_limit' => $faker->randomElement([100000, 250000, 500000]),
                'status' => 'active',
            ]));
        }

        // Advertisements
        $contentTypes = ['image', 'video', 'text', 'html'];
        $ads = collect();
        foreach ($advertisers as $advertiser) {
            $n = $faker->numberBetween(2, 4);
            for ($j = 0; $j < $n; $j++) {
                $approval = $faker->randomElement(['approved', 'approved', 'approved', 'draft', 'submitted', 'rejected']);
                $ads->push(Advertisement::create([
                    'code' => Codes::next('advertisements', 'AD'),
                    'advertiser_id' => $advertiser->id,
                    'title' => $advertiser->company_name.' '.$faker->randomElement(['Festive Offer', 'Brand Film', 'Summer Sale', 'New Launch', 'Cashback']),
                    'description' => $faker->sentence(),
                    'content_type' => $faker->randomElement($contentTypes),
                    'duration' => $faker->randomElement([10, 15, 20, 30]),
                    'resolution' => '1920x1080', 'orientation' => 'landscape',
                    'start_date' => now()->subDays(30), 'end_date' => now()->addDays(60),
                    'approval_status' => $approval,
                    'approved_at' => $approval === 'approved' ? now()->subDays($faker->numberBetween(1, 20)) : null,
                    'rejection_reason' => $approval === 'rejected' ? 'Creative resolution below requirement.' : null,
                    'category' => $faker->randomElement(['Food', 'Finance', 'Education', 'Retail', 'Health']),
                    'tags' => [$faker->word(), $faker->word()],
                    'status' => 'active',
                ]));
            }
        }

        // Campaigns
        $campaignService = app(CampaignService::class);
        $invoiceService = app(InvoiceService::class);
        $paymentService = app(PaymentService::class);
        $settlementService = app(SettlementService::class);
        $pop = app(ProofOfPlayService::class);

        $activeAutos = $autos->where('status', 'active')->values();
        $statusPlan = ['active', 'active', 'active', 'active', 'under_delivery', 'under_delivery', 'completed', 'completed', 'scheduled', 'scheduled', 'paused', 'draft', 'draft', 'pending_approval', 'active'];
        $campaigns = collect();

        foreach ($statusPlan as $ci => $cstatus) {
            $advertiser = $advertisers->random();
            $approvedAds = $ads->where('advertiser_id', $advertiser->id)->where('approval_status', 'approved')->values();
            if ($approvedAds->isEmpty()) {
                $approvedAds = $ads->where('approval_status', 'approved')->values();
            }
            $start = now()->subDays($faker->numberBetween(5, 25));
            $end = (clone $start)->addDays($faker->numberBetween(20, 45));
            $budget = $faker->randomElement([50000, 75000, 100000, 150000, 200000]);

            $campaign = Campaign::create([
                'code' => Codes::next('campaigns', 'CMP'),
                'advertiser_id' => $advertiser->id,
                'name' => $advertiser->company_name.' '.$faker->randomElement(['Q3 Push', 'City Blast', 'Awareness', 'Performance', 'Launch']).' '.($ci + 1),
                'campaign_type' => 'paid',
                'start_date' => $start, 'end_date' => $end,
                'budget' => $budget,
                'pricing_model' => 'per_play', 'rate' => $faker->randomElement([1.5, 2.0, 2.5]),
                'target_type' => 'autos', 'priority' => 2,
                'schedule' => ['days' => ['mon', 'tue', 'wed', 'thu', 'fri'], 'slots' => [['08:00', '11:00'], ['16:00', '20:00']]],
                'status' => $cstatus,
                'expected_plays' => $faker->numberBetween(8000, 40000),
                'activated_at' => in_array($cstatus, ['active', 'under_delivery', 'completed', 'paused']) ? $start : null,
                'completed_at' => $cstatus === 'completed' ? $end : null,
            ]);
            if ($approvedAds->isNotEmpty()) {
                $campaign->advertisements()->sync($approvedAds->take(2)->pluck('id')->toArray());
            }
            // assign autos
            $assignCount = $faker->numberBetween(5, 9);
            $picked = $activeAutos->shuffle()->take($assignCount);
            $campaignService->assignAutos($campaign, $picked->pluck('id')->toArray());
            if (in_array($cstatus, ['active', 'under_delivery', 'completed', 'paused'])) {
                $campaign->assignments()->update(['status' => 'active']);
            }
            $campaigns->push($campaign);

            // Simulate playback for live/completed campaigns
            if (in_array($cstatus, ['active', 'under_delivery', 'completed'])) {
                $this->simulatePlayback($campaign, $picked, $approvedAds, $pop, $faker, $cstatus === 'under_delivery');
                // invoice + payment + revenue
                $invoice = $invoiceService->createForCampaign($campaign, ['amount' => $budget], 1);
                if ($faker->boolean(70)) {
                    $paymentService->recordReceived([
                        'invoice_id' => $invoice->id,
                        'amount' => $faker->boolean(60) ? $invoice->total : round($invoice->total * 0.5, 2),
                        'method' => $faker->randomElement(['bank_transfer', 'upi', 'gateway']),
                        'reference' => 'TXN'.$faker->numerify('########'),
                    ], 1);
                } else {
                    // leave overdue
                    $invoice->update(['due_date' => now()->subDays(10), 'status' => 'overdue']);
                }
            }
        }

        // Settlements + earnings for a subset of drivers with runtime
        $driversWithRuntime = \App\Models\RuntimeLog::distinct()->pluck('driver_id')->filter();
        foreach ($driversWithRuntime->take(15) as $drvId) {
            $driver = Driver::find($drvId);
            if (! $driver) { continue; }
            $settlement = $settlementService->create($driver, now()->startOfMonth(), now());
            if ($faker->boolean(60)) {
                $settlementService->approve($settlement, 1);
                if ($faker->boolean(50)) {
                    $settlementService->pay($settlement, ['method' => 'bank_transfer', 'reference' => 'NEFT'.$faker->numerify('#######')], 1);
                }
            }
        }

        // Vendors, assets, warranties
        $vendors = collect();
        foreach (['ScreenTech Solutions', 'PowerSafe Automotive', 'ConnectSIM Distributors', 'FieldFix Services'] as $vn) {
            $vendors->push(Vendor::create(['code' => Codes::next('vendors', 'VEN'), 'name' => $vn, 'contact_person' => $faker->name(), 'phone' => '9'.$faker->numerify('#########'), 'email' => Str::slug($vn).'@vendor.com', 'gstin' => strtoupper($faker->bothify('29?????####?#Z#')), 'services' => 'Hardware & Field', 'payment_terms' => 'Net 30', 'status' => 'active']));
        }
        for ($i = 0; $i < 20; $i++) {
            Asset::create([
                'code' => Codes::next('assets', 'AST'),
                'asset_type' => $faker->randomElement(['power_controller', 'mounting_bracket', 'cable', 'adapter', 'accessory']),
                'name' => $faker->randomElement(['12V Power Controller', 'Universal Mount', 'HDMI Cable', 'DC Adapter', 'Surge Protector']),
                'serial_number' => strtoupper($faker->bothify('AST#######')),
                'vendor_id' => $vendors->random()->id,
                'purchase_date' => now()->subMonths($faker->numberBetween(1, 24)),
                'cost' => $faker->numberBetween(300, 3500),
                'warranty_end' => now()->addMonths($faker->numberBetween(-2, 18)),
                'location' => 'Central Warehouse',
                'status' => $faker->randomElement(['in_stock', 'assigned', 'assigned']),
            ]);
        }
        foreach ($autos->take(10) as $auto) {
            Warranty::create(['asset_type' => 'screen', 'asset_id' => $auto->screen?->id, 'provider' => 'Samsung Care', 'start_date' => now()->subMonths(6), 'end_date' => now()->addDays($faker->numberBetween(-10, 60)), 'terms' => '1 year onsite', 'claim_status' => 'none']);
        }

        // Maintenance tickets
        foreach ($autos->where('status', 'active')->take(8) as $auto) {
            MaintenanceTicket::create([
                'code' => Codes::next('maintenance_tickets', 'TKT'),
                'auto_id' => $auto->id,
                'screen_id' => $auto->screen?->id,
                'device_id' => $auto->device?->id,
                'issue' => $faker->randomElement(['Screen flickering', 'Device not booting', 'SIM no signal', 'Power intermittent']),
                'description' => $faker->sentence(),
                'priority' => $faker->randomElement(['low', 'medium', 'high', 'critical']),
                'status' => $faker->randomElement(['open', 'assigned', 'in_progress', 'resolved']),
                'cost' => $faker->numberBetween(0, 2500),
                'created_by' => 1,
            ]);
        }

        // Installations (pending autos)
        foreach ($autos->whereIn('status', ['installation_pending', 'screen_installed'])->take(5) as $auto) {
            Installation::create([
                'code' => Codes::next('installations', 'INS'),
                'auto_id' => $auto->id,
                'technician_id' => User::where('email', 'tech@autoads.test')->value('id'),
                'scheduled_at' => now()->addDays($faker->numberBetween(1, 7)),
                'status' => 'scheduled',
                'checklist' => ['screen_installed' => false, 'power_checked' => false, 'internet_tested' => false, 'heartbeat_received' => false],
            ]);
        }

        // Operating expenses: SIM monthly + hardware
        foreach (Sim::where('status', 'active')->get() as $sim) {
            Expense::create(['code' => Codes::next('expenses', 'EXP'), 'date' => now()->startOfMonth(), 'category' => 'sim', 'amount' => $sim->monthly_cost, 'payment_status' => 'paid', 'sim_id' => $sim->id, 'auto_id' => $sim->current_auto_id, 'notes' => 'Monthly connectivity '.$sim->code]);
        }
        Expense::create(['code' => Codes::next('expenses', 'EXP'), 'date' => now()->subDays(20), 'category' => 'cloud', 'amount' => 18000, 'payment_status' => 'paid', 'notes' => 'Cloud & storage']);
        Expense::create(['code' => Codes::next('expenses', 'EXP'), 'date' => now()->subDays(12), 'category' => 'hardware', 'amount' => 145000, 'payment_status' => 'unpaid', 'notes' => 'Media player procurement']);

        // Portal users linked to entities
        $advUser = User::updateOrCreate(['email' => 'advertiser@autoads.test'], ['name' => $advertisers->first()->contact_person ?? 'Advertiser', 'password' => Hash::make('Admin@123'), 'status' => 'active', 'advertiser_id' => $advertisers->first()->id, 'email_verified_at' => now()]);
        $advUser->roles()->sync([Role::where('slug', 'advertiser')->value('id')]);

        $demoDriver = Driver::find($driversWithRuntime->first()) ?? $drivers->first();
        $drvUser = User::updateOrCreate(['email' => 'driver@autoads.test'], ['name' => $demoDriver->name, 'phone' => $demoDriver->mobile, 'password' => Hash::make('Admin@123'), 'status' => 'active', 'driver_id' => $demoDriver->id, 'email_verified_at' => now()]);
        $drvUser->roles()->sync([Role::where('slug', 'driver')->value('id')]);

        $ownerUser = User::updateOrCreate(['email' => 'owner@autoads.test'], ['name' => $demoDriver->owner?->name ?? 'Owner', 'password' => Hash::make('Admin@123'), 'status' => 'active', 'owner_id' => $demoDriver->owner_id, 'email_verified_at' => now()]);
        $ownerUser->roles()->sync([Role::where('slug', 'auto-owner')->value('id')]);

        $this->generateAlerts($faker);
    }

    protected function simulatePlayback(Campaign $campaign, $autos, $ads, ProofOfPlayService $pop, $faker, bool $under): void
    {
        $ad = $ads->first();
        $days = 10;
        $playsPerDay = $under ? 1 : $faker->numberBetween(3, 5);

        foreach ($autos as $auto) {
            $device = $auto->device;
            if (! $device) { continue; }
            for ($d = $days; $d >= 1; $d--) {
                for ($p = 0; $p < $playsPerDay; $p++) {
                    $ts = now()->subDays($d)->setTime($faker->numberBetween(8, 20), $faker->numberBetween(0, 59));
                    $expected = $ad?->duration ?? 15;
                    $completion = $faker->boolean(88) ? 100 : $faker->numberBetween(40, 95);
                    $actual = (int) round($expected * $completion / 100);
                    $event = PlaybackEvent::create([
                        'event_id' => (string) Str::uuid(),
                        'device_id' => $device->id,
                        'screen_id' => $device->current_screen_id,
                        'auto_id' => $auto->id,
                        'campaign_id' => $campaign->id,
                        'advertisement_id' => $ad?->id,
                        'driver_id' => $auto->primary_driver_id,
                        'start_time' => $ts,
                        'end_time' => (clone $ts)->addSeconds($actual),
                        'duration' => $actual,
                        'expected_duration' => $expected,
                        'actual_duration' => $actual,
                        'completion_percent' => $completion,
                        'device_timestamp' => $ts,
                        'server_timestamp' => $ts,
                        'network_status' => 'online',
                        'player_version' => $device->app_version,
                        'sync_timestamp' => $ts,
                        'status' => 'received',
                    ]);
                    $pop->validate($event);
                }
            }
        }
    }

    protected function generateAlerts($faker): void
    {
        $notifier = app(NotificationService::class);

        $offlineDevice = Device::where('status', 'active')->where('last_heartbeat_at', '<', now()->subMinutes(10))->first();
        if ($offlineDevice) {
            $notifier->notifyAdmins('device.offline', ['type' => 'critical', 'category' => 'device', 'title' => 'Device offline', 'message' => 'Device '.$offlineDevice->code.' has missed its heartbeat.', 'related_type' => 'Device', 'related_id' => $offlineDevice->id, 'device_id' => $offlineDevice->code, 'auto_number' => $offlineDevice->auto?->registration_number]);
        }
        $under = Campaign::where('status', 'under_delivery')->first();
        if ($under) {
            $notifier->notifyAdmins('campaign.under_delivery', ['type' => 'warning', 'category' => 'campaign', 'title' => 'Campaign under-delivery', 'message' => $under->name.' is under-delivering.', 'related_type' => 'Campaign', 'related_id' => $under->id, 'campaign_name' => $under->name]);
        }
        $overdue = \App\Models\Invoice::where('status', 'overdue')->first();
        if ($overdue) {
            $notifier->notifyAdmins('invoice.overdue', ['type' => 'critical', 'category' => 'finance', 'title' => 'Invoice overdue', 'message' => 'Invoice '.$overdue->number.' is overdue.', 'related_type' => 'Invoice', 'related_id' => $overdue->id, 'invoice_number' => $overdue->number, 'amount' => $overdue->total]);
        }
        $expiringSim = Sim::whereBetween('renewal_date', [now(), now()->addDays(15)])->first();
        if ($expiringSim) {
            $notifier->notifyAdmins('sim.expiry', ['type' => 'warning', 'category' => 'sim', 'title' => 'SIM expiring', 'message' => 'SIM '.$expiringSim->code.' renews on '.$expiringSim->renewal_date?->format('d M'), 'related_type' => 'Sim', 'related_id' => $expiringSim->id]);
        }
        $ticket = MaintenanceTicket::first();
        if ($ticket) {
            $notifier->notifyAdmins('maintenance.created', ['type' => 'warning', 'category' => 'operations', 'title' => 'Maintenance ticket', 'message' => $ticket->code.': '.$ticket->issue, 'related_type' => 'MaintenanceTicket', 'related_id' => $ticket->id]);
        }
        $paid = \App\Models\Payment::where('type', 'received')->first();
        if ($paid) {
            $notifier->notifyAdmins('payment.received', ['type' => 'success', 'category' => 'finance', 'title' => 'Payment received', 'message' => 'Payment '.$paid->code.' of ₹'.number_format($paid->amount).' received.', 'related_type' => 'Payment', 'related_id' => $paid->id]);
        }
        $settlement = \App\Models\DriverSettlement::whereIn('status', ['draft', 'review'])->first();
        if ($settlement) {
            $notifier->notifyAdmins('settlement.pending', ['type' => 'warning', 'category' => 'finance', 'title' => 'Settlement pending', 'message' => 'Settlement '.$settlement->code.' awaits approval.', 'related_type' => 'DriverSettlement', 'related_id' => $settlement->id]);
        }
    }
}
