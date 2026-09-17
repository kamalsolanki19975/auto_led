<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('contact_person')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->string('gstin')->nullable();
            $t->string('services')->nullable();
            $t->string('payment_terms')->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
        });

        Schema::create('installations', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('screen_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('sim_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->string('status')->default('scheduled'); // scheduled, in_progress, testing, approval, completed, activated, cancelled
            $t->json('checklist')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index('status');
        });

        Schema::create('assets', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('asset_type'); // screen, device, power_controller, mounting_bracket, cable, adapter, accessory
            $t->string('name');
            $t->string('serial_number')->nullable();
            $t->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $t->date('purchase_date')->nullable();
            $t->decimal('cost', 12, 2)->default(0);
            $t->date('warranty_end')->nullable();
            $t->string('location')->nullable();
            $t->foreignId('current_auto_id')->nullable()->constrained('autos')->nullOnDelete();
            $t->string('status')->default('in_stock');
            $t->timestamps();
            $t->index(['asset_type', 'status']);
        });

        Schema::create('inventory_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type'); // purchase, receipt, assignment, transfer, replacement, return, scrap
            $t->string('reference')->nullable();
            $t->integer('quantity')->default(1);
            $t->string('from_location')->nullable();
            $t->string('to_location')->nullable();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['type', 'created_at']);
        });

        Schema::create('warranties', function (Blueprint $t) {
            $t->id();
            $t->string('asset_type');
            $t->unsignedBigInteger('asset_id')->nullable();
            $t->string('provider')->nullable();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->text('terms')->nullable();
            $t->string('claim_status')->default('none');
            $t->json('claims')->nullable();
            $t->timestamps();
            $t->index(['asset_type', 'asset_id']);
            $t->index('end_date');
        });

        Schema::create('maintenance_tickets', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('screen_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('sim_id')->nullable()->constrained()->nullOnDelete();
            $t->string('issue');
            $t->text('description')->nullable();
            $t->string('priority')->default('medium'); // low, medium, high, critical
            $t->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('sla_due')->nullable();
            $t->string('status')->default('open'); // open, assigned, in_progress, waiting, resolved, closed
            $t->decimal('cost', 12, 2)->default(0);
            $t->text('resolution')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->softDeletes();
            $t->timestamps();
            $t->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        foreach (['maintenance_tickets','warranties','inventory_transactions','assets','installations','vendors'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
