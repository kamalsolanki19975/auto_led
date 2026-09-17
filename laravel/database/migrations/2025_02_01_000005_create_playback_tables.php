<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playback_events', function (Blueprint $t) {
            $t->id();
            $t->uuid('event_id')->unique(); // idempotency key from device
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('screen_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('advertisement_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('start_time')->nullable();
            $t->timestamp('end_time')->nullable();
            $t->integer('duration')->default(0);
            $t->integer('expected_duration')->default(0);
            $t->integer('actual_duration')->default(0);
            $t->decimal('completion_percent', 5, 2)->default(0);
            $t->timestamp('device_timestamp')->nullable();
            $t->timestamp('server_timestamp')->nullable();
            $t->string('network_status')->nullable();
            $t->string('player_version')->nullable();
            $t->timestamp('sync_timestamp')->nullable();
            $t->string('status')->default('received'); // received, validating, valid, invalid, duplicate, rejected
            $t->boolean('processed')->default(false);
            $t->timestamps();
            $t->index(['campaign_id', 'server_timestamp']);
            $t->index(['device_id', 'server_timestamp']);
            $t->index(['auto_id', 'server_timestamp']);
            $t->index(['status', 'processed']);
        });

        Schema::create('proof_of_play', function (Blueprint $t) {
            $t->id();
            $t->foreignId('playback_event_id')->nullable()->constrained('playback_events')->nullOnDelete();
            $t->uuid('event_id')->index();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('advertisement_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $t->integer('valid_duration')->default(0);
            $t->decimal('completion_percent', 5, 2)->default(0);
            $t->string('status')->default('received'); // received, validating, valid, invalid, duplicate, rejected
            $t->text('validation_notes')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->timestamp('server_timestamp')->nullable();
            $t->timestamps();
            $t->index(['campaign_id', 'status']);
            $t->index(['auto_id', 'status']);
            $t->index(['driver_id', 'status']);
        });

        Schema::create('heartbeats', function (Blueprint $t) {
            $t->id();
            $t->foreignId('device_id')->constrained()->cascadeOnDelete();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->string('ip')->nullable();
            $t->string('app_version')->nullable();
            $t->string('android_version')->nullable();
            $t->integer('storage_free')->nullable();
            $t->decimal('temperature', 5, 2)->nullable();
            $t->string('network')->nullable();
            $t->integer('signal_strength')->nullable();
            $t->foreignId('current_campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $t->foreignId('current_advertisement_id')->nullable()->constrained('advertisements')->nullOnDelete();
            $t->integer('uptime')->nullable();
            $t->text('errors')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->index(['device_id', 'created_at']);
        });

        Schema::create('runtime_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('auto_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->date('date');
            $t->integer('screen_runtime_seconds')->default(0);
            $t->integer('connected_runtime_seconds')->default(0);
            $t->integer('player_runtime_seconds')->default(0);
            $t->integer('advertisement_runtime_seconds')->default(0);
            $t->integer('valid_advertising_runtime_seconds')->default(0);
            $t->integer('total_plays')->default(0);
            $t->integer('valid_plays')->default(0);
            $t->timestamps();
            $t->index(['auto_id', 'date']);
            $t->index(['driver_id', 'date']);
            $t->index(['campaign_id', 'date']);
        });
    }

    public function down(): void
    {
        foreach (['runtime_logs','heartbeats','proof_of_play','playback_events'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
