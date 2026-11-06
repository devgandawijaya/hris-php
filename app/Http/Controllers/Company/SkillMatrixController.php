<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSkill;
use App\Models\Position;
use App\Models\PositionSkillRequirement;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Services\Talent\SkillMatrixService;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillMatrixController extends Controller
{
    public function __construct(
        protected SkillMatrixService $skillMatrixService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $departments = Department::where('tenant_id', $tenantId)->orderBy('name')->get();
        $positions = Position::where('tenant_id', $tenantId)->orderBy('title')->get();
        $categories = SkillCategory::where('tenant_id', $tenantId)->with('skills')->orderBy('name')->get();
        $skills = Skill::where('tenant_id', $tenantId)->with('category')->orderBy('name')->get();

        $selectedDeptId = $request->query('department_id');

        // Fetch employees
        $empQuery = Employee::where('tenant_id', $tenantId)->where('status', 'active');
        if ($selectedDeptId) {
            $empQuery->where('department_id', $selectedDeptId);
        }
        $employees = $empQuery->with(['department', 'position', 'skills.skill'])->orderBy('first_name')->paginate(15);

        // Calculate skill gap metrics for each employee
        $employeeScorecards = [];
        foreach ($employees as $emp) {
            $employeeScorecards[$emp->id] = $this->skillMatrixService->calculateEmployeeSkillGap($emp);
        }

        // Position requirements grouped by position
        $positionRequirements = PositionSkillRequirement::where('tenant_id', $tenantId)
            ->with(['position.department', 'skill.category'])
            ->get()
            ->groupBy('position_id');

        // Department-wide overview
        $deptOverview = $this->skillMatrixService->getDepartmentCompetencyOverview($tenantId, $selectedDeptId ? (int)$selectedDeptId : null);

        return view('company.skills.index', compact(
            'departments',
            'positions',
            'categories',
            'skills',
            'employees',
            'employeeScorecards',
            'positionRequirements',
            'deptOverview',
            'selectedDeptId'
        ));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        SkillCategory::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('company.skills.index')
            ->with('success', "Kategori kompetensi '{$validated['name']}' berhasil ditambahkan.");
    }

    public function storeSkill(Request $request): RedirectResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $validated = $request->validate([
            'skill_category_id' => 'required|exists:skill_categories,id',
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        Skill::create([
            'tenant_id' => $tenantId,
            'skill_category_id' => $validated['skill_category_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('company.skills.index')
            ->with('success', "Kompetensi keahlian '{$validated['name']}' berhasil didaftarkan ke kamus.");
    }

    public function storePositionRequirement(Request $request): RedirectResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'skill_id' => 'required|exists:skills,id',
            'required_level' => 'required|integer|min:1|max:5',
            'is_mandatory' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        PositionSkillRequirement::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'position_id' => $validated['position_id'],
                'skill_id' => $validated['skill_id'],
            ],
            [
                'required_level' => $validated['required_level'],
                'is_mandatory' => $request->has('is_mandatory'),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->route('company.skills.index')
            ->with('success', 'Standar kompetensi jabatan berhasil disimpan.');
    }

    public function deletePositionRequirement(int $id): RedirectResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $req = PositionSkillRequirement::where('id', $id)->where('tenant_id', $tenantId)->firstOrFail();
        $req->delete();

        return redirect()->route('company.skills.index')
            ->with('success', 'Standar kompetensi jabatan berhasil dihapus.');
    }

    public function storeEmployeeSkill(Request $request): RedirectResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'skill_id' => 'required|exists:skills,id',
            'current_level' => 'required|integer|min:1|max:5',
            'certification_name' => 'nullable|string|max:150',
            'certification_date' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:certification_date',
            'assessment_notes' => 'nullable|string|max:500',
        ]);

        $this->skillMatrixService->assessEmployeeSkill(
            $tenantId,
            (int)$validated['employee_id'],
            (int)$validated['skill_id'],
            (int)$validated['current_level'],
            $validated['certification_name'] ?? null,
            $validated['certification_date'] ?? null,
            $validated['valid_until'] ?? null,
            auth()->id(),
            $validated['assessment_notes'] ?? null
        );

        return redirect()->route('company.skills.index')
            ->with('success', 'Penilaian kompetensi karyawan berhasil dicatat.');
    }

    public function gapAnalysis(Employee $employee): JsonResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();
        if ($employee->tenant_id !== $tenantId) {
            abort(403);
        }

        $analysis = $this->skillMatrixService->calculateEmployeeSkillGap($employee);

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'code' => $employee->employee_code,
                'position' => $employee->position?->title ?? '-',
                'department' => $employee->department?->name ?? '-',
            ],
            'analysis' => $analysis,
        ]);
    }
}
