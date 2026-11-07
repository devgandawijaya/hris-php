<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\User;
use App\Models\Employee;
use App\Models\WorkplaceIncident;
use App\Models\BpjsJkkClaim;
use App\Models\EmployeeMcuRecord;
use App\Models\FringeBenefit;
use App\Models\OvertimeOrder;
use App\Models\CompanyRegulation;
use App\Models\BipartiteMeeting;
use App\Services\Safety\K3IncidentService;
use App\Services\Tax\FringeBenefitTaxService;
use App\Services\Overtime\SplComplianceService;
use App\Services\Compliance\CompanyRegulationService;
use App\Services\Relations\BipartiteService;
use Illuminate\Validation\ValidationException;

class AdvancedEnterpriseCompliancePhase14Test extends TestCase
{
    protected Tenant $tenant;
    protected Company $company;
    protected User $hrManager;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-phase14-k3-corp'],
            ['name' => 'PT Test Phase 14 K3 Solusindo', 'status' => 'active']
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pt-test-p14-k3'],
            [
                'name' => 'PT Test Phase 14 K3 Solusindo',
                'email' => 'k3@testphase14.com',
                'status' => 'active',
            ]
        );

        $this->hrManager = User::firstOrCreate(
            ['email' => 'hrmanager.p14@testphase14.com'],
            [
                'name' => 'Hendra HR Manager',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
                'is_super_admin' => false,
            ]
        );

        $this->employee = Employee::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'email' => 'agung.p14@testphase14.com'],
            [
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-P14-001',
                'first_name' => 'Agung',
                'last_name' => 'Kurniawan',
                'status' => 'active',
                'employment_status' => 'pkwtt',
                'join_date' => now()->subMonths(12)->toDateString(),
                'phone' => '081288776655',
                'basic_salary' => 9000000,
            ]
        );
    }

    /**
     * 1. Test K3 Workplace Incident Reporting with LTI Lost Workdays.
     */
    public function test_k3_incident_reporting_with_lti_lost_workdays(): void
    {
        $k3Service = app(K3IncidentService::class);

        $incident = $k3Service->reportIncident(
            $this->company,
            $this->employee,
            [
                'incident_date' => now()->toDateString(),
                'incident_time' => '10:15',
                'location' => 'Lantai 2 Gudang Transmisi',
                'severity' => 'lost_time_injury_lti',
                'injury_type' => 'Dislokasi Bahu Kanan',
                'lost_workdays' => 5,
                'description' => 'Terjatuh saat memindahkan box peralatan tanpa sabuk pengaman.',
                'corrective_actions' => 'Wajib pemakaian full-body harness di area ketinggian di atas 1.5 meter.',
            ],
            $this->hrManager
        );

        $this->assertNotNull($incident->id);
        $this->assertStringStartsWith('INC/K3/', $incident->incident_number);
        $this->assertEquals('lost_time_injury_lti', $incident->severity);
        $this->assertEquals(5, $incident->lost_workdays);
        $this->assertEquals('reported', $incident->status);

        $this->assertDatabaseHas('workplace_incidents', [
            'id' => $incident->id,
            'incident_number' => $incident->incident_number,
            'lost_workdays' => 5,
        ]);
    }

    /**
     * 2. Test BPJS Ketenagakerjaan JKK Claim Filing & STMB Allowance Calculation.
     */
    public function test_bpjs_jkk_claim_filing_and_stmb_allowance_calculation(): void
    {
        $k3Service = app(K3IncidentService::class);

        $incident = WorkplaceIncident::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'incident_number' => 'INC/K3/2026/TEST01',
            'incident_date' => now()->toDateString(),
            'location' => 'Bengkel Workshop',
            'employee_id' => $this->employee->id,
            'severity' => 'lost_time_injury_lti',
            'lost_workdays' => 6,
            'description' => 'Luka sayat pada lengan kanan.',
            'status' => 'investigating',
        ]);

        $claim = $k3Service->fileBpjsJkkClaim($incident, [
            'stage' => 'tahap_1',
            'medical_facility_name' => 'RS PLKK Siloam',
            'estimated_medical_expense' => 4500000.00,
            'bpjs_notes' => 'Laporan 2x24 jam diterima.',
        ]);

        $this->assertNotNull($claim->id);
        $this->assertStringStartsWith('JKK-BPJS-', $claim->claim_number);
        $this->assertEquals('tahap_1', $claim->stage);
        // Basic salary = 9,000,000 -> daily = 300,000 -> 6 days STMB = 1,800,000
        $this->assertEquals(1800000.00, (float) $claim->temporary_disability_allowance);
        $this->assertEquals('submitted_to_bpjs', $claim->claim_status);
    }

    /**
     * 3. Test BPJS JKK Claim Settlement reimburses and closes incident.
     */
    public function test_bpjs_jkk_claim_settlement_reimburses_and_closes_incident(): void
    {
        $k3Service = app(K3IncidentService::class);

        $incident = WorkplaceIncident::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'incident_number' => 'INC/K3/2026/TEST02',
            'incident_date' => now()->toDateString(),
            'location' => 'Lini Produksi',
            'employee_id' => $this->employee->id,
            'severity' => 'minor_first_aid',
            'lost_workdays' => 2,
            'description' => 'Iritasi mata ringan karena debu kimia.',
            'status' => 'investigating',
        ]);

        $claim = BpjsJkkClaim::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'workplace_incident_id' => $incident->id,
            'claim_number' => 'JKK-BPJS-2026-TEST02',
            'stage' => 'tahap_2',
            'medical_facility_name' => 'RS PLKK Kimia Farma',
            'estimated_medical_expense' => 1200000.00,
            'claim_status' => 'submitted_to_bpjs',
        ]);

        $settledClaim = $k3Service->settleJkkClaim($claim, 1200000.00, 'Klaim disetujui penuh.');

        $this->assertEquals('reimbursed', $settledClaim->claim_status);
        $this->assertEquals(1200000.00, (float) $settledClaim->approved_bpjs_coverage);
        $this->assertNotNull($settledClaim->settled_at);

        $incident->refresh();
        $this->assertEquals('closed', $incident->status);
    }

    /**
     * 4. Test Employee MCU Health Surveillance record and fitness category.
     */
    public function test_k3_mcu_surveillance_record_and_fitness_category(): void
    {
        $k3Service = app(K3IncidentService::class);

        $mcu = $k3Service->recordMcu($this->employee, [
            'mcu_date' => now()->toDateString(),
            'clinic_provider' => 'Klinik Prodia Utama',
            'blood_pressure' => '120/80',
            'cholesterol' => 195.0,
            'blood_sugar' => 98.0,
            'fitness_category' => 'fit_to_work',
            'doctor_notes' => 'Kondisi kardiovaskular sangat baik.',
        ]);

        $this->assertNotNull($mcu->id);
        $this->assertEquals('fit_to_work', $mcu->fitness_category);
        $this->assertEquals('120/80', $mcu->blood_pressure);
        $this->assertEquals(195.00, (float) $mcu->cholesterol);

        $this->assertDatabaseHas('employee_mcu_records', [
            'id' => $mcu->id,
            'employee_id' => $this->employee->id,
            'fitness_category' => 'fit_to_work',
        ]);
    }

    /**
     * 5. Test Fringe Benefit Tax PMK 66/2023 threshold exemption and taxable natura.
     */
    public function test_fringe_benefit_tax_pmk_66_threshold_exemption_and_taxable_addition(): void
    {
        $fringeService = app(FringeBenefitTaxService::class);

        // Voucher groceries: Gross = 4,500,000; Threshold = 3,000,000; Taxable = 1,500,000
        $benefit = $fringeService->recordBenefit($this->company, $this->employee, [
            'tax_year' => (int) date('Y'),
            'period_month' => (int) date('n'),
            'benefit_type' => 'groceries_vouchers',
            'description' => 'Paket Sembako & Belanja Bulanan',
            'gross_market_value' => 4500000.00,
        ]);

        $this->assertNotNull($benefit->id);
        $this->assertEquals(4500000.00, (float) $benefit->gross_market_value);
        $this->assertEquals(3000000.00, (float) $benefit->non_taxable_threshold);
        $this->assertEquals(1500000.00, (float) $benefit->taxable_natura_value);
        $this->assertTrue($benefit->is_taxable_object);

        $monthlyTaxAddition = $fringeService->getMonthlyNaturaTaxAddition($this->employee, (int) date('Y'), (int) date('n'));
        $this->assertEquals(1500000.00, $monthlyTaxAddition);
    }

    /**
     * 6. Test Fringe Benefit Tax is non-taxable when under PMK 66 threshold.
     */
    public function test_fringe_benefit_tax_non_taxable_when_under_threshold(): void
    {
        $fringeService = app(FringeBenefitTaxService::class);

        // Voucher groceries: Gross = 2,500,000; Threshold = 3,000,000; Taxable = 0
        $benefit = $fringeService->recordBenefit($this->company, $this->employee, [
            'tax_year' => 2025,
            'period_month' => 5,
            'benefit_type' => 'groceries_vouchers',
            'description' => 'Voucher Parsel Lebaran',
            'gross_market_value' => 2500000.00,
        ]);

        $this->assertEquals(0.00, (float) $benefit->taxable_natura_value);
        $this->assertFalse($benefit->is_taxable_object);
    }

    /**
     * 7. Test SPL Overtime Daily Limit validation blocks exceeding 4 hours (PP 35/2021).
     */
    public function test_spl_overtime_daily_limit_validation_blocks_exceeding_4_hours(): void
    {
        $splService = app(SplComplianceService::class);

        $this->expectException(ValidationException::class);

        // Attempt 5 hours overtime on workday -> must fail per Pasal 26 PP 35/2021
        $splService->createOrder($this->company, $this->employee, [
            'overtime_date' => now()->toDateString(),
            'start_time' => '17:00:00',
            'end_time' => '22:00:00', // 5 hours
            'planned_duration_hours' => 5.00,
            'reason_task_description' => 'Pekerjaan melampaui batas harian.',
            'is_day_off' => false,
        ]);
    }

    /**
     * 8. Test SPL Overtime Weekly Limit validation blocks exceeding 18 hours (PP 35/2021).
     */
    public function test_spl_overtime_weekly_limit_validation_blocks_exceeding_18_hours(): void
    {
        $splService = app(SplComplianceService::class);

        $monday = now()->startOfWeek();

        // Seed 16 hours of overtime on Monday-Thursday (4h each)
        for ($i = 0; $i < 4; $i++) {
            OvertimeOrder::withoutGlobalScopes()->create([
                'tenant_id' => $this->tenant->id,
                'company_id' => $this->company->id,
                'order_number' => "SPL/TEST/{$i}",
                'employee_id' => $this->employee->id,
                'overtime_date' => $monday->copy()->addDays($i)->toDateString(),
                'start_time' => '17:00:00',
                'end_time' => '21:00:00',
                'planned_duration_hours' => 4.00,
                'reason_task_description' => 'Existing daily overtime',
                'is_day_off' => false,
                'status' => 'approved',
            ]);
        }

        $this->expectException(ValidationException::class);

        // Attempting 3 more hours on Friday (16 + 3 = 19 > 18 hours/week limit) -> must fail!
        $splService->createOrder($this->company, $this->employee, [
            'overtime_date' => $monday->copy()->addDays(4)->toDateString(),
            'start_time' => '17:00:00',
            'end_time' => '20:00:00',
            'planned_duration_hours' => 3.00,
            'reason_task_description' => 'Friday overtime exceeding weekly quota',
            'is_day_off' => false,
        ]);
    }

    /**
     * 9. Test SPL Overtime approval and employee written consent.
     */
    public function test_spl_overtime_approval_and_employee_written_consent(): void
    {
        $splService = app(SplComplianceService::class);

        $order = OvertimeOrder::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'order_number' => 'SPL/2026/11/TEST99',
            'employee_id' => $this->employee->id,
            'overtime_date' => now()->toDateString(),
            'start_time' => '17:00:00',
            'end_time' => '19:30:00',
            'planned_duration_hours' => 2.50,
            'reason_task_description' => 'Deploy hotfix',
            'is_day_off' => false,
            'status' => 'pending_approval',
        ]);

        // Manager approval
        $splService->approveOrder($order, $this->hrManager);
        $this->assertEquals('approved', $order->status);
        $this->assertNotNull($order->manager_approved_at);

        // Employee written consent per Pasal 26 ayat 3
        $splService->giveEmployeeConsent($order);
        $this->assertNotNull($order->employee_consented_at);

        // Actual verification
        $splService->verifyActualOvertime($order, 2.50);
        $this->assertEquals('completed_verified', $order->status);
        $this->assertEquals(2.50, (float) $order->actual_duration_hours);
    }

    /**
     * 10. Test Company Regulation 2-year statutory validity and H-90 renewal alerts.
     */
    public function test_company_regulation_two_year_statutory_validity_and_h90_alerts(): void
    {
        $regulationService = app(CompanyRegulationService::class);

        // 1. Create PP with 3-year end date -> service must cap at exactly 2 years (UU 13/2003 Pasal 111 ayat 3)
        $reg = $regulationService->registerRegulation($this->company, [
            'document_type' => 'peraturan_perusahaan_pp',
            'title' => 'PP PT Test 2026',
            'reg_number' => 'KEP.560/999/2026',
            'effective_start_date' => '2026-01-01',
            'effective_end_date' => '2029-01-01', // 3 years
            'disnaker_office' => 'Disnakertrans Jakarta',
        ]);

        $this->assertEquals('2028-01-01', $reg->effective_end_date->toDateString()); // Capped to 2 years

        // 2. Alert check for a regulation expiring in 30 days
        CompanyRegulation::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'document_type' => 'peraturan_perusahaan_pp',
            'title' => 'PP Expiring Soon',
            'effective_start_date' => now()->subYears(2)->toDateString(),
            'effective_end_date' => now()->addDays(30)->toDateString(), // H-30
            'status' => 'active',
        ]);

        $alerts = $regulationService->getRenewalAlerts($this->company);
        $expiringAlert = collect($alerts)->firstWhere('title', 'PP Expiring Soon');

        $this->assertNotNull($expiringAlert);
        $this->assertTrue($expiringAlert['needs_action']);
        $this->assertEquals('renewal_pending', $expiringAlert['status']);
    }

    /**
     * 11. Test LKS Bipartite Meeting requires equal representation and Disnaker reporting.
     */
    public function test_lks_bipartite_meeting_requires_equal_representation_and_disnaker_reporting(): void
    {
        $bipartiteService = app(BipartiteService::class);

        // Test 1: Empty representatives should fail
        try {
            $bipartiteService->recordMeeting($this->company, [
                'meeting_date' => now()->toDateString(),
                'meeting_title' => 'Rapat Tanpa Wakil',
                'agenda_topics' => 'Topik',
                'employer_representatives' => [],
                'worker_representatives' => [],
                'meeting_minutes' => 'Notulen',
            ]);
            $this->fail('Should have thrown ValidationException for missing representatives');
        } catch (ValidationException $e) {
            $this->assertTrue(true);
        }

        // Test 2: Valid meeting with 1:1 representatives
        $meeting = $bipartiteService->recordMeeting($this->company, [
            'meeting_date' => now()->toDateString(),
            'meeting_title' => 'Musyawarah K3 & Fasilitas Staf',
            'agenda_topics' => 'Evaluasi APD dan jam kerja lembur',
            'employer_representatives' => ['Direktur HR', 'Manajer Produksi'],
            'worker_representatives' => ['Ketua Serikat', 'Sekretaris Serikat'],
            'meeting_minutes' => 'Mufakat dicapai untuk penambahan rambu K3.',
            'action_items' => 'Realisasi akhir bulan.',
        ]);

        $this->assertNotNull($meeting->id);
        $this->assertStringStartsWith('BIPARTIT/', $meeting->meeting_number);
        $this->assertCount(2, $meeting->employer_representatives);
        $this->assertCount(2, $meeting->worker_representatives);
        $this->assertEquals('conducted', $meeting->status);

        // Test 3: Disnaker reporting
        $bipartiteService->markReportedToDisnaker($meeting);
        $this->assertEquals('reported_to_disnaker', $meeting->status);
        $this->assertNotNull($meeting->disnaker_reported_at);
    }
}
