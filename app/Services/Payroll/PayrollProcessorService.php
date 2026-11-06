<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\OvertimeRequest;
use App\Models\Reimbursement;
use App\Services\Compliance\BpjsCalculator;
use App\Services\Compliance\Pph21TerCalculator;
use Illuminate\Support\Facades\DB;

class PayrollProcessorService
{
    public function __construct(
        protected BpjsCalculator $bpjsCalculator,
        protected Pph21TerCalculator $pph21Calculator
    ) {}

    /**
     * Process payroll calculation for a period.
     *
     * @param PayrollPeriod $period
     * @param array|null $employeeIds
     * @return array Summary of processed payrolls
     */
    public function processPeriod(PayrollPeriod $period, ?array $employeeIds = null): array
    {
        return DB::transaction(function () use ($period, $employeeIds) {
            $query = Employee::query()
                ->where('tenant_id', $period->tenant_id)
                ->where('status', 'active');

            if (!empty($employeeIds)) {
                $query->whereIn('id', $employeeIds);
            }

            $employees = $query->with(['salaryComponents.salaryComponent'])->get();
            $processedCount = 0;
            $totalGross = 0;
            $totalNet = 0;
            $totalTax = 0;

            foreach ($employees as $employee) {
                $payroll = $this->calculateEmployeePayroll($period, $employee);
                $processedCount++;
                $totalGross += (float) $payroll->gross_salary;
                $totalNet += (float) $payroll->take_home_pay;
                $totalTax += (float) $payroll->pph21_tax;
            }

            $period->update([
                'status' => 'processed',
            ]);

            return [
                'period_id' => $period->id,
                'period_name' => $period->name,
                'processed_employees' => $processedCount,
                'total_gross' => round($totalGross, 2),
                'total_net' => round($totalNet, 2),
                'total_tax' => round($totalTax, 2),
            ];
        });
    }

    /**
     * Calculate and persist payroll for a single employee in a period.
     */
    public function calculateEmployeePayroll(PayrollPeriod $period, Employee $employee): Payroll
    {
        $basicSalary = (float) $employee->basic_salary;

        // 1. Fetch active salary components
        $components = $employee->salaryComponents()
            ->where('is_active', true)
            ->with('salaryComponent')
            ->get();

        $fixedAllowancesTotal = 0;
        $variableAllowancesTotal = 0;
        $customDeductionsTotal = 0;
        $itemizedAllowances = [];
        $itemizedDeductions = [];

        foreach ($components as $empComp) {
            $comp = $empComp->salaryComponent;
            $amount = (float) $empComp->amount;

            if ($comp->type === 'allowance') {
                if ($comp->is_fixed) {
                    $fixedAllowancesTotal += $amount;
                } else {
                    $variableAllowancesTotal += $amount;
                }
                $itemizedAllowances[] = [
                    'name' => $comp->name,
                    'is_fixed' => $comp->is_fixed,
                    'amount' => $amount,
                ];
            } elseif ($comp->type === 'deduction') {
                $customDeductionsTotal += $amount;
                $itemizedDeductions[] = [
                    'name' => $comp->name,
                    'amount' => $amount,
                ];
            }
        }

        $allowancesTotal = $fixedAllowancesTotal + $variableAllowancesTotal;

        // 2. Fetch approved Overtime during the period
        $overtimePay = (float) OvertimeRequest::query()
            ->where('tenant_id', $period->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$period->start_date->format('Y-m-d'), $period->end_date->format('Y-m-d')])
            ->sum('calculated_pay');

        // 3. Fetch approved Reimbursements pending disbursement
        $reimbursements = Reimbursement::query()
            ->where('tenant_id', $period->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'approved_finance')
            ->whereNull('payroll_id')
            ->where('expense_date', '<=', $period->end_date->format('Y-m-d'))
            ->get();

        $reimbursementPay = (float) $reimbursements->sum('amount');

        // 4. Base wage for BPJS (Gaji Pokok + Tunjangan Tetap)
        $bpjsBaseWage = $basicSalary + $fixedAllowancesTotal;
        $bpjsResult = $this->bpjsCalculator->calculate($bpjsBaseWage);

        $bpjsTkEmployee = $bpjsResult['bpjs_tk_total_employee'];
        $bpjsTkEmployer = $bpjsResult['bpjs_tk_total_employer'];
        $bpjsKesEmployee = $bpjsResult['bpjs_kes_employee'];
        $bpjsKesEmployer = $bpjsResult['bpjs_kes_employer'];

        // 5. Gross Salary = Basic Salary + All Allowances + Overtime Pay
        $grossSalary = $basicSalary + $allowancesTotal + $overtimePay;

