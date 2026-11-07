<?php

namespace App\Services\Financial;

use App\Models\Employee;
use App\Models\EmployeeCooperative;
use App\Models\CooperativeTransaction;
use Carbon\Carbon;
use InvalidArgumentException;

class EmployeeCooperativeService
{
    /**
     * Register an employee to the Employee Cooperative (Koperasi Karyawan)
     */
    public function registerMember(Employee $employee, array $data): EmployeeCooperative
    {
        $tenantId = $employee->tenant_id;

        $existing = EmployeeCooperative::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException("Karyawan telah terdaftar sebagai anggota koperasi.");
        }

        $nextNum = EmployeeCooperative::where('tenant_id', $tenantId)->count() + 1;
        $memberNumber = sprintf('KOPKAR-%s-%04d', date('Y'), $nextNum);

        $principalSavings = (float) ($data['principal_savings_paid'] ?? 100000.00);
        $mandatorySavings = (float) ($data['mandatory_savings'] ?? 50000.00);
        $voluntarySavings = (float) ($data['voluntary_savings'] ?? 0.00);

        $member = EmployeeCooperative::create([
            'tenant_id' => $tenantId,
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'member_number' => $memberNumber,
            'join_date' => Carbon::parse($data['join_date'] ?? date('Y-m-d'))->toDateString(),
            'status' => 'active',
            'mandatory_savings' => $mandatorySavings,
            'voluntary_savings' => $voluntarySavings,
            'principal_savings_paid' => $principalSavings,
            'total_accumulated_savings' => $principalSavings,
            'active_loan_balance' => 0.00,
        ]);

        // Record initial principal savings transaction
        CooperativeTransaction::create([
            'tenant_id' => $tenantId,
            'employee_cooperative_id' => $member->id,
            'transaction_type' => 'mandatory_saving',
            'amount' => $principalSavings,
            'transaction_date' => $member->join_date,
            'is_payroll_deducted' => true,
            'reference_notes' => 'Pembayaran Simpanan Pokok awal keanggotaan Koperasi Karyawan.',
        ]);

        return $member;
    }

    /**
     * Record monthly recurring payroll deduction for savings
     */
    public function recordMonthlyPayrollDeduction(EmployeeCooperative $member, ?string $transactionDate = null): CooperativeTransaction
    {
        $totalDeduction = $member->mandatory_savings + $member->voluntary_savings;
        $tDate = $transactionDate ? Carbon::parse($transactionDate)->toDateString() : Carbon::today()->toDateString();

        $transaction = CooperativeTransaction::create([
            'tenant_id' => $member->tenant_id,
            'employee_cooperative_id' => $member->id,
            'transaction_type' => 'mandatory_saving',
            'amount' => $totalDeduction,
            'transaction_date' => $tDate,
            'is_payroll_deducted' => true,
            'reference_notes' => "Pemotongan rutin simpanan wajib & sukarela via payroll ({$tDate}).",
        ]);

        $member->increment('total_accumulated_savings', $totalDeduction);

        return $transaction;
    }

    /**
     * Disburse loan from cooperative
     */
    public function disburseLoan(EmployeeCooperative $member, float $loanAmount, string $notes): CooperativeTransaction
    {
        if ($loanAmount <= 0) {
            throw new InvalidArgumentException("Nominal pinjaman koperasi harus lebih dari 0.");
        }

        $transaction = CooperativeTransaction::create([
            'tenant_id' => $member->tenant_id,
            'employee_cooperative_id' => $member->id,
            'transaction_type' => 'loan_disbursement',
            'amount' => $loanAmount,
            'transaction_date' => Carbon::today()->toDateString(),
            'is_payroll_deducted' => false,
            'reference_notes' => $notes,
        ]);

        $member->increment('active_loan_balance', $loanAmount);

        return $transaction;
    }

    /**
     * Repay loan from cooperative (via payroll or cash)
     */
    public function repayLoan(EmployeeCooperative $member, float $paymentAmount): CooperativeTransaction
    {
        if ($paymentAmount <= 0) {
            throw new InvalidArgumentException("Nominal pembayaran cicilan harus lebih dari 0.");
        }

        $transaction = CooperativeTransaction::create([
            'tenant_id' => $member->tenant_id,
            'employee_cooperative_id' => $member->id,
            'transaction_type' => 'loan_repayment',
            'amount' => $paymentAmount,
            'transaction_date' => Carbon::today()->toDateString(),
            'is_payroll_deducted' => true,
            'reference_notes' => 'Pembayaran angsuran pinjaman koperasi.',
        ]);

        $newBalance = max(0, (float) $member->active_loan_balance - $paymentAmount);
        $member->update(['active_loan_balance' => $newBalance]);

        return $transaction;
    }

    /**
     * Disburse employee welfare assistance / bereavement fund
     */
    public function disburseWelfareAid(EmployeeCooperative $member, float $amount, string $reason): CooperativeTransaction
    {
        return CooperativeTransaction::create([
            'tenant_id' => $member->tenant_id,
            'employee_cooperative_id' => $member->id,
            'transaction_type' => 'welfare_aid_disbursement',
            'amount' => $amount,
            'transaction_date' => Carbon::today()->toDateString(),
            'is_payroll_deducted' => false,
            'reference_notes' => "Penyaluran Santunan Dana Sosial: {$reason}",
        ]);
    }
}
