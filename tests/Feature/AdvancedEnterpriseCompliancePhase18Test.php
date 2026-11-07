<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\FlexBenefitCategory;
use App\Models\FlexBenefitAllocation;
use App\Models\FlexBenefitClaim;
use App\Models\ThrDisbursement;
use App\Models\EsopGrant;
use App\Models\CobInsurancePolicy;
use App\Models\P2k3Committee;
use App\Models\P2k3QuarterlyReport;
use App\Services\Financial\FlexBenefitService;
use App\Services\Payroll\ThrCalculationService;
use App\Services\Financial\EsopVestingService;
use App\Services\Compliance\CobInsuranceService;
use App\Services\Compliance\P2k3DisnakerService;
use Carbon\Carbon;
use InvalidArgumentException;

class AdvancedEnterpriseCompliancePhase18Test extends TestCase
{
    protected Tenant $tenant;
    protected Company $company;
    protected Department $techDept;
    protected Position $devPos;
    protected User $hrManager;
    protected Employee $employeeSenior;
    protected Employee $employeeJunior;
    protected Employee $employeeNewbie;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-phase18-enterprise-flex-thr'],
            ['name' => 'PT Test Phase 18 Enterprise Kesejahteraan', 'status' => 'active']
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pt-test-p18-kesejahteraan'],
            [
                'name' => 'PT Test Phase 18 Enterprise Kesejahteraan',
                'legal_name' => 'PT Test Phase 18 Enterprise Kesejahteraan Tbk',
                'npwp' => '01.234.567.8-018.000',
                'address' => 'One Pacific Place Lt. 18, SCBD',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12190',
                'phone' => '+62 21 5566 7788',
                'email' => 'compliance@p18kesejahteraan.test',
                'industry' => 'Technology & Human Capital Solutions',
            ]
        );

        $this->techDept = Department::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Engineering & Infrastructure']
        );

        $this->devPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Staff Specialist']
        );

        $this->hrManager = User::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'hrmanager.p18@p18kesejahteraan.test'],
            [
                'name' => 'Kusuma Wardani, S.Psi, CHRP',
                'password' => bcrypt('Password123!'),
                'is_active' => true,
            ]
        );

        // Senior employee (Tenure: 24 months, Basic Salary: Rp 16.000.000)
        $this->employeeSenior = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'nik' => 'P18-EMP-001'],
            [
                'first_name' => 'Arya',
                'last_name' => 'Wicaksana',
                'email' => 'arya.wicaksana@p18kesejahteraan.test',
                'department_id' => $this->techDept->id,
                'position_id' => $this->devPos->id,
                'join_date' => Carbon::now()->subMonths(24)->toDateString(),
                'employment_type' => 'permanent',
                'basic_salary' => 16000000.00,
                'status' => 'active',
            ]
        );

        // Junior employee (Tenure: 6 months, Basic Salary: Rp 10.000.000)
        $this->employeeJunior = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'nik' => 'P18-EMP-002'],
            [
                'first_name' => 'Siti',
                'last_name' => 'Nurhaliza',
                'email' => 'siti.nurhaliza@p18kesejahteraan.test',
                'department_id' => $this->techDept->id,
                'position_id' => $this->devPos->id,
                'join_date' => Carbon::now()->subMonths(6)->toDateString(),
                'employment_type' => 'contract',
                'basic_salary' => 10000000.00,
                'status' => 'active',
            ]
        );

        // Newbie employee (Tenure: 15 days, Basic Salary: Rp 8.000.000)
        $this->employeeNewbie = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'nik' => 'P18-EMP-003'],
            [
                'first_name' => 'Rian',
                'last_name' => 'Kurnia',
                'email' => 'rian.kurnia@p18kesejahteraan.test',
                'department_id' => $this->techDept->id,
                'position_id' => $this->devPos->id,
                'join_date' => Carbon::now()->subDays(15)->toDateString(),
                'employment_type' => 'probation',
                'basic_salary' => 8000000.00,
                'status' => 'active',
            ]
        );
    }

    /**
     * 1. Test Flex Benefit Allocation and Claim Deduction Lifecycle.
     */
    public function test_flex_benefit_allocation_and_point_deduction_lifecycle(): void
    {
        $service = new FlexBenefitService();

        $category = FlexBenefitCategory::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Kacamata & Optik',
            'code' => 'optical_care',
            'is_active' => true,
        ]);

        $allocation = $service->allocatePoints(
            $this->tenant->id,
            $this->company->id,
            $this->employeeSenior->id,
            2026,
            5000000.00
        );

        $this->assertEquals(5000000.00, (float) $allocation->total_points_allocated);
        $this->assertEquals(5000000.00, (float) $allocation->points_remaining);
        $this->assertEquals(0.00, (float) $allocation->points_used);

        $claim = $service->submitClaim(
            $allocation,
            $category->id,
            1500000.00,
            Carbon::now()->toDateString(),
            'https://storage.hris.test/receipts/glasses.pdf',
            'Kacamata lensa minus dan silinder'
        );

        $this->assertEquals('submitted', $claim->status);
        $this->assertEquals(1500000.00, (float) $claim->claim_amount);

        $service->approveClaim($claim, $this->hrManager->id);

        $allocation->refresh();
        $this->assertEquals('approved', $claim->status);
        $this->assertEquals(1500000.00, (float) $allocation->points_used);
        $this->assertEquals(3500000.00, (float) $allocation->points_remaining);
    }

    /**
     * 2. Test Flex Benefit Rejects Claim Exceeding Available Points.
     */
    public function test_flex_benefit_claim_rejects_when_points_insufficient(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $service = new FlexBenefitService();
        $category = FlexBenefitCategory::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Dental Care',
            'code' => 'dental_care',
            'is_active' => true,
        ]);

        $allocation = $service->allocatePoints(
            $this->tenant->id,
            $this->company->id,
            $this->employeeJunior->id,
            2026,
            2000000.00
        );

        // Attempting to claim Rp 3.000.000 with remaining 2.000.000
        $service->submitClaim(
            $allocation,
            $category->id,
            3000000.00,
            Carbon::now()->toDateString()
        );
    }

    /**
     * 3. Test Statutory THR Full 1 Month Wage for Tenure >= 12 Months (Permenaker 6/2016 Pasal 3).
     */
    public function test_statutory_thr_full_one_month_wage_for_tenure_twelve_months_or_more(): void
    {
        $service = new ThrCalculationService();

        $holidayDate = Carbon::parse('2026-03-21'); // Idul Fitri 2026
        $paymentDate = Carbon::parse('2026-03-13'); // H-8 (On-time, before H-7 deadline 2026-03-14)

        $result = $service->computeStatutoryThr(
            $this->employeeSenior,
            2026,
            'idul_fitri',
            $holidayDate,
            $paymentDate
        );

        $this->assertTrue($result['is_eligible']);
        $this->assertEquals(1.0000, $result['prorata_multiplier']);
        $this->assertEquals(16000000.00, $result['calculated_thr_amount']);
        $this->assertFalse($result['is_late']);
        $this->assertEquals(0.00, $result['late_penalty_interest']);
        $this->assertGreaterThan(0.00, $result['ter_tax_deduction']);
    }

    /**
     * 4. Test Statutory THR Pro-rata Calculation for Tenure Between 1 and 11 Months.
     */
    public function test_statutory_thr_prorata_calculation_for_tenure_between_one_and_eleven_months(): void
    {
        $service = new ThrCalculationService();

        $holidayDate = Carbon::parse('2026-03-21');
        $paymentDate = Carbon::parse('2026-03-14'); // Exactly H-7 deadline

        $result = $service->computeStatutoryThr(
            $this->employeeJunior,
            2026,
            'idul_fitri',
            $holidayDate,
            $paymentDate
        );

        $this->assertTrue($result['is_eligible']);
        $this->assertGreaterThanOrEqual(5, $result['tenure_months']);
        $this->assertLessThan(12, $result['tenure_months']);
        $this->assertLessThan(1.0000, $result['prorata_multiplier']);

        // Prorata calculation: (tenure / 12) * wage
        $expectedAmount = round(($result['tenure_months'] / 12) * 10000000.00, 2);
        $this->assertEquals($expectedAmount, $result['calculated_thr_amount']);
        $this->assertFalse($result['is_late']);
    }

    /**
     * 5. Test Statutory THR Ineligible for Tenure Under 1 Month (Permenaker 6/2016 Pasal 2).
     */
    public function test_statutory_thr_ineligible_for_tenure_less_than_one_month(): void
    {
        $service = new ThrCalculationService();

        $holidayDate = Carbon::parse('2026-03-21');

        $result = $service->computeStatutoryThr(
            $this->employeeNewbie,
            2026,
            'idul_fitri',
            $holidayDate
        );

        $this->assertFalse($result['is_eligible']);
        $this->assertEquals(0.00, $result['calculated_thr_amount']);
        $this->assertEquals(0.00, $result['net_thr_paid']);
    }

    /**
     * 6. Test Statutory THR 5% Late Payment Penalty under Pasal 10 Permenaker 6/2016.
     */
    public function test_statutory_thr_late_payment_penalty_five_percent_under_pasal_10_permenaker_6_2016(): void
    {
        $service = new ThrCalculationService();

        $holidayDate = Carbon::parse('2026-03-21');
        // Deadline is H-7 (2026-03-14). Paying on 2026-03-18 is late!
        $latePaymentDate = Carbon::parse('2026-03-18');

        $result = $service->computeStatutoryThr(
            $this->employeeSenior,
            2026,
            'idul_fitri',
            $holidayDate,
            $latePaymentDate
        );

        $this->assertTrue($result['is_late']);
        $expectedPenalty = round(16000000.00 * 0.05, 2); // 5% dari 16 juta = Rp 800.000
        $this->assertEquals(800000.00, $result['late_penalty_interest']);
        $this->assertEquals($expectedPenalty, $result['late_penalty_interest']);
    }

    /**
     * 7. Test Statutory THR Disbursement Record Persistence.
     */
    public function test_statutory_thr_disbursement_record_and_status_update(): void
    {
        $service = new ThrCalculationService();

        $holidayDate = Carbon::parse('2026-03-21');
        $paymentDate = Carbon::parse('2026-03-12');

        $disbursement = $service->recordThrDisbursement(
            $this->employeeSenior,
            2026,
            'idul_fitri',
            $holidayDate,
            $paymentDate,
            'calculated'
        );

        $this->assertNotNull($disbursement->id);
        $this->assertEquals(16000000.00, (float) $disbursement->calculated_thr_amount);
        $this->assertEquals('calculated', $disbursement->status);

        $disbursement->update(['status' => 'paid']);
        $this->assertEquals('paid', $disbursement->fresh()->status);
    }

    /**
     * 8. Test ESOP Vesting Cliff Enforcement Under 1 Year (UU No. 40/2007).
     */
    public function test_esop_vesting_cliff_enforcement_under_one_year(): void
    {
        $service = new EsopVestingService();

        $grantDate = Carbon::now()->subMonths(6); // 6 months ago (before 12 months cliff)

        $grant = $service->issueGrant(
            $this->employeeSenior,
            24000,
            1000.00,
            4000.00,
            $grantDate,
            4,
            12
        );

        $service->updateVestingProgress($grant, Carbon::now());
        $this->assertEquals(0, $grant->fresh()->vested_shares_count);

        // Advance simulated date to 24 months after grant date (50% through 48-month schedule)
        $futureDate = $grantDate->copy()->addMonths(24);
        $service->updateVestingProgress($grant, $futureDate);

        $grant->refresh();
        $this->assertEquals(12000, $grant->vested_shares_count); // 50% of 24,000 = 12,000 shares
    }

    /**
     * 9. Test ESOP Option Exercise and Capital Gain Calculation.
     */
    public function test_esop_option_exercise_and_capital_gain_calculation(): void
    {
        $service = new EsopVestingService();

        $grantDate = Carbon::now()->subMonths(24);
        $grant = $service->issueGrant(
            $this->employeeSenior,
            20000,
            1500.00, // Strike Price
            5000.00, // Fair Market Value
            $grantDate,
            4,
            12
        );

        $service->updateVestingProgress($grant, Carbon::now());
        $grant->refresh();
        $this->assertGreaterThan(0, $grant->vested_shares_count);

        $exerciseResult = $service->exerciseOptions($grant, 2000);

        $this->assertEquals(2000, $exerciseResult['shares_exercised']);
        $this->assertEquals(3000000.00, $exerciseResult['total_exercise_cost']); // 2000 * 1500
        $this->assertEquals(10000000.00, $exerciseResult['total_fair_value']); // 2000 * 5000
        $this->assertEquals(7000000.00, $exerciseResult['intrinsic_gain']); // 10jt - 3jt = 7jt gain
        $this->assertEquals(2000, $grant->fresh()->exercised_shares_count);
    }

    /**
     * 10. Test CoB Insurance BPJS Kesehatan First Payer and Commercial Top-Up.
     */
    public function test_cob_insurance_bpjs_kesehatan_first_payer_and_commercial_topup(): void
    {
        $service = new CobInsuranceService();

        $policy = $service->registerPolicy(
            $this->employeeSenior,
            'PT Asuransi Allianz Life Indonesia',
            'POL-ALLZ-99182',
            '0001992817261',
            'Platinum Executive',
            100000000.00
        );

        $this->assertEquals(100000000.00, (float) $policy->annual_limit_amount);
        $this->assertEquals(0.00, (float) $policy->current_usage_amount);

        // Hospital Bill: Rp 25.000.000, BPJS INA-CBGs covers: Rp 15.000.000
        // Excess: Rp 10.000.000 (Commercial policy should cover fully, leaving 0 patient copay)
        $claimResult = $service->coordinateClaim($policy, 25000000.00, 15000000.00, true);

        $this->assertEquals(25000000.00, $claimResult['total_hospital_bill']);
        $this->assertEquals(15000000.00, $claimResult['bpjs_first_payer_paid']);
        $this->assertEquals(10000000.00, $claimResult['excess_cost_sharing']);
        $this->assertEquals(10000000.00, $claimResult['commercial_topup_paid']);
        $this->assertEquals(0.00, $claimResult['patient_copay_amount']);
        $this->assertEquals('fully_covered', $claimResult['cob_status']);

        $policy->refresh();
        $this->assertEquals(10000000.00, (float) $policy->current_usage_amount);
        $this->assertEquals(90000000.00, (float) $policy->annual_limit_amount - (float) $policy->current_usage_amount);
    }

    /**
     * 11. Test P2K3 Mandatory Compliance Audit Workforce Threshold (Permenaker 04/1987).
     */
    public function test_p2k3_mandatory_compliance_audit_workforce_threshold(): void
    {
        $service = new P2k3DisnakerService();

        // 120 employees: Mandatory
        $resultMandatory = $service->assessMandatoryCompliance($this->company, 120, false);
        $this->assertTrue($resultMandatory['is_p2k3_mandatory']);

        // 60 employees low risk: Non-mandatory
        $resultOptional = $service->assessMandatoryCompliance($this->company, 60, false);
        $this->assertFalse($resultOptional['is_p2k3_mandatory']);

        // 40 employees high risk: Mandatory
        $resultHighRisk = $service->assessMandatoryCompliance($this->company, 40, true);
        $this->assertTrue($resultHighRisk['is_p2k3_mandatory']);
    }

    /**
     * 12. Test P2K3 Committee Registration Requires Valid Ahli K3 SKP and Generates Quarterly Report.
     */
    public function test_p2k3_committee_registration_requires_valid_ahli_k3_skp_and_quarterly_reporting(): void
    {
        $service = new P2k3DisnakerService();

        // Failure case: Empty SKP
        try {
            $service->registerCommittee(
                $this->company,
                'SK-P2K3/2026/001',
                'Direktur Utama',
                'Sekretaris Non-Certified',
                '', // empty SKP
                Carbon::now()
            );
            $this->fail('Should throw InvalidArgumentException when SKP is empty');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('SKP Ahli K3 Umum', $e->getMessage());
        }

        // Success case: Valid certified Ahli K3 SKP
        $committee = $service->registerCommittee(
            $this->company,
            'SK-P2K3/2026/001',
            'Hendra Wijaya (Direktur Operasional)',
            'Ir. Joko Prasetyo, ST, K3',
            'SKP.8821/NAKER-BINWASK3/2024',
            Carbon::now(),
            'DISNAKER-DKI/P2K3/2026/001'
        );

        $this->assertEquals('SKP.8821/NAKER-BINWASK3/2024', $committee->ahli_k3_skp_number);
        $this->assertEquals('active', $committee->status);

        $report = $service->submitQuarterlyReport(
            $committee,
            2026,
            'Q1',
            4,
            3,
            'Zero accident di tempat kerja.',
            'DISNAKER-DKI/P2K3/2026/REC-0918'
        );

        $this->assertEquals('submitted_disnaker', $report->status);
        $this->assertEquals('DISNAKER-DKI/P2K3/2026/REC-0918', $report->disnaker_receipt_number);
        $this->assertEquals(4, $report->workplace_inspections_count);
    }
}
