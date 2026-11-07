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
use App\Http\Controllers\Company\MutationController;
use App\Http\Controllers\Company\TerminationController;
use App\Http\Controllers\Company\LoanController;
use App\Http\Controllers\Company\EngagementController;
use App\Http\Controllers\Company\SkillMatrixController;
use App\Http\Controllers\Company\SuccessionController;
use App\Http\Controllers\Company\CronMonitoringController;
use App\Http\Controllers\Company\Appraisal360Controller;
use App\Http\Controllers\Company\BankDisbursalController;
use App\Http\Controllers\Company\ComplianceGatewayController;
use App\Http\Controllers\Company\QrKioskController;
use App\Http\Controllers\Company\WageScaleController;
use App\Http\Controllers\Company\BusinessTripController;
use App\Http\Controllers\Company\OffboardingClearanceController;
use App\Http\Controllers\Company\TaxEbupotController;
use App\Http\Controllers\Company\OrgChartController;
use App\Http\Controllers\Company\DigitalContractController;
use App\Http\Controllers\Company\ProbationEvaluationController;
use App\Http\Controllers\Company\PaymentGatewayController;
use App\Http\Controllers\Company\KirkpatrickTrainingController;
use App\Http\Controllers\Company\FwaController;
use App\Http\Controllers\NotificationController;

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

