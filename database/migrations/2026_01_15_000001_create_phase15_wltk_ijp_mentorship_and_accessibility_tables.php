<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter employees table for Citizenship and Disability tracking
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'citizenship')) {
                $table->string('citizenship', 10)->default('WNI')->after('gender'); // WNI, WNA
            }
            if (!Schema::hasColumn('employees', 'has_disability')) {
                $table->boolean('has_disability')->default(false)->after('employment_status');
            }
        });

        // 2. Wajib Lapor Ketenagakerjaan Perusahaan (WLTK / WLKP UU No. 7/1981 & Permenaker No. 18/2017)
        Schema::create('wltk_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('report_number', 50)->unique(); // WLTK/YYYY/XXXX
            $table->unsignedSmallInteger('reporting_year');
            $table->string('report_period', 30)->default('annual'); // annual, establishment, relocation, closure
            $table->string('status', 30)->default('draft'); // draft, submitted_kemnaker, verified_disnaker
            $table->date('submission_date')->nullable();
            $table->date('valid_until')->nullable(); // Exactly 1 year from submission_date
            $table->string('company_registration_number', 60); // NIB / WLKP Number
            $table->string('kbli_code', 20)->default('62012');
            $table->unsignedInteger('total_workers')->default(0);
            $table->unsignedInteger('total_wni_male')->default(0);
            $table->unsignedInteger('total_wni_female')->default(0);
            $table->unsignedInteger('total_wna_male')->default(0);
            $table->unsignedInteger('total_wna_female')->default(0);
            $table->unsignedInteger('total_pkwtt')->default(0);
            $table->unsignedInteger('total_pkwt')->default(0);
            $table->unsignedInteger('total_disability_workers')->default(0);
            $table->decimal('lowest_wage', 15, 2)->default(0.00);
            $table->decimal('highest_wage', 15, 2)->default(0.00);
            $table->boolean('bpjs_tk_registered')->default(true);
            $table->boolean('bpjs_kes_registered')->default(true);
            $table->boolean('has_pp_or_pkb')->default(true);
            $table->boolean('has_lks_bipartit')->default(true);
            $table->json('raw_payload_json')->nullable();
            $table->string('disnaker_receipt_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'reporting_year', 'status']);
        });

        // 3. Internal Job Posting (IJP) Vacancies
        Schema::create('ijp_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->string('vacancy_code', 50)->unique(); // IJP-YYYY-XXXX
            $table->string('title', 150);
            $table->text('description');
            $table->text('requirements');
            $table->unsignedSmallInteger('min_tenure_months')->default(12);
            $table->decimal('min_performance_score', 3, 2)->default(3.00);
            $table->unsignedSmallInteger('open_slots')->default(1);
            $table->date('application_deadline');
            $table->string('status', 30)->default('open'); // open, closed, cancelled
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'application_deadline']);
        });

        // 4. IJP Applications (Internal Talent Mobility)
        Schema::create('ijp_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('ijp_vacancy_id')->constrained('ijp_vacancies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('current_department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('current_position_id')->constrained('positions')->cascadeOnDelete();
            $table->date('application_date');
            $table->text('motivation_letter');
            $table->string('portfolio_url', 255)->nullable();
            $table->unsignedSmallInteger('current_tenure_months')->default(0);
            $table->decimal('latest_performance_score', 3, 2)->nullable();
            $table->boolean('has_active_warning')->default(false);
            $table->boolean('is_eligible')->default(true);
            $table->string('ineligibility_reason', 255)->nullable();
            $table->string('manager_approval_status', 30)->default('pending'); // pending, approved, rejected
            $table->text('manager_approval_notes')->nullable();
            $table->dateTime('manager_approved_at')->nullable();
            $table->string('hr_interview_status', 30)->default('pending'); // pending, passed, failed
            $table->text('hr_interview_notes')->nullable();
            $table->string('final_decision', 30)->default('in_review'); // in_review, offered, accepted, rejected, withdrawn
            $table->date('transfer_effective_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'final_decision', 'manager_approval_status']);
        });

        // 5. Onboarding Buddy & Mentorship Program
        Schema::create('mentorship_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mentee_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('mentor_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('active'); // active, completed, terminated
            $table->text('program_objectives')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 6. Mentorship Milestones Check-ins (Day-30, Day-60, Day-90)
        Schema::create('mentorship_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('mentorship_pair_id')->constrained('mentorship_pairs')->cascadeOnDelete();
            $table->string('milestone', 20); // day_30, day_60, day_90
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->string('status', 30)->default('pending'); // pending, completed, missed
            $table->text('agenda_topics')->nullable();
            $table->unsignedTinyInteger('mentee_satisfaction_score')->nullable(); // 1 to 5
            $table->text('mentee_feedback')->nullable();
            $table->unsignedTinyInteger('mentor_adaptation_score')->nullable(); // 1 to 5
            $table->text('mentor_notes')->nullable();
            $table->text('action_plan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'milestone']);
        });

        // 7. Asset Handover BAST (Berita Acara Serah Terima Pengembalian Aset)
        Schema::create('asset_handover_basts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('bast_number', 50)->unique(); // BAST-ASSET-YYYY-XXXX
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('offboarding_clearance_id')->nullable()->constrained('offboarding_clearances')->nullOnDelete();
            $table->date('handover_date');
            $table->string('status', 30)->default('draft'); // draft, signed, completed
            $table->string('officer_name', 100);
            $table->string('officer_position', 100);
            $table->string('employee_signature_token', 100)->nullable();
            $table->string('officer_signature_token', 100)->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->decimal('total_compensation_due', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'handover_date']);
        });

        // 8. Asset Handover Items
        Schema::create('asset_handover_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_handover_bast_id')->constrained('asset_handover_basts')->cascadeOnDelete();
            $table->foreignId('company_asset_id')->nullable()->constrained('company_assets')->nullOnDelete();
            $table->string('asset_name', 150);
            $table->string('serial_number', 100)->nullable();
            $table->string('condition_on_return', 30)->default('good'); // good, minor_damage, severe_damage, lost
            $table->decimal('compensation_amount', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 9. Automated System Access Revocation Logs (SSO & IT Offboarding)
        Schema::create('system_access_revocation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('asset_handover_bast_id')->nullable()->constrained('asset_handover_basts')->nullOnDelete();
            $table->string('system_name', 100); // Google Workspace, Slack, VPN, M365, HRIS
            $table->string('account_identifier', 150); // email or username
            $table->string('revocation_status', 30)->default('pending'); // pending, revoked, failed
            $table->dateTime('revoked_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('api_response_log')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'revocation_status']);
        });

        // 10. Audit Kepatuhan Kuota Tenaga Kerja Disabilitas (UU No. 8/2016 Pasal 53)
        Schema::create('disability_employment_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('audit_code', 50)->unique(); // DISABILITY-AUDIT-YYYY-XXXX
            $table->unsignedSmallInteger('audit_year');
            $table->date('audit_date');
            $table->string('entity_type', 30)->default('private_sector'); // private_sector (1%), state_owned_bumn (2%)
            $table->unsignedInteger('total_active_workforce')->default(0);
            $table->unsignedInteger('disability_workforce_count')->default(0);
            $table->decimal('statutory_quota_percentage', 4, 2)->default(1.00);
            $table->decimal('actual_quota_percentage', 4, 2)->default(0.00);
            $table->unsignedInteger('statutory_headcount_required')->default(0);
            $table->unsignedInteger('headcount_shortfall')->default(0);
            $table->string('compliance_status', 30)->default('non_compliant'); // compliant, near_target, non_compliant
            $table->unsignedTinyInteger('physical_accessibility_score')->default(0); // 0-100%
            $table->unsignedTinyInteger('digital_accessibility_score')->default(0); // 0-100%
            $table->text('reasonable_accommodations_summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('audited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'audit_year', 'compliance_status']);
        });

        // 11. Profil Karyawan Disabilitas & Reasonable Accommodation
        Schema::create('employee_disability_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('disability_category', 50); // physical, sensory_vision, sensory_hearing, intellectual, mental_psychosocial, multiple
            $table->string('medical_certificate_number', 100)->nullable();
            $table->text('accommodations_provided')->nullable(); // ramp, screen reader, adjustable desk, flexible breaks
            $table->text('accessibility_needs')->nullable();
            $table->text('emergency_evacuation_assistance_plan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'disability_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_disability_profiles');
        Schema::dropIfExists('disability_employment_audits');
        Schema::dropIfExists('system_access_revocation_logs');
        Schema::dropIfExists('asset_handover_items');
        Schema::dropIfExists('asset_handover_basts');
        Schema::dropIfExists('mentorship_checkins');
        Schema::dropIfExists('mentorship_pairs');
        Schema::dropIfExists('ijp_applications');
        Schema::dropIfExists('ijp_vacancies');
        Schema::dropIfExists('wltk_reports');

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'citizenship')) {
                $table->dropColumn('citizenship');
            }
            if (Schema::hasColumn('employees', 'has_disability')) {
                $table->dropColumn('has_disability');
            }
        });
    }
};
