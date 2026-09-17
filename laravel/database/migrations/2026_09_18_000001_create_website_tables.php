<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable()->index();
                $table->string('name');
                $table->string('company')->nullable();
                $table->string('email');
                $table->string('phone')->nullable();
                $table->text('message')->nullable();
                $table->string('type')->default('general');   // advertiser | auto_owner | general
                $table->string('source')->default('website');
                $table->string('page')->nullable();
                $table->string('status')->default('new');      // new|contacted|qualified|proposal|converted|lost
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamp('followup_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['status', 'type']);
            });
        }

        if (! Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->string('category')->default('General');
                $table->string('question');
                $table->text('answer');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
                $table->index(['category', 'status']);
            });
        }

        if (! Schema::hasTable('pricing_plans')) {
            Schema::create('pricing_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('tagline')->nullable();
                $table->string('price_label')->default('Custom');
                $table->string('price_note')->nullable();
                $table->json('features')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->string('cta_label')->default('Get Started');
                $table->string('cta_url')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('leads');
    }
};
