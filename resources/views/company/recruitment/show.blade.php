@extends('layouts.app')

@section('title', 'Pipeline Rekrutmen: ' . $posting->title)

@section('content')
<div class="space-y-6" x-data="{ openCandidateModal: false, openStageModal: false, openConvertModal: false, selectedAppId: '', selectedAppStage: '', selectedCandidateName: '', selectedOfferedSalary: '' }">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('company.recruitment.index') }}" class="p-2 rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $posting->title }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-700">
                    {{ str_replace('_', ' ', $posting->employment_type) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-4">
                <span><strong>Departemen:</strong> {{ $posting->department?->name ?? 'Semua Bagian' }}</span>
                <span>•</span>
                <span><strong>Lokasi:</strong> {{ $posting->location }}</span>
                <span>•</span>
                <span><strong>Total Pelamar:</strong> {{ $applications->count() }} Orang</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="openCandidateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tambah Pelamar Manual
            </button>
        </div>
    </div>

    <!-- Interactive Pipeline Board (5 Stages) -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <!-- Stage 1: Applied -->
        <div class="bg-slate-100/70 rounded-2xl p-4 space-y-3 border border-slate-200/60">
            <div class="flex items-center justify-between font-bold text-xs text-slate-700 pb-2 border-b border-slate-200">
                <span>1. Berkas Masuk</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-800 text-[10px]">
                    {{ $applications->where('stage', 'applied')->count() }}
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse($applications->where('stage', 'applied') as $app)
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs space-y-2 hover:border-slate-300 transition">
                        <div class="font-bold text-xs text-slate-900">{{ $app->candidate->full_name }}</div>
                        <div class="text-[11px] text-slate-500 truncate">{{ $app->candidate->email }}</div>

                        @if($app->candidate->resume_path)
                            <a href="{{ asset('storage/' . $app->candidate->resume_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-red-600 hover:text-red-800 font-bold">
                                <i data-lucide="file-text" class="w-3 h-3"></i>
                                Unduh CV
                            </a>
                        @endif

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400">{{ $app->created_at->format('d/m/Y') }}</span>
                            <button @click="selectedAppId = '{{ $app->id }}'; selectedAppStage = 'screening'; openStageModal = true"
                                    class="text-[10px] font-bold text-blue-600 hover:text-blue-800">
                                Lolos Screening &rarr;
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-[11px] text-slate-400">Kosong</div>
                @endforelse
            </div>
        </div>

        <!-- Stage 2: Screening -->
        <div class="bg-blue-50/40 rounded-2xl p-4 space-y-3 border border-blue-200/50">
            <div class="flex items-center justify-between font-bold text-xs text-blue-900 pb-2 border-b border-blue-200/60">
                <span>2. Screening HR</span>
                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px]">
                    {{ $applications->where('stage', 'screening')->count() }}
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse($applications->where('stage', 'screening') as $app)
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs space-y-2">
                        <div class="font-bold text-xs text-slate-900">{{ $app->candidate->full_name }}</div>
                        <div class="text-[11px] text-slate-500">{{ $app->candidate->phone ?? '-' }}</div>

                        @if($app->candidate->resume_path)
                            <a href="{{ asset('storage/' . $app->candidate->resume_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-red-600 font-bold">
                                <i data-lucide="file-text" class="w-3 h-3"></i>
                                Unduh CV
                            </a>
                        @endif

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <button @click="selectedAppId = '{{ $app->id }}'; selectedAppStage = 'rejected'; openStageModal = true" class="text-[10px] font-semibold text-rose-600">
                                Tolak
                            </button>
                            <button @click="selectedAppId = '{{ $app->id }}'; selectedAppStage = 'interview'; openStageModal = true" class="text-[10px] font-bold text-blue-600">
                                Jadwal Interview &rarr;
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-[11px] text-slate-400">Kosong</div>
                @endforelse
            </div>
        </div>

        <!-- Stage 3: Interview -->
        <div class="bg-amber-50/40 rounded-2xl p-4 space-y-3 border border-amber-200/50">
            <div class="flex items-center justify-between font-bold text-xs text-amber-900 pb-2 border-b border-amber-200/60">
                <span>3. Sesi Interview</span>
                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px]">
                    {{ $applications->where('stage', 'interview')->count() }}
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse($applications->where('stage', 'interview') as $app)
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs space-y-2">
                        <div class="font-bold text-xs text-slate-900">{{ $app->candidate->full_name }}</div>
                        @if($app->interview_scheduled_at)
                            <div class="text-[10px] text-amber-700 font-semibold flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                {{ $app->interview_scheduled_at->format('d M, H:i') }}
                            </div>
                        @endif
                        @if($app->interview_score)
                            <div class="text-[10px] font-black text-emerald-600">
                                Skor Penilaian: {{ $app->interview_score }}/100
                            </div>
                        @endif

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <button @click="selectedAppId = '{{ $app->id }}'; selectedAppStage = 'rejected'; openStageModal = true" class="text-[10px] font-semibold text-rose-600">
                                Tolak
                            </button>
                            <button @click="selectedAppId = '{{ $app->id }}'; selectedAppStage = 'offered'; openStageModal = true" class="text-[10px] font-bold text-purple-600">
                                Ajukan Offer &rarr;
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-[11px] text-slate-400">Kosong</div>
                @endforelse
            </div>
        </div>

        <!-- Stage 4: Offered -->
        <div class="bg-purple-50/40 rounded-2xl p-4 space-y-3 border border-purple-200/50">
            <div class="flex items-center justify-between font-bold text-xs text-purple-900 pb-2 border-b border-purple-200/60">
                <span>4. Penawaran (Offer)</span>
                <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[10px]">
                    {{ $applications->where('stage', 'offered')->count() }}
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse($applications->where('stage', 'offered') as $app)
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs space-y-2">
                        <div class="font-bold text-xs text-slate-900">{{ $app->candidate->full_name }}</div>
                        @if($app->offered_salary)
                            <div class="text-[11px] font-black text-purple-700">
                                Gaji Ditawarkan: Rp {{ number_format($app->offered_salary, 0, ',', '.') }}
                            </div>
                        @endif

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <button @click="selectedAppId = '{{ $app->id }}'; selectedAppStage = 'rejected'; openStageModal = true" class="text-[10px] font-semibold text-rose-600">
                                Batalkan
                            </button>
                            <button @click="selectedAppId = '{{ $app->id }}'; selectedCandidateName = '{{ $app->candidate->full_name }}'; selectedOfferedSalary = '{{ $app->offered_salary }}'; openConvertModal = true"
                                    class="text-[10px] font-bold px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                Terima (Hire) &rarr;
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-[11px] text-slate-400">Kosong</div>
                @endforelse
            </div>
        </div>

        <!-- Stage 5: Hired -->
        <div class="bg-emerald-50/40 rounded-2xl p-4 space-y-3 border border-emerald-200/50">
            <div class="flex items-center justify-between font-bold text-xs text-emerald-900 pb-2 border-b border-emerald-200/60">
                <span>5. Diterima (Hired)</span>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px]">
                    {{ $applications->where('stage', 'hired')->count() }}
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse($applications->where('stage', 'hired') as $app)
                    <div class="bg-white rounded-xl p-3.5 border border-emerald-200 shadow-xs space-y-2">
                        <div class="font-bold text-xs text-slate-900 flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                            {{ $app->candidate->full_name }}
                        </div>
                        @if($app->convertedEmployee)
                            <div class="text-[10px] text-slate-500">
                                NIK: <strong class="text-slate-800">{{ $app->convertedEmployee->employee_number }}</strong>
                            </div>
                            <a href="{{ route('company.employees.show', $app->convertedEmployee->id) }}" class="inline-block text-[10px] font-bold text-[#DC2626] hover:underline">
                                Buka Profil Karyawan &rarr;
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-6 text-[11px] text-slate-400">Belum ada</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Modal Tambah Pelamar Manual -->
    <div x-show="openCandidateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openCandidateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Daftarkan Pelamar Baru ke Posisi Ini</h3>
                <button @click="openCandidateModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.recruitment.apply', $posting->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Depan <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required placeholder="Contoh: Raden"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Belakang</label>
                        <input type="text" name="last_name" placeholder="Contoh: Wijaya"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required placeholder="raden@email.com"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WA</label>
                        <input type="text" name="phone" placeholder="+62812345678"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unggah Berkas CV / Resume (PDF Maks. 5MB)</label>
                    <input type="file" name="resume" accept="application/pdf"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tautan LinkedIn / Portofolio</label>
                    <input type="url" name="linkedin_url" placeholder="https://linkedin.com/in/..."
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openCandidateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Pelamar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Tahap Pipeline -->
    <div x-show="openStageModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openStageModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-3">Pembaruan Tahap Seleksi Pelamar</h3>

            <form :action="'/recruitment/applications/' + selectedAppId + '/stage'" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="stage" :value="selectedAppStage">

                <div x-show="selectedAppStage === 'interview'">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jadwal Tanggal & Waktu Wawancara</label>
                    <input type="datetime-local" name="interview_scheduled_at"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">

                    <label class="block text-xs font-bold text-slate-700 mt-3 mb-1">Skor Penilaian Interview (0 - 100)</label>
                    <input type="number" step="1" min="0" max="100" name="interview_score" placeholder="Contoh: 85"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">

                    <label class="block text-xs font-bold text-slate-700 mt-3 mb-1">Catatan Evaluasi / Ulasan Pewawancara</label>
                    <textarea name="interview_notes" rows="2" placeholder="Kemampuan teknis, komunikasi, pemecahan masalah..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div x-show="selectedAppStage === 'offered'">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Gaji Ditawarkan (Offering Salary)</label>
                    <input type="number" name="offered_salary" step="500000" placeholder="Contoh: 12000000"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div x-show="selectedAppStage === 'rejected'">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="2" placeholder="Kualifikasi belum sesuai kriteria saat ini..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openStageModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                        Perbarui Tahap
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konversi Pelamar Jadi Pegawai (Hire) -->
    <div x-show="openConvertModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openConvertModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Konversi Kandidat Menjadi Karyawan Resmi</h3>
            <p class="text-xs text-slate-500 mt-0.5">Kandidat <strong x-text="selectedCandidateName"></strong> telah menerima penawaran dan akan dibuatkan data kepegawaian resmi.</p>

            <form :action="'/recruitment/applications/' + selectedAppId + '/convert'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Induk Karyawan (NIK) <span class="text-red-500">*</span></label>
                        <input type="text" name="employee_number" required value="EMP-{{ date('Ymd') }}-{{ rand(100, 999) }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Hubungan Kerja <span class="text-red-500">*</span></label>
                        <select name="employment_status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="pkwt">PKWT (Kontrak Tertentu)</option>
                            <option value="pkwtt">PKWTT (Karyawan Tetap)</option>
                            <option value="probation">Probation (Masa Percobaan)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Departemen <span class="text-red-500">*</span></label>
                        <select name="department_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            @foreach(\App\Models\Department::where('tenant_id', $posting->tenant_id)->get() as $dep)
                                <option value="{{ $dep->id }}" {{ $posting->department_id == $dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                        <select name="position_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            @foreach(\App\Models\Position::where('tenant_id', $posting->tenant_id)->get() as $pos)
                                <option value="{{ $pos->id }}" {{ $posting->position_id == $pos->id ? 'selected' : '' }}>{{ $pos->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai Bekerja (Join Date) <span class="text-red-500">*</span></label>
                        <input type="date" name="join_date" required value="{{ now()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Pokok Awal (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="basic_salary" required :value="selectedOfferedSalary || 8000000"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openConvertModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition shadow-sm">
                        Konfirmasi & Rekrut
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
