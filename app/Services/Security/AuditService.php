<?php

namespace App\Services\Security;

use App\Models\AuditLog;
use App\Models\CompanyIpWhitelist;

class AuditService
{
    /**
     * Record an audit event log for security traceability.
     */
    public function log(
        int $tenantId,
        ?int $userId,
        string $action,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System / Console',
        ]);
    }

    /**
     * Verify whether an incoming client IP address is allowed by corporate security policies.
     */
    public function isIpAllowed(int $tenantId, string $clientIp): bool
    {
        $hasWhitelists = CompanyIpWhitelist::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->exists();

        // If no whitelist is defined, corporate policy allows all standard connections
        if (!$hasWhitelists) {
            return true;
        }

        return CompanyIpWhitelist::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('ip_address', $clientIp)
            ->exists();
    }

    /**
     * Add an IP address to the whitelist.
     */
    public function addIpWhitelist(int $tenantId, string $ipAddress, ?string $label = null): CompanyIpWhitelist
    {
        return CompanyIpWhitelist::firstOrCreate(
            ['tenant_id' => $tenantId, 'ip_address' => $ipAddress],
            ['label' => $label ?? 'Kantor Pusat / VPN', 'is_active' => true]
        );
    }

    /**
     * Remove an IP address from the whitelist.
     */
    public function removeIpWhitelist(CompanyIpWhitelist $whitelist): void
    {
        $whitelist->delete();
    }
}

