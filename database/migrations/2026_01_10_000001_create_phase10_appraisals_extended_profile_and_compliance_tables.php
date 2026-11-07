<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add reports_to_id to employees table (Supervisory hierarchy)
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('reports_to_id')->nullable()->after('position_id')->constrained('employees')->nullOnDelete();
        });

        // 2. Employee Family Members
        Schema::create('employee_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relationship', 50); // spouse, child, parent, sibling
            $table->string('gender', 10)->default('male');
            $table->date('birth_date')->nullable();
            $table->string('nik', 30)->nullable();
            $table->string('education_level', 50)->nullable();
            $table->boolean('is_bpjs_covered')->default(false);
            $table->boolean('is_tax_dependent')->default(false);
            $table->string('contact_number', 50)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        // 3. Employee Education History
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('degree_level', 50); // SMA/SMK, D3, S1/D4, S2, S3, Kursus/Sertifikasi
            $table->string('institution_name', 150);
            $table->string('major', 150)->nullable();
            $table->unsignedSmallInteger('graduation_year');
            $table->decimal('gpa', 4, 2)->nullable();
            $table->string('certificate_number', 100)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        // 4. Employee Previous Employment Histories
        Schema::create('employee_employment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('company_name', 150);
            $table->string('position_title', 150);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('last_salary', 15, 2)->nullable();
            $table->text('leaving_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        // 5. Employee Emergency Contacts
        Schema::create('employee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('contact_name', 150);
            $table->string('relationship', 50);
            $table->string('phone', 50);
            $table->text('address')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        // 6. 360-Degree Performance Feedback
        Schema::create('performance_feedbacks_360', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('review_cycle_id')->nullable()->constrained('performance_cycles')->nullOnDelete();
            $table->foreignId('subject_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('reviewer_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('rater_relationship', 30)->default('peer'); // self, supervisor, peer, subordinate
            $table->boolean('is_anonymous')->default(true);
            $table->unsignedTinyInteger('core_values_score')->default(4); // 1-5
            $table->unsignedTinyInteger('leadership_score')->default(4);
            $table->unsignedTinyInteger('collaboration_score')->default(4);
            $table->unsignedTinyInteger('execution_score')->default(4);
            $table->decimal('overall_score', 4, 2)->default(4.00);
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->string('status', 30)->default('pending'); // pending, completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'subject_employee_id']);
            $table->index(['tenant_id', 'reviewer_employee_id']);
        });

        // 7. Performance Improvement Plans (PIP)
        Schema::create('performance_improvement_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('supervisor_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('title', 150);
            $table->text('reason');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('target_objectives');
            $table->text('action_plan');
            $table->text('progress_notes')->nullable();
            $table->string('status', 30)->default('in_progress'); // in_progress, passed, failed, extended
            $table->text('final_evaluation')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        // 8. Indonesian Banking Mass Disbursal Batches
        Schema::create('bank_disbursal_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->string('batch_number', 50);
            $table->string('bank_target', 30); // bca, mandiri, bni, bri
            $table->unsignedInteger('total_records')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('file_format', 20)->default('csv'); // txt, csv
            $table->string('status', 30)->default('generated'); // generated, downloaded, executed
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'payroll_period_id']);
            $table->unique(['tenant_id', 'batch_number']);
        });

        // 9. Regional Minimum Wages (UMR / UMK Compliance Master)
        Schema::create('regional_minimum_wages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('province', 100);
            $table->string('city_or_regency', 100);
            $table->unsignedSmallInteger('year');
            $table->decimal('minimum_wage_amount', 15, 2);
            $table->string('legal_reference', 150)->nullable(); // e.g. Kepgub Jabar No. 561/2026
            $table->timestamps();

            $table->index(['province', 'city_or_regency', 'year']);
        });

        // 10. BPJS Statutory Compliance Reporting (SIPP Online & e-Dabu)
        Schema::create('bpjs_compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->string('report_type', 30); // bpjs_tk_sipp, bpjs_kes_edabu
            $table->string('report_month', 20); // YYYY-MM
            $table->unsignedInteger('total_employees')->default(0);
            $table->decimal('total_wages', 15, 2)->default(0.00);
            $table->decimal('total_company_share', 15, 2)->default(0.00);
            $table->decimal('total_employee_share', 15, 2)->default(0.00);
            $table->decimal('total_contribution', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['tenant_id', 'payroll_period_id']);
        });

        // 11. Branch QR Attendance Kiosks
        Schema::create('branch_qr_kiosks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('kiosk_name', 100);
            $table->string('secret_token', 64);
            $table->string('current_qr_code', 100);
            $table->timestamp('qr_refreshed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_qr_kiosks');
        Schema::dropIfExists('bpjs_compliance_reports');
        Schema::dropIfExists('regional_minimum_wages');
        Schema::dropIfExists('bank_disbursal_batches');
        Schema::dropIfExists('performance_improvement_plans');
        Schema::dropIfExists('performance_feedbacks_360');
        Schema::dropIfExists('employee_emergency_contacts');
        Schema::dropIfExists('employee_employment_histories');
        Schema::dropIfExists('employee_educations');
        Schema::dropIfExists('employee_families');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['reports_to_id']);
            $table->dropColumn('reports_to_id');
        });
    }
};
