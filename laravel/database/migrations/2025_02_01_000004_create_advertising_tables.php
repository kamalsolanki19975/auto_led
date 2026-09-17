<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisers', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('company_name');
            $t->string('contact_person')->nullable();
            $t->string('mobile')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->string('gstin')->nullable();
            $t->string('pan')->nullable();
            $t->string('billing_address')->nullable();
            $t->string('payment_terms')->nullable();
            $t->decimal('credit_limit', 14, 2)->default(0);
            $t->string('status')->default('active');
            $t->text('notes')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('advertisements', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('advertiser_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('content_type')->default('image'); // image, video, text, html
            $t->string('media_path')->nullable();
            $t->string('media_url')->nullable();
            $t->string('thumbnail_path')->nullable();
            $t->text('text_content')->nullable();
            $t->longText('html_content')->nullable();
            $t->integer('duration')->default(15); // seconds
            $t->string('resolution')->nullable();
            $t->string('orientation')->default('landscape');
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->string('approval_status')->default('draft'); // draft, submitted, under_review, approved, rejected, scheduled, active, completed, archived
            $t->text('rejection_reason')->nullable();
            $t->integer('version')->default(1);
            $t->string('status')->default('active');
            $t->json('tags')->nullable();
            $t->string('category')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->softDeletes();
            $t->timestamps();
            $t->index(['approval_status', 'advertiser_id']);
        });

        Schema::create('advertisement_versions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('advertisement_id')->constrained()->cascadeOnDelete();
            $t->integer('version');
            $t->string('media_path')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('advertiser_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('campaign_type')->default('paid'); // paid, house, emergency
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->decimal('budget', 14, 2)->default(0);
            $t->string('pricing_model')->default('per_play'); // cpm, per_play, per_day, per_hour, fixed
            $t->decimal('rate', 12, 2)->default(0);
            $t->string('target_type')->default('autos'); // autos, groups, areas, city, all, screen_type
            $t->json('target_areas')->nullable();
            $t->json('target_groups')->nullable();
            $t->integer('priority')->default(2); // 1 emergency, 2 paid, 3 house, 4 default
            $t->string('frequency')->nullable();
            $t->json('schedule')->nullable(); // days_of_week, time_slots
            $t->bigInteger('expected_plays')->default(0);
            $t->string('status')->default('draft'); // draft, pending_approval, approved, scheduled, active, paused, under_delivery, completed, expired, cancelled
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('activated_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->softDeletes();
            $t->timestamps();
            $t->index(['status', 'advertiser_id']);
        });

        Schema::create('campaign_advertisement', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $t->foreignId('advertisement_id')->constrained()->cascadeOnDelete();
            $t->integer('order')->default(0);
            $t->unique(['campaign_id', 'advertisement_id']);
        });

        Schema::create('campaign_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $t->foreignId('auto_id')->constrained()->cascadeOnDelete();
            $t->string('status')->default('assigned'); // assigned, active, removed
            $t->timestamp('assigned_at')->nullable();
            $t->timestamps();
            $t->index(['campaign_id', 'auto_id']);
        });

        Schema::create('playlists', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->text('description')->nullable();
            $t->integer('priority')->default(2);
            $t->json('schedule')->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
        });

        Schema::create('playlist_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $t->foreignId('advertisement_id')->constrained()->cascadeOnDelete();
            $t->integer('order')->default(0);
            $t->integer('duration')->default(15);
            $t->integer('frequency')->default(1);
            $t->integer('priority')->default(2);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['playlist_items','playlists','campaign_assignments','campaign_advertisement','campaigns','advertisement_versions','advertisements','advertisers'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
