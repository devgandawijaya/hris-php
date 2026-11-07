<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Job Grades & Wage Scales (Permenaker No. 1/2017)
        Schema::create('job_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('grade_code', 20); // e.g. G1, G2, G3, or I, II, III
            $table->string('name', 100); // e.g. Entry Staff, Senior Officer, Manager, VP
            $table->unsignedInteger('level_order')->default(1);
            $table->decimal('min_salary', 15, 2);
            $table->decimal('mid_salary', 15, 2);
            $table->decimal('max_salary', 15, 2);
            $table->decimal('spread_percentage', 5, 2)->default(0.00); // (Max - Min) / Min * 100
            $table->decimal('midpoint_progression', 5, 2)->default(0.00); // (Mid_N - Mid_N-1) / Mid_N-1 * 100
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'company_id', 'grade_code']);
            $table->index(['tenant_id', 'company_id', 'level_order']);
        });

        // Add job_grade_id to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('job_grade_id')->nullable()->after('position_id')->constrained('job_grades')->nullOnDelete();
        });

        // 2. Business Trip Orders / Surat Perintah Perjalanan Dinas (SPPD)
        Schema::create('business_trip_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('sppd_number', 60)->unique();
            $table->string('purpose', 255);
            $table->string('origin_city', 100);
            $table->string('destination_city', 100);
            $table->date('departure_date');
            $table->date('return_date');
            $table->unsignedInteger('duration_days')->default(1);
            $table->string('transport_type', 50)->default('flight'); // flight, train, company_car, personal_vehicle, public_transport
            $table->boolean('lodging_needed')->default(true);
            $table->decimal('per_diem_daily_rate', 15, 2)->default(0.00);
            $table->decimal('estimated_budget', 15, 2)->default(0.00);
            $table->string('status', 30)->default('draft'); // draft, submitted, approved, rejected, ongoing, completed, cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'company_id', 'status']);
            $table->index(['tenant_id', 'employee_id']);
        });

        // 3. Business Trip Cash Advances (Uang Muka Perjalanan Dinas)
        Schema::create('business_trip_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('business_trip_order_id')->constrained('business_trip_orders')->cascadeOnDelete();
            $table->decimal('amount_requested', 15, 2);
            $table->decimal('amount_approved', 15, 2)->default(0.00);
            $table->string('status', 30)->default('pending'); // pending, approved, disbursed, rejected
            $table->string('transfer_reference', 100)->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'business_trip_order_id']);
        });

        // 4. Business Trip Expenses (Realisasi Pengeluaran Tiket, Hotel, Transportasi, Makan)
        Schema::create('business_trip_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('business_trip_order_id')->constrained('business_trip_orders')->cascadeOnDelete();
            $table->string('expense_category', 50); // per_diem, transport, lodging, meals, representation, toll_parking, other
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'business_trip_order_id']);
        });

        // 5. Business Trip Settlements (Rekonsiliasi Uang Muka vs Realisasi)
        Schema::create('business_trip_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('business_trip_order_id')->constrained('business_trip_orders')->cascadeOnDelete();
            $table->decimal('total_advance', 15, 2)->default(0.00);
            $table->decimal('total_expenses', 15, 2)->default(0.00);
            $table->decimal('balance_amount', 15, 2)->default(0.00); // positive: reimbursement, negative: refund
            $table->string('status', 30)->default('draft'); // draft, submitted, audited, finalized
            $table->foreignId('audited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('audited_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'business_trip_order_id']);
        });

        // 6. Offboarding Clearance Checklist (HR, IT, Finance, Supervisor)
        Schema::create('offboarding_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('resignation_date');
            $table->date('last_working_day');
            $table->string('separation_reason', 50)->default('resigned'); // resigned, contract_end, retired, terminated, deceased

            // 1. HR Clearance (ID card, BPJS fisik, Exit interview sign)
            $table->string('hr_status', 30)->default('pending'); // pending, cleared
            $table->text('hr_notes')->nullable();
            $table->foreignId('hr_cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_cleared_at')->nullable();

            // 2. IT Clearance (Laptop, peripherals, email revoke, SSO wipe)
            $table->string('it_status', 30)->default('pending');
            $table->text('it_notes')->nullable();
            $table->foreignId('it_cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('it_cleared_at')->nullable();

            // 3. Finance Clearance (Pinjaman/Kasbon lunas, advance SPPD, kartu kredit korporat)
            $table->string('finance_status', 30)->default('pending');
            $table->text('finance_notes')->nullable();
            $table->foreignId('finance_cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finance_cleared_at')->nullable();

            // 4. Supervisor Clearance (Handover project/repository, serah terima pekerjaan)
            $table->string('supervisor_status', 30)->default('pending');
            $table->text('supervisor_notes')->nullable();
            $table->foreignId('supervisor_cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_cleared_at')->nullable();

            // Overall Status
            $table->string('overall_status', 30)->default('in_progress'); // in_progress, completed, rejected
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'company_id', 'overall_status']);
            $table->index(['tenant_id', 'employee_id']);
        });

        // 7. Surat Keterangan Bekerja / Paklaring (KUHPerdata Pasal 1603p)
        Schema::create('work_experience_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('offboarding_clearance_id')->nullable()->constrained('offboarding_clearances')->nullOnDelete();
            $table->string('letter_number', 100)->unique();
            $table->string('signer_name', 150);
            $table->string('signer_position', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('last_position', 150);
            $table->string('last_department', 150);
            $table->string('conduct_appraisal', 50)->default('very_good'); // exemplary, very_good, good, satisfactory
            $table->text('achievement_summary')->nullable();
            $table->date('issued_date');
            $table->timestamps();

            $table->index(['tenant_id', 'company_id']);
            $table->index(['tenant_id', 'employee_id']);
        });

        // 8. Tax Form 1721-A1 & e-Bupot 21/26 Records (PER-2/PJ/2024)
        Schema::create('tax_form1721_a1_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedInteger('tax_year');
            $table->string('nomor_bupot', 60); // Format e-Bupot 21/26 resmi
            $table->string('pemotong_npwp', 30);
            $table->string('pemotong_nama', 150);
            $table->string('penerima_npwp', 30)->nullable();
            $table->string('penerima_nik', 30);
            $table->string('penerima_nama', 150);
            $table->text('penerima_alamat')->nullable();
            $table->string('status_ptkp', 10)->default('TK/0');
            $table->unsignedTinyInteger('jumlah_tanggungan')->default(0);
            $table->unsignedTinyInteger('masa_perolehan_awal')->default(1);
            $table->unsignedTinyInteger('masa_perolehan_akhir')->default(12);

            // Rincian Penghasilan Bruto
            $table->decimal('gaji_pensiun_tht', 15, 2)->default(0.00);
            $table->decimal('tunjangan_pph', 15, 2)->default(0.00);
            $table->decimal('tunjangan_lain_lembur', 15, 2)->default(0.00);
            $table->decimal('honorarium_imbalan', 15, 2)->default(0.00);
            $table->decimal('premi_asuransi_jkk_jkm', 15, 2)->default(0.00);
            $table->decimal('natura_kenikmatan', 15, 2)->default(0.00);
            $table->decimal('tantiem_bonus_thr', 15, 2)->default(0.00);
            $table->decimal('penghasilan_bruto', 15, 2)->default(0.00);

            // Pengurang Penghasilan Bruto
            $table->decimal('biaya_jabatan', 15, 2)->default(0.00); // 5% max 6jt/thn
            $table->decimal('iuran_pensiun_jht', 15, 2)->default(0.00);
            $table->decimal('total_pengurang', 15, 2)->default(0.00);

            // Perhitungan PPh 21
            $table->decimal('penghasilan_neto', 15, 2)->default(0.00);
            $table->decimal('penghasilan_neto_masa_sebelumnya', 15, 2)->default(0.00);
            $table->decimal('jumlah_penghasilan_neto_disetahunkan', 15, 2)->default(0.00);
            $table->decimal('ptkp', 15, 2)->default(0.00);
            $table->decimal('pkp', 15, 2)->default(0.00);
            $table->decimal('pph21_terutang_setahun', 15, 2)->default(0.00);
            $table->decimal('pph21_telah_dipotong', 15, 2)->default(0.00);
            $table->decimal('pph21_kurang_lebih_bayar', 15, 2)->default(0.00);

            $table->string('status', 30)->default('draft'); // draft, published, reported
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id', 'tax_year']);
            $table->index(['tenant_id', 'company_id', 'tax_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_form1721_a1_records');
        Schema::dropIfExists('work_experience_letters');
        Schema::dropIfExists('offboarding_clearances');
        Schema::dropIfExists('business_trip_settlements');
        Schema::dropIfExists('business_trip_expenses');
        Schema::dropIfExists('business_trip_advances');
        Schema::dropIfExists('business_trip_orders');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['job_grade_id']);
            $table->dropColumn('job_grade_id');
        });

        Schema::dropIfExists('job_grades');
    }
};
