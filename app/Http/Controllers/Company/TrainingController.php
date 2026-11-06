<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use App\Services\Talent\TrainingService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected TrainingService $trainingService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $category = $request->query('category');

        $query = TrainingCourse::withCount('enrollments')
            ->with(['enrollments.employee'])
            ->where('tenant_id', $tenant->id)
            ->latest('start_date');

        if (!empty($category)) {
            $query->where('category', $category);
        }

        $courses = $query->paginate(10);
        $employees = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->get();

        $stats = [
            'total_courses' => TrainingCourse::where('tenant_id', $tenant->id)->count(),
            'total_enrollments' => TrainingEnrollment::where('tenant_id', $tenant->id)->count(),
            'completed_enrollments' => TrainingEnrollment::where('tenant_id', $tenant->id)->where('status', 'completed')->count(),
            'certificates_issued' => TrainingEnrollment::where('tenant_id', $tenant->id)->whereNotNull('certificate_number')->count(),
        ];

        return view('company.training.index', compact('courses', 'employees', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'trainer_name' => 'nullable|string|max:150',
            'category' => 'required|in:technical,leadership,compliance,soft_skills',
            'location' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $course = $this->trainingService->createCourse($tenant->id, $validated);

        return back()->with('success', "Program pelatihan '{$course->title}' berhasil dijadwalkan.");
    }

    public function enroll(Request $request, TrainingCourse $course): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);
        $this->trainingService->enrollEmployee($course, $employee);

        return back()->with('success', "Karyawan {$employee->full_name} berhasil didaftarkan ke pelatihan '{$course->title}'.");
    }

    public function complete(Request $request, TrainingEnrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $this->trainingService->completeEnrollment(
            $enrollment,
            (float) $validated['score'],
            $validated['feedback'] ?? null
        );

        return back()->with('success', "Kelulusan pelatihan berhasil dicatat. Nomor Sertifikat: {$enrollment->certificate_number}.");
    }
}
