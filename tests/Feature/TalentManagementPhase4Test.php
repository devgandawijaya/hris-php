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
use App\Models\JobPosting;
use App\Models\JobCandidate;
use App\Models\JobApplication;
use App\Models\CompanyAsset;
use App\Models\OnboardingOffboardingChecklist;
use App\Models\ExitInterview;
use App\Models\PerformanceCycle;
use App\Models\KpiGoal;
use App\Models\PerformanceReview;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use App\Services\Talent\RecruitmentService;
use App\Services\Talent\OnboardingOffboardingService;
use App\Services\Talent\PerformanceService;
use App\Services\Talent\TrainingService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class TalentManagementPhase4Test extends TestCase
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
            'name' => 'PT Solusi Talenta Nusantara',
            'slug' => 'solusi-talenta',
            'status' => 'active',
        ]);

        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Solusi Talenta Nusantara',
            'email' => 'admin@solusitalenta.id',
            'npwp' => '01.222.333.4-012.000',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Setup HR User
        $this->hrUser = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@solusitalenta.id',
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
            'title' => 'Software Engineer',
            'job_level' => 'Senior',
        ]);

        // 4. Setup Employee User & Employee Record
        $this->empUser = User::create([
            'name' => 'Rangga Perkasa',
            'email' => 'rangga@solusitalenta.id',
            'password' => bcrypt('password'),
            'active_tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $employeeRole = Role::where('slug', 'employee')->first();
        $this->empUser->tenants()->attach($this->tenant->id, ['role_id' => $employeeRole?->id, 'is_owner' => false]);
        $this->empUser->assignRole($employeeRole, $this->tenant->id);

        $this->employee = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->empUser->id,
            'employee_number' => 'STN-001',
            'nik_ktp' => '3171012304920001',
            'first_name' => 'Rangga',
            'last_name' => 'Perkasa',
            'email' => 'rangga@solusitalenta.id',
            'phone' => '+6281234567890',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'pkwtt',
            'join_date' => '2024-01-01',
            'basic_salary' => 12000000.00,
            'status' => 'active',
        ]);
    }

    /**
     * Test 1: Recruitment Job Posting, Application Pipeline, and Conversion to Employee.
     */
    public function test_recruitment_lifecycle_and_conversion_to_employee(): void
    {
        /** @var RecruitmentService $service */
        $service = app(RecruitmentService::class);

        // 1. Create Job Posting
        $posting = $service->createPosting($this->tenant->id, [
            'title' => 'Fullstack Laravel Developer',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_type' => 'full_time',
            'location' => 'Jakarta / Hybrid',
            'description' => 'Membangun aplikasi HRIS SaaS skala enterprise.',
            'requirements' => 'Pengalaman Laravel & PostgreSQL.',
            'salary_min' => 10000000.00,
            'salary_max' => 15000000.00,
            'deadline' => now()->addDays(30)->toDateString(),
        ]);

        $this->assertDatabaseHas('job_postings', [
            'id' => $posting->id,
            'title' => 'Fullstack Laravel Developer',
            'status' => 'published',
        ]);

        // 2. Candidate Applies
        $application = $service->applyCandidate($posting, [
            'first_name' => 'Bintang',
            'last_name' => 'Ramadhan',
            'email' => 'bintang@example.com',
            'phone' => '+62899123456',
            'linkedin_url' => 'https://linkedin.com/in/bintangramadhan',
            'notes' => 'Pengalaman 5 tahun di bidang backend.',
        ]);

        $this->assertDatabaseHas('job_candidates', [
            'email' => 'bintang@example.com',
            'first_name' => 'Bintang',
        ]);

        $this->assertEquals('applied', $application->stage);

        // 3. Advance Candidate to Interview and Offered
        $service->updateApplicationStage($application, 'interview', [
            'interview_scheduled_at' => now()->addDays(2),
            'interview_notes' => 'Hasil tes coding sangat memuaskan.',
            'interview_score' => 92.50,
        ]);
        $this->assertEquals('interview', $application->fresh()->stage);

        $service->updateApplicationStage($application, 'offered', [
            'offered_salary' => 14000000.00,
        ]);
        $this->assertEquals('offered', $application->fresh()->stage);

        // 4. Convert Hired Candidate to Employee
        $newEmployee = $service->convertToEmployee($application, [
            'employee_number' => 'STN-002',
            'basic_salary' => 14000000.00,
            'join_date' => now()->toDateString(),
        ]);

        $this->assertNotNull($newEmployee);
        $this->assertEquals('Bintang', $newEmployee->first_name);
        $this->assertEquals('Ramadhan', $newEmployee->last_name);
        $this->assertEquals('active', $newEmployee->status);
        $this->assertEquals('hired', $application->fresh()->stage);
        $this->assertEquals($newEmployee->id, $application->fresh()->converted_employee_id);
    }

    /**
     * Test 2: Onboarding & Offboarding Checklist Workflow.
     */
    public function test_onboarding_and_offboarding_checklist_workflow(): void
    {
        /** @var OnboardingOffboardingService $service */
        $service = app(OnboardingOffboardingService::class);

        // 1. Initialize Onboarding
        $service->initializeOnboarding($this->employee, $this->hrUser);

        $tasks = OnboardingOffboardingChecklist::where('employee_id', $this->employee->id)
            ->where('type', 'onboarding')
            ->get();

        $this->assertCount(6, $tasks);
        $this->assertFalse($tasks->first()->is_completed);

        // 2. Complete Task
        $firstTask = $tasks->first();
        $service->completeTask($firstTask, 'Berkas KTP & NPWP telah diverifikasi lengkap.');

        $firstTask->refresh();
        $this->assertTrue($firstTask->is_completed);
        $this->assertNotNull($firstTask->completed_at);
        $this->assertStringContainsString('Berkas KTP', $firstTask->notes);

        // 3. Initialize Offboarding
        $service->initializeOffboarding($this->employee, $this->hrUser);

        $offboardingTasks = OnboardingOffboardingChecklist::where('employee_id', $this->employee->id)
            ->where('type', 'offboarding')
            ->get();

        $this->assertCount(6, $offboardingTasks);

        // 4. Record Exit Interview
        $exitInterview = $service->recordExitInterview($this->employee, [
            'interview_date' => now()->toDateString(),
            'reason_for_leaving' => 'career_change',
            'feedback' => 'Lingkungan kerja sangat suportif dan profesional.',
            'willing_to_recommend' => true,
            'interviewer_user_id' => $this->hrUser->id,
        ]);

        $this->assertDatabaseHas('exit_interviews', [
            'id' => $exitInterview->id,
            'employee_id' => $this->employee->id,
            'reason_for_leaving' => 'career_change',
            'willing_to_recommend' => true,
        ]);
    }

    /**
     * Test 3: Company Asset Handover & Return Management.
     */
    public function test_company_asset_handover_and_return(): void
    {
        /** @var OnboardingOffboardingService $service */
        $service = app(OnboardingOffboardingService::class);

        // 1. Register Asset
        $asset = $service->createAsset($this->tenant->id, [
            'asset_code' => 'NB-MAC-099',
            'name' => 'MacBook Pro 16 M3 Max',
            'category' => 'laptop',
            'serial_number' => 'C02ABC123XYZ',
            'notes' => 'Perangkat kerja engineer.',
        ]);

        $this->assertEquals('available', $asset->status);
        $this->assertNull($asset->assigned_employee_id);

        // 2. Assign Asset to Employee
        $service->assignAsset($asset, $this->employee, now()->toDateString());

        $asset->refresh();
        $this->assertEquals('assigned', $asset->status);
        $this->assertEquals($this->employee->id, $asset->assigned_employee_id);
        $this->assertNotNull($asset->assigned_date);

        // 3. Return Asset
        $service->returnAsset($asset, 'Dikembalikan dalam kondisi sangat baik tanpa cacat.');

        $asset->refresh();
        $this->assertEquals('available', $asset->status);
        $this->assertNull($asset->assigned_employee_id);
        $this->assertNull($asset->assigned_date);
    }

    /**
     * Test 4: Performance Management (Cycle, KPI / OKR Goals, and 30/70 Weighted Appraisal).
     */
    public function test_performance_cycle_kpi_and_weighted_appraisal_scoring(): void
    {
        /** @var PerformanceService $service */
        $service = app(PerformanceService::class);

        // 1. Create Performance Cycle
        $cycle = $service->createCycle($this->tenant->id, [
            'name' => 'Penilaian Semester 1 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
        ]);

        $this->assertEquals('active', $cycle->status);

        // 2. Add KPI Goal
        $goal = $service->addGoal($this->employee, [
            'performance_cycle_id' => $cycle->id,
            'type' => 'kpi',
            'title' => 'SLA Respon Bug & Incident',
            'target_value' => 100.00,
            'current_value' => 60.00,
            'unit' => '%',
            'weight' => 50.00,
        ]);

        $this->assertEquals('in_progress', $goal->status);

        // 3. Update KPI Progress to 100%
        $service->updateGoalProgress($goal, 100.00);
        $this->assertEquals('achieved', $goal->fresh()->status);
        $this->assertEquals(100.00, $goal->fresh()->current_value);

        // 4. Employee Submits Self-Review (Rating = 90.0)
        $review = $service->submitSelfReview($cycle, $this->employee, 90.0, 'Semua tiket terselesaikan tepat waktu.');
        $this->assertEquals('self_reviewed', $review->status);
        $this->assertEquals(90.0, $review->self_rating);

        // 5. Manager Submits Appraisal (Rating = 80.0)
        // Weighted formula: (Self 90.0 * 0.3) + (Manager 80.0 * 0.7) = 27.0 + 56.0 = 83.0
        $finalReview = $service->submitManagerReview($cycle, $this->employee, $this->hrUser, 80.0, 'Pekerjaan rapi dan memuaskan.');

        $this->assertEquals('completed', $finalReview->status);
        $this->assertEquals(80.0, $finalReview->manager_rating);
        $this->assertEquals(83.0, (float) $finalReview->final_score);
    }

    /**
     * Test 5: Training Course Enrollment, Completion, and Certificate Issuance.
     */
    public function test_training_enrollment_and_certificate_generation(): void
    {
        /** @var TrainingService $service */
        $service = app(TrainingService::class);

        // 1. Create Training Course
        $course = $service->createCourse($this->tenant->id, [
            'title' => 'Cloud Native Architecture with Kubernetes',
            'trainer_name' => 'DevOps Master',
            'category' => 'technical',
            'location' => 'Online Zoom',
            'start_date' => now()->addDays(5)->toDateTimeString(),
            'end_date' => now()->addDays(7)->toDateTimeString(),
            'capacity' => 30,
            'description' => 'Panduan deployment microservices scalable.',
        ]);

        $this->assertDatabaseHas('training_courses', [
            'id' => $course->id,
            'title' => 'Cloud Native Architecture with Kubernetes',
            'status' => 'scheduled',
        ]);

        // 2. Enroll Employee
        $enrollment = $service->enrollEmployee($course, $this->employee);
        $this->assertEquals('enrolled', $enrollment->status);

        // 3. Complete Enrollment with Score 95.5
        $completed = $service->completeEnrollment($enrollment, 95.5, 'Lulus dengan nilai sangat memuaskan.');

        $this->assertEquals('completed', $completed->status);
        $this->assertEquals(95.5, (float) $completed->score);
        $this->assertNotNull($completed->certificate_number);

        // Expected format: CERT-YYYY-{course_id}-{employee_id}
        $expectedCertPrefix = 'CERT-' . date('Y') . '-' . str_pad($course->id, 4, '0', STR_PAD_LEFT);
        $this->assertStringStartsWith($expectedCertPrefix, $completed->certificate_number);
    }

    /**
     * Test 6: Web Routes Accessibility for HR and Employee Portals.
     */
    public function test_talent_management_web_views_return_ok(): void
    {
        // 1. HR Manager Views
        $this->actingAs($this->hrUser);

        // Recruitment Index
        $response = $this->get(route('company.recruitment.index'));
        $response->assertStatus(200);

        // Create a sample job posting for Show page
        $jobPosting = JobPosting::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'title' => 'QA Automation Lead',
            'description' => 'Deskripsi lowongan.',
            'status' => 'published',
        ]);

        $response = $this->get(route('company.recruitment.show', $jobPosting->id));
        $response->assertStatus(200);

        // Onboarding / Assets Index
        $response = $this->get(route('company.onboarding.index'));
        $response->assertStatus(200);

        // Performance Index
        $response = $this->get(route('company.performance.index'));
        $response->assertStatus(200);

        // Training Index
        $response = $this->get(route('company.training.index'));
        $response->assertStatus(200);

        // 2. Employee Self-Service Dashboard (with Talent Tabs)
        $this->actingAs($this->empUser);

        $response = $this->get(route('ess.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Kinerja & Evaluasi');
        $response->assertSee('Aset & Pelatihan');
    }
}

