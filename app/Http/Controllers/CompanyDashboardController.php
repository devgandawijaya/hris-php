<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\Department;
use App\Models\Reimbursement;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\WorkplaceIncident;
use App\Models\EmployeeWarning;
use App\Models\EmployeeLoan;
use App\Models\CompanyAnnouncement;
use App\Models\HrHelpdeskTicket;
use App\Models\CompanyRegulation;
use App\Models\OvertimeOrder;
use App\Models\WltkReport;
use App\Models\EmployeeDisabilityProfile;
use App\Models\EmployeeGrievance;
use App\Models\Holiday;
use App\Models\BipartiteMeeting;
use App\Models\TrainingEnrollment;
use App\Services\Analytics\HrAnalyticsService;
use App\Tenancy\TenantManager;
use Illuminate\View\View;

class CompanyDashboardController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected HrAnalyticsService $analyticsService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = $this->tenantManager->getCompany();
        $tenantId = $tenant->id;

        // Executive HR Metrics (Headcount, Turnover, Demografi, Payroll Cost)
        $metrics = $this->analyticsService->getExecutiveMetrics($tenantId);

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $probationEmployees = Employee::where('employment_status', 'probation')->count();

        // Presensi Hari Ini (Live Attendance)
        $today = now()->toDateString();
        $todayAttendance = AttendanceRecord::where('date', $today)->get();
        $presentCount = $todayAttendance->where('status', 'present')->count();
        $lateCount = $todayAttendance->where('status', 'late')->count();
        $onLeaveTodayCount = $todayAttendance->where('status', 'leave')->count();
        $notClockedInCount = max(0, $activeEmployees - ($presentCount + $lateCount + $onLeaveTodayCount));

        $recentAttendanceLogs = AttendanceRecord::where('date', $today)
            ->with(['employee.department', 'employee.position'])
            ->latest('clock_in')
            ->take(5)
            ->get();

        // Approval Hub Terpadu (Cuti, Reimbursement, Lembur/SPL, Kasbon)
        $pendingLeaveRequests = LeaveRequest::with(['employee', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $pendingReimbursements = Reimbursement::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $pendingOvertimeOrders = OvertimeOrder::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $pendingLoans = EmployeeLoan::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $totalPendingApprovals = $pendingLeaveRequests->count() 
            + $pendingReimbursements->count() 
            + $pendingOvertimeOrders->count() 
            + $pendingLoans->count();

        // Departemen
        $departments = Department::withCount('employees')->get();

        // Peringatan: Jatuh Tempo PKWT (< 60 Hari) PP 35/2021
        $expiringContracts = Employee::where('employment_status', 'pkwt')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->get();

        // Rekrutmen (ATS)
        $activeJobPostings = JobPosting::where('status', 'published')->count();
        $incomingApplications = JobApplication::whereIn('stage', ['applied', 'screening', 'interview'])->count();
        $recentApplications = JobApplication::with(['jobPosting', 'candidate'])
            ->latest()
            ->take(4)
            ->get();

        // K3 & Disiplin (SMK3 PP 50/2012)
        $totalIncidents = WorkplaceIncident::count();
        $activeWarnings = EmployeeWarning::where('status', 'active')->count();

        // Layanan, Whistleblowing & Pinjaman
        $openHelpdeskTickets = HrHelpdeskTicket::where('status', 'open')->count();
        $openGrievancesCount = EmployeeGrievance::whereIn('status', ['submitted', 'in_review', 'investigating'])->count();
        $activeLoansCount = EmployeeLoan::where('status', 'approved')->count();
        $activeLoansTotal = EmployeeLoan::where('status', 'approved')->sum('remaining_amount');

        // Pengumuman Perusahaan
        $announcements = CompanyAnnouncement::latest()->take(3)->get();

        // Radar Kepatuhan Regulasi (Compliance Radar)
        $companyRegulation = CompanyRegulation::where('status', 'active')->latest('effective_end_date')->first();
        $latestWltkReport = WltkReport::latest('reporting_year')->first();
        $disabilityCount = EmployeeDisabilityProfile::count();
        $disabilityRate = $activeEmployees > 0 ? round(($disabilityCount / $activeEmployees) * 100, 2) : 0.0;

        // Agenda & Kalender HR Mendatang
        $upcomingHolidays = Holiday::where('date', '>=', $today)->orderBy('date')->take(3)->get();
        $upcomingBirthdays = Employee::where('status', 'active')
            ->whereNotNull('birth_date')
            ->get()
            ->filter(fn($e) => $e->birth_date?->month === now()->month)
            ->sortBy(fn($e) => $e->birth_date?->day)
            ->take(4);
        $upcomingBipartite = BipartiteMeeting::where('meeting_date', '>=', $today)->orderBy('meeting_date')->first();

        // Pelatihan & Pengembangan
        $activeTrainingsCount = TrainingEnrollment::whereIn('status', ['enrolled', 'attended'])->count();
        $recentTrainings = TrainingEnrollment::with(['trainingCourse', 'employee'])
            ->latest()
            ->take(3)
            ->get();

        return view('company.dashboard', compact(
            'tenant',
            'company',
            'metrics',
            'totalEmployees',
            'activeEmployees',
            'probationEmployees',
            'presentCount',
            'lateCount',
            'onLeaveTodayCount',
            'notClockedInCount',
            'recentAttendanceLogs',
            'pendingLeaveRequests',
            'pendingReimbursements',
            'pendingOvertimeOrders',
            'pendingLoans',
            'totalPendingApprovals',
            'departments',
            'expiringContracts',
            'todayAttendance',
            'activeJobPostings',
            'incomingApplications',
            'recentApplications',
            'totalIncidents',
            'activeWarnings',
            'openHelpdeskTickets',
            'openGrievancesCount',
            'activeLoansCount',
            'activeLoansTotal',
            'announcements',
            'companyRegulation',
            'latestWltkReport',
            'disabilityCount',
            'disabilityRate',
            'upcomingHolidays',
            'upcomingBirthdays',
            'upcomingBipartite',
            'activeTrainingsCount',
            'recentTrainings'
        ));
    }
}
