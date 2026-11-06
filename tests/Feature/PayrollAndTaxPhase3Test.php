<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalaryComponent;
use App\Models\OvertimeRequest;
use App\Models\Reimbursement;
use App\Models\EmploymentContract;
use App\Models\ThrRecord;
use App\Models\TaxReconciliation;
use App\Services\Payroll\PayrollProcessorService;
use App\Services\Compliance\ThrCalculator;
use App\Services\Compliance\TaxReconciliationService;
use App\Services\Reimbursement\ReimbursementService;
use App\Services\Contract\ContractService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PayrollAndTaxPhase3Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $hrUser;
    protected User $empUser;
    protected Employee $employee;
    protected Department $department;
    protected Position $position;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // 1. Setup Tenant & Company
        $this->tenant = Tenant::create([
            'name' => 'PT Prima Solusi Nusantara',
            'slug' => 'prima-solusi',
            'status' => 'active',
        ]);

        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Prima Solusi Nusantara',
            'email' => 'admin@primasolusi.id',
            'npwp' => '01.999.888.7-012.000',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Setup HR Admin
        $this->hrUser = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@primasolusi.id',
            'password' => bcrypt('password'),
            'active_tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $ownerRole = Role::where('slug', 'company-owner')->first();
        $this->hrUser->tenants()->attach($this->tenant->id, ['role_id' => $ownerRole?->id, 'is_owner' => true]);
        $this->hrUser->assignRole($ownerRole, $this->tenant->id);

        // 3. Setup Department & Position
        $this->department = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $this->position = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'Senior Backend Engineer',
            'job_level' => 'Senior',
        ]);

        // 4. Setup Employee and User
        $this->empUser = User::create([
            'name' => 'Bambang Triatmojo',
            'email' => 'bambang@primasolusi.id',
            'password' => bcrypt('password'),
            'active_tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $empRole = Role::where('slug', 'employee')->first();
        $this->empUser->tenants()->attach($this->tenant->id, ['role_id' => $empRole?->id, 'is_owner' => false]);
        $this->empUser->assignRole($empRole, $this->tenant->id);

        $this->employee = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->empUser->id,
            'employee_number' => 'PSN-2024-001',
            'first_name' => 'Bambang',
            'last_name' => 'Triatmojo',
            'email' => 'bambang@primasolusi.id',
            'gender' => 'male',
            'tax_ptkp_status' => 'K/1', // Maps to TER Category B
            'npwp' => '12.345.678.9-012.000',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'pkwt',
            'join_date' => '2023-01-01',
            'basic_salary' => 15000000.00,
            'status' => 'active',
        ]);
    }

    /**
     * Test 1: Full Payroll Run Calculation including Fixed/Variable Allowances,
     * Approved Overtime (PP 35/2021), BPJS TK/Kes deductions, PPh 21 TER, and Net THP.
     */
    public function test_payroll_run_engine_calculates_statutory_deductions_and_thp(): void
    {
        // 1. Add salary components: Tunjangan Tetap (Rp 2.000.000) & Tunjangan Variabel (Rp 1.000.000)
        $compFixed = SalaryComponent::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tunjangan Jabatan',
            'type' => 'allowance',
            'is_fixed' => true,
            'is_taxable' => true,
        ]);

        $compVar = SalaryComponent::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Uang Transport',
            'type' => 'allowance',
            'is_fixed' => false,
            'is_taxable' => true,
        ]);

        EmployeeSalaryComponent::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'salary_component_id' => $compFixed->id,
            'amount' => 2000000.00,
            'is_active' => true,
        ]);

        EmployeeSalaryComponent::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'salary_component_id' => $compVar->id,
            'amount' => 1000000.00,
            'is_active' => true,
        ]);

        // 2. Add approved Overtime in period (e.g. 4 hours = Rp 500.000)
        OvertimeRequest::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'date' => '2026-01-10',
            'hours' => 4.0,
            'reason' => 'Server maintenance',
            'status' => 'approved',
            'calculated_pay' => 500000.00,
        ]);

        // 3. Create Payroll Period
        $period = PayrollPeriod::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Gaji Bulan Januari 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'payment_date' => '2026-01-25',
            'status' => 'draft',
        ]);

        // 4. Process Payroll via Service
        /** @var PayrollProcessorService $service */
        $service = app(PayrollProcessorService::class);
        $result = $service->processPeriod($period);

        $this->assertEquals(1, $result['processed_employees']);
        $this->assertEquals('processed', $period->fresh()->status);

        // 5. Inspect generated Payroll record
        $payroll = Payroll::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->where('payroll_period_id', $period->id)
            ->where('employee_id', $this->employee->id)
            ->first();

        $this->assertNotNull($payroll);

        // Expected Gross = Basic (15,000,000) + Fixed (2,000,000) + Var (1,000,000) + Overtime (500,000) = 18,500,000
        $this->assertEquals(18500000.00, (float) $payroll->gross_salary);

        // Base wage for BPJS = Basic (15,000,000) + Fixed Allowance (2,000,000) = 17,000,000
        // BPJS Kes Employee = 1% of capped 12,000,000 = 120,000
        $this->assertEquals(120000.00, (float) $payroll->bpjs_kes_employee);

        // BPJS TK Employee = JHT 2% of 17,000,000 (340,000) + JP 1% of capped 10,042,300 (100,423) = 440,423
        $this->assertEquals(440423.00, (float) $payroll->bpjs_tk_employee);

        // PPh 21 TER: Gross 18,500,000 with PTKP K/1 (Category B tier)
        // Rate from compliance config category B for 18.5jt is 8% (0.08 * 18,500,000 = 1,480,000)
        $this->assertGreaterThan(0, (float) $payroll->pph21_tax);

        // Take Home Pay must equal Gross - Deductions - Tax
        $expectedNet = $payroll->gross_salary - $payroll->deductions_total - $payroll->pph21_tax;
        $this->assertEquals((float) $expectedNet, (float) $payroll->take_home_pay);

        // Check structured breakdown JSON
        $this->assertIsArray($payroll->breakdown);
        $this->assertArrayHasKey('earnings', $payroll->breakdown);
        $this->assertArrayHasKey('deductions', $payroll->breakdown);
        $this->assertArrayHasKey('employer_contributions', $payroll->breakdown);
    }

    /**
     * Test 2: Payroll Approval and Payment Workflow
     */
    public function test_payroll_approval_and_disbursement_lifecycle(): void
    {
        $period = PayrollPeriod::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Gaji Bulan Februari 2026',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28',
            'payment_date' => '2026-02-25',
            'status' => 'draft',
        ]);

        /** @var PayrollProcessorService $service */
        $service = app(PayrollProcessorService::class);
        $service->processPeriod($period);

        // Approve
        $service->approvePeriod($period);
        $this->assertEquals('approved', $period->fresh()->status);
        $this->assertEquals('approved', $period->payrolls()->first()->status);

        // Mark As Paid
        $service->markAsPaid($period);
        $this->assertEquals('paid', $period->fresh()->status);
        $this->assertEquals('paid', $period->payrolls()->first()->status);
        $this->assertNotNull($period->payrolls()->first()->paid_at);
    }

    /**
     * Test 3: THR Calculation per Permenaker No. 6/2016
     * (Full 1 month wage for >= 12 months, Pro-rata for 1 to < 12 months, 0 for < 1 month)
     */
    public function test_thr_calculation_per_permenaker_6_2016(): void
    {
        /** @var ThrCalculator $thrCalc */
        $thrCalc = app(ThrCalculator::class);

        // 1. Employee with >= 12 months service (joined 2023-01-01, as of 2026-04-01 => > 36 months)
        $res1 = $thrCalc->calculateForEmployee($this->employee, '2026-04-01');
        $this->assertTrue($res1['is_eligible']);
        $this->assertEquals(15000000.00, $res1['thr_amount']); // 1 full month basic salary
        $this->assertStringContainsString('1 x Upah Sebulan', $res1['formula']);

        // 2. Employee with 6 months service
        $empNew = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_number' => 'PSN-NEW-01',
            'first_name' => 'Doni',
            'email' => 'doni@primasolusi.id',
            'join_date' => '2025-10-01',
            'basic_salary' => 12000000.00,
            'status' => 'active',
        ]);

        $res2 = $thrCalc->calculateForEmployee($empNew, '2026-04-01'); // exactly 6 months
        $this->assertTrue($res2['is_eligible']);
        // 6 / 12 * 12,000,000 = 6,000,000
        $this->assertEquals(6000000.00, $res2['thr_amount']);
        $this->assertStringContainsString('Pro-rata', $res2['formula']);

        // 3. Employee with < 1 month service
        $empFresh = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_number' => 'PSN-FRESH-01',
            'first_name' => 'Fajar',
            'email' => 'fajar@primasolusi.id',
            'join_date' => '2026-03-25',
            'basic_salary' => 8000000.00,
            'status' => 'active',
        ]);

        $res3 = $thrCalc->calculateForEmployee($empFresh, '2026-04-01');
        $this->assertFalse($res3['is_eligible']);
        $this->assertEquals(0.00, $res3['thr_amount']);
    }

    /**
     * Test 4: Annual Tax Reconciliation (Masa Pajak Terakhir PPh 21 / Formulir 1721-A1)
     */
    public function test_annual_tax_reconciliation_pasal_17_and_1721_a1(): void
    {
        // 1. Generate 11 months of payroll for Bambang in 2026
        for ($m = 1; $m <= 11; $m++) {
            $monthStr = str_pad($m, 2, '0', STR_PAD_LEFT);
            $period = PayrollPeriod::withoutGlobalScopes()->create([
                'tenant_id' => $this->tenant->id,
                'name' => "Gaji Bulan {$m} 2026",
                'start_date' => "2026-{$monthStr}-01",
                'end_date' => "2026-{$monthStr}-28",
                'payment_date' => "2026-{$monthStr}-25",
                'status' => 'paid',
            ]);

            Payroll::withoutGlobalScopes()->create([
                'tenant_id' => $this->tenant->id,
                'payroll_period_id' => $period->id,
                'employee_id' => $this->employee->id,
                'basic_salary' => 15000000.00,
                'allowances_total' => 0.00,
                'gross_salary' => 15000000.00,
                'deductions_total' => 400000.00,
                'bpjs_tk_employee' => 300000.00,
                'bpjs_tk_employer' => 600000.00,
                'bpjs_kes_employee' => 120000.00,
                'bpjs_kes_employer' => 480000.00,
                'pph21_tax' => 900000.00, // Monthly TER
                'take_home_pay' => 13580000.00,
                'status' => 'paid',
            ]);
        }

        // 2. Add THR for 2026
        ThrRecord::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'year' => 2026,
            'months_of_service' => 36,
            'base_wage' => 15000000.00,
            'thr_amount' => 15000000.00,
            'payment_date' => '2026-03-25',
            'status' => 'paid',
        ]);

        // 3. Run Tax Reconciliation Service for 2026
        /** @var TaxReconciliationService $taxService */
        $taxService = app(TaxReconciliationService::class);
        $reconciliation = $taxService->reconcileEmployee($this->employee, 2026);

        $this->assertInstanceOf(TaxReconciliation::class, $reconciliation);
        // Annual Gross = (11 * 15,000,000) + 15,000,000 THR = 180,000,000
        $this->assertEquals(180000000.00, (float) $reconciliation->gross_income_annual);

        // Biaya Jabatan = 5% of 180,000,000 = 9,000,000 => Capped at max 6,000,000
        $this->assertEquals(6000000.00, (float) $reconciliation->biaya_jabatan);

        // PTKP for K/1 = 63,000,000
        $this->assertEquals(63000000.00, (float) $reconciliation->ptkp_annual);

        // Annual PPh 21 due per Pasal 17 brackets must be positive
        $this->assertGreaterThan(0, (float) $reconciliation->pph21_annual_due);

        // Total TER paid Jan-Nov = 11 * 900,000 = 9,900,000
        $this->assertEquals(9900000.00, (float) $reconciliation->pph21_ter_paid_total);

        // December Tax = Annual Due - 9,900,000
        $this->assertEquals(
            round($reconciliation->pph21_annual_due - 9900000.00, 2),
            (float) $reconciliation->pph21_december_tax
        );
    }

    /**
     * Test 5: Reimbursement Multi-Level Approval Workflow
     */
    public function test_reimbursement_multilevel_approval_and_disbursement(): void
    {
        /** @var ReimbursementService $reimbService */
        $reimbService = app(ReimbursementService::class);

        // 1. Submit claim
        $claim = $reimbService->submitClaim(
            $this->employee,
            'transport',
            'Taxi Meeting Klien',
            250000.00,
            '2026-01-15',
            'Transport pulang malam setelah kick-off'
        );

        $this->assertEquals('pending', $claim->status);

        // 2. Manager Approval
        $reimbService->approveByManager($claim, $this->hrUser);
        $this->assertEquals('approved_manager', $claim->fresh()->status);
        $this->assertEquals($this->hrUser->id, $claim->fresh()->reviewed_by_manager_id);

        // 3. Finance Approval
        $reimbService->approveByFinance($claim, $this->hrUser);
        $this->assertEquals('approved_finance', $claim->fresh()->status);
        $this->assertEquals($this->hrUser->id, $claim->fresh()->reviewed_by_finance_id);

        // 4. Mark As Paid directly
        $reimbService->markAsPaid($claim);
        $this->assertEquals('paid', $claim->fresh()->status);
        $this->assertNotNull($claim->fresh()->paid_at);
    }

    /**
     * Test 6: Contract Management & PP 35/2021 Statutory Compensation Calculation
     */
    public function test_pkwt_contract_statutory_compensation_and_renewal(): void
    {
        /** @var ContractService $contractService */
        $contractService = app(ContractService::class);

        // 1. Create a 12-month PKWT contract
        // Wage: 12,000,000, 12 months duration => Compensation: 1 x 12,000,000 = 12,000,000
        $contract = $contractService->createContract($this->employee, [
            'contract_number' => 'PKWT/PSN/2025/001',
            'type' => 'pkwt',
            'start_date' => '2025-01-01',
            'end_date' => '2026-01-01',
            'monthly_wage' => 12000000.00,
            'notes' => 'Kontrak awal tahun pertama',
        ]);

        $this->assertEquals('active', $contract->status);
        $this->assertEquals(12000000.00, (float) $contract->compensation_calculated);
        $this->assertFalse($contract->compensation_paid);

        // 2. Mark statutory compensation as paid
        $contractService->markCompensationPaid($contract);
        $this->assertTrue($contract->fresh()->compensation_paid);

        // 3. Renew contract (extend for another year)
        $newContract = $contractService->renewContract($contract, [
            'contract_number' => 'PKWT/PSN/2026/001-EXT',
            'type' => 'pkwt',
            'start_date' => '2026-01-02',
            'end_date' => '2027-01-02',
            'monthly_wage' => 13500000.00,
        ]);

        $this->assertEquals('renewed', $contract->fresh()->status);
        $this->assertEquals('active', $newContract->status);
        $this->assertEquals(1, $newContract->renewal_count);
        $this->assertEquals(13500000.00, (float) $newContract->compensation_calculated);
    }

    /**
     * Test 7: HTTP Web Routes for Phase 3
     */
    public function test_company_payroll_web_routes_and_views(): void
    {
        // 1. Index Payroll Periods
        $response = $this->actingAs($this->hrUser)->get(route('company.payroll.index'));
        $response->assertStatus(200);
        $response->assertSee('Penggajian & Kepatuhan Pajak');

        // 2. Index Salary Components
        $responseComp = $this->actingAs($this->hrUser)->get(route('company.components.index'));
        $responseComp->assertStatus(200);
        $responseComp->assertSee('Komponen Gaji & Tunjangan');

        // 3. Index THR
        $responseThr = $this->actingAs($this->hrUser)->get(route('company.thr.index'));
        $responseThr->assertStatus(200);
        $responseThr->assertSee('Tunjangan Hari Raya (THR)');

        // 4. Index Tax Reconciliation
        $responseTax = $this->actingAs($this->hrUser)->get(route('company.tax.index'));
        $responseTax->assertStatus(200);
        $responseTax->assertSee('Rekonsiliasi Tahunan PPh 21');

        // 5. Index Reimbursements
        $responseReimb = $this->actingAs($this->hrUser)->get(route('company.reimbursements.index'));
        $responseReimb->assertStatus(200);
        $responseReimb->assertSee('Klaim Reimbursement & Biaya');

        // 6. Index Contracts
        $responseCtr = $this->actingAs($this->hrUser)->get(route('company.contracts.index'));
        $responseCtr->assertStatus(200);
        $responseCtr->assertSee('Manajemen Kontrak PKWT & Kompensasi');
    }
}
