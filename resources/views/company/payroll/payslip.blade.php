<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji — {{ $payroll->employee->full_name }} — {{ $payroll->payrollPeriod->name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0f172a;
            background-color: #f8fafc;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 1rem !important;
            }
            @page {
                size: A4;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6">

    <!-- Top Action Bar (Screen Only) -->
    <div class="max-w-[800px] mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Official Payslip Document Sheet -->
    <div class="max-w-[800px] mx-auto bg-white rounded-2xl border border-slate-200/90 shadow-lg p-8 sm:p-10 print-container">

        <!-- Company Letterhead & Document Title -->
        <div class="flex items-start justify-between pb-6 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#DC2626] text-white flex items-center justify-center font-black text-sm">
                        Ri
                    </div>
                    <div>
                        <div class="text-lg font-black text-slate-900 tracking-tight uppercase">{{ $company?->name ?? 'PT SOLUSI DIGITAL NUSANTARA' }}</div>
                        <div class="text-[11px] text-slate-500">{{ $company?->address ?? 'Cyber 2 Tower, Kuningan Barat, Jakarta Selatan' }} • Telp: {{ $company?->phone ?? '(021) 555-0199' }}</div>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <div class="text-xs font-black text-[#DC2626] uppercase tracking-widest">SLIP GAJI KARYAWAN</div>
                <div class="text-sm font-bold text-slate-800 mt-0.5">{{ $payroll->payrollPeriod->name }}</div>
                <div class="text-[11px] text-slate-500">Tgl Pembayaran: {{ $payroll->payrollPeriod->payment_date->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Employee Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-5 border-b border-slate-200 text-xs">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Nama Karyawan</div>
                <div class="font-bold text-slate-900 mt-0.5">{{ $payroll->employee->full_name }}</div>
                <div class="text-[11px] text-slate-500">NIK: {{ $payroll->employee->employee_number }}</div>
            </div>

            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Jabatan / Bagian</div>
                <div class="font-bold text-slate-900 mt-0.5">{{ $payroll->employee->position?->title ?? '-' }}</div>
                <div class="text-[11px] text-slate-500">{{ $payroll->employee->department?->name ?? 'Umum' }}</div>
            </div>

            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Status Pajak & NPWP</div>
                <div class="font-bold text-slate-900 mt-0.5">PTKP {{ $payroll->employee->tax_ptkp_status ?? 'TK/0' }}</div>
                <div class="text-[11px] text-slate-500">{{ $payroll->employee->npwp ?: 'Non-NPWP (+20%)' }}</div>
            </div>

            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Rekening Bank</div>
                <div class="font-bold text-slate-900 mt-0.5">{{ $payroll->employee->bank_name ?? 'Bank BCA' }}</div>
                <div class="text-[11px] text-slate-500">{{ $payroll->employee->bank_account_number ?? '-' }}</div>
            </div>
        </div>

        <!-- 2-Column Earnings & Deductions Table -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 py-6">

            <!-- 1. Penghasilan (Earnings) -->
            <div>
                <div class="pb-2 border-b-2 border-slate-900 flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-900">A. Penghasilan (Earnings)</span>
                    <span class="text-[11px] text-slate-400 font-semibold">Rupiah (IDR)</span>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 flex justify-between">
                        <span class="text-slate-700">Gaji Pokok (Basic Salary)</span>
                        <span class="font-semibold text-slate-900">{{ number_format($payroll->basic_salary, 0, ',', '.') }}</span>
                    </div>

                    @if(!empty($breakdown['earnings']['allowances_itemized']))
                        @foreach($breakdown['earnings']['allowances_itemized'] as $item)
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-700">{{ $item['name'] }}</span>
                                <span class="font-semibold text-slate-900">{{ number_format($item['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    @elseif($payroll->allowances_total > 0)
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-700">Total Tunjangan</span>
                            <span class="font-semibold text-slate-900">{{ number_format($payroll->allowances_total, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if($payroll->overtime_pay > 0)
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-700">Upah Lembur (PP 35/2021)</span>
                            <span class="font-semibold text-slate-900">{{ number_format($payroll->overtime_pay, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if($payroll->reimbursement_pay > 0)
                        <div class="py-2.5 flex justify-between text-emerald-700 font-medium">
                            <span>Klaim Reimbursement</span>
                            <span>{{ number_format($payroll->reimbursement_pay, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 mt-2 border-t-2 border-slate-300 flex justify-between text-xs font-black text-slate-900">
                    <span>TOTAL PENGHASILAN BRUTO</span>
                    <span>Rp {{ number_format($payroll->gross_salary + $payroll->reimbursement_pay, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 2. Potongan (Deductions) -->
            <div>
                <div class="pb-2 border-b-2 border-slate-900 flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-900">B. Potongan (Deductions)</span>
                    <span class="text-[11px] text-slate-400 font-semibold">Rupiah (IDR)</span>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 flex justify-between">
                        <div>
                            <span class="text-slate-700">BPJS Ketenagakerjaan (JHT 2%)</span>
                            <span class="text-[10px] text-slate-400 block">Jaminan Hari Tua</span>
                        </div>
                        <span class="font-semibold text-slate-900">
                            {{ number_format($breakdown['deductions']['bpjs_tk_jht_employee'] ?? round($payroll->bpjs_tk_employee * 0.666, 0), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="py-2.5 flex justify-between">
                        <div>
                            <span class="text-slate-700">BPJS Ketenagakerjaan (JP 1%)</span>
                            <span class="text-[10px] text-slate-400 block">Jaminan Pensiun (Capped)</span>
                        </div>
                        <span class="font-semibold text-slate-900">
                            {{ number_format($breakdown['deductions']['bpjs_tk_jp_employee'] ?? round($payroll->bpjs_tk_employee * 0.334, 0), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="py-2.5 flex justify-between">
                        <div>
                            <span class="text-slate-700">BPJS Kesehatan (1%)</span>
                            <span class="text-[10px] text-slate-400 block">Iuran Pekerja</span>
                        </div>
                        <span class="font-semibold text-slate-900">
                            {{ number_format($payroll->bpjs_kes_employee, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="py-2.5 flex justify-between">
                        <div>
                            <span class="text-slate-700 font-medium">PPh 21 TER (PP 58/2023)</span>
                            <span class="text-[10px] text-slate-400 block">Tarif Efektif Rata-Rata</span>
                        </div>
                        <span class="font-semibold text-amber-700">
                            {{ number_format($payroll->pph21_tax, 0, ',', '.') }}
                        </span>
                    </div>

                    @if(!empty($breakdown['deductions']['deductions_itemized']))
                        @foreach($breakdown['deductions']['deductions_itemized'] as $item)
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-700">{{ $item['name'] }}</span>
                                <span class="font-semibold text-slate-900">{{ number_format($item['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="pt-3 mt-2 border-t-2 border-slate-300 flex justify-between text-xs font-black text-rose-600">
                    <span>TOTAL POTONGAN</span>
                    <span>- Rp {{ number_format($payroll->deductions_total + $payroll->pph21_tax, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Take Home Pay Highlight Banner -->
        <div class="my-4 p-5 rounded-xl bg-red-50 border-2 border-[#DC2626] flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <div>
                <div class="text-xs font-black uppercase tracking-wider text-[#991B1B]">GAJI BERSIH DITERIMA (TAKE HOME PAY)</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Total bruto dikurangi iuran jaminan sosial & pemotongan pajak terutang</div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-[#DC2626] tracking-tight">
                Rp {{ number_format($payroll->take_home_pay, 0, ',', '.') }}
            </div>
        </div>

        <!-- Employer Social Security Contribution Transparency -->
        <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 text-[11px] text-slate-600">
            <span class="font-bold text-slate-800">Manfaat Perlindungan Ditanggung Perusahaan (Employer Benefits):</span>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mt-2 text-[10px]">
                <div>BPJS TK JHT (3.7%): <strong class="text-slate-900">Rp {{ number_format($breakdown['employer_contributions']['bpjs_tk_jht_employer'] ?? 0, 0, ',', '.') }}</strong></div>
                <div>BPJS TK JKK: <strong class="text-slate-900">Rp {{ number_format($breakdown['employer_contributions']['bpjs_tk_jkk_employer'] ?? 0, 0, ',', '.') }}</strong></div>
                <div>BPJS TK JKM (0.3%): <strong class="text-slate-900">Rp {{ number_format($breakdown['employer_contributions']['bpjs_tk_jkm_employer'] ?? 0, 0, ',', '.') }}</strong></div>
                <div>BPJS TK JP (2.0%): <strong class="text-slate-900">Rp {{ number_format($breakdown['employer_contributions']['bpjs_tk_jp_employer'] ?? 0, 0, ',', '.') }}</strong></div>
                <div>BPJS Kes (4.0%): <strong class="text-slate-900">Rp {{ number_format($payroll->bpjs_kes_employer, 0, ',', '.') }}</strong></div>
            </div>
        </div>

        <!-- Signatures & Verification Badge -->
        <div class="mt-8 pt-6 border-t border-slate-200 grid grid-cols-2 gap-8 text-center text-xs">
            <div>
                <div class="text-slate-400 font-semibold mb-12">Disetujui Oleh (Finance / HRD):</div>
                <div class="font-bold text-slate-900 border-t border-slate-300 pt-2 inline-block px-8">
                    Departemen Keuangan
                </div>
            </div>

            <div>
                <div class="text-slate-400 font-semibold mb-12">Diterima Oleh (Karyawan):</div>
                <div class="font-bold text-slate-900 border-t border-slate-300 pt-2 inline-block px-8">
                    {{ $payroll->employee->full_name }}
                </div>
            </div>
        </div>

        <!-- Official Footer Note -->
        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
            <span>Dokumen ini sah dan diterbitkan secara digital oleh sistem RiHRIS.</span>
            <span>Verifikasi ID: #PAY-{{ str_pad($payroll->id, 8, '0', STR_PAD_LEFT) }}-{{ strtoupper(substr(md5($payroll->id . $payroll->created_at), 0, 6)) }}</span>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>

