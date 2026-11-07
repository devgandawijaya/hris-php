<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CommuteEmissionAudit;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeCommuteProfile;
use App\Services\Compliance\CarbonCommuteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CarbonCommuteController extends Controller
{
    public function __construct(
        protected CarbonCommuteService $commuteService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $selectedYear = (int) $request->input('year', date('Y'));

        $profiles = EmployeeCommuteProfile::with(['employee.department', 'employee.position'])
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(15);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        $audits = CommuteEmissionAudit::where('company_id', $company->id)
            ->latest('audit_year')
            ->get();

        $latestAudit = $audits->firstWhere('audit_year', $selectedYear);

        $stats = [
            'total_participating' => EmployeeCommuteProfile::where('company_id', $company->id)->count(),
            'total_weekly_emissions_kg' => EmployeeCommuteProfile::where('company_id', $company->id)->sum('weekly_emissions_kg_co2'),
            'total_weekly_avoided_kg' => EmployeeCommuteProfile::where('company_id', $company->id)->sum('weekly_avoided_emissions_kg_co2'),
        ];

        return view('company.esg.carbon_commute', compact(
            'company',
            'profiles',
            'employees',
            'audits',
            'latestAudit',
            'selectedYear',
            'stats'
        ));
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'home_to_office_distance_km' => 'required|numeric|min:0.1|max:200',
            'primary_commute_mode' => 'required|string|in:car_gasoline,motorcycle_gasoline,electric_vehicle,public_transit,bicycle_walking',
            'weekly_office_days' => 'required|integer|min:0|max:7',
            'weekly_remote_days' => 'required|integer|min:0|max:7',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        try {
            $this->commuteService->updateEmployeeCommute(
                $employee,
                (float) $validated['home_to_office_distance_km'],
                $validated['primary_commute_mode'],
                (int) $validated['weekly_office_days'],
                (int) $validated['weekly_remote_days']
            );

            return redirect()->route('carbon-commute.index')
                ->with('success', "Profil komuter emisi karbon {$employee->full_name} berhasil disimpan.");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function runAudit(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'audit_year' => 'required|integer|min:2020|max:2050',
        ]);

        $this->commuteService->runAnnualCommuteAudit($company, (int) $validated['audit_year']);

        return redirect()->route('carbon-commute.index', ['year' => $validated['audit_year']])
            ->with('success', "Audit Emisi Karbon Komuter Scope 3 Tahun {$validated['audit_year']} berhasil dihitung.");
    }
}
