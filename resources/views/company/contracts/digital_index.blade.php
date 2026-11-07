@extends('layouts.app')

@section('title', 'Kontrak Kerja Digital & Tanda Tangan Elektronik - ' . $company->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Kontrak Kerja Digital & E-Signature</h1>
            <p class="text-sm text-slate-500 mt-1">Pembuatan perjanjian kerja (PKWT/PKWTT), penandatanganan elektronik tersertifikasi UU ITE No. 1/2024 & verifikasi hash SHA256.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('modal-create-contract').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Kontrak Digital
            </button>
        </div>
    </div>

    <!-- Alert Banner UU ITE -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 text-blue-900 text-sm flex items-start gap-3 shadow-sm">
        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
        </svg>
        <div>
            <span class="font-bold">Keabsahan Yuridis Tanda Tangan Elektronik:</span> Dokumen kontrak dalam modul ini mematuhi ketentuan <strong>UU ITE No. 1 Tahun 2024</strong> dan <strong>PP No. 71 Tahun 2019</strong>. Setiap penandatanganan dicatat dengan alamat IP, timestamp UTC, User-Agent, serta disegel dengan <em>SHA256 Cryptographic Integrity Hash</em> anti-tamper.
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kontrak Digital</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['total'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Seluruh dokumen PKWT & PKWTT</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu TTD Karyawan</p>
            <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['pending_employee'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Dapat ditandatangani via ESS portal</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu TTD Perusahaan</p>
            <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ $summary['pending_company'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Memerlukan otorisasi Direktur/HRD</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sah & Lengkap (Fully Signed)</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['fully_signed'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Memiliki kekuatan hukum penuh</p>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Daftar Dokumen Kontrak Kerja</h3>
            <span class="text-xs text-slate-500">{{ $contracts->count() }} dokumen terdaftar</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">No. Dokumen</th>
                        <th class="px-6 py-3">Pegawai & Posisi</th>
                        <th class="px-6 py-3">Jenis & Periode</th>
                        <th class="px-6 py-3">TTD Perusahaan</th>
                        <th class="px-6 py-3">TTD Pegawai</th>
                        <th class="px-6 py-3">Integritas Hash</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($contracts as $contract)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-bold text-slate-800">{{ $contract->contract_number }}</span>
                            <div class="text-[11px] text-slate-400">Dibuat {{ $contract->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900">{{ $contract->employee->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $contract->employee->designation->name ?? $contract->employee->position ?? 'Staff' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $contract->contract_type === 'PKWTT' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $contract->contract_type }}
                            </span>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ \Carbon\Carbon::parse($contract->start_date)->format('d M Y') }}
                                @if($contract->end_date)
                                    s/d {{ \Carbon\Carbon::parse($contract->end_date)->format('d M Y') }}
                                @else
                                    (Tetap)
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($contract->company_signed_at)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Ditandatangani
                                </span>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($contract->company_signed_at)->format('d/m/Y H:i') }}</div>
                            @else
                                <span class="inline-flex items-center text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">
                                    Belum Ditandatangani
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($contract->employee_signed_at)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Ditandatangani
                                </span>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($contract->employee_signed_at)->format('d/m/Y H:i') }}</div>
                            @else
                                <span class="inline-flex items-center text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">
                                    Belum Ditandatangani
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-[11px]">
                            @if($contract->document_integrity_hash)
                                <span class="text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200" title="{{ $contract->document_integrity_hash }}">
                                    {{ substr($contract->document_integrity_hash, 0, 10) }}...{{ substr($contract->document_integrity_hash, -6) }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Belum disegel</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('company.digital-contracts.show', $contract) }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded text-xs font-medium transition">
                                Detail
                            </a>
                            <a href="{{ route('company.digital-contracts.print', $contract) }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded text-xs font-medium transition">
                                Cetak
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            Belum ada kontrak kerja digital yang dibuat. Klik tombol "Buat Kontrak Digital" untuk membuat draft perjanjian baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat Kontrak Digital -->
<div id="modal-create-contract" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-lg text-slate-900">Buat Perjanjian Kerja Digital</h3>
                <p class="text-xs text-slate-500">Standarisasi format legal PP 35/2021 & UU ITE 1/2024</p>
            </div>
            <button onclick="document.getElementById('modal-create-contract').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form action="{{ route('company.digital-contracts.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan *</label>
                <select name="employee_id" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code }}) - {{ $emp->position ?? 'Staff' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Perjanjian *</label>
                    <select name="contract_type" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border focus:ring-red-500">
                        <option value="PKWT">PKWT (Waktu Tertentu / Kontrak)</option>
                        <option value="PKWTT">PKWTT (Waktu Tidak Tertentu / Tetap)</option>
                        <option value="PROBATION">PROBATION (Masa Percobaan 3 Bln)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Surat Kontrak</label>
                    <input type="text" name="contract_number" placeholder="Otomatis digenerate jika kosong" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border focus:ring-red-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Berlaku *</label>
                    <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Berakhir (Wajib jika PKWT)</label>
                    <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border focus:ring-red-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gaji Pokok (Rp) *</label>
                    <input type="number" name="basic_salary" required min="0" step="1000" placeholder="5000000" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tunjangan Tetap (Rp)</label>
                    <input type="number" name="allowances" min="0" step="1000" value="0" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Designation</label>
                    <input type="text" name="job_title" placeholder="Contoh: Senior Software Engineer" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Kerja</label>
                    <input type="text" name="work_location" placeholder="Contoh: Jakarta Head Office" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Klausul Khusus / Catatan Tambahan (Opsional)</label>
                <textarea name="special_clauses" rows="3" placeholder="Contoh: Pekerja bersedia ditempatkan di seluruh cabang perusahaan..." class="w-full text-sm border-slate-300 rounded-lg p-2.5 border"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-create-contract').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Simpan & Terbitkan Kontrak</button>
            </div>
        </form>
    </div>
</div>
@endsection
