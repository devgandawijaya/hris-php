<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\JobGrade;
use App\Services\Compensation\WageScaleService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WageScaleController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected WageScaleService $wageScaleService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $grades = JobGrade::where('company_id', $company->id)
            ->orderBy('level_order', 'asc')
            ->get();

        $audit = $this->wageScaleService->performWageScaleAudit($company);

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->with(['department', 'position', 'jobGrade'])
            ->orderBy('first_name', 'asc')
            ->get();

        return view('company.wage_scale.index', compact('company', 'grades', 'audit', 'employees'));
    }

    public function storeGrade(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $validated = $request->validate([
            'id' => ['nullable', 'exists:job_grades,id'],
            'grade_code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:100'],
            'level_order' => ['required', 'integer', 'min:1', 'max:20'],
            'min_salary' => ['required', 'numeric', 'min:0'],
            'mid_salary' => ['nullable', 'numeric', 'min:0'],
            'max_salary' => ['required', 'numeric', 'gte:min_salary'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $existing = !empty($validated['id']) ? JobGrade::find($validated['id']) : null;
        $this->wageScaleService->upsertJobGrade($company, $validated, $existing);

        return redirect()->route('company.wage-scales.index')
            ->with('success', 'Golongan & Skala Upah Permenaker No. 1/2017 berhasil disimpan.');
    }

    public function assignEmployee(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'job_grade_id' => ['nullable', 'exists:job_grades,id'],
        ]);

        $employee->job_grade_id = $validated['job_grade_id'];
        $employee->save();

        return redirect()->back()
            ->with('success', "Golongan upah karyawan {$employee->full_name} berhasil diperbarui.");
    }

    public function exportAudit(Request $request): StreamedResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();
        $audit = $this->wageScaleService->performWageScaleAudit($company);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_struktur_skala_upah_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($audit) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID Karyawan',
                'Nama Karyawan',
                'Departemen',
                'Jabatan',
                'Golongan',
                'Gaji Pokok (Rp)',
                'Min Band (Rp)',
                'Mid Band (Rp)',
                'Max Band (Rp)',
                'Compa-Ratio (%)',
                'Status Posisi Skala',
            ]);

            foreach ($audit['audited_employees'] as $row) {
                $emp = $row['employee'];
                $grade = $row['grade'];

                fputcsv($handle, [
                    $emp->employee_number,
                    $emp->full_name,
                    $emp->department ? $emp->department->name : '-',
                    $emp->position ? $emp->position->title : '-',
                    $grade ? $grade->grade_code . ' (' . $grade->name . ')' : 'Belum Ditentukan',
                    number_format((float) $emp->basic_salary, 2, '.', ''),
                    $grade ? number_format((float) $grade->min_salary, 2, '.', '') : '-',
                    $grade ? number_format((float) $grade->mid_salary, 2, '.', '') : '-',
                    $grade ? number_format((float) $grade->max_salary, 2, '.', '') : '-',
                    $row['compa_ratio'] !== null ? $row['compa_ratio'] . '%' : '-',
                    $row['band_label'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
