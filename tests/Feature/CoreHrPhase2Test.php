<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\Shift;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrection;
use App\Models\OvertimeRequest;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CoreHrPhase2Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $adminUser;
    protected User $empUser;
    protected Employee $employee;
    protected Department $department;
    protected Position $position;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // 1. Setup Tenant & Company
        $this->tenant = Tenant::create(['name' => 'PT Megah Nusantara', 'slug' => 'megah-nusantara', 'status' => 'active']);
        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Megah Nusantara',
            'email' => 'admin@megahnusantara.id',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Setup HR Admin User
        $this->adminUser = User::create([
            'name' => 'HR Administrator',
            'email' => 'hr@megahnusantara.id',
            'password' => bcrypt('password'),
            'active_tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $ownerRole = Role::where('slug', 'company-owner')->first();
        $this->adminUser->tenants()->attach($this->tenant->id, ['role_id' => $ownerRole?->id, 'is_owner' => true]);
        $this->adminUser->assignRole($ownerRole, $this->tenant->id);

        // 3. Setup Department & Position
        $this->department = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Information Technology',
            'code' => 'IT-01',
        ]);

        $this->position = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'Software Engineer',
            'job_level' => 'Specialist',
        ]);

        // 4. Setup Employee and linked User
        $this->empUser = User::create([
            'name' => 'Ahmad Santoso',
            'email' => 'ahmad@megahnusantara.id',
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
            'employee_number' => 'MN-2026-001',
            'nik_ktp' => '3171012304920001',
            'first_name' => 'Ahmad',
            'last_name' => 'Santoso',
            'email' => 'ahmad@megahnusantara.id',
            'gender' => 'male',
            'marital_status' => 'married',
            'tax_ptkp_status' => 'K/1', // Kategori TER B
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'pkwtt',
            'join_date' => now()->subYear()->toDateString(),
            'basic_salary' => 12000000.00,
            'status' => 'active',
        ]);
    }

    /**
     * Test Employee CRUD operations by HR Admin.
     */
    public function test_hr_admin_can_create_new_employee_with_auto_login_account(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/employees', [
            'employee_number' => 'MN-2026-002',
            'first_name' => 'Siti',
            'last_name' => 'Nurhaliza',
            'email' => 'siti@megahnusantara.id',
            'gender' => 'female',
            'marital_status' => 'single',
            'tax_ptkp_status' => 'TK/0',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'pkwt',
            'join_date' => now()->toDateString(),
            'basic_salary' => 8500000,
            'create_user_account' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('employees', [
            'tenant_id' => $this->tenant->id,
            'employee_number' => 'MN-2026-002',
            'email' => 'siti@megahnusantara.id',
            'tax_ptkp_status' => 'TK/0',
        ]);

        // Verify login account created
        $this->assertDatabaseHas('users', [
            'email' => 'siti@megahnusantara.id',
            'active_tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test Recording Employee Career Mutation (Promotion).
     */
    public function test_hr_can_record_employee_career_promotion_and_salary_adjustment(): void
    {
        $newPosition = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'title' => 'Engineering Manager',
            'job_level' => 'Manager',
        ]);

        $response = $this->actingAs($this->adminUser)->post("/employees/{$this->employee->id}/mutations", [
            'type' => 'promotion',
            'to_department_id' => $this->department->id,
            'to_position_id' => $newPosition->id,
            'to_salary' => 18000000.00,
            'effective_date' => now()->toDateString(),
            'notes' => 'Promosi berdasarkan evaluasi KPI semester 1',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employee_mutations', [
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'type' => 'promotion',
            'to_salary' => 18000000.00,
        ]);

        // Check employee model updated
        $this->employee->refresh();
        $this->assertEquals($newPosition->id, $this->employee->position_id);
        $this->assertEquals(18000000.00, $this->employee->basic_salary);
    }

    /**
     * Test Branch creation with Geofencing GPS Coordinates.
     */
    public function test_hr_can_create_branch_with_geofence_radius(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/organization/branches', [
            'name' => 'Kantor Cabang Surabaya',
            'city' => 'Surabaya',
            'latitude' => -7.2574719,
            'longitude' => 112.7520883,
            'radius_meters' => 150,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('branches', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Kantor Cabang Surabaya',
            'radius_meters' => 150,
        ]);
    }

    /**
     * Test Employee ESS Clock In and Clock Out with Geolocation.
     */
    public function test_employee_can_clock_in_and_clock_out_via_ess(): void
    {
        // 1. Clock In
        $clockInResponse = $this->actingAs($this->empUser)->post('/ess/clock', [
            'latitude' => -6.225588,
            'longitude' => 106.811802,
        ]);

        $clockInResponse->assertSessionHas('success');
        $this->assertDatabaseHas('attendance_records', [
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'date' => now()->toDateString(),
        ]);

        // 2. Clock Out
        $clockOutResponse = $this->actingAs($this->empUser)->post('/ess/clock');
        $clockOutResponse->assertSessionHas('success');

        $record = AttendanceRecord::where('employee_id', $this->employee->id)->where('date', now()->toDateString())->first();
        $this->assertNotNull($record->clock_out);
    }

    /**
     * Test Attendance Correction submission and Approval.
     */
    public function test_employee_can_submit_correction_and_hr_can_approve(): void
    {
        // Employee submits correction
        $response = $this->actingAs($this->empUser)->post('/ess/corrections', [
            'date' => now()->subDay()->toDateString(),
            'corrected_clock_in' => '08:25',
            'corrected_clock_out' => '17:35',
            'reason' => 'Lupa clock in karena ada rapat vendor pagi',
        ]);

        $response->assertSessionHas('success');
        $correction = AttendanceCorrection::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($correction);
        $this->assertEquals('pending', $correction->status);

        // HR approves correction
        $approveResponse = $this->actingAs($this->adminUser)->post("/attendance/corrections/{$correction->id}/approve");
        $approveResponse->assertSessionHas('success');

        $correction->refresh();
        $this->assertEquals('approved', $correction->status);

        // Attendance record created/updated
        $this->assertDatabaseHas('attendance_records', [
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'date' => now()->subDay()->toDateString(),
            'clock_in' => '08:25:00',
        ]);
    }

    /**
     * Test Leave Request submission and HR Approval with balance deduction.
     */
    public function test_leave_request_workflow_deducts_leave_balance(): void
    {
        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cuti Tahunan',
            'code' => 'CT-01',
            'days_allowed' => 12,
            'is_paid' => true,
        ]);

        // Employee applies for 2 days leave
        $startDate = now()->addDays(2)->toDateString();
        $endDate = now()->addDays(3)->toDateString();

        $response = $this->actingAs($this->empUser)->post('/ess/leave', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Urusan keluarga di kampung',
        ]);

        $response->assertSessionHas('success');
        $leave = LeaveRequest::where('employee_id', $this->employee->id)->first();
        $this->assertEquals('pending', $leave->status);

        // HR Approves
        $approveResponse = $this->actingAs($this->adminUser)->post("/leave/{$leave->id}/approve");
        $approveResponse->assertSessionHas('success');

        $leave->refresh();
        $this->assertEquals('approved', $leave->status);

        // Balance updated
        $this->assertDatabaseHas('leave_balances', [
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'used' => 2,
            'remaining' => 10,
        ]);
    }

    /**
     * Test Overtime SPL submission and PP 35/2021 pay calculation upon approval.
     */
    public function test_overtime_request_approval_calculates_pay_per_pp35_2021(): void
    {
        // Employee basic wage: 12.000.000 -> Hourly rate = 12.000.000 / 173 = 69.364,16
        // 2 hours overtime on normal day: 1.5 + 2.0 = 3.5 multiplier -> Pay = 69.364,16 * 3.5 = 242.774,56
        $response = $this->actingAs($this->empUser)->post('/ess/overtime', [
            'date' => now()->toDateString(),
            'hours' => 2.0,
            'is_holiday' => 0,
            'reason' => 'Perbaikan bug kritis sebelum rilis',
        ]);

        $response->assertSessionHas('success');
        $ot = OvertimeRequest::where('employee_id', $this->employee->id)->first();
        $this->assertEquals('pending', $ot->status);

        // HR Approves
        $approveResponse = $this->actingAs($this->adminUser)->post("/overtime/{$ot->id}/approve");
        $approveResponse->assertSessionHas('success');

        $ot->refresh();
        $this->assertEquals('approved', $ot->status);
        $this->assertGreaterThan(200000, $ot->calculated_pay);
    }
}

