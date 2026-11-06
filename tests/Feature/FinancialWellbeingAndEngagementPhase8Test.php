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
use App\Models\EmployeeLoan;
use App\Models\LoanInstallment;
use App\Models\EmployeeSurvey;
use App\Models\EmployeeRecognition;
use App\Models\CompanyEvent;
use App\Services\Finance\EmployeeLoanService;
use App\Services\Engagement\SurveyAndRecognitionService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinancialWellbeingAndEngagementPhase8Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $hrUser;
    protected User $empUser;
    protected Employee $employee;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // 1. Tenant & Company
        $this->tenant = Tenant::create([
            'name' => 'PT Mandiri Finansial Bersama',
            'slug' => 'mandiri-finansial',
            'status' => 'active',
        ]);

        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Mandiri Finansial Bersama',
            'email' => 'admin@mandirifinansial.id',
            'npwp' => '01.777.666.5-012.000',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Department
        $this->department = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Finance & Accounting',
            'code' => 'FIN',
        ]);

        // 3. Users & Employee
        $hrRole = Role::where('slug', 'hr_manager')->first();
        $empRole = Role::where('slug', 'employee')->first();

        $this->hrUser = User::create([
            'name' => 'Dewi Lestari, S.E.',
            'email' => 'dewi@mandirifinansial.id',
            'password' => bcrypt('password123'),
            'role_id' => $hrRole->id,
            'is_super_admin' => false,
        ]);
        $this->hrUser->tenants()->attach($this->tenant->id);

        $this->empUser = User::create([
            'name' => 'Rizki Pratama',
            'email' => 'rizki@mandirifinansial.id',
            'password' => bcrypt('password123'),
            'role_id' => $empRole->id,
            'is_super_admin' => false,
        ]);
        $this->empUser->tenants()->attach($this->tenant->id);

        $this->employee = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->empUser->id,
            'department_id' => $this->department->id,
            'employee_code' => 'EMP-FIN-088',
            'employee_number' => 'EMP-FIN-088',
            'full_name' => 'Rizki Pratama',
            'email' => 'rizki@mandirifinansial.id',
            'join_date' => '2023-05-10',
            'employment_status' => 'permanent',
            'status' => 'active',
            'base_salary' => 9000000.00,
        ]);
    }

    /**
     * Test 1: Employee applies loan and HR approves with automatic installment schedule
     */
    public function test_employee_can_apply_loan_and_hr_can_approve_with_installment_schedule(): void
    {
        $service = app(EmployeeLoanService::class);

        $loan = $service->applyLoan($this->employee, [
            'loan_type' => 'salary_advance',
            'amount' => 3000000.00,
            'term_months' => 3,
            'reason' => 'Kasbon darurat renovasi pipa air.',
        ]);

        $this->assertNotNull($loan);
        $this->assertEquals('pending', $loan->status);
        $this->assertEquals(1000000.00, (float)$loan->monthly_installment);

        // HR Approves & Disburses
        $service->approveAndDisburse($loan, $this->hrUser);

        $freshLoan = $loan->fresh();
        $this->assertEquals('active', $freshLoan->status);
        $this->assertNotNull($freshLoan->disbursed_at);

        // Verify 3 installments created
        $this->assertCount(3, $freshLoan->installments);
        $this->assertEquals(1000000.00, (float)$freshLoan->installments->first()->amount);
    }

    /**
     * Test 2: Installment repayment reduces remaining debt and completes loan
     */
    public function test_installment_repayment_reduces_remaining_loan_and_completes(): void
    {
        $service = app(EmployeeLoanService::class);

        $loan = $service->applyLoan($this->employee, [
            'loan_type' => 'emergency',
            'amount' => 1000000.00,
            'term_months' => 1,
            'reason' => 'Biaya pengobatan darurat.',
        ]);
        $service->approveAndDisburse($loan, $this->hrUser);

        $installment = $loan->installments->first();
        $service->recordRepayment($installment, true);

        $freshLoan = $loan->fresh();
        $this->assertEquals(0.00, (float)$freshLoan->remaining_amount);
        $this->assertEquals('completed', $freshLoan->status);
        $this->assertEquals('deducted_from_payroll', $installment->fresh()->status);
    }

    /**
     * Test 3: Survey creation, employee responses, and eNPS calculation
     */
    public function test_survey_creation_and_enps_calculation(): void
    {
        $service = app(SurveyAndRecognitionService::class);

        $survey = $service->createSurvey($this->tenant->id, [
            'title' => 'Survei Kepuasan Lingkungan Kerja 2026',
            'survey_type' => 'enps',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'is_anonymous' => true,
        ], $this->hrUser);

        $this->assertNotNull($survey);
        $this->assertEquals('active', $survey->status);

        // Submit 2 responses: 1 promoter (score 10) & 1 passive (score 8)
        $service->submitSurveyResponse($survey, $this->employee, [
            'enps_score' => 10,
            'satisfaction_rating' => 5,
            'feedback' => 'Sangat puas dengan fasilitas kantor.',
        ]);
        $service->submitSurveyResponse($survey, null, [
            'enps_score' => 8,
            'satisfaction_rating' => 4,
            'feedback' => 'Cukup baik.',
        ]);

        $enps = $survey->calculateEnps();
        $this->assertEquals(2, $enps['total_responses']);
        $this->assertEquals(1, $enps['promoters']);
        $this->assertEquals(1, $enps['passives']);
        $this->assertEquals(0, $enps['detractors']);
        // eNPS = (1 - 0) / 2 * 100 = +50
        $this->assertEquals(50.0, $enps['enps_score']);
    }

    /**
     * Test 4: Grant recognition kudos to employee
     */
    public function test_grant_recognition_kudos_and_send_notification(): void
    {
        $service = app(SurveyAndRecognitionService::class);

        $recognition = $service->grantRecognition($this->employee, $this->hrUser, [
            'badge_type' => 'star_performer',
            'title' => 'Kinerja Finansial Terbaik',
            'message' => 'Laporan audit keuangan diselesaikan tepat waktu tanpa temuan.',
            'reward_amount' => 1000000.00,
        ]);

        $this->assertNotNull($recognition);
        $this->assertDatabaseHas('employee_recognitions', [
            'id' => $recognition->id,
            'employee_id' => $this->employee->id,
            'badge_type' => 'star_performer',
        ]);

        // Verify in-app notification delivered
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->empUser->id,
            'type' => 'success',
        ]);
    }

    /**
     * Test 5: Create company event
     */
    public function test_create_company_event(): void
    {
        $service = app(SurveyAndRecognitionService::class);

        $event = $service->createCompanyEvent($this->tenant->id, [
            'title' => 'Annual Strategy Gathering 2026',
            'event_type' => 'gathering',
            'start_time' => now()->addDays(5)->toDateTimeString(),
            'end_time' => now()->addDays(5)->addHours(4)->toDateTimeString(),
            'location' => 'Grand Ballroom Menara Batavia',
        ], $this->hrUser);

        $this->assertNotNull($event);
        $this->assertDatabaseHas('company_events', [
            'id' => $event->id,
            'event_type' => 'gathering',
        ]);
    }

    /**
     * Test 6: Web routes accessibility
     */
    public function test_web_routes_accessibility_for_loans_and_engagement(): void
    {
        $this->actingAs($this->hrUser);

        $resLoans = $this->get(route('company.loans.index'));
        $resLoans->assertStatus(200);

        $resEngage = $this->get(route('company.engagement.index'));
        $resEngage->assertStatus(200);

        $this->actingAs($this->empUser);
        $resEss = $this->get(route('ess.dashboard'));
        $resEss->assertStatus(200);
    }
}

