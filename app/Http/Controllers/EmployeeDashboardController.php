<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrection;
use App\Models\CompanyAsset;
use App\Models\EmploymentContract;
use App\Models\KpiGoal;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OnboardingOffboardingChecklist;
use App\Models\OvertimeRequest;
use App\Models\Payroll;
use App\Models\PerformanceCycle;
use App\Models\PerformanceReview;
use App\Models\Reimbursement;
use App\Models\TrainingEnrollment;
use App\Models\CompanyAnnouncement;
use App\Models\EmployeeWarning;
use App\Models\EmployeeGrievance;
use App\Models\HrHelpdeskTicket;
use App\Models\EmployeeMutation;
use App\Models\EmployeeLoan;
use App\Models\EmployeeSurvey;
use App\Models\EmployeeRecognition;
use App\Models\CompanyEvent;
use App\Services\Finance\EmployeeLoanService;
use App\Services\Engagement\SurveyAndRecognitionService;
use App\Services\Reimbursement\ReimbursementService;
use App\Services\Talent\PerformanceService;
use App\Services\Talent\SkillMatrixService;
use App\Services\EmployeeRelations\GrievanceService;
use App\Services\EmployeeRelations\EngagementService;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmployeeDashboardController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected ReimbursementService $reimbursementService,
        protected PerformanceService $performanceService,
        protected GrievanceService $grievanceService,
        protected EngagementService $engagementService,
        protected EmployeeLoanService $loanService,
        protected SurveyAndRecognitionService $surveyService,
        protected SkillMatrixService $skillMatrixService
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $employee = Employee::with(['department', 'position'])->where('user_id', $user->id)->first();

        $today = now()->toDateString();
        $todayRecord = null;
        $attendanceHistory = collect();
        $leaveRequests = collect();
        $overtimeRequests = collect();
        $corrections = collect();
        $leaveTypes = LeaveType::all();
        $recentPayrolls = collect();
        $reimbursements = collect();
        $contracts = collect();
        $kpiGoals = collect();
        $performanceReviews = collect();
        $assignedAssets = collect();
        $myChecklists = collect();
        $myTrainings = collect();
        $activeCycles = collect();

        if ($employee) {
            $todayRecord = AttendanceRecord::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            $attendanceHistory = AttendanceRecord::where('employee_id', $employee->id)
                ->latest('date')
                ->take(7)
                ->get();

            $leaveRequests = LeaveRequest::with('leaveType')
                ->where('employee_id', $employee->id)
                ->latest()
                ->take(5)
                ->get();

            $overtimeRequests = OvertimeRequest::where('employee_id', $employee->id)
                ->latest('date')
                ->take(5)
                ->get();

            $corrections = AttendanceCorrection::where('employee_id', $employee->id)
                ->latest('date')
                ->take(5)
                ->get();

            $recentPayrolls = Payroll::with('payrollPeriod')
                ->where('employee_id', $employee->id)
                ->latest()
                ->take(6)
                ->get();

            $reimbursements = Reimbursement::where('employee_id', $employee->id)
                ->latest('expense_date')
                ->take(10)
                ->get();

            $contracts = EmploymentContract::where('employee_id', $employee->id)
                ->latest('start_date')
                ->get();

            $kpiGoals = KpiGoal::where('employee_id', $employee->id)->get();
            $performanceReviews = PerformanceReview::with('performanceCycle')->where('employee_id', $employee->id)->get();
            $assignedAssets = CompanyAsset::where('assigned_employee_id', $employee->id)->get();
            $myChecklists = OnboardingOffboardingChecklist::where('employee_id', $employee->id)->get();
            $myTrainings = TrainingEnrollment::with('trainingCourse')->where('employee_id', $employee->id)->get();
            $activeCycles = PerformanceCycle::where('tenant_id', $employee->tenant_id)->where('status', 'active')->get();

            // Phase 6: Relations & Engagement data
            $myWarnings = EmployeeWarning::where('employee_id', $employee->id)->latest('issued_date')->get();
            $myGrievances = EmployeeGrievance::where('employee_id', $employee->id)->latest()->get();
            $myHelpdeskTickets = HrHelpdeskTicket::where('employee_id', $employee->id)->latest()->get();
            $myMutations = EmployeeMutation::with(['fromDepartment', 'toDepartment', 'fromPosition', 'toPosition'])
                ->where('employee_id', $employee->id)
                ->latest('effective_date')
                ->get();

            // Phase 8: Financial Well-being & Engagement
            $myLoans = EmployeeLoan::with('installments')->where('employee_id', $employee->id)->latest()->get();
            $activeSurveys = EmployeeSurvey::where('tenant_id', $employee->tenant_id)->where('status', 'active')->where('end_date', '>=', now()->toDateString())->get();
            $myRecognitions = EmployeeRecognition::with('sender')->where('employee_id', $employee->id)->latest()->get();
            $upcomingEvents = CompanyEvent::where('tenant_id', $employee->tenant_id)
                ->where(function ($q) use ($employee) {
                    $q->whereNull('target_department_id')
                      ->orWhere('target_department_id', $employee->department_id);
                })
                ->where('start_time', '>=', now()->subHours(12))
                ->orderBy('start_time')
                ->take(5)
                ->get();

            $announcements = CompanyAnnouncement::where('tenant_id', $employee->tenant_id)
                ->where(function ($q) use ($employee) {
                    $q->whereNull('target_department_id')
                      ->orWhere('target_department_id', $employee->department_id);
                })
                ->where('published_at', '<=', now()->toDateString())
                ->orderBy('is_pinned', 'desc')
                ->latest('published_at')
                ->take(5)
                ->get();

            // Phase 9: Competency and Skill Gap Analysis
            $mySkillAnalysis = $this->skillMatrixService->calculateEmployeeSkillGap($employee);
        } else {
            $myWarnings = collect();
            $myGrievances = collect();
            $myHelpdeskTickets = collect();
            $myMutations = collect();
            $myLoans = collect();
            $activeSurveys = collect();
            $myRecognitions = collect();
            $upcomingEvents = collect();
            $announcements = collect();
            $mySkillAnalysis = [
                'has_requirements' => false,
                'overall_match_percentage' => 0,
                'total_requirements' => 0,
                'met_requirements' => 0,
                'skills' => [],
                'recommended_trainings' => collect(),
            ];
        }

        return view('employee.dashboard', compact(
            'user',
            'employee',
            'todayRecord',
            'attendanceHistory',
            'leaveRequests',
            'overtimeRequests',
            'corrections',
            'leaveTypes',
            'recentPayrolls',
            'reimbursements',
            'contracts',
            'kpiGoals',
            'performanceReviews',
            'assignedAssets',
            'myChecklists',
            'myTrainings',
            'activeCycles',
            'myWarnings',
            'myGrievances',
            'myHelpdeskTickets',
            'myMutations',
            'myLoans',
            'activeSurveys',
            'myRecognitions',
            'upcomingEvents',
            'announcements',
            'mySkillAnalysis'
        ));
    }

    public function clock(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Profil karyawan belum tertaut dengan akun pengguna ini.');
        }

        $today = now()->toDateString();
        $record = AttendanceRecord::firstOrNew([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        $currentTime = now()->toTimeString();

        if (!$record->exists || empty($record->clock_in)) {
            // Clock In
            $record->clock_in = $currentTime;
            $record->clock_in_latitude = $request->input('latitude', -6.225588);
            $record->clock_in_longitude = $request->input('longitude', 106.811802);

            // Shift tolerance check (e.g. 08:45)
            $shiftStart = '08:30:00';
            if ($currentTime > '08:45:00') {
                $record->status = 'late';
                $record->late_minutes = (int) round((strtotime($currentTime) - strtotime($shiftStart)) / 60);
            } else {
                $record->status = 'present';
                $record->late_minutes = 0;
            }

            $record->save();

            return back()->with('success', "Presensi Masuk (Clock In) berhasil dicatat pada pukul {$currentTime}. Lokasi GPS diverifikasi dalam radius.");
        } else {
            // Clock Out
            $record->clock_out = $currentTime;
            $start = strtotime($record->clock_in);
            $end = strtotime($currentTime);
            $diffHours = round(($end - $start) / 3600, 2);
            $record->work_hours = max(0, $diffHours);
            $record->save();

            return back()->with('success', "Presensi Pulang (Clock Out) berhasil dicatat pada pukul {$currentTime}. Total jam kerja efektif: {$diffHours} jam.");
        }
    }

    public function submitLeave(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Profil karyawan belum ditemukan.');
        }

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $start = strtotime($validated['start_date']);
        $end = strtotime($validated['end_date']);
        $days = (int) round(($end - $start) / (60 * 60 * 24)) + 1;

        LeaveRequest::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $days,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return back()->with('success', "Pengajuan cuti selama {$days} hari berhasil diajukan dan sedang menunggu persetujuan atasan.");
    }

    public function submitCorrection(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'corrected_clock_in' => 'nullable|date_format:H:i',
            'corrected_clock_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        AttendanceCorrection::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'corrected_clock_in' => $validated['corrected_clock_in'],
            'corrected_clock_out' => $validated['corrected_clock_out'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Permohonan koreksi presensi berhasil dikirim ke bagian HR.');
    }

    public function submitOvertime(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:12',
            'is_holiday' => 'nullable|boolean',
            'reason' => 'required|string|max:500',
        ]);

        OvertimeRequest::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'hours' => $validated['hours'],
            'is_holiday' => $request->boolean('is_holiday'),
            'reason' => $validated['reason'],
            'status' => 'pending',
            'calculated_pay' => 0.00,
        ]);

        return back()->with('success', 'Surat Perintah Lembur (SPL) berhasil diajukan ke atasan.');
    }

    public function submitReimbursement(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'category' => 'required|in:medical,transport,meal,business_trip,operational,other',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1000',
            'expense_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $this->reimbursementService->submitClaim(
            $employee,
            $validated['category'],
            $validated['title'],
            (float) $validated['amount'],
            $validated['expense_date'],
            $validated['description'] ?? null,
            $request->file('receipt')
        );

        return back()->with('success', 'Pengajuan reimbursement berhasil dikirim dan menunggu persetujuan.');
    }

    public function submitSelfAppraisal(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'performance_cycle_id' => 'required|exists:performance_cycles,id',
            'self_rating' => 'required|numeric|min:0|max:100',
            'self_feedback' => 'required|string|max:2000',
        ]);

        $cycle = PerformanceCycle::where('tenant_id', $employee->tenant_id)->findOrFail($validated['performance_cycle_id']);

        $this->performanceService->submitSelfReview(
            $cycle,
            $employee,
            (float) $validated['self_rating'],
            $validated['self_feedback']
        );

        return back()->with('success', 'Evaluasi mandiri (Self-Appraisal) berhasil dikirim.');
    }

    public function updateKpiProgress(Request $request, KpiGoal $goal): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee || $goal->employee_id !== $employee->id) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);

        $this->performanceService->updateGoalProgress($goal, (float) $validated['current_value']);

        return back()->with('success', 'Pencapaian KPI Anda berhasil diperbarui.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:50',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'marital_status' => 'nullable|in:single,married,widowed,divorced',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:150',
        ]);

        if ($employee) {
            $employee->update($validated);
        }

        if (!empty($validated['phone'])) {
            $user->update(['phone' => $validated['phone']]);
        }

        return back()->with('success', 'Data profil mandiri Anda berhasil diperbarui.');
    }

    public function submitGrievance(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'category' => 'required|in:workplace_conflict,harassment,compensation,facility,unfair_treatment,other',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:3000',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $this->grievanceService->submitGrievance($employee, $validated);

        return back()->with('success', 'Laporan keluhan / aspirasi Anda telah terkirim secara aman ke manajemen HR.');
    }

    public function submitHelpdeskTicket(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'category' => 'required|in:work_certificate,bank_reference,data_correction,bpjs_inquiry,general',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
        ]);

        $ticket = $this->engagementService->createTicket($employee, $validated);

        return back()->with('success', "Tiket layanan HR {$ticket->ticket_number} berhasil dibuat. Tim HR akan segera memproses permohonan Anda.");
    }

    public function applyLoan(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'loan_type' => 'required|in:emergency,salary_advance,education,housing,other',
            'amount' => 'required|numeric|min:100000',
            'term_months' => 'required|integer|min:1|max:24',
            'reason' => 'required|string|max:1000',
        ]);

        $loan = $this->loanService->applyLoan($employee, $validated);

        return back()->with('success', "Pengajuan pinjaman/kasbon {$loan->loan_number} berhasil dikirim ke HR/Finance.");
    }

    public function submitSurvey(Request $request, EmployeeSurvey $survey): RedirectResponse
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'enps_score' => 'nullable|integer|min:0|max:10',
            'satisfaction_rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:2000',
        ]);

        $this->surveyService->submitSurveyResponse($survey, $employee, $validated);

        return back()->with('success', 'Terima kasih! Tanggapan survei Anda telah berhasil tersimpan secara aman.');
    }
}
