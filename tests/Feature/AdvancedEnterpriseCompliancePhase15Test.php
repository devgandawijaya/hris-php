<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\EmployeeWarning;
use App\Models\CompanyAsset;
use App\Models\WltkReport;
use App\Models\IjpVacancy;
use App\Models\IjpApplication;
use App\Models\MentorshipPair;
use App\Models\MentorshipCheckin;
use App\Models\AssetHandoverBast;
use App\Models\AssetHandoverItem;
use App\Models\SystemAccessRevocationLog;
use App\Models\DisabilityEmploymentAudit;
use App\Models\EmployeeDisabilityProfile;
use App\Services\Compliance\WltkComplianceService;
use App\Services\Talent\IjpMarketplaceService;
use App\Services\Talent\MentorshipService;
use App\Services\Offboarding\ExitAssetClearanceService;
use App\Services\Compliance\DisabilityComplianceAuditService;
use Carbon\Carbon;
use InvalidArgumentException;

class AdvancedEnterpriseCompliancePhase15Test extends TestCase
{
    protected Tenant $tenant;
    protected Company $company;
    protected Department $techDept;
    protected Position $seniorEngPos;
    protected Position $juniorEngPos;
    protected User $hrManager;
    protected Employee $employeeSenior;
    protected Employee $employeeNewHire;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-phase15-talent-retention'],
            ['name' => 'PT Test Phase 15 Talent Solusindo', 'status' => 'active']
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pt-test-p15-talent'],
            [
                'name' => 'PT Test Phase 15 Talent Solusindo',
                'legal_name' => 'PT Test Phase 15 Talent Solusindo Tbk',
                'npwp' => '01.234.567.8-015.000',
                'address' => 'Jl. Jenderal Sudirman Kav. 15',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'status' => 'active',
            ]
        );

        $this->techDept = Department::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Engineering & Cloud'],
            ['code' => 'ENG-CLOUD']
        );

        $this->seniorEngPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'department_id' => $this->techDept->id, 'title' => 'Senior Backend Engineer'],
            ['code' => 'SR-BE']
        );

        $this->juniorEngPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'department_id' => $this->techDept->id, 'title' => 'Junior Developer'],
            ['code' => 'JR-DEV']
        );

        $this->hrManager = User::firstOrCreate(
            ['email' => 'hrmanager.p15@testphase15.com'],
            [
                'name' => 'Dewi HR Manager',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
                'is_super_admin' => false,
            ]
        );

        $this->employeeSenior = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'senior.budi@testphase15.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P15-001',
                'nik_ktp' => '3171010101900015',
                'first_name' => 'Budi',
                'last_name' => 'Santoso',
                'gender' => 'male',
                'citizenship' => 'WNI',
                'department_id' => $this->techDept->id,
                'position_id' => $this->seniorEngPos->id,
                'employment_status' => 'pkwtt',
                'join_date' => Carbon::today()->subMonths(24)->toDateString(),
                'basic_salary' => 15000000.00,
                'status' => 'active',
                'bpjs_tk_number' => 'TK-9812401',
                'bpjs_kes_number' => 'KES-9812401',
                'has_disability' => false,
            ]
        );

        $this->employeeNewHire = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'newhire.ani@testphase15.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P15-002',
                'nik_ktp' => '3171010101950015',
                'first_name' => 'Ani',
                'last_name' => 'Rahmawati',
                'gender' => 'female',
                'citizenship' => 'WNI',
                'department_id' => $this->techDept->id,
                'position_id' => $this->juniorEngPos->id,
                'employment_status' => 'pkwt',
                'join_date' => Carbon::today()->subMonths(4)->toDateString(),
                'basic_salary' => 7500000.00,
                'status' => 'active',
                'bpjs_tk_number' => 'TK-9812402',
                'bpjs_kes_number' => 'KES-9812402',
                'has_disability' => false,
            ]
        );
    }

    /**
     * Test 1: WLTK draft report generation aggregates workforce demographics and statutory wages
     */
    public function test_wltk_draft_report_generation_aggregates_demographics_wages_and_statutory_status(): void
    {
        $service = app(WltkComplianceService::class);
        $year = (int) date('Y');

        $report = $service->generateDraftReport($this->company, $year, 'annual');

        $this->assertInstanceOf(WltkReport::class, $report);
        $this->assertEquals($this->tenant->id, $report->tenant_id);
        $this->assertEquals($year, $report->reporting_year);
        $this->assertEquals('draft', $report->status);
        $this->assertStringStartsWith('WLTK/', $report->report_number);
        $this->assertGreaterThanOrEqual(2, $report->total_workers);
        $this->assertGreaterThanOrEqual(1, $report->total_wni_male);
        $this->assertGreaterThanOrEqual(1, $report->total_wni_female);
        $this->assertGreaterThan(0, $report->lowest_wage);
        $this->assertGreaterThanOrEqual($report->lowest_wage, $report->highest_wage);
        $this->assertTrue($report->bpjs_tk_registered);
        $this->assertTrue($report->bpjs_kes_registered);
    }

    /**
     * Test 2: WLTK submission marks report submitted with 1-year statutory validity and alerts
     */
    public function test_wltk_submission_receipt_activates_one_year_statutory_validity_and_renewal_alerts(): void
    {
        $service = app(WltkComplianceService::class);
        $report = $service->generateDraftReport($this->company, 2026, 'annual');

        $submissionDate = Carbon::today()->toDateString();
        $receiptNumber = 'TANDA-TERIMA-DISNAKER-2026-9811';

        $submittedReport = $service->markSubmitted($report, $receiptNumber, $submissionDate);

        $this->assertEquals('submitted_kemnaker', $submittedReport->status);
        $this->assertEquals($receiptNumber, $submittedReport->disnaker_receipt_number);
        $this->assertEquals(Carbon::parse($submissionDate)->addYear()->toDateString(), $submittedReport->valid_until->toDateString());

        // Check renewal due alerts
        $submittedReport->update(['valid_until' => Carbon::today()->addDays(20)->toDateString()]);
        $alerts = $service->checkRenewalDueAlerts($this->tenant->id, 30);

        $this->assertTrue($alerts->contains('id', $submittedReport->id));
        $this->assertTrue($submittedReport->isRenewalDue(30));
    }

    /**
     * Test 3: WLTK export Kemnaker JSON and CSV formats
     */
    public function test_wltk_export_json_and_csv_generation(): void
    {
        $service = app(WltkComplianceService::class);
        $report = $service->generateDraftReport($this->company, 2026, 'annual');

        $jsonData = $service->exportKemnakerJson($report);
        $this->assertIsArray($jsonData);
        $this->assertArrayHasKey('pelaporan', $jsonData);
        $this->assertArrayHasKey('ketenagakerjaan', $jsonData);
        $this->assertEquals($report->report_number, $jsonData['pelaporan']['nomor_laporan']);

        $csvData = $service->exportKemnakerCsv($report);
        $this->assertIsString($csvData);
        $this->assertStringContainsString('Nomor Laporan WLTK', $csvData);
        $this->assertStringContainsString($report->report_number, $csvData);
    }

    /**
     * Test 4: IJP create vacancy with tenure and performance requirements
     */
    public function test_ijp_create_vacancy_with_tenure_and_performance_gating(): void
    {
        $service = app(IjpMarketplaceService::class);

        $vacancy = $service->createVacancy([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'department_id' => $this->techDept->id,
            'position_id' => $this->seniorEngPos->id,
            'title' => 'Lead DevOps Architect',
            'description' => 'Memimpin modernisasi sistem CI/CD dan multi-cloud.',
            'requirements' => 'Minimal 12 bulan di divisi saat ini, pemahaman Kubernetes.',
            'min_tenure_months' => 12,
            'min_performance_score' => 3.50,
            'open_slots' => 2,
            'application_deadline' => Carbon::today()->addDays(30)->toDateString(),
        ], $this->hrManager->id);

        $this->assertInstanceOf(IjpVacancy::class, $vacancy);
        $this->assertEquals('open', $vacancy->status);
        $this->assertStringStartsWith('IJP-', $vacancy->vacancy_code);
        $this->assertEquals(12, $vacancy->min_tenure_months);
        $this->assertEquals(3.50, (float) $vacancy->min_performance_score);
    }

    /**
     * Test 5: IJP eligibility validation verifies tenure and active warnings
     */
    public function test_ijp_eligibility_validation_identifies_ineligible_due_to_tenure_or_active_sp(): void
    {
        $service = app(IjpMarketplaceService::class);

        $vacancy = $service->createVacancy([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'department_id' => $this->techDept->id,
            'position_id' => $this->seniorEngPos->id,
            'title' => 'Tech Lead Platform',
            'description' => 'Internal lead position.',
            'requirements' => 'Tenure min 12 bulan.',
            'min_tenure_months' => 12,
            'min_performance_score' => 3.00,
            'open_slots' => 1,
            'application_deadline' => Carbon::today()->addDays(30)->toDateString(),
        ], $this->hrManager->id);

        // Senior employee has 24 months tenure -> Eligible
        $eligibilitySenior = $service->validateEligibility($this->employeeSenior, $vacancy);
        $this->assertTrue($eligibilitySenior['is_eligible']);
        $this->assertGreaterThanOrEqual(12, $eligibilitySenior['tenure_months']);

        // New hire has 4 months tenure (< 12) -> Ineligible
        $eligibilityNewHire = $service->validateEligibility($this->employeeNewHire, $vacancy);
        $this->assertFalse($eligibilityNewHire['is_eligible']);
        $this->assertStringContainsString('Masa kerja belum memenuhi syarat', $eligibilityNewHire['reasons'][0]);

        // If senior employee receives an active SP -> Ineligible
        EmployeeWarning::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_id' => $this->employeeSenior->id,
            'warning_letter_number' => 'SP1/TEST/2026',
            'warning_level' => 'sp1',
            'issued_date' => Carbon::today()->toDateString(),
            'valid_until' => Carbon::today()->addMonths(6)->toDateString(),
            'reason' => 'Disciplinary warning for test.',
            'status' => 'active',
        ]);

        $eligibilitySeniorWithSp = $service->validateEligibility($this->employeeSenior->fresh(), $vacancy);
        $this->assertFalse($eligibilitySeniorWithSp['is_eligible']);
        $this->assertTrue($eligibilitySeniorWithSp['has_active_warning']);
    }

    /**
     * Test 6: IJP application workflow with manager approval and HR acceptance
     */
    public function test_ijp_application_workflow_manager_approval_and_hr_final_acceptance(): void
    {
        $service = app(IjpMarketplaceService::class);

        $vacancy = $service->createVacancy([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'department_id' => $this->techDept->id,
            'position_id' => $this->seniorEngPos->id,
            'title' => 'Software Architect Special Project',
            'description' => 'Architectural role.',
            'requirements' => 'Requirements.',
            'min_tenure_months' => 6,
            'min_performance_score' => 3.00,
            'open_slots' => 1,
            'application_deadline' => Carbon::today()->addDays(30)->toDateString(),
        ], $this->hrManager->id);

        $application = $service->applyVacancy($this->employeeSenior, $vacancy, [
            'motivation_letter' => 'Saya bertekad memajukan inisiatif arsitektur high-scale perusahaan.',
            'portfolio_url' => 'https://github.com/company/architecture-samples',
        ]);

        $this->assertInstanceOf(IjpApplication::class, $application);
        $this->assertEquals('pending', $application->manager_approval_status);
        $this->assertEquals('in_review', $application->final_decision);

        // Cannot apply twice
        $this->expectException(InvalidArgumentException::class);
        $service->applyVacancy($this->employeeSenior, $vacancy, [
            'motivation_letter' => 'Duplicate application attempt.',
        ]);

        // Manager Approval
        $approvedApp = $service->reviewManagerApproval($application, true, 'Disetujui untuk transfer.');
        $this->assertEquals('approved', $approvedApp->manager_approval_status);

        // HR Final Acceptance
        $effectiveDate = Carbon::today()->addDays(14)->toDateString();
        $acceptedApp = $service->reviewHrDecision($approvedApp, 'accepted', $effectiveDate, 'Lolos interview HR.');
        $this->assertEquals('accepted', $acceptedApp->final_decision);
        $this->assertEquals('passed', $acceptedApp->hr_interview_status);
        $this->assertEquals($effectiveDate, $acceptedApp->transfer_effective_date->toDateString());
    }

    /**
     * Test 7: Onboarding Buddy & Mentorship program assignment and auto milestone scheduling
     */
    public function test_onboarding_buddy_mentorship_assignment_and_90_day_milestone_auto_scheduling(): void
    {
        $service = app(MentorshipService::class);
        $startDate = Carbon::today()->toDateString();

        $pair = $service->assignMentor(
            $this->employeeNewHire,
            $this->employeeSenior,
            $startDate,
            'Pendampingan orientasi 90 hari dan penguasaan arsitektur cloud.'
        );

        $this->assertInstanceOf(MentorshipPair::class, $pair);
        $this->assertEquals('active', $pair->status);
        $this->assertEquals($this->employeeNewHire->id, $pair->mentee_employee_id);
        $this->assertEquals($this->employeeSenior->id, $pair->mentor_employee_id);

        // Check that Day 30, Day 60, Day 90 milestones were automatically scheduled
        $checkins = $pair->checkins;
        $this->assertCount(3, $checkins);

        $milestones = $checkins->pluck('milestone')->toArray();
        $this->assertEquals(['day_30', 'day_60', 'day_90'], $milestones);

        $day30 = $checkins->firstWhere('milestone', 'day_30');
        $this->assertEquals(Carbon::parse($startDate)->addDays(30)->toDateString(), $day30->scheduled_date->toDateString());
        $this->assertEquals('pending', $day30->status);
    }

    /**
     * Test 8: Mentorship milestone check-in completion and pair auto-completion
     */
    public function test_onboarding_buddy_checkin_completion_and_auto_completion_of_mentorship_pair(): void
    {
        $service = app(MentorshipService::class);
        $pair = $service->assignMentor($this->employeeNewHire, $this->employeeSenior, Carbon::today()->toDateString());

        $checkins = $pair->checkins;

        // Complete Day 30
        $service->submitCheckin($checkins[0], [
            'mentee_satisfaction_score' => 5,
            'mentee_feedback' => 'Buddy sangat suportif.',
            'mentor_adaptation_score' => 4,
            'mentor_notes' => 'Adaptasi sangat baik.',
            'action_plan' => 'Lanjut sprint 2.',
        ]);

        $this->assertEquals('completed', $checkins[0]->fresh()->status);
        $this->assertEquals('active', $pair->fresh()->status); // Not completed yet

        // Complete Day 60
        $service->submitCheckin($checkins[1], [
            'mentee_satisfaction_score' => 5,
            'mentor_adaptation_score' => 5,
        ]);

        // Complete Day 90 (Final)
        $service->submitCheckin($checkins[2], [
            'mentee_satisfaction_score' => 5,
            'mentor_adaptation_score' => 5,
        ]);

        $this->assertEquals('completed', $pair->fresh()->status);

        // Verify Summary
        $summary = $service->getMentorshipSummary($this->tenant->id);
        $this->assertGreaterThanOrEqual(1, $summary['total_pairs']);
        $this->assertGreaterThanOrEqual(1, $summary['completed_pairs']);
        $this->assertEquals(5.0, (float) $summary['avg_mentee_satisfaction']);
    }

    /**
     * Test 9: Asset Handover BAST creation and damage compensation calculation
     */
    public function test_asset_handover_bast_creation_and_damage_compensation_due_calculation(): void
    {
        $service = app(ExitAssetClearanceService::class);

        $assetLaptop = CompanyAsset::create([
            'tenant_id' => $this->tenant->id,
            'asset_code' => 'ASSET-P15-MBP',
            'name' => 'MacBook Pro 16 M2',
            'category' => 'laptop',
            'serial_number' => 'C02TEST98124',
            'status' => 'assigned',
            'assigned_employee_id' => $this->employeeSenior->id,
        ]);

        $items = [
            [
                'company_asset_id' => $assetLaptop->id,
                'asset_name' => 'MacBook Pro 16 M2',
                'serial_number' => 'C02TEST98124',
                'condition_on_return' => 'good',
                'compensation_amount' => 0.00,
            ],
            [
                'asset_name' => 'Smart Card ID & Security Badge',
                'serial_number' => 'RFID-P15-091',
                'condition_on_return' => 'lost',
                'compensation_amount' => 150000.00,
                'notes' => 'Kartu akses hilang saat perjalanan dinas.',
            ],
        ];

        $bast = $service->createBast(
            $this->employeeSenior,
            $items,
            'Rian IT Specialist',
            'IT Infrastructure Lead'
        );

        $this->assertInstanceOf(AssetHandoverBast::class, $bast);
        $this->assertEquals('draft', $bast->status);
        $this->assertStringStartsWith('BAST-ASSET-', $bast->bast_number);
        $this->assertCount(2, $bast->items);
        $this->assertEquals(150000.00, (float) $bast->total_compensation_due);
    }

    /**
     * Test 10: Digital signing of BAST updates physical assets and triggers automated SSO de-provisioning
     */
    public function test_asset_handover_digital_signing_updates_inventory_and_triggers_automated_sso_revocation(): void
    {
        $service = app(ExitAssetClearanceService::class);

        $assetLaptop = CompanyAsset::create([
            'tenant_id' => $this->tenant->id,
            'asset_code' => 'ASSET-P15-DEL',
            'name' => 'Dell Latitude 7420',
            'category' => 'laptop',
            'serial_number' => 'DELL-P15-7420',
            'status' => 'assigned',
            'assigned_employee_id' => $this->employeeSenior->id,
        ]);

        $bast = $service->createBast(
            $this->employeeSenior,
            [
                [
                    'company_asset_id' => $assetLaptop->id,
                    'asset_name' => 'Dell Latitude 7420',
                    'serial_number' => 'DELL-P15-7420',
                    'condition_on_return' => 'good',
                    'compensation_amount' => 0.00,
                ],
            ],
            'Rian IT Specialist',
            'IT Lead'
        );

        $signedBast = $service->signAndCompleteBast(
            $bast,
            'TTE-BUDI-SANTOSO-'.md5('budi'),
            'TTE-RIAN-IT-'.md5('rian')
        );

        $this->assertEquals('completed', $signedBast->status);
        $this->assertNotNull($signedBast->signed_at);

        // Verify physical asset inventory returned to available
        $this->assertEquals('available', $assetLaptop->fresh()->status);
        $this->assertNull($assetLaptop->fresh()->assigned_employee_id);

        // Verify automated SSO & system access revocation logs triggered
        $revocationLogs = SystemAccessRevocationLog::where('tenant_id', $this->tenant->id)
            ->where('employee_id', $this->employeeSenior->id)
            ->get();

        $this->assertNotEmpty($revocationLogs);
        $systemNames = $revocationLogs->pluck('system_name')->toArray();
        $this->assertContains('Google Workspace / Corporate Email', $systemNames);
        $this->assertContains('Corporate VPN & Internal Gateway', $systemNames);
    }

    /**
     * Test 11: Disability employment quota audit under UU No. 8/2016 Pasal 53
     */
    public function test_disability_employment_quota_audit_under_uu_8_2016_pasal_53(): void
    {
        $service = app(DisabilityComplianceAuditService::class);

        // Initial state: no disability employees yet
        $auditPrivate = $service->performAudit($this->company, 'private_sector', $this->hrManager->id);

        $this->assertInstanceOf(DisabilityEmploymentAudit::class, $auditPrivate);
        $this->assertEquals(1.00, (float) $auditPrivate->statutory_quota_percentage);
        $this->assertGreaterThanOrEqual(1, $auditPrivate->statutory_headcount_required);
        $this->assertStringStartsWith('DISABILITY-AUDIT-', $auditPrivate->audit_code);

        // Now register employee disability profile
        $profile = $service->registerDisabilityProfile($this->employeeNewHire, [
            'disability_category' => 'physical',
            'medical_certificate_number' => 'SKD-TEST-RSUD-015',
            'accommodations_provided' => 'Meja ergonomis dan akses ramp gedung.',
        ]);

        $this->assertInstanceOf(EmployeeDisabilityProfile::class, $profile);
        $this->assertTrue($this->employeeNewHire->fresh()->has_disability);

        // Re-run audit -> now compliant
        $auditRechecked = $service->performAudit($this->company, 'private_sector', $this->hrManager->id);
        $this->assertGreaterThanOrEqual(1, $auditRechecked->disability_workforce_count);
        $this->assertEquals('compliant', $auditRechecked->compliance_status);
        $this->assertEquals(0, $auditRechecked->headcount_shortfall);

        // Test Disnaker report output
        $reportData = $service->generateDisnakerComplianceReport($auditRechecked);
        $this->assertIsArray($reportData);
        $this->assertStringContainsString('UU No. 8/2016', $reportData['dasar_hukum']);
        $this->assertEquals('COMPLIANT', $reportData['ringkasan_kuota']['status_kepatuhan']);
    }
}
