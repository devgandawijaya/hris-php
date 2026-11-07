<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ThrDisbursement;
use App\Services\Payroll\ThrCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThrDisbursementController extends Controller
{
    public function __construct(
        protected ThrCalculationService $thrService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $selectedYear = (int) $request->input('year', date('Y'));
        $holidayType = $request->input('holiday_type', 'idul_fitri');

        $disbursements = ThrDisbursement::with(['employee.department', 'employee.position'])
            ->where('company_id', $company->id)
            ->where('tax_year', $selectedYear)
            ->when($holidayType !== 'all', function ($q) use ($holidayType) {
                $q->where('religious_holiday_type', $holidayType);
            })
            ->latest()
            ->paginate(15);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        $stats = [
            'total_recipients' => $disbursements->total(),
            'total_gross_thr' => ThrDisbursement::where('company_id', $company->id)->where('tax_year', $selectedYear)->sum('calculated_thr_amount'),
            'total_ter_tax' => ThrDisbursement::where('company_id', $company->id)->where('tax_year', $selectedYear)->sum('ter_tax_deduction'),
            'total_late_penalty' => ThrDisbursement::where('company_id', $company->id)->where('tax_year', $selectedYear)->sum('late_penalty_interest'),
            'total_net_paid' => ThrDisbursement::where('company_id', $company->id)->where('tax_year', $selectedYear)->sum('net_thr_paid'),
        ];

        return view('company.thr.index', compact(
            'company',
            'disbursements',
            'employees',
            'selectedYear',
            'holidayType',
            'stats'
        ));
    }

    public function calculate(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'tax_year' => 'required|integer|min:2020|max:2050',
            'religious_holiday_type' => 'required|string|in:idul_fitri,natal,nyepi,waisak,imlek',
            'holiday_date' => 'required|date',
            'actual_payment_date' => 'required|date',
            'employee_scope' => 'required|string|in:all,individual',
            'employee_id' => 'nullable|required_if:employee_scope,individual|exists:employees,id',
        ]);

        if ($validated['employee_scope'] === 'individual') {
            $employees = Employee::where('id', $validated['employee_id'])->get();
        } else {
            $employees = Employee::where('company_id', $company->id)->where('status', 'active')->get();
        }

        $processedCount = 0;
        $ineligibleCount = 0;

        foreach ($employees as $emp) {
            try {
                $this->thrService->recordThrDisbursement(
                    $emp,
                    (int) $validated['tax_year'],
                    $validated['religious_holiday_type'],
                    $validated['holiday_date'],
                    $validated['actual_payment_date'],
                    'calculated'
                );
                $processedCount++;
            } catch (\InvalidArgumentException $e) {
                $ineligibleCount++;
            }
        }

        return redirect()->route('thr-disbursements.index', [
            'year' => $validated['tax_year'],
            'holiday_type' => $validated['religious_holiday_type'],
        ])->with('success', "Kalkulasi THR Statutori berhasil diproses untuk {$processedCount} karyawan ({$ineligibleCount} tidak memenuhi syarat masa kerja < 1 bulan).");
    }

    public function markPaid(ThrDisbursement $disbursement): RedirectResponse
    {
        $disbursement->update(['status' => 'paid']);
        return back()->with('success', "Status pembayaran THR {$disbursement->employee->full_name} berhasil ditandai Lunas.");
    }

    public function printSlip(ThrDisbursement $disbursement): View
    {
        $disbursement->load(['employee.department', 'employee.position', 'company']);
        return view('company.thr.print_slip', compact('disbursement'));
    }
}
