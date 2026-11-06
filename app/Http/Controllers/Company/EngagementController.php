<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyEvent;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeRecognition;
use App\Models\EmployeeSurvey;
use App\Services\Engagement\SurveyAndRecognitionService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected SurveyAndRecognitionService $engagementService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();

        $surveys = EmployeeSurvey::with('createdBy')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->get();

        $recognitions = EmployeeRecognition::with(['employee.department', 'sender'])
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(12);

        $events = CompanyEvent::with(['targetDepartment', 'createdBy'])
            ->where('tenant_id', $tenant->id)
            ->orderBy('start_time')
            ->get();

        $employees = Employee::with('department')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->get();

        $departments = Department::where('tenant_id', $tenant->id)->get();

        // Calculate aggregate eNPS across all active surveys
        $aggregateEnps = [
            'total_responses' => 0,
            'enps_score' => 0,
        ];
        if ($surveys->isNotEmpty()) {
            $latestSurvey = $surveys->first();
            $aggregateEnps = $latestSurvey->calculateEnps();
        }

        return view('company.engagement.index', compact('surveys', 'recognitions', 'events', 'employees', 'departments', 'aggregateEnps'));
    }

    public function storeSurvey(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'survey_type' => 'required|in:enps,satisfaction,culture,pulse',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $this->engagementService->createSurvey($tenant->id, $validated, Auth::user());

        return back()->with('success', 'Kampanye survei kepuasan kerja baru berhasil dipublikasikan.');
    }

    public function storeRecognition(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'badge_type' => 'required|in:star_performer,team_player,innovation,customer_hero,core_values',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'reward_amount' => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);

        $this->engagementService->grantRecognition($employee, Auth::user(), $validated);

        return back()->with('success', "Apresiasi resmi berhasil diberikan kepada {$employee->full_name}.");
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:townhall,gathering,training,meeting,celebration',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'target_department_id' => 'nullable|exists:departments,id',
        ]);

        $this->engagementService->createCompanyEvent($tenant->id, $validated, Auth::user());

        return back()->with('success', 'Agenda kegiatan perusahaan berhasil ditambahkan ke kalender.');
    }
}
