<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\ExpatriatePermit;
use App\Models\JkpClaimCertification;
use App\Models\EmployeeCooperative;
use App\Models\CooperativeTransaction;
use App\Models\ContinuousShiftRoster;
use App\Models\EmployeeShiftRosterAssignment;
use App\Models\TripartiteIndustrialDispute;
use App\Services\Compliance\ExpatriatePermitService;
use App\Services\Compliance\JkpClaimService;
use App\Services\Financial\EmployeeCooperativeService;
use App\Services\Attendance\ContinuousShiftRosterService;
use App\Services\Relations\IndustrialDisputeService;
use Carbon\Carbon;
use InvalidArgumentException;

class AdvancedEnterpriseCompliancePhase16Test extends TestCase
{
    protected Tenant $tenant;
    protected Company $company;
    protected Department $techDept;
    protected Position $archPos;
    protected Position $hrPos;
    protected User $hrManager;
    protected Employee $employeeWni;
    protected Employee $employeeTka;
    protected Employee $employeeTerminated;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-phase16-enterprise-compliance'],
            ['name' => 'PT Test Phase 16 Enterprise Nusantara', 'status' => 'active']
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pt-test-p16-compliance'],
            [
                'name' => 'PT Test Phase 16 Enterprise Nusantara',
                'legal_name' => 'PT Test Phase 16 Enterprise Nusantara Tbk',
                'npwp' => '01.234.567.8-016.000',
                'address' => 'Jl. HR Rasuna Said Blok X-5 Kav. 16',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'status' => 'active',
            ]
        );

        $this->techDept = Department::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Technology & Operations'],
            ['code' => 'TECH-OPS']
        );

        $this->archPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'department_id' => $this->techDept->id, 'title' => 'Chief Solution Architect'],
            ['code' => 'CHIEF-ARCH']
        );

        $this->hrPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'department_id' => $this->techDept->id, 'title' => 'Human Resources Director'],
            ['code' => 'HR-DIR']
        );

        $this->hrManager = User::firstOrCreate(
            ['email' => 'hrmanager.p16@testphase16.com'],
            [
                'name' => 'Hendro HR Compliance Lead',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
                'is_super_admin' => false,
            ]
        );

        // WNI Companion employee
        $this->employeeWni = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'budi.santoso@testphase16.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P16-WNI-001',
                'nik_ktp' => '3171010101900016',
                'first_name' => 'Budi',
                'last_name' => 'Santoso',
                'gender' => 'male',
                'citizenship' => 'WNI',
                'department_id' => $this->techDept->id,
                'position_id' => $this->archPos->id,
                'employment_status' => 'pkwtt',
                'join_date' => Carbon::parse('2022-01-10'),
                'basic_salary' => 15000000.00,
                'status' => 'active',
            ]
        );

        // Expatriate (TKA) employee
        $this->employeeTka = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'john.smith@testphase16.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P16-TKA-001',
                'nik_ktp' => '9999010101850016',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'gender' => 'male',
                'citizenship' => 'WNA',
                'department_id' => $this->techDept->id,
                'position_id' => $this->archPos->id,
                'employment_status' => 'pkwt',
                'join_date' => Carbon::parse('2025-01-01'),
                'basic_salary' => 45000000.00,
                'status' => 'active',
            ]
        );

        // Terminated employee for JKP
        $this->employeeTerminated = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'agus.setiawan@testphase16.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P16-TERM-001',
                'nik_ktp' => '3171010101920016',
                'first_name' => 'Agus',
                'last_name' => 'Setiawan',
                'gender' => 'male',
                'citizenship' => 'WNI',
                'department_id' => $this->techDept->id,
                'position_id' => $this->archPos->id,
                'employment_status' => 'pkwtt',
                'join_date' => Carbon::parse('2023-01-01'),
                'basic_salary' => 8500000.00,
                'status' => 'inactive',
            ]
        );
    }

    /**
     * Test 1: Expatriate permit registration with RPTKA, companion, and DKPTKA under PP 34/2021
     */
    public function test_expatriate_permit_registration_under_pp_34_2021(): void
    {
        $service = app(ExpatriatePermitService::class);

        $permit = $service->registerPermit($this->company, $this->employeeTka, [
            'rptka_number' => 'RPTKA/TEST/2026/001',
            'rptka_approval_date' => '2026-01-01',
            'rptka_expiry_date' => Carbon::now()->addMonths(12)->toDateString(),
            'job_position_title' => 'Principal Systems Architect & Technical Advisor',
            'passport_number' => 'GBR-TEST-9921',
            'passport_expiry_date' => Carbon::now()->addYears(3)->toDateString(),
            'itas_vitas_number' => 'ITAS-2C21-TEST-01',
            'itas_expiry_date' => Carbon::now()->addMonths(12)->toDateString(),
            'companion_employee_id' => $this->employeeWni->id,
            'technology_transfer_plan' => 'Mentoring WNI engineer on distributed databases and cloud orchestration.',
            'dkptka_payment_proof' => 'NTPN-TEST-2026-991',
        ]);

        $this->assertInstanceOf(ExpatriatePermit::class, $permit);
        $this->assertEquals(100.00, (float) $permit->dkptka_monthly_amount_usd);
        $this->assertEquals($this->employeeWni->id, $permit->companion_employee_id);
        $this->assertEquals('active', $permit->status);
    }

    /**
     * Test 2: TKA permit registration strictly blocks HR/personnel positions under Pasal 46 UU 13/2003
     */
    public function test_expatriate_permit_blocks_hr_positions_under_pasal_46_uu_13_2003(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TKA dilarang menduduki jabatan yang membidangi personalia');

        $service = app(ExpatriatePermitService::class);

        $service->registerPermit($this->company, $this->employeeTka, [
            'rptka_number' => 'RPTKA/TEST/FAIL/002',
            'rptka_approval_date' => '2026-01-01',
            'rptka_expiry_date' => Carbon::now()->addMonths(12)->toDateString(),
            'job_position_title' => 'Chief Human Resources Officer & Personnel Head', // Prohibited!
            'passport_number' => 'GBR-TEST-9922',
            'passport_expiry_date' => Carbon::now()->addYears(3)->toDateString(),
        ]);
    }

    /**
     * Test 3: Expatriate permit expiration detection updates status to expiring_soon
     */
    public function test_expatriate_permit_expiry_detection(): void
    {
        $service = app(ExpatriatePermitService::class);

        $expiringPermit = ExpatriatePermit::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_id' => $this->employeeTka->id,
            'rptka_number' => 'RPTKA/TEST/EXP/003',
            'rptka_approval_date' => Carbon::now()->subMonths(11)->toDateString(),
            'rptka_expiry_date' => Carbon::now()->addDays(20)->toDateString(), // within 60 days
            'job_position_title' => 'Senior Technical Consultant',
            'passport_number' => 'GBR-TEST-9923',
            'passport_expiry_date' => Carbon::now()->addYears(2)->toDateString(),
            'dkptka_monthly_amount_usd' => 100.00,
            'status' => 'active',
        ]);

        $expiringCount = $service->checkExpiringPermits(60);
        $this->assertGreaterThanOrEqual(1, $expiringCount);

        $expiringPermit->refresh();
        $this->assertEquals('expiring_soon', $expiringPermit->status);

        // Test Disnaker report payload
        $reportPayload = $service->generateKemnakerReportPayload($this->company);
        $this->assertIsArray($reportPayload);
        $this->assertArrayHasKey('total_tka', $reportPayload);
        $this->assertArrayHasKey('tka_list', $reportPayload);
    }

    /**
     * Test 4: JKP claim calculation under PP 37/2021 with Rp 5.000.000 salary cap and 45%/25% benefits
     */
    public function test_jkp_claim_benefit_calculation_under_pp_37_2021(): void
    {
        $service = app(JkpClaimService::class);

        $jkp = $service->certifyClaim($this->company, $this->employeeTerminated, [
            'termination_date' => Carbon::now()->subDays(5)->toDateString(),
            'termination_reason_code' => 'efisiensi_merger',
            'bpjs_tk_card_number' => '18029384799',
            'consecutive_paid_months' => 8,
            'total_paid_months_24m' => 18,
        ]);

        $this->assertInstanceOf(JkpClaimCertification::class, $jkp);
        $this->assertTrue($jkp->eligible_for_jkp);
        $this->assertEquals(5000000.00, (float) $jkp->reference_salary_cap);

        // Month 1-3 is 45% x min(8.5jt, 5jt) = 45% x 5jt = Rp 2.250.000
        $this->assertEquals(2250000.00, (float) $jkp->month_1_to_3_benefit_monthly);

        // Month 4-6 is 25% x min(8.5jt, 5jt) = 25% x 5jt = Rp 1.250.000
        $this->assertEquals(1250000.00, (float) $jkp->month_4_to_6_benefit_monthly);

        // Total cash benefit: (2.250.000 * 3) + (1.250.000 * 3) = 6.750.000 + 3.750.000 = Rp 10.500.000
        $this->assertEquals(10500000.00, (float) $jkp->total_potential_cash_benefit);
        $this->assertStringStartsWith('JKP-CERT/', $jkp->certificate_number);
    }

    /**
     * Test 5: JKP claim strictly rejects voluntary resignations under Pasal 19 ayat 3 PP 37/2021
     */
    public function test_jkp_claim_rejects_voluntary_resignation_under_pasal_19_pp_37_2021(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('tidak berhak atas manfaat JKP');

        $service = app(JkpClaimService::class);

        $service->certifyClaim($this->company, $this->employeeTerminated, [
            'termination_date' => Carbon::now()->subDays(5)->toDateString(),
            'termination_reason_code' => 'resignation', // Ineligible under PP 37/2021
            'bpjs_tk_card_number' => '18029384799',
            'consecutive_paid_months' => 12,
            'total_paid_months_24m' => 24,
        ]);
    }

    /**
     * Test 6: JKP reporting payload generation for Disnaker & SiapKerja Kemnaker
     */
    public function test_jkp_disnaker_reporting_payload_generation(): void
    {
        $service = app(JkpClaimService::class);

        $jkp = JkpClaimCertification::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'certificate_number' => 'JKP-CERT/TEST/005',
            'employee_id' => $this->employeeTerminated->id,
            'termination_date' => Carbon::now()->subDays(10)->toDateString(),
            'termination_reason_code' => 'perusahaan_pailit',
            'bpjs_tk_card_number' => '18029384799',
            'consecutive_paid_months' => 6,
            'total_paid_months_24m' => 14,
            'eligible_for_jkp' => true,
            'reference_salary_cap' => 5000000.00,
            'month_1_to_3_benefit_monthly' => 2250000.00,
            'month_4_to_6_benefit_monthly' => 1250000.00,
            'total_potential_cash_benefit' => 10500000.00,
            'disnaker_reporting_status' => 'reported',
            'siapkerja_tracking_code' => 'SK-TEST-9921',
        ]);

        $payload = $service->generateDisnakerReportingPayload($jkp);

        $this->assertIsArray($payload);
        $this->assertEquals('PP No. 37 Tahun 2021', $payload['dasar_hukum']);
        $this->assertEquals($jkp->certificate_number, $payload['nomor_sertifikat']);
        $this->assertEquals(10500000.00, $payload['manfaat_tunai']['total_manfaat_maksimal']);
    }

    /**
     * Test 7: Employee cooperative registration and principal savings under UU 25/1992
     */
    public function test_employee_cooperative_registration_and_principal_savings(): void
    {
        $service = app(EmployeeCooperativeService::class);

        $member = $service->registerMember($this->company, $this->employeeWni, [
            'mandatory_savings' => 100000.00,
            'voluntary_savings' => 50000.00,
            'principal_savings_paid' => 500000.00,
        ]);

        $this->assertInstanceOf(EmployeeCooperative::class, $member);
        $this->assertEquals(500000.00, (float) $member->total_accumulated_savings);
        $this->assertEquals(0.00, (float) $member->active_loan_balance);
        $this->assertEquals('active', $member->status);

        // Verify principal saving transaction was recorded
        $txn = CooperativeTransaction::where('employee_cooperative_id', $member->id)
            ->where('transaction_type', 'principal_saving')
            ->first();
        $this->assertNotNull($txn);
        $this->assertEquals(500000.00, (float) $txn->amount);
    }

    /**
     * Test 8: Cooperative monthly payroll deduction and savings accumulation
     */
    public function test_cooperative_monthly_payroll_deduction_and_savings_accumulation(): void
    {
        $service = app(EmployeeCooperativeService::class);

        $member = EmployeeCooperative::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_id' => $this->employeeWni->id,
            'member_number' => 'KOPKAR-TEST-008',
            'join_date' => Carbon::now()->subMonths(3)->toDateString(),
            'status' => 'active',
            'mandatory_savings' => 100000.00,
            'voluntary_savings' => 50000.00,
            'principal_savings_paid' => 500000.00,
            'total_accumulated_savings' => 500000.00,
            'active_loan_balance' => 0.00,
        ]);

        $transactions = $service->processMonthlyDeduction($member);

        $this->assertCount(2, $transactions); // 1 mandatory + 1 voluntary
        $member->refresh();

        // 500.000 + 100.000 + 50.000 = 650.000
        $this->assertEquals(650000.00, (float) $member->total_accumulated_savings);
    }

    /**
     * Test 9: Cooperative loan disbursement and repayment updates balance correctly
     */
    public function test_cooperative_loan_disbursement_and_repayment_balance(): void
    {
        $service = app(EmployeeCooperativeService::class);

        $member = EmployeeCooperative::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_id' => $this->employeeWni->id,
            'member_number' => 'KOPKAR-TEST-009',
            'join_date' => Carbon::now()->subMonths(6)->toDateString(),
            'status' => 'active',
            'mandatory_savings' => 50000.00,
            'voluntary_savings' => 0.00,
            'principal_savings_paid' => 500000.00,
            'total_accumulated_savings' => 800000.00,
            'active_loan_balance' => 0.00,
        ]);

        // Disburse loan Rp 3.000.000
        $disburseTxn = $service->recordTransaction(
            $member,
            'loan_disbursement',
            3000000.00,
            Carbon::now()->subMonth()->toDateString(),
            false,
            'Pinjaman Multiguna'
        );
        $this->assertInstanceOf(CooperativeTransaction::class, $disburseTxn);
        $member->refresh();
        $this->assertEquals(3000000.00, (float) $member->active_loan_balance);

        // Repay installment Rp 500.000
        $repayTxn = $service->recordTransaction(
            $member,
            'loan_repayment',
            500000.00,
            Carbon::now()->toDateString(),
            true,
            'Angsuran 1 via payroll'
        );
        $this->assertInstanceOf(CooperativeTransaction::class, $repayTxn);
        $member->refresh();
        $this->assertEquals(2500000.00, (float) $member->active_loan_balance);

        // Summary check
        $summary = $service->getMemberFinancialSummary($member);
        $this->assertEquals(800000.00, $summary['total_accumulated_savings']);
        $this->assertEquals(2500000.00, $summary['active_loan_balance']);
    }

    /**
     * Test 10: Continuous shift roster creation and night shift/standby allowance calculation
     */
    public function test_continuous_shift_roster_allowances_under_kepmenaker_233_2003(): void
    {
        $service = app(ContinuousShiftRosterService::class);

        $roster = $service->createRoster($this->company, [
            'name' => 'Pola 3 Shift 4 Grup Sektor 24/7 Ops',
            'department_id' => $this->techDept->id,
            'cycle_days' => 28,
            'work_days_per_cycle' => 21,
            'off_days_per_cycle' => 7,
            'night_shift_allowance_rate' => 60000.00,
            'standby_allowance_rate' => 80000.00,
            'rotation_pattern_json' => ['shifts' => ['Pagi', 'Sore', 'Malam', 'Off']],
        ]);

        $this->assertInstanceOf(ContinuousShiftRoster::class, $roster);
        $this->assertEquals(60000.00, (float) $roster->night_shift_allowance_rate);
        $this->assertEquals(80000.00, (float) $roster->standby_allowance_rate);

        // Assign employee to Group A
        $assignment = $service->assignEmployeeToGroup($roster, $this->employeeWni, 'Grup A', Carbon::now()->startOfMonth()->toDateString());
        $this->assertInstanceOf(EmployeeShiftRosterAssignment::class, $assignment);
        $this->assertEquals('Grup A', $assignment->group_name);
        $this->assertEquals('active', $assignment->status);

        // Calculate allowances: 7 night shifts and 3 standby days
        $allowances = $service->calculateShiftAllowances($roster, 7, 3);
        $this->assertEquals(420000.00, $allowances['night_shift_total']); // 7 * 60.000
        $this->assertEquals(240000.00, $allowances['standby_total']); // 3 * 80.000
        $this->assertEquals(660000.00, $allowances['grand_total_allowance']);
    }

    /**
     * Test 11: PPHI industrial dispute mediation and joint agreement under UU 2/2004
     */
    public function test_tripartite_industrial_dispute_and_perjanjian_bersama_under_uu_2_2004(): void
    {
        $service = app(IndustrialDisputeService::class);

        // Record initial dispute
        $dispute = $service->recordDispute($this->company, [
            'dispute_type' => 'perselisihan_phk',
            'employee_id' => $this->employeeTerminated->id,
            'disnaker_mediator_name' => 'Dra. Siti Nurhaliza, M.H.',
            'disnaker_office' => 'Sudin Tenaga Kerja Jakarta Selatan',
            'mediation_date' => Carbon::now()->subDays(3)->toDateString(),
            'dispute_chronology' => 'Perbedaan perhitungan kompensasi efisiensi organisasi.',
        ]);

        $this->assertInstanceOf(TripartiteIndustrialDispute::class, $dispute);
        $this->assertEquals('mediation_stage', $dispute->settlement_status);

        // Record mediator's written recommendation
        $disputeWithRec = $service->recordMediatorRecommendation(
            $dispute,
            'Mediator menganjurkan agar perusahaan memberikan tambahan pesangon Rp 15.000.000 dan pekerja menerima pengakhiran kerja.'
        );
        $this->assertNotNull($disputeWithRec->mediator_written_recommendation);

        // Both parties accept -> Perjanjian Bersama (PB)
        $settledDispute = $service->respondToRecommendation(
            $disputeWithRec,
            'accepted',
            'accepted',
            'PB-PHI-PN-JKT-PST/2026/088'
        );

        $this->assertEquals('peaceful_agreement_pb', $settledDispute->settlement_status);
        $this->assertEquals('PB-PHI-PN-JKT-PST/2026/088', $settledDispute->joint_agreement_number);

        // Generate PPHI docket
        $docket = $service->generateDisputeFilingDocket($settledDispute);
        $this->assertIsArray($docket);
        $this->assertEquals('UU No. 2 Tahun 2004 tentang PPHI', $docket['dasar_hukum']);
        $this->assertEquals('PERDAMAIAN TERCAPAI (PERJANJIAN BERSAMA / PB)', $docket['status_penyelesaian']);
    }

    /**
     * Test 12: PPHI rejection leads to escalation to PHI Court under UU 2/2004 Pasal 14
     */
    public function test_tripartite_dispute_rejection_escalates_to_phi_court(): void
    {
        $service = app(IndustrialDisputeService::class);

        $dispute = TripartiteIndustrialDispute::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'case_number' => 'PHI-CASE/TEST/012',
            'dispute_type' => 'perselisihan_hak',
            'employee_name_external' => 'Federasi Serikat Pekerja',
            'disnaker_mediator_name' => 'Bambang Irawan, S.H.',
            'disnaker_office' => 'Disnakertrans DKI Jakarta',
            'mediation_date' => Carbon::now()->subDays(7)->toDateString(),
            'dispute_chronology' => 'Tuntutan bonus tahunan di luar kesepakatan PKB.',
            'mediator_written_recommendation' => 'Anjuran tertulis: pengusaha membayarkan 50% bonus.',
            'settlement_status' => 'mediation_stage',
        ]);

        // Employer rejects the recommendation
        $escalated = $service->respondToRecommendation(
            $dispute,
            'rejected',
            'accepted'
        );

        $this->assertEquals('escalated_to_phi_court', $escalated->settlement_status);
        $this->assertNull($escalated->joint_agreement_number);
    }
}
