<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type', 30); // allowance, deduction
            $table->boolean('is_fixed')->default(true);
            $table->boolean('is_taxable')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 100); // e.g. "Gaji Bulan Januari 2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payment_date');
            $table->string('status', 30)->default('draft'); // draft, processed, approved, paid
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('allowances_total', 15, 2)->default(0.00);
            $table->decimal('overtime_pay', 15, 2)->default(0.00);
            $table->decimal('gross_salary', 15, 2);
            $table->decimal('deductions_total', 15, 2)->default(0.00);
            $table->decimal('bpjs_tk_employee', 15, 2)->default(0.00);
            $table->decimal('bpjs_tk_employer', 15, 2)->default(0.00);
            $table->decimal('bpjs_kes_employee', 15, 2)->default(0.00);
            $table->decimal('bpjs_kes_employer', 15, 2)->default(0.00);
            $table->decimal('pph21_tax', 15, 2)->default(0.00);
            $table->decimal('take_home_pay', 15, 2);
            $table->string('status', 30)->default('draft'); // draft, reviewed, approved, paid
            $table->timestamps();

            $table->unique(['tenant_id', 'payroll_period_id', 'employee_id']);
            $table->index(['tenant_id', 'payroll_period_id']);
        });

        Schema::create('thr_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedInteger('year');
            $table->unsignedInteger('months_of_service');
            $table->decimal('base_wage', 15, 2);
            $table->decimal('thr_amount', 15, 2);
            $table->date('payment_date');
            $table->string('status', 30)->default('draft');
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thr_records');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('salary_components');
    }
};

