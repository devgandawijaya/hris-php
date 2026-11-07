@extends('layouts.app')

@section('title', 'Internal Job Posting (IJP) & Mobilitas Talenta')

@section('content')
<div class="space-y-6" x-data="{ createVacancyModal: false, applyModal: false, selectedVacancy: null, activeTab: 'vacancies' }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                    Talent Mobility Marketplace
                </span>
                <span class="text-xs text-slate-400">• Akselerasi Karir Internal</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Internal Job Posting (IJP) & Mobilitas Karir</h1>
            <p class="text-xs text-slate-500 mt-1">Pembukaan lowongan transfer internal, validasi syarat masa kerja, evaluasi kinerja, dan persetujuan manajer asal.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createVacancyModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buka Lowongan Internal
            </button>
        </div>
    </div>

    <!-- Statutory & Internal Policy Guidance -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 text-xs text-indigo-900 flex items-start gap-3">
        <i data-lucide="compass" class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan & Syarat Mobilitas Internal (IJP):</span>
            <ul class="list-disc list-inside space-y-0.5 text-indigo-800">
                <li>Karyawan wajib memiliki masa kerja minimum di posisi saat ini (standar <strong>12 bulan</strong>) sebelum dapat mengajukan rotasi internal.</li>
                <li>Tidak sedang menjalani masa sanksi disipliner aktif (<strong>bebas Surat Peringatan / SP</strong>).</li>
                <li>Memiliki nilai evaluasi kinerja minimum sesuai batas passing grade lowongan (standar rating $\ge 3.00$).</li>
                <li>Persetujuan mutasi/promosi membutuhkan rekomendasi pelepasan dari Manajer Departemen asal.</li>
            </ul>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 text-xs font-bold gap-6">
        <button @click="activeTab = 'vacancies'" :class="activeTab === 'vacancies' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Lowongan Internal Aktif ({{ $vacancies->count() }})
        </button>
        <button @click="activeTab = 'applications'" :class="activeTab === 'applications' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Daftar Lamaran & Persetujuan ({{ $applications->total() }})
        </button>
    </div>

    <!-- Tab 1: Vacancies List -->
    <div x-show="activeTab === 'vacancies'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($vacancies as $v)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4 hover:border-slate-300 transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                {{ $v->vacancy_code }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900 mt-1">{{ $v->title }}</h3>
                            <p class="text-xs text-slate-500">{{ $v->department->name }} • Posisi: {{ $v->position->title }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $v->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $v->status }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 line-clamp-2">{{ $v->description }}</p>

                    <div class="grid grid-cols-3 gap-2 bg-slate-50 p-3 rounded-xl text-center text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Min. Masa Kerja</span>
                            <span class="font-bold text-slate-800">{{ $v->min_tenure_months }} Bulan</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Min. Nilai Kinerja</span>
                            <span class="font-bold text-slate-800">{{ number_format($v->min_performance_score, 2) }} / 5.0</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Batas Akhir</span>
                            <span class="font-bold text-rose-600">{{ $v->application_deadline->format('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[11px] text-slate-500">
                            <strong>{{ $v->applications->count() }}</strong> Karyawan Melamar • <strong>{{ $v->open_slots }}</strong> Slot
                        </span>
                        @if($v->status === 'open')
                            <button @click="selectedVacancy = {{ $v->toJson() }}; applyModal = true" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 transition inline-flex items-center gap-1.5">
                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                Ajukan Lamaran
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    Belum ada lowongan internal yang dibuka.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 2: Applications & Approvals -->
    <div x-show="activeTab === 'applications'" style="display: none;" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Pengajuan Mobilitas Internal Karyawan</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Karyawan / Divisi Asal</th>
                        <th class="px-4 py-3 font-bold">Posisi Tujuan</th>
                        <th class="px-4 py-3 font-bold">Tenure & Kinerja</th>
                        <th class="px-4 py-3 font-bold">Kelayakan Syarat</th>
                        <th class="px-4 py-3 font-bold">Approval Manajer Asal</th>
                        <th class="px-4 py-3 font-bold">Keputusan Akhir HR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($applications as $app)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $app->employee->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $app->currentDepartment->name }} • {{ $app->currentPosition->title }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $app->vacancy->title }}</div>
                                <div class="text-[10px] text-slate-400">{{ $app->vacancy->vacancy_code }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>Masa Kerja: <strong>{{ $app->current_tenure_months }}</strong> bln</div>
                                <div class="text-[10px] text-slate-400">Rating: {{ number_format($app->latest_performance_score, 2) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($app->is_eligible)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Eligible
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800" title="{{ $app->ineligibility_reason }}">
                                        Tidak Memenuhi Syarat
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($app->manager_approval_status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Disetujui</span>
                                @elseif($app->manager_approval_status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                @else
                                    <div class="flex items-center gap-1">
                                        <form action="{{ route('company.ijp.approve-manager', $app->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="approved">
                                            <button type="submit" class="px-2 py-0.5 rounded bg-emerald-600 text-white font-bold text-[10px] hover:bg-emerald-700">Setujui</button>
                                        </form>
                                        <form action="{{ route('company.ijp.approve-manager', $app->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="rejected">
                                            <button type="submit" class="px-2 py-0.5 rounded bg-rose-600 text-white font-bold text-[10px] hover:bg-rose-700">Tolak</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($app->final_decision === 'accepted')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Diterima Mutasi
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Efektif: {{ $app->transfer_effective_date ? $app->transfer_effective_date->format('d/m/Y') : '-' }}</div>
                                @elseif($app->final_decision === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                @else
                                    <div class="flex items-center gap-1">
                                        <form action="{{ route('company.ijp.decide-hr', $app->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="accepted">
                                            <input type="hidden" name="transfer_effective_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}">
                                            <button type="submit" class="px-2 py-0.5 rounded bg-blue-600 text-white font-bold text-[10px] hover:bg-blue-700">Terima</button>
                                        </form>
                                        <form action="{{ route('company.ijp.decide-hr', $app->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="rejected">
                                            <button type="submit" class="px-2 py-0.5 rounded bg-slate-600 text-white font-bold text-[10px] hover:bg-slate-700">Tolak</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                Belum ada pengajuan lamaran internal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Buat Lowongan IJP -->
    <div x-show="createVacancyModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="createVacancyModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Buka Lowongan Internal Baru (IJP)</h3>
                <button @click="createVacancyModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.ijp.vacancies.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Tujuan</label>
                    <select name="department_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Posisi Jabatan</label>
                    <select name="position_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($positions as $p)
                            <option value="{{ $p->id }}">{{ $p->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Lowongan</label>
                    <input type="text" name="title" placeholder="Contoh: Senior Backend Engineer - FinTech Division" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Tugas</label>
                    <textarea name="description" rows="2" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Persyaratan & Kualifikasi Khusus</label>
                    <textarea name="requirements" rows="2" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required></textarea>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Min. Masa Kerja (Bln)</label>
                        <input type="number" name="min_tenure_months" value="12" min="0" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Min. Skor Kinerja</label>
                        <input type="number" step="0.01" name="min_performance_score" value="3.00" min="1.0" max="5.0" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Jumlah Kuota</label>
                        <input type="number" name="open_slots" value="1" min="1" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Waktu Pendaftaran</label>
                    <input type="date" name="application_deadline" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createVacancyModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Publikasikan Lowongan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Lamar Lowongan IJP -->
    <div x-show="applyModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="applyModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Form Pengajuan Lamaran Internal</h3>
                <button @click="applyModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/company/ijp/vacancies') }}/' + (selectedVacancy ? selectedVacancy.id : '') + '/apply'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan Pelamar</label>
                    <select name="employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }} - {{ $e->position->title ?? 'Staff' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Surat Motivasi / Alasan Minat Rotasi</label>
                    <textarea name="motivation_letter" rows="3" placeholder="Jelaskan aspirasi karir dan kontribusi yang ingin diberikan pada divisi baru..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tautan Portofolio / Berkas Kerja (Opsional)</label>
                    <input type="url" name="portfolio_url" placeholder="https://drive.google.com/..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="applyModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Kirimkan Lamaran</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
