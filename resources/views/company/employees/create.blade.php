@extends('layouts.app')

@section('title', 'Tambah Karyawan Baru')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Form Pendaftaran Karyawan Baru</h1>
            <p class="text-xs text-slate-500 mt-1">Lengkapi biodata induk, status kepatuhan pajak (PTKP), dan kompensasi upah.</p>
        </div>
        <a href="{{ route('company.employees.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-1.5 mb-1">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                Mohon periksa kembali kolom input berikut:
            </div>
            @foreach($errors->all() as $err)
                <div>• {{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('company.employees.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. IDENTITAS UTAMA & PEKERJAAN -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="briefcase" class="w-4 h-4 text-red-600"></i>
                Informasi Pekerjaan & Status Hubungan Kerja
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Induk Karyawan (NIK Internal) *</label>
                    <input type="text" name="employee_number" value="{{ old('employee_number', 'EMP-' . date('Y') . '-' . rand(100, 999)) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Hubungan Kerja *</label>
                    <select name="employment_status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="pkwtt" {{ old('employment_status') === 'pkwtt' ? 'selected' : '' }}>PKWTT (Karyawan Tetap)</option>
                        <option value="pkwt" {{ old('employment_status') === 'pkwt' ? 'selected' : '' }}>PKWT (Perjanjian Kerja Waktu Tertentu)</option>
                        <option value="probation" {{ old('employment_status') === 'probation' ? 'selected' : '' }}>Probation (Masa Percobaan)</option>
                        <option value="internship" {{ old('employment_status') === 'internship' ? 'selected' : '' }}>Internship (Magang)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Bekerja (Join Date) *</label>
                    <input type="date" name="join_date" value="{{ old('join_date', now()->toDateString()) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen</label>
                    <select name="department_id" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="">Pilih Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Posisi Jabatan</label>
                    <select name="position_id" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="">Pilih Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->title }} ({{ $pos->job_level }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Berakhir Kontrak (Jika PKWT)</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- 2. BIODATA PRIBADI -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                Biodata Pribadi
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Depan *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Ahmad">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Belakang</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Fauzi">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Kerja *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="ahmad@perusahaan.co.id">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="+628123456789">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin *</label>
                    <select name="gender" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Jakarta">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pernikahan *</label>
                    <select name="marital_status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="single" {{ old('marital_status') === 'single' ? 'selected' : '' }}>Belum Menikah (Lajang)</option>
                        <option value="married" {{ old('marital_status') === 'married' ? 'selected' : '' }}>Menikah</option>
                        <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>Duda / Janda (Meninggal)</option>
                        <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Bercerai</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. KEPATUHAN PAJAK & BPJS INDONESIA -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                Kepatuhan Pajak (PPh 21 TER) & BPJS Ketenagakerjaan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NIK KTP (16 Digit)</label>
                    <input type="text" name="nik_ktp" value="{{ old('nik_ktp') }}" maxlength="16"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-mono" placeholder="3171012304920005">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status PTKP Pajak (PPh 21 TER) *</label>
                    <select name="tax_ptkp_status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-mono">
                        @foreach($ptkpList as $ptkp)
                            <option value="{{ $ptkp }}" {{ old('tax_ptkp_status', 'TK/0') === $ptkp ? 'selected' : '' }}>
                                {{ $ptkp }} ({{ number_format(config('compliance.ptkp.' . $ptkp), 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Pokok Wajib Pajak (NPWP)</label>
                    <input type="text" name="npwp" value="{{ old('npwp') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-mono" placeholder="01.234.567.8-012.000">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">No. BPJS Ketenagakerjaan</label>
                    <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-mono" placeholder="18029384756">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">No. BPJS Kesehatan</label>
                    <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-mono" placeholder="000192837465">
                </div>
            </div>
        </div>

        <!-- 4. PENGGAJIAN & REKENING BANK -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="banknote" class="w-4 h-4 text-emerald-600"></i>
                Kompensasi & Rekening Penggajian
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Upah Pokok Bulanan (IDR) *</label>
                    <input type="number" step="1000" name="basic_salary" value="{{ old('basic_salary', 6000000) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', 'BCA') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="BCA / Mandiri / BNI / BRI">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 font-mono" placeholder="5420987112">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Atas Nama Rekening</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder') }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Sesuai Buku Tabungan">
                </div>
            </div>
        </div>

        <!-- 5. OPSI PEMBUATAN AKUN LOGIN (ESS) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="create_user_account" value="1" checked class="w-4 h-4 mt-0.5 rounded border-slate-300 text-[#DC2626] focus:ring-red-500">
                <div>
                    <div class="text-xs font-bold text-slate-900">Buat Akun Portal Karyawan (ESS) Secara Otomatis</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Karyawan dapat login menggunakan email ini dengan password default: <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-slate-800">Password123#</code> untuk melakukan presensi mandiri dan pengajuan cuti.</div>
                </div>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('company.employees.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-semibold shadow-md shadow-red-600/20 transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                Simpan & Daftarkan Karyawan
            </button>
        </div>
    </form>

</div>
@endsection
