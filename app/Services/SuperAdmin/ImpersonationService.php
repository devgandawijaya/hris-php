<?php

namespace App\Services\SuperAdmin;

use App\Models\Tenant;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationService
{
    /**
     * Start impersonating a tenant as its owner or primary admin.
     */
    public function impersonateTenant(Tenant $tenant, User $superAdmin): User
    {
        // Find owner user or first user in tenant
        $targetUser = $tenant->users()
            ->wherePivot('is_owner', true)
            ->first();

        if (!$targetUser) {
            $targetUser = $tenant->users()->first();
        }

        if (!$targetUser) {
            throw new \RuntimeException("Perusahaan {$tenant->name} belum memiliki akun pengguna yang dapat diimpersonasi.");
        }

        // Store original super admin credentials in session
        Session::put('impersonator_super_admin_id', $superAdmin->id);
        Session::put('impersonated_tenant_id', $tenant->id);
        Session::put('active_tenant_id', $tenant->id);

        // Record AuditLog
        AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => $superAdmin->id,
            'action' => 'superadmin.impersonate_start',
            'auditable_type' => Tenant::class,
            'auditable_id' => $tenant->id,
            'new_values' => [
                'target_user_id' => $targetUser->id,
                'target_user_name' => $targetUser->name,
                'target_tenant_name' => $tenant->name,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Login as the target user
        Auth::login($targetUser);

        return $targetUser;
    }

    /**
     * Leave impersonation and revert back to super admin.
     */
    public function leaveImpersonation(): ?User
    {
        $superAdminId = Session::get('impersonator_super_admin_id');
        $tenantId = Session::get('impersonated_tenant_id');

        if (!$superAdminId) {
            return null;
        }

        $superAdmin = User::find($superAdminId);
        if (!$superAdmin) {
            return null;
        }

        if ($tenantId) {
            AuditLog::create([
                'tenant_id' => $tenantId,
                'user_id' => $superAdmin->id,
                'action' => 'superadmin.impersonate_leave',
                'auditable_type' => Tenant::class,
                'auditable_id' => $tenantId,
                'new_values' => [
                    'reverted_at' => now()->toDateTimeString(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        // Forget impersonation session
        Session::forget(['impersonator_super_admin_id', 'impersonated_tenant_id']);

        // Log back as super admin
        Auth::login($superAdmin);

        return $superAdmin;
    }

    /**
     * Check if current session is under active impersonation.
     */
    public function isImpersonating(): bool
    {
        return Session::has('impersonator_super_admin_id');
    }
}
