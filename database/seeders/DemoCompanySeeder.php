<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\LeaveType;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalaryComponent;
use App\Models\EmploymentContract;
use App\Models\Reimbursement;
use App\Models\PayrollPeriod;
use App\Models\JobPosting;
use App\Models\JobCandidate;
use App\Models\JobApplication;
use App\Models\CompanyAsset;
use App\Models\OnboardingOffboardingChecklist;
use App\Models\PerformanceCycle;
use App\Models\KpiGoal;
use App\Models\PerformanceReview;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\ApprovalRequest;
use App\Models\ApprovalHistory;
use App\Models\ApiKey;
use App\Models\WebhookSubscription;
use App\Models\CompanyIpWhitelist;
use App\Models\AuditLog;
use App\Models\SubscriptionInvoice;
use App\Services\Payroll\PayrollProcessorService;
use App\Services\Talent\OnboardingOffboardingService;
use App\Services\Workflow\WorkflowEngineService;
use App\Services\Integration\EnterpriseApiService;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Hash;

class DemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'solusi-digital'],
            [
                'name' => 'PT Solusi Digital Nusantara',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
            ]
        );

        // Bind tenant to manager during seeding
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenantManager->setCurrentTenant($tenant);

        // 2. Create Company
        $company = Company::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'PT Solusi Digital Nusantara'],
            [
                'legal_name' => 'PT Solusi Digital Nusantara',
                'npwp' => '01.234.567.8-012.000',
                'email' => 'contact@solusidigital.id',
                'phone' => '+62 21 5566 7788',
                'address' => 'Gedung Menara Mandiri Lt. 18, Jl. Gatot Subroto No. 45',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12930',
            ]
        );

        // 3. Company Settings
        CompanySetting::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'company_id' => $company->id],
            [
                'work_days_per_week' => 5,
                'regular_work_hours_per_day' => 8.00,
                'weekly_work_hour_limit' => 40.00,
                'late_tolerance_minutes' => 15,
                'bpjs_kes_max_cap' => 12000000.00,
                'bpjs_tk_jp_max_cap' => 10042300.00,
                'jkk_risk_grade' => 'low',
                'payroll_cutoff_day' => 20,
                'payroll_payment_day' => 25,
            ]
        );

        // 4. Attach Active Subscription
        $proPlan = SubscriptionPlan::where('slug', 'professional')->first();
        if ($proPlan) {
            Subscription::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'plan_id' => $proPlan->id],
                [
                    'status' => 'active',
                    'billing_cycle' => 'yearly',
                    'starts_at' => now()->subMonth(),
                    'ends_at' => now()->addYear(),
                ]
            );
        }

        // 5. Create Users (Owner, HR, Employee)
        $ownerRole = Role::where('slug', 'company-owner')->first();
        $hrRole = Role::where('slug', 'hr-manager')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        // Company Owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@solusidigital.id'],
            [
                'name' => 'Rian Hidayat (Owner)',
                'password' => Hash::make('Password123#'),
                'phone' => '+628123456701',
                'is_super_admin' => false,
                'active_tenant_id' => $tenant->id,
                'email_verified_at' => now(),
            ]
        );
        $owner->tenants()->syncWithoutDetaching([$tenant->id => ['is_owner' => true, 'role_id' => $ownerRole?->id]]);
        $owner->assignRole($ownerRole, $tenant->id);

        // HR Manager
        $hr = User::firstOrCreate(
            ['email' => 'hr@solusidigital.id'],
            [
                'name' => 'Dewi Lestari (HR Manager)',
                'password' => Hash::make('Password123#'),
                'phone' => '+628123456702',
                'is_super_admin' => false,
                'active_tenant_id' => $tenant->id,
                'email_verified_at' => now(),
            ]
        );
        $hr->tenants()->syncWithoutDetaching([$tenant->id => ['is_owner' => false, 'role_id' => $hrRole?->id]]);
        $hr->assignRole($hrRole, $tenant->id);

        // Employee User
        $empUser = User::firstOrCreate(
            ['email' => 'budi@solusidigital.id'],
            [
                'name' => 'Budi Santoso (Lead Developer)',
                'password' => Hash::make('Password123#'),
                'phone' => '+628123456703',
                'is_super_admin' => false,
                'active_tenant_id' => $tenant->id,
                'email_verified_at' => now(),
            ]
        );
        $empUser->tenants()->syncWithoutDetaching([$tenant->id => ['is_owner' => false, 'role_id' => $employeeRole?->id]]);
        $empUser->assignRole($employeeRole, $tenant->id);

        // 6. Departments & Positions
        $deptTech = Department::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Technology & Product'],
            ['company_id' => $company->id, 'code' => 'TECH-01']
        );
        $deptHr = Department::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Human Capital'],
            ['company_id' => $company->id, 'code' => 'HC-01']
        );

        $posDev = Position::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'department_id' => $deptTech->id, 'title' => 'Lead Software Engineer'],
            ['job_level' => 'Manager']
        );
        $posHr = Position::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'department_id' => $deptHr->id, 'title' => 'People Operations Specialist'],
            ['job_level' => 'Specialist']
        );

        // 7. Employees
        $employeeBudi = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'SDN-2024-001'],
            [
                'company_id' => $company->id,
                'user_id' => $empUser->id,
                'nik_ktp' => '3171012304920005',
                'first_name' => 'Budi',
                'last_name' => 'Santoso',
                'email' => 'budi@solusidigital.id',
                'phone' => '+628123456703',
                'gender' => 'male',
                'birth_place' => 'Jakarta',
                'birth_date' => '1992-04-23',
                'marital_status' => 'married',
                'tax_ptkp_status' => 'K/1', // Kategori TER B
                'npwp' => '09.876.543.2-011.000',
                'bpjs_tk_number' => '18029384756',
                'bpjs_kes_number' => '000192837465',
                'department_id' => $deptTech->id,
                'position_id' => $posDev->id,
                'employment_status' => 'pkwtt',
                'join_date' => '2023-01-15',
                'bank_name' => 'Bank Central Asia (BCA)',
                'bank_account_number' => '5420987112',
                'bank_account_holder' => 'Budi Santoso',
                'basic_salary' => 15000000.00,
                'status' => 'active',
            ]
        );

        $employeeSiti = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'SDN-2024-002'],
            [
                'company_id' => $company->id,
                'user_id' => $hr->id,
                'nik_ktp' => '3172081507950002',
                'first_name' => 'Dewi',
                'last_name' => 'Lestari',
                'email' => 'dewi@solusidigital.id',
                'phone' => '+628123456702',
                'gender' => 'female',
                'birth_place' => 'Bandung',
                'birth_date' => '1995-07-15',
                'marital_status' => 'single',
                'tax_ptkp_status' => 'TK/0', // Kategori TER A
                'npwp' => '08.765.432.1-012.000',
                'bpjs_tk_number' => '18029384757',
                'bpjs_kes_number' => '000192837466',
                'department_id' => $deptHr->id,
                'position_id' => $posHr->id,
                'employment_status' => 'pkwt',
                'join_date' => '2023-06-01',
                'end_date' => '2026-12-31',
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '1370019827364',
                'bank_account_holder' => 'Dewi Lestari',
                'basic_salary' => 9500000.00,
                'status' => 'active',
            ]
        );

        // 8. Shift
        Shift::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Shift Reguler (08:30 - 17:30)'],
            [
                'start_time' => '08:30:00',
                'end_time' => '17:30:00',
                'is_night_shift' => false,
            ]
        );

        // 9. Leave Types
        $leaveAnnual = LeaveType::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'AL'],
            ['name' => 'Cuti Tahunan', 'days_allowed' => 12, 'is_paid' => true]
        );

        LeaveType::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SL'],
            ['name' => 'Cuti Sakit', 'days_allowed' => 14, 'is_paid' => true]
        );

        LeaveType::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'ML'],
            ['name' => 'Cuti Melahirkan', 'days_allowed' => 90, 'is_paid' => true]
        );

        // 10. Sample Attendance for today
        AttendanceRecord::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeBudi->id, 'date' => now()->toDateString()],
            [
                'clock_in' => '08:24:00',
                'clock_out' => '17:32:00',
                'clock_in_latitude' => -6.2255880,
                'clock_in_longitude' => 106.8118020,
                'status' => 'present',
                'late_minutes' => 0,
                'early_out_minutes' => 0,
                'work_hours' => 8.13,
            ]
        );

        // 11. Sample Pending Leave Request
        LeaveRequest::withoutGlobalScopes()->firstOrCreate(
            [
                'tenant_id' => $tenant->id, 
                'employee_id' => $employeeBudi->id, 
                'leave_type_id' => $leaveAnnual->id,
                'start_date' => now()->addDays(5)->toDateString()
            ],
            [
                'end_date' => now()->addDays(6)->toDateString(),
                'total_days' => 2,
                'reason' => 'Keperluan keluarga di luar kota',
                'status' => 'pending',
            ]
        );

        // --- PHASE 3 SEEDING: SALARY COMPONENTS, CONTRACTS, REIMBURSEMENT, PAYROLL ---

        // 12. Salary Components Master
        $compJabatan = SalaryComponent::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Tunjangan Jabatan'],
            ['type' => 'allowance', 'is_fixed' => true, 'is_taxable' => true]
        );

        $compTransport = SalaryComponent::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Uang Transport & Makan'],
            ['type' => 'allowance', 'is_fixed' => false, 'is_taxable' => true]
        );

        $compKasbon = SalaryComponent::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Potongan Kasbon'],
            ['type' => 'deduction', 'is_fixed' => false, 'is_taxable' => false]
        );

        // Assign to Budi
        EmployeeSalaryComponent::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeBudi->id, 'salary_component_id' => $compJabatan->id],
            ['amount' => 2500000.00, 'is_active' => true]
        );
        EmployeeSalaryComponent::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeBudi->id, 'salary_component_id' => $compTransport->id],
            ['amount' => 1000000.00, 'is_active' => true]
        );

        // Assign to Dewi
        EmployeeSalaryComponent::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeSiti->id, 'salary_component_id' => $compJabatan->id],
            ['amount' => 1000000.00, 'is_active' => true]
        );
        EmployeeSalaryComponent::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeSiti->id, 'salary_component_id' => $compTransport->id],
            ['amount' => 750000.00, 'is_active' => true]
        );

        // 13. Contracts (PKWTT for Budi, PKWT for Dewi with PP 35/2021 statutory compensation)
        EmploymentContract::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'contract_number' => 'PKWTT/SDN/2023/001'],
            [
                'employee_id' => $employeeBudi->id,
                'type' => 'pkwtt',
                'start_date' => '2023-01-15',
                'end_date' => null,
                'monthly_wage' => 15000000.00,
                'status' => 'active',
                'compensation_calculated' => 0.00,
                'compensation_paid' => false,
                'renewal_count' => 0,
            ]
        );

        // Dewi: 24 months PKWT contract
        EmploymentContract::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'contract_number' => 'PKWT/SDN/2024/002'],
            [
                'employee_id' => $employeeSiti->id,
                'type' => 'pkwt',
                'start_date' => '2024-01-01',
                'end_date' => '2025-12-31',
                'monthly_wage' => 9500000.00,
                'status' => 'active',
                // (24 / 12) * 9,500,000 = 19,000,000
                'compensation_calculated' => 19000000.00,
                'compensation_paid' => false,
                'renewal_count' => 0,
            ]
        );

        // 14. Reimbursements
        Reimbursement::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeBudi->id, 'title' => 'Tiket Kereta Cepat Dinas Bandung'],
            [
                'category' => 'business_trip',
                'description' => 'Perjalanan dinas meeting teknis mitra infrastruktur server',
                'amount' => 500000.00,
                'expense_date' => now()->subDays(3)->toDateString(),
                'status' => 'approved_finance',
                'reviewed_by_manager_id' => $owner->id,
                'reviewed_by_finance_id' => $hr->id,
            ]
        );

        Reimbursement::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeSiti->id, 'title' => 'Klaim Vitamin & Obat Resep'],
            [
                'category' => 'medical',
                'description' => 'Resep dokter klinik rawat jalan',
                'amount' => 350000.00,
                'expense_date' => now()->subDays(5)->toDateString(),
                'status' => 'approved_manager',
                'reviewed_by_manager_id' => $owner->id,
            ]
        );

        // 15. Sample Payroll Period & Run
        $period = PayrollPeriod::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Gaji Bulan ' . now()->isoFormat('MMMM Y')],
            [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'payment_date' => now()->endOfMonth()->toDateString(),
                'status' => 'draft',
            ]
        );

        // Trigger payroll processor for the demo period
        /** @var PayrollProcessorService $processor */
        $processor = app(PayrollProcessorService::class);
        $processor->processPeriod($period);

        // --- PHASE 4: TALENT MANAGEMENT DEMO DATA ---

        // 16. Recruitment (Job Postings & Candidates Pipeline)
        $jobPosting = JobPosting::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Senior Full-Stack Engineer (PHP / Laravel)'],
            [
                'department_id' => $deptTech->id,
                'position_id' => $posDev->id,
                'employment_type' => 'full_time',
                'location' => 'Jakarta Selatan / Hybrid',
                'description' => 'Membangun arsitektur multi-tenant SaaS HRIS berkinerja tinggi menggunakan Laravel 11, PostgreSQL, Redis, dan Tailwind CSS.',
                'requirements' => "1. Pengalaman minimal 4 tahun PHP modern & Laravel.\n2. Pemahaman arsitektur SaaS multi-tenant.\n3. Mahir database tuning & queue processing.",
                'salary_min' => 12000000.00,
                'salary_max' => 18000000.00,
                'status' => 'published',
                'deadline' => now()->addDays(30)->toDateString(),
            ]
        );

        $cand1 = JobCandidate::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'reza.pratama@example.com'],
            [
                'first_name' => 'Reza',
                'last_name' => 'Pratama',
                'phone' => '+62 812-9988-7711',
                'linkedin_url' => 'https://linkedin.com/in/rezapratama',
                'notes' => 'Portofolio SaaS kuat, pernah memimpin tim 5 engineer.',
            ]
        );

        $cand2 = JobCandidate::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'faisal.tanjung@example.com'],
            [
                'first_name' => 'Faisal',
                'last_name' => 'Tanjung',
                'phone' => '+62 813-5544-3322',
                'linkedin_url' => 'https://linkedin.com/in/faisaltanjung',
                'notes' => 'Pengalaman FinTech & payment gateway.',
            ]
        );

        JobApplication::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'job_posting_id' => $jobPosting->id, 'job_candidate_id' => $cand1->id],
            [
                'stage' => 'interview',
                'interview_scheduled_at' => now()->addDays(2),
                'interview_score' => 88.50,
                'interview_notes' => 'Sangat menguasai database transaction & concurrency.',
            ]
        );

        JobApplication::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'job_posting_id' => $jobPosting->id, 'job_candidate_id' => $cand2->id],
            [
                'stage' => 'offered',
                'offered_salary' => 15000000.00,
                'interview_score' => 92.00,
                'interview_notes' => 'Offering letter dikirimkan per hari ini.',
            ]
        );

        // 17. Company Assets (Hardware & Office Equipment)
        CompanyAsset::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'asset_code' => 'LAPTOP-MBP-001'],
            [
                'name' => 'MacBook Pro 14 M3 Pro (18GB/512GB)',
                'category' => 'laptop',
                'serial_number' => 'C02G9988HJK1',
                'status' => 'assigned',
                'assigned_employee_id' => $employeeBudi->id,
                'assigned_date' => '2023-01-15',
                'notes' => 'Laptop operasional Tech Lead',
            ]
        );

        CompanyAsset::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'asset_code' => 'LAPTOP-DELL-002'],
            [
                'name' => 'Dell Latitude 5440 i7 (16GB/512GB)',
                'category' => 'laptop',
                'serial_number' => 'DL-998822-ID',
                'status' => 'assigned',
                'assigned_employee_id' => $employeeSiti->id,
                'assigned_date' => '2024-01-01',
                'notes' => 'Laptop operasional People Operations Specialist',
            ]
        );

        CompanyAsset::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'asset_code' => 'MON-LEN-001'],
            [
                'name' => 'Lenovo ThinkVision 27" 4K Monitor',
                'category' => 'other',
                'serial_number' => 'LV-TV27-4K01',
                'status' => 'available',
                'notes' => 'Monitor docking workstation meja engineering',
            ]
        );

        CompanyAsset::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'asset_code' => 'CARD-ACC-001'],
            [
                'name' => 'Kartu Akses Gedung & Lift RFID',
                'category' => 'access_card',
                'serial_number' => 'RFID-SDN-001',
                'status' => 'assigned',
                'assigned_employee_id' => $employeeBudi->id,
                'assigned_date' => '2023-01-15',
                'notes' => 'Akses lantai 18 Menara Mandiri',
            ]
        );

        // 18. Onboarding Checklists
        /** @var OnboardingOffboardingService $onboardingService */
        $onboardingService = app(OnboardingOffboardingService::class);
        $onboardingService->initializeOnboarding($employeeBudi, $hr);
        $onboardingService->initializeOnboarding($employeeSiti, $hr);

        // Mark Budi's onboarding tasks as completed
        OnboardingOffboardingChecklist::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('employee_id', $employeeBudi->id)
            ->update([
                'is_completed' => true,
                'completed_at' => '2023-01-20 17:00:00',
            ]);

        // Mark partial tasks for Dewi Siti
        OnboardingOffboardingChecklist::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('employee_id', $employeeSiti->id)
            ->take(3)
            ->update([
                'is_completed' => true,
                'completed_at' => '2024-01-05 14:30:00',
            ]);

        // 19. Performance Management (Cycle, KPI / OKR Goals & Appraisals)
        $cycle = PerformanceCycle::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Penilaian Kinerja Semester 1 ' . date('Y')],
            [
                'start_date' => date('Y') . '-01-01',
                'end_date' => date('Y') . '-06-30',
                'status' => 'active',
            ]
        );

        // KPI for Budi
        KpiGoal::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeBudi->id, 'title' => 'Uptime & Keandalan Server Platform RiHRIS'],
            [
                'performance_cycle_id' => $cycle->id,
                'type' => 'kpi',
                'target_value' => 99.90,
                'current_value' => 99.95,
                'unit' => '%',
                'weight' => 50.00,
                'status' => 'achieved',
            ]
        );

        KpiGoal::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeBudi->id, 'title' => 'Penyelesaian Sprint Fitur Payroll & Talent Module'],
            [
                'performance_cycle_id' => $cycle->id,
                'type' => 'okr',
                'target_value' => 100.00,
                'current_value' => 85.00,
                'unit' => '%',
                'weight' => 50.00,
                'status' => 'in_progress',
            ]
        );

        // KPI for Dewi Siti
        KpiGoal::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeSiti->id, 'title' => 'Perekrutan & Pemenuhan Formasi Tim Engineering'],
            [
                'performance_cycle_id' => $cycle->id,
                'type' => 'kpi',
                'target_value' => 10.00,
                'current_value' => 8.00,
                'unit' => 'Kandidat',
                'weight' => 60.00,
                'status' => 'in_progress',
            ]
        );

        KpiGoal::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $employeeSiti->id, 'title' => 'Skor Kepuasan Karyawan Onboarding (CSAT)'],
            [
                'performance_cycle_id' => $cycle->id,
                'type' => 'kpi',
                'target_value' => 90.00,
                'current_value' => 94.00,
                'unit' => 'Poin',
                'weight' => 40.00,
                'status' => 'achieved',
            ]
        );

        // Performance Review for Budi
        PerformanceReview::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'performance_cycle_id' => $cycle->id, 'employee_id' => $employeeBudi->id],
            [
                'reviewer_id' => $owner->id,
                'self_rating' => 92.00,
                'self_feedback' => 'Infrastruktur berjalan stabil dan arsitektur multi-tenant berhasil diimplementasikan sesuai standar industri.',
                'manager_rating' => 90.00,
                'manager_feedback' => 'Hasil kerja sangat memuaskan, SLA sistem tercapai di atas target kuartal.',
                'final_score' => 90.60,
                'status' => 'completed',
            ]
        );

        // 20. Training Courses & Certifications (L&D)
        $course1 = TrainingCourse::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Advanced Cloud Security & Microservices Architecture'],
            [
                'trainer_name' => 'Eko Kurniawan Khannedy',
                'category' => 'technical',
                'location' => 'Online Workshop Zoom',
                'start_date' => now()->subDays(20),
                'end_date' => now()->subDays(18),
                'capacity' => 25,
                'description' => 'Pendalaman arsitektur cloud AWS/GCP, containerization, dan enkripsi data multi-tenant.',
                'status' => 'completed',
            ]
        );

        $course2 = TrainingCourse::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Strategic Human Resource Management & BPJS Compliance'],
            [
                'trainer_name' => 'Agus Prasetyo',
                'category' => 'compliance',
                'location' => 'Ruang Training Lt. 18 Menara Mandiri',
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(6),
                'capacity' => 15,
                'description' => 'Tata kelola kepatuhan ketenagakerjaan, UU Cipta Kerja, dan regulasi pajak PPh 21 TER.',
                'status' => 'scheduled',
            ]
        );

        // Enroll Budi in Course 1 (completed with certificate)
        TrainingEnrollment::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'training_course_id' => $course1->id, 'employee_id' => $employeeBudi->id],
            [
                'status' => 'completed',
                'score' => 95.00,
                'certificate_number' => 'CERT-' . date('Y') . '-' . str_pad($course1->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($employeeBudi->id, 4, '0', STR_PAD_LEFT),
                'feedback' => 'Menguasai seluruh silabus dengan predikat kelulusan Sangat Baik.',
            ]
        );

        // Enroll Dewi Siti in Course 2 (scheduled)
        TrainingEnrollment::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'training_course_id' => $course2->id, 'employee_id' => $employeeSiti->id],
            [
                'status' => 'enrolled',
            ]
        );

        // --- PHASE 5: ENTERPRISE DEMO DATA ---

        // 21. Configurable Multi-Level Approval Workflows
        $leaveWf = Workflow::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_type' => 'leave'],
            [
                'name' => 'Leave Approval 2-Tier Standard',
                'is_active' => true,
            ]
        );
        WorkflowStep::firstOrCreate(
            ['workflow_id' => $leaveWf->id, 'step_order' => 1],
            ['approver_role_slug' => 'hr-manager']
        );
        WorkflowStep::firstOrCreate(
            ['workflow_id' => $leaveWf->id, 'step_order' => 2],
            ['approver_role_slug' => 'company-owner']
        );

        $reimburseWf = Workflow::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_type' => 'reimbursement'],
            [
                'name' => 'Reimbursement Expense Multi-Approval',
                'is_active' => true,
            ]
        );
        WorkflowStep::firstOrCreate(
            ['workflow_id' => $reimburseWf->id, 'step_order' => 1],
            ['approver_role_slug' => 'hr-manager']
        );
        WorkflowStep::firstOrCreate(
            ['workflow_id' => $reimburseWf->id, 'step_order' => 2],
            ['approver_role_slug' => 'company-owner']
        );

        // 22. SaaS Subscription Invoices
        $sub = $tenant->subscription;
        if ($sub) {
            SubscriptionInvoice::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'invoice_number' => 'INV-' . date('Ym') . '-0001'],
                [
                    'subscription_id' => $sub->id,
                    'amount' => 750000.00,
                    'tax_amount' => 82500.00,
                    'total_amount' => 832500.00,
                    'status' => 'paid',
                    'due_date' => now()->subDays(5)->toDateString(),
                    'paid_at' => now()->subDays(5),
                    'payment_method' => 'bank_transfer',
                ]
            );
        }

        // 23. Enterprise API Key
        ApiKey::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Mesin Presensi RFID Lobby Lt. 18'],
            [
                'key_prefix' => 'ri_live_',
                'key_hash' => hash('sha256', 'ri_live_demobiometrickey1234567890'),
                'permissions' => ['attendance:read', 'attendance:write'],
                'is_active' => true,
            ]
        );

        // 24. Webhook Subscription
        WebhookSubscription::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Slack Corporate HR Alerts'],
            [
                'target_url' => 'https://hooks.slack.com/services/DEMO/COMPANY/ALERTS',
                'secret' => 'whsec_demosecret998877',
                'events' => ['employee.created', 'leave.approved', 'payroll.finalized'],
                'is_active' => true,
            ]
        );

        // 25. Security Audit Trail Logs & IP Whitelist
        AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'action' => 'payroll_processed',
            'auditable_type' => 'App\Models\PayrollPeriod',
            'auditable_id' => $period->id,
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => 'processed'],
            'ip_address' => '180.252.12.8',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
        ]);

        AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => $hr->id,
            'action' => 'employee_created',
            'auditable_type' => 'App\Models\Employee',
            'auditable_id' => $employeeBudi->id,
            'old_values' => null,
            'new_values' => ['nik' => $employeeBudi->employee_number, 'name' => $employeeBudi->full_name],
            'ip_address' => '180.252.12.8',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
        ]);

        CompanyIpWhitelist::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'ip_address' => '180.252.12.8'],
            [
                'label' => 'Kantor Pusat Menara Mandiri Lt. 18',
                'is_active' => true,
            ]
        );
    }
}
