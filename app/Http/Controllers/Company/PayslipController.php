<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\Exceptions\HttpResponseException;

class PayslipController extends Controller
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function show(Payroll $payroll): View
    {
        $payroll->load(['payrollPeriod', 'employee.department', 'employee.position', 'employee.company']);
        $company = $payroll->employee->company;
        $breakdown = $payroll->breakdown ?? [];

        return view('company.payroll.payslip', compact('payroll', 'company', 'breakdown'));
    }

    public function employeePayslip(Payroll $payroll): View
    {
        $user = Auth::user();
        if ($payroll->employee->user_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda hanya dapat melihat slip gaji milik sendiri.');
        }

        $payroll->load(['payrollPeriod', 'employee.department', 'employee.position', 'employee.company']);
        $company = $payroll->employee->company;
        $breakdown = $payroll->breakdown ?? [];

        return view('company.payroll.payslip', compact('payroll', 'company', 'breakdown'));
    }
}

