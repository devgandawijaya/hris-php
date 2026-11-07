<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\InternshipProgram;
use App\Models\InternshipLogbook;
use App\Services\Talent\InternshipApprenticeshipService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class InternshipProgramController extends Controller
{
    public function __construct(
        protected InternshipApprenticeshipService $internshipService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $internships = InternshipProgram::with(['department', 'mentor', 'logbooks'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('start_date')
            ->paginate(10);

        $departments = Department::where('company_id', $company->id)->orderBy('name')->get();
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();
        $totalWorkforce = $employees->count();

        return view('company.internships.index', compact('company', 'internships', 'departments', 'employees', 'totalWorkforce'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'program_title' => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pocket_money_allowance_daily' => 'required|numeric|min:0',
            'mentor_employee_id' => 'nullable|exists:employees,id',
            'intern_candidate_name' => 'required|string|max:150',
            'intern_nik_ktp' => 'required|string|max:50',
            'institution_origin' => 'required|string|max:150',
            'bpjs_bpu_covered' => 'nullable|boolean',
        ]);

        $totalWorkforce = Employee::where('company_id', $company->id)->where('status', 'active')->count();

        try {
            $this->internshipService->enrollIntern($company, $validated, $totalWorkforce);

            return redirect()->route('internships.index')->with('success', 'Peserta pemagangan resmi didaftarkan sesuai Permenaker No. 6/2020 dengan perlindungan BPJS.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        $tenantId = session('active_tenant_id');
        $internship = InternshipProgram::with(['department', 'mentor', 'logbooks'])->where('tenant_id', $tenantId)->findOrFail($id);
        $certificateData = $this->internshipService->generateCertificatePayload($internship);

        return view('company.internships.show', compact('internship', 'certificateData'));
    }

    public function storeLogbook(Request $request, string $internshipId): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $internship = InternshipProgram::where('tenant_id', $tenantId)->findOrFail($internshipId);

        $validated = $request->validate([
            'log_date' => 'required|date',
            'activity_summary' => 'required|string',
            'competency_mastered' => 'required|string|max:150',
        ]);

        $this->internshipService->recordLogbook($internship, $validated);

        return back()->with('success', 'Aktivitas logbook pemagangan berhasil dicatat.');
    }

    public function verifyLogbook(Request $request, string $logbookId): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $logbook = InternshipLogbook::where('tenant_id', $tenantId)->findOrFail($logbookId);

        $validated = $request->validate([
            'mentor_rating' => 'required|numeric|min:1.0|max:5.0',
            'mentor_feedback' => 'required|string',
        ]);

        $this->internshipService->verifyLogbook($logbook, (float) $validated['mentor_rating'], $validated['mentor_feedback']);

        return back()->with('success', 'Logbook berhasil diverifikasi dan dinilai oleh mentor.');
    }

    public function printCertificate(string $id): View
    {
        $tenantId = session('active_tenant_id');
        $internship = InternshipProgram::with(['department', 'mentor', 'company'])->where('tenant_id', $tenantId)->findOrFail($id);
        $certificateData = $this->internshipService->generateCertificatePayload($internship);

        return view('company.internships.print_certificate', compact('internship', 'certificateData'));
    }
}
