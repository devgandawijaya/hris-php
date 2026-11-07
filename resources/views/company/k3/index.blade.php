@extends('layouts.app')

@section('title', 'K3 & Klaim JKK BPJS Ketenagakerjaan')

@section('content')
<div class="space-y-6" x-data="{ incidentModal: false, jkkModal: false, mcuModal: false, activeIncident: {} }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    K3 & SMK3 Indonesia
                </span>
                <span class="text-xs text-slate-400">• UU No. 1/1970 & PP No. 50/2012</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Keselamatan, Kesehatan Kerja & Klaim JKK BPJS</h1>
            <p class="text-xs text-slate-500 mt-1">Perekaman insiden kecelakaan kerja, klaim santunan STMB JKK BPJS Ketenagakerjaan, dan surveilans MCU berkala.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="mcuModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="stethoscope" class="w-4 h-4 text-emerald-600"></i>
                Rekam MCU Berkala
            </button>
            <button @click="incidentModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                Lapor Insiden K3
            </button>
        </div>
    </div>

    <!-- K3 Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Total Insiden K3 ({{ $selectedYear }})</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $metrics['total_incidents'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Near-miss: {{ $metrics['near_miss_count'] }} kejadian</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Hari Kerja Hilang (LTI)</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="calendar-x" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $metrics['total_lost_workdays'] }} <span class="text-xs font-normal text-slate-500">hari</span></div>
            <div class="text-[11px] text-slate-500 mt-1">Lost Time Injury: {{ $metrics['lti_count'] }} kasus</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Surveilans MCU Karyawan</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="heart-pulse" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $mcuRecords->count() }} <span class="text-xs font-normal text-slate-500">record</span></div>
            <div class="text-[11px] text-slate-500 mt-1">Pemeriksaan kesehatan berkala K3</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Investigasi Terbuka</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $metrics['open_investigations'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Tindakan korektif sedang diproses</div>
        </div>
    </div>

    <!-- Incidents Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Buku Register Kecelakaan Kerja & Insiden</h2>
            <form action="{{ route('company.k3.index') }}" method="GET" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()" class="px-2.5 py-1 border border-slate-300 rounded-lg text-xs font-semibold">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">No. Register / Tanggal</th>
                        <th class="px-4 py-3 font-bold">Korban / Lokasi</th>
                        <th class="px-4 py-3 font-bold">Tingkat Keparahan</th>
                        <th class="px-4 py-3 font-bold">Hari Hilang</th>
                        <th class="px-4 py-3 font-bold">Klaim JKK BPJS</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($incidents as $inc)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 font-mono">{{ $inc->incident_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $inc->incident_date->format('d M Y') }} {{ $inc->incident_time ? '(' . substr($inc->incident_time, 0, 5) . ')' : '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $inc->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $inc->location }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($inc->severity === 'fatality')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-black text-white">Fatality</span>
                                @elseif($inc->severity === 'lost_time_injury_lti')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">LTI (Cidera Berat)</span>
                                @elseif($inc->severity === 'minor_first_aid')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">P3K / Minor</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Near Miss</span>
                                @endif
                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $inc->injury_type ?? 'Tidak ada luka permanen' }}</div>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-700">
                                {{ $inc->lost_workdays }} hari
                            </td>
                            <td class="px-4 py-3">
                                @if($inc->bpjsJkkClaim)
                                    <div>
                                        <a href="{{ route('company.k3.jkk-claim.print', $inc->bpjsJkkClaim->id) }}" target="_blank" class="font-mono text-emerald-700 hover:underline font-bold">
                                            {{ $inc->bpjsJkkClaim->claim_number }}
                                        </a>
                                        <div class="text-[10px] text-slate-500">{{ strtoupper($inc->bpjsJkkClaim->claim_status) }}</div>
                                    </div>
                                @else
                                    <button @click="activeIncident = {{ json_encode($inc) }}; jkkModal = true" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded font-bold text-[11px] border border-emerald-200">
                                        + Ajukan JKK
                                    </button>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $inc->status === 'closed' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800' }}">
                                    {{ strtoupper($inc->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($inc->bpjsJkkClaim)
                                    <a href="{{ route('company.k3.jkk-claim.print', $inc->bpjsJkkClaim->id) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold inline-flex items-center gap-1">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Cetak JKK
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">Belum ada catatan insiden kecelakaan kerja pada tahun ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $incidents->links() }}
        </div>
    </div>

    <!-- MCU Records Grid -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
            <i data-lucide="heart-pulse" class="w-4 h-4 text-emerald-600"></i>
            Surveilans Kesehatan Kerja (Medical Check-Up Berkala)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($mcuRecords as $mcu)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900">{{ $mcu->employee->full_name }}</span>
                        @if($mcu->fitness_category === 'fit_to_work')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Fit to Work</span>
                        @elseif($mcu->fitness_category === 'fit_with_restriction')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Fit (Bersyarat)</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Unfit</span>
                        @endif
                    </div>
                    <div class="text-slate-500 text-[11px]">{{ $mcu->mcu_date->format('d M Y') }} • {{ $mcu->clinic_provider }}</div>
                    <div class="grid grid-cols-3 gap-1 pt-1 text-[11px] text-center">
                        <div class="p-1.5 bg-white rounded border border-slate-200">
                            <div class="text-[10px] text-slate-400">Tekanan Darah</div>
                            <div class="font-bold text-slate-800">{{ $mcu->blood_pressure ?? '-' }}</div>
                        </div>
                        <div class="p-1.5 bg-white rounded border border-slate-200">
                            <div class="text-[10px] text-slate-400">Kolesterol</div>
                            <div class="font-bold text-slate-800">{{ $mcu->cholesterol ? $mcu->cholesterol . ' mg/dL' : '-' }}</div>
                        </div>
                        <div class="p-1.5 bg-white rounded border border-slate-200">
                            <div class="text-[10px] text-slate-400">Gula Darah</div>
                            <div class="font-bold text-slate-800">{{ $mcu->blood_sugar ? $mcu->blood_sugar . ' mg/dL' : '-' }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-6 text-slate-400 italic text-xs">Belum ada catatan MCU berkala.</div>
            @endforelse
        </div>
    </div>

    <!-- Report Incident Modal -->
    <div x-show="incidentModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Lapor Insiden K3 / Kecelakaan Kerja</h3>
                <button @click="incidentModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('company.k3.incidents.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Karyawan Korban</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->department?->name ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kejadian</label>
                        <input type="date" name="incident_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Waktu / Jam Kejadian</label>
                        <input type="time" name="incident_time" value="{{ date('H:i') }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lokasi Kejadian Spesifik</label>
                    <input type="text" name="location" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Area Gudang Blok B / Jalur Produksi 2">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tingkat Keparahan</label>
                        <select name="severity" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                            <option value="minor_first_aid">P3K / Cidera Ringan</option>
                            <option value="lost_time_injury_lti">LTI (Hari Kerja Hilang / Rawat Inap)</option>
                            <option value="near_miss">Hampir Celaka (Near Miss)</option>
                            <option value="fatality">Fatality (Kematian)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Estimasi Hari Kerja Hilang</label>
                        <input type="number" name="lost_workdays" value="0" min="0" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Uraian Kejadian Kronologis</label>
                    <textarea name="description" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Jelaskan kronologi kejadian secara faktual..."></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tindakan Korektif & Preventif (CAPA)</label>
                    <textarea name="corrective_actions" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Tindakan perbaikan agar insiden tidak terulang..."></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="incidentModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-lg font-bold">Simpan Laporan K3</button>
                </div>
            </form>
        </div>
    </div>

    <!-- File BPJS JKK Claim Modal -->
    <div x-show="jkkModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Pengajuan Klaim JKK BPJS Ketenagakerjaan</h3>
                <button @click="jkkModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'/k3/incidents/' + activeIncident.id + '/jkk-claim'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <div class="text-[11px] text-slate-500 font-bold uppercase mb-1">Nomor Insiden Terkait</div>
                    <div class="font-bold text-slate-900 font-mono" x-text="activeIncident.incident_number"></div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tahap Pelaporan</label>
                    <select name="stage" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                        <option value="tahap_1">Tahap I (Laporan Kecelakaan Kerja 2x24 Jam)</option>
                        <option value="tahap_2">Tahap II (Penetapan Klaim Akhir & Sembuh)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Fasilitas Kesehatan / Rumah Sakit PLKK</label>
                    <input type="text" name="medical_facility_name" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="RS Mitra Keluarga / RSUD PLKK BPJS">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Estimasi Biaya Pengobatan (Rp)</label>
                    <input type="number" name="estimated_medical_expense" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="5000000">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan untuk Petugas BPJS</label>
                    <textarea name="bpjs_notes" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Lampiran rontgen, kronologis saksi, dll..."></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="jkkModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold">Kirim Berkas JKK</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Record MCU Modal -->
    <div x-show="mcuModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Rekam Hasil Medical Check-Up (MCU)</h3>
                <button @click="mcuModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('company.k3.mcu.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Karyawan</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->department?->name ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal MCU</label>
                        <input type="date" name="mcu_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Klinik / Laboratorium</label>
                        <input type="text" name="clinic_provider" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Prodia / Kimia Farma">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tekanan Darah</label>
                        <input type="text" name="blood_pressure" class="w-full px-2.5 py-2 border border-slate-200 rounded-lg" placeholder="120/80">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kolesterol</label>
                        <input type="number" step="0.1" name="cholesterol" class="w-full px-2.5 py-2 border border-slate-200 rounded-lg" placeholder="180">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Gula Darah</label>
                        <input type="number" step="0.1" name="blood_sugar" class="w-full px-2.5 py-2 border border-slate-200 rounded-lg" placeholder="95">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status Kebugaran Kerja (Fitness Category)</label>
                    <select name="fitness_category" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        <option value="fit_to_work">Fit to Work (Sehat & Prima)</option>
                        <option value="fit_with_restriction">Fit with Restriction (Boleh Kerja dengan Batasan)</option>
                        <option value="temporarily_unfit">Temporarily Unfit (Istirahat Sementara)</option>
                        <option value="unfit">Unfit (Tidak Laik Kerja)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Dokter Pemeriksa</label>
                    <textarea name="doctor_notes" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Anjuran diet rendah lemak, olahraga teratur..."></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="mcuModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold">Simpan Hasil MCU</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
