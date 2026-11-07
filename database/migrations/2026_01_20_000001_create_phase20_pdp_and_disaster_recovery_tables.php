<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Log Persetujuan Pemrosesan Data Pribadi (UU No. 27/2022 Pasal 20 - 24)
        Schema::create('pdp_consent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('consent_type', 100); // employment_contract, biometrics_attendance, health_insurance_sharing, salary_banking
            $table->text('purpose_description');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('consented_at');
            $table->timestamp('revoked_at')->nullable();
            $table->string('status', 30)->default('granted'); // granted, revoked, expired
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // 2. Permohonan Hak Subjek Data / DSAR (UU No. 27/2022 Pasal 5 s/d 15)
        Schema::create('pdp_dsar_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('request_number', 50)->unique(); // DSAR-2026-XXXXX
            $table->string('request_type', 50); // data_portability, data_access, data_rectification, data_erasure
            $table->text('reason_or_scope')->nullable();
            $table->date('request_date');
            $table->date('completion_deadline'); // Maksimal 3 x 24 jam s.d. 72 jam kerja (Pasal 8 & 13)
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, in_progress, fulfilled, rejected
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'request_type']);
        });

        // 3. Log Insiden Kegagalan Pelindungan Data Pribadi (Notifikasi 72 Jam Pasal 46 UU PDP)
        Schema::create('pdp_breach_incident_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('incident_number', 50)->unique(); // BREACH-2026-XXXXX
            $table->string('breach_type', 100); // unauthorized_access, accidental_leak, ransomware_threat, credential_theft
            $table->timestamp('detected_at');
            $table->unsignedInteger('affected_records_count')->default(0);
            $table->timestamp('authority_notified_at')->nullable(); // Wajib maks 3 x 24 jam (Pasal 46 ayat 2)
            $table->timestamp('subjects_notified_at')->nullable();
            $table->text('incident_description');
            $table->text('remediation_measures');
            $table->string('status', 30)->default('investigating'); // investigating, contained, notified, resolved
            $table->timestamps();

            $table->index(['tenant_id', 'detected_at']);
        });

        // 4. Konfigurasi Disaster Recovery & Multi-Region High Availability
        Schema::create('disaster_recovery_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('primary_region', 50)->default('ap-southeast-3 (Jakarta)');
            $table->string('standby_region', 50)->default('ap-southeast-1 (Singapore)');
            $table->string('replication_mode', 50)->default('synchronous'); // synchronous, semi_sync, asynchronous
            $table->unsignedSmallInteger('rpo_target_minutes')->default(5); // Recovery Point Objective target
            $table->unsignedSmallInteger('rto_target_minutes')->default(15); // Recovery Time Objective target
            $table->unsignedInteger('current_replication_lag_ms')->default(120); // Replikasi lag real-time
            $table->string('health_status', 30)->default('healthy'); // healthy, degraded, failed_over
            $table->timestamp('last_heartbeat_at');
            $table->timestamps();

            $table->index(['tenant_id', 'health_status']);
        });

        // 5. Riwayat Latihan Pemulihan Bencana (Disaster Recovery Drill Runs)
        Schema::create('disaster_recovery_drill_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->date('drill_date');
            $table->string('scenario_type', 100); // primary_datacenter_outage, network_partition, storage_corruption
            $table->unsignedInteger('actual_failover_duration_seconds');
            $table->unsignedInteger('data_loss_records_count')->default(0);
            $table->boolean('rpo_target_achieved')->default(true);
            $table->boolean('rto_target_achieved')->default(true);
            $table->text('audit_summary');
            $table->string('lead_auditor_name', 100);
            $table->string('status', 30)->default('passed'); // passed, passed_with_notes, failed
            $table->timestamps();

            $table->index(['tenant_id', 'drill_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_recovery_drill_logs');
        Schema::dropIfExists('disaster_recovery_configs');
        Schema::dropIfExists('pdp_breach_incident_logs');
        Schema::dropIfExists('pdp_dsar_requests');
        Schema::dropIfExists('pdp_consent_logs');
    }
};
