<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTermination;
use App\Services\Career\SeveranceCalculationService;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TerminationController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected SeveranceCalculationService $severanceService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');

        $query = EmployeeTermination::with(['employee.department', 'approvedBy'])
            ->where('tenant_id', $tenant->id)
            ->latest('termination_date');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $terminations = $query->paginate(15);
        $employees = Employee::with('department')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->get();

        $stats = [
            'total_terminations' => EmployeeTermination::where('tenant_id', $tenant->id)->count(),
            'total_severance_payout' => EmployeeTermination::where('tenant_id', $tenant->id)->sum('total_severance'),
            'paid_count' => EmployeeTermination::where('tenant_id', $tenant->id)->where('status', 'paid')->count(),
            'pending_payout' => EmployeeTermination::where('tenant_id', $tenant->id)->where('status', '!=', 'paid')->sum('total_severance'),
        ];

        return view('company.terminations.index', compact('terminations', 'employees', 'stats'));
    }

    public function calculate(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'termination_type' => 'required|in:resignation,efficiency,closing,sp3_violation,pension,medical,deceased,other',
            'termination_date' => 'required|date',
            'last_salary' => 'nullable|numeric|min:0',
            'remaining_leave_days' => 'nullable|integer|min:0',
            'separation_pay_amount' => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);

        $result = $this->severanceService->calculateSeverance(
            $employee,
            $validated['termination_type'],
            $validated['termination_date'],
            isset($validated['last_salary']) ? (float)$validated['last_salary'] : null,
            (int)($validated['remaining_leave_days'] ?? 0),
            (float)($validated['separation_pay_amount'] ?? 0)
        );

        return response()->json($result);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'termination_type' => 'required|in:resignation,efficiency,closing,sp3_violation,pension,medical,deceased,other',
            'termination_date' => 'required|date',
            'last_salary' => 'nullable|numeric|min:0',
            'remaining_leave_days' => 'nullable|integer|min:0',
            'separation_pay_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);

        $termination = $this->severanceService->recordTermination($employee, $validated, Auth::user());

        return back()->with('success', "Penyelesaian PHK/Terminasi ({$termination->letter_number}) atas nama {$employee->full_name} berhasil disimpan. Total kompensasi pesangon: Rp " . number_format($termination->total_severance, 0, ',', '.'));
    }

    public function markPaid(EmployeeTermination $termination): RedirectResponse
    {
        $this->severanceService->markAsPaid($termination);

        return back()->with('success', "Kompensasi pesangon {$termination->letter_number} telah ditandai lunas (Paid).");
    }
}
