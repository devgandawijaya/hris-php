<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ProbationEvaluation;
use App\Services\Probation\ProbationEvaluationService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProbationEvaluationController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected ProbationEvaluationService $probationService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $evaluations = ProbationEvaluation::where('company_id', $company->id)
            ->with(['employee.department', 'employee.position', 'evaluator'])
            ->latest('evaluation_date')
            ->paginate(15);

        // Employees currently in probation status or approaching 3-month limit
        $probationEmployees = Employee::where('company_id', $company->id)
            ->whereIn('employment_status', ['probation', 'pkwt'])
            ->where('status', 'active')
            ->orderBy('join_date')
            ->get();

        $stats = [
            'total' => ProbationEvaluation::where('company_id', $company->id)->count(),
            'passed' => ProbationEvaluation::where('company_id', $company->id)->where('decision', 'passed_pkwtt')->count(),
            'failed' => ProbationEvaluation::where('company_id', $company->id)->where('decision', 'failed_terminated')->count(),
            'pending_review' => $probationEmployees->count(),
        ];

        $summary = [
            'total_evaluations' => $stats['total'],
            'passed_count' => $stats['passed'],
            'failed_count' => $stats['failed'],
            'extended_count' => ProbationEvaluation::where('company_id', $company->id)->where('decision', 'extended')->count(),
            'pending_probation' => $stats['pending_review'],
        ];

        $eligibleEmployees = $probationEmployees;

        return view('company.probation.index', compact('company', 'evaluations', 'probationEmployees', 'eligibleEmployees', 'stats', 'summary'));
    }

    public function show(ProbationEvaluation $evaluation): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->first() ?? Company::first();
        $evaluation->load(['company', 'employee.department', 'employee.position', 'evaluator']);
        return view('company.probation.show', compact('company', 'evaluation'));
    }

    public function store(Request $request): RedirectResponse
    {
        $input = $request->all();

        // Field mapping for UI form
        if (isset($input['technical_score']) && !isset($input['technical_competence_score'])) {
            $input['technical_competence_score'] = $input['technical_score'];
        }
        if (isset($input['attendance_score']) && !isset($input['attendance_discipline_score'])) {
            $input['attendance_discipline_score'] = $input['attendance_score'];
        }
        if (isset($input['culture_score']) && !isset($input['culture_integrity_score'])) {
            $input['culture_integrity_score'] = $input['culture_score'];
        }
        if (isset($input['probation_end_date']) && !isset($input['evaluation_date'])) {
            $input['evaluation_date'] = $input['probation_end_date'];
        }
        if (isset($input['recommendation'])) {
            $rec = strtoupper($input['recommendation']);
            $input['decision'] = match($rec) {
                'PASS' => 'passed_pkwtt',
                'FAIL' => 'failed_terminated',
                'EXTEND' => 'extended',
                default => 'passed_pkwtt',
            };
        }

        $validated = validator($input, [
            'employee_id' => ['required', 'exists:employees,id'],
            'evaluation_date' => ['required', 'date'],
            'technical_competence_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'attendance_discipline_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'culture_integrity_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'decision' => ['nullable', 'in:passed_pkwtt,extended,failed_terminated'],
            'evaluator_notes' => ['nullable', 'string', 'max:1000'],
            'development_recommendations' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $employee = Employee::findOrFail($validated['employee_id']);
        $evaluation = $this->probationService->evaluateProbation($employee, $request->user(), $validated);

        return redirect()->route('company.probation.show', $evaluation)
            ->with('success', "Evaluasi masa percobaan untuk {$employee->full_name} berhasil diputuskan.");
    }

    public function printSk(ProbationEvaluation $evaluation): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->first() ?? Company::first();
        $evaluation->load(['company', 'employee.department', 'employee.position', 'evaluator']);
        return view('company.probation.print_sk', compact('company', 'evaluation'));
    }
}
