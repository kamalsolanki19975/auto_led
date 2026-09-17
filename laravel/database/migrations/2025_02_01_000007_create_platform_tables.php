<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_applications', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('description')->nullable();
            $t->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status')->default('active');
            $t->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('api_application_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('key_hash');
            $t->string('prefix', 16)->index();
            $t->json('scopes')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
        });

        Schema::create('webhooks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('api_application_id')->nullable()->constrained()->nullOnDelete();
            $t->string('url');
            $t->json('events')->nullable();
            $t->string('secret')->nullable();
            $t->boolean('active')->default(true);
            $t->string('status')->default('active');
            $t->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $t->string('event');
            $t->json('payload')->nullable();
            $t->string('status')->default('pending'); // pending, delivered, failed, retrying
            $t->integer('response_code')->nullable();
            $t->integer('attempts')->default(0);
            $t->timestamp('next_retry_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->text('error')->nullable();
            $t->timestamps();
            $t->index(['status', 'next_retry_at']);
        });

        Schema::create('api_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('api_key_id')->nullable()->constrained()->nullOnDelete();
            $t->string('method', 10);
            $t->string('path', 512);
            $t->integer('status_code')->nullable();
            $t->string('ip')->nullable();
            $t->string('request_id')->nullable()->index();
            $t->integer('duration_ms')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->index('created_at');
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('type')->default('information'); // critical, warning, information, success
            $t->string('category')->nullable(); // device, sim, campaign, finance, operations
            $t->string('event')->nullable();
            $t->string('title');
            $t->text('message')->nullable();
            $t->string('related_type')->nullable();
            $t->unsignedBigInteger('related_id')->nullable();
            $t->string('link')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'read_at']);
        });

        Schema::create('notification_preferences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('event');
            $t->boolean('email')->default(false);
            $t->boolean('in_app')->default(true);
            $t->boolean('sms')->default(false);
            $t->boolean('whatsapp')->default(false);
            $t->timestamps();
            $t->unique(['user_id', 'event']);
        });

        Schema::create('notification_templates', function (Blueprint $t) {
            $t->id();
            $t->string('event')->unique();
            $t->string('name');
            $t->string('category')->nullable();
            $t->string('default_type')->default('information');
            $t->json('channels')->nullable();
            $t->boolean('status')->default(true);
            $t->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('event');
            $t->string('channel'); // in_app, email, sms, whatsapp
            $t->string('status')->default('sent'); // sent, failed
            $t->text('error')->nullable();
            $t->timestamps();
            $t->index(['event', 'channel']);
        });

        Schema::create('notification_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('notification_id')->nullable()->constrained()->nullOnDelete();
            $t->string('recipient')->nullable();
            $t->string('event');
            $t->string('channel');
            $t->string('status')->default('queued'); // queued, sent, delivered, failed, retrying
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('failed_at')->nullable();
            $t->text('error')->nullable();
            $t->integer('retry_count')->default(0);
            $t->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $t) {
            $t->id();
            $t->string('event')->unique();
            $t->string('name');
            $t->string('subject');
            $t->longText('body');
            $t->json('variables')->nullable();
            $t->boolean('status')->default(true);
            $t->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $t) {
            $t->id();
            $t->string('recipient');
            $t->string('subject');
            $t->string('template_event')->nullable();
            $t->string('event')->nullable();
            $t->string('status')->default('queued'); // queued, sent, delivered, failed, retrying
            $t->text('error')->nullable();
            $t->integer('retry_count')->default(0);
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('failed_at')->nullable();
            $t->timestamps();
            $t->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['email_logs','email_templates','notification_deliveries','notification_logs','notification_templates','notification_preferences','notifications','api_logs','webhook_deliveries','webhooks','api_keys','api_applications'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
