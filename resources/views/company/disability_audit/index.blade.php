@extends('layouts.app')

@section('title', 'Audit Kepatuhan Kuota Tenaga Kerja Disabilitas')

@section('content')
<div class="space-y-6" x-data="{ runAuditModal: false, profileModal: false, activeTab: 'audits' }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                    Kepatuhan UU No. 8/2016
                </span>
                <span class="text-xs text-slate-400">• Pasal 53 Ketenagakerjaan Inklusif</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Audit Kepatuhan Kuota Tenaga Kerja Disabilitas</h1>
            <p class="text-xs text-slate-500 mt-1">Pemenuhan kuota wajib mempekerjakan penyandang disabilitas (1% Swasta / 2% BUMN), evaluasi akomodasi yang layak, dan pelaporan Disnaker.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="profileModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Daftar Profil Disabilitas
            </button>
            <button @click="runAuditModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                Jalankan Audit Kepatuhan
            </button>
        </div>
    </div>

    <!-- Statutory Mandate Banner -->
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-4 text-xs text-teal-900 flex items-start gap-3">
        <i data-lucide="scale" class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Amanat Undang-Undang No. 8 Tahun 2016 tentang Penyandang Disabilitas:</span>
            <ul class="list-disc list-inside space-y-0.5 text-teal-800">
                <li><strong>Pasal 53 ayat (2):</strong> Perusahaan swasta wajib mempekerjakan paling sedikit <strong>1% (satu persen)</strong> Penyandang Disabilitas dari total jumlah pekerja/buruh.</li>
                <li><strong>Pasal 53 ayat (1):</strong> Pemerintah, Pemerintah Daerah, BUMN, dan BUMD wajib mempekerjakan paling sedikit <strong>2% (dua persen)</strong>.</li>
                <li>Pengusaha wajib menyediakan <em>Akomodasi yang Layak (Reasonable Accommodation)</em> dan fasilitas aksesibilitas tempat kerja bebas hambatan.</li>
            </ul>
        </div>
    </div>

    <!-- Latest Audit Status Tiles (if available) -->
    @if($latestAudit)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tenaga Kerja Aktif</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($latestAudit->total_active_workforce) }}</div>
                <span class="text-[11px] text-slate-500">Basis Perhitungan Kuota</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Pekerja Disabilitas</span>
                <div class="text-2xl font-black text-teal-600 mt-1">{{ $latestAudit->disability_workforce_count }} Orang</div>
                <span class="text-[11px] text-slate-500">Rasio Aktual: <strong>{{ $latestAudit->actual_quota_percentage }}%</strong></span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Wajib Minimal Sah</span>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ $latestAudit->statutory_headcount_required }} Orang</div>
                <span class="text-[11px] text-slate-500">Kekurangan: <strong class="{{ $latestAudit->headcount_shortfall > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $latestAudit->headcount_shortfall }} Orang</strong></span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Status Kepatuhan</span>
                <div class="mt-1">
                    @if($latestAudit->compliance_status === 'compliant')
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block">
                            MEMENUHI KUOTA (100%)
                        </span>
                    @elseif($latestAudit->compliance_status === 'near_target')
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-200 inline-block">
                            MENDEKATI KUOTA
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200 inline-block">
                            TIDAK MEMENUHI KUOTA
                        </span>
                    @endif
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Audit: {{ $latestAudit->audit_code }}</span>
            </div>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="flex border-b border-slate-200 text-xs font-bold gap-6">
        <button @click="activeTab = 'audits'" :class="activeTab === 'audits' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Histori Audit Kepatuhan Kuota ({{ $audits->total() }})
        </button>
        <button @click="activeTab = 'profiles'" :class="activeTab === 'profiles' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Daftar Pekerja Disabilitas & Akomodasi ({{ $profiles->count() }})
        </button>
    </div>

    <!-- Tab 1: Audit History -->
    <div x-show="activeTab === 'audits'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Histori Audit Ketenagakerjaan Disabilitas</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Kode Audit / Tanggal</th>
                        <th class="px-4 py-3 font-bold">Entitas Usaha</th>
                        <th class="px-4 py-3 font-bold">Total Pekerja</th>
                        <th class="px-4 py-3 font-bold">Disabilitas Aktual</th>
                        <th class="px-4 py-3 font-bold">Target Wajib Sah</th>
                        <th class="px-4 py-3 font-bold">Status Kepatuhan</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($audits as $a)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $a->audit_code }}</div>
                                <div class="text-[10px] text-slate-400">{{ $a->audit_date->format('d/m/Y') }} (Tahun {{ $a->audit_year }})</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div>{{ $a->entity_type === 'state_owned_bumn' ? 'BUMN / BUMD' : 'Badan Usaha Swasta' }}</div>
                                <div class="text-[10px] text-slate-400">Kuota Sah: {{ $a->statutory_quota_percentage }}%</div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ number_format($a->total_active_workforce) }} Karyawan
                            </td>
                            <td class="px-4 py-3 text-teal-700 font-bold">
                                {{ $a->disability_workforce_count }} Orang ({{ $a->actual_quota_percentage }}%)
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div>Wajib: <strong>{{ $a->statutory_headcount_required }}</strong> Org</div>
                                @if($a->headcount_shortfall > 0)
                                    <div class="text-[10px] text-rose-600 font-bold">Kurang: {{ $a->headcount_shortfall }} Org</div>
                                @else
                                    <div class="text-[10px] text-emerald-600 font-bold">Terpenuhi</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($a->compliance_status === 'compliant')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Patuh (Compliant)
                                    </span>
                                @elseif($a->compliance_status === 'near_target')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Mendekati Kuota
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Belum Patuh
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('company.disability-audit.print', $a->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition inline-block text-[11px] font-semibold">
                                    Cetak Laporan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Belum ada audit ketenagakerjaan disabilitas yang dijalankan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Disability Profiles & Accommodations -->
    <div x-show="activeTab === 'profiles'" style="display: none;" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Pekerja Penyandang Disabilitas & Akomodasi yang Layak</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Karyawan / Divisi</th>
                        <th class="px-4 py-3 font-bold">Ragam Disabilitas</th>
                        <th class="px-4 py-3 font-bold">No. Surat Keterangan Medis</th>
                        <th class="px-4 py-3 font-bold">Akomodasi yang Disediakan</th>
                        <th class="px-4 py-3 font-bold">Rencana Evakuasi Tanggap Darurat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($profiles as $prof)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $prof->employee->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $prof->employee->department->name ?? 'Dept' }} • NIK: {{ $prof->employee->employee_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-100 text-teal-800">
                                    {{ ucfirst(str_replace('_', ' ', $prof->disability_category)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600">
                                {{ $prof->medical_certificate_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $prof->accommodations_provided }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-[11px]">
                                {{ $prof->emergency_evacuation_assistance_plan ?? 'Floor warden pendamping evakuasi.' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Belum ada profil karyawan disabilitas yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Jalankan Audit Baru -->
    <div x-show="runAuditModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl" @click.away="runAuditModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Jalankan Audit Kepatuhan Kuota</h3>
                <button @click="runAuditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500 mb-4">
                Sistem akan menghitung total tenaga kerja aktif dan mengaudit apakah persentase pekerja penyandang disabilitas memenuhi standar wajib UU No. 8/2016 Pasal 53.
            </p>

            <form action="{{ route('company.disability-audit.run') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Klasifikasi Badan Hukum Entitas</label>
                    <select name="entity_type" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        <option value="private_sector">Badan Usaha Swasta (Wajib Minimal 1% Kuota)</option>
                        <option value="state_owned_bumn">BUMN / BUMD / Instansi Pemerintah (Wajib Minimal 2% Kuota)</option>
                    </select>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="runAuditModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Kalkulasi & Jalankan Audit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Daftar Profil Disabilitas -->
    <div x-show="profileModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="profileModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Pendaftaran Profil Karyawan Disabilitas</h3>
                <button @click="profileModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.disability-audit.profiles.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan</label>
                    <select name="employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }}) - NIK: {{ $e->employee_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ragam Disabilitas</label>
                    <select name="disability_category" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        <option value="physical">Penyandang Disabilitas Fisik (Daksa/Amputasi/Kursi Roda)</option>
                        <option value="sensory_vision">Disabilitas Sensorik Netra (Low Vision/Blind)</option>
                        <option value="sensory_hearing">Disabilitas Sensorik Rungu / Wicara (Tuli/Bisu)</option>
                        <option value="intellectual">Disabilitas Intelektual</option>
                        <option value="mental_psychosocial">Disabilitas Mental / Psikososial</option>
                        <option value="multiple">Disabilitas Ganda / Multi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Surat Keterangan Dokter / RS (Opsional)</label>
                    <input type="text" name="medical_certificate_number" placeholder="Contoh: SKD/RSUD/2026/0491" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Akomodasi yang Layak Disediakan Perusahaan</label>
                    <textarea name="accommodations_provided" rows="2" placeholder="Contoh: Akses ramp kursi roda, keyboard braille/screen reader, posisi meja di lantai 1." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Pendampingan Evakuasi Darurat K3</label>
                    <input type="text" name="emergency_evacuation_assistance_plan" value="Didampingi secara khusus oleh safety floor warden tim tanggap darurat gedung." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="profileModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
