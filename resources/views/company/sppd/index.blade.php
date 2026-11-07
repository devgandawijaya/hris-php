@extends('layouts.app')

@section('title', 'Surat Perintah Perjalanan Dinas (SPPD) & Realisasi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Perjalanan Dinas (SPPD)</h1>
            <p class="text-sm text-slate-500 mt-1">Penerbitan Surat Tugas Kedinasan resmi, pencairan uang muka, dan audit realisasi settlement kwitansi.</p>
        </div>
        <button onclick="document.getElementById('modal-add-sppd').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Terbitkan SPPD Baru
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Penugasan SPPD</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Sepanjang tahun berjalan</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Persetujuan</p>
            <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Permohonan diajukan pegawai</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Berlangsung</p>
            <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['ongoing'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Pegawai aktif di luar kota</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selesai & Settled</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['completed'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Rekonsiliasi biaya final</p>
        </div>
    </div>

    <!-- SPPD Orders Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-800">Daftar Surat Perintah Perjalanan Dinas</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('company.business-trips.index') }}" class="px-3 py-1 rounded text-xs {{ !request('status') ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Semua</a>
                <a href="{{ route('company.business-trips.index', ['status' => 'submitted']) }}" class="px-3 py-1 rounded text-xs {{ request('status') === 'submitted' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Pending</a>
                <a href="{{ route('company.business-trips.index', ['status' => 'ongoing']) }}" class="px-3 py-1 rounded text-xs {{ request('status') === 'ongoing' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Aktif</a>
                <a href="{{ route('company.business-trips.index', ['status' => 'completed']) }}" class="px-3 py-1 rounded text-xs {{ request('status') === 'completed' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Selesai</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Nomor SPPD</th>
                        <th class="px-5 py-3">Pegawai Ditugaskan</th>
                        <th class="px-5 py-3">Destinasi & Maksud</th>
                        <th class="px-5 py-3">Tanggal & Durasi</th>
                        <th class="px-5 py-3 text-right">Uang Muka</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php
                            $advance = $order->advances->first();
                            $statusMap = [
                                'draft' => ['Draft', 'bg-gray-100 text-gray-700'],
                                'submitted' => ['Diajukan', 'bg-amber-100 text-amber-700'],
                                'approved' => ['Disetujui', 'bg-blue-100 text-blue-700'],
                                'rejected' => ['Ditolak', 'bg-rose-100 text-rose-700'],
                                'ongoing' => ['Berlangsung', 'bg-indigo-100 text-indigo-700'],
                                'completed' => ['Selesai', 'bg-emerald-100 text-emerald-700'],
                            ];
                            $badge = $statusMap[$order->status] ?? ['Lainnya', 'bg-slate-100 text-slate-700'];
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3">
                                <a href="{{ route('company.business-trips.show', $order) }}" class="font-mono font-bold text-red-600 hover:underline">{{ $order->sppd_number }}</a>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-800">{{ $order->employee->full_name }}</div>
                                <div class="text-xs text-slate-400">{{ $order->employee->department ? $order->employee->department->name : '-' }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-semibold text-slate-800">{{ $order->destination_city }}</div>
                                <div class="text-xs text-slate-500 line-clamp-1">{{ $order->purpose }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div>{{ $order->departure_date->format('d/m/Y') }} - {{ $order->return_date->format('d/m/Y') }}</div>
                                <div class="text-xs text-slate-400">{{ $order->duration_days }} Hari Kerja</div>
                            </td>
                            <td class="px-5 py-3 text-right font-mono">
                                @if($advance)
                                    Rp {{ number_format($advance->amount_approved > 0 ? $advance->amount_approved : $advance->amount_requested, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge[1] }}">{{ $badge[0] }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('company.business-trips.show', $order) }}" class="text-xs px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition font-medium">Detail & Audit</a>
                                    <a href="{{ route('company.business-trips.print', $order) }}" target="_blank" class="text-xs px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded transition font-medium">Cetak Dokumen</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada data perjalanan dinas yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Terbitkan SPPD -->
<div id="modal-add-sppd" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-xl w-full overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800">Terbitkan Surat Perintah Perjalanan Dinas (SPPD)</h3>
            <button onclick="document.getElementById('modal-add-sppd').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="{{ route('company.business-trips.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Pegawai yang Ditugaskan *</label>
                <select name="employee_id" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }}) - {{ $emp->department ? $emp->department->name : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Maksud & Tujuan Kedinasan *</label>
                <input type="text" name="purpose" required placeholder="Contoh: Audit operasional cabang Surabaya & penandatanganan MoU" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Kota Keberangkatan *</label>
                    <input type="text" name="origin_city" value="Jakarta" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Kota Tujuan Kedinasan *</label>
                    <input type="text" name="destination_city" required placeholder="Contoh: Surabaya" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Berangkat *</label>
                    <input type="date" name="departure_date" required value="{{ date('Y-m-d') }}" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Kembali *</label>
                    <input type="date" name="return_date" required value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Moda Transportasi *</label>
                    <select name="transport_type" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                        <option value="flight">Pesawat Udara</option>
                        <option value="train">Kereta Api</option>
                        <option value="company_car">Mobil Dinas Kantor</option>
                        <option value="personal_vehicle">Kendaraan Pribadi</option>
                        <option value="public_transport">Transportasi Umum / Bus</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Uang Muka Dimohon (Rp)</label>
                    <input type="number" name="advance_requested" min="0" placeholder="Contoh: 3000000" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Catatan Tambahan</label>
                <textarea name="notes" rows="2" placeholder="Instruksi khusus, akomodasi hotel yang disiapkan..." class="w-full text-sm border-slate-300 rounded-lg px-3 py-2"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-sppd').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Terbitkan SPPD</button>
            </div>
        </form>
    </div>
</div>
@endsection
