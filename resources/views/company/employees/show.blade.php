@extends('layouts.app')

@section('title', 'Profil Karyawan: ' . $employee->full_name)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{ activeTab: 'biodata' }">

    <!-- Top Breadcrumb & Action -->
    <div class="flex items-center justify-between">
        <a href="{{ route('company.employees.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar Karyawan
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('company.employees.edit', $employee->id) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                Edit Biodata
            </a>

            @if($employee->status === 'active')
                <form action="{{ route('company.employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan/terminasi karyawan ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition inline-flex items-center gap-1.5">
                        <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                        Nonaktifkan
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Employee Profile Header Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-red-600 to-red-700 text-white font-black text-2xl flex items-center justify-center shadow-md shadow-red-500/20 shrink-0">
                {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name ?? '', 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl font-bold text-slate-900">{{ $employee->full_name }}</h1>
                    @if($employee->status === 'active')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">{{ ucfirst($employee->status) }}</span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    <span>NIK: <code class="font-mono font-bold text-slate-700">{{ $employee->employee_number }}</code></span>
                    <span>Jabatan: <strong class="text-slate-800">{{ $employee->position?->title ?? '-' }}</strong></span>
                    <span>Departemen: <strong class="text-slate-800">{{ $employee->department?->name ?? '-' }}</strong></span>
                </div>
            </div>
        </div>

        <div class="text-right sm:border-l sm:border-slate-100 sm:pl-6">
            <span class="text-[10px] uppercase font-bold text-slate-400">Upah Pokok</span>
            <div class="text-lg font-black text-slate-900 font-mono mt-0.5">
                Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-[#DC2626] font-bold mt-0.5">PTKP: {{ $employee->tax_ptkp_status }}</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
        <button type="button" @click="activeTab = 'biodata'"
                :class="activeTab === 'biodata' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4"></i>
            Biodata & Pajak
        </button>

        <button type="button" @click="activeTab = 'presensi'"
                :class="activeTab === 'presensi' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="clock" class="w-4 h-4"></i>
            Presensi & Cuti
        </button>

        <button type="button" @click="activeTab = 'dokumen'"
                :class="activeTab === 'dokumen' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            Berkas Digital ({{ $employee->documents->count() }})
        </button>

        <button type="button" @click="activeTab = 'mutasi'"
                :class="activeTab === 'mutasi' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="trending-up" class="w-4 h-4"></i>
            Riwayat Mutasi & Karier
        </button>
    </div>

    <!-- TAB 1: BIODATA & IDENTITAS PAJAK -->
    <div x-show="activeTab === 'biodata'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Identity -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-3 text-xs">
            <h3 class="font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4 text-blue-600"></i>
                Identitas Kependudukan & Kontak
            </h3>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">Email:</span> <span class="font-semibold text-slate-900">{{ $employee->email }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">Telepon / HP:</span> <span class="font-semibold text-slate-900">{{ $employee->phone ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">NIK KTP (16 Digit):</span> <span class="font-mono font-semibold text-slate-900">{{ $employee->nik_ktp ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">Jenis Kelamin:</span> <span class="font-semibold text-slate-900">{{ $employee->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">Tempat, Tanggal Lahir:</span> <span class="font-semibold text-slate-900">{{ $employee->birth_place ?? '-' }}, {{ $employee->birth_date?->format('d M Y') ?? '-' }}</span></div>
            <div class="flex justify-between py-1"><span class="text-slate-500">Status Pernikahan:</span> <span class="font-semibold text-slate-900">{{ ucfirst($employee->marital_status) }}</span></div>
        </div>

        <!-- Tax & BPJS Compliance -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-3 text-xs">
            <h3 class="font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                Kepatuhan Pajak & Rekening Bank
            </h3>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">Status PTKP (PPh 21 TER):</span> <span class="font-mono font-bold text-red-600">{{ $employee->tax_ptkp_status }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">NPWP 16 Digit:</span> <span class="font-mono font-semibold text-slate-900">{{ $employee->npwp ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">No. BPJS Ketenagakerjaan:</span> <span class="font-mono font-semibold text-slate-900">{{ $employee->bpjs_tk_number ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">No. BPJS Kesehatan:</span> <span class="font-mono font-semibold text-slate-900">{{ $employee->bpjs_kes_number ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-500">Rekening Transfer Bank:</span> <span class="font-mono font-semibold text-slate-900">{{ $employee->bank_name }} - {{ $employee->bank_account_number }}</span></div>
            <div class="flex justify-between py-1"><span class="text-slate-500">Atas Nama Rekening:</span> <span class="font-semibold text-slate-900">{{ $employee->bank_account_holder ?? '-' }}</span></div>
        </div>
    </div>

    <!-- TAB 2: PRESENSI & CUTI TERBARU -->
    <div x-show="activeTab === 'presensi'" x-cloak class="space-y-6">
        <!-- Attendance Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 mb-3 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-emerald-600"></i>
                Riwayat Presensi 10 Hari Terakhir
            </h3>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-4 py-2.5">Tanggal</th>
                        <th class="px-4 py-2.5">Clock In</th>
                        <th class="px-4 py-2.5">Clock Out</th>
                        <th class="px-4 py-2.5">Jam Kerja</th>
                        <th class="px-4 py-2.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->attendanceRecords as $att)
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $att->date->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 font-mono">{{ $att->clock_in ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-mono">{{ $att->clock_out ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ $att->work_hours }} Jam</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $att->status === 'present' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $att->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada rekaman presensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: BERKAS DIGITAL -->
    <div x-show="activeTab === 'dokumen'" x-cloak class="space-y-6">
        <!-- Upload Form -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h3 class="font-bold text-xs text-slate-900 mb-3 flex items-center gap-2">
                <i data-lucide="upload" class="w-4 h-4 text-red-600"></i>
                Unggah Berkas Baru (KTP, Kontrak, Sertifikat, Paklaring)
            </h3>

            <form action="{{ route('company.employees.documents.upload', $employee->id) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                @csrf
                <div class="sm:col-span-3">
                    <select name="document_type" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                        <option value="ktp">Scan KTP</option>
                        <option value="npwp">Scan NPWP</option>
                        <option value="contract">Surat Kontrak PKWT</option>
                        <option value="cv">Curriculum Vitae (CV)</option>
                        <option value="degree">Ijazah Terakhir</option>
                        <option value="certificate">Sertifikat Keahlian</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <input type="text" name="title" required placeholder="Judul Dokumen (misal: Kontrak PKWT 2026)" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-3">
                    <input type="file" name="file" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="w-full py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition">
                        Unggah
                    </button>
                </div>
            </form>
        </div>

        <!-- Documents List -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Arsip Berkas Digital Tersimpan</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3">Judul Berkas</th>
                        <th class="px-5 py-3">Ukuran</th>
                        <th class="px-5 py-3">Tanggal Unggah</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->documents as $doc)
                        <tr>
                            <td class="px-5 py-3 font-semibold uppercase text-slate-700">{{ $doc->document_type }}</td>
                            <td class="px-5 py-3 font-bold text-slate-900">{{ $doc->title }}</td>
                            <td class="px-5 py-3 font-mono">{{ $doc->file_size_kb }} KB</td>
                            <td class="px-5 py-3">{{ $doc->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ $doc->file_path }}" target="_blank" class="text-xs text-red-600 font-semibold hover:underline">
                                    Unduh
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada berkas terunggah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: RIWAYAT MUTASI & KARIER -->
    <div x-show="activeTab === 'mutasi'" x-cloak class="space-y-6">
        <!-- Record Mutation Form -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h3 class="font-bold text-xs text-slate-900 mb-3 flex items-center gap-2">
                <i data-lucide="trending-up" class="w-4 h-4 text-emerald-600"></i>
                Catat Promosi, Demosi, atau Mutasi Jabatan
            </h3>

            <form action="{{ route('company.employees.mutations.record', $employee->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Perubahan *</label>
                        <select name="type" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                            <option value="promotion">Promosi Jabatan</option>
                            <option value="transfer">Mutasi Antar Departemen</option>
                            <option value="demotion">Demosi</option>
                            <option value="status_change">Penyesuaian Gaji Pokok</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Baru</label>
                        <select name="to_department_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                            <option value="">Tetap di Departemen Saat Ini</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Posisi Jabatan Baru</label>
                        <select name="to_position_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                            <option value="">Tetap di Posisi Saat Ini</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->job_level }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Upah Pokok Baru (IDR)</label>
                        <input type="number" step="1000" name="to_salary" value="{{ $employee->basic_salary }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Efektif SK Mutasi *</label>
                        <input type="date" name="effective_date" value="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan SK / Alasan Mutasi</label>
                        <input type="text" name="notes" placeholder="Nomor Surat Keputusan / Pertimbangan Manajemen" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                    Terapkan Perubahan Mutasi
                </button>
            </form>
        </div>

        <!-- Mutation History Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Riwayat Mutasi & Perubahan Karier</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Tanggal Efektif</th>
                        <th class="px-5 py-3">Jenis</th>
                        <th class="px-5 py-3">Jabatan & Dept Lama</th>
                        <th class="px-5 py-3">Jabatan & Dept Baru</th>
                        <th class="px-5 py-3">Penyesuaian Gaji</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->mutations as $m)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $m->effective_date->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-bold uppercase text-red-600 text-[11px]">{{ $m->type }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $m->fromPosition?->title ?? '-' }} ({{ $m->fromDepartment?->name ?? '-' }})</td>
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $m->toPosition?->title ?? '-' }} ({{ $m->toDepartment?->name ?? '-' }})</td>
                            <td class="px-5 py-3 font-mono">
                                Rp {{ number_format($m->from_salary, 0, ',', '.') }} &rarr; <span class="font-bold text-emerald-600">Rp {{ number_format($m->to_salary, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada riwayat mutasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

