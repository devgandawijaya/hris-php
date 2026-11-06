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
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Workflow;
use App\Models\ApprovalRequest;
use App\Models\ApiKey;
use App\Models\WebhookSubscription;
use App\Models\CompanyIpWhitelist;
use App\Services\Workflow\WorkflowEngineService;
use App\Services\Analytics\HrAnalyticsService;
use App\Services\Billing\SubscriptionBillingService;
use App\Services\Integration\EnterpriseApiService;
use App\Services\Integration\WebhookService;
use App\Services\Security\AuditService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnterprisePhase5Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $hrUser;
    protected User $ownerUser;
    protected Employee $employee;
    protected SubscriptionPlan $plan;
    protected Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // 1. Setup Tenant & Company
        $this->tenant = Tenant::create([
            'name' => 'PT Megantara Enterprise Solusi',
            'slug' => 'megantara-solusi',
            'status' => 'active',
        ]);

        $this->company = Company::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Megantara Enterprise Solusi',
            'email' => 'admin@megantara.id',
            'npwp' => '01.555.444.3-012.000',
        ]);

        app(TenantManager::class)->setCurrentTenant($this->tenant);

        // 2. Setup Subscription Plan & Active Subscription
        $this->plan = SubscriptionPlan::create([
            'name' => 'Enterprise Pro',
            'slug' => 'enterprise-pro',
            'price_monthly' => 1500000.00,
            'price_yearly' => 15000000.00,
            'max_employees' => 100,
            'max_admins' => 10,
            'storage_limit_mb' => 10240,
            'is_active' => true,
        ]);

        $this->subscription = Subscription::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // 3. Setup Roles & Users
        $ownerRole = Role::where('slug', 'company-owner')->first();
        $hrRole = Role::where('slug', 'hr-manager')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->ownerUser = User::create([
            'name' => 'Owner Megantara',
            'email' => 'owner@megantara.id',
            'password' => bcrypt('password'),
            'active_tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->ownerUser->tenants()->attach($this->tenant->id, ['role_id' => $ownerRole?->id, 'is_owner' => true]);
        $this->ownerUser->assignRole($ownerRole, $this->tenant->id);

        $this->hrUser = User::create([
            'name' => 'HR Manager Megantara',
            'email' => 'hr@megantara.id',
            'password' => bcrypt('password'),
            'active_tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
        ]);
        $this->hrUser->tenants()->attach($this->tenant->id, ['role_id' => $hrRole?->id, 'is_owner' => false]);
        $this->hrUser->assignRole($hrRole, $this->tenant->id);

        // 4. Setup Employee
        $dept = Department::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Operations',
            'code' => 'OPS',
        ]);

        $pos = Position::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $dept->id,
            'title' => 'Operations Lead',
        ]);

        $this->employee = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->hrUser->id,
            'employee_number' => 'MES-001',
            'nik_ktp' => '3171012304920088',
            'first_name' => 'Bambang',
            'last_name' => 'Pamungkas',
            'email' => 'bambang@megantara.id',
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'employment_status' => 'pkwtt',
            'join_date' => '2023-01-01',
            'basic_salary' => 15000000.00,
            'status' => 'active',
        ]);
    }

    /**
     * Test 1: Workflow Engine 2-Tier Approval Flow (Pending -> Step 1 -> Step 2 Finalized).
     */
    public function test_workflow_engine_multi_level_approval_flow(): void
    {
        /** @var WorkflowEngineService $service */
        $service = app(WorkflowEngineService::class);

        // 1. Create a 2-Tier Workflow for Leave
        $workflow = $service->createWorkflow(
            $this->tenant->id,
            'Leave Approval Multi-Level',
            'leave',
            ['hr-manager', 'company-owner']
        );

        $this->assertDatabaseHas('workflows', [
            'id' => $workflow->id,
            'entity_type' => 'leave',
        ]);
        $this->assertCount(2, $workflow->steps);

        // 2. Create Leave Request Model
        $leaveType = LeaveType::create([
            'name' => 'Cuti Tahunan',
            'code' => 'AL',
            'default_quota' => 12,
        ]);

        $leaveRequest = LeaveRequest::create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'total_days' => 2,
            'reason' => 'Keperluan keluarga',
            'status' => 'pending',
        ]);

        // 3. Initiate Approval Request
        $appRequest = $service->initiateApproval($leaveRequest, 'leave');
        $this->assertNotNull($appRequest);
        $this->assertEquals(1, $appRequest->current_step);
        $this->assertEquals('pending', $appRequest->status);

        // 4. Step 1 Approved by HR Manager
        $step1 = $service->approve($appRequest, $this->hrUser, 'Plafon cuti mencukupi, disetujui HR.');
        $this->assertEquals(2, $step1->current_step);
        $this->assertEquals('pending', $step1->status);

        // 5. Step 2 Approved by Company Owner (Final Step)
        $step2 = $service->approve($step1, $this->ownerUser, 'Disetujui Owner.');
        $this->assertEquals('approved', $step2->status);

        // Verify underlying leave request status updated to approved
        $leaveRequest->refresh();
        $this->assertEquals('approved', $leaveRequest->status);
    }

    /**
     * Test 2: HR Analytics Metrics Computation & CSV Exports.
     */
    public function test_hr_analytics_metrics_and_csv_exports(): void
    {
        /** @var HrAnalyticsService $service */
        $service = app(HrAnalyticsService::class);

        $metrics = $service->getExecutiveMetrics($this->tenant->id);

        $this->assertArrayHasKey('total_headcount', $metrics);
        $this->assertArrayHasKey('active_headcount', $metrics);
        $this->assertArrayHasKey('turnover_rate', $metrics);
        $this->assertArrayHasKey('ontime_rate', $metrics);
        $this->assertEquals(1, $metrics['active_headcount']);

        // Test Headcount CSV
        $headcountCsv = $service->exportHeadcountCsv($this->tenant->id);
        $this->assertStringContainsString('NIK', $headcountCsv);
        $this->assertStringContainsString('Bambang Pamungkas', $headcountCsv);
        $this->assertStringContainsString('MES-001', $headcountCsv);

        // Test Attendance CSV
        $attendanceCsv = $service->exportAttendanceCsv($this->tenant->id);
        $this->assertStringContainsString('Keterlambatan (Menit)', $attendanceCsv);

        // Test Payroll CSV
        $payrollCsv = $service->exportPayrollSummaryCsv($this->tenant->id);
        $this->assertStringContainsString('Take Home Pay (THP)', $payrollCsv);
    }

    /**
     * Test 3: SaaS Subscription Billing, Invoicing, and Quota Verification.
     */
    public function test_subscription_billing_invoicing_and_quota(): void
    {
        /** @var SubscriptionBillingService $service */
        $service = app(SubscriptionBillingService::class);

        // 1. Create Invoice
        $invoice = $service->createInvoice($this->subscription, 1500000.00, 165000.00);
        $this->assertDatabaseHas('subscription_invoices', [
            'id' => $invoice->id,
            'total_amount' => 1665000.00,
            'status' => 'pending',
        ]);

        // 2. Mark Invoice as Paid
        $paidInvoice = $service->markInvoicePaid($invoice, 'bank_transfer');
        $this->assertEquals('paid', $paidInvoice->status);
        $this->assertNotNull($paidInvoice->paid_at);

        // 3. Quota Check
        $canAdd = $service->canAddEmployee($this->tenant);
        $this->assertTrue($canAdd);

        $usage = $service->getSubscriptionUsage($this->tenant);
        $this->assertEquals('Enterprise Pro', $usage['plan_name']);
        $this->assertEquals(100, $usage['max_employees']);
        $this->assertEquals(1, $usage['employee_count']);
    }

    /**
     * Test 4: Enterprise REST API Token Auth and Endpoints.
     */
    public function test_enterprise_api_keys_and_rest_endpoints(): void
    {
        /** @var EnterpriseApiService $service */
        $service = app(EnterpriseApiService::class);

        // 1. Generate API Key
        $keyResult = $service->generateApiKey($this->tenant->id, 'Attendance Device Gate 1');
        $plainKey = $keyResult['plain_key'];

        $this->assertDatabaseHas('api_keys', [
            'name' => 'Attendance Device Gate 1',
            'is_active' => true,
        ]);

        // 2. Call GET /api/v1/employees without key -> 401 Unauthorized
        $responseNoKey = $this->getJson('/api/v1/employees');
        $responseNoKey->assertStatus(401);

        // 3. Call GET /api/v1/employees with X-API-KEY header -> 200 OK
        $responseWithKey = $this->withHeaders([
            'X-API-KEY' => $plainKey,
        ])->getJson('/api/v1/employees');

        $responseWithKey->assertStatus(200);
        $responseWithKey->assertJsonPath('success', true);
        $this->assertCount(1, $responseWithKey->json('data'));

        // 4. Call POST /api/v1/attendance (Biometric Clock-In Sync)
        $clockInResponse = $this->withHeaders([
            'X-API-KEY' => $plainKey,
        ])->postJson('/api/v1/attendance', [
            'employee_number' => 'MES-001',
            'type' => 'clock_in',
            'timestamp' => '2026-05-10 08:02:15',
            'device_id' => 'GATE-LOBBY-01',
        ]);

        $clockInResponse->assertStatus(201);
        $clockInResponse->assertJsonPath('success', true);
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'date' => '2026-05-10',
            'clock_in' => '08:02:15',
        ]);
    }

    /**
     * Test 5: Webhook Event Dispatching & Delivery Logs.
     */
    public function test_webhook_subscription_and_dispatch(): void
    {
        /** @var WebhookService $service */
        $service = app(WebhookService::class);

        // 1. Create Webhook Subscription
        $webhook = $service->createSubscription(
            $this->tenant->id,
            'Corporate Slack Notifications',
            'https://example.com/webhooks/hris',
            ['employee.created', 'leave.approved']
        );

        $this->assertDatabaseHas('webhook_subscriptions', [
            'id' => $webhook->id,
            'name' => 'Corporate Slack Notifications',
        ]);

        // 2. Dispatch Event
        $deliveries = $service->dispatch($this->tenant->id, 'leave.approved', [
            'employee_number' => 'MES-001',
            'leave_type' => 'Cuti Tahunan',
            'total_days' => 2,
        ]);

        $this->assertCount(1, $deliveries);
        $this->assertEquals('leave.approved', $deliveries[0]->event);
        $this->assertEquals(200, $deliveries[0]->response_status);
    }

    /**
     * Test 6: Security Audit Trail Logging & IP Whitelisting.
     */
    public function test_audit_logging_and_ip_whitelist(): void
    {
        /** @var AuditService $service */
        $service = app(AuditService::class);

        // 1. Record Audit Log
        $log = $service->log(
            $this->tenant->id,
            $this->ownerUser->id,
            'security_policy_updated',
            'App\Models\CompanySetting',
            1,
            ['allow_remote' => true],
            ['allow_remote' => false]
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'security_policy_updated',
        ]);

        // 2. IP Whitelisting Logic
        // When no IP whitelist exists -> All IPs allowed
        $this->assertTrue($service->isIpAllowed($this->tenant->id, '118.99.10.5'));

        // Add Corporate IP
        $whitelist = $service->addIpWhitelist($this->tenant->id, '180.252.12.8', 'Kantor Pusat');
        $this->assertDatabaseHas('company_ip_whitelists', [
            'id' => $whitelist->id,
            'ip_address' => '180.252.12.8',
        ]);

        // When whitelist exists -> Only whitelisted IP allowed
        $this->assertTrue($service->isIpAllowed($this->tenant->id, '180.252.12.8'));
        $this->assertFalse($service->isIpAllowed($this->tenant->id, '118.99.10.5'));
    }

    /**
     * Test 7: Enterprise Web Routes Return HTTP 200.
     */
    public function test_enterprise_web_views_return_ok(): void
    {
        $this->actingAs($this->hrUser);

        // Workflows Index
        $response = $this->get(route('company.workflows.index'));
        $response->assertStatus(200);

        // Analytics Index
        $response = $this->get(route('company.analytics.index'));
        $response->assertStatus(200);

        // Billing Index
        $response = $this->get(route('company.billing.index'));
        $response->assertStatus(200);

        // Integrations Index
        $response = $this->get(route('company.integrations.index'));
        $response->assertStatus(200);

        // Security Index
        $response = $this->get(route('company.security.index'));
        $response->assertStatus(200);
    }
}
