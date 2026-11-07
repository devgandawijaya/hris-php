<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Flexi-Benefit / Cafeteria Plan (Plafon Poin Kesejahteraan & Reimbursement)
        Schema::create('flex_benefit_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 50); // medical_topup, optical_dental, wellness_gym, child_education, eco_transport
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'code']);
        });

        Schema::create('flex_benefit_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('job_grade_id')->nullable()->constrained('job_grades')->nullOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->decimal('total_points_allocated', 15, 2)->default(5000000.00); // 1 Poin = Rp 1
            $table->decimal('points_used', 15, 2)->default(0.00);
            $table->decimal('points_remaining', 15, 2)->default(5000000.00);
            $table->string('status', 30)->default('active'); // active, expired, closed
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'period_year']);
        });

        Schema::create('flex_benefit_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('flex_benefit_allocation_id')->constrained('flex_benefit_allocations')->cascadeOnDelete();
            $table->foreignId('flex_benefit_category_id')->constrained('flex_benefit_categories')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('claim_number', 50)->unique(); // FLX-CLAIM-YYYY-XXXX
            $table->date('claim_date');
            $table->decimal('claim_amount', 15, 2);
            $table->decimal('points_deducted', 15, 2);
            $table->string('receipt_file_url', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('submitted'); // submitted, approved, rejected, reimbursed
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // 2. Tunjangan Hari Raya (THR) Statutori & Denda Keterlambatan (Permenaker No. 6/2016 jo. PP 58/2023)
        Schema::create('thr_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('tax_year');
            $table->string('religious_holiday_type', 50); // idul_fitri, natal, nyepi, waisak, imlek
            $table->unsignedSmallInteger('tenure_months')->default(12);
            $table->decimal('prorata_multiplier', 5, 4)->default(1.0000); // 1.0 jika >= 12 bulan, n/12 jika < 12 bulan
            $table->decimal('regular_wage_basis', 15, 2); // Gaji Pokok + Tunjangan Tetap
            $table->decimal('calculated_thr_amount', 15, 2);
            $table->decimal('ter_tax_deduction', 15, 2)->default(0.00); // Pajak PPh 21 TER gabungan
            $table->decimal('net_thr_paid', 15, 2);
            $table->date('statutory_payment_deadline'); // H-7 Hari Raya Keagamaan (Pasal 5 Permenaker 6/2016)
            $table->date('actual_payment_date');
            $table->decimal('late_penalty_interest', 15, 2)->default(0.00); // Denda 5% bila terlambat (Pasal 10 Permenaker 6/2016)
            $table->string('status', 30)->default('calculated'); // calculated, approved, paid
            $table->timestamps();

            $table->index(['tenant_id', 'tax_year', 'religious_holiday_type']);
        });

        // 3. Employee Stock Option Plan (ESOP / RSU) & Long-Term Incentives (UU No. 40/2007)
        Schema::create('esop_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('grant_number', 50)->unique(); // ESOP-GRANT-YYYY-XXXX
            $table->date('grant_date');
            $table->unsignedInteger('total_shares_granted'); // Jumlah lembar opsi saham
            $table->unsignedSmallInteger('vesting_schedule_years')->default(4); // 4 tahun jadwal vesting
            $table->unsignedSmallInteger('cliff_months')->default(12); // 1 tahun masa cliff
            $table->decimal('strike_price_per_share', 15, 2); // Harga pelaksanaan opsi per lembar
            $table->unsignedInteger('vested_shares_count')->default(0);
            $table->unsignedInteger('exercised_shares_count')->default(0);
            $table->decimal('fair_market_value', 15, 2)->default(0.00); // Nilai pasar wajar saat grant
            $table->string('status', 30)->default('active'); // active, fully_vested, exercised, cancelled
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // 4. Asuransi Kesehatan Tambahan & Coordination of Benefit / CoB BPJS Kesehatan (Perpres 82/2018)
        Schema::create('cob_insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('insurance_company_name', 150); // e.g. Allianz, Prudential, AXA Mandiri
            $table->string('policy_number', 100);
            $table->string('plan_type', 50)->default('Platinum Executive');
            $table->string('bpjs_health_card_number', 50);
            $table->string('cob_claim_mechanism', 50)->default('bpjs_first_payer'); // bpjs_first_payer, direct_cashless
            $table->decimal('annual_limit_amount', 15, 2)->default(100000000.00); // Plafon Rp 100 Juta
            $table->decimal('current_usage_amount', 15, 2)->default(0.00);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // 5. Panitia Pembina Keselamatan & Kesehatan Kerja (P2K3 Disnaker Permenaker No. 04/1987)
        Schema::create('p2k3_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('decree_number', 100); // SK Pembentukan Internal P2K3
            $table->string('disnaker_ratification_number', 100)->nullable(); // SK Pengesahan Disnaker setempat
            $table->date('formation_date');
            $table->string('chairman_name', 100); // Pimpinan Perusahaan
            $table->string('secretary_ahli_k3_name', 100); // Wajib Ahli K3 Umum bersertifikat
            $table->string('ahli_k3_skp_number', 100); // Nomor SKP Ahli K3 Umum Kemnaker
            $table->unsignedSmallInteger('total_committee_members')->default(10);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('p2k3_quarterly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('p2k3_committee_id')->constrained('p2k3_committees')->cascadeOnDelete();
            $table->unsignedSmallInteger('reporting_year');
            $table->string('quarter', 10); // Q1, Q2, Q3, Q4
            $table->unsignedSmallInteger('workplace_inspections_count')->default(3);
            $table->unsignedSmallInteger('safety_trainings_count')->default(2);
            $table->text('incident_summary')->nullable(); // Zero Accident or Incident summary
            $table->date('disnaker_submission_date')->nullable();
            $table->string('disnaker_receipt_number', 100)->nullable(); // Bukti lapor triwulan Disnaker
            $table->string('status', 30)->default('draft'); // draft, submitted_disnaker
            $table->timestamps();

            $table->index(['tenant_id', 'reporting_year', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2k3_quarterly_reports');
        Schema::dropIfExists('p2k3_committees');
        Schema::dropIfExists('cob_insurance_policies');
        Schema::dropIfExists('esop_grants');
        Schema::dropIfExists('thr_disbursements');
        Schema::dropIfExists('flex_benefit_claims');
        Schema::dropIfExists('flex_benefit_allocations');
        Schema::dropIfExists('flex_benefit_categories');
    }
};
