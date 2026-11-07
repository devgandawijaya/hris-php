<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alih Daya / Outsourcing Management (PP No. 35 Tahun 2021 BAB IV & Prinsip TUPE)
        Schema::create('outsourcing_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('vendor_code', 50);
            $table->string('name', 150);
            $table->string('legal_entity_type', 50)->default('PT'); // PT berbadan hukum (Pasal 18 PP 35/2021)
            $table->string('nib_oss_license', 100); // Perizinan Berusaha Berbasis Risiko OSS Kemnaker
            $table->string('disnaker_registration_number', 100)->nullable(); // Tanda Daftar Rekanan Disnaker
            $table->string('pic_name', 100);
            $table->string('pic_contact', 100);
            $table->decimal('management_fee_percentage', 5, 2)->default(10.00); // Standar fee 8% - 15%
            $table->string('status', 30)->default('active'); // active, suspended, inactive
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('outsourcing_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('outsourcing_vendor_id')->constrained('outsourcing_vendors')->cascadeOnDelete();
            $table->string('contract_number', 100);
            $table->string('service_type', 50); // cleaning, security, driver, catering, call_center, it_support, warehousing
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('tupe_protection_guarantee')->default(true); // Jaminan TUPE Putusan MK No. 27/PUU-IX/2011 & Pasal 19 PP 35/2021
            $table->unsignedInteger('headcount_quota')->default(10);
            $table->decimal('monthly_billing_amount', 15, 2)->default(0.00);
            $table->string('status', 30)->default('active'); // active, expiring_soon, expired, terminated
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'end_date']);
        });

        Schema::create('vendor_worker_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('outsourcing_contract_id')->constrained('outsourcing_contracts')->cascadeOnDelete();
            $table->string('worker_name', 150);
            $table->string('nik_ktp', 50);
            $table->string('assigned_role', 100);
            $table->boolean('bpjs_tk_registered')->default(true);
            $table->boolean('bpjs_kes_registered')->default(true);
            $table->decimal('monthly_wage_paid', 15, 2);
            $table->boolean('wage_compliant_with_umk')->default(true);
            $table->string('status', 30)->default('active'); // active, replaced, offboarded
            $table->timestamps();

            $table->index(['tenant_id', 'outsourcing_contract_id', 'status']);
        });

        // 2. Perjanjian Kerja Bersama (PKB) & Serikat Pekerja (UU No. 21/2000 & Permenaker No. 28/2014)
        Schema::create('labor_unions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('union_name', 150);
            $table->string('registration_number', 100); // Nomor Bukti Pencatatan Disnaker (Pasal 18 UU 21/2000)
            $table->string('chairperson_name', 100);
            $table->unsignedInteger('total_verified_members')->default(0);
            $table->decimal('workforce_percentage', 5, 2)->default(0.00); // Persentase keterwakilan pekerja
            $table->boolean('has_bargaining_rights')->default(false); // Memiliki >50% anggota untuk berunding PKB (Pasal 119 UU 13/2003)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('pkb_negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('labor_union_id')->constrained('labor_unions')->cascadeOnDelete();
            $table->string('pkb_period_years', 50); // e.g. "2026-2028" (Masa berlaku 2 tahun Pasal 123 UU 13/2003)
            $table->string('draft_code', 50);
            $table->date('negotiation_start_date');
            $table->date('negotiation_target_end_date'); // Maksimal 30 hari kerja (Permenaker 28/2014)
            $table->string('employer_delegation_lead', 100);
            $table->string('union_delegation_lead', 100);
            $table->string('status', 40)->default('in_progress'); // in_progress, agreed, deadlocked, registered_at_disnaker
            $table->string('disnaker_decree_number', 100)->nullable(); // Nomor SK Pendaftaran PKB Disnaker
            $table->text('summary_clauses')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('union_dues_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('labor_union_id')->constrained('labor_unions')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('monthly_dues_amount', 15, 2)->default(25000.00);
            $table->string('consent_letter_number', 100); // Surat Kuasa Tertulis Pemotongan Upah (Check-Off System)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'is_active']);
        });

        // 3. Satgas Pencegahan & Penanganan Kekerasan Seksual (PPKS) & Ramah Perempuan (Kepmenaker 88/2023 & UU 12/2022)
        Schema::create('ppks_complaint_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('ticket_number', 50)->unique(); // PPKS-YYYY-XXXX
            $table->date('incident_date');
            $table->string('allegation_type', 50); // verbal, physical, non_physical, digital_online
            $table->boolean('is_anonymous')->default(true);
            $table->string('complainant_pseudonym', 100)->default('Saksi/Korban Terlindungi');
            $table->string('investigator_satgas_lead', 100);
            $table->text('victim_support_measures')->nullable(); // Konseling, pemindahan sementara, perlindungan bebas retaliasi
            $table->string('sanction_recommendation', 50)->nullable(); // teguran_tertulis, skorsing, pemindahan_tugas, phk_berat
            $table->string('status', 40)->default('received'); // received, investigating, resolved, referred_to_authorities
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('female_worker_facility_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->date('inspection_date');
            $table->boolean('lactation_room_standard_met')->default(true); // Ruang laktasi higienis (Permenkes 15/2013)
            $table->boolean('night_shift_transport_provided')->default(true); // Angkutan antar-jemput shift malam 23:00 - 05:00 (Pasal 76 ayat 3-4 UU 13/2003)
            $table->boolean('night_shift_nutritious_food_provided')->default(true); // Makanan/minuman bergizi bagi shift malam
            $table->boolean('daycare_subsidy_available')->default(false); // Fasilitas penitipan anak / subsidi daycare
            $table->text('compliance_notes')->nullable();
            $table->foreignId('audited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'inspection_date']);
        });

        // 4. Voluntary Separation Scheme (VSS / Pensiun Dini Sukarela) & DPLK (UU No. 4/2023 P2SK)
        Schema::create('voluntary_separation_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('program_code', 50)->unique(); // VSS-YYYY-XXXX
            $table->string('title', 150);
            $table->date('open_date');
            $table->date('close_date');
            $table->decimal('severance_multiplier', 4, 2)->default(1.50); // Faktor pengali paket pensiun dini (misal 1.5x - 2.0x PMTK)
            $table->decimal('additional_golden_handshake_bonus', 15, 2)->default(0.00); // Uang tali asih tambahan
            $table->unsignedInteger('max_quota')->default(20);
            $table->unsignedInteger('approved_applicants_count')->default(0);
            $table->string('status', 30)->default('open'); // open, closed, completed
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('vss_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('voluntary_separation_program_id')->constrained('voluntary_separation_programs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('application_date');
            $table->unsignedSmallInteger('tenure_years')->default(1);
            $table->decimal('last_basic_salary', 15, 2);
            $table->decimal('calculated_severance_amount', 15, 2);
            $table->decimal('total_package_received', 15, 2);
            $table->string('status', 30)->default('submitted'); // submitted, approved, rejected, disbursed
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('settlement_effective_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('dplk_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('dplk_provider_name', 100); // e.g. DPLK Bank Mandiri, DPLK Manulife, DPLK BNI
            $table->string('account_number', 50);
            $table->decimal('employer_contribution_percentage', 5, 2)->default(5.00);
            $table->decimal('employee_contribution_percentage', 5, 2)->default(2.50);
            $table->decimal('current_accumulated_fund', 15, 2)->default(0.00);
            $table->string('status', 30)->default('active'); // active, mature, claimed
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // 5. Program Pemagangan Terstruktur Dalam Negeri (Permenaker No. 6 Tahun 2020)
        Schema::create('internship_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('program_title', 150);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date'); // Maksimal jangka waktu 1 tahun (Pasal 5 Permenaker 6/2020)
            $table->decimal('pocket_money_allowance_daily', 15, 2)->default(75000.00); // Uang saku/transport/makan magang
            $table->boolean('bpjs_bpu_covered')->default(true); // Jaminan JKK & JKM Magang (Pasal 13)
            $table->foreignId('mentor_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('intern_candidate_name', 150);
            $table->string('intern_nik_ktp', 50);
            $table->string('institution_origin', 150); // Asal SMK / Perguruan Tinggi
            $table->string('agreement_letter_number', 100); // Nomor Perjanjian Pemagangan (Pasal 9)
            $table->boolean('statutory_ratio_compliant')->default(true); // Maksimal kuota 20% dari total pekerja (Pasal 10)
            $table->string('status', 30)->default('active'); // active, completed, converted_to_employee, terminated
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('internship_logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('internship_program_id')->constrained('internship_programs')->cascadeOnDelete();
            $table->date('log_date');
            $table->text('activity_summary');
            $table->string('competency_mastered', 150);
            $table->text('mentor_feedback')->nullable();
            $table->decimal('mentor_rating', 3, 2)->nullable(); // Skala 1.00 s/d 5.00
            $table->boolean('is_verified_by_mentor')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'internship_program_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_logbooks');
        Schema::dropIfExists('internship_programs');
        Schema::dropIfExists('dplk_participations');
        Schema::dropIfExists('vss_applications');
        Schema::dropIfExists('voluntary_separation_programs');
        Schema::dropIfExists('female_worker_facility_checks');
        Schema::dropIfExists('ppks_complaint_cases');
        Schema::dropIfExists('union_dues_deductions');
        Schema::dropIfExists('pkb_negotiations');
        Schema::dropIfExists('labor_unions');
        Schema::dropIfExists('vendor_worker_assignments');
        Schema::dropIfExists('outsourcing_contracts');
        Schema::dropIfExists('outsourcing_vendors');
    }
};
