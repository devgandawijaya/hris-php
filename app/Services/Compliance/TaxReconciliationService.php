<?php

namespace App\Services\Compliance;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\TaxReconciliation;
use App\Models\ThrRecord;
use Illuminate\Support\Facades\DB;

class TaxReconciliationService
{
    /**
     * Calculate annual tax reconciliation (Masa Pajak Terakhir PPh 21 / 1721-A1) for an employee.
     *
     * @param Employee $employee
     * @param int $taxYear
     * @return TaxReconciliation
     */
    public function reconcileEmployee(Employee $employee, int $taxYear): TaxReconciliation
    {
        // 1. Fetch all payrolls of the employee in the given tax year
        $payrolls = Payroll::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereHas('payrollPeriod', function ($q) use ($taxYear) {
                $q->whereYear('payment_date', $taxYear);
            })
            ->get();

        // 2. Fetch any THR received in the tax year
        $thrTotal = (float) ThrRecord::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('year', $taxYear)
            ->sum('thr_amount');

        $grossPayroll = (float) $payrolls->sum('gross_salary');
        $grossAnnual = $grossPayroll + $thrTotal;

        // 3. Biaya Jabatan: 5% of gross income, max 6,000,000 / year (PMK 168/2023)
        $biayaJabatan = min(round($grossAnnual * 0.05, 2), 6000000.00);

        // 4. Annual BPJS Employee Deductions (JHT + JP are tax deductible)
        $bpjsJhtJpTotal = (float) $payrolls->sum('bpjs_tk_employee');

        // 5. Net Annual Income
        $netAnnualIncome = max(0.0, $grossAnnual - $biayaJabatan - $bpjsJhtJpTotal);

        // 6. PTKP based on tax status
        $ptkpStatus = $employee->tax_ptkp_status ?: 'TK/0';
        $ptkpTable = config('compliance.ptkp', []);
        $ptkp = (float) ($ptkpTable[$ptkpStatus] ?? 54000000.00);

        // 7. PKP (Penghasilan Kena Pajak) - rounded down to thousands per Indonesian tax rule
        $rawPkp = max(0.0, $netAnnualIncome - $ptkp);
        $pkp = floor($rawPkp / 1000) * 1000;

        // 8. Annual PPh 21 calculation using Pasal 17 brackets
        $hasNpwp = !empty($employee->npwp);
        $brackets = config('compliance.pasal_17_brackets', [
            ['limit' => 60000000, 'rate' => 0.05],
            ['limit' => 250000000, 'rate' => 0.15],
            ['limit' => 500000000, 'rate' => 0.25],
            ['limit' => 5000000000, 'rate' => 0.30],
            ['limit' => PHP_INT_MAX, 'rate' => 0.35],
        ]);

        $annualTaxDue = 0.0;
        $remainingPkp = $pkp;
        $prevLimit = 0.0;

        foreach ($brackets as $bracket) {
            $taxableInBracket = min($remainingPkp, $bracket['limit'] - $prevLimit);
            if ($taxableInBracket > 0) {
                $annualTaxDue += $taxableInBracket * $bracket['rate'];
                $remainingPkp -= $taxableInBracket;
                $prevLimit = $bracket['limit'];
            }
            if ($remainingPkp <= 0) {
                break;
            }
        }

        // Apply non-NPWP 20% surcharge if applicable
        if (!$hasNpwp && $annualTaxDue > 0) {
            $annualTaxDue = round($annualTaxDue * 1.20, 2);
        } else {
            $annualTaxDue = round($annualTaxDue, 2);
        }

        // 9. Total PPh 21 TER already withheld in monthly payrolls
        $terPaidTotal = (float) $payrolls->sum('pph21_tax');

        // 10. December adjustment (PPh 21 Masa Terakhir)
        $decemberTax = round($annualTaxDue - $terPaidTotal, 2);

        $reconciliationData = [
            'gross_payroll' => $grossPayroll,
            'thr_total' => $thrTotal,
            'has_npwp' => $hasNpwp,
            'ptkp_status' => $ptkpStatus,
            'payrolls_count' => $payrolls->count(),
            'monthly_ter_paid' => $terPaidTotal,
            'calculated_at' => now()->toIso8601String(),
        ];

        return TaxReconciliation::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'tax_year' => $taxYear,
            ],
            [
                'gross_income_annual' => $grossAnnual,
                'biaya_jabatan' => $biayaJabatan,
                'bpjs_deductions_annual' => $bpjsJhtJpTotal,
                'net_income_annual' => $netAnnualIncome,
                'ptkp_annual' => $ptkp,
                'pkp' => $pkp,
                'pph21_annual_due' => $annualTaxDue,
                'pph21_ter_paid_total' => $terPaidTotal,
                'pph21_december_tax' => $decemberTax,
                'status' => 'draft',
                'reconciliation_data' => $reconciliationData,
            ]
        );
    }

    /**
     * Reconcile all active employees for a tenant for the tax year.
     */
    public function reconcileTenant(int $tenantId, int $taxYear): array
    {
        return DB::transaction(function () use ($tenantId, $taxYear) {
            $employees = Employee::where('tenant_id', $tenantId)->get();
            $reconciled = [];

            foreach ($employees as $employee) {
                $reconciled[] = $this->reconcileEmployee($employee, $taxYear);
            }

            return [
                'tax_year' => $taxYear,
                'reconciled_employees' => count($reconciled),
                'total_annual_tax' => round(collect($reconciled)->sum('pph21_annual_due'), 2),
                'records' => $reconciled,
            ];
        });
    }
}