// Phase 13: Leave Tenant Impersonation (Available to any authenticated session under impersonation)
Route::post('/super-admin/impersonate/leave', [App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'leave'])
    ->name('super.admin.impersonate.leave')
    ->middleware('auth');

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
    Route::post('/companies/{id}/update-plan', [CompanyManagementController::class, 'updatePlan'])->name('companies.update-plan');
    Route::post('/impersonate/{tenantId}', [App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'start'])->name('impersonate.start');

    // Platform Health & Financial Diagnostics (Phase 13)
    Route::get('/health', [App\Http\Controllers\SuperAdmin\PlatformHealthController::class, 'index'])->name('health.index');

    // SaaS Subscription Plans (Phase 13)
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [App\Http\Controllers\SuperAdmin\SubscriptionPlanController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\SuperAdmin\SubscriptionPlanController::class, 'store'])->name('store');
        Route::put('/{plan}', [App\Http\Controllers\SuperAdmin\SubscriptionPlanController::class, 'update'])->name('update');
        Route::post('/{plan}/toggle', [App\Http\Controllers\SuperAdmin\SubscriptionPlanController::class, 'toggle'])->name('toggle');
    });

    // Inbound Leads CRM (Phase 13)
    Route::prefix('cms/leads')->name('cms.leads.')->group(function () {
        Route::get('/', [App\Http\Controllers\SuperAdmin\CmsLeadController::class, 'index'])->name('index');
        Route::put('/{lead}', [App\Http\Controllers\SuperAdmin\CmsLeadController::class, 'updateStatus'])->name('update');
    });

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
        Route::post('/loan', [EmployeeDashboardController::class, 'applyLoan'])->name('loan.apply');
        Route::post('/surveys/{survey}/submit', [EmployeeDashboardController::class, 'submitSurvey'])->name('surveys.submit');
        Route::post('/360/{feedback}', [EmployeeDashboardController::class, 'submit360Feedback'])->name('360.submit');
        Route::post('/attendance/qr', [EmployeeDashboardController::class, 'clockWithQr'])->name('attendance.qr');
        Route::post('/profile/family', [EmployeeDashboardController::class, 'addEssFamily'])->name('profile.family');
        Route::post('/profile/education', [EmployeeDashboardController::class, 'addEssEducation'])->name('profile.education');
        Route::post('/profile/history', [EmployeeDashboardController::class, 'addEssEmploymentHistory'])->name('profile.history');
        Route::post('/profile/emergency', [EmployeeDashboardController::class, 'addEssEmergencyContact'])->name('profile.emergency');
        Route::post('/business-trip', [EmployeeDashboardController::class, 'submitEssBusinessTrip'])->name('business-trip.submit');
        Route::post('/business-trip/{order}/expense', [EmployeeDashboardController::class, 'addEssBusinessTripExpense'])->name('business-trip.expense');
        Route::get('/form1721a1/{record}', [EmployeeDashboardController::class, 'viewEssForm1721A1'])->name('form1721a1.show');
        Route::get('/paklaring/{letter}', [EmployeeDashboardController::class, 'viewEssPaklaring'])->name('paklaring.show');
        Route::post('/contracts/{contract}/sign', [EmployeeDashboardController::class, 'signEssContract'])->name('contract.sign');
        Route::get('/certificates/{certificate}', [EmployeeDashboardController::class, 'viewEssCertificate'])->name('certificate.show');
        Route::post('/fwa', [EmployeeDashboardController::class, 'requestEssFwa'])->name('fwa.request');
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

    // --- PHASE 7: CAREER TRANSITIONS, TERMINATIONS & NOTIFICATIONS ---

    // 26. Employee Mutations, Promotions & Demotions
    Route::prefix('mutations')->name('company.mutations.')->group(function () {
        Route::get('/', [MutationController::class, 'index'])->name('index');
        Route::post('/', [MutationController::class, 'store'])->name('store');
    });

    // 27. Employee Terminations & Statutory Severance (PP 35/2021)
    Route::prefix('terminations')->name('company.terminations.')->group(function () {
        Route::get('/', [TerminationController::class, 'index'])->name('index');
        Route::post('/calculate', [TerminationController::class, 'calculate'])->name('calculate');
        Route::post('/', [TerminationController::class, 'store'])->name('store');
        Route::post('/{termination}/pay', [TerminationController::class, 'markPaid'])->name('pay');
    });

    // 28. In-App Notification Center
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    // --- PHASE 8: FINANCIAL WELL-BEING & ADVANCED ENGAGEMENT ---

    // 29. Employee Loans & Kasbon
    Route::prefix('loans')->name('company.loans.')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('index');
        Route::post('/', [LoanController::class, 'store'])->name('store');
        Route::post('/{loan}/approve', [LoanController::class, 'approve'])->name('approve');
        Route::post('/installments/{installment}/pay', [LoanController::class, 'payInstallment'])->name('installments.pay');
    });

    // 30. Engagement: Surveys, Recognitions (Wall of Fame) & Events
    Route::prefix('engagement')->name('company.engagement.')->group(function () {
        Route::get('/', [EngagementController::class, 'index'])->name('index');
        Route::post('/surveys', [EngagementController::class, 'storeSurvey'])->name('surveys.store');
        Route::post('/recognitions', [EngagementController::class, 'storeRecognition'])->name('recognitions.store');
        Route::post('/events', [EngagementController::class, 'storeEvent'])->name('events.store');
    });

    // --- PHASE 9: COMPETENCY SKILL MATRIX, SUCCESSION & CRON SCHEDULER ---

    // 31. Competency Dictionary, Position Requirements & Employee Skill Matrix
    Route::prefix('skills')->name('company.skills.')->group(function () {
        Route::get('/', [SkillMatrixController::class, 'index'])->name('index');
        Route::post('/categories', [SkillMatrixController::class, 'storeCategory'])->name('categories.store');
        Route::post('/skills', [SkillMatrixController::class, 'storeSkill'])->name('skills.store');
        Route::post('/requirements', [SkillMatrixController::class, 'storePositionRequirement'])->name('requirements.store');
        Route::delete('/requirements/{id}', [SkillMatrixController::class, 'deletePositionRequirement'])->name('requirements.delete');
        Route::post('/assess', [SkillMatrixController::class, 'storeEmployeeSkill'])->name('assess.store');
        Route::get('/gap/{employee}', [SkillMatrixController::class, 'gapAnalysis'])->name('gap');
    });

    // 32. Key Role Succession Planning & Talent Pool
    Route::prefix('succession')->name('company.succession.')->group(function () {
        Route::get('/', [SuccessionController::class, 'index'])->name('index');
        Route::post('/plans', [SuccessionController::class, 'storePlan'])->name('plans.store');
        Route::post('/candidates', [SuccessionController::class, 'storeCandidate'])->name('candidates.store');
        Route::get('/suggest/{position}', [SuccessionController::class, 'suggestCandidates'])->name('suggest');
    });

    // 33. Automated Background Cron Monitoring & Manual Trigger
    Route::prefix('cron')->name('company.cron.')->group(function () {
        Route::get('/', [CronMonitoringController::class, 'index'])->name('index');
        Route::post('/run', [CronMonitoringController::class, 'runCommand'])->name('run');
    });

    // --- PHASE 10: ADVANCED TALENT APPRAISALS, EXTENDED LIFECYCLE PROFILE & STATUTORY COMPLIANCE ---

    // 34. 360-Degree Performance Feedback & PIP
    Route::prefix('appraisals360')->name('company.appraisals360.')->group(function () {
        Route::get('/', [Appraisal360Controller::class, 'index'])->name('index');
        Route::post('/requests', [Appraisal360Controller::class, 'storeFeedbackRequest'])->name('requests.store');
        Route::post('/pips', [Appraisal360Controller::class, 'storePip'])->name('pips.store');
        Route::post('/pips/{pip}/notes', [Appraisal360Controller::class, 'updatePipNotes'])->name('pips.notes');
        Route::post('/pips/{pip}/resolve', [Appraisal360Controller::class, 'resolvePip'])->name('pips.resolve');
        Route::get('/summary/{employee}', [Appraisal360Controller::class, 'showSummary'])->name('summary');
    });

    // 35. Indonesian Banking Mass Disbursal
    Route::prefix('disbursal')->name('company.disbursal.')->group(function () {
        Route::get('/', [BankDisbursalController::class, 'index'])->name('index');
        Route::post('/generate', [BankDisbursalController::class, 'generate'])->name('generate');
        Route::get('/download/{batch}', [BankDisbursalController::class, 'download'])->name('download');
    });

    // 36. Regulatory Compliance Gateway (UMK & BPJS Statutory Filings)
    Route::prefix('compliance')->name('company.compliance.')->group(function () {
        Route::get('/', [ComplianceGatewayController::class, 'index'])->name('index');
        Route::post('/umk', [ComplianceGatewayController::class, 'storeUmk'])->name('umk.store');
        Route::post('/bpjs/generate', [ComplianceGatewayController::class, 'generateBpjsReport'])->name('bpjs.generate');
        Route::get('/bpjs/download/{report}', [ComplianceGatewayController::class, 'downloadBpjsCsv'])->name('bpjs.download');
    });

    // 37. Branch QR Attendance Kiosks
    Route::prefix('qr')->name('company.qr.')->group(function () {
        Route::get('/', [QrKioskController::class, 'index'])->name('index');
        Route::post('/', [QrKioskController::class, 'store'])->name('store');
        Route::post('/{kiosk}/refresh', [QrKioskController::class, 'refresh'])->name('refresh');
        Route::get('/display/{kiosk}', [QrKioskController::class, 'display'])->name('display');
    });

    // 38. Extended Employee Lifecycle Profile Management
    Route::prefix('employees/{id}')->name('company.employees.')->group(function () {
        Route::post('/family', [EmployeeController::class, 'addFamilyMember'])->name('family.store');
        Route::delete('/family/{familyId}', [EmployeeController::class, 'deleteFamilyMember'])->name('family.delete');
        Route::post('/education', [EmployeeController::class, 'addEducation'])->name('education.store');
        Route::delete('/education/{eduId}', [EmployeeController::class, 'deleteEducation'])->name('education.delete');
        Route::post('/history', [EmployeeController::class, 'addEmploymentHistory'])->name('history.store');
        Route::delete('/history/{historyId}', [EmployeeController::class, 'deleteEmploymentHistory'])->name('history.delete');
        Route::post('/emergency', [EmployeeController::class, 'addEmergencyContact'])->name('emergency.store');
        Route::delete('/emergency/{contactId}', [EmployeeController::class, 'deleteEmergencyContact'])->name('emergency.delete');
        Route::post('/supervisor', [EmployeeController::class, 'assignSupervisor'])->name('supervisor.assign');
    });

    // --- PHASE 11: WAGE SCALES (PERMENAKER 1/2017), SPPD BUSINESS TRAVEL, CLEARANCE OFBOARDING & E-BUPOT 21/26 ---

    // 39. Struktur & Skala Upah (Permenaker No. 1 Tahun 2017)
    Route::prefix('wage-scales')->name('company.wage-scales.')->group(function () {
        Route::get('/', [WageScaleController::class, 'index'])->name('index');
        Route::post('/grades', [WageScaleController::class, 'storeGrade'])->name('grades.store');
        Route::post('/assign/{employee}', [WageScaleController::class, 'assignEmployee'])->name('assign');
        Route::get('/export', [WageScaleController::class, 'exportAudit'])->name('export');
    });

    // 40. SPPD (Surat Perintah Perjalanan Dinas) & Settlement
    Route::prefix('business-trips')->name('company.business-trips.')->group(function () {
        Route::get('/', [BusinessTripController::class, 'index'])->name('index');
        Route::post('/', [BusinessTripController::class, 'store'])->name('store');
        Route::get('/{order}', [BusinessTripController::class, 'show'])->name('show');
        Route::get('/{order}/print', [BusinessTripController::class, 'print'])->name('print');
        Route::post('/{order}/approve', [BusinessTripController::class, 'approve'])->name('approve');
        Route::post('/advances/{advance}/disburse', [BusinessTripController::class, 'disburseAdvance'])->name('advances.disburse');
        Route::post('/{order}/expenses', [BusinessTripController::class, 'addExpense'])->name('expenses.store');
        Route::post('/{order}/settle', [BusinessTripController::class, 'settle'])->name('settle');
    });

    // 41. Offboarding Clearance Checklist & Paklaring Generator
    Route::prefix('clearances')->name('company.clearances.')->group(function () {
        Route::get('/', [OffboardingClearanceController::class, 'index'])->name('index');
        Route::post('/', [OffboardingClearanceController::class, 'store'])->name('store');
        Route::get('/{clearance}', [OffboardingClearanceController::class, 'show'])->name('show');
        Route::post('/{clearance}/signoff/{department}', [OffboardingClearanceController::class, 'signOff'])->name('signoff');
        Route::post('/{clearance}/paklaring', [OffboardingClearanceController::class, 'issuePaklaring'])->name('paklaring.issue');
        Route::get('/paklaring/{letter}/print', [OffboardingClearanceController::class, 'printPaklaring'])->name('paklaring.print');
    });

    // 42. e-Bupot 21/26 & Formulir 1721-A1 Generator (PER-2/PJ/2024)
    Route::prefix('ebupot')->name('company.taxes.ebupot.')->group(function () {
        Route::get('/', [TaxEbupotController::class, 'index'])->name('index');
        Route::post('/generate', [TaxEbupotController::class, 'generate'])->name('generate');
        Route::get('/form1721a1/{record}', [TaxEbupotController::class, 'showForm'])->name('form1721a1');
        Route::get('/export-csv', [TaxEbupotController::class, 'exportCsv'])->name('export');
    });

    // 43. Interactive Organizational Chart
    Route::get('/orgchart', [OrgChartController::class, 'index'])->name('company.orgchart.index');

    // --- PHASE 12: DIGITAL CONTRACTS & E-SIGNATURES, PROBATION EVALUATION & SK PKWTT, SAAS MIDTRANS GATEWAY, KIRKPATRICK L&D, AND FWA ---

    // 44. Digital Employment Contracts & E-Signatures (UU ITE No. 1/2024 & PP 35/2021)
    Route::prefix('digital-contracts')->name('company.digital-contracts.')->group(function () {
        Route::get('/', [DigitalContractController::class, 'index'])->name('index');
        Route::post('/', [DigitalContractController::class, 'store'])->name('store');
        Route::get('/{contract}', [DigitalContractController::class, 'show'])->name('show');
        Route::post('/{contract}/sign-company', [DigitalContractController::class, 'signCompany'])->name('sign-company');
        Route::get('/{contract}/print', [DigitalContractController::class, 'printContract'])->name('print');
    });

    // 45. Masa Percobaan & Evaluasi Karyawan Tetap (Pasal 60 UU Ketenagakerjaan No. 13/2003)
    Route::prefix('probation')->name('company.probation.')->group(function () {
        Route::get('/', [ProbationEvaluationController::class, 'index'])->name('index');
        Route::post('/', [ProbationEvaluationController::class, 'store'])->name('store');
        Route::get('/{evaluation}', [ProbationEvaluationController::class, 'show'])->name('show');
        Route::get('/{evaluation}/print-sk', [ProbationEvaluationController::class, 'printSk'])->name('print-sk');
    });

    // 46. SaaS Midtrans Payment Gateway & Subscription Checkout
    Route::prefix('payment-gateway')->name('company.payment-gateway.')->group(function () {
        Route::get('/checkout/{invoice}', [PaymentGatewayController::class, 'checkout'])->name('checkout');
        Route::post('/simulate-success/{transaction}', [PaymentGatewayController::class, 'simulateSuccess'])->name('simulate-success');
    });

    // 47. Kirkpatrick 4-Level Training Evaluation & Digital Certificates
    Route::prefix('kirkpatrick')->name('company.kirkpatrick.')->group(function () {
        Route::get('/', [KirkpatrickTrainingController::class, 'index'])->name('index');
        Route::post('/{enrollment}/test-scores', [KirkpatrickTrainingController::class, 'storeTestScores'])->name('test-scores.store');
        Route::post('/{enrollment}/reaction', [KirkpatrickTrainingController::class, 'storeReaction'])->name('reaction.store');
        Route::post('/{enrollment}/behavior', [KirkpatrickTrainingController::class, 'storeBehavior'])->name('behavior.store');
        Route::post('/{enrollment}/certificate', [KirkpatrickTrainingController::class, 'issueCertificate'])->name('certificate.issue');
        Route::get('/certificates/{certificate}/print', [KirkpatrickTrainingController::class, 'printCertificate'])->name('certificate.print');
    });

    // 48. Flexible Working Arrangement (FWA WFH/WFO Scheduler)
    Route::prefix('fwa')->name('company.fwa.')->group(function () {
        Route::get('/', [FwaController::class, 'index'])->name('index');
        Route::post('/', [FwaController::class, 'store'])->name('store');
        Route::post('/{schedule}/approve', [FwaController::class, 'approve'])->name('approve');
    });

    // --- PHASE 13: ADVANCED HR ANALYTICS & MULTI-CHANNEL NOTIFICATION HUB ---

    // 49. Advanced HR Analytics, Turnover Formula & Bradford Factor
    Route::prefix('analytics/advanced')->name('company.analytics.advanced.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\AdvancedHrAnalyticsController::class, 'index'])->name('index');
        Route::get('/export-csv', [App\Http\Controllers\Company\AdvancedHrAnalyticsController::class, 'exportCsv'])->name('export-csv');
    });

    // 50. Enterprise Multi-Channel Notification Hub & Template Engine
    Route::prefix('notifications/hub')->name('company.notifications.hub.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\NotificationHubController::class, 'index'])->name('index');
        Route::post('/templates', [App\Http\Controllers\Company\NotificationHubController::class, 'storeTemplate'])->name('templates.store');
        Route::post('/test', [App\Http\Controllers\Company\NotificationHubController::class, 'dispatchTest'])->name('test.dispatch');
    });

    // --- PHASE 14: K3 SAFETY & BPJS JKK, PAJAK NATURA PMK 66/2023, SPL PP 35/2021, PP/PKB, DAN LKS BIPARTIT ---

    // 51. Keselamatan dan Kesehatan Kerja (K3) & Klaim JKK BPJS Ketenagakerjaan
    Route::prefix('k3')->name('company.k3.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\K3IncidentController::class, 'index'])->name('index');
        Route::post('/incidents', [App\Http\Controllers\Company\K3IncidentController::class, 'storeIncident'])->name('incidents.store');
        Route::post('/incidents/{incident}/jkk-claim', [App\Http\Controllers\Company\K3IncidentController::class, 'fileJkkClaim'])->name('jkk-claim.store');
        Route::get('/jkk-claims/{claim}/print', [App\Http\Controllers\Company\K3IncidentController::class, 'printJkkClaim'])->name('jkk-claim.print');
        Route::post('/mcu', [App\Http\Controllers\Company\K3IncidentController::class, 'storeMcu'])->name('mcu.store');
    });

    // 52. Pajak Natura & Kenikmatan (PMK No. 66 Tahun 2023)
    Route::prefix('fringe-benefits')->name('company.fringe-benefits.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\FringeBenefitController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\FringeBenefitController::class, 'store'])->name('store');
    });

    // 53. Surat Perintah Lembur (SPL) Resmi & Batas Maksimal PP No. 35/2021
    Route::prefix('spl')->name('company.spl.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\SplOvertimeController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\SplOvertimeController::class, 'store'])->name('store');
        Route::post('/{order}/approve', [App\Http\Controllers\Company\SplOvertimeController::class, 'approve'])->name('approve');
        Route::post('/{order}/consent', [App\Http\Controllers\Company\SplOvertimeController::class, 'consent'])->name('consent');
        Route::post('/{order}/verify', [App\Http\Controllers\Company\SplOvertimeController::class, 'verify'])->name('verify');
    });

    // 54. Peraturan Perusahaan (PP) & PKB Register (UU No. 13/2003 & H-90 Renewal)
    Route::prefix('regulations')->name('company.regulations.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\CompanyRegulationController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\CompanyRegulationController::class, 'store'])->name('store');
    });

    // 55. Forum Lembaga Kerja Sama (LKS) Bipartit (Permenaker No. 32/2008)
    Route::prefix('bipartite')->name('company.bipartite.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\BipartiteController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\BipartiteController::class, 'store'])->name('store');
        Route::post('/{meeting}/report-disnaker', [App\Http\Controllers\Company\BipartiteController::class, 'reportDisnaker'])->name('report-disnaker');
    });

    // --- PHASE 15: TALENT RETENTION, IJP, ONBOARDING MENTORSHIP, ASSET HANDOVER BAST & WLTK STATUTORY REPORTING ---

    // 56. Wajib Lapor Ketenagakerjaan Perusahaan (WLTK / WLKP UU No. 7/1981 & Permenaker 18/2017)
    Route::prefix('wltk')->name('company.wltk.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\WltkComplianceController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\WltkComplianceController::class, 'store'])->name('store');
        Route::get('/{report}', [App\Http\Controllers\Company\WltkComplianceController::class, 'show'])->name('show');
        Route::post('/{report}/receipt', [App\Http\Controllers\Company\WltkComplianceController::class, 'submitReceipt'])->name('submit-receipt');
        Route::get('/{report}/export-json', [App\Http\Controllers\Company\WltkComplianceController::class, 'exportJson'])->name('export-json');
        Route::get('/{report}/export-csv', [App\Http\Controllers\Company\WltkComplianceController::class, 'exportCsv'])->name('export-csv');
    });

    // 57. Internal Job Posting (IJP) & Talent Mobility Marketplace
    Route::prefix('ijp')->name('company.ijp.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\IjpMarketplaceController::class, 'index'])->name('index');
        Route::post('/vacancies', [App\Http\Controllers\Company\IjpMarketplaceController::class, 'storeVacancy'])->name('vacancies.store');
        Route::post('/vacancies/{vacancy}/apply', [App\Http\Controllers\Company\IjpMarketplaceController::class, 'apply'])->name('apply');
        Route::post('/applications/{application}/approve-manager', [App\Http\Controllers\Company\IjpMarketplaceController::class, 'approveManager'])->name('approve-manager');
        Route::post('/applications/{application}/decide-hr', [App\Http\Controllers\Company\IjpMarketplaceController::class, 'decideHr'])->name('decide-hr');
    });

    // 58. Onboarding Buddy & Mentorship Program
    Route::prefix('mentorship')->name('company.mentorship.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\MentorshipController::class, 'index'])->name('index');
        Route::post('/pairs', [App\Http\Controllers\Company\MentorshipController::class, 'storePair'])->name('pairs.store');
        Route::post('/checkins/{checkin}/complete', [App\Http\Controllers\Company\MentorshipController::class, 'completeCheckin'])->name('checkins.complete');
    });

    // 59. Exit Asset Handover BAST & Automated SSO Revocation
    Route::prefix('asset-handover')->name('company.asset-handover.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\AssetHandoverController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\AssetHandoverController::class, 'storeBast'])->name('store');
        Route::get('/{bast}', [App\Http\Controllers\Company\AssetHandoverController::class, 'show'])->name('show');
        Route::post('/{bast}/sign', [App\Http\Controllers\Company\AssetHandoverController::class, 'signBast'])->name('sign');
        Route::get('/{bast}/print', [App\Http\Controllers\Company\AssetHandoverController::class, 'printBast'])->name('print');
        Route::post('/revoke-access', [App\Http\Controllers\Company\AssetHandoverController::class, 'revokeAccess'])->name('revoke-access');
    });

    // 60. Audit Kepatuhan Kuota Tenaga Kerja Disabilitas (UU No. 8/2016 Pasal 53)
    Route::prefix('disability-audit')->name('company.disability-audit.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\DisabilityAuditController::class, 'index'])->name('index');
        Route::post('/run', [App\Http\Controllers\Company\DisabilityAuditController::class, 'runAudit'])->name('run');
        Route::post('/profiles', [App\Http\Controllers\Company\DisabilityAuditController::class, 'storeProfile'])->name('profiles.store');
        Route::get('/{audit}/print', [App\Http\Controllers\Company\DisabilityAuditController::class, 'printReport'])->name('print');
    });

    // --- PHASE 16: TKA & RPTKA, JKP BPJS, KOPERASI KARYAWAN, CONTINUOUS SHIFT ROSTER, DAN PPHI MEDIASI ---

    // 61. Manajemen Tenaga Kerja Asing (TKA) & RPTKA (PP No. 34/2021)
    Route::prefix('expatriates')->name('company.expatriates.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\ExpatriatePermitController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\ExpatriatePermitController::class, 'store'])->name('store');
        Route::get('/{permit}', [App\Http\Controllers\Company\ExpatriatePermitController::class, 'show'])->name('show');
        Route::post('/{permit}/status', [App\Http\Controllers\Company\ExpatriatePermitController::class, 'updateStatus'])->name('update-status');
    });

    // 62. Jaminan Kehilangan Pekerjaan (JKP BPJS TK & PP No. 37/2021)
    Route::prefix('jkp')->name('company.jkp.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\JkpClaimController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\JkpClaimController::class, 'store'])->name('store');
        Route::get('/{certification}', [App\Http\Controllers\Company\JkpClaimController::class, 'show'])->name('show');
        Route::get('/{certification}/print', [App\Http\Controllers\Company\JkpClaimController::class, 'printRecommendation'])->name('print');
    });

    // 63. Koperasi Karyawan & Simpan Pinjam (UU No. 25/1992 & UU 13/2003 Pasal 100)
    Route::prefix('cooperative')->name('company.cooperative.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\EmployeeCooperativeController::class, 'index'])->name('index');
        Route::post('/members', [App\Http\Controllers\Company\EmployeeCooperativeController::class, 'storeMember'])->name('members.store');
        Route::post('/transactions', [App\Http\Controllers\Company\EmployeeCooperativeController::class, 'storeTransaction'])->name('transactions.store');
        Route::post('/payroll-deductions', [App\Http\Controllers\Company\EmployeeCooperativeController::class, 'runPayrollDeduction'])->name('payroll-deductions.run');
    });

    // 64. Pola Shift Khusus 24/7 & Roster (Kepmenakertrans No. KEP-233/MEN/2003)
    Route::prefix('rosters')->name('company.rosters.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\ContinuousShiftRosterController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\ContinuousShiftRosterController::class, 'store'])->name('store');
        Route::post('/assign-group', [App\Http\Controllers\Company\ContinuousShiftRosterController::class, 'assignGroup'])->name('assign-group');
    });

    // 65. Penyelesaian Perselisihan Hubungan Industrial / PPHI (UU No. 2/2004)
    Route::prefix('disputes')->name('company.disputes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\TripartiteDisputeController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Company\TripartiteDisputeController::class, 'store'])->name('store');
        Route::post('/{dispute}/recommendation', [App\Http\Controllers\Company\TripartiteDisputeController::class, 'recordRecommendation'])->name('recommendation.store');
        Route::post('/{dispute}/response', [App\Http\Controllers\Company\TripartiteDisputeController::class, 'recordResponse'])->name('response.store');
        Route::get('/{dispute}/print', [App\Http\Controllers\Company\TripartiteDisputeController::class, 'printDispute'])->name('print');
    });

    // --- PHASE 17: OUTSOURCING, PKB, SATGAS PPKS, VSS & PEMAGANGAN ---

    // 66. Rekanan Alih Daya / Outsourcing (PP No. 35/2021 BAB IV & TUPE)
    Route::prefix('outsourcing')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\OutsourcingVendorController::class, 'index'])->name('outsourcing.index');
        Route::post('/', [App\Http\Controllers\Company\OutsourcingVendorController::class, 'store'])->name('outsourcing.store');
        Route::get('/{vendor}', [App\Http\Controllers\Company\OutsourcingVendorController::class, 'show'])->name('outsourcing.show');
        Route::post('/{vendor}/contracts', [App\Http\Controllers\Company\OutsourcingVendorController::class, 'storeContract'])->name('outsourcing.contracts.store');
    });

    // 67. Perjanjian Kerja Bersama (PKB) & Serikat Pekerja (UU 21/2000 & Permenaker 28/2014)
    Route::prefix('unions')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\LaborUnionController::class, 'index'])->name('unions.index');
        Route::post('/', [App\Http\Controllers\Company\LaborUnionController::class, 'store'])->name('unions.store');
        Route::post('/{union}/pkb', [App\Http\Controllers\Company\LaborUnionController::class, 'storePkb'])->name('unions.pkb.store');
        Route::post('/{union}/checkoff', [App\Http\Controllers\Company\LaborUnionController::class, 'storeCheckoff'])->name('unions.checkoff.store');
    });

    // 68. Satgas PPKS & Fasilitas Ramah Perempuan (Kepmenaker 88/2023 & UU 12/2022)
    Route::prefix('ppks')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\PpksProtectionController::class, 'index'])->name('ppks.index');
        Route::post('/', [App\Http\Controllers\Company\PpksProtectionController::class, 'store'])->name('ppks.store');
        Route::post('/{case}/update-status', [App\Http\Controllers\Company\PpksProtectionController::class, 'updateStatus'])->name('ppks.update-status');
        Route::post('/audit-facility', [App\Http\Controllers\Company\PpksProtectionController::class, 'auditFacility'])->name('ppks.audit-facility');
    });

    // 69. Voluntary Separation Scheme (VSS / Pensiun Dini) & DPLK (UU 4/2023 P2SK)
    Route::prefix('vss')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\VoluntarySeparationController::class, 'index'])->name('vss.index');
        Route::post('/', [App\Http\Controllers\Company\VoluntarySeparationController::class, 'store'])->name('vss.store');
        Route::post('/{program}/apply', [App\Http\Controllers\Company\VoluntarySeparationController::class, 'apply'])->name('vss.apply');
        Route::post('/applications/{application}/approve', [App\Http\Controllers\Company\VoluntarySeparationController::class, 'approve'])->name('vss.approve');
        Route::post('/dplk', [App\Http\Controllers\Company\VoluntarySeparationController::class, 'storeDplk'])->name('vss.dplk.store');
    });

    // 70. Program Pemagangan Dalam Negeri (Permenaker No. 6/2020)
    Route::prefix('internships')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\InternshipProgramController::class, 'index'])->name('internships.index');
        Route::post('/', [App\Http\Controllers\Company\InternshipProgramController::class, 'store'])->name('internships.store');
        Route::get('/{internship}', [App\Http\Controllers\Company\InternshipProgramController::class, 'show'])->name('internships.show');
        Route::post('/{internship}/logbooks', [App\Http\Controllers\Company\InternshipProgramController::class, 'storeLogbook'])->name('internships.logbooks.store');
        Route::post('/logbooks/{logbook}/verify', [App\Http\Controllers\Company\InternshipProgramController::class, 'verifyLogbook'])->name('internships.logbooks.verify');
        Route::get('/{internship}/print-certificate', [App\Http\Controllers\Company\InternshipProgramController::class, 'printCertificate'])->name('internships.print-certificate');
    });

    // --- PHASE 18: FLEX BENEFITS, STATUTORY THR, ESOP, COB & P2K3 ---

    // 71. Cafeteria Plan / Flexible Benefits & Poin Plafon Karyawan
    Route::prefix('flex-benefits')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\FlexBenefitController::class, 'index'])->name('flex-benefits.index');
        Route::post('/categories', [App\Http\Controllers\Company\FlexBenefitController::class, 'storeCategory'])->name('flex-benefits.categories.store');
        Route::post('/allocations', [App\Http\Controllers\Company\FlexBenefitController::class, 'storeAllocation'])->name('flex-benefits.allocations.store');
        Route::post('/claims', [App\Http\Controllers\Company\FlexBenefitController::class, 'storeClaim'])->name('flex-benefits.claims.store');
        Route::post('/claims/{claim}/approve', [App\Http\Controllers\Company\FlexBenefitController::class, 'approveClaim'])->name('flex-benefits.claims.approve');
        Route::post('/claims/{claim}/reject', [App\Http\Controllers\Company\FlexBenefitController::class, 'rejectClaim'])->name('flex-benefits.claims.reject');
    });

    // 72. Tunjangan Hari Raya (THR) Statutori & Denda H-7 (Permenaker No. 6/2016 jo. PP 58/2023)
    Route::prefix('thr-disbursements')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\ThrDisbursementController::class, 'index'])->name('thr-disbursements.index');
        Route::post('/calculate', [App\Http\Controllers\Company\ThrDisbursementController::class, 'calculate'])->name('thr-disbursements.calculate');
        Route::post('/{disbursement}/mark-paid', [App\Http\Controllers\Company\ThrDisbursementController::class, 'markPaid'])->name('thr-disbursements.mark-paid');
        Route::get('/{disbursement}/print-slip', [App\Http\Controllers\Company\ThrDisbursementController::class, 'printSlip'])->name('thr-disbursements.print-slip');
    });

    // 73. Employee Stock Option Plan (ESOP / RSU) & Vesting 4 Tahun (UU No. 40/2007)
    Route::prefix('esop')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\EsopGrantController::class, 'index'])->name('esop.index');
        Route::post('/', [App\Http\Controllers\Company\EsopGrantController::class, 'store'])->name('esop.store');
        Route::post('/{grant}/refresh-vesting', [App\Http\Controllers\Company\EsopGrantController::class, 'refreshVesting'])->name('esop.refresh-vesting');
        Route::post('/{grant}/exercise', [App\Http\Controllers\Company\EsopGrantController::class, 'exercise'])->name('esop.exercise');
    });

    // 74. Asuransi Kesehatan Komersial & CoB BPJS Kesehatan (Perpres 82/2018)
    Route::prefix('cob-insurance')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\CobInsuranceController::class, 'index'])->name('cob-insurance.index');
        Route::post('/', [App\Http\Controllers\Company\CobInsuranceController::class, 'store'])->name('cob-insurance.store');
        Route::post('/coordinate-claim', [App\Http\Controllers\Company\CobInsuranceController::class, 'coordinateClaim'])->name('cob-insurance.coordinate-claim');
    });

    // 75. Panitia Pembina Keselamatan & Kesehatan Kerja (P2K3 Disnaker Permenaker No. 04/1987)
    Route::prefix('p2k3')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\P2k3CommitteeController::class, 'index'])->name('p2k3.index');
        Route::post('/committee', [App\Http\Controllers\Company\P2k3CommitteeController::class, 'storeCommittee'])->name('p2k3.committee.store');
        Route::post('/reports', [App\Http\Controllers\Company\P2k3CommitteeController::class, 'storeQuarterlyReport'])->name('p2k3.reports.store');
        Route::get('/reports/{report}/print', [App\Http\Controllers\Company\P2k3CommitteeController::class, 'printReport'])->name('p2k3.reports.print');
    });

    // --- PHASE 19: ESG SUSTAINABILITY, COMMUTER CARBON & ERGONOMICS ---

    // 76. ESG Sustainability Reporting Ketenagakerjaan (POJK No. 51/2017 & GRI Standards)
    Route::prefix('esg')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\EsgReportingController::class, 'index'])->name('esg.index');
        Route::post('/generate', [App\Http\Controllers\Company\EsgReportingController::class, 'generateSnapshot'])->name('esg.generate');
        Route::get('/{snapshot}/print', [App\Http\Controllers\Company\EsgReportingController::class, 'printReport'])->name('esg.print');
    });

    // 77. Scope 3 GHG Commuter Emissions (GHG Protocol & Penghematan FWA/WFH)
    Route::prefix('carbon-commute')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\CarbonCommuteController::class, 'index'])->name('carbon-commute.index');
        Route::post('/profiles', [App\Http\Controllers\Company\CarbonCommuteController::class, 'storeProfile'])->name('carbon-commute.profiles.store');
        Route::post('/audit', [App\Http\Controllers\Company\CarbonCommuteController::class, 'runAudit'])->name('carbon-commute.audit.run');
    });

    // 78. Ergonomi Tempat Kerja & Beban Kerja Digital (Permenaker No. 5/2018)
    Route::prefix('ergonomics')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\ErgonomicStressController::class, 'index'])->name('ergonomics.index');
        Route::post('/assessments', [App\Http\Controllers\Company\ErgonomicStressController::class, 'storeErgonomic'])->name('ergonomics.assessments.store');
        Route::post('/surveys', [App\Http\Controllers\Company\ErgonomicStressController::class, 'storeStressSurvey'])->name('ergonomics.surveys.store');
    });

    // --- PHASE 20: DISASTER RECOVERY, MULTI-REGION FAILOVER & UU PELINDUNGAN DATA PRIBADI (PDP) ---

    // 79. Kepatuhan UU Pelindungan Data Pribadi / PDP (UU No. 27/2022 & DSAR)
    Route::prefix('pdp')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\PdpComplianceController::class, 'index'])->name('pdp.index');
        Route::post('/consents', [App\Http\Controllers\Company\PdpComplianceController::class, 'storeConsent'])->name('pdp.consents.store');
        Route::post('/consents/{consent}/revoke', [App\Http\Controllers\Company\PdpComplianceController::class, 'revokeConsent'])->name('pdp.consents.revoke');
        Route::post('/dsar', [App\Http\Controllers\Company\PdpComplianceController::class, 'storeDsar'])->name('pdp.dsar.store');
        Route::get('/dsar/{request}/export', [App\Http\Controllers\Company\PdpComplianceController::class, 'fulfillExport'])->name('pdp.dsar.export');
        Route::post('/breaches', [App\Http\Controllers\Company\PdpComplianceController::class, 'storeBreach'])->name('pdp.breaches.store');
    });

    // 80. Disaster Recovery & Multi-Region High-Availability Failover
    Route::prefix('disaster-recovery')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\DisasterRecoveryController::class, 'index'])->name('disaster-recovery.index');
        Route::post('/drills', [App\Http\Controllers\Company\DisasterRecoveryController::class, 'storeDrill'])->name('disaster-recovery.drills.store');
        Route::post('/failover', [App\Http\Controllers\Company\DisasterRecoveryController::class, 'failover'])->name('disaster-recovery.failover');
    });

    // 81. Zero Trust Security & Enkripsi Data Spesifik Karyawan (UU PDP Pasal 35)
    Route::prefix('zero-trust')->group(function () {
        Route::get('/', [App\Http\Controllers\Company\ZeroTrustSecurityController::class, 'index'])->name('zero-trust.index');
    });
});
