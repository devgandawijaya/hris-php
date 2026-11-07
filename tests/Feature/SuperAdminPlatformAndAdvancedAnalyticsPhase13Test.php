<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionInvoice;
use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\CmsContactMessage;
use App\Models\NotificationTemplate;
use App\Models\NotificationDispatchLog;
use App\Services\Subscription\PlanGatingService;
use App\Services\SuperAdmin\ImpersonationService;
use App\Services\SuperAdmin\PlatformHealthService;
use App\Services\Analytics\AdvancedHrAnalyticsService;
use App\Services\Notification\MultiChannelNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class SuperAdminPlatformAndAdvancedAnalyticsPhase13Test extends TestCase
{
    protected Tenant $tenant;
    protected Company $company;
    protected User $superAdmin;
    protected User $companyOwner;
    protected Employee $employee;
    protected SubscriptionPlan $starterPlan;
    protected SubscriptionPlan $proPlan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant & Company
        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-phase13-corp'],
            ['name' => 'PT Test Phase 13 Solusindo', 'status' => 'active']
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pt-test-phase13'],
            [
                'name' => 'PT Test Phase 13 Solusindo',
                'email' => 'info@testphase13.com',
                'status' => 'active',
            ]
        );

        // 2. Setup Super Admin
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin.phase13@rihris.id'],
            [
                'name' => 'Super Administrator Platform',
                'password' => bcrypt('password123'),
                'is_super_admin' => true,
            ]
        );

        // 3. Setup Company Owner
        $this->companyOwner = User::firstOrCreate(
            ['email' => 'owner.phase13@testphase13.com'],
            [
                'name' => 'Bambang HR Owner',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
                'is_super_admin' => false,
            ]
        );

        // Attach owner to tenant
        $this->tenant->users()->syncWithoutDetaching([
            $this->companyOwner->id => ['is_owner' => true]
        ]);

        // 4. Setup Subscription Plans
        $this->starterPlan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'starter-test'],
            [
                'name' => 'Starter SME Test',
                'price_monthly' => 500000,
                'price_yearly' => 5000000,
                'max_employees' => 5,
                'max_admins' => 2,
                'storage_limit_mb' => 1024,
                'has_api_access' => false,
                'has_whatsapp' => false,
                'has_custom_workflows' => false,
                'has_bank_disbursal' => false,
                'has_ebupot_export' => false,
                'has_digital_contracts' => true,
                'is_active' => true,
            ]
        );

        $this->proPlan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'pro-test'],
            [
                'name' => 'Professional Corporate Test',
                'price_monthly' => 1500000,
                'price_yearly' => 15000000,
                'max_employees' => 100,
                'max_admins' => 5,
                'storage_limit_mb' => 10240,
                'has_api_access' => true,
                'has_whatsapp' => true,
                'has_custom_workflows' => true,
                'has_bank_disbursal' => true,
                'has_ebupot_export' => true,
                'has_digital_contracts' => true,
                'is_active' => true,
            ]
        );

        // Active subscription for tenant
        Subscription::updateOrCreate(
            ['tenant_id' => $this->tenant->id],
            [
                'plan_id' => $this->proPlan->id,
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'starts_at' => now()->subMonth(),
            ]
        );

        // 5. Setup Employee
        $this->employee = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'employee.phase13@testphase13.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P13-001',
                'first_name' => 'Bayu',
                'last_name' => 'Pratama',
                'status' => 'active',
                'employment_status' => 'pkwt',
                'join_date' => now()->subMonths(10)->toDateString(),
                'phone' => '081299998888',
                'basic_salary' => 9000000,
            ]
        );
    }

    /**
     * 1. Test Super Admin can impersonate tenant owner with session and audit trail.
     */
    public function test_super_admin_can_impersonate_tenant_owner_and_sets_audit_trail(): void
    {
        $impersonationService = app(ImpersonationService::class);

        $targetUser = $impersonationService->impersonateTenant($this->tenant, $this->superAdmin);

        $this->assertEquals($this->companyOwner->id, $targetUser->id);
        $this->assertEquals($this->superAdmin->id, Session::get('impersonator_super_admin_id'));
        $this->assertEquals($this->tenant->id, Session::get('impersonated_tenant_id'));
        $this->assertTrue($impersonationService->isImpersonating());

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->superAdmin->id,
            'action' => 'superadmin.impersonate_start',
        ]);
    }

    /**
     * 2. Test impersonated user can leave session and revert back to super admin.
     */
    public function test_impersonated_user_can_leave_and_reverts_to_super_admin(): void
    {
        $impersonationService = app(ImpersonationService::class);

        // Start impersonation
        $impersonationService->impersonateTenant($this->tenant, $this->superAdmin);

        // Revert back
        $revertedAdmin = $impersonationService->leaveImpersonation();

        $this->assertNotNull($revertedAdmin);
        $this->assertEquals($this->superAdmin->id, $revertedAdmin->id);
        $this->assertFalse($impersonationService->isImpersonating());
        $this->assertNull(Session::get('impersonator_super_admin_id'));

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->superAdmin->id,
            'action' => 'superadmin.impersonate_leave',
        ]);
    }

    /**
     * 3. Test Plan Gating Service enforces max_employees quota.
     */
    public function test_plan_gating_service_enforces_max_employees_quota(): void
    {
        $gatingService = app(PlanGatingService::class);

        // Set tenant to starter plan with limit of 2
        $limitedPlan = SubscriptionPlan::create([
            'name' => 'Mini SME 2 Pax',
            'slug' => 'mini-sme-2',
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_employees' => 2,
            'max_admins' => 1,
            'storage_limit_mb' => 500,
            'is_active' => true,
        ]);

        Subscription::where('tenant_id', $this->tenant->id)->update(['plan_id' => $limitedPlan->id]);

        // First employee exists (count = 1, max = 2) -> can add employee = true
        $usage = $gatingService->getEmployeeUsage($this->tenant);
        $this->assertEquals(1, $usage['current']);
        $this->assertEquals(2, $usage['max']);
        $this->assertTrue($gatingService->canAddEmployee($this->tenant));

        // Add 2nd employee (count = 2, max = 2) -> quota reached
        Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_number' => 'EMP-P13-002',
            'first_name' => 'Citra',
            'last_name' => 'Lestari',
            'status' => 'active',
            'employment_status' => 'pkwt',
            'join_date' => now()->toDateString(),
            'email' => 'citra.phase13@testphase13.com',
            'basic_salary' => 8500000,
        ]);

        $usageAfter = $gatingService->getEmployeeUsage($this->tenant);
        $this->assertEquals(2, $usageAfter['current']);
        $this->assertTrue($usageAfter['is_exceeded']);
        $this->assertFalse($gatingService->canAddEmployee($this->tenant));
    }

    /**
     * 4. Test Plan Gating Service validates feature flags accurately.
     */
    public function test_plan_gating_service_feature_matrix_and_flags(): void
    {
        $gatingService = app(PlanGatingService::class);

        // Set to starter plan (has_api_access: false, has_digital_contracts: true)
        Subscription::where('tenant_id', $this->tenant->id)->update(['plan_id' => $this->starterPlan->id]);

        $this->assertFalse($gatingService->hasFeature($this->tenant, 'has_api_access'));
        $this->assertTrue($gatingService->hasFeature($this->tenant, 'has_digital_contracts'));

        // Upgrade to Pro plan (has_api_access: true, has_whatsapp: true)
        Subscription::where('tenant_id', $this->tenant->id)->update(['plan_id' => $this->proPlan->id]);

        $this->assertTrue($gatingService->hasFeature($this->tenant, 'has_api_access'));
        $this->assertTrue($gatingService->hasFeature($this->tenant, 'has_whatsapp'));
        $this->assertTrue($gatingService->hasFeature($this->tenant, 'has_custom_workflows'));
    }

    /**
     * 5. Test Super Admin can update tenant subscription plan and billing cycle.
     */
    public function test_super_admin_can_change_tenant_subscription_plan(): void
    {
        $controller = app(\App\Http\Controllers\CompanyManagementController::class);

        $request = new \Illuminate\Http\Request([
            'plan_id' => $this->starterPlan->id,
            'billing_cycle' => 'yearly',
        ]);

        $this->actingAs($this->superAdmin);
        $response = $controller->updatePlan($request, $this->tenant->id);

        $this->assertTrue($response->isRedirection());

        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->starterPlan->id,
            'billing_cycle' => 'yearly',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenant->id,
            'action' => 'tenant.plan_change',
        ]);
    }

    /**
     * 6. Test Platform Health Service calculates accurate MRR, ARR, and system stats.
     */
    public function test_platform_health_service_computes_accurate_mrr_arr_and_system_stats(): void
    {
        $healthService = app(PlatformHealthService::class);

        // Active monthly sub on Pro plan (1,500,000 / month)
        Subscription::where('tenant_id', $this->tenant->id)->update([
            'plan_id' => $this->proPlan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);

        $financials = $healthService->getFinancialMetrics();

        $this->assertGreaterThanOrEqual(1500000.00, $financials['mrr']);
        $this->assertEquals($financials['mrr'] * 12, $financials['arr']);
        $this->assertArrayHasKey('plan_breakdown', $financials);

        $system = $healthService->getSystemHealth();
        $this->assertNotEmpty($system['php_version']);
        $this->assertTrue($system['database_connected']);
        $this->assertArrayHasKey('table_counts', $system);
        $this->assertGreaterThanOrEqual(1, $system['table_counts']['tenants']);
    }

    /**
     * 7. Test Super Admin CMS Inbound Leads CRM status update.
     */
    public function test_super_admin_cms_lead_management_updates_crm_status_and_notes(): void
    {
        $lead = CmsContactMessage::create([
            'name' => 'Irfan Pratama',
            'email' => 'irfan@solusi-energi.co.id',
            'phone' => '081233445566',
            'company' => 'PT Solusi Energi Prima',
            'message' => 'Membutuhkan sistem HRIS dengan e-Bupot CSV dan integrasi presensi biometric.',
            'status' => 'new',
            'is_read' => false,
        ]);

        $controller = app(\App\Http\Controllers\SuperAdmin\CmsLeadController::class);

        $request = new \Illuminate\Http\Request([
            'status' => 'qualified',
            'notes' => 'Telah dihubungi tim sales; demo dijadwalkan hari Jumat pukul 14:00.',
        ]);
        $request->setUserResolver(fn() => $this->superAdmin);

        $response = $controller->updateStatus($request, $lead);

        $this->assertTrue($response->isRedirection());
        $lead->refresh();

        $this->assertEquals('qualified', $lead->status);
        $this->assertTrue($lead->is_read);
        $this->assertNotNull($lead->contacted_at);
        $this->assertEquals($this->superAdmin->id, $lead->contacted_by_user_id);
    }

    /**
     * 8. Test Advanced HR Analytics computes standard turnover rate equation.
     */
    public function test_advanced_hr_analytics_turnover_rate_equation(): void
    {
        $analyticsService = app(AdvancedHrAnalyticsService::class);

        // Simulate 1 resigned employee in the company
        Employee::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'employee_number' => 'EMP-P13-RESIGNED',
            'first_name' => 'Dimas',
            'last_name' => 'Surya',
            'status' => 'resigned',
            'employment_status' => 'resigned',
            'join_date' => now()->subMonths(8)->toDateString(),
            'termination_date' => now()->subMonth()->toDateString(),
            'email' => 'dimas.resigned@testphase13.com',
            'basic_salary' => 8000000,
        ]);

        $metrics = $analyticsService->getTurnoverMetrics($this->company, (int) date('Y'));

        $this->assertArrayHasKey('turnover_rate', $metrics);
        $this->assertArrayHasKey('retention_rate', $metrics);
        $this->assertGreaterThanOrEqual(1, $metrics['total_separations']);
        $this->assertGreaterThan(0.00, $metrics['turnover_rate']);
        $this->assertEquals(round(100.00 - $metrics['turnover_rate'], 2), $metrics['retention_rate']);
    }

    /**
     * 9. Test Advanced HR Analytics computes Bradford Factor index (B = S^2 x D).
     */
    public function test_advanced_hr_analytics_bradford_factor_absenteeism_index(): void
    {
        $analyticsService = app(AdvancedHrAnalyticsService::class);

        // Record 3 distinct non-contiguous absence dates for employee:
        // Jan 10, Feb 15, Mar 20 -> Spells S = 3, Total Days D = 3
        // Bradford B = 3^2 * 3 = 9 * 3 = 27 (Low Risk < 50)
        Attendance::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'date' => date('Y') . '-01-10',
            'status' => 'absent',
        ]);
        Attendance::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'date' => date('Y') . '-02-15',
            'status' => 'absent',
        ]);
        Attendance::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'date' => date('Y') . '-03-20',
            'status' => 'absent',
        ]);

        $bradford = $analyticsService->getBradfordFactorMetrics($this->company, (int) date('Y'));

        $this->assertNotEmpty($bradford['flagged_employees']);
        $flagged = collect($bradford['flagged_employees'])->firstWhere('employee_id', $this->employee->id);

        $this->assertNotNull($flagged);
        $this->assertEquals(3, $flagged['spells']);
        $this->assertEquals(3, $flagged['days']);
        $this->assertEquals(27, $flagged['bradford_score']); // 3^2 * 3 = 27
        $this->assertEquals('low', $flagged['risk_level']);
    }

    /**
     * 10. Test Multi-Channel Notification Service dispatches templated messages with variables.
     */
    public function test_multi_channel_notification_service_dispatches_templated_messages(): void
    {
        $notificationService = app(MultiChannelNotificationService::class);

        $template = NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'SP_WARNING_ALERT',
            'name' => 'Peringatan Surat SP',
            'channel' => 'whatsapp',
            'category' => 'disciplinary',
            'body_template' => 'Pemberitahuan kepada Sdr/i {employee_name} di {company_name}: Telah diterbitkan SP terkait {title} dengan batas waktu {event_date}.',
            'variables' => ['employee_name', 'company_name', 'title', 'event_date'],
            'is_active' => true,
        ]);

        $log = $notificationService->dispatch(
            $template,
            $this->employee,
            [
                'title' => 'Disiplin Kehadiran Kerja',
                'event_date' => '30 November 2026',
            ],
            $this->companyOwner
        );

        $this->assertNotNull($log->id);
        $this->assertEquals('whatsapp', $log->channel);
        $this->assertEquals($this->employee->full_name, $log->recipient_name);
        $this->assertStringContainsString($this->employee->full_name, $log->message_preview);
        $this->assertStringContainsString('Disiplin Kehadiran Kerja', $log->message_preview);
        $this->assertStringContainsString('30 November 2026', $log->message_preview);
        $this->assertEquals('sent', $log->status);

        $this->assertDatabaseHas('notification_dispatch_logs', [
            'id' => $log->id,
            'tenant_id' => $this->tenant->id,
            'template_id' => $template->id,
            'status' => 'sent',
        ]);
    }

    /**
     * 11. Test Advanced HR Analytics CSV export generates valid structured report.
     */
    public function test_advanced_hr_analytics_csv_export_streams_valid_content(): void
    {
        $analyticsService = app(AdvancedHrAnalyticsService::class);

        $csv = $analyticsService->exportAdvancedMetricsCsv($this->company, (int) date('Y'));

        $this->assertStringContainsString('LAPORAN EKSEKUTIF ANALITIK SDM', $csv);
        $this->assertStringContainsString($this->company->name, $csv);
        $this->assertStringContainsString('Tingkat Turnover Tahunan', $csv);
        $this->assertStringContainsString('DISTRIBUSI DEMOGRAFIS KARYAWAN', $csv);
        $this->assertStringContainsString('BIAYA TENAGA KERJA', $csv);
    }
}
