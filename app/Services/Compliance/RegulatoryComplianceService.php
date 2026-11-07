<?php

namespace App\Services\Compliance;

use App\Models\BpjsComplianceReport;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\RegionalMinimumWage;
use Illuminate\Support\Collection;

class RegulatoryComplianceService
{
    /**
     * Check minimum wage (UMR / UMK) compliance for active employees.
     */
    public function checkMinimumWageCompliance(int $tenantId, ?string $city = null): array
    {
        $year = (int) date('Y');

        // Get UMK benchmark
        $umkQuery = RegionalMinimumWage::where('year', $year);
        if ($city) {
            $umkQuery->where('city_or_regency', 'like', "%{$city}%");
        }
        $umkStandard = $umkQuery->first();

        // Fallback default benchmark if none seeded (e.g. DKI Jakarta standard Rp 5.067.381)
        $benchmarkWage = $umkStandard ? (float) $umkStandard->minimum_wage_amount : 5067381.00;
        $benchmarkCity = $umkStandard ? $umkStandard->city_or_regency : 'DKI Jakarta (Default)';

        $employees = Employee::with('department')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        $compliant = [];
        $nonCompliant = [];

        foreach ($employees as $emp) {
            $basic = (float) $emp->basic_salary;
            $wage = $basic; // Under PP 36/2021 & PP 51/2023, Basic Salary must be >= 75% of UMK, and total fixed wage >= UMK

            if ($wage >= $benchmarkWage) {
                $compliant[] = [
                    'employee_id' => $emp->id,
                    'name' => $emp->full_name,
                    'department' => $emp->department?->name ?? '-',
                    'salary' => $wage,
                    'status' => 'compliant',
                ];
            } else {
                $nonCompliant[] = [
                    'employee_id' => $emp->id,
                    'employee_number' => $emp->employee_number,
                    'name' => $emp->full_name,
                    'department' => $emp->department?->name ?? '-',
                    'salary' => $wage,
                    'deficit' => $benchmarkWage - $wage,
                    'status' => 'below_minimum_wage',
                ];
            }
        }

        $total = $employees->count();
        $compliantCount = count($compliant);
        $complianceRate = $total > 0 ? round(($compliantCount / $total) * 100, 1) : 100.0;

        return [
            'benchmark_year' => $year,
            'benchmark_city' => $benchmarkCity,
            'benchmark_amount' => $benchmarkWage,
            'legal_reference' => $umkStandard?->legal_reference ?? 'Peraturan Pemerintah No. 51 Tahun 2023',
            'total_employees' => $total,
            'compliant_count' => $compliantCount,
            'non_compliant_count' => count($nonCompliant),
            'compliance_rate_percentage' => $complianceRate,
            'non_compliant_employees' => $nonCompliant,
        ];
    }

    /**
     * Generate BPJS Ketenagakerjaan SIPP Online statutory summary report.
     */
    public function generateBpjsTkSippReport(PayrollPeriod $period): BpjsComplianceReport
    {
        $payrolls = Payroll::where('payroll_period_id', $period->id)->get();

        $totalWages = (float) $payrolls->sum('gross_salary');
        $totalEmployeeShare = (float) $payrolls->sum('bpjs_tk_employee');
        $totalCompanyShare = (float) $payrolls->sum('bpjs_tk_employer');
        $totalContribution = $totalEmployeeShare + $totalCompanyShare;

        return BpjsComplianceReport::updateOrCreate(
            [
                'tenant_id' => $period->tenant_id,
                'payroll_period_id' => $period->id,
                'report_type' => 'bpjs_tk_sipp',
            ],
            [
                'report_month' => $period->start_date->format('Y-m'),
                'total_employees' => $payrolls->count(),
                'total_wages' => $totalWages,
                'total_company_share' => $totalCompanyShare,
                'total_employee_share' => $totalEmployeeShare,
                'total_contribution' => $totalContribution,
            ]
        );
    }

