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
                    @if($employee->reportsTo)
                        <span>Atasan: <strong class="text-slate-800">{{ $employee->reportsTo->full_name }}</strong></span>
                    @endif
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

        <button type="button" @click="activeTab = 'keluarga'"
                :class="activeTab === 'keluarga' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4"></i>
            Keluarga & Tanggungan ({{ $employee->families->count() }})
        </button>

        <button type="button" @click="activeTab = 'pendidikan'"
                :class="activeTab === 'pendidikan' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
            Pendidikan & Karir Dulu
        </button>

        <button type="button" @click="activeTab = 'darurat'"
                :class="activeTab === 'darurat' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="phone-call" class="w-4 h-4"></i>
            Kontak Darurat & Atasan
        </button>
    </div>

    <!-- TAB 1: BIODATA & IDENTITAS PAJAK -->
    <div x-show="activeTab === 'biodata'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-3 text-xs">
            <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-3 border-b border-slate-100 pb-2">Informasi Pribadi</h3>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Email Resmi</span><span class="font-semibold text-slate-800">{{ $employee->email }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Nomor Telepon</span><span class="font-semibold text-slate-800">{{ $employee->phone ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Jenis Kelamin</span><span class="font-semibold text-slate-800">{{ ucfirst($employee->gender) }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Tempat, Tgl Lahir</span><span class="font-semibold text-slate-800">{{ $employee->birth_place ?? '-' }}, {{ $employee->birth_date?->format('d M Y') ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Status Pernikahan</span><span class="font-semibold text-slate-800 capitalize">{{ $employee->marital_status }}</span></div>
            <div class="flex justify-between py-1"><span class="text-slate-400">Status Ketenagakerjaan</span><span class="font-bold uppercase text-red-600">{{ $employee->employment_status }}</span></div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-3 text-xs">
            <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-3 border-b border-slate-100 pb-2">Perpajakan, BPJS & Rekening Bank</h3>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">NIK (KTP)</span><span class="font-mono font-bold text-slate-800">{{ $employee->nik_ktp ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Nomor NPWP</span><span class="font-mono font-semibold text-slate-800">{{ $employee->npwp ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">BPJS Ketenagakerjaan</span><span class="font-mono font-semibold text-slate-800">{{ $employee->bpjs_tk_number ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">BPJS Kesehatan</span><span class="font-mono font-semibold text-slate-800">{{ $employee->bpjs_kes_number ?? '-' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Nama Bank</span><span class="font-semibold text-slate-800">{{ $employee->bank_name ?? '-' }}</span></div>
            <div class="flex justify-between py-1"><span class="text-slate-400">Nomor & Nama Rekening</span><span class="font-mono font-semibold text-slate-800">{{ $employee->bank_account_number ?? '-' }} ({{ $employee->bank_account_holder ?? '-' }})</span></div>
        </div>
    </div>

    <!-- TAB 2: PRESENSI & CUTI -->
    <div x-show="activeTab === 'presensi'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">10 Presensi Terakhir</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr><th class="px-4 py-2.5">Tanggal</th><th class="px-4 py-2.5">Masuk</th><th class="px-4 py-2.5">Pulang</th><th class="px-4 py-2.5">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->attendanceRecords as $att)
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $att->date->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 font-mono">{{ $att->clock_in ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-mono">{{ $att->clock_out ?? '-' }}</td>
                            <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $att->status === 'present' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $att->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada riwayat presensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Riwayat Permohonan Cuti</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr><th class="px-4 py-2.5">Jenis Cuti</th><th class="px-4 py-2.5">Tanggal</th><th class="px-4 py-2.5">Durasi</th><th class="px-4 py-2.5">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->leaveRequests as $lr)
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-slate-900">{{ $lr->leaveType?->name ?? 'Cuti' }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $lr->start_date->format('d M') }} - {{ $lr->end_date->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 font-mono">{{ $lr->total_days }} Hari</td>
                            <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $lr->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $lr->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada riwayat cuti.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: DOKUMEN & BERKAS DIGITAL -->
    <div x-show="activeTab === 'dokumen'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i data-lucide="upload-cloud" class="w-4 h-4 text-red-600"></i> Unggah Dokumen Karyawan
            </h3>
            <form action="{{ route('company.employees.documents.upload', $employee->id) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                @csrf
                <div class="sm:col-span-3">
                    <label class="block font-semibold text-slate-700 mb-1">Jenis Dokumen *</label>
                    <select name="document_type" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="ktp">KTP / Paspor</option>
                        <option value="npwp">Kartu NPWP</option>
                        <option value="cv">CV / Resume</option>
                        <option value="contract">Surat Kontrak Kerja</option>
                        <option value="degree">Ijazah Terakhir</option>
                        <option value="certificate">Sertifikat Pelatihan</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block font-semibold text-slate-700 mb-1">Judul Dokumen *</label>
                    <input type="text" name="title" required placeholder="Contoh: Salinan KTP Asli" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-3">
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Berkas (PDF/Foto)</label>
                    <input type="file" name="file" class="w-full rounded-xl border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">Unggah</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr><th class="px-5 py-3">Jenis</th><th class="px-5 py-3">Judul Berkas</th><th class="px-5 py-3">Ukuran</th><th class="px-5 py-3">Tanggal Unggah</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->documents as $doc)
                        <tr>
                            <td class="px-5 py-3 font-bold uppercase text-slate-800 text-[10px]">{{ $doc->document_type }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $doc->title }}</td>
                            <td class="px-5 py-3 font-mono text-slate-500">{{ $doc->file_size_kb }} KB</td>
                            <td class="px-5 py-3 text-slate-400">{{ $doc->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada berkas dokumen digital.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: RIWAYAT MUTASI & PROMOSI -->
    <div x-show="activeTab === 'mutasi'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="trending-up" class="w-4 h-4 text-red-600"></i> Buat Surat Keputusan (SK) Mutasi / Promosi Baru
            </h3>
            <form action="{{ route('company.employees.mutation', $employee->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Perubahan *</label>
                        <select name="type" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            <option value="promotion">Promosi Jabatan (Naik Pangkat)</option>
                            <option value="transfer">Mutasi Rotasi (Pindah Departemen)</option>
                            <option value="demotion">Demosi Jabatan</option>
                            <option value="status_change">Perubahan Status PKWT &rarr; PKWTT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Baru</label>
                        <select name="to_department_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            <option value="">Tetap di Departemen Saat Ini</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Posisi Jabatan Baru</label>
                        <select name="to_position_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            <option value="">Tetap di Posisi Saat Ini</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->job_level }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Upah Pokok Baru (IDR)</label>
                        <input type="number" step="1000" name="to_salary" value="{{ $employee->basic_salary }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Efektif SK Mutasi *</label>
                        <input type="date" name="effective_date" value="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan SK / Alasan Mutasi</label>
                        <input type="text" name="notes" placeholder="Nomor Surat Keputusan / Pertimbangan Manajemen" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                    Terapkan Perubahan Mutasi
                </button>
            </form>
        </div>

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

    <!-- TAB 5: KELUARGA & TANGGUNGAN (PHASE 10) -->
    <div x-show="activeTab === 'keluarga'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-red-600"></i>
                Tambah Anggota Keluarga / Tanggungan
            </h3>
            <form action="{{ route('company.employees.family.store', $employee->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                @csrf
                <div class="sm:col-span-3">
                    <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required placeholder="Nama anggota keluarga" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Hubungan *</label>
                    <select name="relationship" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="spouse">Pasangan (Suami/Istri)</option>
                        <option value="child">Anak Kandung</option>
                        <option value="parent">Orang Tua</option>
                        <option value="sibling">Saudara Kandung</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                    <select name="gender" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="male">Laki-laki</option>
                        <option value="female">Perempuan</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-3">
                    <label class="block font-semibold text-slate-700 mb-1">NIK KTP / KIA</label>
                    <input type="text" name="nik" placeholder="16 digit NIK" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono">
                </div>
                <div class="sm:col-span-12 flex items-center justify-between pt-2">
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                            <input type="checkbox" name="is_bpjs_covered" value="1" class="rounded text-red-600"> Tanggungan BPJS Kesehatan
                        </label>
                        <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                            <input type="checkbox" name="is_tax_dependent" value="1" class="rounded text-red-600"> Tanggungan Pajak PTKP
                        </label>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition">
                        Simpan Keluarga
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Daftar Anggota Keluarga Terdaftar</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Nama Anggota</th>
                        <th class="px-5 py-3">Hubungan</th>
                        <th class="px-5 py-3">NIK</th>
                        <th class="px-5 py-3">Tgl Lahir</th>
                        <th class="px-5 py-3">Status Tanggungan</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->families as $fam)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $fam->name }}</td>
                            <td class="px-5 py-3.5 capitalize">{{ $fam->relationship }} ({{ $fam->gender }})</td>
                            <td class="px-5 py-3.5 font-mono text-slate-700">{{ $fam->nik ?: '-' }}</td>
                            <td class="px-5 py-3.5">{{ $fam->birth_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    @if($fam->is_bpjs_covered)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">BPJS</span>
                                    @endif
                                    @if($fam->is_tax_dependent)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">PTKP</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.employees.family.delete', [$employee->id, $fam->id]) }}" method="POST" onsubmit="return confirm('Hapus data keluarga ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada data anggota keluarga.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 6: PENDIDIKAN & PENGALAMAN KERJA (PHASE 10) -->
    <div x-show="activeTab === 'pendidikan'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="graduation-cap" class="w-4 h-4 text-red-600"></i>
                Tambah Riwayat Pendidikan Formal
            </h3>
            <form action="{{ route('company.employees.education.store', $employee->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Jenjang *</label>
                    <select name="degree_level" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="S1/D4">S1 / D4</option>
                        <option value="D3">D3</option>
                        <option value="SMA/SMK">SMA / SMK</option>
                        <option value="S2">S2 (Magister)</option>
                        <option value="S3">S3 (Doktor)</option>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block font-semibold text-slate-700 mb-1">Nama Universitas / Sekolah *</label>
                    <input type="text" name="institution_name" required placeholder="Universitas Indonesia" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-3">
                    <label class="block font-semibold text-slate-700 mb-1">Jurusan / Program Studi</label>
                    <input type="text" name="major" placeholder="Teknik Informatika" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-1">
                    <label class="block font-semibold text-slate-700 mb-1">Lulus *</label>
                    <input type="number" name="graduation_year" required value="2022" class="w-full rounded-xl border border-slate-300 px-2 py-2 text-xs font-mono">
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                        + Tambah Edukasi
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Riwayat Pendidikan</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Jenjang</th>
                        <th class="px-5 py-3">Institusi</th>
                        <th class="px-5 py-3">Jurusan</th>
                        <th class="px-5 py-3">Tahun Kelulusan</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->educations as $edu)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $edu->degree_level }}</td>
                            <td class="px-5 py-3.5">{{ $edu->institution_name }}</td>
                            <td class="px-5 py-3.5">{{ $edu->major ?: '-' }}</td>
                            <td class="px-5 py-3.5 font-mono">{{ $edu->graduation_year }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.employees.education.delete', [$employee->id, $edu->id]) }}" method="POST" onsubmit="return confirm('Hapus data pendidikan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat pendidikan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="briefcase" class="w-4 h-4 text-red-600"></i>
                Tambah Pengalaman Kerja Terdahulu
            </h3>
            <form action="{{ route('company.employees.history.store', $employee->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                @csrf
                <div class="sm:col-span-4">
                    <label class="block font-semibold text-slate-700 mb-1">Perusahaan Sebelumnya *</label>
                    <input type="text" name="company_name" required placeholder="PT Jaya Abadi" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-3">
                    <label class="block font-semibold text-slate-700 mb-1">Posisi Jabatan *</label>
                    <input type="text" name="position_title" required placeholder="Senior Officer" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Tgl Mulai</label>
                    <input type="date" name="start_date" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Tgl Selesai</label>
                    <input type="date" name="end_date" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-1 flex items-end">
                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                        + Tambah
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Riwayat Pengalaman Kerja Terdahulu</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Nama Perusahaan</th>
                        <th class="px-5 py-3">Jabatan</th>
                        <th class="px-5 py-3">Masa Bekerja</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->employmentHistories as $hist)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $hist->company_name }}</td>
                            <td class="px-5 py-3.5">{{ $hist->position_title }}</td>
                            <td class="px-5 py-3.5 text-slate-500">
                                {{ $hist->start_date?->format('M Y') ?? '-' }} s/d {{ $hist->end_date?->format('M Y') ?? 'Sekarang' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.employees.history.delete', [$employee->id, $hist->id]) }}" method="POST" onsubmit="return confirm('Hapus data riwayat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-slate-400">Belum ada riwayat pengalaman kerja.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 7: KONTAK DARURAT & ATASAN LANGSUNG (PHASE 10) -->
    <div x-show="activeTab === 'darurat'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="network" class="w-4 h-4 text-red-600"></i>
                Struktur Pelaporan & Atasan Langsung (Supervisor)
            </h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Tetapkan atasan langsung (Direct Manager) yang bertanggung jawab atas evaluasi kinerja dan persetujuan alur kerja karyawan ini.
            </p>

            <form action="{{ route('company.employees.supervisor.assign', $employee->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1 text-xs">Pilih Atasan Langsung:</label>
                    <select name="reports_to_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="">-- Tanpa Atasan / Direksi Utama --</option>
                        @foreach($allEmployees as $mgr)
                            <option value="{{ $mgr->id }}" {{ $employee->reports_to_id == $mgr->id ? 'selected' : '' }}>
                                {{ $mgr->full_name }} ({{ $mgr->position?->title ?? 'Staff' }} - {{ $mgr->department?->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($employee->reportsTo)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center text-xs">
                            {{ substr($employee->reportsTo->first_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">Atasan Saat Ini: {{ $employee->reportsTo->full_name }}</div>
                            <div class="text-[10px] text-slate-500">{{ $employee->reportsTo->position?->title ?? '-' }} • {{ $employee->reportsTo->email }}</div>
                        </div>
                    </div>
                @endif

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Simpan Penugasan Atasan
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="phone-call" class="w-4 h-4 text-red-600"></i>
                Kontak Darurat (Emergency Contacts)
            </h3>

            <form action="{{ route('company.employees.emergency.store', $employee->id) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Kontak *</label>
                        <input type="text" name="contact_name" required placeholder="Nama kerabat" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Hubungan *</label>
                        <input type="text" name="relationship" required placeholder="Istri / Kakak / Ayah" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp *</label>
                        <input type="text" name="phone" required placeholder="081234567890" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alamat Tinggal</label>
                        <input type="text" name="address" placeholder="Kota / Alamat singkat" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition">
                    + Tambah Kontak Darurat
                </button>
            </form>

            <div class="space-y-2 pt-2 border-t border-slate-100">
                @forelse($employee->emergencyContacts as $c)
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-900">{{ $c->contact_name }} <span class="text-slate-400 font-normal">({{ $c->relationship }})</span></div>
                            <div class="text-[11px] font-mono text-slate-600 mt-0.5">{{ $c->phone }}</div>
                        </div>
                        <form action="{{ route('company.employees.emergency.delete', [$employee->id, $c->id]) }}" method="POST" onsubmit="return confirm('Hapus kontak ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs">Hapus</button>
                        </form>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 italic">Belum ada kontak darurat yang didaftarkan.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
