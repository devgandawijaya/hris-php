<?php

namespace App\Services\Financial;

use App\Models\Company;
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
    public function registerMember($arg1, $arg2 = [], $arg3 = []): EmployeeCooperative
    {
        if ($arg1 instanceof Company) {
            $company = $arg1;
            $employee = $arg2;
            $data = is_array($arg3) ? $arg3 : [];
        } else {
            $employee = $arg1;
            $data = is_array($arg2) ? $arg2 : [];
            $company = $employee->company;
        }

        $tenantId = $employee->tenant_id;

        $existing = EmployeeCooperative::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException("Karyawan telah terdaftar sebagai anggota koperasi.");
        }

        $seq = EmployeeCooperative::where('tenant_id', $tenantId)->count() + 1;
        do {
            $memberNumber = sprintf('KOPKAR-%s-%04d', date('Y'), $seq++);
        } while (EmployeeCooperative::where('member_number', $memberNumber)->exists());

        $principalSavings = (float) ($data['principal_savings_paid'] ?? 100000.00);
        $mandatorySavings = (float) ($data['mandatory_savings'] ?? 50000.00);
        $voluntarySavings = (float) ($data['voluntary_savings'] ?? 0.00);

        $member = EmployeeCooperative::create([
            'tenant_id' => $tenantId,
            'company_id' => $company ? $company->id : $employee->company_id,
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
            'transaction_type' => 'principal_saving',
            'amount' => $principalSavings,
            'transaction_date' => $member->join_date,
            'is_payroll_deducted' => true,
            'reference_notes' => 'Pembayaran Simpanan Pokok awal keanggotaan Koperasi Karyawan.',
        ]);

        return $member;
    }

    /**
     * Process monthly savings deduction via payroll (mandatory + voluntary)
     */
    public function processMonthlyDeduction(EmployeeCooperative $member, ?string $transactionDate = null): array
    {
        $tDate = $transactionDate ? Carbon::parse($transactionDate)->toDateString() : Carbon::today()->toDateString();
        $transactions = [];

        // Mandatory saving
        if ($member->mandatory_savings > 0) {
            $t1 = CooperativeTransaction::create([
                'tenant_id' => $member->tenant_id,
                'employee_cooperative_id' => $member->id,
                'transaction_type' => 'mandatory_saving',
                'amount' => $member->mandatory_savings,
                'transaction_date' => $tDate,
                'is_payroll_deducted' => true,
                'reference_notes' => "Pemotongan rutin simpanan wajib via payroll ({$tDate}).",
            ]);
            $transactions[] = $t1;
        }

        // Voluntary saving
        if ($member->voluntary_savings > 0) {
            $t2 = CooperativeTransaction::create([
                'tenant_id' => $member->tenant_id,
                'employee_cooperative_id' => $member->id,
                'transaction_type' => 'voluntary_saving',
                'amount' => $member->voluntary_savings,
                'transaction_date' => $tDate,
                'is_payroll_deducted' => true,
                'reference_notes' => "Pemotongan rutin simpanan sukarela via payroll ({$tDate}).",
            ]);
            $transactions[] = $t2;
        }

        $totalDeduction = (float) $member->mandatory_savings + (float) $member->voluntary_savings;
        $member->increment('total_accumulated_savings', $totalDeduction);

        return $transactions;
    }

    /**
     * Record a generic or specific cooperative transaction
     */
    public function recordTransaction(
        EmployeeCooperative $member,
        string $type,
        float $amount,
        ?string $date = null,
        bool $isPayroll = false,
        ?string $notes = null
    ): CooperativeTransaction {
        $tDate = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        $txn = CooperativeTransaction::create([
            'tenant_id' => $member->tenant_id,
            'employee_cooperative_id' => $member->id,
            'transaction_type' => $type,
            'amount' => $amount,
            'transaction_date' => $tDate,
            'is_payroll_deducted' => $isPayroll,
            'reference_notes' => $notes,
        ]);

        if ($type === 'loan_disbursement') {
            $member->increment('active_loan_balance', $amount);
        } elseif ($type === 'loan_repayment') {
            $newBalance = max(0, (float) $member->active_loan_balance - $amount);
            $member->update(['active_loan_balance' => $newBalance]);
        } elseif (in_array($type, ['mandatory_saving', 'voluntary_saving'])) {
            $member->increment('total_accumulated_savings', $amount);
        }

        return $txn;
    }

    /**
     * Get financial summary for a member
     */
    public function getMemberFinancialSummary(EmployeeCooperative $member): array
    {
        $member->refresh();

        return [
            'total_accumulated_savings' => (float) $member->total_accumulated_savings,
            'active_loan_balance' => (float) $member->active_loan_balance,
        ];
    }

    /**
     * Record monthly recurring payroll deduction for savings (backward compatibility)
     */
    public function recordMonthlyPayrollDeduction(EmployeeCooperative $member, ?string $transactionDate = null): CooperativeTransaction
    {
        $txns = $this->processMonthlyDeduction($member, $transactionDate);
        return $txns[0] ?? new CooperativeTransaction();
    }

    /**
     * Disburse loan from cooperative
     */
    public function disburseLoan(EmployeeCooperative $member, float $loanAmount, string $notes): CooperativeTransaction
    {
        return $this->recordTransaction($member, 'loan_disbursement', $loanAmount, null, false, $notes);
    }

    /**
     * Repay loan from cooperative (via payroll or cash)
     */
    public function repayLoan(EmployeeCooperative $member, float $paymentAmount): CooperativeTransaction
    {
        return $this->recordTransaction($member, 'loan_repayment', $paymentAmount, null, true, 'Pembayaran angsuran pinjaman koperasi.');
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
