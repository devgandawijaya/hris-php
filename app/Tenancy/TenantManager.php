<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Models\Company;

class TenantManager
{
    protected ?Tenant $currentTenant = null;
    protected ?Company $currentCompany = null;

    /**
     * Set the currently active tenant in the application container.
     */
    public function setCurrentTenant(?Tenant $tenant): self
    {
        $this->currentTenant = $tenant;

        if ($tenant) {
            // Find default or primary company for this tenant
            $this->currentCompany = Company::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->first();
        } else {
            $this->currentCompany = null;
        }

        return $this;
    }

    /**
     * Get the active tenant model instance.
     */
    public function getTenant(): ?Tenant
    {
        return $this->currentTenant;
    }

    /**
     * Check if a tenant is currently bound.
     */
    public function hasActiveTenant(): bool
    {
        return !is_null($this->currentTenant);
    }

    /**
     * Get the active tenant ID.
     */
    public function getActiveTenantId(): ?int
    {
        return $this->currentTenant?->id;
    }

    /**
     * Get active primary company.
     */
    public function getCompany(): ?Company
    {
        return $this->currentCompany;
    }
}

