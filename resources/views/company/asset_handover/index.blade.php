@extends('layouts.app')

@section('title', 'BAST Pengembalian Aset & Pencabutan Akses Digital')

@section('content')
<div class="space-y-6" x-data="{ createBastModal: false, activeTab: 'basts', items: [{ asset_name: '', serial_number: '', condition_on_return: 'good', compensation_amount: 0, company_asset_id: '' }] }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-200">
                    Exit Asset Clearance Engine
                </span>
                <span class="text-xs text-slate-400">• Tata Kelola Aset & Keamanan IT</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">BAST Pengembalian Aset & Revokasi Akses Digital</h1>
            <p class="text-xs text-slate-500 mt-1">Penerbitan Berita Acara Serah Terima inventaris kantor resmi, perhitungan ganti rugi aset rusak/hilang, dan revokasi otomatis akun SSO.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createBastModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Draft BAST Aset
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 text-xs font-bold gap-6">
        <button @click="activeTab = 'basts'" :class="activeTab === 'basts' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Daftar Berkas BAST Pengembalian ({{ $basts->total() }})
        </button>
        <button @click="activeTab = 'sso'" :class="activeTab === 'sso' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Log Pencabutan Akun & Akses Digital ({{ $revocationLogs->count() }})
        </button>
    </div>

    <!-- Tab 1: BAST Table -->
    <div x-show="activeTab === 'basts'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Histori Berita Acara Serah Terima Aset Kantor</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Nomor BAST / Tanggal</th>
                        <th class="px-4 py-3 font-bold">Karyawan Menyerahkan</th>
                        <th class="px-4 py-3 font-bold">Petugas Penerima</th>
                        <th class="px-4 py-3 font-bold">Jumlah Aset</th>
                        <th class="px-4 py-3 font-bold">Ganti Rugi Kerusakan</th>
                        <th class="px-4 py-3 font-bold">Status BAST</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($basts as $b)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <a href="{{ route('company.asset-handover.show', $b->id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $b->bast_number }}
                                </a>
                                <div class="text-[10px] text-slate-400">{{ $b->handover_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $b->employee->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $b->employee->department->name ?? 'Dept' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div>{{ $b->officer_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $b->officer_position }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ $b->items->count() }} Item Aset
                            </td>
                            <td class="px-4 py-3 font-bold {{ $b->total_compensation_due > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                                Rp {{ number_format($b->total_compensation_due, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($b->status === 'completed')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Ditandatangani & Selesai
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Draft Menunggu TTD
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <a href="{{ route('company.asset-handover.show', $b->id) }}" class="px-2 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition inline-block text-[11px] font-semibold">
                                    Detail
                                </a>
                                <a href="{{ route('company.asset-handover.print', $b->id) }}" target="_blank" class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition inline-block text-[11px] font-semibold">
                                    Cetak BAST
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Belum ada berkas BAST pengembalian aset yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: SSO Revocation Logs -->
    <div x-show="activeTab === 'sso'" style="display: none;" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Log Revokasi Otomatis Akun SSO & Akses IT Perusahaan</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Karyawan Terkait</th>
                        <th class="px-4 py-3 font-bold">Layanan / Sistem</th>
                        <th class="px-4 py-3 font-bold">Identitas Akun</th>
                        <th class="px-4 py-3 font-bold">Waktu Revokasi</th>
                        <th class="px-4 py-3 font-bold">Status Revokasi</th>
                        <th class="px-4 py-3 font-bold">Respon API Gateway</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($revocationLogs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $log->employee->full_name }}</div>
                                <div class="text-[10px] text-slate-400">NIK: {{ $log->employee->employee_number }}</div>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-700">
                                {{ $log->system_name }}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600">
                                {{ $log->account_identifier }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ $log->revoked_at ? $log->revoked_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ strtoupper($log->revocation_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-[11px]">
                                {{ $log->api_response_log }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                Belum ada log pencabutan akses digital yang terekam.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Buat BAST Baru -->
    <div x-show="createBastModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl" @click.away="createBastModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Buat Berita Acara Serah Terima (BAST) Pengembalian Aset</h3>
                <button @click="createBastModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.asset-handover.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Karyawan yang Menyerahkan</label>
                        <select name="employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Petugas HR/IT Penerima</label>
                        <input type="text" name="officer_name" value="{{ auth()->user()->name ?? 'HR Officer' }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan Petugas Penerima</label>
                    <input type="text" name="officer_position" value="IT & Asset Management Specialist" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <!-- Dynamic Items -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-bold text-slate-800">Daftar Aset yang Dikembalikan:</label>
                        <button type="button" @click="items.push({ asset_name: '', serial_number: '', condition_on_return: 'good', compensation_amount: 0, company_asset_id: '' })" class="text-[11px] font-bold text-blue-600 hover:underline">
                            + Tambah Baris Aset
                        </button>
                    </div>

                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div>
                                    <input type="text" :name="`items[${index}][asset_name]`" placeholder="Nama Aset (e.g. Macbook Pro M2)" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white" required>
                                </div>
                                <div>
                                    <input type="text" :name="`items[${index}][serial_number]`" placeholder="No Seri / Asset Tag" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white">
                                </div>
                                <div>
                                    <select :name="`items[${index}][condition_on_return]`" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white">
                                        <option value="good">Kondisi Baik & Lengkap</option>
                                        <option value="minor_damage">Rusak Ringan (Perlu Servis)</option>
                                        <option value="severe_damage">Rusak Berat</option>
                                        <option value="lost">Hilang (Wajib Ganti Rugi)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" step="1000" :name="`items[${index}][compensation_amount]`" placeholder="Nominal Ganti Rugi jika Rusak/Hilang (Rp)" class="flex-1 text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white">
                                <button type="button" @click="if (items.length > 1) items.splice(index, 1)" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createBastModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Buat Berkas BAST</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
