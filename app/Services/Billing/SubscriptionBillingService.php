<?php

namespace App\Services\Billing;

use App\Models\Employee;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionBillingService
{
    /**
     * Generate a new billing invoice for a subscription.
     */
    public function createInvoice(
        Subscription $subscription,
        float $amount,
        float $taxAmount = 0.0,
        ?string $paymentMethod = null
    ): SubscriptionInvoice {
        $seq = str_pad(SubscriptionInvoice::where('tenant_id', $subscription->tenant_id)->count() + 1, 4, '0', STR_PAD_LEFT);
        $invoiceNumber = 'INV-' . date('Ym') . '-' . $seq;

        return SubscriptionInvoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $amount + $taxAmount,
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(7)->toDateString(),
            'payment_method' => $paymentMethod ?? 'bank_transfer',
        ]);
    }

    /**
     * Process payment and activate/extend subscription.
     */
    public function markInvoicePaid(SubscriptionInvoice $invoice, string $paymentMethod): SubscriptionInvoice
    {
        return DB::transaction(function () use ($invoice, $paymentMethod) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
                'payment_method' => $paymentMethod,
            ]);

            $sub = $invoice->subscription;
            if ($sub) {
                $now = Carbon::now();
                $startsAt = $sub->ends_at && $sub->ends_at->isFuture() ? $sub->ends_at : $now;
                $endsAt = $sub->billing_cycle === 'yearly' ? $startsAt->copy()->addYear() : $startsAt->copy()->addMonth();

                $sub->update([
                    'status' => 'active',
                    'starts_at' => $sub->starts_at ?? $now,
                    'ends_at' => $endsAt,
                    'trial_ends_at' => null,
                ]);
            }

            return $invoice->fresh();
        });
    }

    /**
     * Upgrade or change subscription plan and issue invoice.
     */
    public function changePlan(Subscription $subscription, SubscriptionPlan $newPlan, string $cycle = 'monthly'): SubscriptionInvoice
    {
        return DB::transaction(function () use ($subscription, $newPlan, $cycle) {
            $subscription->update([
                'plan_id' => $newPlan->id,
                'billing_cycle' => $cycle,
            ]);

            $price = $cycle === 'yearly' ? (float) $newPlan->price_yearly : (float) $newPlan->price_monthly;
            $tax = round($price * 0.11, 2); // PPN 11%

            return $this->createInvoice($subscription, $price, $tax);
        });
    }

    /**
     * Verify if tenant can add more employees within quota.
     */
    public function canAddEmployee(Tenant $tenant): bool
    {
        $sub = $tenant->subscription()->with('plan')->first();
        if (!$sub || !$sub->plan) {
            return true; // default unconstrained if no plan
        }

        $activeEmployees = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        return $activeEmployees < $sub->plan->max_employees;
    }

    /**
     * Get usage metrics vs quota for the tenant.
     */
    public function getSubscriptionUsage(Tenant $tenant): array
    {
        $sub = $tenant->subscription()->with('plan')->first();
        $plan = $sub?->plan;

        $employeeCount = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $adminCount = User::whereHas('tenants', function ($q) use ($tenant) {
            $q->where('tenant_id', $tenant->id);
        })->count();

        $maxEmployees = $plan?->max_employees ?? 50;
        $maxAdmins = $plan?->max_admins ?? 5;
        $storageLimitMb = $plan?->storage_limit_mb ?? 1024;

        return [
            'plan_name' => $plan?->name ?? 'Free Trial',
            'plan_slug' => $plan?->slug ?? 'trial',
            'status' => $sub?->status ?? 'trial',
            'billing_cycle' => $sub?->billing_cycle ?? 'monthly',
            'ends_at' => $sub?->ends_at,
            'employee_count' => $employeeCount,
            'max_employees' => $maxEmployees,
            'employee_pct' => $maxEmployees > 0 ? min(100, round(($employeeCount / $maxEmployees) * 100)) : 0,
            'admin_count' => $adminCount,
            'max_admins' => $maxAdmins,
            'admin_pct' => $maxAdmins > 0 ? min(100, round(($adminCount / $maxAdmins) * 100)) : 0,
            'storage_limit_mb' => $storageLimitMb,
        ];
    }
}

