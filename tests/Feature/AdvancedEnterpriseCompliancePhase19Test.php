<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\EsgMetricSnapshot;
use App\Models\EmployeeCommuteProfile;
use App\Models\CommuteEmissionAudit;
use App\Models\ErgonomicAssessment;
use App\Models\DigitalStressSurvey;
use App\Services\Compliance\EsgReportingService;
use App\Services\Compliance\CarbonCommuteService;
use App\Services\Safety\ErgonomicStressService;
use Carbon\Carbon;
use InvalidArgumentException;

class AdvancedEnterpriseCompliancePhase19Test extends TestCase
{
    protected Tenant $tenant;
    protected Company $company;
    protected Department $techDept;
    protected Position $leadPos;
    protected Position $devPos;
    protected Employee $empMaleLead;
    protected Employee $empFemaleLead;
    protected Employee $empMaleDev;
    protected Employee $empFemaleDev;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-phase19-enterprise-esg'],
            ['name' => 'PT Test Phase 19 Enterprise Sustainability', 'status' => 'active']
        );

        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pt-test-p19-sustainability'],
            [
                'name' => 'PT Test Phase 19 Enterprise Sustainability',
                'legal_name' => 'PT Test Phase 19 Enterprise Sustainability Tbk',
                'npwp' => '01.234.567.8-019.000',
                'address' => 'Sudirman Central Business District (SCBD) Tower Lt. 30',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12190',
                'phone' => '+62 21 8899 0019',
                'email' => 'esg@p19sustainability.test',
                'industry' => 'Renewable Energy & Digital Infrastructure',
            ]
        );

        $this->techDept = Department::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Sustainable Technology']
        );

        $this->leadPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Engineering Lead']
        );

        $this->devPos = Position::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'name' => 'Software Engineer']
        );

        // Male Lead: Salary 20,000,000
        $this->empMaleLead = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'nik' => 'P19-EMP-001'],
            [
                'first_name' => 'Bambang',
                'last_name' => 'Sudirman',
                'email' => 'bambang.sudirman@p19sustainability.test',
                'gender' => 'male',
                'department_id' => $this->techDept->id,
                'position_id' => $this->leadPos->id,
                'join_date' => Carbon::now()->subMonths(36)->toDateString(),
                'employment_type' => 'permanent',
                'basic_salary' => 20000000.00,
                'status' => 'active',
            ]
        );

        // Female Lead: Salary 19,800,000
        $this->empFemaleLead = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'nik' => 'P19-EMP-002'],
            [
                'first_name' => 'Dewi',
                'last_name' => 'Sartika',
                'email' => 'dewi.sartika@p19sustainability.test',
                'gender' => 'female',
                'department_id' => $this->techDept->id,
                'position_id' => $this->leadPos->id,
                'join_date' => Carbon::now()->subMonths(30)->toDateString(),
                'employment_type' => 'permanent',
                'basic_salary' => 19800000.00,
                'status' => 'active',
            ]
        );

        // Male Dev: Salary 12,000,000
        $this->empMaleDev = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'nik' => 'P19-EMP-003'],
            [
                'first_name' => 'Eko',
                'last_name' => 'Prabowo',
                'email' => 'eko.prabowo@p19sustainability.test',
                'gender' => 'male',
                'department_id' => $this->techDept->id,
                'position_id' => $this->devPos->id,
                'join_date' => Carbon::now()->subMonths(18)->toDateString(),
                'employment_type' => 'permanent',
                'basic_salary' => 12000000.00,
                'status' => 'active',
            ]
        );

        // Female Dev: Salary 12,000,000
        $this->empFemaleDev = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'company_id' => $this->company->id, 'nik' => 'P19-EMP-004'],
            [
                'first_name' => 'Maya',
                'last_name' => 'Indriyani',
                'email' => 'maya.indriyani@p19sustainability.test',
                'gender' => 'female',
                'department_id' => $this->techDept->id,
                'position_id' => $this->devPos->id,
                'join_date' => Carbon::now()->subMonths(12)->toDateString(),
                'employment_type' => 'permanent',
                'basic_salary' => 12000000.00,
                'status' => 'active',
            ]
        );
    }

    /**
     * 1. Test ESG Metric Snapshot Calculates Gender Pay Gap Ratio (GRI 405-2).
     */
    public function test_esg_metric_snapshot_generation_calculates_gender_pay_gap_ratio(): void
    {
        $service = new EsgReportingService();
        $snapshot = $service->generateAnnualSnapshot($this->company, 2026);

        $this->assertNotNull($snapshot->id);
        $this->assertEquals(2026, $snapshot->reporting_year);

        // Male average: (20jt + 12jt)/2 = 16jt. Female average: (19.8jt + 12jt)/2 = 15.9jt.
        // Ratio: 15.9 / 16.0 = 0.9938
        $this->assertGreaterThan(0.9500, (float) $snapshot->gender_pay_gap_ratio);
        $this->assertLessThanOrEqual(1.0500, (float) $snapshot->gender_pay_gap_ratio);
    }

    /**
     * 2. Test ESG Snapshot Calculates Female Workforce and Leadership Percentage (GRI 405-1).
     */
    public function test_esg_metric_snapshot_female_workforce_and_leadership_diversity_percentage(): void
    {
        $service = new EsgReportingService();
        $snapshot = $service->generateAnnualSnapshot($this->company, 2026);

        // 2 females out of 4 employees = 50.0%
        $this->assertEquals(50.00, (float) $snapshot->female_workforce_pct);

        // 1 female lead out of 2 leads = 50.0%
        $this->assertEquals(50.00, (float) $snapshot->female_leadership_pct);
    }

    /**
     * 3. Test ESG Snapshot Board Diversity and Training Hours (GRI 404-1).
     */
    public function test_esg_metric_snapshot_board_diversity_and_training_hours(): void
    {
        $service = new EsgReportingService();
        $snapshot = $service->generateAnnualSnapshot($this->company, 2026);

        $this->assertGreaterThanOrEqual(0.00, (float) $snapshot->board_diversity_pct);
        $this->assertGreaterThan(0.00, (float) $snapshot->avg_training_hours_per_employee);
        $this->assertEquals('published', $snapshot->status);
    }

    /**
     * 4. Test ESG Snapshot Turnover and Safety Incident Rate (GRI 401-1 & GRI 403-9).
     */
    public function test_esg_metric_snapshot_turnover_and_ltifr_calculation(): void
    {
        $service = new EsgReportingService();
        $snapshot = $service->generateAnnualSnapshot($this->company, 2026);

        $this->assertGreaterThanOrEqual(0.00, (float) $snapshot->annual_turnover_rate);
        $this->assertEquals(0.00, (float) $snapshot->lost_time_injury_frequency_rate);
    }

    /**
     * 5. Test Carbon Commute Profile Calculates Weekly Emissions for Gasoline Car.
     */
    public function test_carbon_commute_profile_calculates_weekly_emissions_for_car_gasoline(): void
    {
        $service = new CarbonCommuteService();

        // Distance: 20 km one-way (40 km roundtrip), 4 WFO days, car_gasoline (0.192 kg/km)
        // 40 km * 4 days * 0.192 = 30.72 kg CO2e
        $profile = $service->updateEmployeeCommute(
            $this->empMaleLead,
            20.0,
            'car_gasoline',
            4,
            1
        );

        $this->assertEquals(20.00, (float) $profile->home_to_office_distance_km);
        $this->assertEquals('car_gasoline', $profile->primary_commute_mode);
        $this->assertEquals(30.72, (float) $profile->weekly_emissions_kg_co2);
    }

    /**
     * 6. Test Carbon Commute Profile Calculates Avoided Emissions from Remote Work (WFH).
     */
    public function test_carbon_commute_profile_calculates_avoided_emissions_from_wfh(): void
    {
        $service = new CarbonCommuteService();

        // Distance: 20 km one-way (40 km roundtrip), 2 WFH days, electric_vehicle (0.053 kg/km)
        // Avoided: 40 km * 2 days * 0.053 = 4.24 kg CO2e
        $profile = $service->updateEmployeeCommute(
            $this->empFemaleLead,
            20.0,
            'electric_vehicle',
            3,
            2
        );

        $this->assertEquals(4.24, (float) $profile->weekly_avoided_emissions_kg_co2);
        // Actual emissions: 40 km * 3 days * 0.053 = 6.36 kg CO2e
        $this->assertEquals(6.36, (float) $profile->weekly_emissions_kg_co2);
    }

    /**
     * 7. Test Carbon Commute Rejects Invalid Transport Mode or Negative Distance.
     */
    public function test_carbon_commute_rejects_invalid_transport_mode_or_negative_distance(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $service = new CarbonCommuteService();
        $service->updateEmployeeCommute(
            $this->empMaleDev,
            -15.0, // Invalid negative distance
            'public_transit'
        );
    }

    /**
     * 8. Test Scope 3 Corporate Commute Emission Annual Audit Aggregation.
     */
    public function test_carbon_commute_annual_scope_three_audit_aggregation(): void
    {
        $service = new CarbonCommuteService();

        // Register profiles
        $service->updateEmployeeCommute($this->empMaleLead, 15.0, 'car_gasoline', 3, 2);
        $service->updateEmployeeCommute($this->empFemaleLead, 15.0, 'public_transit', 3, 2);

        $audit = $service->runAnnualCommuteAudit($this->company, 2026);

        $this->assertNotNull($audit->id);
        $this->assertEquals(2026, $audit->audit_year);
        $this->assertGreaterThanOrEqual(2, $audit->participating_employees_count);
        $this->assertGreaterThan(0.00, (float) $audit->total_annual_commute_distance_km);
        $this->assertGreaterThan(0.00, (float) $audit->gross_emissions_ton_co2e);
        $this->assertGreaterThan(0.00, (float) $audit->avoided_emissions_wfh_ton_co2e);
        $this->assertLessThan((float) $audit->gross_emissions_ton_co2e, (float) $audit->net_commute_emissions_ton_co2e);
    }

    /**
     * 9. Test Ergonomic Assessment Full Compliance Score and Low Posture Risk (Permenaker 5/2018).
     */
    public function test_ergonomic_assessment_full_compliance_score_and_low_risk(): void
    {
        $service = new ErgonomicStressService();

        // 350 Lux (>=300 lux = 30pts), lumbar chair (25pts), eye level monitor (25pts), wrist rest (20pts) = 100pts
        $assessment = $service->recordErgonomicAssessment(
            $this->company,
            $this->empMaleLead->id,
            $this->techDept->id,
            350,
            true,
            true,
            true,
            'Executive Tech Workstation'
        );

        $this->assertEquals(100.00, (float) $assessment->ergonomic_compliance_score);
        $this->assertEquals('low', $assessment->posture_risk_level);
        $this->assertEquals('completed', $assessment->status);
    }

    /**
     * 10. Test Ergonomic Assessment Substandard Lighting and Missing Accessories Flags Action Required.
     */
    public function test_ergonomic_assessment_substandard_lighting_and_accessories_action_required(): void
    {
        $service = new ErgonomicStressService();

        // 120 Lux (< 200 = 5pts), no lumbar chair (0), no eye level (0), no wrist rest (0) = 5pts
        $assessment = $service->recordErgonomicAssessment(
            $this->company,
            $this->empMaleDev->id,
            $this->techDept->id,
            120,
            false,
            false,
            false,
            'Makeshift Desk',
            'Pencahayaan redup, segera pasang lampu LED dan sediakan kursi ergonomis bersertifikat.'
        );

        $this->assertEquals(5.00, (float) $assessment->ergonomic_compliance_score);
        $this->assertEquals('high', $assessment->posture_risk_level);
        $this->assertEquals('action_required', $assessment->status);
    }

    /**
     * 11. Test Digital Stress Survey Computes Low Burnout Risk Index for Balanced Workload.
     */
    public function test_digital_stress_survey_computes_burnout_risk_index_and_low_classification(): void
    {
        $service = new ErgonomicStressService();

        // Screen hours: 7.0, meeting: 2/5, after-hours: 1/5, eyestrain: 2/5
        $survey = $service->recordStressSurvey(
            $this->empFemaleDev,
            7.0,
            2,
            1,
            2
        );

        $this->assertEquals(7.0, (float) $survey->avg_daily_screen_hours);
        $this->assertLessThan(35.0, (float) $survey->digital_burnout_risk_index);
        $this->assertEquals('low', $survey->risk_classification);
    }

    /**
     * 12. Test Digital Stress Survey Detects High/Severe Burnout Risk for Overworked Employee.
     */
    public function test_digital_stress_survey_detects_severe_burnout_risk_for_excessive_workload(): void
    {
        $service = new ErgonomicStressService();

        // Screen hours: 14.0, meeting: 5/5, after-hours: 5/5, eyestrain: 5/5
        $survey = $service->recordStressSurvey(
            $this->empMaleDev,
            14.0,
            5,
            5,
            5
        );

        $this->assertGreaterThanOrEqual(80.0, (float) $survey->digital_burnout_risk_index);
        $this->assertEquals('severe', $survey->risk_classification);
        $this->assertStringContainsString('burnout digital akut', $survey->coping_recommendation);
    }
}
