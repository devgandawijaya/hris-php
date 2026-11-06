@extends('layouts.app')

@section('title', 'Audit Log & Keamanan Korporat')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'audit', openIpModal: false }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Audit Log & Keamanan Akses Korporat</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau rekam jejak aktivitas sensitif administratif, audit kepatuhan, dan batasan alamat IP jaringan kantor.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openIpModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                Tambah IP Whitelist
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px">
        <button type="button" @click="activeTab = 'audit'"
                :class="activeTab === 'audit' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="shield-alert" class="w-4 h-4"></i>
            Jejak Audit Aktivitas (Audit Trail)
        </button>

        <button type="button" @click="activeTab = 'ip'"
                :class="activeTab === 'ip' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="network" class="w-4 h-4"></i>
            Pembatasan IP Jaringan (Whitelist)
        </button>
    </div>

    <!-- TAB 1: AUDIT TRAIL LOGS -->
    <div x-show="activeTab === 'audit'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Catatan Aktivitas Pengguna & Perubahan Data</h2>
                <span class="text-xs text-slate-500">Rekam jejak tidak dapat dimanipulasi (*immutable*)</span>
            </div>

            <!-- Action Filter -->
            <form action="{{ route('company.security.index') }}" method="GET" class="flex items-center gap-2">
                <select name="action" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-1.5 font-bold text-slate-700 bg-slate-50">
                    <option value="">Semua Tindakan</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login Masuk</option>
                    <option value="payroll_processed" {{ request('action') === 'payroll_processed' ? 'selected' : '' }}>Proses Payroll</option>
                    <option value="employee_created" {{ request('action') === 'employee_created' ? 'selected' : '' }}>Karyawan Baru</option>
                    <option value="leave_approved" {{ request('action') === 'leave_approved' ? 'selected' : '' }}>Persetujuan Cuti</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Waktu Aktivitas</th>
                        <th class="px-5 py-3.5">Pengguna (Actor)</th>
                        <th class="px-5 py-3.5">Aksi / Tindakan</th>
                        <th class="px-5 py-3.5">Objek Target</th>
                        <th class="px-5 py-3.5">Alamat IP</th>
                        <th class="px-5 py-3.5">Perangkat / Browser</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $log->user?->name ?? 'System Task' }}
                                <div class="text-[10px] text-slate-400 font-normal">{{ $log->user?->email ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-800">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 font-mono text-[11px]">
                                {{ $log->auditable_type ? class_basename($log->auditable_type) . ' #' . $log->auditable_id : '-' }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="px-5 py-4 text-slate-400 max-w-xs truncate text-[11px]">
                                {{ $log->user_agent ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada log aktivitas yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $auditLogs->links() }}
        </div>
    </div>

    <!-- TAB 2: IP WHITELIST -->
    <div x-show="activeTab === 'ip'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Daftar Alamat IP yang Diizinkan (IP Whitelist)</h2>
                <span class="text-xs text-slate-500">Jika dikonfigurasi, hanya koneksi dari IP ini yang dapat login ke dashboard perusahaan</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Alamat IP</th>
                        <th class="px-5 py-3.5">Label Keterangan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ipWhitelists as $ip)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                {{ $ip->ip_address }}
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                {{ $ip->label ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Diizinkan
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('company.security.ip-whitelist.destroy', $ip->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus alamat IP ini dari whitelist?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 font-bold text-[11px] transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada pembatasan IP (Akses terbuka dari seluruh jaringan internet).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Tambah IP Whitelist -->
    <div x-show="openIpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openIpModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-black text-slate-900">Tambah Alamat IP Whitelist</h3>

            <form action="{{ route('company.security.ip-whitelist.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat IP (IPv4 / IPv6) <span class="text-red-500">*</span></label>
                    <input type="text" name="ip_address" required placeholder="Contoh: 180.252.12.8" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono font-bold focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Label Lokasi / Kantor</label>
                    <input type="text" name="label" placeholder="Contoh: VPN Kantor Pusat Jakarta" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="openIpModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B]">Simpan IP</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
