<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\JobGrade;
use App\Models\BusinessTripOrder;
use App\Models\BusinessTripAdvance;
use App\Models\BusinessTripExpense;
use App\Models\BusinessTripSettlement;
use App\Models\OffboardingClearance;
use App\Models\WorkExperienceLetter;
use App\Models\TaxForm1721A1Record;
use App\Models\TaxReconciliation;
use App\Services\Compensation\WageScaleService;
use App\Services\Travel\BusinessTripService;
use App\Services\Offboarding\ClearanceService;
use App\Services\Tax\Ebupot21ExportService;
use App\Services\Organization\OrgChartService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdvancedEnterpriseCompliancePhase11Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected Branch $branch;
    protected User $hrUser;
    protected User $empUser;
    protected Employee $employeeBudi;
    protected Employee $employeeAni;
    protected Department $department;
    protected Position $positionDev;
    protected Position $positionLead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->tenant = Tenant::create([
            'name' => 'Demo Enterprise Corp',
            'code' => 'demo-ent',
            'status' => 'active',
        ]);

        app(TenantManager::class)->setTenant($this->tenant);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Enterprise Solusi Indonesia',
            'legal_name' => 'PT Enterprise Solusi Indonesia Tbk',
            'npwp' => '01.234.567.8-012.000',
            'email' => 'corporate@enterprise.co.id',
            'phone' => '021-5250001',
            'city' => 'Jakarta Selatan',
            'address' => 'Jl. Jend. Sudirman Kav 25',
        ]);

        $this->branch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Kantor Pusat Sudirman',
            'city' => 'Jakarta Selatan',
            'latitude' => -6.2146200,
            'longitude' => 106.8189800,
            'radius_meters' => 150,
        ]);

        $this->department = Department::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Engineering & Core Systems',
            'code' => 'ENG',
        ]);

        $this->positionLead = Position::create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'VP of Engineering',
            'job_level' => 'executive',
        ]);

        $this->positionDev = Position::create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'Senior Backend Engineer',
            'job_level' => 'senior',
        ]);

        $hrRole = Role::where('name', 'HR Manager')->first() ?? Role::create(['name' => 'HR Manager', 'label' => 'HR Manager']);
        $empRole = Role::where('name', 'Employee')->first() ?? Role::create(['name' => 'Employee', 'label' => 'Employee']);

        $this->hrUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'HR Director Sisca',
            'email' => 'hr.sisca@enterprise.co.id',
            'password' => bcrypt('password123'),
        ]);
        $this->hrUser->roles()->attach($hrRole);

        $this->empUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@enterprise.co.id',
            'password' => bcrypt('password123'),
        ]);
        $this->empUser->roles()->attach($empRole);

        $this->employeeAni = Employee::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->hrUser->id,
            'employee_number' => 'EMP-ANI-001',
            'nik_ktp' => '3171012345670001',
            'first_name' => 'Ani',
            'last_name' => 'Wijaya',
            'email' => 'ani.wijaya@enterprise.co.id',
            'phone' => '08111223344',
            'gender' => 'female',
            'department_id' => $this->department->id,
            'position_id' => $this->positionLead->id,
            'employment_status' => 'permanent',
            'join_date' => '2021-01-01',
            'tax_ptkp_status' => 'TK/0',
            'npwp' => '12.345.678.9-011.000',
            'basic_salary' => 25000000.00,
            'status' => 'active',
        ]);

        $this->employeeBudi = Employee::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->empUser->id,
            'employee_number' => 'EMP-BUDI-002',
            'nik_ktp' => '3171019988770002',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'budi.santoso@enterprise.co.id',
            'phone' => '081234567890',
            'gender' => 'male',
            'department_id' => $this->department->id,
            'position_id' => $this->positionDev->id,
            'reports_to_id' => $this->employeeAni->id,
            'employment_status' => 'permanent',
            'join_date' => '2023-01-15',
            'tax_ptkp_status' => 'K/1',
            'npwp' => '98.765.432.1-012.000',
            'basic_salary' => 11000000.00,
            'status' => 'active',
        ]);
    }

    /**
     * 1. Test Wage Scale Management & Automated Spread Calculation (Permenaker 1/2017).
     */
    public function test_can_manage_job_grades_and_calculate_wage_scale_spread_and_midpoint(): void
    {
        /** @var WageScaleService $wageService */
        $wageService = app(WageScaleService::class);

        $gradeG1 = $wageService->upsertJobGrade($this->company, [
            'grade_code' => 'G1',
            'name' => 'Pelaksana Junior',
            'level_order' => 1,
            'min_salary' => 6000000.00,
            'max_salary' => 9000000.00,
        ]);

        $this->assertEquals(7500000.00, (float) $gradeG1->mid_salary);
        $this->assertEquals(50.00, (float) $gradeG1->spread_percentage);

        $gradeG2 = $wageService->upsertJobGrade($this->company, [
            'grade_code' => 'G2',
            'name' => 'Officer Senior',
            'level_order' => 2,
            'min_salary' => 9000000.00,
            'max_salary' => 15000000.00,
        ]);

        $this->assertEquals(12000000.00, (float) $gradeG2->mid_salary);
        $this->assertEquals(66.67, (float) $gradeG2->spread_percentage);
        $this->assertEquals(60.00, (float) $gradeG2->midpoint_progression); // (12M - 7.5M) / 7.5M = 60%
    }

    /**
     * 2. Test Compa-Ratio Audit Engine and Band Positioning.
     */
    public function test_wage_scale_compa_ratio_audit_accurately_detects_band_position(): void
    {
        /** @var WageScaleService $wageService */
        $wageService = app(WageScaleService::class);

        $grade = $wageService->upsertJobGrade($this->company, [
            'grade_code' => 'G2',
            'name' => 'Senior Officer',
            'level_order' => 2,
            'min_salary' => 8000000.00,
            'max_salary' => 14000000.00,
        ]);

        $this->employeeBudi->job_grade_id = $grade->id;
        $this->employeeBudi->basic_salary = 11000000.00; // Exactly mid_salary (11M)
        $this->employeeBudi->save();

        $audit = $wageService->performWageScaleAudit($this->company);

        $this->assertEquals(1, $audit['parity_count']);
        $this->assertEquals(100.0, $audit['compliance_rate']);

        $budiRow = collect($audit['audited_employees'])->firstWhere('employee.id', $this->employeeBudi->id);
        $this->assertNotNull($budiRow);
        $this->assertEquals(100.0, $budiRow['compa_ratio']);
        $this->assertEquals('parity', $budiRow['band_status']);
    }

    /**
     * 3. Test SPPD (Surat Perintah Perjalanan Dinas) Creation & Cash Advance Request.
     */
    public function test_can_create_sppd_business_trip_with_cash_advance(): void
    {
        /** @var BusinessTripService $travelService */
        $travelService = app(BusinessTripService::class);

        $order = $travelService->createTripOrder($this->employeeBudi, [
            'purpose' => 'Audit Operasional Cabang Surabaya',
            'origin_city' => 'Jakarta',
            'destination_city' => 'Surabaya',
            'departure_date' => '2026-03-10',
            'return_date' => '2026-03-13',
            'transport_type' => 'flight',
            'lodging_needed' => true,
            'per_diem_daily_rate' => 300000.00,
            'advance_requested' => 3500000.00,
        ]);

        $this->assertDatabaseHas('business_trip_orders', [
            'id' => $order->id,
            'destination_city' => 'Surabaya',
            'duration_days' => 4,
            'status' => 'submitted',
        ]);

        $this->assertStringStartsWith('SPPD/2026/', $order->sppd_number);

        $this->assertDatabaseHas('business_trip_advances', [
            'business_trip_order_id' => $order->id,
            'amount_requested' => 3500000.00,
            'status' => 'pending',
        ]);
    }

    /**
     * 4. Test SPPD Approval and Finance Disbursement Workflow.
     */
    public function test_sppd_approval_and_finance_disbursement_lifecycle(): void
    {
        /** @var BusinessTripService $travelService */
        $travelService = app(BusinessTripService::class);

        $order = $travelService->createTripOrder($this->employeeBudi, [
            'purpose' => 'Negosiasi Kontrak Klien Medan',
            'origin_city' => 'Jakarta',
            'destination_city' => 'Medan',
            'departure_date' => '2026-04-01',
            'return_date' => '2026-04-03',
            'advance_requested' => 2500000.00,
        ]);

        // Manager Approval
        $travelService->processApproval($order, $this->hrUser, true);
        $this->assertEquals('approved', $order->fresh()->status);
        $this->assertEquals($this->hrUser->id, $order->fresh()->approved_by);

        // Advance is approved
        $advance = $order->advances()->first();
        $this->assertEquals('approved', $advance->fresh()->status);

        // Finance Disbursement
        $travelService->disburseAdvance($advance, $this->hrUser, 'TRF-BNI-88192');
        $this->assertEquals('disbursed', $advance->fresh()->status);
        $this->assertEquals('TRF-BNI-88192', $advance->fresh()->transfer_reference);
        $this->assertEquals('ongoing', $order->fresh()->status);
    }

    /**
     * 5. Test SPPD Expenses Logging and Settlement Finalization.
     */
    public function test_sppd_expense_logging_and_settlement_reconciliation(): void
    {
        /** @var BusinessTripService $travelService */
        $travelService = app(BusinessTripService::class);

        $order = $travelService->createTripOrder($this->employeeBudi, [
            'purpose' => 'Workshop Teknologi Bandung',
            'destination_city' => 'Bandung',
            'departure_date' => '2026-05-01',
            'return_date' => '2026-05-02',
            'advance_requested' => 1000000.00,
        ]);

        $travelService->processApproval($order, $this->hrUser, true);
        $advance = $order->advances()->first();
        $travelService->disburseAdvance($advance, $this->hrUser, 'TRF-123');

        // Add expenses
        $travelService->addExpense($order, [
            'expense_category' => 'transport',
            'amount' => 350000.00,
            'description' => 'Tiket Kereta Cepat Whoosh PP',
        ]);

        $travelService->addExpense($order, [
            'expense_category' => 'lodging',
            'amount' => 500000.00,
            'description' => 'Hotel Ibis Bandung',
        ]);

        $travelService->addExpense($order, [
            'expense_category' => 'per_diem',
            'amount' => 300000.00,
            'description' => 'Uang saku harian 2 hari',
        ]);

        // Total expenses = 350k + 500k + 300k = 1.150.000. Advance = 1.000.000.
        // Balance = 150.000 (company reimburses employee)
        $settlement = $travelService->finalizeSettlement($order, $this->hrUser, 'Audit pengeluaran lengkap sesuai kwitansi.');

        $this->assertEquals(1000000.00, (float) $settlement->total_advance);
        $this->assertEquals(1150000.00, (float) $settlement->total_expenses);
        $this->assertEquals(150000.00, (float) $settlement->balance_amount);
        $this->assertEquals('finalized', $settlement->status);
        $this->assertEquals('completed', $order->fresh()->status);
    }

    /**
     * 6. Test Offboarding Clearance Lifecycle across 4 Departments.
     */
    public function test_offboarding_clearance_lifecycle_across_4_departments(): void
    {
        /** @var ClearanceService $clearanceService */
        $clearanceService = app(ClearanceService::class);

        $clearance = $clearanceService->initiateClearance($this->employeeBudi, [
            'resignation_date' => '2026-06-01',
            'last_working_day' => '2026-06-30',
            'separation_reason' => 'resigned',
        ]);

        $this->assertEquals('in_progress', $clearance->overall_status);
        $this->assertEquals('pending', $clearance->hr_status);

        // Signoff HR
        $clearanceService->signOffDepartment($clearance, 'hr', $this->hrUser, [
            'status' => 'cleared',
            'notes' => 'ID Card dan berkas diserahkan.',
        ]);
        $this->assertEquals('cleared', $clearance->fresh()->hr_status);

        // Signoff IT
        $clearanceService->signOffDepartment($clearance, 'it', $this->hrUser, [
            'status' => 'cleared',
            'notes' => 'Laptop dan email di-wipe.',
        ]);

        // Signoff Finance
        $clearanceService->signOffDepartment($clearance, 'finance', $this->hrUser, [
            'status' => 'cleared',
            'notes' => 'Kasbon lunas.',
        ]);

        // Signoff Supervisor (completing all 4)
        $clearanceService->signOffDepartment($clearance, 'supervisor', $this->hrUser, [
            'status' => 'cleared',
            'notes' => 'Handover project tuntas.',
        ]);

        $freshClearance = $clearance->fresh();
        $this->assertEquals('completed', $freshClearance->overall_status);
        $this->assertNotNull($freshClearance->completed_at);
        $this->assertEquals('inactive', $this->employeeBudi->fresh()->status);
    }

    /**
     * 7. Test Paklaring (Work Experience Letter) Issuance.
     */
    public function test_issue_and_verify_paklaring_work_experience_letter(): void
    {
        /** @var ClearanceService $clearanceService */
        $clearanceService = app(ClearanceService::class);

        $clearance = $clearanceService->initiateClearance($this->employeeBudi, [
            'resignation_date' => '2026-06-01',
            'last_working_day' => '2026-06-30',
        ]);

        $clearance->hr_status = 'cleared';
        $clearance->it_status = 'cleared';
        $clearance->finance_status = 'cleared';
        $clearance->supervisor_status = 'cleared';
        $clearance->overall_status = 'completed';
        $clearance->save();

        $letter = $clearanceService->issuePaklaring($clearance, $this->hrUser, [
            'signer_position' => 'Head of People Operations',
            'conduct_appraisal' => 'exemplary',
        ]);

        $this->assertDatabaseHas('work_experience_letters', [
            'id' => $letter->id,
            'employee_id' => $this->employeeBudi->id,
            'conduct_appraisal' => 'exemplary',
        ]);

        $this->assertStringContainsString('/HRD-PKL/', $letter->letter_number);
    }

    /**
     * 8. Test Tax Form 1721-A1 Generation & e-Bupot 21/26 CSV Export (PER-2/PJ/2024).
     */
    public function test_tax_form_1721_a1_generation_and_ebupot_csv_export(): void
    {
        TaxReconciliation::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employeeBudi->id,
            'tax_year' => 2026,
            'gross_income_annual' => 132000000.00,
            'biaya_jabatan' => 6000000.00,
            'bpjs_deductions_annual' => 3960000.00,
            'net_income_annual' => 122040000.00,
            'ptkp_annual' => 63000000.00, // K/1
            'pkp' => 59040000.00,
            'pph21_annual_due' => 3356000.00,
            'pph21_ter_paid_total' => 3356000.00,
            'pph21_december_tax' => 0.00,
            'status' => 'finalized',
        ]);

        /** @var Ebupot21ExportService $ebupotService */
        $ebupotService = app(Ebupot21ExportService::class);

        $form1721 = $ebupotService->generateForm1721A1($this->employeeBudi, 2026);

        $this->assertDatabaseHas('tax_form1721_a1_records', [
            'employee_id' => $this->employeeBudi->id,
            'tax_year' => 2026,
            'status_ptkp' => 'K/1',
        ]);

        $this->assertEquals(132000000.00, (float) $form1721->penghasilan_bruto);
        $this->assertEquals(6000000.00, (float) $form1721->biaya_jabatan);
        $this->assertEquals(3356000.00, (float) $form1721->pph21_terutang_setahun);

        // Generate e-Bupot CSV
        $csv = $ebupotService->generateEbupotCsv($this->company, 2026);

        $this->assertStringContainsString('MasaPajak;TahunPajak;Pembetulan;NomorBuktiPotong', $csv);
        $this->assertStringContainsString($this->employeeBudi->full_name, $csv);
        $this->assertStringContainsString('132000000', $csv);
    }

    /**
     * 9. Test Interactive Organization Chart Hierarchy Tree Builder.
     */
    public function test_organization_chart_hierarchical_tree_builder(): void
    {
        /** @var OrgChartService $orgService */
        $orgService = app(OrgChartService::class);

        $hierarchy = $orgService->buildHierarchy($this->company);

        $this->assertEquals(2, $hierarchy['total_headcount']);
        $this->assertCount(1, $hierarchy['tree']); // Root is Ani (who has no supervisor)

        $root = $hierarchy['tree'][0];
        $this->assertEquals('Ani Wijaya', $root['name']);
        $this->assertEquals(1, $root['direct_reports_count']); // Budi reports to Ani
        $this->assertEquals('Budi Santoso', $root['children'][0]['name']);
    }

    /**
     * 10. Test ESS Business Trip Submission and Tax Form Access.
     */
    public function test_ess_business_trip_submission_and_tax_form_download(): void
    {
        $response = $this->actingAs($this->empUser)
            ->post(route('ess.business-trip.submit'), [
                'purpose' => 'Meeting koordinasi dengan vendor cloud',
                'origin_city' => 'Jakarta',
                'destination_city' => 'Tangerang BSD',
                'departure_date' => '2026-07-01',
                'return_date' => '2026-07-01',
                'transport_type' => 'company_car',
                'advance_requested' => 500000.00,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('business_trip_orders', [
            'employee_id' => $this->employeeBudi->id,
            'destination_city' => 'Tangerang BSD',
        ]);

        // Form 1721-A1 view
        $form = TaxForm1721A1Record::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_id' => $this->employeeBudi->id,
            'tax_year' => 2026,
            'nomor_bupot' => '1.1-12.26-9999999',
            'pemotong_npwp' => '01.234.567.8-012.000',
            'pemotong_nama' => 'PT Enterprise Solusi Indonesia',
            'penerima_nik' => '3171019988770002',
            'penerima_nama' => 'Budi Santoso',
            'status_ptkp' => 'K/1',
            'status' => 'published',
        ]);

        $viewResponse = $this->actingAs($this->empUser)
            ->get(route('ess.form1721a1.show', $form));

        $viewResponse->assertOk();
        $viewResponse->assertSee('FORMULIR 1721 - A1');
        $viewResponse->assertSee('Budi Santoso');
    }
}
