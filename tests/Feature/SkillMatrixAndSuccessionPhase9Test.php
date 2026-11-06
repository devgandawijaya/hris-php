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
use App\Models\SkillCategory;
use App\Models\Skill;
use App\Models\PositionSkillRequirement;
use App\Models\EmployeeSkill;
use App\Models\SuccessionPlan;
use App\Models\SuccessionCandidate;
use App\Models\TrainingCourse;
use App\Models\EmployeeWarning;
use App\Models\EmployeeLoan;
use App\Models\LoanInstallment;
use App\Models\CronExecutionLog;
use App\Services\Talent\SkillMatrixService;
use App\Services\Talent\SuccessionService;
use App\Services\Automation\CronAutomationService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class SkillMatrixAndSuccessionPhase9Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $hrUser;
    protected User $empUser;
    protected Employee $employeeBudi;
    protected Employee $employeeSiti;
    protected Department $department;
    protected Position $positionDev;
    protected Position $positionLead;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // 1. Tenant & Company
        $this->tenant = Tenant::create([
            'name' => 'PT Talenta Sukses Nusantara',
            'slug' => 'talenta-sukses',
            'status' => 'active',
        ]);

        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Talenta Sukses Nusantara',
            'email' => 'hr@talentasukses.id',
            'npwp' => '01.555.444.3-012.000',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Department & Positions
        $this->department = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Information Technology',
            'code' => 'IT',
        ]);

        $this->positionDev = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'Software Engineer',
            'job_level' => 'Senior',
        ]);

        $this->positionLead = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'Chief Technology Officer',
            'job_level' => 'Executive',
        ]);

        // 3. Users & Employees
        $hrRole = Role::where('slug', 'hr_manager')->first();
        $empRole = Role::where('slug', 'employee')->first();

        $this->hrUser = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@talentasukses.id',
            'password' => bcrypt('password123'),
        ]);
        $this->hrUser->tenants()->attach($this->tenant->id, ['is_owner' => false, 'role_id' => $hrRole->id]);

        $this->empUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@talentasukses.id',
            'password' => bcrypt('password123'),
        ]);
        $this->empUser->tenants()->attach($this->tenant->id, ['is_owner' => false, 'role_id' => $empRole->id]);

        $this->employeeBudi = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->empUser->id,
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'employee_code' => 'EMP-001',
            'nik' => '3171012304920005',
            'email' => 'budi@talentasukses.id',
            'phone' => '+628123456789',
            'department_id' => $this->department->id,
            'position_id' => $this->positionDev->id,
            'employment_status' => 'permanent',
            'join_date' => Carbon::now()->subYears(2),
            'basic_salary' => 15000000.00,
            'status' => 'active',
        ]);

        $this->employeeSiti = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'first_name' => 'Siti',
            'last_name' => 'Rahmawati',
            'employee_code' => 'EMP-002',
            'nik' => '3171012304920006',
            'email' => 'siti@talentasukses.id',
            'phone' => '+628123456780',
            'department_id' => $this->department->id,
            'position_id' => $this->positionDev->id,
            'employment_status' => 'contract',
            'join_date' => Carbon::now()->subMonths(11),
            'end_date' => Carbon::now()->addDays(20), // expires in 20 days (PP 35/2021)
            'basic_salary' => 12000000.00,
            'status' => 'active',
        ]);
    }

    public function test_competency_dictionary_and_position_requirements_creation(): void
    {
        $this->actingAs($this->hrUser);

        // 1. Create Skill Category
        $catResponse = $this->post(route('company.skills.categories.store'), [
            'name' => 'Rekayasa Perangkat Lunak',
            'description' => 'Keahlian teknis pengembangan aplikasi.',
        ]);
        $catResponse->assertRedirect(route('company.skills.index'));
        $this->assertDatabaseHas('skill_categories', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Rekayasa Perangkat Lunak',
        ]);
        $cat = SkillCategory::where('name', 'Rekayasa Perangkat Lunak')->first();

        // 2. Create Skill in Dictionary
        $skillResponse = $this->post(route('company.skills.skills.store'), [
            'skill_category_id' => $cat->id,
            'name' => 'PHP Laravel Architecture',
            'code' => 'TECH-LARAVEL',
            'description' => 'Penguasaan Laravel 11 dan domain services.',
        ]);
        $skillResponse->assertRedirect(route('company.skills.index'));
        $this->assertDatabaseHas('skills', [
            'tenant_id' => $this->tenant->id,
            'name' => 'PHP Laravel Architecture',
        ]);
        $skill = Skill::where('name', 'PHP Laravel Architecture')->first();

        // 3. Define Position Requirement (Target Level: 4 / Ahli)
        $reqResponse = $this->post(route('company.skills.requirements.store'), [
            'position_id' => $this->positionDev->id,
            'skill_id' => $skill->id,
            'required_level' => 4,
            'is_mandatory' => 1,
            'notes' => 'Standar wajib untuk Senior Software Engineer.',
        ]);
        $reqResponse->assertRedirect(route('company.skills.index'));
        $this->assertDatabaseHas('position_skill_requirements', [
            'tenant_id' => $this->tenant->id,
            'position_id' => $this->positionDev->id,
            'skill_id' => $skill->id,
            'required_level' => 4,
            'is_mandatory' => true,
        ]);
    }

    public function test_employee_skill_assessment_and_gap_analysis(): void
    {
        $this->actingAs($this->hrUser);

        // Setup Category & Skills
        $cat = SkillCategory::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Teknis Cloud',
        ]);
        $skill1 = Skill::create([
            'tenant_id' => $this->tenant->id,
            'skill_category_id' => $cat->id,
            'name' => 'Docker & Kubernetes',
            'code' => 'TECH-K8S',
        ]);
        $skill2 = Skill::create([
            'tenant_id' => $this->tenant->id,
            'skill_category_id' => $cat->id,
            'name' => 'PostgreSQL High Availability',
            'code' => 'TECH-PG',
        ]);

        // Position requires: K8S Lv 4, PG Lv 3
        PositionSkillRequirement::create([
            'tenant_id' => $this->tenant->id,
            'position_id' => $this->positionDev->id,
            'skill_id' => $skill1->id,
            'required_level' => 4,
            'is_mandatory' => true,
        ]);
        PositionSkillRequirement::create([
            'tenant_id' => $this->tenant->id,
            'position_id' => $this->positionDev->id,
            'skill_id' => $skill2->id,
            'required_level' => 3,
            'is_mandatory' => true,
        ]);

        // Assess Employee: Budi has K8S Lv 3 (Gap = 1) and PG Lv 4 (Met, points = 3)
        $assessResponse1 = $this->post(route('company.skills.assess.store'), [
            'employee_id' => $this->employeeBudi->id,
            'skill_id' => $skill1->id,
            'current_level' => 3,
            'certification_name' => 'Certified Kubernetes Administrator (CKA)',
            'assessment_notes' => 'Mampu mengelola pod dan cluster ingress.',
        ]);
        $assessResponse1->assertRedirect(route('company.skills.index'));

        $assessResponse2 = $this->post(route('company.skills.assess.store'), [
            'employee_id' => $this->employeeBudi->id,
            'skill_id' => $skill2->id,
            'current_level' => 4,
            'assessment_notes' => 'Sangat menguasai indexing dan partitioning.',
        ]);
        $assessResponse2->assertRedirect(route('company.skills.index'));

        // Test SkillMatrixService directly
        $service = app(SkillMatrixService::class);
        $gapRes = $service->calculateEmployeeSkillGap($this->employeeBudi);

        $this->assertTrue($gapRes['has_requirements']);
        $this->assertEquals(2, $gapRes['total_requirements']);
        $this->assertEquals(1, $gapRes['met_requirements']); // skill2 is met, skill1 has gap of 1
        // Total points required: 4 + 3 = 7. Total points earned: 3 + 3 = 6. Match = 6/7 * 100 = 85.7%
        $this->assertEquals(85.7, $gapRes['overall_match_percentage']);

        // Test live Gap API endpoint
        $apiResponse = $this->get(route('company.skills.gap', $this->employeeBudi->id));
        $apiResponse->assertOk();
        $apiResponse->assertJsonPath('employee.id', $this->employeeBudi->id);
        $apiResponse->assertJsonPath('analysis.total_requirements', 2);
    }

    public function test_skill_matrix_recommends_training_for_gaps(): void
    {
        $cat = SkillCategory::create(['tenant_id' => $this->tenant->id, 'name' => 'Leadership']);
        $skill = Skill::create(['tenant_id' => $this->tenant->id, 'skill_category_id' => $cat->id, 'name' => 'Strategic Decision Making']);

        PositionSkillRequirement::create([
            'tenant_id' => $this->tenant->id,
            'position_id' => $this->positionDev->id,
            'skill_id' => $skill->id,
            'required_level' => 4,
        ]);

        // Employee has level 1 (gap = 3)
        EmployeeSkill::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employeeBudi->id,
            'skill_id' => $skill->id,
            'current_level' => 1,
        ]);

        // LMS Training course in system
        $course = TrainingCourse::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Executive Leadership & Decision Making Workshop',
            'trainer_name' => 'Dr. H. Bambang',
            'category' => 'leadership',
            'location' => 'Auditorium',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(6),
            'status' => 'scheduled',
        ]);

        $service = app(SkillMatrixService::class);
        $gapRes = $service->calculateEmployeeSkillGap($this->employeeBudi);

        $this->assertNotEmpty($gapRes['recommended_trainings']);
        $this->assertEquals($course->id, $gapRes['recommended_trainings']->first()->id);
    }

    public function test_succession_plan_and_candidate_nomination(): void
    {
        $this->actingAs($this->hrUser);

        // 1. Create Succession Plan for Key Position (CTO)
        $planResponse = $this->post(route('company.succession.plans.store'), [
            'position_id' => $this->positionLead->id,
            'current_holder_employee_id' => null,
            'criticality_level' => 'critical',
            'emergency_replacement_plan' => 'Lead Engineer mengambil alih arsitektur darurat jika lowong.',
            'notes' => 'Posisi vital untuk kepemimpinan tim teknologi.',
        ]);
        $planResponse->assertRedirect(route('company.succession.index'));
        $this->assertDatabaseHas('succession_plans', [
            'tenant_id' => $this->tenant->id,
            'position_id' => $this->positionLead->id,
            'criticality_level' => 'critical',
        ]);
        $plan = SuccessionPlan::where('position_id', $this->positionLead->id)->first();

        // 2. Nominate Successor (Budi as Ready Now)
        $nominateResponse = $this->post(route('company.succession.candidates.store'), [
            'succession_plan_id' => $plan->id,
            'candidate_employee_id' => $this->employeeBudi->id,
            'readiness_level' => 'ready_now',
            'strengths' => 'Kecakapan teknis luar biasa dan kepemimpinan agile.',
            'development_needs' => 'Perlu bimbingan aspek komersial dan anggaran P&L.',
        ]);
        $nominateResponse->assertRedirect(route('company.succession.index'));
        $this->assertDatabaseHas('succession_candidates', [
            'tenant_id' => $this->tenant->id,
            'succession_plan_id' => $plan->id,
            'candidate_employee_id' => $this->employeeBudi->id,
            'readiness_level' => 'ready_now',
        ]);

        // Check succession pipeline calculation
        $service = app(SuccessionService::class);
        $pipeline = $service->getSuccessionPipeline($this->tenant->id);

        $this->assertEquals(1, $pipeline['total_plans']);
        $this->assertEquals(1, $pipeline['covered_count']);
        $this->assertEquals(0, $pipeline['uncovered_count']);
        $this->assertEquals(100.0, $pipeline['bench_strength_percentage']);
    }

    public function test_algorithmic_successor_suggestion_api(): void
    {
        $this->actingAs($this->hrUser);

        $response = $this->get(route('company.succession.suggest', $this->positionLead->id));
        $response->assertOk();
        $response->assertJsonStructure([
            'position' => ['id', 'title', 'department'],
            'suggestions',
        ]);
    }

    public function test_automated_cron_tasks_execution(): void
    {
        $automationService = app(CronAutomationService::class);

        // 1. Test checkContractExpirations (employeeSiti ends in 20 days <= 30 days)
        $resContract = $automationService->checkContractExpirations();
        $this->assertEquals('success', $resContract['status']);
        $this->assertGreaterThanOrEqual(1, $resContract['items_processed']);
        $this->assertDatabaseHas('cron_execution_logs', [
            'command_name' => 'hris:check-contract-expirations',
            'status' => 'success',
        ]);

        // 2. Test expireWarningLetters (Pasal 161 UU 13/2003: 6 months limit)
        $oldWarning = EmployeeWarning::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employeeBudi->id,
            'sp_number' => 'SP1/DIR-HR/2025/001',
            'level' => 1,
            'reason' => 'Keterlambatan berulang',
            'issued_date' => Carbon::now()->subMonths(7),
            'valid_until' => Carbon::now()->subMonths(1), // expired 1 month ago
            'status' => 'active',
        ]);

        $resSP = $automationService->expireWarningLetters();
        $this->assertEquals('success', $resSP['status']);
        $this->assertEquals(1, $resSP['items_processed']);
        $oldWarning->refresh();
        $this->assertEquals('expired', $oldWarning->status);

        // 3. Test checkProbationExpirations (Pasal 60 UU Ketenagakerjaan)
        $resProbation = $automationService->checkProbationExpirations();
        $this->assertEquals('success', $resProbation['status']);

        // 4. Test processLoanInstallments (PP 36/2021)
        $loan = EmployeeLoan::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employeeBudi->id,
            'loan_number' => 'LOAN-2026-0001',
            'type' => 'kasbon',
            'amount' => 2000000.00,
            'installments_count' => 2,
            'installment_amount' => 1000000.00,
            'remaining_amount' => 2000000.00,
            'reason' => 'Kebutuhan mendesak',
            'status' => 'approved',
        ]);
        LoanInstallment::create([
            'tenant_id' => $this->tenant->id,
            'employee_loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000.00,
            'due_date' => Carbon::now()->addDays(3), // due in 3 days
            'status' => 'pending',
        ]);

        $resLoan = $automationService->processLoanInstallments();
        $this->assertEquals('success', $resLoan['status']);
        $this->assertGreaterThanOrEqual(1, $resLoan['items_processed']);
    }

    public function test_employee_can_view_personal_skills_in_ess_dashboard(): void
    {
        $this->actingAs($this->empUser);

        $response = $this->get(route('ess.dashboard'));
        $response->assertOk();
        $response->assertViewHas('mySkillAnalysis');
        $response->assertSee('Keahlian & Karir');
        $response->assertSee('Chief Technology Officer'); // or Software Engineer
    }
}
