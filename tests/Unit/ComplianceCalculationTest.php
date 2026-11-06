<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Compliance\Pph21TerCalculator;
use App\Services\Compliance\BpjsCalculator;
use App\Services\Compliance\OvertimeCalculator;
use App\Services\Compliance\PkwtCompensationCalculator;

class ComplianceCalculationTest extends TestCase
{
    /**
     * Test PPh 21 TER calculation per PP 58/2023 & PMK 168/2023.
     */
    public function test_pph21_ter_calculates_correct_category_and_tax_rate(): void
    {
        $calculator = new Pph21TerCalculator();

        // 1. TK/0 maps to Category A
        $this->assertEquals('A', $calculator->getCategory('TK/0'));

        // 2. K/1 maps to Category B
        $this->assertEquals('B', $calculator->getCategory('K/1'));

        // 3. K/3 maps to Category C
        $this->assertEquals('C', $calculator->getCategory('K/3'));

        // Test Category A with Gross Rp 5.000.000 (below 5.4m -> 0% tax)
        $res1 = $calculator->calculateMonthly(5000000, 'TK/0');
        $this->assertEquals(0.00, $res1['rate']);
        $this->assertEquals(0.00, $res1['tax']);

        // Test Category A with Gross Rp 10.000.000 (Tier 9.65m - 10.05m -> rate 2%)
        $res2 = $calculator->calculateMonthly(10000000, 'TK/0');
        $this->assertEquals(0.02, $res2['rate']);
        $this->assertEquals(200000.00, $res2['tax']);
    }

    /**
     * Test BPJS calculations with official ceiling caps.
     */
    public function test_bpjs_calculates_correct_shares_and_respects_caps(): void
    {
        $calculator = new BpjsCalculator();

        // Monthly wage: Rp 15.000.000
        $result = $calculator->calculate(15000000, 'low', 12000000, 10042300);

        // JHT: 3.7% employer, 2.0% employee of Rp 15.000.000
        $this->assertEquals(555000.00, $result['jht_employer']);
        $this->assertEquals(300000.00, $result['jht_employee']);

        // JKM: 0.3% of 15m = Rp 45.000
        $this->assertEquals(45000.00, $result['jkm_employer']);

        // JKK: low risk (0.54%) of 15m = Rp 81.000
        $this->assertEquals(81000.00, $result['jkk_employer']);

        // JP: Capped at Rp 10.042.300 (Employer 2%, Employee 1%)
        $this->assertEquals(200846.00, $result['jp_employer']);
        $this->assertEquals(100423.00, $result['jp_employee']);

        // BPJS Kesehatan: Capped at Rp 12.000.000 (Employer 4% = 480k, Employee 1% = 120k)
        $this->assertEquals(480000.00, $result['bpjs_kes_employer']);
        $this->assertEquals(120000.00, $result['bpjs_kes_employee']);
    }

    /**
     * Test Overtime calculation per PP 35/2021 (1/173 divisor).
     */
    public function test_overtime_calculates_correctly_per_pp35_2021(): void
    {
        $calculator = new OvertimeCalculator();

        $monthlyWage = 8650000.00; // 8.650.000 / 173 = 50.000 hourly rate
        
        // 3 hours normal workday overtime:
        // Hour 1 x 1.5 = 1.5, Hour 2 x 2.0 = 2.0, Hour 3 x 2.0 = 2.0 -> total multiplier = 5.5
        // Total pay = 50.000 * 5.5 = 275.000
        $result = $calculator->calculate($monthlyWage, 3.0, false);
        $this->assertEquals(50000.00, $result['hourly_rate']);
        $this->assertEquals(5.5, $result['multiplier_total']);
        $this->assertEquals(275000.00, $result['overtime_pay']);
    }

    /**
     * Test PKWT compensation per PP 35/2021 Article 61A.
     */
    public function test_pkwt_compensation_calculates_correct_pro_rata(): void
    {
        $calculator = new PkwtCompensationCalculator();

        // 6 months service with Rp 12.000.000 wage:
        // (6 / 12) * 12.000.000 = Rp 6.000.000
        $result = $calculator->calculate(12000000, 6);
        $this->assertTrue($result['is_eligible']);
        $this->assertEquals(6000000.00, $result['compensation_amount']);

        // Less than 1 month service is not eligible
        $resultZero = $calculator->calculate(12000000, 0);
        $this->assertFalse($resultZero['is_eligible']);
        $this->assertEquals(0.00, $resultZero['compensation_amount']);
    }
}

