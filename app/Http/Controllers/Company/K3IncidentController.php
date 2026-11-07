<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\WorkplaceIncident;
use App\Models\BpjsJkkClaim;
use App\Models\EmployeeMcuRecord;
use App\Services\Safety\K3IncidentService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class K3IncidentController extends Controller
{
    public function __construct(
        protected K3IncidentService $k3Service
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $selectedYear = (int) $request->get('year', date('Y'));

        $incidents = WorkplaceIncident::with(['employee.department', 'bpjsJkkClaim'])
            ->where('company_id', $company->id)
            ->whereYear('incident_date', $selectedYear)
            ->latest('incident_date')
            ->paginate(10);

        $mcuRecords = EmployeeMcuRecord::with('employee.department')
            ->where('tenant_id', $tenantId)
            ->latest('mcu_date')
            ->take(15)
            ->get();

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $metrics = $this->k3Service->getK3Metrics($company, $selectedYear);

        return view('company.k3.index', compact(
            'company',
            'incidents',
            'mcuRecords',
            'employees',
            'metrics',
            'selectedYear'
        ));
    }

    public function storeIncident(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'incident_date' => ['required', 'date'],
            'incident_time' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:150'],
            'severity' => ['required', 'in:near_miss,minor_first_aid,lost_time_injury_lti,fatality'],
            'injury_type' => ['nullable', 'string', 'max:150'],
            'lost_workdays' => ['nullable', 'integer', 'min:0'],
            'description' => ['required', 'string'],
            'root_cause_analysis' => ['nullable', 'string'],
            'corrective_actions' => ['nullable', 'string'],
        ]);

        $victim = Employee::findOrFail($validated['employee_id']);
        $incident = $this->k3Service->reportIncident($company, $victim, $validated, $request->user());

        return back()->with('success', "Insiden K3 [{$incident->incident_number}] berhasil didokumentasikan.");
    }

    public function fileJkkClaim(Request $request, WorkplaceIncident $incident): RedirectResponse
    {
        $validated = $request->validate([
            'stage' => ['required', 'in:tahap_1,tahap_2'],
            'medical_facility_name' => ['required', 'string', 'max:150'],
            'estimated_medical_expense' => ['nullable', 'numeric', 'min:0'],
            'bpjs_notes' => ['nullable', 'string'],
        ]);

        $claim = $this->k3Service->fileBpjsJkkClaim($incident, $validated);

        return back()->with('success', "Berkas Klaim JKK BPJS [{$claim->claim_number}] berhasil diajukan resmi.");
    }

    public function printJkkClaim(BpjsJkkClaim $claim): View
    {
        $claim->load(['incident.employee.company', 'incident.employee.department']);
        return view('company.k3.jkk_claim', compact('claim'));
    }

    public function storeMcu(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'mcu_date' => ['required', 'date'],
            'clinic_provider' => ['required', 'string', 'max:150'],
            'blood_pressure' => ['nullable', 'string', 'max:30'],
            'cholesterol' => ['nullable', 'numeric'],
            'blood_sugar' => ['nullable', 'numeric'],
            'fitness_category' => ['required', 'in:fit_to_work,fit_with_restriction,temporarily_unfit,unfit'],
            'doctor_notes' => ['nullable', 'string'],
            'follow_up_recommendation' => ['nullable', 'string'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $this->k3Service->recordMcu($employee, $validated);

        return back()->with('success', "Hasil pemeriksaan kesehatan MCU {$employee->full_name} berhasil dicatat.");
    }
}
