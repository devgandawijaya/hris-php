<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\LoanInstallment;
use App\Services\Finance\EmployeeLoanService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected EmployeeLoanService $loanService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');

        $query = EmployeeLoan::with(['employee.department', 'approvedBy', 'installments'])
            ->where('tenant_id', $tenant->id)
            ->latest();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $loans = $query->paginate(15);
        $employees = Employee::with('department')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->get();

        $stats = [
            'total_loans' => EmployeeLoan::where('tenant_id', $tenant->id)->count(),
            'total_disbursed' => EmployeeLoan::where('tenant_id', $tenant->id)->whereIn('status', ['active', 'completed'])->sum('amount'),
            'active_loans' => EmployeeLoan::where('tenant_id', $tenant->id)->where('status', 'active')->count(),
            'outstanding_balance' => EmployeeLoan::where('tenant_id', $tenant->id)->where('status', 'active')->sum('remaining_amount'),
            'pending_approval' => EmployeeLoan::where('tenant_id', $tenant->id)->where('status', 'pending')->count(),
        ];

        return view('company.loans.index', compact('loans', 'employees', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'loan_type' => 'required|in:emergency,salary_advance,education,housing,other',
            'amount' => 'required|numeric|min:100000',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1|max:36',
            'reason' => 'required|string|max:1000',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);

        $loan = $this->loanService->applyLoan($employee, $validated);

        return back()->with('success', "Permohonan pinjaman ({$loan->loan_number}) untuk {$employee->full_name} berhasil dibuat dan menunggu persetujuan.");
    }

    public function approve(EmployeeLoan $loan): RedirectResponse
    {
        $this->loanService->approveAndDisburse($loan, Auth::user());

        return back()->with('success', "Pinjaman {$loan->loan_number} berhasil disetujui, dana telah dicairkan, dan jadwal angsuran telah dibentuk.");
    }

    public function payInstallment(LoanInstallment $installment): RedirectResponse
    {
        $this->loanService->recordRepayment($installment, false);

        return back()->with('success', "Pembayaran angsuran ke-{$installment->installment_number} sebesar Rp " . number_format($installment->amount, 0, ',', '.') . " berhasil dicatat.");
    }
}
