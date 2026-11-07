<?php

namespace App\Services\Financial;

use App\Models\FlexBenefitAllocation;
use App\Models\FlexBenefitCategory;
use App\Models\FlexBenefitClaim;
use App\Models\Employee;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FlexBenefitService
{
    /**
     * Initialize or allocate annual flexible benefit points for an employee.
     */
    public function allocatePoints(
        int $tenantId,
        int $companyId,
        int $employeeId,
        int $periodYear,
        float $pointsAllocated = 5000000.00,
        ?int $jobGradeId = null
    ): FlexBenefitAllocation {
        return FlexBenefitAllocation::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'employee_id' => $employeeId,
                'period_year' => $periodYear,
            ],
            [
                'job_grade_id' => $jobGradeId,
                'total_points_allocated' => $pointsAllocated,
                'points_used' => 0.00,
                'points_remaining' => $pointsAllocated,
                'status' => 'active',
            ]
        );
    }

    /**
     * Submit a flexible benefit reimbursement claim.
     */
    public function submitClaim(
        FlexBenefitAllocation $allocation,
        int $categoryId,
        float $claimAmount,
        string $claimDate,
        ?string $receiptUrl = null,
        ?string $notes = null
    ): FlexBenefitClaim {
        if ($claimAmount <= 0) {
            throw new InvalidArgumentException('Claim amount must be greater than zero.');
        }

        if ($claimAmount > (float) $allocation->points_remaining) {
            throw new InvalidArgumentException(
                sprintf('Insufficient flex points balance. Remaining: Rp %s, requested: Rp %s',
                    number_format($allocation->points_remaining, 0, ',', '.'),
                    number_format($claimAmount, 0, ',', '.')
                )
            );
        }

        $category = FlexBenefitCategory::findOrFail($categoryId);

        $claimNumber = 'FLX-' . date('Y') . '-' . strtoupper(Str::random(6));

        return FlexBenefitClaim::create([
            'tenant_id' => $allocation->tenant_id,
            'flex_benefit_allocation_id' => $allocation->id,
            'flex_benefit_category_id' => $category->id,
            'employee_id' => $allocation->employee_id,
            'claim_number' => $claimNumber,
            'claim_date' => $claimDate,
            'claim_amount' => $claimAmount,
            'points_deducted' => $claimAmount, // 1 Poin = Rp 1
            'receipt_file_url' => $receiptUrl,
            'notes' => $notes,
            'status' => 'submitted',
        ]);
    }

    /**
     * Approve a claim and deduct points from allocation.
     */
    public function approveClaim(FlexBenefitClaim $claim, int $approvedByUserId): FlexBenefitClaim
    {
        if ($claim->status === 'approved') {
            return $claim;
        }

        $allocation = $claim->allocation;

        if ((float) $claim->points_deducted > (float) $allocation->points_remaining) {
            throw new InvalidArgumentException('Insufficient points remaining to approve claim.');
        }

        $allocation->points_used = (float) $allocation->points_used + (float) $claim->points_deducted;
        $allocation->points_remaining = (float) $allocation->points_remaining - (float) $claim->points_deducted;
        $allocation->save();

        $claim->status = 'approved';
        $claim->approved_by_user_id = $approvedByUserId;
        $claim->save();

        return $claim;
    }

    /**
     * Reject a claim.
     */
    public function rejectClaim(FlexBenefitClaim $claim, int $reviewedByUserId, ?string $reason = null): FlexBenefitClaim
    {
        $claim->status = 'rejected';
        $claim->approved_by_user_id = $reviewedByUserId;
        if ($reason) {
            $claim->notes = ($claim->notes ? $claim->notes . " | " : '') . "Ditolak: " . $reason;
        }
        $claim->save();

        return $claim;
    }

    /**
     * Get summary usage breakdown across categories for an employee/year.
     */
    public function getCategoryUsageBreakdown(int $tenantId, int $companyId, int $periodYear): array
    {
        $categories = FlexBenefitCategory::where('company_id', $companyId)->get();
        $breakdown = [];

        foreach ($categories as $cat) {
            $totalClaimed = (float) FlexBenefitClaim::where('tenant_id', $tenantId)
                ->where('flex_benefit_category_id', $cat->id)
                ->where('status', 'approved')
                ->whereYear('claim_date', $periodYear)
                ->sum('claim_amount');

            $breakdown[] = [
                'category_id' => $cat->id,
                'name' => $cat->name,
                'code' => $cat->code,
                'total_claimed' => $totalClaimed,
            ];
        }

        return $breakdown;
    }
}
