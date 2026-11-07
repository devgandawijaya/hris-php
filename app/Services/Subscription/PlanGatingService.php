<?php

namespace App\Services\Subscription;

use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use App\Models\Employee;

class PlanGatingService
{
    /**
     * Get the active subscription plan of the tenant.
     */
    public function getPlan(Tenant $tenant): ?SubscriptionPlan
    {
        $tenant->loadMissing('activeSubscription.plan');
        return $tenant->activeSubscription?->plan;
    }

    /**
     * Get employee headcount quota usage.
     */
    public function getEmployeeUsage(Tenant $tenant): array
    {
        $plan = $this->getPlan($tenant);
        $maxEmployees = $plan?->max_employees ?? 25;

        $currentCount = Employee::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->count();

        $percentage = $maxEmployees > 0 ? round(($currentCount / $maxEmployees) * 100, 1) : 0;
        $isExceeded = $currentCount >= $maxEmployees;

        return [
            'current' => $currentCount,
            'max' => $maxEmployees,
            'percentage' => min(100.0, $percentage),
            'is_exceeded' => $isExceeded,
            'remaining' => max(0, $maxEmployees - $currentCount),
            'plan_name' => $plan?->name ?? 'Free / Trial',
        ];
    }

    /**
     * Check if tenant is allowed to add one more active employee.
     */
    public function canAddEmployee(Tenant $tenant): bool
    {
        $usage = $this->getEmployeeUsage($tenant);
        return !$usage['is_exceeded'];
    }

    /**
     * Check if a specific module/feature is enabled in tenant's plan.
     */
    public function hasFeature(Tenant $tenant, string $featureKey): bool
    {
        $plan = $this->getPlan($tenant);
        if (!$plan) {
            return false;
        }

        // Check explicit boolean column if exists
        if (isset($plan->$featureKey)) {
            return (bool) $plan->$featureKey;
        }

        // Check JSON features array
        $features = $plan->features ?? [];
        if (is_array($features)) {
            return in_array($featureKey, $features) || ($features[$featureKey] ?? false) === true;
        }

        return false;
    }

    /**
     * Get full feature accessibility matrix for tenant.
     */
    public function getFeatureMatrix(Tenant $tenant): array
    {
        $plan = $this->getPlan($tenant);
        $features = [
            'has_api_access' => $this->hasFeature($tenant, 'has_api_access'),
            'has_whatsapp' => $this->hasFeature($tenant, 'has_whatsapp'),
            'has_custom_workflows' => $this->hasFeature($tenant, 'has_custom_workflows'),
            'has_bank_disbursal' => $this->hasFeature($tenant, 'has_bank_disbursal'),
            'has_ebupot_export' => $this->hasFeature($tenant, 'has_ebupot_export'),
            'has_digital_contracts' => $this->hasFeature($tenant, 'has_digital_contracts'),
        ];

        return [
            'plan_name' => $plan?->name ?? 'Trial',
            'features' => $features,
            'usage' => $this->getEmployeeUsage($tenant),
        ];
    }
}
