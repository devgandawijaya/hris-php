<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollApiController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $periodId = $request->query('period_id');
        $period = $periodId 
            ? PayrollPeriod::where('tenant_id', $tenant->id)->find($periodId)
            : PayrollPeriod::where('tenant_id', $tenant->id)->latest('start_date')->first();

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'No payroll period found.',
            ], 404);
        }

        $payrolls = Payroll::where('tenant_id', $tenant->id)
            ->where('payroll_period_id', $period->id)
            ->with(['employee.department'])
            ->get();

        $grossTotal = (float) $payrolls->sum('gross_salary');
        $thpTotal = (float) $payrolls->sum('take_home_pay');
        $pphTotal = (float) $payrolls->sum('pph21_amount');
        $bpjsCompanyTotal = (float) $payrolls->sum('bpjs_company_total');
        $bpjsEmployeeTotal = (float) $payrolls->sum('bpjs_employee_total');

        // Department breakdown for ERP General Ledger allocations
        $deptAllocation = [];
        foreach ($payrolls as $p) {
            $deptName = $p->employee?->department?->name ?? 'Unassigned';
            if (!isset($deptAllocation[$deptName])) {
                $deptAllocation[$deptName] = [
                    'department' => $deptName,
                    'headcount' => 0,
                    'gross_salary' => 0.0,
                    'take_home_pay' => 0.0,
                    'pph21_tax' => 0.0,
                    'bpjs_company' => 0.0,
                ];
            }
            $deptAllocation[$deptName]['headcount']++;
            $deptAllocation[$deptName]['gross_salary'] += (float) $p->gross_salary;
            $deptAllocation[$deptName]['take_home_pay'] += (float) $p->take_home_pay;
            $deptAllocation[$deptName]['pph21_tax'] += (float) $p->pph21_amount;
            $deptAllocation[$deptName]['bpjs_company'] += (float) $p->bpjs_company_total;
        }

        return response()->json([
            'success' => true,
            'period' => [
                'id' => $period->id,
                'name' => $period->name,
                'start_date' => $period->start_date?->format('Y-m-d'),
                'end_date' => $period->end_date?->format('Y-m-d'),
                'payment_date' => $period->payment_date?->format('Y-m-d'),
                'status' => $period->status,
            ],
            'totals' => [
                'total_employees' => $payrolls->count(),
                'gross_salary' => $grossTotal,
                'take_home_pay' => $thpTotal,
                'pph21_ter_tax' => $pphTotal,
                'bpjs_company_cost' => $bpjsCompanyTotal,
                'bpjs_employee_deduction' => $bpjsEmployeeTotal,
            ],
            'department_allocations' => array_values($deptAllocation),
        ]);
    }
}
