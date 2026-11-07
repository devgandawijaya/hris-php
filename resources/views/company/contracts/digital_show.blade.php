@extends('layouts.app')

@section('title', 'Detail Kontrak Kerja Digital - ' . $contract->contract_number)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('company.digital-contracts.index') }}" class="p-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-800">{{ $contract->contract_number }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $contract->contract_type === 'PKWTT' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $contract->contract_type }}
                    </span>
                    @if($contract->company_signed_at && $contract->employee_signed_at)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                            ✓ Sah & Ditandatangani Penuh
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                            Menunggu Penandatanganan
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-1">Dokumen Perjanjian Kerja Karyawan: {{ $contract->employee->name ?? '-' }} ({{ $contract->employee->employee_code ?? '-' }})</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('company.digital-contracts.print', $contract) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Dokumen Resmi
            </a>

            @if(!$contract->company_signed_at)
            <button onclick="document.getElementById('modal-sign-company').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                Tandatangani Sebagai Perusahaan
            </button>
            @endif
        </div>
    </div>

    <!-- Security & Audit Trail Card -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-2xl p-6 text-white shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-indigo-800/60">
            <div>
                <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sertifikasi Integritas UU ITE No. 1/2024 & PP 71/2019
                </div>
                <h3 class="text-lg font-bold text-white mt-1">Cryptographic Digital Signature Seal</h3>
                <p class="text-xs text-indigo-200 mt-0.5">Segel kriptografis anti-pemalsuan dan verifikasi keutuhan isi naskah perjanjian kerja.</p>
            </div>
            <div class="bg-indigo-900/60 px-4 py-2 rounded-xl border border-indigo-700/60 font-mono text-xs text-indigo-200">
                Status: <span class="text-white font-bold">{{ ($contract->company_signed_at && $contract->employee_signed_at) ? 'COMPLETED' : 'IN_PROGRESS' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6">
            <!-- Company Signer -->
            <div class="bg-indigo-900/40 p-4 rounded-xl border border-indigo-800/40">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase text-indigo-300">Pihak Pertama (Perusahaan)</span>
                    @if($contract->company_signed_at)
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-[11px] font-bold">✓ Tervalidasi</span>
                    @else
                        <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 rounded text-[11px] font-bold">Pending Sign</span>
                    @endif
                </div>
                <div class="text-sm font-bold text-white">{{ $contract->company_signer_name ?? $company->name }}</div>
                <div class="text-xs text-indigo-200 mt-1">Jabatan: {{ $contract->company_signer_title ?? 'Direktur / HR Authorized' }}</div>
                <div class="text-xs text-indigo-300 mt-2 font-mono">
                    Waktu: {{ $contract->company_signed_at ? \Carbon\Carbon::parse($contract->company_signed_at)->format('Y-m-d H:i:s T') : '-' }}<br>
                    IP: {{ $contract->company_signer_ip ?? '-' }}
                </div>
            </div>

            <!-- Employee Signer -->
            <div class="bg-indigo-900/40 p-4 rounded-xl border border-indigo-800/40">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase text-indigo-300">Pihak Kedua (Karyawan)</span>
                    @if($contract->employee_signed_at)
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-[11px] font-bold">✓ Tervalidasi</span>
                    @else
                        <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 rounded text-[11px] font-bold">Pending Sign</span>
                    @endif
                </div>
                <div class="text-sm font-bold text-white">{{ $contract->employee->name ?? '-' }}</div>
                <div class="text-xs text-indigo-200 mt-1">NIK/ID: {{ $contract->employee->employee_code ?? '-' }}</div>
                <div class="text-xs text-indigo-300 mt-2 font-mono">
                    Waktu: {{ $contract->employee_signed_at ? \Carbon\Carbon::parse($contract->employee_signed_at)->format('Y-m-d H:i:s T') : '-' }}<br>
                    IP: {{ $contract->employee_signer_ip ?? '-' }}
                </div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-indigo-800/40 font-mono text-[11px] text-indigo-300 break-all">
            <span class="text-indigo-400 font-bold">SHA256 Hash Keutuhan Dokumen:</span>
            <div class="mt-1 bg-black/30 p-2.5 rounded border border-indigo-800/40 text-emerald-400">
                {{ $contract->document_integrity_hash ?? 'Hash akan dihasilkan otomatis setelah dokumen diterbitkan atau ditandatangani.' }}
            </div>
        </div>
    </div>

    <!-- Contract Content Document Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 max-w-4xl mx-auto space-y-6 text-slate-800 leading-relaxed text-sm">
        <div class="text-center pb-6 border-b border-slate-200">
            <h2 class="text-xl font-bold uppercase tracking-wider text-slate-900">
                SURAT PERJANJIAN KERJA
                @if($contract->contract_type === 'PKWTT')
                    WAKTU TIDAK TERTENTU (PKWTT)
                @else
                    WAKTU TERTENTU (PKWT)
                @endif
            </h2>
            <p class="font-mono text-sm text-slate-500 mt-1">Nomor: {{ $contract->contract_number }}</p>
        </div>

        <p>Pada hari ini, bertempat di <strong>{{ $contract->work_location ?? $company->city ?? 'Jakarta' }}</strong>, telah dibuat dan disepakati perjanjian kerja oleh dan antara:</p>

        <div class="space-y-4 pl-4 border-l-2 border-slate-200">
            <div>
                <p class="font-bold text-slate-900">I. PIHAK PERTAMA (PEMBERI KERJA):</p>
                <table class="text-sm mt-1">
                    <tr><td class="w-36 text-slate-500 py-0.5">Nama Perusahaan</td><td class="py-0.5">: <strong>{{ $company->name }}</strong></td></tr>
                    <tr><td class="text-slate-500 py-0.5">Alamat</td><td class="py-0.5">: {{ $company->address ?? '-' }}</td></tr>
                    <tr><td class="text-slate-500 py-0.5">Diwakili Oleh</td><td class="py-0.5">: {{ $contract->company_signer_name ?? 'Manajemen Perusahaan' }} ({{ $contract->company_signer_title ?? 'Authorized Officer' }})</td></tr>
                </table>
            </div>

            <div>
                <p class="font-bold text-slate-900">II. PIHAK KEDUA (PEKERJA):</p>
                <table class="text-sm mt-1">
                    <tr><td class="w-36 text-slate-500 py-0.5">Nama Lengkap</td><td class="py-0.5">: <strong>{{ $contract->employee->name ?? '-' }}</strong></td></tr>
                    <tr><td class="text-slate-500 py-0.5">Nomor Identitas (NIK)</td><td class="py-0.5">: {{ $contract->employee->nik ?? $contract->employee->employee_code ?? '-' }}</td></tr>
                    <tr><td class="text-slate-500 py-0.5">Jabatan / Posisi</td><td class="py-0.5">: {{ $contract->job_title ?? $contract->employee->designation->name ?? $contract->employee->position ?? 'Staff' }}</td></tr>
                    <tr><td class="text-slate-500 py-0.5">Lokasi Penempatan</td><td class="py-0.5">: {{ $contract->work_location ?? 'Kantor Perusahaan' }}</td></tr>
                </table>
            </div>
        </div>

        <p>Kedua belah pihak sepakat mengikatkan diri dalam Perjanjian Kerja dengan syarat dan ketentuan sebagaimana diatur dalam pasal-pasal berikut:</p>

        <!-- Terms Clauses -->
        <div class="space-y-4">
            <div>
                <h4 class="font-bold text-slate-900">Pasal 1: Hubungan Kerja dan Masa Berlaku</h4>
                <p class="text-slate-600 mt-1">
                    Perjanjian ini berlaku terhitung mulai tanggal <strong>{{ \Carbon\Carbon::parse($contract->start_date)->translatedFormat('d F Y') }}</strong>
                    @if($contract->end_date)
                        sampai dengan tanggal <strong>{{ \Carbon\Carbon::parse($contract->end_date)->translatedFormat('d F Y') }}</strong>.
                    @else
                        untuk jangka waktu tidak tertentu (PKWTT) sesuai ketentuan Undang-Undang Ketenagakerjaan.
                    @endif
                </p>
            </div>

            <div>
                <h4 class="font-bold text-slate-900">Pasal 2: Tugas dan Tanggung Jawab</h4>
                <p class="text-slate-600 mt-1">
                    PIHAK KEDUA menjalankan tugas sebagai <strong>{{ $contract->job_title ?? 'Karyawan' }}</strong> dengan penuh integritas, keahlian, dan kepatuhan terhadap peraturan perusahaan serta SOP yang berlaku.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-slate-900">Pasal 3: Kompensasi dan Remunerasi</h4>
                <div class="mt-1 bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-1">
                    <div class="flex justify-between"><span>Gaji Pokok Bulanan:</span><span class="font-bold">Rp {{ number_format($contract->basic_salary, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span>Tunjangan Tetap:</span><span class="font-bold">Rp {{ number_format($contract->allowances, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between border-t border-slate-200 pt-1 text-slate-900 font-bold"><span>Total Remunerasi Tetap:</span><span>Rp {{ number_format($contract->basic_salary + $contract->allowances, 0, ',', '.') }}</span></div>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-900">Pasal 4: Hak dan Kewajiban & Perlindungan Jamsostek</h4>
                <p class="text-slate-600 mt-1">
                    PIHAK KEDUA berhak atas perlindungan Jaminan Sosial Ketenagakerjaan (BPJS Ketenagakerjaan: JKK, JKM, JHT, JP) dan Jaminan Kesehatan (BPJS Kesehatan) sesuai peraturan perundang-undangan yang berlaku di Republik Indonesia.
                </p>
            </div>

            @if($contract->special_clauses)
            <div>
                <h4 class="font-bold text-slate-900">Pasal 5: Klausul Khusus</h4>
                <div class="text-slate-600 mt-1 whitespace-pre-line bg-amber-50/50 p-3 rounded-lg border border-amber-200">{{ $contract->special_clauses }}</div>
            </div>
            @endif

            <div>
                <h4 class="font-bold text-slate-900">Pasal {{ $contract->special_clauses ? '6' : '5' }}: Keabsahan Tanda Tangan Elektronik</h4>
                <p class="text-slate-600 mt-1">
                    Kedua belah pihak mengakui bahwa penandatanganan elektronik dokumen ini sah dan mengikat secara hukum sesuai dengan UU ITE No. 1 Tahun 2024 dan PP No. 71 Tahun 2019.
                </p>
            </div>
        </div>

        <!-- Signatures Display in Document -->
        <div class="pt-8 grid grid-cols-2 gap-8 border-t border-slate-200 text-center">
            <div class="space-y-2">
                <p class="font-semibold text-slate-700">PIHAK PERTAMA<br>{{ $company->name }}</p>
                <div class="h-28 flex flex-col items-center justify-center border border-dashed border-slate-300 rounded-lg bg-slate-50 p-2">
                    @if($contract->company_signed_at)
                        <span class="text-xs font-mono font-bold text-emerald-700">DITANDATANGANI SECARA ELEKTRONIK</span>
                        <span class="text-[11px] text-slate-500 mt-1">{{ $contract->company_signer_name }}</span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $contract->company_signed_at }}</span>
                    @else
                        <span class="text-xs text-slate-400 italic">Belum ditandatangani</span>
                    @endif
                </div>
                <p class="font-bold text-slate-800">{{ $contract->company_signer_name ?? 'Direktur / HR Head' }}</p>
            </div>

            <div class="space-y-2">
                <p class="font-semibold text-slate-700">PIHAK KEDUA<br>PEKERJA</p>
                <div class="h-28 flex flex-col items-center justify-center border border-dashed border-slate-300 rounded-lg bg-slate-50 p-2">
                    @if($contract->employee_signed_at)
                        <span class="text-xs font-mono font-bold text-emerald-700">DITANDATANGANI SECARA ELEKTRONIK</span>
                        <span class="text-[11px] text-slate-500 mt-1">{{ $contract->employee->name }}</span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $contract->employee_signed_at }}</span>
                    @else
                        <span class="text-xs text-slate-400 italic">Menunggu TTD Karyawan via ESS</span>
                    @endif
                </div>
                <p class="font-bold text-slate-800">{{ $contract->employee->name ?? 'Karyawan' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tandatangani Sebagai Perusahaan -->
<div id="modal-sign-company" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <h3 class="font-bold text-base text-slate-900">Tanda Tangan Elektronik Perusahaan</h3>
            <button onclick="document.getElementById('modal-sign-company').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form action="{{ route('company.digital-contracts.sign-company', $contract) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pejabat Penandatangan *</label>
                <input type="text" name="signer_name" required value="{{ auth()->user()->name ?? 'HR Director' }}" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan Pejabat *</label>
                <input type="text" name="signer_title" required value="Direktur HR & Legal" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
            </div>
            <div class="bg-blue-50 p-3 rounded-lg border border-blue-200 text-xs text-blue-800">
                Dengan menekan tombol tanda tangan, sistem akan mencatat timestamp, IP address, dan menghasilkan hash verifikasi kriptografis UU ITE.
            </div>
            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-sign-company').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">Bubuhkan Tanda Tangan</button>
            </div>
        </form>
    </div>
</div>
@endsection
