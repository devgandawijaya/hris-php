<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeCooperative;
use App\Models\CooperativeTransaction;
use App\Services\Financial\EmployeeCooperativeService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class EmployeeCooperativeController extends Controller
{
    public function __construct(
        protected EmployeeCooperativeService $cooperativeService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $members = EmployeeCooperative::with(['employee.department'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('join_date')
            ->paginate(10);

        $recentTransactions = CooperativeTransaction::with(['member.employee'])
            ->where('tenant_id', $tenantId)
            ->latest('transaction_date')
            ->take(15)
            ->get();

        $totalSavings = EmployeeCooperative::where('tenant_id', $tenantId)->where('company_id', $company->id)->sum('total_accumulated_savings');
        $totalLoans = EmployeeCooperative::where('tenant_id', $tenantId)->where('company_id', $company->id)->sum('active_loan_balance');

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        return view('company.cooperative.index', compact('company', 'members', 'recentTransactions', 'totalSavings', 'totalLoans', 'employees'));
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'join_date' => 'required|date',
            'principal_savings_paid' => 'required|numeric|min:0',
            'mandatory_savings' => 'required|numeric|min:0',
            'voluntary_savings' => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        try {
            $member = $this->cooperativeService->registerMember($employee, $validated);

            return back()->with('success', "Anggota Koperasi ({$member->member_number}) untuk {$employee->full_name} berhasil didaftarkan.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'employee_cooperative_id' => 'required|exists:employee_cooperatives,id',
            'transaction_type' => 'required|in:loan_disbursement,loan_repayment,welfare_aid_disbursement',
            'amount' => 'required|numeric|min:1',
            'notes' => 'required|string|max:255',
        ]);

        $member = EmployeeCooperative::where('tenant_id', $tenantId)->findOrFail($validated['employee_cooperative_id']);

        try {
            if ($validated['transaction_type'] === 'loan_disbursement') {
                $this->cooperativeService->disburseLoan($member, (float) $validated['amount'], $validated['notes']);
                $msg = "Pinjaman koperasi sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil dicairkan.";
            } elseif ($validated['transaction_type'] === 'loan_repayment') {
                $this->cooperativeService->repayLoan($member, (float) $validated['amount']);
                $msg = "Pembayaran angsuran pinjaman sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil dicatat.";
            } else {
                $this->cooperativeService->disburseWelfareAid($member, (float) $validated['amount'], $validated['notes']);
                $msg = "Santunan dana sosial sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil disalurkan.";
            }

            return back()->with('success', $msg);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function runPayrollDeduction(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $activeMembers = EmployeeCooperative::where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($activeMembers as $m) {
            $this->cooperativeService->recordMonthlyPayrollDeduction($m);
            $count++;
        }

        return back()->with('success', "Pemotongan iuran simpanan koperasi berhasil diproses untuk {$count} anggota aktif.");
    }
}
