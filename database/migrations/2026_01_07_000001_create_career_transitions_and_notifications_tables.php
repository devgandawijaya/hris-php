<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employee Terminations & Statutory Severance (PP 35/2021 & Pasal 156 UU Cipta Kerja)
        Schema::create('employee_terminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('letter_number', 100)->nullable(); // e.g. SK-PHK/2026/001
            $table->string('termination_type', 40); // resignation, efficiency, closing, sp3_violation, pension, medical, deceased, other
            $table->date('termination_date');
            $table->integer('years_of_service')->default(0);
            $table->integer('months_of_service')->default(0);
            $table->decimal('last_salary', 15, 2);
            $table->decimal('severance_multiplier', 4, 2)->default(1.00); // 0.5, 1.0, 1.75, 2.0
            $table->decimal('severance_amount', 15, 2)->default(0); // Uang Pesangon (UP)
            $table->decimal('upmk_amount', 15, 2)->default(0); // Uang Penghargaan Masa Kerja (UPMK)
            $table->decimal('uph_amount', 15, 2)->default(0); // Uang Penggantian Hak (UPH)
            $table->decimal('separation_pay_amount', 15, 2)->default(0); // Uang Pisah (untuk resign / kesepakatan)
            $table->decimal('total_severance', 15, 2)->default(0);
            $table->string('status', 20)->default('draft'); // draft, approved, paid
            $table->text('notes')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'termination_date']);
            $table->index(['tenant_id', 'status']);
        });

        // 2. In-App Notification Center
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete(); // null = broadcast to tenant
            $table->string('title', 255);
            $table->text('message');
            $table->string('type', 20)->default('info'); // info, warning, success, urgent
            $table->string('action_url', 500)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'is_read']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('employee_terminations');
    }
};
