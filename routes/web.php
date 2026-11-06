<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\CompanyManagementController;
use App\Http\Controllers\AdminCmsController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LeaveManagementController;
use App\Http\Controllers\OvertimeManagementController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\TenantSwitchController;
use App\Http\Controllers\Company\PayrollController;
use App\Http\Controllers\Company\PayslipController;
use App\Http\Controllers\Company\SalaryComponentController;
use App\Http\Controllers\Company\ThrController;
use App\Http\Controllers\Company\TaxReconciliationController;
use App\Http\Controllers\Company\ReimbursementController;
use App\Http\Controllers\Company\ContractManagementController;
use App\Http\Controllers\Company\RecruitmentController;
use App\Http\Controllers\Company\OnboardingOffboardingController;
use App\Http\Controllers\Company\PerformanceController;
use App\Http\Controllers\Company\TrainingController;
use App\Http\Controllers\Company\WorkflowManagementController;
use App\Http\Controllers\Company\AnalyticsController;
use App\Http\Controllers\Company\BillingController;
use App\Http\Controllers\Company\IntegrationController;
use App\Http\Controllers\Company\SecurityController;
use App\Http\Controllers\Company\DisciplinaryController;
use App\Http\Controllers\Company\GrievanceController;
use App\Http\Controllers\Company\AnnouncementController;
use App\Http\Controllers\Company\HelpdeskController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicLandingController::class, 'index'])->name('landing');
Route::post('/contact-inquiry', [PublicLandingController::class, 'submitContact'])->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Super Admin Platform Routes (Prefix: /super-admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'super.admin'])->prefix('super-admin')->name('super.admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    // Companies & Tenant Management
    Route::get('/companies', [CompanyManagementController::class, 'index'])->name('companies.index');
    Route::get('/companies/{id}', [CompanyManagementController::class, 'show'])->name('companies.show');
    Route::post('/companies/{id}/toggle-status', [CompanyManagementController::class, 'toggleStatus'])->name('companies.toggle-status');

    // CMS Management
    Route::get('/cms', [AdminCmsController::class, 'index'])->name('cms.index');
    Route::post('/cms/sliders', [AdminCmsController::class, 'storeSlider'])->name('cms.sliders.store');
    Route::delete('/cms/sliders/{id}', [AdminCmsController::class, 'deleteSlider'])->name('cms.sliders.delete');
    Route::post('/cms/features', [AdminCmsController::class, 'storeFeature'])->name('cms.features.store');
    Route::delete('/cms/features/{id}', [AdminCmsController::class, 'deleteFeature'])->name('cms.features.delete');
    Route::post('/cms/faqs', [AdminCmsController::class, 'storeFaq'])->name('cms.faqs.store');
    Route::delete('/cms/faqs/{id}', [AdminCmsController::class, 'deleteFaq'])->name('cms.faqs.delete');
    Route::post('/cms/settings', [AdminCmsController::class, 'updateSettings'])->name('cms.settings.update');
});

