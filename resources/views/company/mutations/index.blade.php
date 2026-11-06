@extends('layouts.app')

@section('title', 'Pergerakan Karir (Mutasi, Promosi & Demosi)')

@section('content')
<div class="space-y-6" x-data="{ 
    openModal: false,
    selectedEmpId: '',
    employeesList: {{ Js::from($employees) }},
    currentDept: '-',
    currentPos: '-',
    currentSalary: 0,
    onEmployeeChange() {
        const emp = this.employeesList.find(e => e.id == this.selectedEmpId);
        if (emp) {
            this.currentDept = emp.department ? emp.department.name : '-';
            this.currentPos = emp.position ? emp.position.title : '-';
            this.currentSalary = emp.base_salary ? parseFloat(emp.base_salary) : 0;
        } else {
            this.currentDept = '-';
            this.currentPos = '-';
            this.currentSalary = 0;
        }
    }
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Karir & Mutasi Karyawan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola promosi jabatan, mutasi antar divisi, demosi, dan riwayat penyesuaian upah resmi.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                Terbitkan SK Mutasi / Promosi
            </button>
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pergerakan</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Semua riwayat transisi karir</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Promosi Jabatan</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['promotions']) }}</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Kenaikan jenjang karir</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Mutasi / Rotasi Divisi</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ number_format($stats['transfers']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Pengembangan & penyegaran</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Demosi / Pembinaan</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['demotions']) }}</div>
            <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Penataan ulang posisi</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Riwayat Pergerakan Karir & SK Direksi</h2>
                <p class="text-xs text-slate-400 mt-0.5">Data mutasi otomatis memperbarui master data departemen, posisi, dan gaji pokok karyawan.</p>
            </div>

            <!-- Filter Type -->
            <form action="{{ route('company.mutations.index') }}" method="GET" class="flex items-center gap-2">
                <select name="type" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Jenis Pergerakan</option>
                    <option value="promotion" {{ request('type') === 'promotion' ? 'selected' : '' }}>Promosi Jabatan</option>
                    <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Mutasi / Rotasi Divisi</option>
                    <option value="demotion" {{ request('type') === 'demotion' ? 'selected' : '' }}>Demosi</option>
                    <option value="salary_adjustment" {{ request('type') === 'salary_adjustment' ? 'selected' : '' }}>Penyesuaian Gaji</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Karyawan & NIK</th>
                        <th class="py-3 px-4">Jenis Pergerakan</th>
                        <th class="py-3 px-4">Perubahan Departemen</th>
                        <th class="py-3 px-4">Perubahan Jabatan</th>
                        <th class="py-3 px-4">Penyesuaian Gaji</th>
                        <th class="py-3 px-4">Tgl Efektif</th>
                        <th class="py-3 px-4">Catatan / Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($mutations as $m)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $m->employee?->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $m->employee?->employee_code }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($m->type === 'promotion')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i> Promosi
                                    </span>
                                @elseif($m->type === 'transfer')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">
                                        <i data-lucide="repeat" class="w-3 h-3"></i> Mutasi
                                    </span>
                                @elseif($m->type === 'demotion')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                                        <i data-lucide="arrow-down-right" class="w-3 h-3"></i> Demosi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-800">
                                        <i data-lucide="coins" class="w-3 h-3"></i> Penyesuaian Gaji
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-slate-500 text-[11px]">{{ $m->fromDepartment?->name ?? 'Dept Lama' }}</div>
                                <div class="font-bold text-slate-900 flex items-center gap-1 mt-0.5">
                                    <i data-lucide="arrow-right" class="w-3 h-3 text-red-500"></i>
                                    {{ $m->toDepartment?->name ?? 'Dept Baru' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-slate-500 text-[11px]">{{ $m->fromPosition?->title ?? 'Posisi Lama' }}</div>
                                <div class="font-bold text-slate-900 flex items-center gap-1 mt-0.5">
                                    <i data-lucide="arrow-right" class="w-3 h-3 text-emerald-600"></i>
                                    {{ $m->toPosition?->title ?? 'Posisi Baru' }}
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($m->to_salary && $m->to_salary != $m->from_salary)
                                    <div class="text-slate-400 text-[10px] line-through">Rp {{ number_format($m->from_salary, 0, ',', '.') }}</div>
                                    <div class="font-bold text-slate-900 text-xs">Rp {{ number_format($m->to_salary, 0, ',', '.') }}</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Tetap</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $m->effective_date->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $m->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <div class="text-slate-700 text-[11px] truncate" title="{{ $m->notes }}">{{ $m->notes ?? '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i data-lucide="briefcase" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Belum ada riwayat pergerakan karir atau mutasi tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutations->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $mutations->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Create Mutation / Promotion -->
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="user-check" class="w-5 h-5 text-red-600"></i>
                    Terbitkan SK Pergerakan Karir Karyawan
                </div>
                <button @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.mutations.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" x-model="selectedEmpId" @change="onEmployeeChange" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->department?->name ?? 'Dept' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Current Info Box -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 grid grid-cols-3 gap-2 text-[11px]">
                    <div>
                        <span class="text-slate-400 block">Departemen Saat Ini:</span>
                        <strong class="text-slate-800" x-text="currentDept"></strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jabatan Saat Ini:</span>
                        <strong class="text-slate-800" x-text="currentPos"></strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Gaji Pokok Saat Ini:</span>
                        <strong class="text-slate-800" x-text="'Rp ' + Number(currentSalary).toLocaleString('id-ID')"></strong>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Pergerakan <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="promotion">Promosi Jabatan (Naik Pangkat)</option>
                            <option value="transfer">Mutasi / Rotasi Divisi</option>
                            <option value="demotion">Demosi / Pembinaan Karir</option>
                            <option value="salary_adjustment">Penyesuaian Gaji Pokok Berkala</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Efektif <span class="text-red-500">*</span></label>
                        <input type="date" name="effective_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Departemen Tujuan Baru</label>
                        <select name="to_department_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Tetap (Tidak Berubah)</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jabatan Tujuan Baru</label>
                        <select name="to_position_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Tetap (Tidak Berubah)</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gaji Pokok Baru (Opsional, kosongkan jika tetap)</label>
                    <input type="number" step="1000" name="to_salary" placeholder="Contoh: 12000000" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor SK Direksi & Catatan Pertimbangan</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: SK Direksi No. 045/SK-DIR/2026 atas pencapaian kinerja istimewa kuartal 4..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#DC2626] hover:bg-[#991B1B] rounded-xl shadow-sm transition">
                        Simpan & Terbitkan SK
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
