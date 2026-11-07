@extends('layouts.app')

@section('title', 'Struktur dan Skala Upah (Permenaker 1/2017) - ' . $company->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Struktur dan Skala Upah</h1>
            <p class="text-sm text-slate-500 mt-1">Standarisasi rentang upah, spread margin, dan audit compa-ratio kepatuhan Permenaker No. 1 Tahun 2017 & PP No. 36/2021.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('company.wage-scales.export') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Ekspor Audit Kemnaker (CSV)
            </a>
            <button onclick="document.getElementById('modal-add-grade').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Golongan Upah
            </button>
        </div>
    </div>

    <!-- KPI Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tingkat Kepatuhan</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $audit['compliance_rate'] }}%</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 font-bold">
                    ✓
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">{{ $audit['assigned_count'] }} dari {{ $audit['total_employees'] }} pegawai memiliki golongan</p>
        </div>

        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Parity & Premium</p>
                    <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ $audit['parity_count'] + $audit['premium_count'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 font-bold">
                    ★
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">Compa-Ratio 100% s.d. 120%</p>
        </div>

        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penetrasi Pasar</p>
                    <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $audit['penetration_count'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 font-bold">
                    ⚖
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">Compa-Ratio 80% s.d. 99% (Learning zone)</p>
        </div>

        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Defisit / Outlier</p>
                    <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ $audit['underpaid_count'] }} <span class="text-xs text-slate-400 font-normal">/ {{ $audit['redCircleCount'] ?? $audit['red_circle_count'] }}</span></h3>
                </div>
                <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 font-bold">
                    !
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">{{ $audit['underpaid_count'] }} di bawah minimum; {{ $audit['redCircleCount'] ?? $audit['red_circle_count'] }} di atas skala</p>
        </div>
    </div>

    <!-- Job Grades Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-800">Matriks Golongan Jabatan & Rentang Upah Resmi</h2>
            <span class="text-xs text-slate-500">Permenaker No. 1/2017 Pasal 2 s.d. Pasal 6</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Tingkat</th>
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Nama Golongan</th>
                        <th class="px-5 py-3 text-right">Upah Terendah (Min)</th>
                        <th class="px-5 py-3 text-right">Upah Tengah (Mid)</th>
                        <th class="px-5 py-3 text-right">Upah Tertinggi (Max)</th>
                        <th class="px-5 py-3 text-right">Rentang (Spread)</th>
                        <th class="px-5 py-3 text-right">Progresi Midpoint</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($grades as $g)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3 font-semibold text-slate-800">Level {{ $g->level_order }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-mono font-bold">{{ $g->grade_code }}</span>
                            </td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $g->name }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($g->min_salary, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono font-semibold text-slate-800">Rp {{ number_format($g->mid_salary, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($g->max_salary, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono text-blue-600 font-semibold">{{ $g->spread_percentage }}%</td>
                            <td class="px-5 py-3 text-right font-mono text-emerald-600">{{ $g->midpoint_progression > 0 ? '+' . $g->midpoint_progression . '%' : '-' }}</td>
                            <td class="px-5 py-3 text-center">
                                <button onclick="editGrade({{ json_encode($g) }})" class="text-xs text-slate-600 hover:text-red-600 font-medium transition">Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-slate-400">Belum ada golongan skala upah yang dikonfigurasi. Klik tombol di atas untuk menambah golongan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Employee Positioning & Compa-Ratio Audit -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-800">Analisis Penempatan Karyawan & Audit Compa-Ratio</h2>
                <p class="text-xs text-slate-500 mt-0.5">Compa-Ratio = (Gaji Pokok / Midpoint Golongan) x 100%</p>
            </div>
            <span class="text-xs text-slate-400">{{ count($audit['audited_employees']) }} Karyawan Aktif</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Departemen & Jabatan</th>
                        <th class="px-5 py-3">Golongan Upah</th>
                        <th class="px-5 py-3 text-right">Gaji Pokok</th>
                        <th class="px-5 py-3 text-center">Compa-Ratio</th>
                        <th class="px-5 py-3">Status Skala</th>
                        <th class="px-5 py-3 text-center">Ubah Golongan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($audit['audited_employees'] as $row)
                        @php
                            $emp = $row['employee'];
                            $grd = $row['grade'];
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-800">{{ $emp->full_name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $emp->employee_number }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div>{{ $emp->department ? $emp->department->name : '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $emp->position ? $emp->position->title : '-' }}</div>
                            </td>
                            <td class="px-5 py-3">
                                @if($grd)
                                    <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-semibold">{{ $grd->grade_code }} - {{ $grd->name }}</span>
                                @else
                                    <span class="text-xs text-rose-500 italic">Belum di-assign</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-mono font-medium text-slate-800">
                                Rp {{ number_format($emp->basic_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($row['compa_ratio'] !== null)
                                    <span class="font-mono font-bold text-slate-800">{{ $row['compa_ratio'] }}%</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $row['badge_class'] }}">
                                    {{ $row['band_label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <form method="POST" action="{{ route('company.wage-scales.assign', $emp->id) }}" class="flex items-center justify-center gap-2">
                                    @csrf
                                    <select name="job_grade_id" onchange="this.form.submit()" class="text-xs border-slate-300 rounded px-2 py-1 focus:ring-red-500 focus:border-red-500">
                                        <option value="">-- Pilih Golongan --</option>
                                        @foreach($grades as $g)
                                            <option value="{{ $g->id }}" {{ $emp->job_grade_id == $g->id ? 'selected' : '' }}>
                                                {{ $g->grade_code }} (Level {{ $g->level_order }})
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Grade -->
<div id="modal-add-grade" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modal-title" class="text-lg font-bold text-slate-800">Tambah Golongan Upah</h3>
            <button onclick="document.getElementById('modal-add-grade').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="{{ route('company.wage-scales.grades.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="id" id="grade-id">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Kode Golongan *</label>
                    <input type="text" name="grade_code" id="grade-code" required placeholder="Contoh: G-01" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Urutan Level (1 = Terendah) *</label>
                    <input type="number" name="level_order" id="grade-level" required min="1" max="20" value="1" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Nama / Kategori Golongan *</label>
                <input type="text" name="name" id="grade-name" required placeholder="Contoh: Pelaksana / Entry Level Staff" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Upah Terendah *</label>
                    <input type="number" name="min_salary" id="grade-min" required min="0" placeholder="Rp Min" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Midpoint (Opsional)</label>
                    <input type="number" name="mid_salary" id="grade-mid" min="0" placeholder="Auto" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Upah Tertinggi *</label>
                    <input type="number" name="max_salary" id="grade-max" required min="0" placeholder="Rp Max" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Keterangan / Kualifikasi Jabatan</label>
                <textarea name="description" id="grade-desc" rows="2" placeholder="Persyaratan pendidikan, pengalaman, dan kompetensi minimal..." class="w-full text-sm border-slate-300 rounded-lg px-3 py-2"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-grade').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Simpan Golongan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editGrade(grade) {
    document.getElementById('modal-title').innerText = 'Edit Golongan Upah ' + grade.grade_code;
    document.getElementById('grade-id').value = grade.id;
    document.getElementById('grade-code').value = grade.grade_code;
    document.getElementById('grade-level').value = grade.level_order;
    document.getElementById('grade-name').value = grade.name;
    document.getElementById('grade-min').value = grade.min_salary;
    document.getElementById('grade-mid').value = grade.mid_salary;
    document.getElementById('grade-max').value = grade.max_salary;
    document.getElementById('grade-desc').value = grade.description || '';
    document.getElementById('modal-add-grade').classList.remove('hidden');
}
</script>
@endsection
