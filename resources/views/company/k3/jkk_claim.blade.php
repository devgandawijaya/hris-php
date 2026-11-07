<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Klaim JKK BPJS - {{ $claim->claim_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-md border border-slate-200">
        <!-- Action Buttons -->
        <div class="no-print mb-6 flex justify-between items-center pb-4 border-b border-slate-200">
            <a href="{{ route('company.k3.index') }}" class="text-xs text-slate-600 hover:text-slate-900 font-bold">&larr; Kembali ke Modul K3</a>
            <button onclick="window.print()" class="px-4 py-2 bg-[#DC2626] text-white text-xs font-bold rounded-lg shadow hover:bg-red-700 transition">
                Cetak Formulir JKK
            </button>
        </div>

        <!-- Official Header -->
        <div class="border-b-2 border-slate-900 pb-4 text-center">
            <div class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">BPJS KETENAGAKERJAAN • JAMINAN KECELAKAAN KERJA (JKK)</div>
            <h1 class="text-xl font-black text-slate-900 mt-1">FORMULIR LAPORAN KECELAKAAN KERJA {{ strtoupper(str_replace('_', ' ', $claim->stage)) }}</h1>
            <div class="text-xs font-mono font-bold text-slate-700 mt-0.5">NOMOR REGISTRASI: {{ $claim->claim_number }}</div>
        </div>

        <!-- Meta info -->
        <div class="grid grid-cols-2 gap-4 my-6 text-xs bg-slate-50 p-4 rounded-lg border border-slate-200">
            <div>
                <span class="text-slate-500">Perusahaan Pemberi Kerja:</span>
                <div class="font-bold text-slate-900">{{ $claim->incident->company->name ?? 'PT Demo Solusindo' }}</div>
            </div>
            <div>
                <span class="text-slate-500">Fasilitas Kesehatan PLKK:</span>
                <div class="font-bold text-slate-900">{{ $claim->medical_facility_name }}</div>
            </div>
            <div>
                <span class="text-slate-500">Tanggal Pengajuan:</span>
                <div class="font-bold text-slate-900">{{ $claim->submitted_at ? $claim->submitted_at->format('d F Y') : '-' }}</div>
            </div>
            <div>
                <span class="text-slate-500">Status Pemrosesan:</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">{{ $claim->claim_status }}</span>
            </div>
        </div>

        <!-- Patient Details -->
        <div class="space-y-4 text-xs">
            <h2 class="font-bold text-slate-900 uppercase border-b border-slate-200 pb-1">I. Data Tenaga Kerja (Korban)</h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <span class="text-slate-500">Nama Lengkap:</span>
                    <div class="font-bold text-slate-900">{{ $claim->incident->employee->full_name }}</div>
                </div>
                <div>
                    <span class="text-slate-500">Nomor Induk Karyawan (NIK/NIP):</span>
                    <div class="font-bold text-slate-900">{{ $claim->incident->employee->nik ?? $claim->incident->employee->employee_number }}</div>
                </div>
                <div>
                    <span class="text-slate-500">Jabatan / Departemen:</span>
                    <div class="font-bold text-slate-900">{{ $claim->incident->employee->position?->title ?? 'Staff' }} / {{ $claim->incident->employee->department?->name ?? 'General' }}</div>
                </div>
                <div>
                    <span class="text-slate-500">Nomor Telepon Seluler:</span>
                    <div class="font-bold text-slate-900">{{ $claim->incident->employee->phone ?? '-' }}</div>
                </div>
            </div>

            <h2 class="font-bold text-slate-900 uppercase border-b border-slate-200 pb-1 pt-3">II. Rincian Peristiwa & Cidera</h2>
            <div class="space-y-2">
                <div>
                    <span class="text-slate-500">Waktu & Tempat Kejadian:</span>
                    <div class="font-bold text-slate-900">{{ $claim->incident->incident_date->format('d F Y') }} ({{ $claim->incident->incident_time ?? '-' }}) di {{ $claim->incident->location }}</div>
                </div>
                <div>
                    <span class="text-slate-500">Jenis Cidera / Bagian Tubuh:</span>
                    <div class="font-bold text-slate-900">{{ $claim->incident->injury_type ?? 'Cidera Fisik Terkait Kerja' }} (Tingkat: {{ strtoupper($claim->incident->severity) }})</div>
                </div>
                <div>
                    <span class="text-slate-500">Uraian Kejadian Singkat:</span>
                    <div class="p-2.5 bg-slate-50 rounded border border-slate-200 text-slate-700 italic">{{ $claim->incident->description }}</div>
                </div>
            </div>

            <h2 class="font-bold text-slate-900 uppercase border-b border-slate-200 pb-1 pt-3">III. Santunan & Pembiayaan JKK BPJS</h2>
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-slate-50 rounded border border-slate-200">
                    <span class="text-slate-500">Estimasi Biaya Pengobatan:</span>
                    <div class="text-base font-black text-slate-900 mt-1">Rp {{ number_format($claim->estimated_medical_expense, 0, ',', '.') }}</div>
                </div>
                <div class="p-3 bg-emerald-50 rounded border border-emerald-200">
                    <span class="text-emerald-700 font-bold">Santunan STMB (Tidak Mampu Bekerja):</span>
                    <div class="text-base font-black text-emerald-900 mt-1">Rp {{ number_format($claim->temporary_disability_allowance, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-emerald-600 mt-0.5">100% Upah untuk {{ $claim->incident->lost_workdays }} hari hilang</div>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 text-xs text-center mt-12 pt-4 border-t border-slate-200">
            <div>
                <div class="text-slate-500">Pemberi Kerja / Pengurus K3</div>
                <div class="h-16"></div>
                <div class="font-bold text-slate-900 underline">( Bambang Soedarmono, M.B.A. )</div>
                <div class="text-[10px] text-slate-400">Direktur HR & K3 Perusahaan</div>
            </div>
            <div>
                <div class="text-slate-500">Petugas BPJS Ketenagakerjaan</div>
                <div class="h-16"></div>
                <div class="font-bold text-slate-900 underline">( Petugas Verifikator JKK )</div>
                <div class="text-[10px] text-slate-400">Kantor Cabang BPJS Ketenagakerjaan</div>
            </div>
        </div>
    </div>

</body>
</html>
