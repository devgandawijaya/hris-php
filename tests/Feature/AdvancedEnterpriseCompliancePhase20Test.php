<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\User;
use App\Models\PdpConsentLog;
use App\Models\PdpDsarRequest;
use App\Models\PdpBreachIncidentLog;
use App\Models\DisasterRecoveryConfig;
use App\Models\DisasterRecoveryDrillLog;
use App\Services\Compliance\PdpComplianceService;
use App\Services\Infrastructure\DisasterRecoveryService;
use App\Services\Security\ZeroTrustSecurityService;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdvancedEnterpriseCompliancePhase20Test extends TestCase
{
    use RefreshDatabase;
    protected Tenant $tenant;
    protected Company $company;
    protected Department $secDept;
    protected Position $dpoPos;
    protected Employee $employee;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-phase20-enterprise-pdp-dr'],
            ['name' => 'PT Test Phase 20 Enterprise DR & PDP', 'status' => 'active']
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'name' => 'PT Test Phase 20 Enterprise DR & PDP'],
            [
                'legal_name' => 'PT Test Phase 20 Enterprise DR & PDP Tbk',
                'npwp' => '01.234.567.8-020.000',
                'address' => 'Cyber 2 Tower Lt. 35, Jl. H.R. Rasuna Said Blok X-5',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12950',
                'phone' => '+62 21 8899 0020',
                'email' => 'dpo-secops@p20drpdp.test',
            ]
        );

        $this->secDept = Department::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Information Security & Data Privacy']
        );

        $this->dpoPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'department_id' => $this->secDept->id, 'title' => 'Data Protection Officer (DPO)']
        );

        $this->employee = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'employee_number' => 'P20-EMP-DPO-001'],
            [
                'company_id' => $this->company->id,
                'nik_ktp' => '3171012345678001',
                'first_name' => 'Aditya',
                'last_name' => 'Kurniawan',
                'email' => 'aditya.kurniawan@p20drpdp.test',
                'gender' => 'male',
                'department_id' => $this->secDept->id,
                'position_id' => $this->dpoPos->id,
                'join_date' => Carbon::now()->subMonths(24)->toDateString(),
                'employment_status' => 'pkwtt',
                'basic_salary' => 28000000.00,
                'status' => 'active',
            ]
        );

        $this->user = User::firstOrCreate(
            ['email' => 'dpo.admin@p20drpdp.test'],
            [
                'name' => 'DPO Security Admin',
                'password' => bcrypt('SecOpsPassword2026!'),
            ]
        );
    }

    /**
     * 1. Test PDP Consent Logging and Revocation under Pasal 9 & 20 UU No. 27/2022.
     */
    public function test_pdp_consent_logging_and_revocation(): void
    {
        $service = new PdpComplianceService();

        $consent = $service->recordConsent(
            $this->employee,
            'biometric_attendance_and_payroll',
            'Persetujuan pemrosesan data pengenalan wajah dan NIK untuk payroll',
            '10.20.30.40',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'
        );

        $this->assertNotNull($consent->id);
        $this->assertEquals('granted', $consent->status);
        $this->assertEquals('biometric_attendance_and_payroll', $consent->consent_type);
        $this->assertEquals('10.20.30.40', $consent->ip_address);
        $this->assertNull($consent->revoked_at);

        // Test Revocation under Pasal 9 UU PDP
        $revokedConsent = $service->revokeConsent($consent);

        $this->assertEquals('revoked', $revokedConsent->status);
        $this->assertNotNull($revokedConsent->revoked_at);
    }

    /**
     * 2. Test PDP DSAR Data Portability Package Export under Pasal 13 UU No. 27/2022.
     */
    public function test_pdp_dsar_portability_package_export(): void
    {
        $service = new PdpComplianceService();

        // Record a consent log first
        $service->recordConsent(
            $this->employee,
            'health_insurance_disclosure',
            'Persetujuan transfer data BPJS Kesehatan'
        );

        $dsar = $service->submitDsarRequest(
            $this->employee,
            'data_portability',
            'Permintaan ekspor riwayat data pribadi lengkap untuk portabilitas ke platform baru.'
        );

        $this->assertNotNull($dsar->id);
        $this->assertEquals('pending', $dsar->status);

        $package = $service->exportPortabilityPackage($dsar, $this->user->id);

        $this->assertIsArray($package);
        $this->assertStringContainsString('Pasal 13', $package['pdp_statutory_basis']);
        $this->assertEquals($dsar->request_number, $package['request_number']);
        $this->assertEquals($this->employee->nik, $package['data_subject']['nik']);
        $this->assertEquals($this->employee->full_name, $package['data_subject']['full_name']);
        $this->assertNotEmpty($package['consents_history']);

        // Check DSAR model fulfillment status
        $dsar->refresh();
        $this->assertEquals('fulfilled', $dsar->status);
        $this->assertNotNull($dsar->fulfilled_at);
        $this->assertEquals($this->user->id, $dsar->handled_by_user_id);
    }

    /**
     * 3. Test PDP DSAR Completion Deadline Calculation (Maksimal 3 Hari Kerja).
     */
    public function test_pdp_dsar_completion_deadline_enforcement(): void
    {
        $service = new PdpComplianceService();

        $dsar = $service->submitDsarRequest(
            $this->employee,
            'data_access',
            'Permintaan akses dan verifikasi kelengkapan profil data kepegawaian.'
        );

        $this->assertStringStartsWith('DSAR-', $dsar->request_number);
        $this->assertEquals(Carbon::today()->toDateString(), $dsar->request_date->toDateString());
        $this->assertEquals(Carbon::today()->addDays(3)->toDateString(), $dsar->completion_deadline->toDateString());
        $this->assertEquals('pending', $dsar->status);
    }

    /**
     * 4. Test PDP DSAR Rejection for Invalid Request Types.
     */
    public function test_pdp_dsar_rejects_invalid_request_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Tipe permohonan hak PDP 'illegal_type_unsupported' tidak valid.");

        $service = new PdpComplianceService();
        $service->submitDsarRequest($this->employee, 'illegal_type_unsupported');
    }

    /**
     * 5. Test PDP Data Breach Notification Audit within 72 Hours under Pasal 46 UU PDP.
     */
    public function test_pdp_data_breach_incident_notification_within_seventy_two_hours(): void
    {
        $service = new PdpComplianceService();

        $breach = $service->recordDataBreach(
            $this->company,
            'unauthorized_api_access',
            45,
            'Deteksi anomali akses API gateway di luar jam kerja dengan token kedaluwarsa.',
            'Pencabutan token seketika, pemblokiran CIDR IP penyerang, dan pengaktifan MFA wajib.'
        );

        $this->assertNotNull($breach->id);
        $this->assertStringStartsWith('BREACH-', $breach->incident_number);
        $this->assertEquals('notified', $breach->status);
        $this->assertEquals(45, $breach->affected_records_count);

        // Verification of statutory 72 hours window
        $hoursUntilNotification = $breach->detected_at->diffInHours($breach->authority_notified_at);
        $this->assertLessThanOrEqual(72, $hoursUntilNotification);
        $this->assertEquals(2, $hoursUntilNotification); // Dispatched in 2 hours
    }

    /**
     * 6. Test Multi-Region Disaster Recovery Config Initialization and SLA Targets.
     */
    public function test_disaster_recovery_config_initialization_and_targets(): void
    {
        $service = new DisasterRecoveryService();

        $config = $service->getOrCreateConfig(
            $this->company,
            'ap-southeast-3 (Jakarta)',
            'ap-southeast-1 (Singapore)',
            5,
            15
        );

        $this->assertNotNull($config->id);
        $this->assertEquals('ap-southeast-3 (Jakarta)', $config->primary_region);
        $this->assertEquals('ap-southeast-1 (Singapore)', $config->standby_region);
        $this->assertEquals(5, $config->rpo_target_minutes);
        $this->assertEquals(15, $config->rto_target_minutes);
        $this->assertEquals('synchronous', $config->replication_mode);
        $this->assertEquals('healthy', $config->health_status);
        $this->assertLessThanOrEqual(100, $config->current_replication_lag_ms);
    }

    /**
     * 7. Test Disaster Recovery Drill Run Succeeds when RPO and RTO Targets are Achieved.
     */
    public function test_disaster_recovery_drill_run_success_when_rpo_and_rto_achieved(): void
    {
        $service = new DisasterRecoveryService();
        $config = $service->getOrCreateConfig($this->company);

        // Drill: 180 seconds failover (<= 15 min / 900s) and 0 record loss (<= 5 min RPO)
        $drill = $service->executeDrillRun(
            $config,
            'simulated_datacenter_power_failure',
            180,
            0,
            'Auditor Utama Cloud Security'
        );

        $this->assertNotNull($drill->id);
        $this->assertTrue($drill->rpo_target_achieved);
        $this->assertTrue($drill->rto_target_achieved);
        $this->assertEquals('passed', $drill->status);
        $this->assertEquals(180, $drill->actual_failover_duration_seconds);
        $this->assertEquals(0, $drill->data_loss_records_count);
        $this->assertStringContainsString('Integritas data 100%', $drill->audit_summary);
    }

    /**
     * 8. Test Disaster Recovery Drill Run Flags Notes when RTO or RPO Target is Exceeded.
     */
    public function test_disaster_recovery_drill_run_notes_when_rto_or_rpo_exceeded(): void
    {
        $service = new DisasterRecoveryService();
        $config = $service->getOrCreateConfig($this->company);

        // Failover took 1200 seconds (20 min > 15 min RTO target), and lost 12 records
        $drill = $service->executeDrillRun(
            $config,
            'submarine_cable_interruption',
            1200,
            12,
            'Auditor Eksternal ISO 22301'
        );

        $this->assertFalse($drill->rpo_target_achieved);
        $this->assertFalse($drill->rto_target_achieved);
        $this->assertEquals('passed_with_notes', $drill->status);
        $this->assertEquals(12, $drill->data_loss_records_count);
    }

    /**
     * 9. Test Disaster Recovery Emergency Failover Region Switch.
     */
    public function test_disaster_recovery_emergency_failover_region_switch(): void
    {
        $service = new DisasterRecoveryService();
        $config = $service->getOrCreateConfig(
            $this->company,
            'ap-southeast-3 (Jakarta)',
            'ap-southeast-1 (Singapore)'
        );

        $this->assertEquals('ap-southeast-3 (Jakarta)', $config->primary_region);

        // Trigger Emergency Regional Failover
        $failedOverConfig = $service->triggerFailover($config);

        $this->assertEquals('ap-southeast-1 (Singapore)', $failedOverConfig->primary_region);
        $this->assertEquals('ap-southeast-3 (Jakarta)', $failedOverConfig->standby_region);
        $this->assertEquals('failed_over', $failedOverConfig->health_status);
    }

    /**
     * 10. Test Zero Trust Security Sensitive Columns Encryption Audit (Pasal 35 UU PDP).
     */
    public function test_zero_trust_security_sensitive_columns_encryption_audit(): void
    {
        $service = new ZeroTrustSecurityService();
        $audit = $service->auditDataProtectionSecurity($this->company);

        $this->assertArrayHasKey('sensitive_fields_audit', $audit);
        $fields = $audit['sensitive_fields_audit'];

        $this->assertArrayHasKey('nik', $fields);
        $this->assertArrayHasKey('npwp', $fields);
        $this->assertArrayHasKey('bank_account_number', $fields);
        $this->assertArrayHasKey('basic_salary', $fields);

        $this->assertTrue($fields['nik']['encrypted']);
        $this->assertTrue($fields['npwp']['encrypted']);
        $this->assertTrue($fields['bank_account_number']['encrypted']);
        $this->assertTrue($fields['basic_salary']['encrypted']);

        $this->assertStringContainsString('AES-256-GCM', $fields['nik']['algo']);
        $this->assertStringContainsString('AES-256-GCM', $fields['basic_salary']['algo']);
    }

    /**
     * 11. Test Zero Trust Security Score and Technical Safeguards Standard.
     */
    public function test_zero_trust_security_score_calculation(): void
    {
        $service = new ZeroTrustSecurityService();
        $audit = $service->auditDataProtectionSecurity($this->company);

        $this->assertGreaterThanOrEqual(95.0, (float) $audit['overall_security_score']);
        $this->assertEquals('fully_compliant', $audit['status']);
        $this->assertStringContainsString('Pasal 35', $audit['compliance_law']);
        $this->assertStringContainsString('FIPS 140-2', $audit['key_management_standard']);
        $this->assertTrue($audit['zero_trust_status']['least_privilege_enforced']);
        $this->assertTrue($audit['zero_trust_status']['audit_trail_logging']);
    }

    /**
     * 12. Test Company and Employee Relationships with PDP & Disaster Recovery Models.
     */
    public function test_pdp_and_dr_model_relationships(): void
    {
        $pdpService = new PdpComplianceService();
        $drService = new DisasterRecoveryService();

        $consent = $pdpService->recordConsent($this->employee, 'general_hr_storage', 'General HR Consent');
        $dsar = $pdpService->submitDsarRequest($this->employee, 'data_access', 'Access Request');
        $breach = $pdpService->recordDataBreach($this->company, 'unauthorized_login', 1, 'Test Incident', 'Test Remediation');
        $drConfig = $drService->getOrCreateConfig($this->company);
        $drill = $drService->executeDrillRun($drConfig, 'routine_failover_test', 120, 0);

        // Company relationships
        $this->assertTrue($this->company->pdpConsentLogs()->where('id', $consent->id)->exists());
        $this->assertTrue($this->company->pdpDsarRequests()->where('id', $dsar->id)->exists());
        $this->assertTrue($this->company->pdpBreachIncidentLogs()->where('id', $breach->id)->exists());
        $this->assertInstanceOf(DisasterRecoveryConfig::class, $this->company->disasterRecoveryConfig);
        $this->assertEquals($drConfig->id, $this->company->disasterRecoveryConfig->id);
        $this->assertTrue($this->company->disasterRecoveryDrillLogs()->where('id', $drill->id)->exists());

        // Employee relationships
        $this->assertTrue($this->employee->pdpConsentLogs()->where('id', $consent->id)->exists());
        $this->assertTrue($this->employee->pdpDsarRequests()->where('id', $dsar->id)->exists());
    }
}
