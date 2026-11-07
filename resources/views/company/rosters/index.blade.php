@extends('layouts.app')

@section('title', 'Pola Shift Khusus 24/7 & Roster Bergilir')

@section('content')
<div class="space-y-6" x-data="{ rosterModal: false, assignModal: false, activeTab: 'rosters' }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                    Sektor Khusus & Operasional Kontinu
                </span>
                <span class="text-xs text-slate-400">• Kepmenakertrans No. KEP-233/MEN/2003</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Pola Shift Khusus 24/7 & Tunjangan Roster</h1>
            <p class="text-xs text-slate-500 mt-1">Pengaturan rotasi 3 shift 4 grup tanpa henti, pemenuhan hak istirahat bergilir, serta kalkulasi otomatis tunjangan shift malam dan siaga (*on-call*).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="assignModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tugaskan Grup Karyawan
            </button>
            <button @click="rosterModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Pola Roster Baru
            </button>
        </div>
    </div>

    <!-- Statutory Alert Banner -->
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4 text-xs text-purple-900 flex items-start gap-3">
        <i data-lucide="clock" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Jam Kerja Sektor Tertentu (Kepmenakertrans No. KEP-233/MEN/2003):</span>
            <ul class="list-disc list-inside space-y-0.5 text-purple-800">
                <li>Berlaku bagi bidang usaha yang pelayanannya harus terus menerus (TI, Data Center, Rumah Sakit, Manufaktur Berat, Keamanan).</li>
                <li>Standar rotasi adalah <strong>3 Shift 4 Grup</strong> (Shift Pagi 07:00-15:00, Shift Siang 15:00-23:00, Shift Malam 23:00-07:00).</li>
                <li>Pekerja shift malam berhak memperoleh <strong>Tunjangan Shift Malam</strong> dan konsumsi bergizi seimbang sesuai anjuran Depnakertrans.</li>
            </ul>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex border-b border-slate-200 text-xs font-bold gap-6">
        <button @click="activeTab = 'rosters'" :class="activeTab === 'rosters' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Daftar Pola Roster 24/7 ({{ $rosters->total() }})
        </button>
        <button @click="activeTab = 'assignments'" :class="activeTab === 'assignments' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Penugasan Grup Karyawan ({{ $assignments->total() }})
        </button>
    </div>

    <!-- Tab 1: Rosters Table -->
    <div x-show="activeTab === 'rosters'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($rosters as $ros)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4 hover:border-slate-300 transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                {{ $ros->roster_code }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900 mt-1">{{ $ros->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $ros->department->name ?? 'Seluruh Departemen Operasional' }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $ros->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $ros->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 bg-slate-50 p-3 rounded-xl text-center text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Siklus Rotasi</span>
                            <span class="font-bold text-slate-800">{{ $ros->cycle_days }} Hari</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Kerja vs Libur</span>
                            <span class="font-bold text-slate-800">{{ $ros->work_days_per_cycle }}H / {{ $ros->off_days_per_cycle }}H</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Tunj. Shift Malam</span>
                            <span class="font-bold text-purple-700">Rp {{ number_format($ros->night_shift_allowance_rate, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                        <span class="text-slate-500">
                            Tunj. Siaga: <strong>Rp {{ number_format($ros->standby_allowance_rate, 0, ',', '.') }}</strong>
                        </span>
                        <span class="font-bold text-blue-600">
                            {{ $ros->assignments->count() }} Karyawan Terdaftar
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    Belum ada pola shift roster yang dibuat.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 2: Assignments Table -->
    <div x-show="activeTab === 'assignments'" style="display: none;" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Penugasan Karyawan ke Grup Shift Roster</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Karyawan / NIK</th>
                        <th class="px-4 py-3 font-bold">Pola Roster</th>
                        <th class="px-4 py-3 font-bold">Penempatan Grup</th>
                        <th class="px-4 py-3 font-bold">Tanggal Mulai Berlaku</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assignments as $asg)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $asg->employee->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $asg->employee->department->name ?? 'Dept' }} • NIK: {{ $asg->employee->employee_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $asg->roster->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $asg->roster->roster_code }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-800">
                                    {{ $asg->group_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $asg->effective_start_date->format('d F Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $asg->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $asg->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Belum ada penugasan grup shift yang aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Buat Pola Roster Baru -->
    <div x-show="rosterModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="rosterModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Buat Pola Shift Roster Kontinu Baru</h3>
                <button @click="rosterModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.rosters.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pola Shift</label>
                    <input type="text" name="name" placeholder="Contoh: Pola 3 Shift 4 Grup Kontinu 24/7 Data Center" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Terkait (Opsional)</label>
                    <select name="department_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                        <option value="">-- Seluruh Departemen Operasional --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Siklus Penuh (Hari)</label>
                        <input type="number" name="cycle_days" value="28" min="7" max="90" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Hari Kerja / Siklus</label>
                        <input type="number" name="work_days_per_cycle" value="21" min="1" max="90" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Hari Libur / Siklus</label>
                        <input type="number" name="off_days_per_cycle" value="7" min="1" max="90" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tunjangan Shift Malam (Rp/Shift)</label>
                        <input type="number" step="1000" name="night_shift_allowance_rate" value="50000" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tunjangan Siaga (Rp/Hari)</label>
                        <input type="number" step="1000" name="standby_allowance_rate" value="75000" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="rosterModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Buat Pola Roster</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Penugasan Grup Karyawan -->
    <div x-show="assignModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl" @click.away="assignModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Penugasan Karyawan ke Grup Shift</h3>
                <button @click="assignModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.rosters.assign-group') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Pola Roster</label>
                    <select name="continuous_shift_roster_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($rosters as $ros)
                            <option value="{{ $ros->id }}">{{ $ros->name }} ({{ $ros->roster_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan</label>
                    <select name="employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Penempatan Grup</label>
                    <select name="group_name" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        <option value="Grup A">Grup A</option>
                        <option value="Grup B">Grup B</option>
                        <option value="Grup C">Grup C</option>
                        <option value="Grup D">Grup D</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Efektif Penugasan</label>
                    <input type="date" name="effective_start_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="assignModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Simpan Penugasan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
