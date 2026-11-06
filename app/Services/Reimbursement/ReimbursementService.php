<?php

namespace App\Services\Reimbursement;

use App\Models\Employee;
use App\Models\Reimbursement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReimbursementService
{
    /**
     * Submit a new reimbursement request.
     */
    public function submitClaim(
        Employee $employee,
        string $category,
        string $title,
        float $amount,
        string $expenseDate,
        ?string $description = null,
        ?UploadedFile $receipt = null
    ): Reimbursement {
        $receiptPath = null;
        if ($receipt) {
            $receiptPath = $receipt->store("tenants/{$employee->tenant_id}/reimbursements", 'public');
        }

        return Reimbursement::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'amount' => $amount,
            'expense_date' => $expenseDate,
            'receipt_path' => $receiptPath,
            'status' => 'pending',
        ]);
    }

    /**
     * Manager approves the reimbursement request.
     */
    public function approveByManager(Reimbursement $reimbursement, User $manager): Reimbursement
    {
        $reimbursement->update([
            'status' => 'approved_manager',
            'reviewed_by_manager_id' => $manager->id,
        ]);

        return $reimbursement;
    }

    /**
     * Finance approves the reimbursement request (ready for payment/payroll).
     */
    public function approveByFinance(Reimbursement $reimbursement, User $financeUser): Reimbursement
    {
        $reimbursement->update([
            'status' => 'approved_finance',
            'reviewed_by_finance_id' => $financeUser->id,
        ]);

        return $reimbursement;
    }

    /**
     * Reject reimbursement request with reason.
     */
    public function reject(Reimbursement $reimbursement, User $reviewer, string $reason): Reimbursement
    {
        $reimbursement->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $reimbursement;
    }

    /**
     * Mark reimbursement as paid directly (outside payroll).
     */
    public function markAsPaid(Reimbursement $reimbursement): Reimbursement
    {
        $reimbursement->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $reimbursement;
    }
}
