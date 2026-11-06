<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\Employee;
use App\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultiTenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_scope_strictly_isolates_data_between_companies(): void
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);

        // 1. Create Tenant A
        $tenantA = Tenant::create(['name' => 'Company A', 'slug' => 'company-a']);
        $companyA = Company::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'PT Company A',
            'email' => 'admin@companya.com',
        ]);

        // 2. Create Tenant B
        $tenantB = Tenant::create(['name' => 'Company B', 'slug' => 'company-b']);
        $companyB = Company::withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'PT Company B',
            'email' => 'admin@companyb.com',
        ]);

        // 3. Create employees in both companies
        $empA = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'company_id' => $companyA->id,
            'employee_number' => 'EMP-A-01',
            'first_name' => 'Andi',
            'email' => 'andi@companya.com',
            'join_date' => now()->toDateString(),
            'basic_salary' => 10000000,
        ]);

        $empB = Employee::withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'company_id' => $companyB->id,
            'employee_number' => 'EMP-B-01',
            'first_name' => 'Bambang',
            'email' => 'bambang@companyb.com',
            'join_date' => now()->toDateString(),
            'basic_salary' => 12000000,
        ]);

        // 4. Test Query under Tenant A context
        $tenantManager->setCurrentTenant($tenantA);
        $employeesUnderA = Employee::all();

        $this->assertCount(1, $employeesUnderA);
        $this->assertEquals('EMP-A-01', $employeesUnderA->first()->employee_number);
        $this->assertFalse($employeesUnderA->pluck('employee_number')->contains('EMP-B-01'));

        // 5. Test Query under Tenant B context
        $tenantManager->setCurrentTenant($tenantB);
        $employeesUnderB = Employee::all();

        $this->assertCount(1, $employeesUnderB);
        $this->assertEquals('EMP-B-01', $employeesUnderB->first()->employee_number);
        $this->assertFalse($employeesUnderB->pluck('employee_number')->contains('EMP-A-01'));
    }
}

