<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_cards', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $t->string('auto_type')->nullable();
            $t->string('campaign_type')->nullable();
            $t->string('rate_basis')->default('per_play'); // per_hour, per_play, per_day, per_campaign, hybrid
            $t->decimal('rate', 12, 4)->default(0);
            $t->decimal('minimum_guarantee', 12, 2)->default(0);
            $t->decimal('bonus', 12, 2)->default(0);
            $t->decimal('penalty', 12, 2)->default(0);
            $t->date('valid_from')->nullable();
            $t->date('valid_to')->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
            $t->index(['status', 'rate_basis']);
        });

        Schema::create('driver_settlements', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $t->foreignId('owner_id')->nullable()->constrained()->nullOnDelete();
            $t->date('period_start');
            $t->date('period_end');
            $t->decimal('total_earnings', 14, 2)->default(0);
            $t->decimal('total_adjustments', 14, 2)->default(0);
            $t->decimal('net_payable', 14, 2)->default(0);
            $t->string('status')->default('draft'); // draft, review, approved, paid
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->json('calculation_snapshot')->nullable();
            $t->softDeletes();
            $t->timestamps();
            $t->index(['driver_id', 'status']);
        });

        Schema::create('driver_earnings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('rate_card_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('settlement_id')->nullable()->constrained('driver_settlements')->nullOnDelete();
            $t->date('period_start');
            $t->date('period_end');
            $t->string('calculation_basis')->nullable();
            $t->decimal('quantity', 14, 2)->default(0);
            $t->decimal('rate', 12, 4)->default(0);
            $t->decimal('gross_amount', 14, 2)->default(0);
            $t->decimal('bonus', 12, 2)->default(0);
            $t->decimal('penalty', 12, 2)->default(0);
            $t->decimal('adjustment', 12, 2)->default(0);
            $t->decimal('net_amount', 14, 2)->default(0);
            $t->integer('valid_runtime_seconds')->default(0);
            $t->integer('valid_plays')->default(0);
            $t->integer('calculation_version')->default(1);
            $t->timestamp('calculated_at')->nullable();
            $t->string('status')->default('calculated'); // calculated, settled
            $t->timestamps();
            $t->index(['driver_id', 'period_start']);
        });

        Schema::create('driver_payments', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('settlement_id')->nullable()->constrained('driver_settlements')->nullOnDelete();
            $t->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $t->decimal('amount', 14, 2)->default(0);
            $t->string('method')->default('bank_transfer');
            $t->string('reference')->nullable();
            $t->string('bank')->nullable();
            $t->string('upi')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('driver_disputes', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('settlement_id')->nullable()->constrained('driver_settlements')->nullOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('owner_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('disputed_amount', 14, 2)->default(0);
            $t->text('reason');
            $t->string('evidence')->nullable();
            $t->string('status')->default('open'); // open, review, resolved, rejected
            $t->text('resolution')->nullable();
            $t->decimal('adjustment', 12, 2)->default(0);
            $t->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });

        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();
            $t->foreignId('advertiser_id')->constrained()->cascadeOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->date('invoice_date');
            $t->date('due_date')->nullable();
            $t->decimal('amount', 14, 2)->default(0);
            $t->decimal('tax_percent', 5, 2)->default(18);
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->decimal('total', 14, 2)->default(0);
            $t->decimal('amount_paid', 14, 2)->default(0);
            $t->string('status')->default('draft'); // draft, issued, partially_paid, paid, overdue, cancelled
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->softDeletes();
            $t->timestamps();
            $t->index(['status', 'advertiser_id']);
        });

        Schema::create('invoice_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $t->string('description');
            $t->decimal('quantity', 12, 2)->default(1);
            $t->decimal('rate', 12, 2)->default(0);
            $t->decimal('amount', 14, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('type')->default('received'); // received, made
            $t->foreignId('advertiser_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('amount', 14, 2)->default(0);
            $t->string('method')->default('bank_transfer'); // bank_transfer, upi, cash, gateway, other
            $t->string('reference')->nullable();
            $t->string('bank')->nullable();
            $t->string('upi')->nullable();
            $t->string('gateway')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['type', 'paid_at']);
        });

        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->date('date');
            $t->string('category'); // driver_payment, sim, hardware, installation, maintenance, cloud, storage, cdn, sms, whatsapp, api, vendor, other
            $t->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('amount', 14, 2)->default(0);
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->string('payment_status')->default('unpaid'); // unpaid, paid
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('sim_id')->nullable()->constrained()->nullOnDelete();
            $t->string('attachment')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['category', 'date']);
        });

        Schema::create('revenues', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('source')->default('campaign'); // campaign, other
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('advertiser_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('amount', 14, 2)->default(0);
            $t->date('date');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['source', 'date']);
        });

        Schema::create('cost_allocations', function (Blueprint $t) {
            $t->id();
            $t->string('type'); // sim, hardware, maintenance, platform, other
            $t->string('basis')->default('fixed_monthly'); // per_auto, per_device, per_runtime, per_campaign, fixed_monthly, depreciation
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('sim_id')->nullable()->constrained()->nullOnDelete();
            $t->date('period_start')->nullable();
            $t->date('period_end')->nullable();
            $t->decimal('amount', 14, 2)->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['type', 'basis']);
        });
    }

    public function down(): void
    {
        foreach (['cost_allocations','revenues','expenses','payments','invoice_items','invoices','driver_disputes','driver_payments','driver_earnings','driver_settlements','rate_cards'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
