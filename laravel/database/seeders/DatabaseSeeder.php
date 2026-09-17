<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            TemplateSeeder::class,
            DemoDataSeeder::class,
            WebsiteSeeder::class,
        ]);
    }
}
