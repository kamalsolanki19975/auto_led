<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('mobile')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $t->string('pan')->nullable();
            $t->string('bank_name')->nullable();
            $t->string('bank_account')->nullable();
            $t->string('ifsc')->nullable();
            $t->string('upi')->nullable();
            $t->string('status')->default('active');
            $t->text('notes')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('drivers', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('mobile')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->string('license_no')->nullable();
            $t->date('license_expiry')->nullable();
            $t->foreignId('owner_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $t->string('bank_name')->nullable();
            $t->string('bank_account')->nullable();
            $t->string('ifsc')->nullable();
            $t->string('upi')->nullable();
            $t->string('status')->default('active');
            $t->text('notes')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('auto_groups', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('autos', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('registration_number')->unique();
            $t->string('vehicle_type')->default('auto_rickshaw');
            $t->string('manufacturer')->nullable();
            $t->string('model')->nullable();
            $t->year('manufacturing_year')->nullable();
            $t->string('chassis_number')->nullable();
            $t->string('engine_number')->nullable();
            $t->date('registration_date')->nullable();
            $t->foreignId('owner_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('primary_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $t->foreignId('secondary_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $t->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('auto_group_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('latitude', 10, 7)->nullable();  // GPS-ready
            $t->decimal('longitude', 10, 7)->nullable(); // GPS-ready
            $t->string('status')->default('available');
            $t->text('remarks')->nullable();
            $t->softDeletes();
            $t->timestamps();
            $t->index(['status', 'city_id']);
        });

        Schema::create('auto_group_auto', function (Blueprint $t) {
            $t->id();
            $t->foreignId('auto_group_id')->constrained()->cascadeOnDelete();
            $t->foreignId('auto_id')->constrained()->cascadeOnDelete();
            $t->unique(['auto_group_id', 'auto_id']);
        });

        Schema::create('screens', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('screen_type')->default('lcd');
            $t->string('brand')->nullable();
            $t->string('model')->nullable();
            $t->string('serial_number')->unique();
            $t->string('size')->nullable();
            $t->string('resolution')->nullable();
            $t->string('orientation')->default('landscape');
            $t->date('installation_date')->nullable();
            $t->date('warranty_end')->nullable();
            $t->unsignedBigInteger('current_auto_id')->nullable();
            $t->unsignedBigInteger('current_device_id')->nullable();
            $t->string('status')->default('inventory');
            $t->softDeletes();
            $t->timestamps();
            $t->index('status');
        });

        Schema::create('sims', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('mobile_number')->nullable();
            $t->string('iccid')->unique();
            $t->string('imsi')->nullable();
            $t->string('operator')->nullable();
            $t->string('plan')->nullable();
            $t->decimal('monthly_cost', 10, 2)->default(0);
            $t->integer('data_limit_mb')->default(0);
            $t->integer('data_used_mb')->default(0);
            $t->date('activation_date')->nullable();
            $t->date('renewal_date')->nullable();
            $t->unsignedBigInteger('current_device_id')->nullable();
            $t->unsignedBigInteger('current_auto_id')->nullable();
            $t->string('status')->default('available');
            $t->softDeletes();
            $t->timestamps();
            $t->index(['status', 'renewal_date']);
        });

        Schema::create('devices', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('device_uuid')->unique();
            $t->string('serial_number')->nullable();
            $t->string('android_version')->nullable();
            $t->string('app_version')->nullable();
            $t->string('hardware_model')->nullable();
            $t->string('imei')->nullable();
            $t->string('mac_address')->nullable();
            $t->string('ram')->nullable();
            $t->string('storage')->nullable();
            $t->string('cpu')->nullable();
            $t->decimal('temperature', 5, 2)->nullable();
            $t->foreignId('current_screen_id')->nullable()->constrained('screens')->nullOnDelete();
            $t->foreignId('current_auto_id')->nullable()->constrained('autos')->nullOnDelete();
            $t->foreignId('current_sim_id')->nullable()->constrained('sims')->nullOnDelete();
            $t->string('auth_token')->nullable();
            $t->timestamp('last_heartbeat_at')->nullable();
            $t->timestamp('last_sync_at')->nullable();
            $t->string('status')->default('registered');
            $t->json('config')->nullable();
            $t->softDeletes();
            $t->timestamps();
            $t->index('status');
        });

        Schema::create('device_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('device_id')->constrained()->cascadeOnDelete();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('screen_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('sim_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('assigned_at')->nullable();
            $t->timestamp('unassigned_at')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
            $t->index(['device_id', 'active']);
        });

        Schema::create('sim_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sim_id')->constrained()->cascadeOnDelete();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('assigned_at')->nullable();
            $t->timestamp('unassigned_at')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
            $t->index(['sim_id', 'active']);
        });
    }

    public function down(): void
    {
        foreach (['sim_assignments','device_assignments','devices','sims','screens','auto_group_auto','autos','auto_groups','drivers','owners'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
