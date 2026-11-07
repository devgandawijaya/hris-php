<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expatriate Work Permits & RPTKA (PP No. 34 Tahun 2021 & Permenaker No. 8/2021)
        Schema::create('expatriate_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('rptka_number', 100); // Pengesahan RPTKA Kemnaker
            $table->date('rptka_approval_date');
            $table->date('rptka_expiry_date');
            $table->string('job_position_title', 150); // Jabatan yang diizinkan (Non-HR)
            $table->string('passport_number', 50);
            $table->date('passport_expiry_date');
            $table->string('itas_vitas_number', 100)->nullable(); // Izin Tinggal Terbatas
            $table->date('itas_expiry_date')->nullable();
            $table->decimal('dkptka_monthly_amount_usd', 8, 2)->default(100.00); // USD 100 / bulan
            $table->string('dkptka_payment_proof', 150)->nullable(); // NTPN / Bukti Bayar PNBP Kas Negara
            $table->foreignId('companion_employee_id')->nullable()->constrained('employees')->nullOnDelete(); // TKI / WNI Pendamping (Pasal 11)
            $table->text('technology_transfer_plan')->nullable(); // Rencana Alih Teknologi & Keahlian
            $table->string('status', 30)->default('active'); // active, expiring_soon, expired, repatriated
            $table->timestamps();

            $table->index(['tenant_id', 'rptka_expiry_date', 'status']);
        });

        // 2. Jaminan Kehilangan Pekerjaan (JKP BPJS Ketenagakerjaan & PP No. 37 Tahun 2021)
        Schema::create('jkp_claim_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('certificate_number', 50)->unique(); // JKP-CERT/YYYY/XXXX
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('termination_date');
            $table->string('termination_reason_code', 50); // efisiensi_merger, pailit, force_majeure, perusahaan_tutup
            $table->string('bpjs_tk_card_number', 50);
            $table->unsignedSmallInteger('consecutive_paid_months')->default(6); // Minimal 6 bulan berturut-turut
            $table->unsignedSmallInteger('total_paid_months_24m')->default(12); // Minimal 12 bulan dalam 24 bulan
            $table->boolean('eligible_for_jkp')->default(true);
            $table->decimal('reference_salary_cap', 15, 2)->default(5000000.00); // Batas upah acuan Rp 5.000.000 (Pasal 21)
            $table->decimal('month_1_to_3_benefit_monthly', 15, 2); // 45% x min(upah, cap)
            $table->decimal('month_4_to_6_benefit_monthly', 15, 2); // 25% x min(upah, cap)
            $table->decimal('total_potential_cash_benefit', 15, 2); // (45% x 3) + (25% x 3)
            $table->string('disnaker_reporting_status', 30)->default('reported'); // reported, verified_kemnaker, claimed
            $table->string('siapkerja_tracking_code', 100)->nullable(); // Kode lacak portal SiapKerja Kemnaker
            $table->text('company_recommendation_letter')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'termination_date', 'eligible_for_jkp']);
        });

        // 3. Employee Cooperative (Koperasi Karyawan UU No. 25/1992 & UU 13/2003 Pasal 100)
        Schema::create('employee_cooperatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('member_number', 50)->unique(); // KOPKAR-YYYY-XXXX
            $table->date('join_date');
            $table->string('status', 30)->default('active'); // active, suspended, withdrawn
            $table->decimal('mandatory_savings', 15, 2)->default(50000.00); // Simpanan Wajib per bulan
            $table->decimal('voluntary_savings', 15, 2)->default(0.00); // Simpanan Sukarela per bulan
            $table->decimal('principal_savings_paid', 15, 2)->default(100000.00); // Simpanan Pokok awal
            $table->decimal('total_accumulated_savings', 15, 2)->default(0.00);
            $table->decimal('active_loan_balance', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 4. Cooperative Transactions & Payroll Deductions
        Schema::create('cooperative_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_cooperative_id')->constrained('employee_cooperatives')->cascadeOnDelete();
            $table->string('transaction_type', 40); // mandatory_saving, voluntary_saving, loan_disbursement, loan_repayment, welfare_aid_disbursement, shu_dividend
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->boolean('is_payroll_deducted')->default(true);
            $table->string('reference_notes', 255)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'transaction_type', 'transaction_date']);
        });

        // 5. Continuous Shift Rosters (Sektor Khusus 24/7 Kepmenakertrans No. KEP-233/MEN/2003)
        Schema::create('continuous_shift_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('roster_code', 50)->unique(); // ROSTER-YYYY-XXXX
            $table->string('name', 150); // e.g. "Pola Rotasi 3 Shift 4 Grup Kontinu (24/7)"
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedSmallInteger('cycle_days')->default(28); // 28 hari siklus rotasi penuh
            $table->unsignedSmallInteger('work_days_per_cycle')->default(21);
            $table->unsignedSmallInteger('off_days_per_cycle')->default(7);
            $table->decimal('night_shift_allowance_rate', 15, 2)->default(50000.00); // Tunjangan Shift Malam
            $table->decimal('standby_allowance_rate', 15, 2)->default(75000.00); // Tunjangan Siaga / On-Call
            $table->json('rotation_pattern_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        // 6. Shift Roster Employee Assignments
        Schema::create('employee_shift_roster_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('continuous_shift_roster_id')->constrained('continuous_shift_rosters')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('group_name', 50); // Grup A, Grup B, Grup C, Grup D
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // 7. Penyelesaian Perselisihan Hubungan Industrial / Mediasi Tripartit (UU No. 2 Tahun 2004)
        Schema::create('tripartite_industrial_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('case_number', 50)->unique(); // PHI-CASE/YYYY/XXXX
            $table->string('dispute_type', 50); // perselisihan_hak, perselisihan_kepentingan, perselisihan_phk, perselisihan_antar_serikat
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employee_name_external', 150)->nullable(); // Serikat Pekerja / Kuasa Hukum
            $table->foreignId('bipartite_meeting_id')->nullable()->constrained('bipartite_meetings')->nullOnDelete();
            $table->string('disnaker_mediator_name', 100);
            $table->string('disnaker_office', 150);
            $table->date('mediation_date');
            $table->text('dispute_chronology');
            $table->text('mediator_written_recommendation')->nullable(); // Anjuran Tertulis Mediator
            $table->string('company_response', 30)->default('pending'); // pending, accepted, rejected
            $table->string('worker_response', 30)->default('pending'); // pending, accepted, rejected
            $table->string('settlement_status', 40)->default('mediation_stage'); // bipartite_failed, mediation_stage, peaceful_agreement_pb, escalated_to_phi_court, settled
            $table->string('joint_agreement_number', 100)->nullable(); // Nomor Akta Pendaftaran PB di PHI
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'dispute_type', 'settlement_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tripartite_industrial_disputes');
        Schema::dropIfExists('employee_shift_roster_assignments');
        Schema::dropIfExists('continuous_shift_rosters');
        Schema::dropIfExists('cooperative_transactions');
        Schema::dropIfExists('employee_cooperatives');
        Schema::dropIfExists('jkp_claim_certifications');
        Schema::dropIfExists('expatriate_permits');
    }
};
