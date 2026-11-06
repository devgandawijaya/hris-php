<?php

namespace App\Services\Compliance;

class BpjsCalculator
{
    /**
     * Calculate BPJS Ketenagakerjaan and BPJS Kesehatan contributions.
     *
     * @param float $baseWage (Gaji Pokok + Tunjangan Tetap)
     * @param string $jkkGrade ('very_low', 'low', 'medium', 'high', 'very_high')
     * @param float|null $customKesCap
     * @param float|null $customJpCap
     * @return array
     */
    public function calculate(
        float $baseWage, 
        string $jkkGrade = 'low', 
        ?float $customKesCap = null, 
        ?float $customJpCap = null
    ): array {
        if ($baseWage <= 0) {
            return [
                'jht_employer' => 0.0,
                'jht_employee' => 0.0,
                'jkm_employer' => 0.0,
                'jkk_employer' => 0.0,
                'jp_employer' => 0.0,
                'jp_employee' => 0.0,
                'bpjs_tk_total_employer' => 0.0,
                'bpjs_tk_total_employee' => 0.0,
                'bpjs_kes_employer' => 0.0,
                'bpjs_kes_employee' => 0.0,
                'total_employer_contribution' => 0.0,
                'total_employee_deduction' => 0.0,
            ];
        }

        $configTk = config('compliance.bpjs_tk');
        $configKes = config('compliance.bpjs_kesehatan');

        // 1. JHT: Employer 3.7%, Employee 2.0%
        $jhtEmployer = round($baseWage * ($configTk['jht']['employer_rate'] ?? 0.037), 2);
        $jhtEmployee = round($baseWage * ($configTk['jht']['employee_rate'] ?? 0.020), 2);

        // 2. JKM: Employer 0.3%
        $jkmEmployer = round($baseWage * ($configTk['jkm']['employer_rate'] ?? 0.003), 2);

        // 3. JKK: Employer risk grade
        $jkkRate = $configTk['jkk_rates'][$jkkGrade] ?? 0.0054;
        $jkkEmployer = round($baseWage * $jkkRate, 2);

        // 4. JP: Employer 2.0%, Employee 1.0% with ceiling cap
        $jpCap = $customJpCap ?? ($configTk['jp']['max_wage_cap'] ?? 10042300);
        $jpWageBasis = min($baseWage, $jpCap);
        $jpEmployer = round($jpWageBasis * ($configTk['jp']['employer_rate'] ?? 0.020), 2);
        $jpEmployee = round($jpWageBasis * ($configTk['jp']['employee_rate'] ?? 0.010), 2);

        $bpjsTkEmployer = $jhtEmployer + $jkmEmployer + $jkkEmployer + $jpEmployer;
        $bpjsTkEmployee = $jhtEmployee + $jpEmployee;

        // 5. BPJS Kesehatan: Employer 4.0%, Employee 1.0% with ceiling cap
        $kesCap = $customKesCap ?? ($configKes['max_wage_cap'] ?? 12000000);
        $kesWageBasis = min($baseWage, $kesCap);
        $bpjsKesEmployer = round($kesWageBasis * ($configKes['employer_rate'] ?? 0.040), 2);
        $bpjsKesEmployee = round($kesWageBasis * ($configKes['employee_rate'] ?? 0.010), 2);

        return [
            'wage_basis' => $baseWage,
            'jht_employer' => $jhtEmployer,
            'jht_employee' => $jhtEmployee,
            'jkm_employer' => $jkmEmployer,
            'jkk_employer' => $jkkEmployer,
            'jp_employer' => $jpEmployer,
            'jp_employee' => $jpEmployee,
            'bpjs_tk_total_employer' => $bpjsTkEmployer,
            'bpjs_tk_total_employee' => $bpjsTkEmployee,
            'bpjs_kes_employer' => $bpjsKesEmployer,
            'bpjs_kes_employee' => $bpjsKesEmployee,
            'total_employer_contribution' => round($bpjsTkEmployer + $bpjsKesEmployer, 2),
            'total_employee_deduction' => round($bpjsTkEmployee + $bpjsKesEmployee, 2),
        ];
    }
}

