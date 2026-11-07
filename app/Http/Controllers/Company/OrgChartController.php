<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Services\Organization\OrgChartService;
use App\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrgChartController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected OrgChartService $orgChartService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $selectedDeptId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $departments = Department::where('company_id', $company->id)->orderBy('name')->get();

        $hierarchy = $this->orgChartService->buildHierarchy($company, $selectedDeptId);

        return view('company.orgchart.index', compact('company', 'departments', 'selectedDeptId', 'hierarchy'));
    }
}
