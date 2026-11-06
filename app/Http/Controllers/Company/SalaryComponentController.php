<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSalaryComponent;
use App\Models\SalaryComponent;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryComponentController extends Controller
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();

        $components = SalaryComponent::where('tenant_id', $tenant->id)
            ->withCount('employeeComponents')
            ->get();

        $employees = Employee::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with(['department', 'position', 'salaryComponents.salaryComponent'])
            ->get();

        return view('company.payroll.components', compact('components', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:allowance,deduction',
            'is_fixed' => 'nullable|boolean',
            'is_taxable' => 'nullable|boolean',
        ]);

        SalaryComponent::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_fixed' => $request->boolean('is_fixed'),
            'is_taxable' => $request->boolean('is_taxable', true),
        ]);

        return back()->with('success', "Komponen gaji '{$validated['name']}' berhasil ditambahkan ke master komponen.");
    }

    public function assign(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_component_id' => 'required|exists:salary_components,id',
            'amount' => 'required|numeric|min:0',
        ]);

        EmployeeSalaryComponent::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $validated['employee_id'],
                'salary_component_id' => $validated['salary_component_id'],
            ],
            [
                'amount' => $validated['amount'],
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Komponen gaji berhasil ditetapkan untuk karyawan.');
    }

    public function destroy(SalaryComponent $salaryComponent): RedirectResponse
    {
        $salaryComponent->delete();
        return back()->with('success', 'Komponen gaji berhasil dihapus.');
    }
}
