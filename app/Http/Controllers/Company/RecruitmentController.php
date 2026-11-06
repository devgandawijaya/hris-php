<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\JobApplication;
use App\Models\JobCandidate;
use App\Models\JobPosting;
use App\Models\Position;
use App\Services\Talent\RecruitmentService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected RecruitmentService $recruitmentService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');

        $query = JobPosting::with(['department', 'position'])
            ->withCount('applications')
            ->where('tenant_id', $tenant->id)
            ->latest();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $postings = $query->paginate(10);
        $departments = Department::where('tenant_id', $tenant->id)->get();
        $positions = Position::where('tenant_id', $tenant->id)->get();

        $stats = [
            'total_postings' => JobPosting::where('tenant_id', $tenant->id)->count(),
            'active_postings' => JobPosting::where('tenant_id', $tenant->id)->where('status', 'published')->count(),
            'total_candidates' => JobCandidate::where('tenant_id', $tenant->id)->count(),
            'total_applications' => JobApplication::where('tenant_id', $tenant->id)->count(),
            'hired_count' => JobApplication::where('tenant_id', $tenant->id)->where('stage', 'hired')->count(),
        ];

        return view('company.recruitment.index', compact('postings', 'departments', 'positions', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employment_type' => 'required|in:full_time,part_time,contract,internship',
            'location' => 'required|string|max:150',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);

        $posting = $this->recruitmentService->createPosting($tenant->id, $validated);

        return back()->with('success', "Lowongan '{$posting->title}' berhasil dipublikasikan.");
    }

    public function show(JobPosting $jobPosting): View
    {
        $posting = $jobPosting;
        $tenant = $this->tenantManager->getTenant();

        $applications = JobApplication::with(['candidate', 'convertedEmployee'])
            ->where('tenant_id', $tenant->id)
            ->where('job_posting_id', $posting->id)
            ->latest()
            ->get();

        return view('company.recruitment.show', compact('posting', 'applications'));
    }

    public function applyCandidate(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'resume' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $this->recruitmentService->applyForJob($jobPosting, $validated, $request->file('resume'));

        return back()->with('success', 'Data pelamar baru berhasil didaftarkan ke lowongan ini.');
    }

    public function updateStage(Request $request, JobApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'stage' => 'required|in:applied,screening,interview,offered,hired,rejected',
            'interview_scheduled_at' => 'nullable|date',
            'interview_notes' => 'nullable|string',
            'interview_score' => 'nullable|numeric|min:0|max:100',
            'offered_salary' => 'nullable|numeric|min:0',
            'rejection_reason' => 'nullable|string',
        ]);

        $this->recruitmentService->advanceStage($application, $validated['stage'], $validated);

        return back()->with('success', "Status lamaran berhasil diperbarui menjadi " . strtoupper($validated['stage']) . ".");
    }

    public function convertToEmployee(Request $request, JobApplication $application): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_number' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'employment_status' => 'required|in:pkwt,pkwtt,probation,internship',
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
        ]);

        $company = $tenant->companies()->first();

        $employee = $this->recruitmentService->convertToEmployee(
            $application,
            $company->id,
            $validated
        );

        return redirect()->route('company.employees.show', $employee->id)
            ->with('success', "Kandidat {$employee->full_name} berhasil dikonversi menjadi Karyawan resmi (NIK: {$employee->employee_number}).");
    }
}

