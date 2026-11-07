<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\OvertimeOrder;
use App\Services\Overtime\SplComplianceService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SplOvertimeController extends Controller
{
    public function __construct(
        protected SplComplianceService $splService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $query = OvertimeOrder::with(['employee.department', 'managerApprover'])
            ->where('company_id', $company->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest('overtime_date')->paginate(12)->withQueryString();

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $complianceStats = [
            'pending_count' => OvertimeOrder::where('company_id', $company->id)->where('status', 'pending_approval')->count(),
            'approved_count' => OvertimeOrder::where('company_id', $company->id)->where('status', 'approved')->count(),
            'completed_count' => OvertimeOrder::where('company_id', $company->id)->where('status', 'completed_verified')->count(),
        ];

        return view('company.spl.index', compact('company', 'orders', 'employees', 'complianceStats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'overtime_date' => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'planned_duration_hours' => ['nullable', 'numeric', 'min:0.5'],
            'reason_task_description' => ['required', 'string'],
            'is_day_off' => ['nullable', 'boolean'],
        ]);

        $validated['is_day_off'] = $request->boolean('is_day_off');
        $employee = Employee::findOrFail($validated['employee_id']);

        try {
            $order = $this->splService->createOrder($company, $employee, $validated);
            return back()->with('success', "Surat Perintah Lembur (SPL) [{$order->order_number}] berhasil diterbitkan.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function approve(OvertimeOrder $order): RedirectResponse
    {
        $this->splService->approveOrder($order, request()->user());
        return back()->with('success', "SPL [{$order->order_number}] telah disetujui atasan.");
    }

    public function consent(OvertimeOrder $order): RedirectResponse
    {
        $this->splService->giveEmployeeConsent($order);
        return back()->with('success', "Persetujuan tertulis lembur pekerja (Pasal 26 ayat 3 PP 35/2021) berhasil dicatat.");
    }

    public function verify(Request $request, OvertimeOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'actual_duration_hours' => ['required', 'numeric', 'min:0'],
        ]);

        $this->splService->verifyActualOvertime($order, (float) $validated['actual_duration_hours']);
        return back()->with('success', "Realisasi jam lembur aktual [{$order->order_number}] berhasil diverifikasi ({$validated['actual_duration_hours']} jam).");
    }
}
