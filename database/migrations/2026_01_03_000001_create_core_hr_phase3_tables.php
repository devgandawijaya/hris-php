<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employee Salary Components (Mapping specific allowances/deductions per employee)
        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->unique(['tenant_id', 'employee_id', 'salary_component_id'], 'emp_sal_comp_unique');
        });

        // 2. Extend payrolls table with itemized breakdown and reimbursement payout
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('reimbursement_pay', 15, 2)->default(0.00)->after('overtime_pay');
            $table->json('breakdown')->nullable()->after('take_home_pay');
            $table->string('payment_method', 50)->default('bank_transfer')->after('breakdown');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->text('notes')->nullable()->after('paid_at');
        });

        // 3. Reimbursements & Expense Claims
        Schema::create('reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('category', 50); // medical, transport, meal, business_trip, operational, other
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('receipt_path')->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved_manager, approved_finance, paid, rejected
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->foreignId('reviewed_by_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_finance_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'status']);
        });

        // 4. Employment Contracts & PKWT Tracking
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('contract_number', 100);
            $table->string('type', 30); // pkwt, pkwtt, internship, probation
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->decimal('monthly_wage', 15, 2);
            $table->string('status', 30)->default('active'); // active, expiring_soon, expired, renewed, terminated
            $table->decimal('compensation_calculated', 15, 2)->default(0.00); // PP 35/2021 Article 61A
            $table->boolean('compensation_paid')->default(false);
            $table->unsignedInteger('renewal_count')->default(0);
            $table->string('document_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'end_date']);
        });

        // 5. Tax Reconciliations (Annual Masa Pajak Terakhir PPh 21 / Formulir 1721-A1)
        Schema::create('tax_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedInteger('tax_year');
            $table->decimal('gross_income_annual', 15, 2);
            $table->decimal('biaya_jabatan', 15, 2);
            $table->decimal('bpjs_deductions_annual', 15, 2);
            $table->decimal('net_income_annual', 15, 2);
            $table->decimal('ptkp_annual', 15, 2);
            $table->decimal('pkp', 15, 2);
            $table->decimal('pph21_annual_due', 15, 2);
            $table->decimal('pph21_ter_paid_total', 15, 2);
            $table->decimal('pph21_december_tax', 15, 2);
            $table->string('status', 30)->default('draft'); // draft, finalized, submitted
            $table->json('reconciliation_data')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id', 'tax_year']);
            $table->index(['tenant_id', 'tax_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_reconciliations');
        Schema::dropIfExists('employment_contracts');
        Schema::dropIfExists('reimbursements');

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['reimbursement_pay', 'breakdown', 'payment_method', 'paid_at', 'notes']);
        });

        Schema::dropIfExists('employee_salary_components');
    }
};

