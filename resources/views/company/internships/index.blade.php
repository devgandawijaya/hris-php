@extends('layouts.app')

@section('title', 'Manajemen Pemagangan Dalam Negeri (Permenaker No. 6/2020)')

@section('content')
<div class="space-y-6" x-data="{ internModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                    Kepatuhan Permenaker No. 6/2020
                </span>
                <span class="text-xs text-slate-400">• Program Pemagangan Berstruktur & Sertifikasi</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Program Pemagangan Terstruktur Dalam Negeri</h1>
            <p class="text-xs text-slate-500 mt-1">Perjanjian pemagangan tertulis, batas kuota 20% tenaga kerja, uang saku harian, jaminan BPJS BPU, dan logbook instruktur.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="internModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Daftarkan Peserta Magang
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-4 text-xs text-teal-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Yuridis Pemagangan Dalam Negeri (Permenaker No. 6 Tahun 2020):</span>
            <ul class="list-disc list-inside space-y-0.5 text-teal-800">
                <li>Bukan merupakan hubungan kerja, melainkan hubungan pemagangan yang diikat melalui <strong>Perjanjian Pemagangan tertulis</strong> (Pasal 9).</li>
                <li>Jangka waktu pemagangan diselenggarakan paling lama <strong>1 (satu) tahun</strong> (Pasal 5).</li>
                <li><strong>Batas Kuota Pemagang</strong>: Jumlah peserta pemagangan di perusahaan paling banyak <strong>20% (dua puluh persen)</strong> dari jumlah seluruh pekerja (Pasal 10).</li>
                <li>Hak peserta: Memperoleh <strong>uang saku</strong> (transport/makan), fasilitas K3, dan kepesertaan jaminan sosial (BPJS Ketenagakerjaan BPU: JKK & JKM) (Pasal 13).</li>
            </ul>
        </div>
    </div>

    <!-- Internships Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Peserta Pemagangan Aktif</h2>
            <div class="text-xs text-slate-500">
                Kuota Pemagang: <span class="font-bold text-slate-800">{{ $internships->total() }}</span> / {{ max(1, (int) ceil($totalWorkforce * 0.20)) }} Peserta (Batas 20%)
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="p-3">Nama & Asal Institusi</th>
                        <th class="p-3">Program & Departemen</th>
                        <th class="p-3">Periode & Durasi</th>
                        <th class="p-3">Uang Saku & BPJS BPU</th>
                        <th class="p-3">Pembimbing (Mentor)</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($internships as $intern)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3">
                                <div class="font-bold text-slate-900">{{ $intern->intern_candidate_name }}</div>
                                <span class="text-[10px] text-slate-400">{{ $intern->institution_origin }} (NIK: {{ $intern->intern_nik_ktp }})</span>
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-slate-800">{{ $intern->program_title }}</div>
                                <span class="text-[10px] text-slate-400">{{ $intern->department->name ?? 'Lintas Divisi' }}</span>
                            </td>
                            <td class="p-3">
                                <div>{{ $intern->start_date->format('d M Y') }} s/d {{ $intern->end_date->format('d M Y') }}</div>
                                <span class="text-[10px] font-mono text-slate-500">{{ $intern->agreement_letter_number }}</span>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-slate-800">Rp {{ number_format($intern->pocket_money_allowance_daily, 0, ',', '.') }} / hari</div>
                                <span class="text-[10px] px-1.5 py-0.2 rounded {{ $intern->bpjs_bpu_covered ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                                    BPJS BPU: {{ $intern->bpjs_bpu_covered ? 'Tercover JKK/JKM' : 'Belum' }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-700">
                                {{ $intern->mentor->full_name ?? 'Belum Ditunjuk' }}
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $intern->status === 'active' ? 'bg-teal-100 text-teal-800' : ($intern->status === 'converted_to_employee' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ str_replace('_', ' ', $intern->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-right space-x-1">
                                <a href="{{ route('internships.show', $intern->id) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition inline-flex items-center gap-1">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                                    Logbook
                                </a>
                                <a href="{{ route('internships.print-certificate', $intern->id) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-teal-50 text-teal-700 hover:bg-teal-100 transition inline-flex items-center gap-1">
                                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                                    Sertifikat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-xs">Belum ada peserta pemagangan yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($internships->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $internships->links() }}
            </div>
        @endif
    </div>

    <!-- Create Intern Modal -->
    <div x-show="internModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="internModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Daftarkan Peserta Pemagangan Baru</h3>
                <button @click="internModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('internships.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama Lengkap Peserta *</label>
                        <input type="text" name="intern_candidate_name" required placeholder="Ahmad Rizky Pratama" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">NIK KTP Peserta *</label>
                        <input type="text" name="intern_nik_ktp" required placeholder="3271012304990001" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Asal Lembaga Pendidikan *</label>
                        <input type="text" name="institution_origin" required placeholder="Universitas Indonesia / SMKN 1" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Departemen Penempatan</label>
                        <select name="department_id" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                            <option value="">-- Tanpa Departemen --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Judul / Kurikulum Pemagangan *</label>
                    <input type="text" name="program_title" required placeholder="Software Engineering & Cloud Architecture Internship" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Selesai (Maks 1 Tahun) *</label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Uang Saku / Transport Harian (Rp) *</label>
                        <input type="number" name="pocket_money_allowance_daily" required value="75000" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Pembimbing / Mentor Pegawai Tetap</label>
                        <select name="mentor_employee_id" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                            <option value="">-- Pilih Pembimbing --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-teal-50 border border-teal-200 rounded-xl flex items-center gap-2">
                    <input type="checkbox" name="bpjs_bpu_covered" value="1" checked class="rounded border-teal-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-teal-900 font-medium">Didaftarkan dalam program BPJS Ketenagakerjaan BPU (JKK & JKM)</span>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="internModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-xl hover:bg-[#991B1B] font-semibold">Simpan Pemagang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
