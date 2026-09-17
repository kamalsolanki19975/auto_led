<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard' => ['view'],
            'network.auto' => ['view', 'create', 'update', 'delete'],
            'network.owner' => ['view', 'create', 'update', 'delete'],
            'network.driver' => ['view', 'create', 'update', 'delete'],
            'network.autogroup' => ['view', 'create', 'update', 'delete'],
            'network.screen' => ['view', 'create', 'update', 'delete'],
            'network.device' => ['view', 'create', 'update', 'delete', 'activate', 'restart'],
            'network.sim' => ['view', 'create', 'update', 'delete'],
            'advertising.advertiser' => ['view', 'create', 'update', 'delete'],
            'advertising.advertisement' => ['view', 'create', 'update', 'delete', 'approve'],
            'advertising.campaign' => ['view', 'create', 'update', 'delete', 'approve', 'activate'],
            'advertising.playlist' => ['view', 'create', 'update', 'delete'],
            'advertising.pop' => ['view'],
            'operations.installation' => ['view', 'create', 'update', 'delete'],
            'operations.asset' => ['view', 'create', 'update', 'delete'],
            'operations.inventory' => ['view', 'create', 'update'],
            'operations.maintenance' => ['view', 'create', 'update', 'delete'],
            'operations.warranty' => ['view', 'create', 'update'],
            'operations.vendor' => ['view', 'create', 'update', 'delete'],
            'finance.revenue' => ['view', 'create'],
            'finance.invoice' => ['view', 'create', 'update', 'delete'],
            'finance.payment' => ['view', 'create'],
            'finance.expense' => ['view', 'create', 'update', 'delete'],
            'finance.ratecard' => ['view', 'create', 'update', 'delete'],
            'finance.earning' => ['view'],
            'finance.settlement' => ['view', 'create', 'approve', 'pay'],
            'finance.profitability' => ['view'],
            'reports' => ['view'],
            'integrations.api' => ['view', 'create', 'update', 'delete'],
            'integrations.webhook' => ['view', 'create', 'update', 'delete'],
            'admin.user' => ['view', 'create', 'update', 'delete'],
            'admin.role' => ['view', 'create', 'update', 'delete'],
            'admin.notification' => ['view'],
            'admin.audit' => ['view'],
            'admin.settings' => ['view', 'update'],
        ];

        $allSlugs = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $slug = $module.'.'.$action;
                $allSlugs[] = $slug;
                Permission::updateOrCreate(['slug' => $slug], [
                    'name' => ucwords(str_replace(['.', '_'], ' ', $slug)),
                    'module' => $module,
                    'action' => $action,
                ]);
            }
        }

        $perm = fn (array $prefixes) => Permission::where(function ($q) use ($prefixes) {
            foreach ($prefixes as $p) {
                $q->orWhere('slug', 'like', $p.'%');
            }
        })->pluck('id')->all();

        $roles = [
            ['super-admin', 'Super Admin', 'admin', 'all'],
            ['administrator', 'Administrator', 'admin', 'all'],
            ['operations-manager', 'Operations Manager', 'admin', ['dashboard', 'network.', 'operations.', 'reports']],
            ['advertising-manager', 'Advertising Manager', 'admin', ['dashboard', 'advertising.', 'reports']],
            ['finance-manager', 'Finance Manager', 'admin', ['dashboard', 'finance.', 'reports']],
            ['sales-user', 'Sales User', 'admin', ['dashboard', 'advertising.advertiser', 'advertising.campaign.view', 'advertising.campaign.create']],
            ['technician', 'Technician', 'technician', ['dashboard', 'operations.installation', 'operations.maintenance', 'network.device', 'network.sim.view']],
            ['advertiser', 'Advertiser', 'advertiser', ['advertising.advertisement.view', 'advertising.advertisement.create', 'advertising.campaign.view']],
            ['driver', 'Driver', 'driver', []],
            ['auto-owner', 'Auto Owner', 'owner', []],
        ];

        foreach ($roles as [$slug, $name, $portal, $scope]) {
            $role = Role::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'portal' => $portal, 'is_system' => true,
                'description' => $name.' role',
            ]);
            $ids = $scope === 'all' ? Permission::pluck('id')->all() : $perm($scope);
            $role->permissions()->sync($ids);
        }

        // Users
        $users = [
            ['Super Admin', 'admin@autoads.test', 'super-admin'],
            ['Operations Manager', 'ops@autoads.test', 'operations-manager'],
            ['Advertising Manager', 'ads@autoads.test', 'advertising-manager'],
            ['Finance Manager', 'finance@autoads.test', 'finance-manager'],
            ['Sales User', 'sales@autoads.test', 'sales-user'],
            ['Technician', 'tech@autoads.test', 'technician'],
        ];
        foreach ($users as [$name, $email, $roleSlug]) {
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('Admin@123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $user->roles()->sync([Role::where('slug', $roleSlug)->value('id')]);
        }
    }
}
