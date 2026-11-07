<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\OutsourcingVendor;
use App\Models\OutsourcingContract;
use App\Models\VendorWorkerAssignment;
use App\Models\LaborUnion;
use App\Models\PkbNegotiation;
use App\Models\UnionDuesDeduction;
use App\Models\PpksComplaintCase;
use App\Models\FemaleWorkerFacilityCheck;
use App\Models\VoluntarySeparationProgram;
use App\Models\VssApplication;
use App\Models\DplkParticipation;
use App\Models\InternshipProgram;
use App\Models\InternshipLogbook;
use App\Services\Compliance\OutsourcingComplianceService;
use App\Services\Relations\PkbNegotiationService;
use App\Services\Compliance\PpksProtectionService;
use App\Services\Payroll\VoluntarySeparationService;
use App\Services\Talent\InternshipApprenticeshipService;
use Carbon\Carbon;
use InvalidArgumentException;

class AdvancedEnterpriseCompliancePhase17Test extends TestCase
{
    protected Tenant $tenant;
    protected Company $company;
    protected Department $techDept;
    protected Position $devPos;
    protected User $hrManager;
    protected Employee $employeeSenior;
    protected Employee $employeeJunior;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-phase17-enterprise-relations'],
            ['name' => 'PT Test Phase 17 Enterprise Harmoni', 'status' => 'active']
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pt-test-p17-relations'],
            [
                'name' => 'PT Test Phase 17 Enterprise Harmoni',
                'legal_name' => 'PT Test Phase 17 Enterprise Harmoni Tbk',
                'npwp' => '01.234.567.8-017.000',
                'address' => 'Gedung Artha Graha Lt. 25, SCBD',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'status' => 'active',
            ]
        );

        $this->techDept = Department::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Platform Engineering'],
            ['code' => 'PLT-ENG']
        );

        $this->devPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'department_id' => $this->techDept->id, 'title' => 'Senior Infrastructure Engineer'],
            ['code' => 'SR-INFRA']
        );

        $this->hrManager = User::firstOrCreate(
            ['email' => 'hrmanager.p17@testphase17.com'],
            [
                'name' => 'Ratna Dewi, S.H., M.H.',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
                'is_super_admin' => false,
            ]
        );

        $this->employeeSenior = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'hendra.gunawan@testphase17.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P17-001',
                'nik_ktp' => '3171010101880017',
                'first_name' => 'Hendra',
                'last_name' => 'Gunawan',
                'gender' => 'male',
                'citizenship' => 'WNI',
                'department_id' => $this->techDept->id,
                'position_id' => $this->devPos->id,
                'employment_status' => 'pkwtt',
                'join_date' => Carbon::parse('2020-01-15'),
                'basic_salary' => 16000000.00,
                'status' => 'active',
            ]
        );

        $this->employeeJunior = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'siti.nurhaliza@testphase17.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P17-002',
                'nik_ktp' => '3171010101960017',
                'first_name' => 'Siti',
                'last_name' => 'Nurhaliza',
                'gender' => 'female',
                'citizenship' => 'WNI',
                'department_id' => $this->techDept->id,
                'position_id' => $this->devPos->id,
                'employment_status' => 'pkwt',
                'join_date' => Carbon::parse('2024-03-01'),
                'basic_salary' => 9500000.00,
                'status' => 'active',
            ]
        );
    }

    /**
     * Test 1: Outsourcing vendor registration under PP No. 35/2021 Pasal 18
     */
    public function test_outsourcing_vendor_registration_under_pp_35_2021(): void
    {
        $service = app(OutsourcingComplianceService::class);

        $vendor = $service->registerVendor($this->company, [
            'name' => 'PT Garda Bhakti Nusantara Sentosa',
            'legal_entity_type' => 'PT',
            'nib_oss_license' => '0220109182312-OSS-KEMNAKER',
            'disnaker_registration_number' => '560/DISNAKER-REKANAN/2026/089',
            'pic_name' => 'Bambang Wijaya',
            'pic_contact' => '081298765431',
            'management_fee_percentage' => 10.00,
        ]);

        $this->assertInstanceOf(OutsourcingVendor::class, $vendor);
        $this->assertEquals('PT', $vendor->legal_entity_type);
        $this->assertEquals(10.00, (float) $vendor->management_fee_percentage);
        $this->assertEquals('active', $vendor->status);
    }

    /**
     * Test 2: Outsourcing contract creation with mandatory TUPE protection under Putusan MK 27/2011
     */
    public function test_outsourcing_contract_creation_with_tupe_guarantee(): void
    {
        $service = app(OutsourcingComplianceService::class);

        $vendor = OutsourcingVendor::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'vendor_code' => 'VND-TEST-002',
            'name' => 'PT Cipta Jasa Mandiri',
            'legal_entity_type' => 'PT',
            'nib_oss_license' => '0220109999999',
            'pic_name' => 'Ahmad Fauzi',
            'pic_contact' => '081234567890',
            'management_fee_percentage' => 12.00,
            'status' => 'active',
        ]);

        $contract = $service->createContract($vendor, [
            'contract_number' => 'SPK-CLEAN/2026/001',
            'service_type' => 'cleaning',
            'start_date' => Carbon::now()->startOfYear()->toDateString(),
            'end_date' => Carbon::now()->endOfYear()->toDateString(),
            'tupe_protection_guarantee' => true,
            'headcount_quota' => 8,
            'monthly_billing_amount' => 45000000.00,
        ]);

        $this->assertInstanceOf(OutsourcingContract::class, $contract);
        $this->assertTrue($contract->tupe_protection_guarantee);
        $this->assertEquals(8, $contract->headcount_quota);
        $this->assertEquals('active', $contract->status);
    }

    /**
     * Test 3: Vendor worker assignment and monthly billing calculation with management fee
     */
    public function test_outsourcing_worker_assignment_and_billing_calculation(): void
    {
        $service = app(OutsourcingComplianceService::class);

        $vendor = OutsourcingVendor::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'vendor_code' => 'VND-TEST-003',
            'name' => 'PT Mitra Sarana Keamanan',
            'legal_entity_type' => 'PT',
            'nib_oss_license' => '0220108888888',
            'pic_name' => 'Suryadi',
            'pic_contact' => '08111222333',
            'management_fee_percentage' => 10.00, // 10% fee
            'status' => 'active',
        ]);

        $contract = OutsourcingContract::create([
            'tenant_id' => $this->tenant->id,
            'outsourcing_vendor_id' => $vendor->id,
            'contract_number' => 'SPK-SEC/2026/003',
            'service_type' => 'security',
            'start_date' => Carbon::now()->startOfYear()->toDateString(),
            'end_date' => Carbon::now()->endOfYear()->toDateString(),
            'tupe_protection_guarantee' => true,
            'headcount_quota' => 2,
            'monthly_billing_amount' => 12000000.00,
            'status' => 'active',
        ]);

        // Assign 2 workers with wage >= UMK (Rp 5.340.000)
        $w1 = $service->assignWorker($contract, [
            'worker_name' => 'Budi Santoso',
            'nik_ktp' => '3271012304800001',
            'assigned_role' => 'Anggota Security Regu A',
            'monthly_wage_paid' => 5500000.00,
        ], 5340000.00);

        $w2 = $service->assignWorker($contract, [
            'worker_name' => 'Agus Salim',
            'nik_ktp' => '3271012304800002',
            'assigned_role' => 'Anggota Security Regu B',
            'monthly_wage_paid' => 5500000.00,
        ], 5340000.00);

        $this->assertTrue($w1->wage_compliant_with_umk);
        $this->assertTrue($w1->bpjs_tk_registered);

        $billing = $service->calculateMonthlyBilling($contract);

        $this->assertEquals(2, $billing['active_headcount']);
        $this->assertEquals(11000000.00, $billing['total_base_wages']); // 5.5jt * 2
        $this->assertEquals(1100000.00, $billing['management_fee_amount']); // 10% of 11jt
        $this->assertEquals(12100000.00, $billing['grand_total_billing']); // 11jt + 1.1jt
    }

    /**
     * Test 4: Labor union registration and 50% bargaining rights threshold under Pasal 119 UU 13/2003
     */
    public function test_labor_union_registration_and_bargaining_rights_threshold(): void
    {
        $service = app(PkbNegotiationService::class);

        // Company with 20 employees, union has 12 members = 60% (> 50% threshold)
        $unionMajor = $service->registerUnion($this->company, [
            'union_name' => 'Serikat Pekerja Solusindo Bersatu',
            'registration_number' => '568/DISNAKER-PENCATATAN/2026/001',
            'chairperson_name' => 'Hendra Gunawan',
            'total_verified_members' => 12,
        ], 20);

        $this->assertInstanceOf(LaborUnion::class, $unionMajor);
        $this->assertEquals(60.00, (float) $unionMajor->workforce_percentage);
        $this->assertTrue($unionMajor->has_bargaining_rights);

        // Union with only 6 members out of 20 = 30% (< 50% threshold)
        $unionMinor = $service->registerUnion($this->company, [
            'union_name' => 'Serikat Buruh Progresif',
            'registration_number' => '568/DISNAKER-PENCATATAN/2026/002',
            'chairperson_name' => 'Bambang Sutrisno',
            'total_verified_members' => 6,
        ], 20);

        $this->assertEquals(30.00, (float) $unionMinor->workforce_percentage);
        $this->assertFalse($unionMinor->has_bargaining_rights);
    }

    /**
     * Test 5: PKB negotiation time limit enforcement under Permenaker No. 28/2014
     */
    public function test_pkb_negotiation_time_limit_under_permenaker_28_2014(): void
    {
        $service = app(PkbNegotiationService::class);

        $union = LaborUnion::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'union_name' => 'Serikat Pekerja Sah PKB',
            'registration_number' => '568/DISNAKER/2026/005',
            'chairperson_name' => 'Hendra Gunawan',
            'total_verified_members' => 15,
            'workforce_percentage' => 75.00,
            'has_bargaining_rights' => true,
            'is_active' => true,
        ]);

        $pkb = $service->initiatePkbNegotiation($this->company, $union, [
            'pkb_period_years' => '2026-2028',
            'negotiation_start_date' => Carbon::now()->toDateString(),
            'negotiation_target_end_date' => Carbon::now()->addDays(30)->toDateString(),
            'employer_delegation_lead' => 'Direktur HR',
            'union_delegation_lead' => 'Ketua SP',
            'summary_clauses' => 'Penguatan klausul cuti tahunan dan jaminan pensiun.',
        ]);

        $this->assertInstanceOf(PkbNegotiation::class, $pkb);
        $this->assertEquals('in_progress', $pkb->status);

        // Finalize registration at Disnaker
        $finalized = $service->finalizePkbRegistration($pkb, 'KEP.560/PKB/2026/089');
        $this->assertEquals('registered_at_disnaker', $finalized->status);
        $this->assertEquals('KEP.560/PKB/2026/089', $finalized->disnaker_decree_number);
    }

    /**
     * Test 6: Union dues check-off system registration requires written consent letter
     */
    public function test_union_dues_checkoff_with_written_consent(): void
    {
        $service = app(PkbNegotiationService::class);

        $union = LaborUnion::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'union_name' => 'Serikat Pekerja Check-off',
            'registration_number' => '568/DISNAKER/2026/006',
            'chairperson_name' => 'Hendra Gunawan',
            'total_verified_members' => 10,
            'workforce_percentage' => 55.00,
            'has_bargaining_rights' => true,
            'is_active' => true,
        ]);

        $checkoff = $service->registerDuesCheckoff(
            $union,
            $this->employeeSenior,
            30000.00,
            'SK-CHECKOFF-SP/2026/015'
        );

        $this->assertInstanceOf(UnionDuesDeduction::class, $checkoff);
        $this->assertEquals(30000.00, (float) $checkoff->monthly_dues_amount);
        $this->assertEquals('SK-CHECKOFF-SP/2026/015', $checkoff->consent_letter_number);
        $this->assertTrue($checkoff->is_active);
    }

    /**
     * Test 7: PPKS confidential complaint filing and pseudonym under Kepmenaker 88/2023 & UU TPKS 12/2022
     */
    public function test_ppks_confidential_complaint_filing_and_pseudonym(): void
    {
        $service = app(PpksProtectionService::class);

        $case = $service->fileComplaint($this->company, [
            'incident_date' => Carbon::now()->subDays(3)->toDateString(),
            'allegation_type' => 'verbal',
            'is_anonymous' => true,
            'investigator_satgas_lead' => 'Dra. Nurul Hidayati (Satgas PPKS)',
            'victim_support_measures' => 'Pendampingan psikologis dan jaminan bebas retaliasi.',
        ]);

        $this->assertInstanceOf(PpksComplaintCase::class, $case);
        $this->assertStringStartsWith('PPKS-', $case->ticket_number);
        $this->assertTrue($case->is_anonymous);
        $this->assertStringStartsWith('Saksi/Korban Terlindungi-', $case->complainant_pseudonym);
        $this->assertEquals('received', $case->status);
    }

    /**
     * Test 8: PPKS investigation resolution and disciplinary sanction recommendation
     */
    public function test_ppks_investigation_update_and_sanction_recommendation(): void
    {
        $service = app(PpksProtectionService::class);

        $case = PpksComplaintCase::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'ticket_number' => 'PPKS-2026-TEST-008',
            'incident_date' => Carbon::now()->subDays(7)->toDateString(),
            'allegation_type' => 'physical',
            'is_anonymous' => true,
            'complainant_pseudonym' => 'Saksi Terlindungi-C',
            'investigator_satgas_lead' => 'Ketua Satgas PPKS',
            'status' => 'investigating',
        ]);

        $updatedCase = $service->updateInvestigation(
            $case,
            'resolved',
            'skorsing',
            'Sanksi skorsing 1 bulan bagi terlapor dan pemindahan lokasi kerja.'
        );

        $this->assertEquals('resolved', $updatedCase->status);
        $this->assertEquals('skorsing', $updatedCase->sanction_recommendation);
    }

    /**
     * Test 9: Female worker facilities statutory audit under Pasal 76 UU 13/2003 & Permenkes 15/2013
     */
    public function test_female_worker_facility_audit_under_pasal_76_uu_13_2003(): void
    {
        $service = app(PpksProtectionService::class);

        $audit = $service->auditFemaleWorkerFacilities($this->company, [
            'inspection_date' => Carbon::now()->toDateString(),
            'lactation_room_standard_met' => true,
            'night_shift_transport_provided' => true,
            'night_shift_nutritious_food_provided' => true,
            'daycare_subsidy_available' => true,
            'compliance_notes' => 'Ruang laktasi higienis lengkap dengan freezer ASI dan armada shuttle jemputan malam operasional.',
        ], $this->hrManager->id);

        $this->assertInstanceOf(FemaleWorkerFacilityCheck::class, $audit);
        $this->assertTrue($audit->lactation_room_standard_met);
        $this->assertTrue($audit->night_shift_transport_provided);

        $report = $service->generateDisnakerGenderComplianceReport($this->company);
        $this->assertIsArray($report);
        $this->assertEquals('PATUH PENUH', $report['status_kepatuhan_keseluruhan']);
        $this->assertEquals('MEMENUHI STANDAR', $report['fasilitas_ruang_laktasi']);
        $this->assertEquals('TERSEDIA', $report['angkutan_antar_jemput_malam']);
    }

    /**
     * Test 10: Voluntary separation scheme (VSS) package calculation with severance multiplier
     */
    public function test_vss_program_creation_and_package_calculation(): void
    {
        $service = app(VoluntarySeparationService::class);

        $program = $service->createProgram($this->company, [
            'title' => 'Program Pensiun Dini Sukarela 2026',
            'open_date' => Carbon::now()->subWeek()->toDateString(),
            'close_date' => Carbon::now()->addMonth()->toDateString(),
            'severance_multiplier' => 2.00, // 2x statutory PMTK
            'additional_golden_handshake_bonus' => 25000000.00,
            'max_quota' => 5,
        ]);

        $this->assertInstanceOf(VoluntarySeparationProgram::class, $program);
        $this->assertEquals(2.00, (float) $program->severance_multiplier);
        $this->assertEquals(25000000.00, (float) $program->additional_golden_handshake_bonus);

        // Apply for Senior Employee (tenure > 5 years, salary 16jt)
        $application = $service->applyForProgram($program, $this->employeeSenior);

        $this->assertInstanceOf(VssApplication::class, $application);
        $this->assertEquals(16000000.00, (float) $application->last_basic_salary);
        $this->assertGreaterThan(0, (float) $application->calculated_severance_amount);
        $this->assertGreaterThan((float) $application->calculated_severance_amount, (float) $application->total_package_received);

        // Approve application
        $approved = $service->approveApplication($application, $this->hrManager->id);
        $this->assertEquals('approved', $approved->status);
        $this->assertEquals(1, $program->fresh()->approved_applicants_count);
    }

    /**
     * Test 11: DPLK pension program registration under UU No. 4/2023 (UU P2SK)
     */
    public function test_dplk_participation_registration_under_uu_p2sk(): void
    {
        $service = app(VoluntarySeparationService::class);

        $dplk = $service->registerDplk($this->company, $this->employeeSenior, [
            'dplk_provider_name' => 'DPLK Bank Mandiri',
            'account_number' => 'DPLK-MDR-992182',
            'employer_contribution_percentage' => 5.00,
            'employee_contribution_percentage' => 2.50,
            'initial_balance' => 35000000.00,
        ]);

        $this->assertInstanceOf(DplkParticipation::class, $dplk);
        $this->assertEquals(5.00, (float) $dplk->employer_contribution_percentage);
        $this->assertEquals(2.50, (float) $dplk->employee_contribution_percentage);
        $this->assertEquals(35000000.00, (float) $dplk->current_accumulated_fund);
        $this->assertEquals('active', $dplk->status);
    }

    /**
     * Test 12: Internship enrollment enforces statutory 20% quota, duration limit, and certificate payload
     */
    public function test_internship_enrollment_with_statutory_quota_and_duration_limit(): void
    {
        $service = app(InternshipApprenticeshipService::class);

        // Valid enrollment (6 months duration, active workforce 20 -> max 4 interns)
        $intern = $service->enrollIntern($this->company, [
            'program_title' => 'Software Engineering & Cloud DevOps Internship',
            'department_id' => $this->techDept->id,
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addMonths(6)->toDateString(),
            'pocket_money_allowance_daily' => 85000.00,
            'bpjs_bpu_covered' => true,
            'mentor_employee_id' => $this->employeeSenior->id,
            'intern_candidate_name' => 'Dimas Arya Pratama',
            'intern_nik_ktp' => '3171012304020005',
            'institution_origin' => 'Universitas Indonesia',
        ], 20);

        $this->assertInstanceOf(InternshipProgram::class, $intern);
        $this->assertTrue($intern->statutory_ratio_compliant);
        $this->assertTrue($intern->bpjs_bpu_covered);
        $this->assertEquals(85000.00, (float) $intern->pocket_money_allowance_daily);

        // Record & verify logbook
        $logbook = $service->recordLogbook($intern, [
            'log_date' => Carbon::now()->toDateString(),
            'activity_summary' => 'Belajar perancangan API dan CI/CD pipeline.',
            'competency_mastered' => 'REST API Design & Docker Containerization',
        ]);
        $this->assertFalse($logbook->is_verified_by_mentor);

        $verifiedLog = $service->verifyLogbook($logbook, 4.80, 'Sangat baik dan cepat beradaptasi.');
        $this->assertTrue($verifiedLog->is_verified_by_mentor);
        $this->assertEquals(4.80, (float) $verifiedLog->mentor_rating);

        // Certificate payload generation under Pasal 18 Permenaker 6/2020
        $cert = $service->generateCertificatePayload($intern);
        $this->assertIsArray($cert);
        $this->assertEquals('Dimas Arya Pratama', $cert['nama_peserta']);
        $this->assertEquals('SANGAT KOMPETEN', $cert['predikat_kelulusan']);
        $this->assertStringContainsString('Permenaker No. 6 Tahun 2020', $cert['dasar_hukum']);

        // Invalid duration (> 1 year) must throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('maksimal 1 (satu) tahun');

        $service->enrollIntern($this->company, [
            'program_title' => 'Internship Terlalu Lama',
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addYears(2)->toDateString(), // 2 years > 1 year!
            'intern_candidate_name' => 'Pelamar Ilegal',
            'intern_nik_ktp' => '3171019999999999',
            'institution_origin' => 'Kampus X',
        ], 20);
    }
}
