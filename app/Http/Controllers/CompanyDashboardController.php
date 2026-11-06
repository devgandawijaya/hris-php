<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\Department;
use App\Tenancy\TenantManager;
use Illuminate\View\View;

class CompanyDashboardController extends Controller
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = $this->tenantManager->getCompany();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();

        $today = now()->toDateString();
        $todayAttendance = AttendanceRecord::where('date', $today)->get();
        $presentCount = $todayAttendance->where('status', 'present')->count();
        $lateCount = $todayAttendance->where('status', 'late')->count();

        $pendingLeaveRequests = LeaveRequest::with(['employee', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $departments = Department::withCount('employees')->get();

        // Expiring contracts (within next 60 days)
        $expiringContracts = Employee::where('employment_status', 'pkwt')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->get();

        return view('company.dashboard', compact(
            'tenant',
            'company',
            'totalEmployees',
            'activeEmployees',
            'presentCount',
            'lateCount',
            'pendingLeaveRequests',
            'departments',
            'expiringContracts',
            'todayAttendance'
        ));
    }
}