        // 6. PPh 21 TER Calculation (PP 58/2023 & PMK 168/2023)
        $ptkpStatus = $employee->tax_ptkp_status ?: 'TK/0';
        $hasNpwp = !empty($employee->npwp);
        $taxResult = $this->pph21Calculator->calculateMonthly($grossSalary, $ptkpStatus, $hasNpwp);
        $pph21Tax = $taxResult['tax'];

        // 7. Deductions total (excluding tax, separated for clean reporting)
        $deductionsTotal = $bpjsTkEmployee + $bpjsKesEmployee + $customDeductionsTotal;

        // 8. Net Take Home Pay (THP)
        // THP = Gross - Total Deductions - PPh 21 + Reimbursement
        $takeHomePay = max(0, ($grossSalary - $deductionsTotal - $pph21Tax) + $reimbursementPay);

        // 9. Structured breakdown for payslip rendering
        $breakdown = [
            'earnings' => [
                'basic_salary' => $basicSalary,
                'fixed_allowances' => $fixedAllowancesTotal,
                'variable_allowances' => $variableAllowancesTotal,
                'allowances_itemized' => $itemizedAllowances,
                'overtime_pay' => $overtimePay,
                'reimbursement_pay' => $reimbursementPay,
                'gross_salary' => $grossSalary,
            ],
            'deductions' => [
                'bpjs_tk_jht_employee' => $bpjsResult['jht_employee'],
                'bpjs_tk_jp_employee' => $bpjsResult['jp_employee'],
                'bpjs_tk_total_employee' => $bpjsTkEmployee,
                'bpjs_kes_employee' => $bpjsKesEmployee,
                'pph21_tax' => $pph21Tax,
                'custom_deductions' => $customDeductionsTotal,
                'deductions_itemized' => $itemizedDeductions,
                'total_deductions' => $deductionsTotal + $pph21Tax,
            ],
            'employer_contributions' => [
                'bpjs_tk_jht_employer' => $bpjsResult['jht_employer'],
                'bpjs_tk_jkm_employer' => $bpjsResult['jkm_employer'],
                'bpjs_tk_jkk_employer' => $bpjsResult['jkk_employer'],
                'bpjs_tk_jp_employer' => $bpjsResult['jp_employer'],
                'bpjs_tk_total_employer' => $bpjsTkEmployer,
                'bpjs_kes_employer' => $bpjsKesEmployer,
                'total_employer_contributions' => $bpjsResult['total_employer_contribution'],
            ],
            'tax_meta' => [
                'ptkp_status' => $ptkpStatus,
                'category' => $taxResult['category'],
                'ter_rate' => $taxResult['rate'],
                'effective_rate' => $taxResult['effective_rate'],
                'has_npwp' => $hasNpwp,
            ],
            'net_take_home_pay' => $takeHomePay,
        ];

        // 10. Save or update payroll record
        $payroll = Payroll::updateOrCreate(
            [
                'tenant_id' => $period->tenant_id,
                'payroll_period_id' => $period->id,
                'employee_id' => $employee->id,
            ],
            [
                'basic_salary' => $basicSalary,
                'allowances_total' => $allowancesTotal,
                'overtime_pay' => $overtimePay,
                'reimbursement_pay' => $reimbursementPay,
                'gross_salary' => $grossSalary,
                'deductions_total' => $deductionsTotal,
                'bpjs_tk_employee' => $bpjsTkEmployee,
                'bpjs_tk_employer' => $bpjsTkEmployer,
                'bpjs_kes_employee' => $bpjsKesEmployee,
                'bpjs_kes_employer' => $bpjsKesEmployer,
                'pph21_tax' => $pph21Tax,
                'take_home_pay' => $takeHomePay,
                'breakdown' => $breakdown,
                'status' => 'draft',
            ]
        );

        // Attach reimbursements to this payroll
        foreach ($reimbursements as $reimbursement) {
            $reimbursement->update(['payroll_id' => $payroll->id]);
        }

        return $payroll;
    }

    /**
     * Approve payroll period and mark all payrolls as approved.
     */
    public function approvePeriod(PayrollPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            $period->update(['status' => 'approved']);
            $period->payrolls()->update(['status' => 'approved']);
        });
    }

    /**
     * Mark payroll period and payrolls as paid.
     */
    public function markAsPaid(PayrollPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            $now = now();
            $period->update(['status' => 'paid']);
            $period->payrolls()->update([
                'status' => 'paid',
                'paid_at' => $now,
            ]);

            // Mark reimbursements linked to payrolls in this period as paid
            $payrollIds = $period->payrolls()->pluck('id');
            Reimbursement::whereIn('payroll_id', $payrollIds)
                ->update([
                    'status' => 'paid',
                    'paid_at' => $now,
                ]);
        });
    }
}
