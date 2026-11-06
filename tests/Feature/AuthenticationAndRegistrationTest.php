<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationAndRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(SubscriptionPlanSeeder::class);
    }

    public function test_saas_registration_provisions_tenant_company_and_owner(): void
    {
        $response = $this->post('/register', [
            'company_name' => 'PT Karya Cipta Digital',
            'company_email' => 'founder@karyacipta.id',
            'pic_name' => 'Bambang Pamungkas',
            'phone' => '+6281122334455',
            'password' => 'SecurePass123#',
            'password_confirmation' => 'SecurePass123#',
            'terms' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Verify Database Records
        $this->assertDatabaseHas('tenants', [
            'name' => 'PT Karya Cipta Digital',
            'status' => 'trial',
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'PT Karya Cipta Digital',
            'email' => 'founder@karyacipta.id',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'founder@karyacipta.id',
            'name' => 'Bambang Pamungkas',
        ]);

        $user = User::where('email', 'founder@karyacipta.id')->first();
        $this->assertTrue($user->hasRole('company-owner'));
    }

    public function test_super_admin_cannot_be_accessed_by_regular_tenant_user(): void
    {
        $tenant = Tenant::create(['name' => 'Acme Inc', 'slug' => 'acme']);
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@acme.com',
            'password' => bcrypt('password'),
            'active_tenant_id' => $tenant->id,
            'is_super_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/super-admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_super_admin_dashboard(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@rihris.id',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Ringkasan Platform SaaS RiHRIS');
    }
}