/*
|--------------------------------------------------------------------------
| Tenant Authenticated Routes (Company Management & ESS)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant', 'tenant.access'])->group(function () {
    // Tenant switcher
    Route::post('/switch-tenant/{tenantId}', [TenantSwitchController::class, 'switch'])->name('tenant.switch');

    // Company Admin & HR Dashboard
    Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('company.dashboard');

    // --- PHASE 2: CORE HR ROUTES ---

    // 1. Employee Management
    Route::prefix('employees')->name('company.employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{id}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/documents', [EmployeeController::class, 'uploadDocument'])->name('documents.upload');
        Route::post('/{id}/mutations', [EmployeeController::class, 'recordMutation'])->name('mutations.record');
    });

    // 2. Organization Management
    Route::prefix('organization')->name('company.organization.')->group(function () {
        Route::get('/', [OrganizationController::class, 'index'])->name('index');
        Route::post('/departments', [OrganizationController::class, 'storeDepartment'])->name('departments.store');
        Route::delete('/departments/{id}', [OrganizationController::class, 'deleteDepartment'])->name('departments.delete');
        Route::post('/positions', [OrganizationController::class, 'storePosition'])->name('positions.store');
        Route::delete('/positions/{id}', [OrganizationController::class, 'deletePosition'])->name('positions.delete');
        Route::post('/branches', [OrganizationController::class, 'storeBranch'])->name('branches.store');
        Route::delete('/branches/{id}', [OrganizationController::class, 'deleteBranch'])->name('branches.delete');
    });

    // 3. Attendance Management
    Route::prefix('attendance')->name('company.attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/export', [AttendanceController::class, 'export'])->name('export');
        Route::get('/corrections', [AttendanceController::class, 'corrections'])->name('corrections');
        Route::post('/corrections/{id}/approve', [AttendanceController::class, 'approveCorrection'])->name('corrections.approve');
        Route::post('/corrections/{id}/reject', [AttendanceController::class, 'rejectCorrection'])->name('corrections.reject');
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts');
        Route::post('/shifts', [ShiftController::class, 'storeShift'])->name('shifts.store');
        Route::delete('/shifts/{id}', [ShiftController::class, 'deleteShift'])->name('shifts.delete');
        Route::post('/holidays', [ShiftController::class, 'storeHoliday'])->name('holidays.store');
        Route::delete('/holidays/{id}', [ShiftController::class, 'deleteHoliday'])->name('holidays.delete');
    });

    // 4. Leave Management
    Route::prefix('leave')->name('company.leave.')->group(function () {
        Route::get('/', [LeaveManagementController::class, 'index'])->name('index');
        Route::post('/{id}/approve', [LeaveManagementController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LeaveManagementController::class, 'reject'])->name('reject');
        Route::post('/types', [LeaveManagementController::class, 'storeType'])->name('types.store');
        Route::delete('/types/{id}', [LeaveManagementController::class, 'deleteType'])->name('types.delete');
    });

    // 5. Overtime Management (SPL)
    Route::prefix('overtime')->name('company.overtime.')->group(function () {
        Route::get('/', [OvertimeManagementController::class, 'index'])->name('index');
        Route::post('/{id}/approve', [OvertimeManagementController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [OvertimeManagementController::class, 'reject'])->name('reject');
    });

    // --- PHASE 3: PAYROLL, TAX & COMPLIANCE ROUTES ---

    // 6. Payroll Runs
    Route::prefix('payroll')->name('company.payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::post('/', [PayrollController::class, 'store'])->name('store');
        Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show');
        Route::post('/{payroll}/process', [PayrollController::class, 'process'])->name('process');
        Route::post('/{payroll}/approve', [PayrollController::class, 'approve'])->name('approve');
        Route::post('/{payroll}/pay', [PayrollController::class, 'pay'])->name('pay');
        Route::get('/{payroll}/export', [PayrollController::class, 'export'])->name('export');
        Route::get('/payslips/{payroll}', [PayslipController::class, 'show'])->name('payslip.show');
    });

    // 7. Salary Components Builder
    Route::prefix('salary-components')->name('company.components.')->group(function () {
        Route::get('/', [SalaryComponentController::class, 'index'])->name('index');
        Route::post('/', [SalaryComponentController::class, 'store'])->name('store');
        Route::post('/assign', [SalaryComponentController::class, 'assign'])->name('assign');
        Route::delete('/{salaryComponent}', [SalaryComponentController::class, 'destroy'])->name('destroy');
    });

    // 8. THR & Religious Bonus Management (Permenaker 6/2016)
    Route::prefix('thr')->name('company.thr.')->group(function () {
        Route::get('/', [ThrController::class, 'index'])->name('index');
        Route::post('/generate', [ThrController::class, 'generate'])->name('generate');
        Route::post('/{thrRecord}/pay', [ThrController::class, 'markPaid'])->name('pay');
    });

    // 9. Tax Management & Annual Reconciliation (PPh 21 TER / 1721-A1)
    Route::prefix('tax-reconciliation')->name('company.tax.')->group(function () {
        Route::get('/', [TaxReconciliationController::class, 'index'])->name('index');
        Route::post('/reconcile', [TaxReconciliationController::class, 'reconcile'])->name('reconcile');
        Route::get('/{reconciliation}', [TaxReconciliationController::class, 'show'])->name('show');
    });

    // 10. Reimbursement & Expense Claims
    Route::prefix('reimbursements')->name('company.reimbursements.')->group(function () {
        Route::get('/', [ReimbursementController::class, 'index'])->name('index');
        Route::post('/', [ReimbursementController::class, 'store'])->name('store');
        Route::post('/{reimbursement}/approve-manager', [ReimbursementController::class, 'approveManager'])->name('approve-manager');
        Route::post('/{reimbursement}/approve-finance', [ReimbursementController::class, 'approveFinance'])->name('approve-finance');
        Route::post('/{reimbursement}/reject', [ReimbursementController::class, 'reject'])->name('reject');
        Route::post('/{reimbursement}/pay', [ReimbursementController::class, 'pay'])->name('pay');
    });

    // 11. Employment Contracts & PKWT Tracker (PP 35/2021)
    Route::prefix('contracts')->name('company.contracts.')->group(function () {
        Route::get('/', [ContractManagementController::class, 'index'])->name('index');
        Route::post('/', [ContractManagementController::class, 'store'])->name('store');
        Route::post('/{contract}/renew', [ContractManagementController::class, 'renew'])->name('renew');
        Route::post('/{contract}/mark-compensation', [ContractManagementController::class, 'markCompensation'])->name('mark-compensation');
    });

    // --- PHASE 4: TALENT MANAGEMENT ROUTES ---

    // 12. Recruitment (Job Postings & Candidate Pipeline)
    Route::prefix('recruitment')->name('company.recruitment.')->group(function () {
        Route::get('/', [RecruitmentController::class, 'index'])->name('index');
        Route::post('/', [RecruitmentController::class, 'store'])->name('store');
        Route::get('/{jobPosting}', [RecruitmentController::class, 'show'])->name('show');
        Route::post('/{jobPosting}/apply', [RecruitmentController::class, 'applyCandidate'])->name('apply');
        Route::post('/applications/{application}/stage', [RecruitmentController::class, 'updateStage'])->name('stage.update');
        Route::post('/applications/{application}/convert', [RecruitmentController::class, 'convertToEmployee'])->name('convert');
    });

    // 13. Onboarding, Offboarding & Assets
    Route::prefix('onboarding')->name('company.onboarding.')->group(function () {
        Route::get('/', [OnboardingOffboardingController::class, 'index'])->name('index');
        Route::post('/initialize', [OnboardingOffboardingController::class, 'initialize'])->name('initialize');
        Route::post('/tasks/{task}/complete', [OnboardingOffboardingController::class, 'completeTask'])->name('task.complete');
        Route::post('/assets', [OnboardingOffboardingController::class, 'storeAsset'])->name('assets.store');
        Route::post('/assets/{asset}/assign', [OnboardingOffboardingController::class, 'assignAsset'])->name('assets.assign');
        Route::post('/assets/{asset}/return', [OnboardingOffboardingController::class, 'returnAsset'])->name('assets.return');
        Route::post('/exit-interview', [OnboardingOffboardingController::class, 'recordExitInterview'])->name('exit-interview.store');
    });

    // 14. Performance Management (KPI / OKR & Appraisals)
    Route::prefix('performance')->name('company.performance.')->group(function () {
        Route::get('/', [PerformanceController::class, 'index'])->name('index');
        Route::post('/cycles', [PerformanceController::class, 'storeCycle'])->name('cycles.store');
        Route::post('/goals', [PerformanceController::class, 'storeGoal'])->name('goals.store');
        Route::post('/goals/{goal}/progress', [PerformanceController::class, 'updateProgress'])->name('goals.progress');
        Route::post('/appraisal', [PerformanceController::class, 'submitManagerAppraisal'])->name('appraisal.submit');
    });

    // 15. Learning & Development (Training)
    Route::prefix('training')->name('company.training.')->group(function () {
        Route::get('/', [TrainingController::class, 'index'])->name('index');
        Route::post('/', [TrainingController::class, 'store'])->name('store');
        Route::post('/{course}/enroll', [TrainingController::class, 'enroll'])->name('enroll');
        Route::post('/enrollments/{enrollment}/complete', [TrainingController::class, 'complete'])->name('complete');
    });

    // 16. Employee Self-Service (ESS)
    Route::prefix('ess')->name('ess.')->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        Route::post('/clock', [EmployeeDashboardController::class, 'clock'])->name('clock');
        Route::post('/leave', [EmployeeDashboardController::class, 'submitLeave'])->name('leave.submit');
        Route::post('/corrections', [EmployeeDashboardController::class, 'submitCorrection'])->name('correction.submit');
        Route::post('/overtime', [EmployeeDashboardController::class, 'submitOvertime'])->name('overtime.submit');
        Route::post('/reimbursement', [EmployeeDashboardController::class, 'submitReimbursement'])->name('reimbursement.submit');
        Route::post('/self-appraisal', [EmployeeDashboardController::class, 'submitSelfAppraisal'])->name('self-appraisal.submit');
        Route::post('/kpi-progress/{goal}', [EmployeeDashboardController::class, 'updateKpiProgress'])->name('kpi-progress.update');
        Route::post('/profile', [EmployeeDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/grievance', [EmployeeDashboardController::class, 'submitGrievance'])->name('grievance.submit');
        Route::post('/helpdesk', [EmployeeDashboardController::class, 'submitHelpdeskTicket'])->name('helpdesk.submit');
        Route::get('/payslip/{payroll}', [PayslipController::class, 'employeePayslip'])->name('payslip.show');
    });

    // --- PHASE 5: ENTERPRISE ROUTES ---

    // 17. Workflow Engine & Multi-Level Approvals
    Route::prefix('workflows')->name('company.workflows.')->group(function () {
        Route::get('/', [WorkflowManagementController::class, 'index'])->name('index');
        Route::post('/', [WorkflowManagementController::class, 'store'])->name('store');
        Route::post('/{approvalRequest}/approve', [WorkflowManagementController::class, 'approve'])->name('approve');
        Route::post('/{approvalRequest}/reject', [WorkflowManagementController::class, 'reject'])->name('reject');
    });

    // 18. Executive HR Analytics & Reporting
    Route::prefix('analytics')->name('company.analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/export/headcount', [AnalyticsController::class, 'exportHeadcount'])->name('export.headcount');
        Route::get('/export/attendance', [AnalyticsController::class, 'exportAttendance'])->name('export.attendance');
        Route::get('/export/payroll', [AnalyticsController::class, 'exportPayroll'])->name('export.payroll');
    });

    // 19. SaaS Subscription & Billing
    Route::prefix('billing')->name('company.billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::post('/plan', [BillingController::class, 'changePlan'])->name('change-plan');
        Route::post('/invoices/{invoice}/pay', [BillingController::class, 'payInvoice'])->name('pay');
    });

    // 20. Enterprise API & Integrations (Webhooks & WhatsApp)
    Route::prefix('integrations')->name('company.integrations.')->group(function () {
        Route::get('/', [IntegrationController::class, 'index'])->name('index');
        Route::post('/api-keys', [IntegrationController::class, 'storeApiKey'])->name('api-keys.store');
        Route::post('/api-keys/{apiKey}/revoke', [IntegrationController::class, 'revokeApiKey'])->name('api-keys.revoke');
        Route::post('/webhooks', [IntegrationController::class, 'storeWebhook'])->name('webhooks.store');
        Route::post('/webhooks/{webhook}/test', [IntegrationController::class, 'testWebhook'])->name('webhooks.test');
        Route::post('/whatsapp/test', [IntegrationController::class, 'testWhatsApp'])->name('whatsapp.test');
    });

    // 21. Corporate Security & Audit Logs
    Route::prefix('security')->name('company.security.')->group(function () {
        Route::get('/', [SecurityController::class, 'index'])->name('index');
        Route::post('/ip-whitelist', [SecurityController::class, 'storeIpWhitelist'])->name('ip-whitelist.store');
        Route::delete('/ip-whitelist/{ipWhitelist}', [SecurityController::class, 'destroyIpWhitelist'])->name('ip-whitelist.destroy');
    });

    // --- PHASE 6: EMPLOYEE RELATIONS & ENGAGEMENT ROUTES ---

    // 22. Disciplinary & Surat Peringatan (SP1, SP2, SP3)
    Route::prefix('disciplinary')->name('company.disciplinary.')->group(function () {
        Route::get('/', [DisciplinaryController::class, 'index'])->name('index');
        Route::post('/', [DisciplinaryController::class, 'store'])->name('store');
        Route::post('/{warning}/revoke', [DisciplinaryController::class, 'revoke'])->name('revoke');
    });

    // 23. Employee Grievances & Whistleblowing
    Route::prefix('grievances')->name('company.grievances.')->group(function () {
        Route::get('/', [GrievanceController::class, 'index'])->name('index');
        Route::post('/{grievance}/status', [GrievanceController::class, 'updateStatus'])->name('status.update');
        Route::post('/{grievance}/resolve', [GrievanceController::class, 'resolve'])->name('resolve');
    });

    // 24. Company Announcements & Internal Bulletins
    Route::prefix('announcements')->name('company.announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store');
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
    });

    // 25. HR Helpdesk & Internal Service Requests
    Route::prefix('helpdesk')->name('company.helpdesk.')->group(function () {
        Route::get('/', [HelpdeskController::class, 'index'])->name('index');
        Route::post('/{ticket}/resolve', [HelpdeskController::class, 'resolve'])->name('resolve');
    });
});
