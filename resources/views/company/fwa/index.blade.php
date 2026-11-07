@extends('layouts.app')

@section('title', 'Flexible Working Arrangement (FWA WFH/WFO) - ' . $company->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Flexible Working Arrangement (FWA)</h1>
            <p class="text-sm text-slate-500 mt-1">Pengaturan kerja fleksibel: Jadwal WFH (Work From Home), WFO (Kantor), Hybrid, dan Dinas Luar sesuai kebijakan operasional modern.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('modal-add-fwa').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Atur Jadwal Kerja Karyawan
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Jadwal FWA</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['total_schedules'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Jadwal tercatat dalam sistem</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal WFH Disetujui</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-1">{{ $summary['approved_wfh'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Bekerja dari rumah / remote</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal WFO Disetujui</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['approved_wfo'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Bekerja di kantor (On-site)</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Persetujuan</p>
            <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['pending_approval'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Permintaan FWA baru dari ESS</p>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Daftar Jadwal & Pengajuan FWA</h3>
            <span class="text-xs text-slate-500">{{ $schedules->count() }} catatan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Karyawan</th>
                        <th class="px-6 py-3">Tanggal Kerja</th>
                        <th class="px-6 py-3">Jenis Pengaturan</th>
                        <th class="px-6 py-3">Alasan / Keterangan</th>
                        <th class="px-6 py-3">Status Persetujuan</th>
                        <th class="px-6 py-3 text-right">Aksi Manajerial</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($schedules as $fwa)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900">{{ $fwa->employee->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $fwa->employee->employee_code ?? '-' }} | {{ $fwa->employee->position ?? 'Staff' }}</div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">
                            {{ \Carbon\Carbon::parse($fwa->work_date)->translatedFormat('l, d F Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($fwa->arrangement_type === 'WFH')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">
                                    🏠 WFH (Remote)
                                </span>
                            @elseif($fwa->arrangement_type === 'WFO')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                    🏢 WFO (Kantor)
                                </span>
                            @elseif($fwa->arrangement_type === 'HYBRID')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                    🔄 Hybrid
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                    🚗 Dinas Luar
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate">
                            {{ $fwa->reason ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($fwa->approval_status === 'APPROVED')
                                <span class="inline-flex items-center text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">
                                    ✓ Disetujui
                                </span>
                                <div class="text-[11px] text-slate-400 mt-0.5">Oleh: {{ $fwa->approved_by_name ?? 'Manager' }}</div>
                            @elseif($fwa->approval_status === 'REJECTED')
                                <span class="inline-flex items-center text-xs font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded">
                                    ✕ Ditolak
                                </span>
                            @else
                                <span class="inline-flex items-center text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">
                                    Menunggu Persetujuan
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($fwa->approval_status === 'PENDING')
                            <div class="inline-flex items-center gap-1">
                                <form action="{{ route('company.fwa.approve', $fwa) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="APPROVED">
                                    <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium transition">
                                        Setujui
                                    </button>
                                </form>
                                <form action="{{ route('company.fwa.approve', $fwa) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="REJECTED">
                                    <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-medium transition">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="text-xs text-slate-400">Selesai diproses</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            Belum ada jadwal pengaturan kerja fleksibel (FWA). Klik tombol "Atur Jadwal Kerja Karyawan" untuk memulai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Atur Jadwal FWA -->
<div id="modal-add-fwa" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <h3 class="font-bold text-base text-slate-900">Penetapan Jadwal FWA Karyawan</h3>
            <button onclick="document.getElementById('modal-add-fwa').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form action="{{ route('company.fwa.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan *</label>
                <select name="employee_id" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border focus:ring-red-500">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Jadwal Kerja *</label>
                <input type="date" name="work_date" required value="{{ date('Y-m-d') }}" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Pengaturan Kerja *</label>
                <select name="arrangement_type" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                    <option value="WFH">WFH (Work From Home / Remote)</option>
                    <option value="WFO">WFO (Work From Office / Kantor)</option>
                    <option value="HYBRID">HYBRID (Kombinasi Jadwal)</option>
                    <option value="FIELD_WORK">FIELD WORK (Kunjungan Lapangan / Dinas)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Alasan Penugasan</label>
                <textarea name="reason" rows="2" placeholder="Contoh: Jadwal rotasi tim engineering WFH hari Jumat..." class="w-full text-sm border-slate-300 rounded-lg p-2.5 border"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-fwa').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>
@endsection
