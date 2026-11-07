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
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\EmployeeFamily;
use App\Models\EmployeeEducation;
use App\Models\EmployeeEmploymentHistory;
use App\Models\EmployeeEmergencyContact;
use App\Models\PerformanceFeedback360;
use App\Models\PerformanceImprovementPlan;
use App\Models\BankDisbursalBatch;
use App\Models\RegionalMinimumWage;
use App\Models\BpjsComplianceReport;
use App\Models\BranchQrKiosk;
use App\Services\Talent\Appraisal360Service;
use App\Services\Finance\BankDisbursalService;
use App\Services\Compliance\RegulatoryComplianceService;
use App\Services\Attendance\QrAttendanceService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdvancedAppraisalsAndCompliancePhase10Test extends TestCase
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
    protected PayrollPeriod $payrollPeriod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // 1. Tenant & Company
        $this->tenant = Tenant::create([
            'name' => 'PT Nusantara Sejahtera Mandiri',
            'slug' => 'nusantara-sejahtera',
            'status' => 'active',
        ]);

        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Nusantara Sejahtera Mandiri',
            'email' => 'hr@nusantara.id',
            'npwp' => '01.234.567.8-012.000',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Branch with GPS Geofencing
        $this->branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Kantor Pusat Jakarta',
            'code' => 'JKT-HQ',
            'city' => 'Jakarta Selatan',
            'latitude' => -6.2088000,
            'longitude' => 106.8456000,
            'radius_meters' => 150,
        ]);

        // 3. Department & Positions
        $this->department = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Engineering & Core Tech',
            'code' => 'ENG',
        ]);

        $this->positionDev = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'Senior Backend Engineer',
            'job_level' => 'Senior',
        ]);

        $this->positionLead = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'VP of Engineering',
            'job_level' => 'Executive',
        ]);

        // 4. Roles & Users
        $hrRole = Role::where('slug', 'hr_manager')->first();
        $empRole = Role::where('slug', 'employee')->first();

        $this->hrUser = User::create([
            'name' => 'HR Manager Corporate',
            'email' => 'hr.manager@nusantara.id',
            'password' => bcrypt('password123'),
        ]);
        $this->hrUser->tenants()->attach($this->tenant->id, ['is_owner' => false, 'role_id' => $hrRole->id]);

        $this->empUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@nusantara.id',
            'password' => bcrypt('password123'),
        ]);
        $this->empUser->tenants()->attach($this->tenant->id, ['is_owner' => false, 'role_id' => $empRole->id]);

        // 5. Employees
        $this->employeeAni = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_number' => 'EMP-001',
            'nik_ktp' => '3201123456780001',
            'first_name' => 'Ani',
            'last_name' => 'Wijaya',
            'email' => 'ani.wijaya@nusantara.id',
            'phone' => '081234567890',
            'gender' => 'female',
            'marital_status' => 'married',
            'tax_ptkp_status' => 'K/1',
            'department_id' => $this->department->id,
            'position_id' => $this->positionLead->id,
            'employment_status' => 'pkwtt',
            'join_date' => '2020-01-01',
            'basic_salary' => 25000000.00,
            'bank_name' => 'BCA',
            'bank_account_number' => '8881234567',
            'bank_account_holder' => 'Ani Wijaya',
            'status' => 'active',
        ]);

        $this->employeeBudi = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->empUser->id,
            'employee_number' => 'EMP-002',
            'nik_ktp' => '3201123456780002',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'budi.santoso@nusantara.id',
            'phone' => '081234567891',
            'gender' => 'male',
            'marital_status' => 'single',
            'tax_ptkp_status' => 'TK/0',
            'department_id' => $this->department->id,
            'position_id' => $this->positionDev->id,
            'reports_to_id' => $this->employeeAni->id,
            'employment_status' => 'pkwtt',
            'join_date' => '2022-01-01',
            'basic_salary' => 12000000.00,
            'bank_name' => 'MANDIRI',
            'bank_account_number' => '1370001234567',
            'bank_account_holder' => 'Budi Santoso',
            'status' => 'active',
        ]);

        // 6. Payroll Period & Payrolls
        $this->payrollPeriod = PayrollPeriod::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Payroll Januari 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'payment_date' => '2026-01-31',
            'status' => 'approved',
        ]);

        Payroll::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'payroll_period_id' => $this->payrollPeriod->id,
            'employee_id' => $this->employeeBudi->id,
            'basic_salary' => 12000000.00,
            'gross_salary' => 12000000.00,
            'bpjs_tk_employee' => 360000.00,
            'bpjs_tk_employer' => 748000.00,
            'bpjs_kes_employee' => 120000.00,
            'bpjs_kes_employer' => 480000.00,
            'pph21_tax' => 450000.00,
            'take_home_pay' => 11070000.00,
            'status' => 'paid',
        ]);
    }

    /**
     * 1. Test Extended Employee 360 Profile: Family, Education, Employment History, Emergency Contact.
     */
    public function test_extended_employee_profile_creation_and_relations(): void
    {
        $this->actingAs($this->hrUser);

        // Add Family
        $famResponse = $this->post(route('company.employees.family.store', $this->employeeBudi->id), [
            'name' => 'Dewi Lestari',
            'relationship' => 'spouse',
            'gender' => 'female',
            'birth_date' => '1995-05-15',
            'nik' => '3201998877660001',
            'is_bpjs_covered' => 1,
            'is_tax_dependent' => 1,
        ]);
        $famResponse->assertRedirect();
        $this->assertDatabaseHas('employee_families', [
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employeeBudi->id,
            'name' => 'Dewi Lestari',
            'relationship' => 'spouse',
            'is_bpjs_covered' => true,
        ]);

        // Add Education
        $eduResponse = $this->post(route('company.employees.education.store', $this->employeeBudi->id), [
            'degree_level' => 'S1/D4',
            'institution_name' => 'Universitas Indonesia',
            'major' => 'Ilmu Komputer',
            'graduation_year' => 2018,
            'gpa' => 3.80,
        ]);
        $eduResponse->assertRedirect();
        $this->assertDatabaseHas('employee_educations', [
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employeeBudi->id,
            'institution_name' => 'Universitas Indonesia',
            'graduation_year' => 2018,
        ]);

        // Add Employment History
        $histResponse = $this->post(route('company.employees.history.store', $this->employeeBudi->id), [
            'company_name' => 'PT Solusi Finansial Indonesia',
            'position_title' => 'Junior Web Developer',
            'start_date' => '2019-01-01',
            'end_date' => '2021-12-31',
            'last_salary' => 8500000.00,
        ]);
        $histResponse->assertRedirect();
        $this->assertDatabaseHas('employee_employment_histories', [
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employeeBudi->id,
            'company_name' => 'PT Solusi Finansial Indonesia',
        ]);

        // Add Emergency Contact
        $contactResponse = $this->post(route('company.employees.emergency.store', $this->employeeBudi->id), [
            'contact_name' => 'Agus Santoso',
            'relationship' => 'Ayah Kandung',
            'phone' => '081311223344',
            'address' => 'Jakarta Barat',
            'is_primary' => 1,
        ]);
        $contactResponse->assertRedirect();
        $this->assertDatabaseHas('employee_emergency_contacts', [
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employeeBudi->id,
            'contact_name' => 'Agus Santoso',
        ]);

        // Test Eloquent relationships
        $budi = $this->employeeBudi->fresh();
        $this->assertCount(1, $budi->families);
        $this->assertCount(1, $budi->educations);
        $this->assertCount(1, $budi->employmentHistories);
        $this->assertCount(1, $budi->emergencyContacts);
    }

    /**
     * 2. Test Supervisory Hierarchy (reports_to_id and subordinates).
     */
    public function test_employee_reports_to_hierarchical_supervision(): void
    {
        $this->actingAs($this->hrUser);

        // Assign Ani as supervisor of Budi
        $this->post(route('company.employees.supervisor.assign', $this->employeeBudi->id), [
            'reports_to_id' => $this->employeeAni->id,
        ])->assertRedirect();

        $budi = $this->employeeBudi->fresh();
        $ani = $this->employeeAni->fresh();

        $this->assertEquals($ani->id, $budi->reportsTo->id);
        $this->assertTrue($ani->subordinates->contains($budi));
    }

    /**
     * 3. Test 360-Degree Feedback: Request creation, submission & summary metrics calculation.
     */
    public function test_360_degree_feedback_request_and_submission(): void
    {
        $appraisalService = app(Appraisal360Service::class);

        // 1. HR requests 360 review for Budi from Ani (Supervisor)
        $feedback = $appraisalService->createFeedbackRequest(
            $this->tenant->id,
            $this->employeeBudi->id,
            $this->employeeAni->id,
            'supervisor',
            null,
            true
        );

        $this->assertDatabaseHas('performance_feedbacks_360', [
            'subject_employee_id' => $this->employeeBudi->id,
            'reviewer_employee_id' => $this->employeeAni->id,
            'status' => 'pending',
            'rater_relationship' => 'supervisor',
        ]);

        // 2. Reviewer submits feedback evaluation
        $appraisalService->submitFeedback($feedback, [
            'core_values_score' => 5,
            'leadership_score' => 4,
            'collaboration_score' => 5,
            'execution_score' => 4,
            'strengths' => 'Eksekusi teknis sangat rapi dan konsisten.',
            'areas_for_improvement' => 'Perbanyak komunikasi lintas divisi.',
        ]);

        $feedback->refresh();
        $this->assertEquals('completed', $feedback->status);
        $this->assertEquals(4.50, $feedback->overall_score);

        // 3. Verify summary calculation
        $summary = $appraisalService->getEmployee360Summary($this->employeeBudi->id);
        $this->assertEquals(1, $summary['total_responses']);
        $this->assertEquals(4.50, $summary['overall_average']);
        $this->assertEquals(5.00, $summary['core_values_avg']);
        $this->assertArrayHasKey('supervisor', $summary['by_relationship']);
    }

    /**
     * 4. Test Performance Improvement Plan (PIP) Lifecycle.
     */
    public function test_performance_improvement_plan_lifecycle(): void
    {
        $this->actingAs($this->hrUser);

        // 1. Create PIP
        $response = $this->post(route('company.appraisals360.pips.store'), [
            'employee_id' => $this->employeeBudi->id,
            'supervisor_employee_id' => $this->employeeAni->id,
            'title' => 'PIP Peningkatan Kecepatan Delivery Q1',
            'reason' => 'Target deployment mingguan belum tercapai tepat waktu.',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'target_objectives' => 'Mencapai 100% on-time deployment pada 4 sprint berturut-turut.',
            'action_plan' => '1-on-1 coaching mingguan bersama VP Engineering.',
        ]);
        $response->assertRedirect();

        $pip = PerformanceImprovementPlan::where('employee_id', $this->employeeBudi->id)->first();
        $this->assertNotNull($pip);
        $this->assertEquals('in_progress', $pip->status);

        // 2. Add progress notes
        $this->post(route('company.appraisals360.pips.notes', $pip->id), [
            'notes' => 'Minggu ke-2: Sprint backlog berhasil diselesaikan sesuai SLA.',
        ])->assertRedirect();

        $pip->refresh();
        $this->assertStringContainsString('Minggu ke-2', $pip->progress_notes);

        // 3. Resolve PIP as passed
        $this->post(route('company.appraisals360.pips.resolve', $pip->id), [
            'status' => 'passed',
            'final_evaluation' => 'Karyawan menunjukkan peningkatan performa kerja yang signifikan.',
        ])->assertRedirect();

        $pip->refresh();
        $this->assertEquals('passed', $pip->status);
        $this->assertNotNull($pip->completed_at);
    }

    /**
     * 5. Test Indonesian Banking Mass Disbursal Batch & Export (BCA, Mandiri, BNI, BRI).
     */
    public function test_indonesian_bank_disbursal_export_bca_mandiri_bni_bri(): void
    {
        $this->actingAs($this->hrUser);
        $disbursalService = app(BankDisbursalService::class);

        // 1. Validation check
        $validation = $disbursalService->validateEmployeeBankAccounts($this->payrollPeriod);
        $this->assertEquals(1, $validation['valid_count']);
        $this->assertEquals(11070000.00, $validation['total_disbursal_amount']);

        // 2. Generate Batch via Controller
        $response = $this->post(route('company.disbursal.generate'), [
            'payroll_period_id' => $this->payrollPeriod->id,
            'bank_target' => 'bca',
        ]);
        $response->assertRedirect();

        $batch = BankDisbursalBatch::where('payroll_period_id', $this->payrollPeriod->id)->first();
        $this->assertNotNull($batch);
        $this->assertEquals('bca', $batch->bank_target);

        // 3. Verify bank-specific exports
        $bcaCsv = $disbursalService->exportBcaKlikBca($this->payrollPeriod);
        $this->assertStringContainsString('REK_TUJUAN', $bcaCsv);
        $this->assertStringContainsString('1370001234567', $bcaCsv);
        $this->assertStringContainsString('11070000.00', $bcaCsv);

        $mandiriCsv = $disbursalService->exportMandiriMcm($this->payrollPeriod);
        $this->assertStringContainsString('BENEFICIARY_ACCOUNT', $mandiriCsv);

        $bniCsv = $disbursalService->exportBniDirect($this->payrollPeriod);
        $this->assertStringContainsString('CREDIT_ACCOUNT_NO', $bniCsv);

        $briCsv = $disbursalService->exportBriCms($this->payrollPeriod);
        $this->assertStringContainsString('NOMOR_REKENING_TUJUAN', $briCsv);
    }

    /**
     * 6. Test Regional Minimum Wage (UMK PP 51/2023) Compliance Audit Engine.
     */
    public function test_regional_minimum_wage_umk_compliance_audit(): void
    {
        $this->actingAs($this->hrUser);
        $complianceService = app(RegulatoryComplianceService::class);

        // 1. Seed UMK Standard via Controller
        $this->post(route('company.compliance.umk.store'), [
            'province' => 'DKI Jakarta',
            'city_or_regency' => 'Jakarta Pusat',
            'year' => (int) date('Y'),
            'minimum_wage_amount' => 5340000.00,
            'legal_reference' => 'Kepgub DKI No. 112 Tahun 2025',
        ])->assertRedirect();

        $this->assertDatabaseHas('regional_minimum_wages', [
            'city_or_regency' => 'Jakarta Pusat',
            'minimum_wage_amount' => 5340000.00,
        ]);

        // 2. Run Audit
        $audit = $complianceService->checkMinimumWageCompliance($this->tenant->id, 'Jakarta Pusat');
        $this->assertEquals(2, $audit['total_employees']);
        $this->assertEquals(2, $audit['compliant_count']); // Budi: 12M, Ani: 25M
        $this->assertEquals(100.0, $audit['compliance_rate_percentage']);

        // 3. Test non-compliant employee
        $lowWageEmp = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_number' => 'EMP-003',
            'first_name' => 'Karyawan',
            'last_name' => 'Magang',
            'email' => 'magang@nusantara.id',
            'employment_status' => 'pkwt',
            'join_date' => '2026-01-01',
            'basic_salary' => 4500000.00, // Below 5.34M UMK
            'status' => 'active',
        ]);

        $auditAfter = $complianceService->checkMinimumWageCompliance($this->tenant->id, 'Jakarta Pusat');
        $this->assertEquals(1, $auditAfter['non_compliant_count']);
        $this->assertEquals(840000.00, $auditAfter['non_compliant_employees'][0]['deficit']);
    }

    /**
     * 7. Test BPJS Statutory Compliance Reporting (SIPP Online & e-Dabu).
     */
    public function test_bpjs_tk_sipp_and_kes_edabu_report_generation(): void
    {
        $this->actingAs($this->hrUser);
        $complianceService = app(RegulatoryComplianceService::class);

        // 1. Generate BPJS TK SIPP Report
        $this->post(route('company.compliance.bpjs.generate'), [
            'payroll_period_id' => $this->payrollPeriod->id,
            'report_type' => 'bpjs_tk_sipp',
        ])->assertRedirect();

        $reportTk = BpjsComplianceReport::where('report_type', 'bpjs_tk_sipp')->first();
        $this->assertNotNull($reportTk);
        $this->assertEquals(1108000.00, $reportTk->total_contribution);

        // 2. Generate BPJS Kes e-Dabu Report
        $this->post(route('company.compliance.bpjs.generate'), [
            'payroll_period_id' => $this->payrollPeriod->id,
            'report_type' => 'bpjs_kes_edabu',
        ])->assertRedirect();

        $reportKes = BpjsComplianceReport::where('report_type', 'bpjs_kes_edabu')->first();
        $this->assertNotNull($reportKes);
        $this->assertEquals(600000.00, $reportKes->total_contribution);

        // 3. Test CSV content
        $csvTk = $complianceService->exportBpjsTkCsv($this->payrollPeriod);
        $this->assertStringContainsString('KPJ_BPJS_TK', $csvTk);
        $this->assertStringContainsString('BUDI SANTOSO', $csvTk);

        $csvKes = $complianceService->exportBpjsKesCsv($this->payrollPeriod);
        $this->assertStringContainsString('NO_KARTU_BPJS_KES', $csvKes);
    }

    /**
     * 8. Test Branch QR Touchless Attendance Kiosk & Geofenced Clock-in.
     */
    public function test_branch_qr_kiosk_generation_and_touchless_clock_in(): void
    {
        $qrService = app(QrAttendanceService::class);

        // 1. Create Kiosk
        $kiosk = $qrService->generateOrRefreshKiosk($this->branch, 'Lobby Tower A');
        $this->assertNotNull($kiosk->secret_token);
        $this->assertStringStartsWith('RIHRIS-KIOSK:', $kiosk->current_qr_code);

        // 2. Clock in within geofence (-6.2088000, 106.8456000)
        $clockResult = $qrService->recordQrAttendance(
            $this->employeeBudi,
            $kiosk->current_qr_code,
            -6.2088100,
            106.8456100
        );

        $this->assertTrue($clockResult['success']);
        $this->assertEquals('clock_in', $clockResult['action']);
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employeeBudi->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        // 3. Test out of geofence rejection
        $farResult = $qrService->recordQrAttendance(
            $this->employeeBudi,
            $kiosk->current_qr_code,
            -6.9000000, // Bandung (out of Jakarta geofence)
            107.6000000
        );
        $this->assertFalse($farResult['success']);
        $this->assertStringContainsString('di luar radius kantor', $farResult['message']);
    }

    /**
     * 9. Test Employee Self-Service (ESS) Portal Access to Phase 10 Features.
     */
    public function test_employee_can_access_phase10_features_in_ess(): void
    {
        $this->actingAs($this->empUser);

        // 1. Employee can add Family in ESS
        $this->post(route('ess.profile.family'), [
            'name' => 'Ahmad Santoso',
            'relationship' => 'child',
            'gender' => 'male',
            'birth_date' => '2023-01-10',
        ])->assertRedirect();

        $this->assertDatabaseHas('employee_families', [
            'employee_id' => $this->employeeBudi->id,
            'name' => 'Ahmad Santoso',
            'relationship' => 'child',
        ]);

        // 2. Employee can add Education in ESS
        $this->post(route('ess.profile.education'), [
            'degree_level' => 'S1/D4',
            'institution_name' => 'Universitas Gadjah Mada',
            'major' => 'Sistem Informasi',
            'graduation_year' => 2020,
        ])->assertRedirect();

        $this->assertDatabaseHas('employee_educations', [
            'employee_id' => $this->employeeBudi->id,
            'institution_name' => 'Universitas Gadjah Mada',
        ]);

        // 3. ESS Dashboard renders properly
        $response = $this->get(route('ess.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Review 360° & PIP');
        $response->assertSee('Keluarga & Edukasi');
    }
}
