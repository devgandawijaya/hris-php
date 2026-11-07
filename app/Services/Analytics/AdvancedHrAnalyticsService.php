<?php

namespace App\Services\Analytics;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use Carbon\Carbon;

class AdvancedHrAnalyticsService
{
    /**
     * Calculate Employee Turnover & Attrition Rate based on standard HR equation.
     */
    public function getTurnoverMetrics(Company $company, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfDay();

        // Active at start of year: joined before or on startOfYear and (not terminated or terminated after startOfYear)
        $headcountStart = Employee::where('company_id', $company->id)
            ->where('join_date', '<=', $startOfYear->toDateString())
            ->where(function ($q) use ($startOfYear) {
                $q->whereNull('termination_date')
                  ->orWhere('termination_date', '>', $startOfYear->toDateString());
            })
            ->count();

        // Active at end of year (or now if current year)
        $evalEnd = now()->year === $year ? now()->endOfDay() : $endOfYear;
        $headcountEnd = Employee::where('company_id', $company->id)
            ->where('join_date', '<=', $evalEnd->toDateString())
            ->where(function ($q) use ($evalEnd) {
                $q->whereNull('termination_date')
                  ->orWhere('termination_date', '>', $evalEnd->toDateString());
            })
            ->count();

        // Separations during year
        $separationsQuery = Employee::where('company_id', $company->id)
            ->whereNotNull('termination_date')
            ->whereBetween('termination_date', [$startOfYear->toDateString(), $evalEnd->toDateString()]);

        $totalSeparations = $separationsQuery->count();

        $byReason = [
            'resigned' => (clone $separationsQuery)->where('employment_status', 'resigned')->count(),
            'terminated' => (clone $separationsQuery)->where('employment_status', 'terminated')->count(),
            'end_of_contract' => (clone $separationsQuery)->where('employment_status', 'contract_expired')->count(),
        ];

        $averageHeadcount = ($headcountStart + $headcountEnd) / 2;
        $turnoverRate = $averageHeadcount > 0 ? round(($totalSeparations / $averageHeadcount) * 100, 2) : 0.00;
        $retentionRate = max(0.00, round(100.00 - $turnoverRate, 2));

        return [
            'year' => $year,
            'headcount_start' => $headcountStart,
            'headcount_end' => $headcountEnd,
            'average_headcount' => round($averageHeadcount, 1),
            'total_separations' => $totalSeparations,
            'turnover_rate' => $turnoverRate,
            'retention_rate' => $retentionRate,
            'by_reason' => $byReason,
        ];
    }

    /**
     * Compute Bradford Factor Index for Absenteeism (B = S^2 x D).
     */
    public function getBradfordFactorMetrics(Company $company, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $startOfYear = Carbon::createFromDate($year, 1, 1)->toDateString();
        $endOfYear = Carbon::createFromDate($year, 12, 31)->toDateString();

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->get();

        $employeeScores = [];
        $categories = [
            'low' => 0,      // < 50 (Normal / Minimal risk)
            'medium' => 0,   // 50 - 199 (Noticeable pattern)
            'high' => 0,     // 200 - 499 (Disruptive / Action required)
            'critical' => 0, // >= 500 (Disciplinary review)
        ];

        foreach ($employees as $emp) {
            // Count absences marked as absent or unexcused
            $absenceDates = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$startOfYear, $endOfYear])
                ->where('status', 'absent')
                ->orderBy('date')
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();

            $totalDays = count($absenceDates);
            $spells = 0;

            if ($totalDays > 0) {
                $spells = 1;
                for ($i = 1; $i < $totalDays; $i++) {
                    $prev = Carbon::parse($absenceDates[$i - 1]);
                    $curr = Carbon::parse($absenceDates[$i]);
                    // If not contiguous day (excluding weekend or next calendar day)
                    if ($prev->diffInDays($curr) > 1) {
                        $spells++;
                    }
                }
            }

            // Bradford formula: B = S^2 * D
            $bradfordScore = ($spells * $spells) * $totalDays;

