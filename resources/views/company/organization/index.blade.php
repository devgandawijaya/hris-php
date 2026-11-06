@extends('layouts.app')

@section('title', 'Struktur Organisasi & Departemen')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'departments' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Struktur Organisasi</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola departemen, jenjang posisi jabatan, dan radius kantor cabang geofencing.</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
        <button type="button" @click="activeTab = 'departments'"
                :class="activeTab === 'departments' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="layers" class="w-4 h-4"></i>
            Departemen ({{ $departments->count() }})
        </button>

        <button type="button" @click="activeTab = 'positions'"
                :class="activeTab === 'positions' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="briefcase" class="w-4 h-4"></i>
            Posisi Jabatan ({{ $positions->count() }})
        </button>

        <button type="button" @click="activeTab = 'branches'"
                :class="activeTab === 'branches' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="map-pin" class="w-4 h-4"></i>
            Kantor Cabang & GPS ({{ $branches->count() }})
        </button>

        <button type="button" @click="activeTab = 'chart'"
                :class="activeTab === 'chart' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="network" class="w-4 h-4"></i>
            Bagan Organisasi
        </button>
    </div>

    <!-- TAB 1: DEPARTMENTS -->
    <div x-show="activeTab === 'departments'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-red-600"></i>
                Tambah Departemen Baru
            </h2>

            <form action="{{ route('company.organization.departments.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                @csrf
                <div class="sm:col-span-5">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Departemen *</label>
                    <input type="text" name="name" required placeholder="Contoh: Finance & Accounting" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Departemen</label>
                    <input type="text" name="code" placeholder="FIN-01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div class="sm:col-span-4 flex items-end">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Departemen
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Nama Departemen</th>
                        <th class="px-5 py-3">Jumlah Jabatan</th>
                        <th class="px-5 py-3">Jumlah Karyawan</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($departments as $dept)
                        <tr>
                            <td class="px-5 py-3.5 font-mono text-slate-500">{{ $dept->code ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $dept->name }}</td>
                            <td class="px-5 py-3.5">{{ $dept->positions->count() }} Posisi</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $dept->employees->count() }} Orang</td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.organization.departments.delete', $dept->id) }}" method="POST" onsubmit="return confirm('Hapus departemen ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada departemen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: POSITIONS -->
    <div x-show="activeTab === 'positions'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-red-600"></i>
                Tambah Posisi Jabatan Baru
            </h2>

            <form action="{{ route('company.organization.positions.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                @csrf
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Posisi Jabatan *</label>
                    <input type="text" name="title" required placeholder="Senior Backend Developer" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Terkait *</label>
                    <select name="department_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Level Jabatan *</label>
                    <select name="job_level" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                        <option value="Staff">Staff</option>
                        <option value="Officer">Officer</option>
                        <option value="Specialist">Specialist</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Manager">Manager</option>
                        <option value="Director">Director</option>
                    </select>
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Posisi
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Nama Posisi Jabatan</th>
                        <th class="px-5 py-3">Departemen</th>
                        <th class="px-5 py-3">Jenjang (Level)</th>
                        <th class="px-5 py-3">Jumlah Karyawan</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($positions as $pos)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $pos->title }}</td>
                            <td class="px-5 py-3.5">{{ $pos->department?->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">{{ $pos->job_level }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $pos->employees->count() }} Orang</td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.organization.positions.delete', $pos->id) }}" method="POST" onsubmit="return confirm('Hapus posisi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada posisi jabatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: BRANCHES & GEOFENCING -->
    <div x-show="activeTab === 'branches'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-emerald-600"></i>
                Tambah Kantor Cabang & Titik Koordinat Presensi Geofence
            </h2>
            <p class="text-xs text-slate-500 mb-4">Presensi GPS karyawan akan memvalidasi jarak koordinat perangkat terhadap radius meter kantor ini.</p>

            <form action="{{ route('company.organization.branches.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                @csrf
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Cabang / Kantor *</label>
                    <input type="text" name="name" required placeholder="Kantor Pusat SCBD" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kota</label>
                    <input type="text" name="city" placeholder="Jakarta Selatan" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude Koordinat *</label>
                    <input type="number" step="0.0000001" name="latitude" value="-6.225588" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude *</label>
                    <input type="number" step="0.0000001" name="longitude" value="106.811802" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Radius (Meter) *</label>
                    <input type="number" name="radius_meters" value="100" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div class="sm:col-span-12 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Kantor Cabang
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Nama Kantor</th>
                        <th class="px-5 py-3">Kota</th>
                        <th class="px-5 py-3">Titik Koordinat GPS</th>
                        <th class="px-5 py-3">Radius Geofencing</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($branches as $branch)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $branch->name }}</td>
                            <td class="px-5 py-3.5">{{ $branch->city ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-mono text-[11px] text-slate-600">{{ $branch->latitude }}, {{ $branch->longitude }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                    {{ $branch->radius_meters }} Meter
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.organization.branches.delete', $branch->id) }}" method="POST" onsubmit="return confirm('Hapus kantor cabang ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada kantor cabang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: VISUAL ORG CHART -->
    <div x-show="activeTab === 'chart'" x-cloak class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-sm">
        <h2 class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2">
            <i data-lucide="network" class="w-4 h-4 text-red-600"></i>
            Hierarki Struktur Organisasi Perusahaan
        </h2>

        <div class="space-y-6">
            <!-- Root: Company Level -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 text-white max-w-sm mx-auto text-center shadow-lg">
                <div class="text-[10px] font-bold uppercase tracking-wider text-red-400">Dewan Direksi / Holding</div>
                <div class="text-base font-black mt-1">{{ $currentTenant->name ?? 'Perusahaan' }}</div>
            </div>

            <div class="w-0.5 h-6 bg-slate-300 mx-auto"></div>

            <!-- Departments Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($departments as $dept)
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Departemen</div>
                                <div class="text-sm font-bold text-slate-900">{{ $dept->name }}</div>
                            </div>
                            <span class="text-xs bg-white px-2 py-0.5 rounded font-mono font-bold text-slate-700 border border-slate-200">
                                {{ $dept->employees->count() }} Org
                            </span>
                        </div>

                        <!-- Positions within department -->
                        <div class="space-y-1.5">
                            @forelse($dept->positions as $p)
                                <div class="p-2 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-800">{{ $p->title }}</span>
                                    <span class="text-[10px] text-slate-500 font-medium">{{ $p->job_level }}</span>
                                </div>
                            @empty
                                <div class="text-[11px] text-slate-400 italic">Belum ada posisi jabatan.</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
