<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Surat Peringatan (Disciplinary Warning Letters: SP1, SP2, SP3 per UU Ketenagakerjaan / PP 35/2021)
        Schema::create('employee_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('warning_level', 20); // sp1, sp2, sp3
            $table->string('letter_number', 100)->unique();
            $table->date('issued_date');
            $table->date('valid_until'); // Standard 6 months validity per Pasal 161 UU Ketenagakerjaan
            $table->text('reason'); // Violation details / dasar pelanggaran tata tertib
            $table->text('action_plan')->nullable(); // Target perbaikan performa / corrective action
            $table->foreignId('authorized_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('active'); // active, expired, revoked
            $table->string('file_path')->nullable(); // Upload scanned signed letter
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'status']);
        });

        // 2. Employee Grievance & Whistleblowing (Keluhan & Pengaduan Karyawan)
        Schema::create('employee_grievances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('category', 50); // workplace_conflict, harassment, compensation, facility, unfair_treatment, other
            $table->string('subject');
            $table->text('description');
            $table->boolean('is_anonymous')->default(false);
            $table->string('status', 30)->default('submitted'); // submitted, in_review, investigating, resolved, closed
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 3. Company Announcements (Pengumuman Resmi Perusahaan)
        Schema::create('company_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('category', 50)->default('general'); // general, policy, holiday, event, urgent
            $table->foreignId('target_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->boolean('is_pinned')->default(false);
            $table->date('published_at');
            $table->date('expires_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'is_pinned']);
        });

        // 4. HR Helpdesk & Employee Service Requests (Layanan Mandiri HR)
        Schema::create('hr_helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('ticket_number', 50)->unique();
            $table->string('category', 50); // work_certificate, bank_reference, data_correction, bpjs_inquiry, general
            $table->string('subject');
            $table->text('description');
            $table->string('status', 30)->default('open'); // open, in_progress, resolved, rejected
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_helpdesk_tickets');
        Schema::dropIfExists('company_announcements');
        Schema::dropIfExists('employee_grievances');
        Schema::dropIfExists('employee_warnings');
    }
};