            if ($bradfordScore >= 500) {
                $riskLevel = 'critical';
                $categories['critical']++;
            } elseif ($bradfordScore >= 200) {
                $riskLevel = 'high';
                $categories['high']++;
            } elseif ($bradfordScore >= 50) {
                $riskLevel = 'medium';
                $categories['medium']++;
            } else {
                $riskLevel = 'low';
                $categories['low']++;
            }

            if ($bradfordScore > 0) {
                $employeeScores[] = [
                    'employee_id' => $emp->id,
                    'name' => $emp->full_name,
                    'department' => $emp->department?->name ?? 'General',
                    'spells' => $spells,
                    'days' => $totalDays,
                    'bradford_score' => $bradfordScore,
                    'risk_level' => $riskLevel,
                ];
            }
        }

        // Sort by highest score first
        usort($employeeScores, fn($a, $b) => $b['bradford_score'] <=> $a['bradford_score']);

        return [
            'year' => $year,
            'summary_counts' => $categories,
            'total_evaluated' => $employees->count(),
            'flagged_employees' => array_slice($employeeScores, 0, 15),
        ];
    }

    /**
     * Compute Demographic Pyramids (Gender, Age, Tenure, Education, Contract Type).
     */
    public function getDemographics(Company $company): array
    {
        $employees = Employee::with(['educationHistories', 'department', 'position'])
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->get();

        $total = $employees->count();

        // 1. Gender breakdown
        $male = $employees->where('gender', 'male')->count();
        $female = $employees->where('gender', 'female')->count();

        // 2. Age distribution
        $ageBrackets = ['under_25' => 0, 'age_25_34' => 0, 'age_35_44' => 0, 'age_45_plus' => 0];
        foreach ($employees as $e) {
            $dob = $e->date_of_birth ? Carbon::parse($e->date_of_birth) : null;
            $age = $dob ? $dob->age : 30; // fallback median
            if ($age < 25) $ageBrackets['under_25']++;
            elseif ($age <= 34) $ageBrackets['age_25_34']++;
            elseif ($age <= 44) $ageBrackets['age_35_44']++;
            else $ageBrackets['age_45_plus']++;
        }

        // 3. Tenure distribution (Length of Service)
        $tenureBrackets = ['under_1yr' => 0, 'yr_1_3' => 0, 'yr_3_5' => 0, 'over_5yr' => 0];
        foreach ($employees as $e) {
            $join = $e->join_date ? Carbon::parse($e->join_date) : now();
            $years = $join->diffInYears(now());
            if ($years < 1) $tenureBrackets['under_1yr']++;
            elseif ($years <= 3) $tenureBrackets['yr_1_3']++;
            elseif ($years <= 5) $tenureBrackets['yr_3_5']++;
            else $tenureBrackets['over_5yr']++;
        }

        // 4. Employment Type distribution
        $contractTypes = [
            'pkwtt' => $employees->where('employment_status', 'pkwtt')->count(),
            'pkwt' => $employees->where('employment_status', 'pkwt')->count(),
            'internship' => $employees->where('employment_status', 'internship')->count(),
            'freelance' => $employees->where('employment_status', 'freelance')->count(),
        ];

        return [
            'total_active_headcount' => $total,
            'gender' => [
                'male' => $male,
                'female' => $female,
                'male_pct' => $total > 0 ? round(($male / $total) * 100, 1) : 0,
                'female_pct' => $total > 0 ? round(($female / $total) * 100, 1) : 0,
            ],
            'age_brackets' => $ageBrackets,
            'tenure_brackets' => $tenureBrackets,
            'contract_types' => $contractTypes,
        ];
    }

    /**
     * Compute Annual Labor Cost Ratio.
     */
    public function getLaborCostMetrics(Company $company, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');

        $payrolls = Payroll::where('company_id', $company->id)
            ->whereYear('period_end', $year)
            ->where('status', 'paid')
            ->get();

        $totalGross = (float) $payrolls->sum('gross_salary');
        $totalNet = (float) $payrolls->sum('net_salary');
        $totalOvertime = (float) $payrolls->sum('overtime_pay');
        $totalBpjsEmployer = (float) $payrolls->sum('bpjs_company_total');
        $totalTaxPPh21 = (float) $payrolls->sum('pph21_amount');

        $totalCompensationCost = $totalGross + $totalBpjsEmployer;

        return [
            'year' => $year,
            'payroll_runs_count' => $payrolls->count(),
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'total_overtime' => $totalOvertime,
            'total_bpjs_employer' => $totalBpjsEmployer,
            'total_tax_pph21' => $totalTaxPPh21,
            'total_compensation_cost' => $totalCompensationCost,
        ];
    }

    /**
     * Stream Export comprehensive executive report as CSV.
     */
    public function exportAdvancedMetricsCsv(Company $company, ?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $turnover = $this->getTurnoverMetrics($company, $year);
        $demographics = $this->getDemographics($company);
        $labor = $this->getLaborCostMetrics($company, $year);

        $output = fopen('php://temp', 'r+');

        // CSV Header
        fputcsv($output, ['=== LAPORAN EKSEKUTIF ANALITIK SDM & KINERJA PERUSAHAAN ===']);
        fputcsv($output, ['Perusahaan', $company->name]);
        fputcsv($output, ['Tahun Analisis', $year]);
        fputcsv($output, ['Tanggal Terbit', now()->toFormattedDateString()]);
        fputcsv($output, []);

        // 1. TURNOVER & ATTRITION
        fputcsv($output, ['1. INDIKATOR TURNOVER & RETENSI KARYAWAN']);
        fputcsv($output, ['Metrik', 'Nilai']);
        fputcsv($output, ['Headcount Awal Tahun', $turnover['headcount_start']]);
        fputcsv($output, ['Headcount Akhir Tahun', $turnover['headcount_end']]);
        fputcsv($output, ['Rata-Rata Headcount', $turnover['average_headcount']]);
        fputcsv($output, ['Total Karyawan Keluar (Separations)', $turnover['total_separations']]);
        fputcsv($output, ['Tingkat Turnover Tahunan (Turnover Rate %)', $turnover['turnover_rate'] . '%']);
        fputcsv($output, ['Tingkat Retensi Karyawan (Retention Rate %)', $turnover['retention_rate'] . '%']);
        fputcsv($output, []);

        // 2. DEMOGRAFIS
        fputcsv($output, ['2. DISTRIBUSI DEMOGRAFIS KARYAWAN']);
        fputcsv($output, ['Kategori', 'Jumlah', 'Persentase']);
        fputcsv($output, ['Laki-Laki', $demographics['gender']['male'], $demographics['gender']['male_pct'] . '%']);
        fputcsv($output, ['Perempuan', $demographics['gender']['female'], $demographics['gender']['female_pct'] . '%']);
        fputcsv($output, ['Usia < 25 Tahun', $demographics['age_brackets']['under_25'], '']);
        fputcsv($output, ['Usia 25-34 Tahun', $demographics['age_brackets']['age_25_34'], '']);
        fputcsv($output, ['Usia 35-44 Tahun', $demographics['age_brackets']['age_35_44'], '']);
        fputcsv($output, ['Usia 45+ Tahun', $demographics['age_brackets']['age_45_plus'], '']);
        fputcsv($output, []);

        // 3. LABOR COST
        fputcsv($output, ['3. BIAYA TENAGA KERJA (LABOR COST COMPLIANCE)']);
        fputcsv($output, ['Komponen Biaya', 'Total (IDR)']);
        fputcsv($output, ['Total Upah Bruto (Gross)', number_format($labor['total_gross'], 2)]);
        fputcsv($output, ['Total Iuran BPJS Pemberi Kerja', number_format($labor['total_bpjs_employer'], 2)]);
        fputcsv($output, ['Total Upah Lembur (Overtime)', number_format($labor['total_overtime'], 2)]);
        fputcsv($output, ['Total Pajak PPh 21 Ditampung', number_format($labor['total_tax_pph21'], 2)]);
        fputcsv($output, ['Total Biaya Kompensasi Korporat', number_format($labor['total_compensation_cost'], 2)]);

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
