<?php

namespace App\Services\Finance;

use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\LoanInstallment;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\Notification\AppNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeLoanService
{
    public function __construct(
        protected AppNotificationService $notificationService
    ) {}

    /**
     * Apply for an employee loan / salary advance (Kasbon)
     */
    public function applyLoan(Employee $employee, array $data): EmployeeLoan
    {
        return DB::transaction(function () use ($employee, $data) {
            $amount = (float) $data['amount'];
            $interestRate = isset($data['interest_rate']) ? (float)$data['interest_rate'] : 0.0;
            $termMonths = max(1, (int) ($data['term_months'] ?? 1));

            // Flat interest calculation
            $totalInterest = $amount * ($interestRate / 100) * ($termMonths / 12);
            $totalPayable = round($amount + $totalInterest, 2);
            $monthlyInstallment = round($totalPayable / $termMonths, 2);

            $year = date('Y');
            $count = EmployeeLoan::where('tenant_id', $employee->tenant_id)
                ->whereYear('created_at', $year)
                ->count() + 1;
            $loanNumber = sprintf("LOAN-%s-%04d", $year, $count);

            return EmployeeLoan::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'loan_number' => $loanNumber,
                'loan_type' => $data['loan_type'] ?? 'salary_advance',
                'amount' => $amount,
                'interest_rate' => $interestRate,
                'term_months' => $termMonths,
                'monthly_installment' => $monthlyInstallment,
                'total_payable' => $totalPayable,
                'remaining_amount' => $totalPayable,
                'status' => 'pending',
                'reason' => $data['reason'],
            ]);
        });
    }

    /**
     * Approve loan and generate installment repayment schedule
     */
    public function approveAndDisburse(EmployeeLoan $loan, User $approver): EmployeeLoan
    {
        return DB::transaction(function () use ($loan, $approver) {
            $loan->update([
                'status' => 'active',
                'approved_by_user_id' => $approver->id,
                'approved_at' => now(),
                'disbursed_at' => now(),
            ]);

            // Generate monthly installment schedule starting next month
            $dueDate = now()->addMonth()->startOfMonth()->addDays(24); // e.g. 25th of next month (payday)
            $remaining = $loan->total_payable;

            for ($i = 1; $i <= $loan->term_months; $i++) {
                $installmentAmount = ($i === $loan->term_months) ? $remaining : $loan->monthly_installment;
                $remaining -= $installmentAmount;

                LoanInstallment::create([
                    'tenant_id' => $loan->tenant_id,
                    'employee_loan_id' => $loan->id,
                    'installment_number' => $i,
                    'due_date' => $dueDate->toDateString(),
                    'amount' => $installmentAmount,
                    'status' => 'pending',
                ]);

                $dueDate = $dueDate->copy()->addMonth();
            }

            // Send notification to employee
            if ($loan->employee?->user) {
                $this->notificationService->sendToUser(
                    $loan->employee->user,
                    "Pinjaman {$loan->loan_number} Disetujui",
                    "Permohonan pinjaman/kasbon sebesar Rp " . number_format($loan->amount, 0, ',', '.') . " telah disetujui dan dicairkan.",
                    'success',
                    route('ess.dashboard')
                );
            }

            return $loan;
        });
    }

    /**
     * Record an installment payment (manual or via payroll deduction)
     */
    public function recordRepayment(
        LoanInstallment $installment,
        bool $fromPayroll = false,
        ?PayrollPeriod $period = null
    ): LoanInstallment {
        return DB::transaction(function () use ($installment, $fromPayroll, $period) {
            $installment->update([
                'status' => $fromPayroll ? 'deducted_from_payroll' : 'paid',
                'paid_at' => now(),
                'payroll_period_id' => $period?->id,
            ]);

            $loan = $installment->loan;
            $newRemaining = max(0, round($loan->remaining_amount - $installment->amount, 2));

            $loanUpdate = ['remaining_amount' => $newRemaining];
            if ($newRemaining <= 0) {
                $loanUpdate['status'] = 'completed';
            }
            $loan->update($loanUpdate);

            return $installment;
        });
    }
}
