@extends('layouts.app')

@section('title', 'Hubungan Industrial & Disiplin Karyawan (SP1-SP3)')

@section('content')
<div class="space-y-6" x-data="{ 
    openWarningModal: false, 
    openRevokeModal: false, 
    selectedWarningId: '', 
    selectedLetterNumber: '', 
    selectedEmployeeName: '' 
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Hubungan Industrial & Surat Peringatan (SP)</h1>
            <p class="text-sm text-slate-500 mt-1">Pengelolaan disiplin kerja, pembinaan, dan penerbitan Surat Peringatan sesuai PP 35/2021 & Pasal 161 UU Ketenagakerjaan.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openWarningModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="file-warning" class="w-4 h-4"></i>
                Terbitkan Surat Peringatan
            </button>
        </div>
    </div>

    <!-- Legal Compliance Alert Banner -->
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3 text-xs leading-relaxed">
        <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
        <div>
            <span class="font-bold">Ketentuan Hukum Pasal 161 UU Ketenagakerjaan jo. PP No. 35/2021:</span>
            Masa berlaku Surat Peringatan (SP 1, SP 2, SP 3) adalah masing-masing paling lama <strong>6 (enam) bulan</strong> sejak tanggal diterbitkan. RiHRIS secara otomatis menetapkan batas akhir kedaluwarsa 6 bulan untuk setiap surat peringatan yang diterbitkan.
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Diterbitkan</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_warnings']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Semua riwayat SP tercatat</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">SP Aktif Berjalan</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['active_warnings']) }}</div>
            <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Masa berlaku &le; 6 bulan</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Peringatan Pertama (SP 1)</div>
            <div class="text-2xl font-black text-yellow-600 mt-1">{{ number_format($stats['sp1_count']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Tahap pembinaan awal</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Peringatan Terakhir (SP 3)</div>
            <div class="text-2xl font-black text-red-600 mt-1">{{ number_format($stats['sp3_count']) }}</div>
            <div class="text-[10px] text-red-600 font-semibold mt-0.5">Peringatan terakhir / PHK</div>
        </div>
    </div>

    <!-- Warnings Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Daftar Surat Peringatan Karyawan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pantau status, masa berlaku, dan tindakan korektif karyawan.</p>
            </div>

            <!-- Status Filter -->
            <form action="{{ route('company.disciplinary.index') }}" method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Status SP</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Dicabut (Revoked)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kedaluwarsa (Expired)</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Surat & Tingkat</th>
                        <th class="py-3 px-4">Karyawan</th>
                        <th class="py-3 px-4">Alasan Pelanggaran</th>
                        <th class="py-3 px-4">Tgl Terbit & Masa Berlaku</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Pejabat Berwenang</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($warnings as $warning)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $warning->letter_number }}</div>
                                <div class="mt-1">
                                    @if($warning->warning_level === 'sp1')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                                            SP 1 (Pertama)
                                        </span>
                                    @elseif($warning->warning_level === 'sp2')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-orange-100 text-orange-800">
                                            SP 2 (Kedua)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-red-100 text-red-800">
                                            SP 3 (Terakhir)
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $warning->employee?->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $warning->employee?->employee_code }} &bull; {{ $warning->employee?->department?->name ?? 'Dept -' }}</div>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <div class="text-slate-800 font-medium truncate" title="{{ $warning->reason }}">{{ $warning->reason }}</div>
                                @if($warning->action_plan)
                                    <div class="text-[10px] text-slate-400 mt-0.5 truncate" title="{{ $warning->action_plan }}">
                                        Target: {{ $warning->action_plan }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div>{{ $warning->issued_date->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">s/d {{ $warning->valid_until->format('d/m/Y') }}</div>
                                @if($warning->status === 'active')
                                    @php
                                        $diffDays = now()->diffInDays($warning->valid_until, false);
                                    @endphp
                                    <div class="text-[10px] {{ $diffDays > 30 ? 'text-slate-500' : 'text-amber-600 font-bold' }}">
                                        @if($diffDays > 0)
                                            Sisa {{ $diffDays }} hari lagi
                                        @else
                                            Kedaluwarsa hari ini
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($warning->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @elseif($warning->status === 'revoked')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Dicabut
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Kedaluwarsa
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-slate-700">{{ $warning->authorizedBy?->name ?? 'HR Director' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $warning->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($warning->status === 'active')
                                    <button 
                                        @click="openRevokeModal = true; selectedWarningId = '{{ $warning->id }}'; selectedLetterNumber = '{{ $warning->letter_number }}'; selectedEmployeeName = '{{ $warning->employee?->full_name }}'"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-slate-600 hover:text-red-700 hover:bg-red-50 font-bold transition text-xs border border-slate-200">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        Cabut SP
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Belum ada catatan Surat Peringatan yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($warnings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $warnings->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Issue Warning Letter -->
    <div x-show="openWarningModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openWarningModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    Terbitkan Surat Peringatan (SP)
                </div>
                <button @click="openWarningModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.disciplinary.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan Terlapor --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->department?->name ?? 'Dept' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tingkat Surat Peringatan <span class="text-red-500">*</span></label>
                        <select name="warning_level" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="sp1">SP 1 (Peringatan Pertama)</option>
                            <option value="sp2">SP 2 (Peringatan Kedua)</option>
                            <option value="sp3">SP 3 (Peringatan Ketiga / PHK)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Terbit <span class="text-red-500">*</span></label>
                        <input type="date" name="issued_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-slate-600">
                    <span class="font-bold text-slate-800">Masa Berlaku Otomatis:</span> 6 Bulan sejak tanggal terbit sesuai Pasal 161 UU Ketenagakerjaan.
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Pelanggaran & Pasal PKB/PP <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Terlambat hadir lebih dari 5 kali dalam 1 bulan tanpa keterangan sah melanggar Pasal 12 PKB Perusahaan..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Perbaikan / Action Plan (Opsional)</label>
                    <textarea name="action_plan" rows="2" placeholder="Contoh: Wajib menjaga ketepatan waktu hadir 100% dan melapor ke Supervisor mingguan..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openWarningModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#DC2626] hover:bg-[#991B1B] rounded-xl shadow-sm transition">
                        Terbitkan Surat Peringatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Revoke Warning Letter -->
    <div x-show="openRevokeModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openRevokeModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="rotate-ccw" class="w-5 h-5 text-amber-600"></i>
                    Cabut Surat Peringatan
                </div>
                <button @click="openRevokeModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('disciplinary') }}/' + selectedWarningId + '/revoke'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="text-xs text-slate-600">
                    Apakah Anda yakin ingin mencabut <strong class="text-slate-900" x-text="selectedLetterNumber"></strong> atas nama <strong class="text-slate-900" x-text="selectedEmployeeName"></strong> sebelum masa berlaku habis?
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Pencabutan / Kinerja Membaik</label>
                    <textarea name="reason" rows="3" placeholder="Contoh: Karyawan menunjukkan perbaikan disiplin signifikan dan telah menyelesaikan program konseling..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openRevokeModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition">
                        Konfirmasi Pencabutan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
