<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\BusinessTripAdvance;
use App\Models\BusinessTripOrder;
use App\Models\Company;
use App\Models\Employee;
use App\Services\Travel\BusinessTripService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessTripController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected BusinessTripService $travelService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $query = BusinessTripOrder::where('company_id', $company->id)
            ->with(['employee.department', 'advances', 'expenses', 'settlement'])
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(15);

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $stats = [
            'total' => BusinessTripOrder::where('company_id', $company->id)->count(),
            'pending' => BusinessTripOrder::where('company_id', $company->id)->where('status', 'submitted')->count(),
            'ongoing' => BusinessTripOrder::where('company_id', $company->id)->where('status', 'ongoing')->count(),
            'completed' => BusinessTripOrder::where('company_id', $company->id)->where('status', 'completed')->count(),
        ];

        return view('company.sppd.index', compact('orders', 'employees', 'stats'));
    }

    public function show(BusinessTripOrder $order): View
    {
        $order->load(['employee.department', 'employee.position', 'employee.jobGrade', 'approver', 'advances.disburser', 'expenses', 'settlement.auditor']);
        return view('company.sppd.show', compact('order'));
    }

    public function print(BusinessTripOrder $order): View
    {
        $order->load(['employee.department', 'employee.position', 'approver', 'advances', 'expenses', 'settlement']);
        return view('company.sppd.print', compact('order'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'purpose' => ['required', 'string', 'max:255'],
            'origin_city' => ['required', 'string', 'max:100'],
            'destination_city' => ['required', 'string', 'max:100'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
            'transport_type' => ['required', 'string'],
            'lodging_needed' => ['boolean'],
            'per_diem_daily_rate' => ['nullable', 'numeric', 'min:0'],
            'advance_requested' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $order = $this->travelService->createTripOrder($employee, $validated);

        return redirect()->route('company.business-trips.show', $order)
            ->with('success', "Surat Tugas SPPD {$order->sppd_number} berhasil diterbitkan.");
    }

    public function approve(Request $request, BusinessTripOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'approved' => ['required', 'boolean'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->travelService->processApproval(
            $order,
            $request->user(),
            (bool) $validated['approved'],
            $validated['rejection_reason'] ?? null
        );

        $msg = $validated['approved'] ? 'SPPD berhasil disetujui.' : 'SPPD telah ditolak.';
        return redirect()->back()->with('success', $msg);
    }

    public function disburseAdvance(Request $request, BusinessTripAdvance $advance): RedirectResponse
    {
        $validated = $request->validate([
            'transfer_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $this->travelService->disburseAdvance(
            $advance,
            $request->user(),
            $validated['transfer_reference'] ?? null
        );

        return redirect()->back()->with('success', 'Uang Muka Perjalanan Dinas berhasil dicairkan.');
    }

    public function addExpense(Request $request, BusinessTripOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $this->travelService->addExpense($order, $validated);

        return redirect()->back()->with('success', 'Pengeluaran perjalanan dinas berhasil dicatat.');
    }

    public function settle(Request $request, BusinessTripOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->travelService->finalizeSettlement($order, $request->user(), $validated['notes'] ?? null);

        return redirect()->back()->with('success', 'Rekonsiliasi dan settlement SPPD berhasil diselesaikan.');
    }
}
