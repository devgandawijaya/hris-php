<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. K3 Workplace Incidents (UU No. 1/1970 & PP No. 50/2012)
        Schema::create('workplace_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('incident_number', 50)->unique(); // INC/K3/YYYY/XXXX
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->string('location', 150);
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('severity', 30)->default('minor_first_aid'); // near_miss, minor_first_aid, lost_time_injury_lti, fatality
            $table->string('injury_type', 150)->nullable(); // e.g. Fraktur, Luka Bakar, Terkilir
            $table->unsignedInteger('lost_workdays')->default(0); // Hari kerja hilang (LTI)
            $table->text('description');
            $table->text('root_cause_analysis')->nullable(); // Analisis 5-Why / Fishbone
            $table->text('corrective_actions')->nullable(); // Tindakan korektif & preventif
            $table->string('status', 30)->default('reported'); // reported, investigating, action_taken, closed
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('investigated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'incident_date', 'severity']);
        });

        // 2. BPJS Ketenagakerjaan JKK Claims (Tahap I & Tahap II)
        Schema::create('bpjs_jkk_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('workplace_incident_id')->constrained('workplace_incidents')->cascadeOnDelete();
            $table->string('claim_number', 50)->unique(); // JKK-BPJS-YYYY-XXXX
            $table->string('stage', 20)->default('tahap_1'); // tahap_1 (Lapor 2x24 Jam), tahap_2 (Klaim Akhir/Sembuh)
            $table->string('medical_facility_name', 150); // PLKK Mitra BPJS
            $table->decimal('estimated_medical_expense', 15, 2)->default(0.00);
            $table->decimal('approved_bpjs_coverage', 15, 2)->default(0.00);
            $table->decimal('temporary_disability_allowance', 15, 2)->default(0.00); // Santunan STMB (100% upah)
            $table->string('claim_status', 30)->default('submitted_to_bpjs'); // draft, submitted_to_bpjs, verified, reimbursed, rejected
            $table->date('submitted_at')->nullable();
            $table->date('settled_at')->nullable();
            $table->text('bpjs_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'stage', 'claim_status']);
        });

        // 3. Employee Medical Check-Up (MCU) K3 Health Surveillance
        Schema::create('employee_mcu_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('mcu_date');
            $table->string('clinic_provider', 150);
            $table->string('blood_pressure', 30)->nullable();
            $table->decimal('cholesterol', 6, 2)->nullable();
            $table->decimal('blood_sugar', 6, 2)->nullable();
            $table->string('fitness_category', 30)->default('fit_to_work'); // fit_to_work, fit_with_restriction, temporarily_unfit, unfit
            $table->text('doctor_notes')->nullable();
            $table->text('follow_up_recommendation')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'mcu_date', 'fitness_category']);
        });

        // 4. Pajak Natura & Kenikmatan (PMK No. 66 Tahun 2023)
        Schema::create('fringe_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('tax_year');
            $table->unsignedTinyInteger('period_month');
            $table->string('benefit_type', 50); // company_car_facility, housing_apartment_rental, club_membership, groceries_vouchers, etc.
            $table->string('description', 200);
            $table->decimal('gross_market_value', 15, 2)->default(0.00);
            $table->decimal('non_taxable_threshold', 15, 2)->default(0.00); // Batas pengecualian PMK 66
            $table->decimal('taxable_natura_value', 15, 2)->default(0.00); // Nilai kena PPh 21
            $table->boolean('is_taxable_object')->default(true);
            $table->text('fiscal_notes')->nullable(); // Rujukan pasal PMK 66/2023
            $table->timestamps();

            $table->index(['tenant_id', 'tax_year', 'period_month']);
        });

        // 5. Surat Perintah Lembur (SPL) Resmi & Batas Maksimal PP No. 35/2021
        Schema::create('overtime_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('order_number', 50)->unique(); // SPL/YYYY/MM/XXXX
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('overtime_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('planned_duration_hours', 5, 2);
            $table->decimal('actual_duration_hours', 5, 2)->nullable();
            $table->text('reason_task_description');
            $table->boolean('is_day_off')->default(false); // Hari libur resmi/istirahat
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->timestamp('employee_consented_at')->nullable(); // Persetujuan tertulis pekerja Pasal 26
            $table->string('status', 30)->default('pending_approval'); // pending_approval, approved, rejected, completed_verified
            $table->timestamps();

            $table->index(['tenant_id', 'overtime_date', 'status']);
        });

        // 6. Peraturan Perusahaan (PP) & PKB Register (UU No. 13/2003 & PP 35/2021)
        Schema::create('company_regulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('document_type', 30)->default('peraturan_perusahaan_pp'); // peraturan_perusahaan_pp, perjanjian_kerja_bersama_pkb
            $table->string('title', 150);
            $table->string('reg_number', 100)->nullable(); // Nomor SK Disnaker
            $table->date('effective_start_date');
            $table->date('effective_end_date'); // Maksimal 2 tahun
            $table->string('disnaker_office', 150)->nullable(); // Dinas Tenaga Kerja yang mengesahkan
            $table->string('status', 30)->default('active'); // active, renewal_pending, expired
            $table->text('summary_provisions')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'effective_end_date', 'status']);
        });

        // 7. Forum Lembaga Kerja Sama (LKS) Bipartit (Permenaker No. 32/2008 & UU 13/2003 Pasal 106)
        Schema::create('bipartite_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('meeting_number', 50)->unique(); // BIPARTIT/YYYY/MM/XXXX
            $table->date('meeting_date');
            $table->string('meeting_title', 200);
            $table->text('agenda_topics');
            $table->json('employer_representatives'); // Daftar perwakilan pengusaha
            $table->json('worker_representatives'); // Daftar perwakilan pekerja (1:1)
            $table->text('meeting_minutes'); // Risalah musyawarah mufakat
            $table->text('action_items')->nullable(); // Rekomendasi bersama & deadline
            $table->date('disnaker_reported_at')->nullable(); // Laporan 6 bulanan ke Disnaker
            $table->string('status', 30)->default('conducted'); // scheduled, conducted, minutes_signed, reported_to_disnaker
            $table->timestamps();

            $table->index(['tenant_id', 'meeting_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bipartite_meetings');
        Schema::dropIfExists('company_regulations');
        Schema::dropIfExists('overtime_orders');
        Schema::dropIfExists('fringe_benefits');
        Schema::dropIfExists('employee_mcu_records');
        Schema::dropIfExists('bpjs_jkk_claims');
        Schema::dropIfExists('workplace_incidents');
    }
};
