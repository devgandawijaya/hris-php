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
use App\Models\EmployeeMutation;
use App\Models\EmployeeTermination;
use App\Models\AppNotification;
use App\Services\Career\CareerTransitionService;
use App\Services\Career\SeveranceCalculationService;
use App\Services\Notification\AppNotificationService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CareerTransitionsAndSeverancePhase7Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $hrUser;
    protected User $empUser;
    protected Employee $employee;
    protected Department $deptOld;
    protected Department $deptNew;
    protected Position $posOld;
    protected Position $posNew;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // 1. Tenant & Company
        $this->tenant = Tenant::create([
            'name' => 'PT Karir Cemerlang Nusantara',
            'slug' => 'karir-cemerlang',
            'status' => 'active',
        ]);

        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Karir Cemerlang Nusantara',
            'email' => 'hr@karircemerlang.id',
            'npwp' => '01.999.888.7-012.000',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Departments & Positions
        $this->deptOld = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Software Engineering',
            'code' => 'SWE',
        ]);
        $this->deptNew = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cloud Infrastructure & DevOps',
            'code' => 'CLD',
        ]);

        $this->posOld = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->deptOld->id,
            'title' => 'Junior Software Engineer',
            'grade' => 'G1',
        ]);
        $this->posNew = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->deptNew->id,
            'title' => 'Lead DevOps Architect',
            'grade' => 'G4',
        ]);

        // 3. Users & Employee
        $hrRole = Role::where('slug', 'hr_manager')->first();
        $empRole = Role::where('slug', 'employee')->first();

        $this->hrUser = User::create([
            'name' => 'Indah Permatasari, M.Psi',
            'email' => 'indah.hr@karircemerlang.id',
            'password' => bcrypt('password123'),
            'role_id' => $hrRole->id,
            'is_super_admin' => false,
        ]);
        $this->hrUser->tenants()->attach($this->tenant->id);

        $this->empUser = User::create([
            'name' => 'Bambang Sudirman',
            'email' => 'bambang@karircemerlang.id',
            'password' => bcrypt('password123'),
            'role_id' => $empRole->id,
            'is_super_admin' => false,
        ]);
        $this->empUser->tenants()->attach($this->tenant->id);

        $this->employee = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->empUser->id,
            'department_id' => $this->deptOld->id,
            'position_id' => $this->posOld->id,
            'employee_code' => 'EMP-KCR-001',
            'employee_number' => 'EMP-KCR-001',
            'full_name' => 'Bambang Sudirman',
            'email' => 'bambang@karircemerlang.id',
            'join_date' => Carbon::now()->subYears(3)->subMonths(4)->toDateString(), // 3 years, 4 months
            'employment_status' => 'permanent',
            'status' => 'active',
            'base_salary' => 8000000.00,
        ]);
    }

    /**
     * Test 1: Record career promotion and verify employee record update & notification
     */
    public function test_can_record_employee_career_mutation_and_update_employee_record(): void
    {
        $service = app(CareerTransitionService::class);

        $mutation = $service->recordTransition($this->employee, [
            'type' => 'promotion',
            'to_department_id' => $this->deptNew->id,
            'to_position_id' => $this->posNew->id,
            'to_salary' => 12500000.00,
            'effective_date' => now()->toDateString(),
            'notes' => 'SK Direksi Promosi Jabatan No. 042/SK-DIR/2026',
        ], $this->hrUser);

        $this->assertNotNull($mutation);
        $this->assertEquals('promotion', $mutation->type);
        $this->assertEquals(8000000.00, (float)$mutation->from_salary);
        $this->assertEquals(12500000.00, (float)$mutation->to_salary);

        // Verify Employee updated
        $freshEmployee = $this->employee->fresh();
        $this->assertEquals($this->deptNew->id, $freshEmployee->department_id);
        $this->assertEquals($this->posNew->id, $freshEmployee->position_id);
        $this->assertEquals(12500000.00, (float)$freshEmployee->base_salary);

        // Verify In-App Notification created
        $this->assertDatabaseHas('app_notifications', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->empUser->id,
            'type' => 'success',
        ]);
    }

    /**
     * Test 2: Calculate statutory severance under PP 35/2021
     */
    public function test_can_calculate_statutory_severance_under_pp35_2021(): void
    {
        $service = app(SeveranceCalculationService::class);

        // Employee joined 3 years 4 months ago (Masa kerja: 3 tahun s.d. < 4 tahun)
        // PP 35/2021 Pasal 40 ayat 2: 3 s/d < 4 tahun = 4 bulan upah
        // PP 35/2021 Pasal 40 ayat 3: 3 s/d < 6 tahun UPMK = 2 bulan upah
        // Efficiency multiplier = 0.5x UP
        $calc = $service->calculateSeverance(
            $this->employee,
            'efficiency',
            now()->toDateString(),
            8000000.00,
            5 // 5 remaining leave days
        );

        $this->assertEquals(3, $calc['years_of_service']);
        $this->assertEquals(4, $calc['base_severance_months']);
        $this->assertEquals(0.5, $calc['severance_multiplier']);

        // Severance UP = 4 bln * 8.000.000 * 0.5 = 16.000.000
        $this->assertEquals(16000000.00, $calc['severance_amount']);

        // UPMK = 2 bln * 8.000.000 = 16.000.000
        $this->assertEquals(16000000.00, $calc['upmk_amount']);

        // UPH = (5 / 21) * 8.000.000 = 1.904.761,90
        $this->assertGreaterThan(0, $calc['uph_amount']);

        $expectedTotal = round(16000000.00 + 16000000.00 + $calc['uph_amount'], 2);
        $this->assertEquals($expectedTotal, $calc['total_severance']);
    }

    /**
     * Test 3: Record employee termination and mark severance as paid
     */
    public function test_can_record_termination_and_mark_as_paid(): void
    {
        $service = app(SeveranceCalculationService::class);

        $termDate = '2026-03-31';
        $termination = $service->recordTermination($this->employee, [
            'termination_type' => 'efficiency',
            'termination_date' => $termDate,
            'last_salary' => 8000000.00,
            'remaining_leave_days' => 4,
            'notes' => 'Restrukturisasi divisi Cloud.',
        ], $this->hrUser);

        $this->assertNotNull($termination);
        $this->assertStringStartsWith('SK-PHK/' . date('Y'), $termination->letter_number);
        $this->assertEquals('approved', $termination->status);

        // Verify Employee marked inactive
        $freshEmployee = $this->employee->fresh();
        $this->assertEquals('inactive', $freshEmployee->status);
        $this->assertEquals($termDate, $freshEmployee->resigned_date->toDateString());

        // Mark as paid
        $service->markAsPaid($termination);
        $this->assertEquals('paid', $termination->fresh()->status);
        $this->assertNotNull($termination->fresh()->paid_at);
    }

    /**
     * Test 4: In-App notification creation, unread count, and mark as read
     */
    public function test_in_app_notification_flow(): void
    {
        $notifService = app(AppNotificationService::class);

        // Send direct notification
        $notifService->sendToUser(
            $this->empUser,
            'Slip Gaji Diterbitkan',
            'Slip gaji bulan Februari 2026 telah terbit.',
            'info'
        );

        $this->assertEquals(1, $notifService->getUnreadCount($this->empUser));

        // Mark all as read
        $notifService->markAllAsRead($this->empUser);
        $this->assertEquals(0, $notifService->getUnreadCount($this->empUser));
    }

    /**
     * Test 5: Web routes accessibility for mutations, terminations, and notifications
     */
    public function test_web_routes_for_mutations_and_terminations(): void
    {
        $this->actingAs($this->hrUser);

        // 1. Mutations Index
        $resMut = $this->get(route('company.mutations.index'));
        $resMut->assertStatus(200);

        // 2. Terminations Index
        $resTerm = $this->get(route('company.terminations.index'));
        $resTerm->assertStatus(200);

        // 3. Live Calculate API Endpoint
        $resCalc = $this->postJson(route('company.terminations.calculate'), [
            'employee_id' => $this->employee->id,
            'termination_type' => 'efficiency',
            'termination_date' => now()->toDateString(),
            'last_salary' => 8000000,
            'remaining_leave_days' => 2,
        ]);
        $resCalc->assertStatus(200);
        $resCalc->assertJsonStructure(['total_severance', 'severance_amount', 'upmk_amount', 'uph_amount']);

        // 4. Notifications Index JSON
        $resNotif = $this->getJson(route('notifications.index'));
        $resNotif->assertStatus(200);
        $resNotif->assertJsonStructure(['unread_count', 'notifications']);
    }
}

