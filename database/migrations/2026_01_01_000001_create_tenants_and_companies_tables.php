<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status', 30)->default('trial'); // trial, active, suspended, expired
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('npwp', 30)->nullable();
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedSmallInteger('work_days_per_week')->default(5);
            $table->decimal('regular_work_hours_per_day', 4, 2)->default(8.00);
            $table->decimal('weekly_work_hour_limit', 4, 2)->default(40.00);
            $table->unsignedInteger('late_tolerance_minutes')->default(15);
            $table->decimal('bpjs_kes_max_cap', 15, 2)->default(12000000.00);
            $table->decimal('bpjs_tk_jp_max_cap', 15, 2)->default(10042300.00);
            $table->string('jkk_risk_grade', 20)->default('low'); // very_low, low, medium, high, very_high
            $table->unsignedSmallInteger('payroll_cutoff_day')->default(20);
            $table->unsignedSmallInteger('payroll_payment_day')->default(25);
            $table->timestamps();

            $table->unique(['tenant_id', 'company_id']);
        });

        Schema::create('tenant_user_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->boolean('is_owner')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
            $table->index('tenant_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_user_access');
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('tenants');
    }
};

