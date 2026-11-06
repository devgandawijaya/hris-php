<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employee Loans & Kasbon / Salary Advance
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('loan_number', 50); // LOAN-YYYY-XXXX
            $table->string('loan_type', 30); // emergency, salary_advance, education, housing, other
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0); // 0% for emergency cash advance
            $table->integer('term_months')->default(1);
            $table->decimal('monthly_installment', 15, 2);
            $table->decimal('total_payable', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->string('status', 20)->default('pending'); // pending, approved, active, completed, rejected
            $table->text('reason');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'status']);
        });

        // 2. Loan Installments (Cicilan Angsuran)
        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('pending'); // pending, paid, deducted_from_payroll
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('payroll_period_id')->nullable()->constrained('payroll_periods')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_loan_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'due_date']);
        });

        // 3. Employee Surveys & eNPS (Survei Kepuasan Kerja)
        Schema::create('employee_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('survey_type', 30)->default('enps'); // enps, satisfaction, culture, pulse
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_anonymous')->default(true);
            $table->string('status', 20)->default('active'); // draft, active, closed
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 4. Employee Survey Responses
        Schema::create('employee_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_survey_id')->constrained('employee_surveys')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->integer('enps_score')->nullable(); // 0 - 10
            $table->integer('satisfaction_rating')->nullable(); // 1 - 5
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['tenant_id', 'employee_survey_id']);
        });

        // 5. Employee Recognition & Kudos (Apresiasi Karyawan)
        Schema::create('employee_recognitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('badge_type', 40); // star_performer, team_player, innovation, customer_hero, core_values
            $table->string('title', 255);
            $table->text('message');
            $table->decimal('reward_amount', 15, 2)->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'created_at']);
        });

        // 6. Company Events & Internal Calendar
        Schema::create('company_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('event_type', 30)->default('townhall'); // townhall, gathering, training, meeting, celebration
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('location', 255)->nullable();
            $table->foreignId('target_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_events');
        Schema::dropIfExists('employee_recognitions');
        Schema::dropIfExists('employee_survey_responses');
        Schema::dropIfExists('employee_surveys');
        Schema::dropIfExists('loan_installments');
        Schema::dropIfExists('employee_loans');
    }
};