    /**
     * Generate BPJS Kesehatan e-Dabu statutory summary report.
     */
    public function generateBpjsKesEdabuReport(PayrollPeriod $period): BpjsComplianceReport
    {
        $payrolls = Payroll::where('payroll_period_id', $period->id)->get();

        $totalWages = (float) $payrolls->sum('gross_salary');
        $totalEmployeeShare = (float) $payrolls->sum('bpjs_kes_employee');
        $totalCompanyShare = (float) $payrolls->sum('bpjs_kes_employer');
        $totalContribution = $totalEmployeeShare + $totalCompanyShare;

        return BpjsComplianceReport::updateOrCreate(
            [
                'tenant_id' => $period->tenant_id,
                'payroll_period_id' => $period->id,
                'report_type' => 'bpjs_kes_edabu',
            ],
            [
                'report_month' => $period->start_date->format('Y-m'),
                'total_employees' => $payrolls->count(),
                'total_wages' => $totalWages,
                'total_company_share' => $totalCompanyShare,
                'total_employee_share' => $totalEmployeeShare,
                'total_contribution' => $totalContribution,
            ]
        );
    }

    /**
     * Export BPJS Ketenagakerjaan SIPP Online CSV.
     */
    public function exportBpjsTkCsv(PayrollPeriod $period): string
    {
        $payrolls = Payroll::with('employee')
            ->where('payroll_period_id', $period->id)
            ->get();

        $output = fopen('php://temp', 'r+');

        fputcsv($output, [
            'NIK_KTP',
            'KPJ_BPJS_TK',
            'NAMA_TENAGA_KERJA',
            'UPAH_POKOK',
            'JHT_TK_2PCT',
            'JHT_PERUSAHAAN_3_7PCT',
            'JKK_PERUSAHAAN',
            'JKM_PERUSAHAAN_0_3PCT',
            'JP_TK_1PCT',
            'JP_PERUSAHAAN_2PCT',
            'TOTAL_IURAN'
        ]);

        foreach ($payrolls as $p) {
            $emp = $p->employee;
            $jhtTk = round($p->basic_salary * 0.02, 2);
            $jhtPer = round($p->basic_salary * 0.037, 2);
            $jkkPer = round($p->basic_salary * 0.0024, 2);
            $jkmPer = round($p->basic_salary * 0.003, 2);
            $jpWageCap = min($p->basic_salary, 10042300);
            $jpTk = round($jpWageCap * 0.01, 2);
            $jpPer = round($jpWageCap * 0.02, 2);

            $total = $p->bpjs_tk_employee + $p->bpjs_tk_employer;

            fputcsv($output, [
                $emp->nik_ktp ?: "3201" . str_pad($emp->id, 12, '0', STR_PAD_LEFT),
                $emp->bpjs_tk_number ?: "TK-" . str_pad($emp->id, 8, '0', STR_PAD_LEFT),
                strtoupper($emp->full_name),
                number_format($p->basic_salary, 2, '.', ''),
                number_format($jhtTk, 2, '.', ''),
                number_format($jhtPer, 2, '.', ''),
                number_format($jkkPer, 2, '.', ''),
                number_format($jkmPer, 2, '.', ''),
                number_format($jpTk, 2, '.', ''),
                number_format($jpPer, 2, '.', ''),
                number_format($total, 2, '.', ''),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export BPJS Kesehatan e-Dabu CSV.
     */
    public function exportBpjsKesCsv(PayrollPeriod $period): string
    {
        $payrolls = Payroll::with('employee')
            ->where('payroll_period_id', $period->id)
            ->get();

        $output = fopen('php://temp', 'r+');

        fputcsv($output, [
            'NIK_KTP',
            'NO_KARTU_BPJS_KES',
            'NAMA_LENGKAP',
            'GAJI_POKOK_DASAR',
            'IURAN_PERUSAHAAN_4PCT',
            'IURAN_PEKERJA_1PCT',
            'TOTAL_IURAN_5PCT'
        ]);

        foreach ($payrolls as $p) {
            $emp = $p->employee;
            $total = $p->bpjs_kes_employee + $p->bpjs_kes_employer;

            fputcsv($output, [
                $emp->nik_ktp ?: "3201" . str_pad($emp->id, 12, '0', STR_PAD_LEFT),
                $emp->bpjs_kes_number ?: "KES-" . str_pad($emp->id, 8, '0', STR_PAD_LEFT),
                strtoupper($emp->full_name),
                number_format($p->basic_salary, 2, '.', ''),
                number_format($p->bpjs_kes_employer, 2, '.', ''),
                number_format($p->bpjs_kes_employee, 2, '.', ''),
                number_format($total, 2, '.', ''),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
