<?php

namespace App\Services\Analytics;

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Reimbursement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HrAnalyticsService
{
    /**
     * Compute executive HR metrics and distributions for executive dashboard.
     */
    public function getExecutiveMetrics(int $tenantId): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        // 1. Headcount & Turnover
        $totalEmployees = Employee::where('tenant_id', $tenantId)->count();
        $activeEmployees = Employee::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $resignedEmployees = Employee::where('tenant_id', $tenantId)->whereIn('status', ['resigned', 'terminated'])->count();

        $turnoverRate = $activeEmployees > 0 
            ? round(($resignedEmployees / ($activeEmployees + $resignedEmployees)) * 100, 2) 
            : 0.0;

        // 2. Breakdown by Employment Status (PKWT vs PKWTT)
        $statusCounts = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select('employment_status', DB::raw('count(*) as total'))
            ->groupBy('employment_status')
            ->pluck('total', 'employment_status')
            ->toArray();

        // 3. Breakdown by Gender
        $genderCounts = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        // 4. Breakdown by Department
        $departments = Department::where('tenant_id', $tenantId)
            ->withCount(['employees' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get();

        $deptBreakdown = [];
        foreach ($departments as $d) {
            $deptBreakdown[$d->name] = $d->employees_count;
        }

        // 5. Attendance & Punctuality this month
        $totalRecords = AttendanceRecord::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->count();

        $lateRecords = AttendanceRecord::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('late_minutes', '>', 0)
            ->count();

        $ontimeRate = $totalRecords > 0 
            ? round((($totalRecords - $lateRecords) / $totalRecords) * 100, 1) 
            : 100.0;

        // 6. Payroll & Labor Cost (Latest Period)
        $latestPeriod = PayrollPeriod::where('tenant_id', $tenantId)->latest('start_date')->first();
        $totalGrossPayroll = 0.0;
        $totalThp = 0.0;
        $totalTaxPph21 = 0.0;
        $totalBpjsEmployer = 0.0;

        if ($latestPeriod) {
            $payrolls = Payroll::where('tenant_id', $tenantId)
                ->where('payroll_period_id', $latestPeriod->id)
                ->get();

            $totalGrossPayroll = (float) $payrolls->sum('gross_salary');
            $totalThp = (float) $payrolls->sum('take_home_pay');
            $totalTaxPph21 = (float) $payrolls->sum('pph21_amount');
            $totalBpjsEmployer = (float) $payrolls->sum('bpjs_company_total');
        }

        // 7. Reimbursements this month
        $reimbursementTotal = (float) Reimbursement::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return [
            'total_headcount' => $totalEmployees,
            'active_headcount' => $activeEmployees,
            'resigned_headcount' => $resignedEmployees,
            'turnover_rate' => $turnoverRate,
            'status_breakdown' => $statusCounts,
            'gender_breakdown' => $genderCounts,
            'department_breakdown' => $deptBreakdown,
            'total_attendance_logs' => $totalRecords,
            'ontime_rate' => $ontimeRate,
            'latest_payroll_gross' => $totalGrossPayroll,
            'latest_payroll_thp' => $totalThp,
            'latest_payroll_tax' => $totalTaxPph21,
            'latest_payroll_bpjs_employer' => $totalBpjsEmployer,
            'reimbursement_paid_month' => $reimbursementTotal,
            'latest_period_name' => $latestPeriod?->name ?? 'Belum ada periode',
        ];
    }

    /**
     * Generate CSV export string for Headcount report.
     */
    public function exportHeadcountCsv(int $tenantId): string
    {
        $employees = Employee::where('tenant_id', $tenantId)
            ->with(['department', 'position'])
            ->orderBy('employee_number')
            ->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['NIK', 'Nama Lengkap', 'Email', 'No Telepon', 'Departemen', 'Jabatan', 'Status Karyawan', 'Tanggal Masuk', 'Gaji Pokok', 'Status']);

        foreach ($employees as $emp) {
            fputcsv($output, [
                $emp->employee_number,
                $emp->full_name,
                $emp->email,
                $emp->phone,
                $emp->department?->name ?? '-',
                $emp->position?->title ?? '-',
                strtoupper($emp->employment_status),
                $emp->join_date ? $emp->join_date->format('Y-m-d') : '-',
                number_format((float) $emp->basic_salary, 2, '.', ''),
                $emp->status,
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate CSV export string for Attendance report.
     */
    public function exportAttendanceCsv(int $tenantId, ?string $month = null): string
    {
        $query = AttendanceRecord::where('tenant_id', $tenantId)->with('employee');

        if ($month) {
            $date = Carbon::parse($month);
            $query->whereBetween('date', [$date->startOfMonth()->toDateString(), $date->endOfMonth()->toDateString()]);
        } else {
            $query->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
        }

        $records = $query->orderBy('date', 'desc')->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['NIK', 'Nama Karyawan', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Keterlambatan (Menit)', 'Status', 'Catatan']);

        foreach ($records as $r) {
            fputcsv($output, [
                $r->employee?->employee_number ?? '-',
                $r->employee?->full_name ?? '-',
                $r->date->format('Y-m-d'),
                $r->clock_in ?? '-',
                $r->clock_out ?? '-',
                $r->late_minutes ?? 0,
                $r->status,
                $r->notes ?? '-',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate CSV export string for Payroll summary.
     */
    public function exportPayrollSummaryCsv(int $tenantId, ?int $periodId = null): string
    {
        $query = Payroll::where('tenant_id', $tenantId)->with(['employee', 'payrollPeriod']);

        if ($periodId) {
            $query->where('payroll_period_id', $periodId);
        }

        $payrolls = $query->latest()->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Periode Gaji', 'NIK', 'Nama Karyawan', 'Gaji Pokok', 'Tunjangan Tetap', 'Upah Lembur', 'Gaji Bruto', 'Potongan BPJS Karyawan', 'Potongan PPh 21 TER', 'Potongan Lain', 'Take Home Pay (THP)']);

        foreach ($payrolls as $p) {
            fputcsv($output, [
                $p->payrollPeriod?->name ?? '-',
                $p->employee?->employee_number ?? '-',
                $p->employee?->full_name ?? '-',
                number_format((float) $p->basic_salary, 2, '.', ''),
                number_format((float) $p->fixed_allowance_total, 2, '.', ''),
                number_format((float) $p->overtime_amount, 2, '.', ''),
                number_format((float) $p->gross_salary, 2, '.', ''),
                number_format((float) $p->bpjs_employee_total, 2, '.', ''),
                number_format((float) $p->pph21_amount, 2, '.', ''),
                number_format((float) $p->other_deductions_total, 2, '.', ''),
                number_format((float) $p->take_home_pay, 2, '.', ''),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}

