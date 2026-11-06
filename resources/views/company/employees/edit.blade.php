@extends('layouts.app')

@section('title', 'Edit Karyawan: ' . $employee->full_name)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Data Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Perbarui biodata induk, status jabatan, nomor BPJS, dan upah pokok.</p>
        </div>
        <a href="{{ route('company.employees.show', $employee->id) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Profil
        </a>
    </div>

    <form action="{{ route('company.employees.update', $employee->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. IDENTITAS UTAMA & PEKERJAAN -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="briefcase" class="w-4 h-4 text-red-600"></i>
                Informasi Pekerjaan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NIK Karyawan *</label>
                    <input type="text" name="employee_number" value="{{ old('employee_number', $employee->employee_number) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Hubungan Kerja *</label>
                    <select name="employment_status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                        <option value="pkwtt" {{ old('employment_status', $employee->employment_status) === 'pkwtt' ? 'selected' : '' }}>PKWTT (Tetap)</option>
                        <option value="pkwt" {{ old('employment_status', $employee->employment_status) === 'pkwt' ? 'selected' : '' }}>PKWT (Kontrak)</option>
                        <option value="probation" {{ old('employment_status', $employee->employment_status) === 'probation' ? 'selected' : '' }}>Probation</option>
                        <option value="internship" {{ old('employment_status', $employee->employment_status) === 'internship' ? 'selected' : '' }}>Internship</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Operasional *</label>
                    <select name="status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="suspended" {{ old('status', $employee->status) === 'suspended' ? 'selected' : '' }}>Ditangguhkan (Suspended)</option>
                        <option value="resigned" {{ old('status', $employee->status) === 'resigned' ? 'selected' : '' }}>Resign (Mengundurkan Diri)</option>
                        <option value="terminated" {{ old('status', $employee->status) === 'terminated' ? 'selected' : '' }}>Terminasi / PHK</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Bergabung *</label>
                    <input type="date" name="join_date" value="{{ old('join_date', $employee->join_date->format('Y-m-d')) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen</label>
                    <select name="department_id" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                        <option value="">Pilih Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Posisi Jabatan</label>
                    <select name="position_id" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                        <option value="">Pilih Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id', $employee->position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->title }} ({{ $pos->job_level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Akhir Kontrak</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $employee->end_date?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
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
                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Belakang</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Kerja *</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin *</label>
                    <select name="gender" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                        <option value="male" {{ old('gender', $employee->gender) === 'male' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="female" {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $employee->birth_place) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pernikahan *</label>
                    <select name="marital_status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                        <option value="single" {{ old('marital_status', $employee->marital_status) === 'single' ? 'selected' : '' }}>Lajang</option>
                        <option value="married" {{ old('marital_status', $employee->marital_status) === 'married' ? 'selected' : '' }}>Menikah</option>
                        <option value="widowed" {{ old('marital_status', $employee->marital_status) === 'widowed' ? 'selected' : '' }}>Duda / Janda</option>
                        <option value="divorced" {{ old('marital_status', $employee->marital_status) === 'divorced' ? 'selected' : '' }}>Bercerai</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. KEPATUHAN PAJAK & BPJS -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                Kepatuhan Pajak (PTKP) & BPJS
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NIK KTP (16 Digit)</label>
                    <input type="text" name="nik_ktp" value="{{ old('nik_ktp', $employee->nik_ktp) }}" maxlength="16"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status PTKP (PPh 21 TER) *</label>
                    <select name="tax_ptkp_status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                        @foreach($ptkpList as $ptkp)
                            <option value="{{ $ptkp }}" {{ old('tax_ptkp_status', $employee->tax_ptkp_status) === $ptkp ? 'selected' : '' }}>
                                {{ $ptkp }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp', $employee->npwp) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">No. BPJS KTK</label>
                    <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number', $employee->bpjs_tk_number) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">No. BPJS Kesehatan</label>
                    <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number', $employee->bpjs_kes_number) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                </div>
            </div>
        </div>

        <!-- 4. KOMPENSASI & REKENING -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="banknote" class="w-4 h-4 text-emerald-600"></i>
                Kompensasi & Rekening Bank
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Upah Pokok Bulanan *</label>
                    <input type="number" step="1000" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" required
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Atas Nama Rekening</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $employee->bank_account_holder) }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('company.employees.show', $employee->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-semibold shadow-md shadow-red-600/20 transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                Perbarui Data Karyawan
            </button>
        </div>
    </form>

</div>
@endsection
