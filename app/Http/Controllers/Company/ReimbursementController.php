<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Reimbursement;
use App\Services\Reimbursement\ReimbursementService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReimbursementController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected ReimbursementService $reimbursementService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');
        $category = $request->query('category');

        $query = Reimbursement::with(['employee.department', 'employee.position', 'managerReviewer', 'financeReviewer'])
            ->where('tenant_id', $tenant->id)
            ->latest('expense_date');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($category)) {
            $query->where('category', $category);
        }

        $reimbursements = $query->paginate(15);
        $employees = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->get();

        $stats = [
            'pending_total' => Reimbursement::where('tenant_id', $tenant->id)->whereIn('status', ['pending', 'approved_manager'])->sum('amount'),
            'approved_finance_total' => Reimbursement::where('tenant_id', $tenant->id)->where('status', 'approved_finance')->sum('amount'),
            'paid_total' => Reimbursement::where('tenant_id', $tenant->id)->where('status', 'paid')->sum('amount'),
        ];

        return view('company.reimbursement.index', compact('reimbursements', 'employees', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'category' => 'required|in:medical,transport,meal,business_trip,operational,other',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1000',
            'expense_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);

        $this->reimbursementService->submitClaim(
            $employee,
            $validated['category'],
            $validated['title'],
            (float) $validated['amount'],
            $validated['expense_date'],
            $validated['description'] ?? null,
            $request->file('receipt')
        );

        return back()->with('success', 'Klaim reimbursement berhasil diajukan.');
    }

    public function approveManager(Reimbursement $reimbursement): RedirectResponse
    {
        $user = Auth::user();
        $this->reimbursementService->approveByManager($reimbursement, $user);

        return back()->with('success', 'Pengajuan reimbursement telah disetujui Manajer dan diteruskan ke Finance.');
    }

    public function approveFinance(Reimbursement $reimbursement): RedirectResponse
    {
        $user = Auth::user();
        $this->reimbursementService->approveByFinance($reimbursement, $user);

        return back()->with('success', 'Pengajuan reimbursement telah disetujui Finance dan siap dicairkan atau digabung ke payroll.');
    }

    public function reject(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->reimbursementService->reject($reimbursement, $user, $validated['reason']);

        return back()->with('success', 'Pengajuan reimbursement telah ditolak.');
    }

    public function pay(Reimbursement $reimbursement): RedirectResponse
    {
        $this->reimbursementService->markAsPaid($reimbursement);

        return back()->with('success', 'Reimbursement telah ditandai Lunas / Dibayarkan secara tunai/transfer langsung.');
    }
}

