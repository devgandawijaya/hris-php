<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Integration\EnterpriseApiService;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenApiAndThirdPartyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $owner;
    protected Employee $employee;
    protected string $apiKeyPlain;
    protected ApiKey $apiKeyModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(SubscriptionPlanSeeder::class);

        $this->tenant = Tenant::create([
            'name' => 'PT Integrasi Solusi Nusantara',
            'slug' => 'integrasi-solusi',
            'status' => 'active',
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Integrasi Solusi Nusantara',
            'email' => 'tech@integrasisolusi.com',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->owner = User::create([
            'name' => 'Chief Technology Officer',
            'email' => 'cto@integrasisolusi.com',
            'password' => bcrypt('SecretPass123#'),
            'active_tenant_id' => $this->tenant->id,
            'email_verified_at' => now(),
        ]);

        $roleOwner = Role::where('slug', 'company-owner')->first();
        if ($roleOwner) {
            $this->owner->tenants()->attach($this->tenant->id, ['role_id' => $roleOwner->id, 'is_owner' => true]);
            $this->owner->assignRole($roleOwner, $this->tenant->id);
        }

        $this->employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->owner->id,
            'employee_number' => 'EMP-001',
            'first_name' => 'Budi',
            'last_name' => 'Pratama',
            'email' => 'budi.pratama@integrasisolusi.com',
            'employment_status' => 'pkwtt',
            'join_date' => '2026-01-01',
            'basic_salary' => 10000000.00,
            'status' => 'active',
        ]);

        /** @var EnterpriseApiService $service */
        $service = app(EnterpriseApiService::class);
        $keyResult = $service->generateApiKey($this->tenant->id, 'Third Party ERP Gateway', ['*']);
        $this->apiKeyPlain = $keyResult['plain_key'];
        $this->apiKeyModel = $keyResult['model'];
    }

    public function test_openapi_specification_endpoint_returns_valid_openapi_3_1_json(): void
    {
        $response = $this->getJson('/api/v1/openapi.json');

        $response->assertStatus(200);
        $response->assertJsonPath('openapi', '3.1.0');
        $response->assertJsonPath('info.title', 'RiHRIS Enterprise Open API');
        $response->assertJsonStructure([
            'openapi',
            'info',
            'servers',
            'tags',
            'paths' => [
                '/employees',
                '/employees/{id}',
                '/attendance',
                '/attendance/bulk',
                '/departments',
                '/leave/types',
                '/leave/requests',
                '/payroll/summary',
                '/webhooks/events',
            ],
            'components' => [
                'securitySchemes' => ['ApiKeyAuth', 'BearerAuth'],
                'schemas',
            ],
        ]);
    }

    public function test_openapi_web_portal_renders_successfully(): void
    {
        $response = $this->actingAs($this->owner)->get('/integrations/openapi');

        $response->assertStatus(200);
        $response->assertSee('RiHRIS Open API Developer Portal');
        $response->assertSee('openapi.json');
        $response->assertSee('Konsol Pengujian API Interaktif');
    }

    public function test_api_authentication_requires_valid_api_key(): void
    {
        // Missing key -> 401
        $missingKeyResponse = $this->getJson('/api/v1/employees');
        $missingKeyResponse->assertStatus(401);
        $missingKeyResponse->assertJsonPath('success', false);

        // Invalid key -> 401
        $invalidKeyResponse = $this->withHeaders([
            'X-API-KEY' => 'ri_live_invalid_fake_key_12345678',
        ])->getJson('/api/v1/employees');
        $invalidKeyResponse->assertStatus(401);

        // Valid key via X-API-KEY header -> 200
        $validResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->getJson('/api/v1/employees');
        $validResponse->assertStatus(200);
        $validResponse->assertJsonPath('success', true);

        // Valid key via Bearer token -> 200
        $bearerResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->apiKeyPlain}",
        ])->getJson('/api/v1/employees');
        $bearerResponse->assertStatus(200);
    }

    public function test_third_party_can_register_new_employee_via_api(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->postJson('/api/v1/employees', [
            'employee_number' => 'EMP-2026-999',
            'first_name' => 'Dewi',
            'last_name' => 'Lestari',
            'email' => 'dewi.lestari@integrasisolusi.com',
            'phone' => '081234567891',
            'gender' => 'female',
            'employment_status' => 'pkwt',
            'join_date' => '2026-06-01',
            'basic_salary' => 9000000,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.employee_number', 'EMP-2026-999');

        $this->assertDatabaseHas('employees', [
            'tenant_id' => $this->tenant->id,
            'employee_number' => 'EMP-2026-999',
            'first_name' => 'Dewi',
        ]);
    }

    public function test_third_party_can_update_employee_via_api(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->putJson("/api/v1/employees/{$this->employee->id}", [
            'first_name' => 'Budi Santoso',
            'basic_salary' => 12500000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('employees', [
            'id' => $this->employee->id,
            'first_name' => 'Budi Santoso',
            'basic_salary' => 12500000,
        ]);
    }

    public function test_biometric_device_can_push_single_and_bulk_attendance(): void
    {
        // 1. Single clock-in
        $singleResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->postJson('/api/v1/attendance', [
            'employee_number' => 'EMP-001',
            'type' => 'clock_in',
            'timestamp' => '2026-11-14 08:00:00',
            'device_id' => 'GATE-A1',
        ]);

        $singleResponse->assertStatus(201);
        $singleResponse->assertJsonPath('success', true);

        // 2. Bulk attendance push from offline biometric machine
        $bulkResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->postJson('/api/v1/attendance/bulk', [
            'device_id' => 'ZKTECO-GATE-01',
            'logs' => [
                [
                    'employee_number' => 'EMP-001',
                    'type' => 'clock_out',
                    'timestamp' => '2026-11-14 17:05:00',
                ],
                [
                    'employee_number' => 'EMP-UNKNOWN',
                    'type' => 'clock_in',
                    'timestamp' => '2026-11-14 08:15:00',
                ],
            ],
        ]);

        $bulkResponse->assertStatus(200);
        $bulkResponse->assertJsonPath('summary.total', 2);
        $bulkResponse->assertJsonPath('summary.successful', 1);
        $bulkResponse->assertJsonPath('summary.failed', 1);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'date' => '2026-11-14',
            'clock_in' => '08:00:00',
            'clock_out' => '17:05:00',
        ]);
    }

    public function test_departments_and_leave_integration_endpoints(): void
    {
        // 1. Create department via API
        $deptResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->postJson('/api/v1/departments', [
            'name' => 'Divisi Operasional & Rantai Pasok',
            'description' => 'Departemen baru sinkronisasi ERP.',
        ]);

        $deptResponse->assertStatus(201);
        $deptResponse->assertJsonPath('data.name', 'Divisi Operasional & Rantai Pasok');

        // 2. Query leave types
        $leaveType = LeaveType::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cuti Tahunan',
            'code' => 'CT',
            'days_allowed' => 12,
            'is_paid' => true,
        ]);

        $typesResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->getJson('/api/v1/leave/types');

        $typesResponse->assertStatus(200);
        $typesResponse->assertJsonPath('count', 1);

        // 3. Submit leave request from third-party app
        $leaveResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->postJson('/api/v1/leave/requests', [
            'employee_number' => 'EMP-001',
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-03',
            'reason' => 'Libur akhir tahun bersama keluarga.',
        ]);

        $leaveResponse->assertStatus(201);
        $leaveResponse->assertJsonPath('data.total_days', 3);
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'total_days' => 3,
            'status' => 'pending',
        ]);
    }

    public function test_payroll_summary_and_webhook_catalog_endpoints(): void
    {
        $period = PayrollPeriod::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Payroll November 2026',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'payment_date' => '2026-11-25',
            'status' => 'approved',
        ]);

        Payroll::create([
            'tenant_id' => $this->tenant->id,
            'payroll_period_id' => $period->id,
            'employee_id' => $this->employee->id,
            'basic_salary' => 10000000,
            'gross_salary' => 10000000,
            'take_home_pay' => 9200000,
            'pph21_amount' => 500000,
            'bpjs_company_total' => 600000,
            'bpjs_employee_total' => 300000,
        ]);

        // 1. Payroll summary for ERP General Ledger
        $payrollResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->getJson('/api/v1/payroll/summary');

        $payrollResponse->assertStatus(200);
        $payrollResponse->assertJsonPath('success', true);
        $payrollResponse->assertJsonPath('totals.gross_salary', 10000000);
        $payrollResponse->assertJsonPath('totals.take_home_pay', 9200000);

        // 2. Webhooks events catalog
        $webhookResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyPlain,
        ])->getJson('/api/v1/webhooks/events');

        $webhookResponse->assertStatus(200);
        $webhookResponse->assertJsonPath('success', true);
        $this->assertGreaterThan(0, $webhookResponse->json('count'));
    }
}
