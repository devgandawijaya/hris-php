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
use App\Models\EmployeeWarning;
use App\Models\EmployeeGrievance;
use App\Models\CompanyAnnouncement;
use App\Models\HrHelpdeskTicket;
use App\Services\EmployeeRelations\DisciplinaryService;
use App\Services\EmployeeRelations\GrievanceService;
use App\Services\EmployeeRelations\EngagementService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class EmployeeRelationsAndEngagementPhase6Test extends TestCase
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
            'name' => 'PT Nusantara Sejahtera Mandiri',
            'slug' => 'nusantara-sejahtera',
            'status' => 'active',
        ]);

        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Nusantara Sejahtera Mandiri',
            'email' => 'admin@nusantara.id',
            'npwp' => '01.888.777.6-012.000',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Department & Position
        $this->department = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Human Capital & Relations',
            'code' => 'HCR',
        ]);

        $this->position = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'People Operations Specialist',
            'grade' => 'G3',
        ]);

        // 3. Setup Users & Roles
        $hrRole = Role::where('slug', 'hr_manager')->first();
        $empRole = Role::where('slug', 'employee')->first();

        $this->hrUser = User::create([
            'name' => 'Siti Aminah, S.Psi',
            'email' => 'hr@nusantara.id',
            'password' => bcrypt('password123'),
            'role_id' => $hrRole->id,
            'is_super_admin' => false,
        ]);
        $this->hrUser->tenants()->attach($this->tenant->id);

        $this->empUser = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'fauzi@nusantara.id',
            'password' => bcrypt('password123'),
            'role_id' => $empRole->id,
            'is_super_admin' => false,
        ]);
        $this->empUser->tenants()->attach($this->tenant->id);

        // 4. Employee Model
        $this->employee = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->empUser->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employee_code' => 'EMP-2026-099',
            'employee_number' => 'EMP-2026-099',
            'full_name' => 'Ahmad Fauzi',
            'email' => 'fauzi@nusantara.id',
            'phone' => '081234567890',
            'join_date' => '2024-01-15',
            'employment_status' => 'permanent',
            'status' => 'active',
            'base_salary' => 8500000.00,
        ]);
    }

    /**
     * Test 1: Pasal 161 UU Ketenagakerjaan SP1 automatic 6-month validity calculation
     */
    public function test_can_issue_warning_letter_sp1_with_automatic_6_month_expiry(): void
    {
        $service = app(DisciplinaryService::class);

        $issuedDate = '2026-02-01';
        $warning = $service->issueWarning($this->employee, [
            'warning_level' => 'sp1',
            'issued_date' => $issuedDate,
            'reason' => 'Keterlambatan hadir berulang tanpa surat izin tertulis.',
            'action_plan' => 'Wajib hadir tepat waktu pukul 08:30 WIB setiap hari kerja.',
        ], $this->hrUser);

        $this->assertNotNull($warning);
        $this->assertDatabaseHas('employee_warnings', [
            'id' => $warning->id,
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'warning_level' => 'sp1',
            'status' => 'active',
        ]);

        // Verify letter numbering structure: SP1/DIR-HR/YYYY/001
        $this->assertStringStartsWith('SP1/DIR-HR/' . date('Y'), $warning->letter_number);

        // Verify 6-month expiry rule (Pasal 161 UU Ketenagakerjaan / PP 35/2021)
        $expectedValidUntil = Carbon::parse($issuedDate)->addMonths(6)->toDateString();
        $this->assertEquals($expectedValidUntil, $warning->valid_until->toDateString());
    }

    /**
     * Test 2: Warning letter revocation before expiry
     */
    public function test_can_revoke_warning_letter_early(): void
    {
        $service = app(DisciplinaryService::class);

        $warning = $service->issueWarning($this->employee, [
            'warning_level' => 'sp2',
            'issued_date' => '2026-03-01',
            'reason' => 'Pelanggaran SOP keselamatan kerja.',
        ], $this->hrUser);

        $this->assertEquals('active', $warning->status);

        $service->revokeWarning($warning, 'Karyawan telah lulus uji sertifikasi ulang K3.');

        $this->assertDatabaseHas('employee_warnings', [
            'id' => $warning->id,
            'status' => 'revoked',
        ]);
        $this->assertStringContainsString('lulus uji sertifikasi ulang K3', $warning->fresh()->reason);
    }

    /**
     * Test 3: Confidential & Anonymous Employee Grievance Submission and Resolution
     */
    public function test_employee_can_submit_grievance_anonymously_and_hr_can_resolve(): void
    {
        $service = app(GrievanceService::class);

        // Submit anonymous grievance
        $grievance = $service->submitGrievance($this->employee, [
            'category' => 'harassment',
            'subject' => 'Tindakan intimidasi verbal oleh rekan kerja saat lembur',
            'description' => 'Terjadi insiden intimidasi dan ancaman pada tanggal 10 Februari.',
            'is_anonymous' => true,
        ]);

        $this->assertNotNull($grievance);
        $this->assertTrue((bool)$grievance->is_anonymous);
        $this->assertEquals('submitted', $grievance->status);

        // HR updates to investigating
        $service->updateStatus($grievance, 'investigating', 'Wawancara saksi shift malam sedang berlangsung.');
        $this->assertEquals('investigating', $grievance->fresh()->status);
        $this->assertNotNull($grievance->fresh()->investigation_notes);

        // HR resolves grievance
        $service->resolveGrievance($grievance, $this->hrUser, 'Pihak terlapor telah diberikan sanksi SP2 dan mutasi shift.');
        $freshGrievance = $grievance->fresh();
        $this->assertEquals('resolved', $freshGrievance->status);
        $this->assertEquals($this->hrUser->id, $freshGrievance->resolved_by_user_id);
        $this->assertNotNull($freshGrievance->resolved_at);
    }

    /**
     * Test 4: Publish Announcements with Target Department and Pinning
     */
    public function test_can_publish_announcement_with_target_department_and_pin_toggle(): void
    {
        $service = app(EngagementService::class);

        $announcement = $service->createAnnouncement($this->tenant->id, [
            'title' => 'Pemberitahuan Hari Libur Nasional Hari Raya Nyepi 2026',
            'content' => 'Kantor akan libur operasional dan seluruh operasional remote diliburkan.',
            'category' => 'holiday',
            'target_department_id' => $this->department->id,
            'is_pinned' => true,
            'published_at' => '2026-03-15',
            'expires_at' => '2026-03-25',
        ], $this->hrUser);

        $this->assertNotNull($announcement);
        $this->assertDatabaseHas('company_announcements', [
            'id' => $announcement->id,
            'tenant_id' => $this->tenant->id,
            'target_department_id' => $this->department->id,
            'is_pinned' => true,
            'category' => 'holiday',
        ]);
    }

    /**
     * Test 5: HR Helpdesk Ticket Creation and Resolution
     */
    public function test_employee_can_request_hr_helpdesk_ticket_and_hr_can_resolve(): void
    {
        $service = app(EngagementService::class);

        // Employee requests certificate
        $ticket = $service->createTicket($this->employee, [
            'category' => 'work_certificate',
            'subject' => 'Permohonan Paklaring untuk Keperluan Visa Australia',
            'description' => 'Mohon dibuatkan surat keterangan dalam Bahasa Inggris.',
        ]);

        $this->assertNotNull($ticket);
        $this->assertStringStartsWith('TICK-' . date('Ym'), $ticket->ticket_number);
        $this->assertEquals('open', $ticket->status);

        // HR resolves ticket
        $service->resolveTicket($ticket, $this->hrUser, 'Dokumen Surat Keterangan Kerja No. SKK/EN/2026/012 telah diemail.');
        $freshTicket = $ticket->fresh();
        $this->assertEquals('resolved', $freshTicket->status);
        $this->assertEquals($this->hrUser->id, $freshTicket->assigned_to_user_id);
        $this->assertNotNull($freshTicket->resolved_at);
    }

    /**
     * Test 6: Web Routes Accessibility for HR and Employee Portals
     */
    public function test_web_routes_accessibility_for_hr_and_ess(): void
    {
        // 1. HR Routes
        $this->actingAs($this->hrUser);

        $responseDisciplinary = $this->get(route('company.disciplinary.index'));
        $responseDisciplinary->assertStatus(200);

        $responseGrievances = $this->get(route('company.grievances.index'));
        $responseGrievances->assertStatus(200);

        $responseAnnouncements = $this->get(route('company.announcements.index'));
        $responseAnnouncements->assertStatus(200);

        $responseHelpdesk = $this->get(route('company.helpdesk.index'));
        $responseHelpdesk->assertStatus(200);

        // 2. ESS Route
        $this->actingAs($this->empUser);
        $responseEss = $this->get(route('ess.dashboard'));
        $responseEss->assertStatus(200);
    }
}

