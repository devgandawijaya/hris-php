@extends('layouts.app')

@section('title', 'Surat Perintah Lembur (SPL PP 35/2021)')

@section('content')
<div class="space-y-6" x-data="{ splModal: false, verifyModal: false, activeOrder: {} }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-100 text-red-800 border border-red-200">
                    Statutory Overtime
                </span>
                <span class="text-xs text-slate-400">• PP No. 35 Tahun 2021 Pasal 26</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Surat Perintah Lembur (SPL) & Batas Waktu</h1>
            <p class="text-xs text-slate-500 mt-1">Validasi kepatuhan batas maksimal lembur 4 jam/hari dan 18 jam/minggu dengan persetujuan tertulis pekerja.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="splModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Terbitkan SPL Baru
            </button>
        </div>
    </div>

    <!-- Statutory Rules Alert -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900 flex items-start gap-3">
        <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Hukum Pasal 26 PP No. 35 Tahun 2021:</span>
            <ul class="list-disc list-inside space-y-0.5 text-amber-800">
                <li>Waktu lembur hari kerja maksimal <strong>4 (empat) jam per hari</strong>.</li>
                <li>Akumulasi lembur hari kerja maksimal <strong>18 (delapan belas) jam per minggu</strong>.</li>
                <li>Wajib memperoleh <strong>persetujuan tertulis</strong> dari pekerja bersangkutan (secara fisik atau tanda tangan digital).</li>
            </ul>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Menunggu Persetujuan Atasan</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $complianceStats['pending_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Memerlukan otorisasi manajerial</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Disetujui & Siap Dijalankan</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $complianceStats['approved_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Siap direkonsiliasi jam aktual</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Lembur Selesai & Terverifikasi</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-700">{{ $complianceStats['completed_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Telah siap masuk ke proses payroll</div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Buku Register Surat Perintah Lembur</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">No. SPL / Tanggal</th>
                        <th class="px-4 py-3 font-bold">Karyawan Ditugaskan</th>
                        <th class="px-4 py-3 font-bold">Rencana Waktu Lembur</th>
                        <th class="px-4 py-3 font-bold">Uraian Tugas Pekerjaan</th>
                        <th class="px-4 py-3 font-bold">Persetujuan Pekerja</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 font-mono">{{ $order->order_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $order->overtime_date->format('d M Y') }}</div>
                                @if($order->is_day_off)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Hari Libur</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $order->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $order->employee->department?->name ?? 'General' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ substr($order->start_time, 0, 5) }} - {{ substr($order->end_time, 0, 5) }}</div>
                                <div class="text-[11px] text-slate-500">Rencana: {{ $order->planned_duration_hours }} jam</div>
                                @if($order->actual_duration_hours)
                                    <div class="text-[11px] font-bold text-emerald-700">Aktual: {{ $order->actual_duration_hours }} jam</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 max-w-xs text-slate-700 truncate" title="{{ $order->reason_task_description }}">
                                {{ $order->reason_task_description }}
                            </td>
                            <td class="px-4 py-3">
                                @if($order->employee_consented_at)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1 w-max">
                                        <i data-lucide="check" class="w-3 h-3"></i> Setuju
                                    </span>
                                    <div class="text-[9px] text-slate-400 mt-0.5">{{ $order->employee_consented_at->format('d/m H:i') }}</div>
                                @else
                                    <form action="{{ route('company.spl.consent', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:underline font-bold">
                                            + Rekam Setuju
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($order->status === 'completed_verified')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Verified</span>
                                @elseif($order->status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Approved</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                @if($order->status === 'pending_approval')
                                    <form action="{{ route('company.spl.approve', $order->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-bold text-xs">
                                            Setujui
                                        </button>
                                    </form>
                                @elseif($order->status === 'approved')
                                    <button @click="activeOrder = {{ json_encode($order) }}; verifyModal = true" class="px-2.5 py-1 bg-slate-900 hover:bg-black text-white rounded font-bold text-xs">
                                        Verifikasi Jam
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">Belum ada surat perintah lembur yang diterbitkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>

    <!-- Create SPL Modal -->
    <div x-show="splModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Terbitkan Surat Perintah Lembur (SPL)</h3>
                <button @click="splModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('company.spl.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Karyawan Ditugaskan</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->department?->name ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Lembur</label>
                        <input type="date" name="overtime_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori Hari</label>
                        <label class="flex items-center gap-2 mt-2">
                            <input type="checkbox" name="is_day_off" value="1">
                            <span class="text-slate-700 font-semibold">Hari Libur / Istirahat</span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jam Mulai</label>
                        <input type="time" name="start_time" value="17:00" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jam Selesai</label>
                        <input type="time" name="end_time" value="20:00" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Rencana Durasi (Maks. 4 Jam Hari Kerja)</label>
                    <input type="number" step="0.5" name="planned_duration_hours" value="3.0" min="0.5" max="14" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Uraian Pekerjaan / Alasan Mendesak</label>
                    <textarea name="reason_task_description" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Penyelesaian closing pembukuan akhir bulan / perbaikan server..."></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="splModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-lg font-bold">Terbitkan SPL</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Verify Actual Overtime Modal -->
    <div x-show="verifyModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Verifikasi Jam Aktual Lembur</h3>
                <button @click="verifyModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'/spl/' + activeOrder.id + '/verify'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <div class="text-[11px] text-slate-500 font-bold uppercase mb-1">Nomor SPL</div>
                    <div class="font-bold text-slate-900 font-mono" x-text="activeOrder.order_number"></div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Durasi Aktual Lembur (Jam)</label>
                    <input type="number" step="0.5" name="actual_duration_hours" :value="activeOrder.planned_duration_hours" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="verifyModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg font-bold">Verifikasi & Selesai</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
