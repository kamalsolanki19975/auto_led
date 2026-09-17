<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('logo')->nullable();
            $t->string('address')->nullable();
            $t->string('gstin')->nullable();
            $t->string('pan')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('website')->nullable();
            $t->timestamps();
        });

        Schema::create('cities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('code')->nullable();
            $t->string('state')->nullable();
            $t->string('country')->default('India');
            $t->timestamps();
        });

        Schema::create('areas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('city_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('pincode')->nullable();
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('description')->nullable();
            $t->boolean('is_system')->default(false);
            $t->string('portal')->default('admin'); // admin, advertiser, driver, owner, technician
            $t->timestamps();
        });

        Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('module');
            $t->string('action');
            $t->timestamps();
        });

        Schema::create('role_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unique(['role_id', 'user_id']);
        });

        Schema::create('permission_role', function (Blueprint $t) {
            $t->id();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->unique(['permission_id', 'role_id']);
        });

        Schema::create('system_settings', function (Blueprint $t) {
            $t->id();
            $t->string('group')->index();
            $t->string('key');
            $t->longText('value')->nullable();
            $t->string('type')->default('string');
            $t->timestamps();
            $t->unique(['group', 'key']);
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->string('entity_type')->nullable();
            $t->unsignedBigInteger('entity_id')->nullable();
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->string('ip')->nullable();
            $t->string('user_agent', 512)->nullable();
            $t->timestamps();
            $t->index(['entity_type', 'entity_id']);
        });

        Schema::create('login_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('email')->nullable();
            $t->string('ip')->nullable();
            $t->string('user_agent', 512)->nullable();
            $t->boolean('successful')->default(true);
            $t->timestamps();
            $t->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['login_histories','audit_logs','system_settings','permission_role','role_user','permissions','roles','areas','cities','companies'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
