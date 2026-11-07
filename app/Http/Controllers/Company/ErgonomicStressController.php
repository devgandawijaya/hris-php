<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\DigitalStressSurvey;
use App\Models\Employee;
use App\Models\ErgonomicAssessment;
use App\Services\Safety\ErgonomicStressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ErgonomicStressController extends Controller
{
    public function __construct(
        protected ErgonomicStressService $ergoService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $assessments = ErgonomicAssessment::with(['department', 'employee'])
            ->where('company_id', $company->id)
            ->latest('assessment_date')
            ->paginate(15);

        $stressSurveys = DigitalStressSurvey::with('employee')
            ->where('company_id', $company->id)
            ->latest('survey_date')
            ->paginate(15);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();
        $departments = Department::where('company_id', $company->id)->get();

        $stats = [
            'total_assessments' => ErgonomicAssessment::where('company_id', $company->id)->count(),
            'avg_compliance_score' => round(ErgonomicAssessment::where('company_id', $company->id)->avg('ergonomic_compliance_score') ?: 0, 1),
            'high_posture_risks' => ErgonomicAssessment::where('company_id', $company->id)->where('posture_risk_level', 'high')->count(),
            'avg_burnout_index' => round(DigitalStressSurvey::where('company_id', $company->id)->avg('digital_burnout_risk_index') ?: 0, 1),
        ];

        return view('company.ergonomics.index', compact(
            'company',
            'assessments',
            'stressSurveys',
            'employees',
            'departments',
            'stats'
        ));
    }

    public function storeErgonomic(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'workstation_type' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
            'lighting_lux_level' => 'required|integer|min:50|max:2000',
            'chair_lumbar_support_compliant' => 'nullable|boolean',
            'monitor_eye_level_compliant' => 'nullable|boolean',
            'keyboard_wrist_rest_compliant' => 'nullable|boolean',
            'corrective_actions' => 'nullable|string|max:1000',
        ]);

        $this->ergoService->recordErgonomicAssessment(
            $company,
            $validated['employee_id'] ? (int) $validated['employee_id'] : null,
            $validated['department_id'] ? (int) $validated['department_id'] : null,
            (int) $validated['lighting_lux_level'],
            (bool) ($request->has('chair_lumbar_support_compliant')),
            (bool) ($request->has('monitor_eye_level_compliant')),
            (bool) ($request->has('keyboard_wrist_rest_compliant')),
            $validated['workstation_type'],
            $validated['corrective_actions'] ?? null
        );

        return redirect()->route('ergonomics.index')->with('success', 'Audit Ergonomi Stasiun Kerja berhasil dicatat sesuai Permenaker 5/2018.');
    }

    public function storeStressSurvey(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'avg_daily_screen_hours' => 'required|numeric|min:1|max:20',
            'meeting_fatigue_score' => 'required|integer|min:1|max:5',
            'after_hours_communication_score' => 'required|integer|min:1|max:5',
            'eyestrain_headache_frequency' => 'required|integer|min:1|max:5',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $this->ergoService->recordStressSurvey(
            $employee,
            (float) $validated['avg_daily_screen_hours'],
            (int) $validated['meeting_fatigue_score'],
            (int) $validated['after_hours_communication_score'],
            (int) $validated['eyestrain_headache_frequency']
        );

        return redirect()->route('ergonomics.index')->with('success', "Survei Beban Kerja Digital {$employee->full_name} berhasil dicatat.");
    }
}
