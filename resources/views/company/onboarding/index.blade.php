@extends('layouts.app')

@section('title', 'Onboarding, Offboarding & Aset Perusahaan')

@section('content')
<div class="space-y-6" x-data="{ activeTalentTab: '{{ $type ?? 'onboarding' }}', openInitModal: false, openAssetModal: false, openAssignAssetModal: false, openExitModal: false, selectedAssetId: '', selectedAssetName: '' }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Onboarding, Offboarding & Inventaris Aset</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola orientasi karyawan baru, serah terima aset hardware, prosedur pengunduran diri, dan exit interview.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openAssetModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="laptop" class="w-4 h-4 text-slate-500"></i>
                Tambah Aset Kantor
            </button>
            <button @click="openInitModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Inisialisasi Checklist
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tugas Onboarding Tertunda</div>
            <div class="text-xl font-black text-blue-600 mt-1">{{ number_format($stats['total_onboarding_pending']) }} Tugas</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Perlu penyelesaian orientasi</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tugas Offboarding Tertunda</div>
            <div class="text-xl font-black text-amber-600 mt-1">{{ number_format($stats['total_offboarding_pending']) }} Tugas</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Clearance & serah terima</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Inventaris Aset</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total_assets']) }} Unit</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Hardware & fasilitas terdaftar</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Aset Dipinjamkan</div>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($stats['assigned_assets']) }} Unit</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Aktif dipegang karyawan</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px">
        <button type="button" @click="activeTalentTab = 'onboarding'"
                :class="activeTalentTab === 'onboarding' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="user-check" class="w-4 h-4"></i>
            Onboarding Karyawan Baru
        </button>

        <button type="button" @click="activeTalentTab = 'offboarding'"
                :class="activeTalentTab === 'offboarding' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="user-minus" class="w-4 h-4"></i>
            Offboarding & Clearance
        </button>

        <button type="button" @click="activeTalentTab = 'assets'"
                :class="activeTalentTab === 'assets' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="laptop" class="w-4 h-4"></i>
            Inventaris Aset Perusahaan
        </button>

        <button type="button" @click="activeTalentTab = 'exit'"
                :class="activeTalentTab === 'exit' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="message-square" class="w-4 h-4"></i>
            Exit Interview
        </button>
    </div>

    <!-- TAB 1: ONBOARDING & OFFBOARDING CHECKLISTS -->
    <div x-show="activeTalentTab === 'onboarding' || activeTalentTab === 'offboarding'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900" x-text="activeTalentTab === 'onboarding' ? 'Daftar Periksa Orientasi Karyawan Baru (Onboarding)' : 'Daftar Periksa Pengunduran Diri / Pemutusan Hubungan Kerja (Offboarding)'"></h2>
            <span class="text-xs text-slate-500">Centang tugas yang telah diselesaikan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Nama Karyawan</th>
                        <th class="px-5 py-3.5">Uraian Tugas / Checklist</th>
                        <th class="px-5 py-3.5">Penanggung Jawab</th>
                        <th class="px-5 py-3.5">Waktu Selesai</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($checklists as $task)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $task->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $task->employee->employee_number }} • {{ $task->employee->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-800 font-semibold max-w-sm">
                                <span class="{{ $task->is_completed ? 'line-through text-slate-400 font-normal' : '' }}">
                                    {{ $task->title }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $task->assignedUser?->name ?? 'Tim HR Ops' }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                                {{ $task->completed_at ? $task->completed_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($task->is_completed)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Tertunda
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if(!$task->is_completed)
                                    <form action="{{ route('company.onboarding.task.complete', $task->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-[11px] hover:bg-emerald-700 transition">
                                            Tandai Selesai
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-[11px]">Tuntas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="clipboard-list" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Belum ada tugas checklist aktif.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Inisialisasi Checklist"</strong> untuk mengaktifkan alur onboarding/offboarding bagi karyawan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: INVENTARIS ASET PERUSAHAAN -->
    <div x-show="activeTalentTab === 'assets'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Inventaris Perangkat & Fasilitas Kantor</h2>
            <button @click="openAssetModal = true" class="px-3 py-1.5 rounded-lg bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition">
                + Tambah Aset
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Kode Aset</th>
                        <th class="px-5 py-3.5">Nama Aset & Kategori</th>
                        <th class="px-5 py-3.5">Nomor Serial (S/N)</th>
                        <th class="px-5 py-3.5">Penerima Fasilitas</th>
                        <th class="px-5 py-3.5">Tanggal Serah Terima</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                {{ $asset->asset_code }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $asset->name }}</div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase bg-slate-100 text-slate-700">
                                    {{ $asset->category }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 font-mono text-[11px]">
                                {{ $asset->serial_number ?: '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($asset->assignedEmployee)
                                    <div class="font-bold text-slate-900">{{ $asset->assignedEmployee->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $asset->assignedEmployee->employee_number }}</div>
                                @else
                                    <span class="text-slate-400 italic">Di Gudang (Tersedia)</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $asset->assigned_date ? $asset->assigned_date->format('d M Y') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($asset->status === 'assigned')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Dipinjamkan
                                    </span>
                                @elseif($asset->status === 'available')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                        {{ ucfirst($asset->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($asset->status === 'available')
                                    <button @click="selectedAssetId = '{{ $asset->id }}'; selectedAssetName = '{{ $asset->name }} ({{ $asset->asset_code }})'; openAssignAssetModal = true"
                                            class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-bold text-[10px] hover:bg-slate-800 transition">
                                        Serahkan
                                    </button>
                                @elseif($asset->status === 'assigned')
                                    <form action="{{ route('company.onboarding.assets.return', $asset->id) }}" method="POST" class="inline" onsubmit="return confirm('Konfirmasi pengembalian aset ini ke inventaris?');">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg border border-slate-300 text-slate-700 font-bold text-[10px] hover:bg-slate-100 transition">
                                            Kembalikan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="laptop" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Belum ada aset yang didaftarkan ke inventaris.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: EXIT INTERVIEW -->
    <div x-show="activeTalentTab === 'exit'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Arsip Hasil Wawancara Keluar (Exit Interview)</h2>
            <button @click="openExitModal = true" class="px-3 py-1.5 rounded-lg bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition">
                + Rekam Exit Interview
            </button>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($exitInterviews as $exit)
                <div class="p-5 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-sm">{{ $exit->employee->full_name }}</span>
                            <span class="text-slate-400 ml-2">({{ $exit->employee->position?->title ?? '-' }} • {{ $exit->employee->department?->name ?? '-' }})</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                            Alasan: {{ str_replace('_', ' ', $exit->reason_for_leaving) }}
                        </span>
                    </div>

                    <div class="text-[11px] text-slate-500">
                        Tanggal Wawancara: {{ $exit->interview_date->format('d M Y') }} • Pewawancara: <strong>{{ $exit->interviewer?->name ?? 'HR Manager' }}</strong>
                    </div>

                    @if($exit->feedback)
                        <div class="p-3 bg-slate-50 rounded-xl text-slate-700 mt-2 border border-slate-200/60 leading-relaxed">
                            <span class="font-bold text-slate-900 block text-[11px] mb-0.5">Umpan Balik Karyawan:</span>
                            {{ $exit->feedback }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-12 text-center text-slate-400 text-xs">
                    <i data-lucide="message-square" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                    Belum ada data wawancara keluar yang direkam.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Inisialisasi Checklist -->
    <div x-show="openInitModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openInitModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-2">Inisialisasi Checklist Tugas</h3>
            <p class="text-xs text-slate-500 mb-4">Buat daftar periksa orientasi (Onboarding) atau pengunduran diri (Offboarding) secara otomatis.</p>

            <form action="{{ route('company.onboarding.initialize') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }}) - {{ $emp->department?->name ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Alur Checklist <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="onboarding">Onboarding (Karyawan Baru / Orientasi)</option>
                        <option value="offboarding">Offboarding (Resign / Termination Clearance)</option>
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openInitModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Buat Checklist
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Aset Inventaris -->
    <div x-show="openAssetModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openAssetModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-2">Tambah Aset Baru ke Inventaris</h3>

            <form action="{{ route('company.onboarding.assets.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Identitas Aset <span class="text-red-500">*</span></label>
                    <input type="text" name="asset_code" required placeholder="AST-NB-{{ date('Y') }}-001"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perangkat / Fasilitas <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="MacBook Pro 14 M3 Pro 18GB/512GB"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Aset <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="laptop">Laptop / Komputer</option>
                            <option value="phone">Ponsel / Tablet</option>
                            <option value="access_card">Kartu Akses / ID Card</option>
                            <option value="vehicle">Kendaraan Operasional</option>
                            <option value="other">Perangkat Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Serial (S/N)</label>
                        <input type="text" name="serial_number" placeholder="C02G40..."
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Spesifikasi kelengkapan, charger, tas..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openAssetModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                        Simpan Aset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Serahkan Aset ke Karyawan -->
    <div x-show="openAssignAssetModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openAssignAssetModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-1">Serah Terima Fasilitas Kantor</h3>
            <p class="text-xs text-slate-500 mb-4">Aset: <strong x-text="selectedAssetName"></strong></p>

            <form :action="'/onboarding/assets/' + selectedAssetId + '/assign'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Karyawan Penerima <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openAssignAssetModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition shadow-sm">
                        Serahkan Aset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Rekam Exit Interview -->
    <div x-show="openExitModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openExitModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-2">Rekam Exit Interview</h3>

            <form action="{{ route('company.onboarding.exit-interview.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Wawancara <span class="text-red-500">*</span></label>
                        <input type="date" name="interview_date" required value="{{ now()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Utama Keluar <span class="text-red-500">*</span></label>
                        <select name="reason_for_leaving" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="new_job">Mendapatkan Peluang Kerja Baru</option>
                            <option value="compensation">Kompensasi / Gaji Lebih Tinggi</option>
                            <option value="career_change">Peralihan Jalur Karier</option>
                            <option value="personal">Alasan Pribadi / Keluarga</option>
                            <option value="company_culture">Kesesuaian Budaya Organisasi</option>
                            <option value="other">Lain-lain</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Umpan Balik & Saran untuk Perusahaan</label>
                    <textarea name="feedback" rows="3" placeholder="Saran perbaikan untuk manajemen, kepemimpinan, dan lingkungan kerja..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openExitModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Exit Interview
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
