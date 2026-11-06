<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Indonesian Labor & Tax Compliance Configuration (RiHRIS)
    |--------------------------------------------------------------------------
    | Sesuai UU No. 13/2003, UU No. 6/2023 (Cipta Kerja), PP No. 35/2021,
    | PP No. 36/2021, PP No. 58/2023, PMK No. 168/2023, & BPJS Regulations.
    */

    'ptkp' => [
        'TK/0' => 54000000,
        'TK/1' => 58500000,
        'TK/2' => 63000000,
        'TK/3' => 67500000,
        'K/0'  => 58500000,
        'K/1'  => 63000000,
        'K/2'  => 67500000,
        'K/3'  => 72000000,
    ],

    // Mapping PTKP ke Kategori TER Bulanan (PP 58/2023)
    'ter_category_mapping' => [
        'TK/0' => 'A',
        'TK/1' => 'A',
        'K/0'  => 'A',
        'TK/2' => 'B',
        'TK/3' => 'B',
        'K/1'  => 'B',
        'K/2'  => 'B',
        'K/3'  => 'C',
    ],

    // Tabel Tarif Efektif Rata-Rata Bulanan Kategori A (PMK 168/2023)
    'ter_table_a' => [
        ['max' => 5400000, 'rate' => 0.00],
        ['max' => 5650000, 'rate' => 0.0025],
        ['max' => 5950000, 'rate' => 0.005],
        ['max' => 6300000, 'rate' => 0.0075],
        ['max' => 6750000, 'rate' => 0.01],
        ['max' => 7500000, 'rate' => 0.0125],
        ['max' => 8550000, 'rate' => 0.015],
        ['max' => 9650000, 'rate' => 0.0175],
        ['max' => 10050000, 'rate' => 0.02],
        ['max' => 10350000, 'rate' => 0.0225],
        ['max' => 10700000, 'rate' => 0.025],
        ['max' => 11050000, 'rate' => 0.03],
        ['max' => 11600000, 'rate' => 0.035],
        ['max' => 12500000, 'rate' => 0.04],
        ['max' => 13750000, 'rate' => 0.05],
        ['max' => 15100000, 'rate' => 0.06],
        ['max' => 16950000, 'rate' => 0.07],
        ['max' => 19750000, 'rate' => 0.08],
        ['max' => 24150000, 'rate' => 0.09],
        ['max' => 26450000, 'rate' => 0.10],
        ['max' => 28000000, 'rate' => 0.11],
        ['max' => 30050000, 'rate' => 0.12],
        ['max' => 32400000, 'rate' => 0.13],
        ['max' => 35400000, 'rate' => 0.14],
        ['max' => 39100000, 'rate' => 0.15],
        ['max' => 43850000, 'rate' => 0.16],
        ['max' => 47800000, 'rate' => 0.17],
        ['max' => 51400000, 'rate' => 0.18],
        ['max' => 56300000, 'rate' => 0.19],
        ['max' => 62200000, 'rate' => 0.20],
        ['max' => 68600000, 'rate' => 0.21],
        ['max' => 77500000, 'rate' => 0.22],
        ['max' => 89000000, 'rate' => 0.23],
        ['max' => 103000000, 'rate' => 0.24],
        ['max' => 125000000, 'rate' => 0.25],
        ['max' => 157000000, 'rate' => 0.26],
        ['max' => 206000000, 'rate' => 0.27],
        ['max' => 337000000, 'rate' => 0.28],
        ['max' => 454000000, 'rate' => 0.29],
        ['max' => 550000000, 'rate' => 0.30],
        ['max' => 695000000, 'rate' => 0.31],
        ['max' => 910000000, 'rate' => 0.32],
        ['max' => 1400000000, 'rate' => 0.33],
        ['max' => PHP_INT_MAX, 'rate' => 0.34],
    ],

    // Tabel Tarif Efektif Rata-Rata Bulanan Kategori B (PMK 168/2023)
    'ter_table_b' => [
        ['max' => 6200000, 'rate' => 0.00],
        ['max' => 6500000, 'rate' => 0.0025],
        ['max' => 6850000, 'rate' => 0.005],
        ['max' => 7300000, 'rate' => 0.0075],
        ['max' => 9200000, 'rate' => 0.01],
        ['max' => 10750000, 'rate' => 0.015],
        ['max' => 11250000, 'rate' => 0.02],
        ['max' => 11600000, 'rate' => 0.025],
        ['max' => 12600000, 'rate' => 0.03],
        ['max' => 13600000, 'rate' => 0.04],
        ['max' => 14950000, 'rate' => 0.05],
        ['max' => 16400000, 'rate' => 0.06],
        ['max' => 18450000, 'rate' => 0.07],
        ['max' => 21850000, 'rate' => 0.08],
        ['max' => 26000000, 'rate' => 0.09],
        ['max' => 27700000, 'rate' => 0.10],
        ['max' => 29300000, 'rate' => 0.11],
        ['max' => 31450000, 'rate' => 0.12],
        ['max' => 33950000, 'rate' => 0.13],
        ['max' => 37100000, 'rate' => 0.14],
        ['max' => 41100000, 'rate' => 0.15],
        ['max' => 45800000, 'rate' => 0.16],
        ['max' => 49500000, 'rate' => 0.17],
        ['max' => 53800000, 'rate' => 0.18],
        ['max' => 58500000, 'rate' => 0.19],
        ['max' => 64000000, 'rate' => 0.20],
        ['max' => 71000000, 'rate' => 0.21],
        ['max' => 80000000, 'rate' => 0.22],
        ['max' => 93000000, 'rate' => 0.23],
        ['max' => 109000000, 'rate' => 0.24],
        ['max' => 129000000, 'rate' => 0.25],
        ['max' => 163000000, 'rate' => 0.26],
        ['max' => 211000000, 'rate' => 0.27],
        ['max' => 374000000, 'rate' => 0.28],
        ['max' => 459000000, 'rate' => 0.29],
        ['max' => 555000000, 'rate' => 0.30],
        ['max' => 704000000, 'rate' => 0.31],
        ['max' => 957000000, 'rate' => 0.32],
        ['max' => 1405000000, 'rate' => 0.33],
        ['max' => PHP_INT_MAX, 'rate' => 0.34],
    ],

    // Tabel Tarif Efektif Rata-Rata Bulanan Kategori C (PMK 168/2023)
    'ter_table_c' => [
        ['max' => 6600000, 'rate' => 0.00],
        ['max' => 6950000, 'rate' => 0.0025],
        ['max' => 7350000, 'rate' => 0.005],
        ['max' => 7800000, 'rate' => 0.0075],
        ['max' => 8850000, 'rate' => 0.01],
        ['max' => 9800000, 'rate' => 0.0125],
        ['max' => 10950000, 'rate' => 0.015],
        ['max' => 11200000, 'rate' => 0.0175],
        ['max' => 12050000, 'rate' => 0.02],
        ['max' => 12950000, 'rate' => 0.03],
        ['max' => 14150000, 'rate' => 0.04],
        ['max' => 15550000, 'rate' => 0.05],
        ['max' => 17050000, 'rate' => 0.06],
        ['max' => 19500000, 'rate' => 0.07],
        ['max' => 22700000, 'rate' => 0.08],
        ['max' => 26600000, 'rate' => 0.09],
        ['max' => 28100000, 'rate' => 0.10],
        ['max' => 30100000, 'rate' => 0.11],
        ['max' => 32600000, 'rate' => 0.12],
        ['max' => 35400000, 'rate' => 0.13],
        ['max' => 38900000, 'rate' => 0.14],
        ['max' => 43000000, 'rate' => 0.15],
        ['max' => 47400000, 'rate' => 0.16],
        ['max' => 51200000, 'rate' => 0.17],
        ['max' => 55800000, 'rate' => 0.18],
        ['max' => 60400000, 'rate' => 0.19],
        ['max' => 66700000, 'rate' => 0.20],
        ['max' => 74500000, 'rate' => 0.21],
        ['max' => 83200000, 'rate' => 0.22],
        ['max' => 95600000, 'rate' => 0.23],
        ['max' => 110000000, 'rate' => 0.24],
        ['max' => 134000000, 'rate' => 0.25],
        ['max' => 169000000, 'rate' => 0.26],
        ['max' => 221000000, 'rate' => 0.27],
        ['max' => 390000000, 'rate' => 0.28],
        ['max' => 463000000, 'rate' => 0.29],
        ['max' => 561000000, 'rate' => 0.30],
        ['max' => 709000000, 'rate' => 0.31],
        ['max' => 965000000, 'rate' => 0.32],
        ['max' => 1419000000, 'rate' => 0.33],
        ['max' => PHP_INT_MAX, 'rate' => 0.34],
    ],

    // Lapisan Tarif Pasal 17 Ayat 1 Huruf a UU HPP
    'pasal_17_brackets' => [
        ['limit' => 60000000, 'rate' => 0.05],
        ['limit' => 250000000, 'rate' => 0.15],
        ['limit' => 500000000, 'rate' => 0.25],
        ['limit' => 5000000000, 'rate' => 0.30],
        ['limit' => PHP_INT_MAX, 'rate' => 0.35],
    ],

    // Konfigurasi BPJS Ketenagakerjaan
    'bpjs_tk' => [
        'jht' => [
            'employer_rate' => 0.037, // 3.7%
            'employee_rate' => 0.020, // 2.0%
        ],
        'jkm' => [
            'employer_rate' => 0.003, // 0.3%
        ],
        'jkk_rates' => [
            'very_low'  => 0.0024, // 0.24%
            'low'       => 0.0054, // 0.54%
            'medium'    => 0.0089, // 0.89%
            'high'      => 0.0127, // 1.27%
            'very_high' => 0.0174, // 1.74%
        ],
        'jp' => [
            'employer_rate' => 0.020, // 2.0%
            'employee_rate' => 0.010, // 1.0%
            'max_wage_cap'  => 10042300, // Batas upah JP (dapat diperbarui)
        ],
    ],

    // Konfigurasi BPJS Kesehatan
    'bpjs_kesehatan' => [
        'employer_rate' => 0.040, // 4.0%
        'employee_rate' => 0.010, // 1.0%
        'max_wage_cap'  => 12000000, // Batas atas perhitungan iuran
    ],

    // Ketentuan Lembur PP No. 35/2021
    'overtime' => [
        'hourly_rate_divisor' => 173, // 1/173 x Upah Sebulan
        'workday_first_hour_multiplier' => 1.5,
        'workday_next_hour_multiplier'  => 2.0,
    ],
];

