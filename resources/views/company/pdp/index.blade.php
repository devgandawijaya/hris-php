@extends('layouts.app')

@section('title', 'Kepatuhan UU Pelindungan Data Pribadi (UU PDP No. 27/2022)')

@section('content')
<div class="space-y-6" x-data="{ consentModal: false, dsarModal: false, breachModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                    Kepatuhan Mutlak UU No. 27/2022 (UU PDP)
                </span>
                <span class="text-xs text-slate-400">• Tata Kelola & Akuntabilitas Pengendali Data</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Pelindungan Data Pribadi (PDP) & Hak Subjek Data</h1>
            <p class="text-xs text-slate-500 mt-1">Pengelolaan persetujuan pemrosesan (consent), pemenuhan hak portabilitas data (DSAR Pasal 13), dan audit notifikasi insiden kebocoran 72 jam (Pasal 46).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="consentModal = true" class="px-3 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="check-square" class="w-4 h-4"></i>
                Catat Persetujuan (Consent)
            </button>
            <button @click="dsarModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                Permohonan Hak (DSAR)
            </button>
            <button @click="breachModal = true" class="px-3 py-2 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                Lapor Insiden Data
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4 text-xs text-purple-900 flex items-start gap-3">
        <i data-lucide="shield-check" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Kunci UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi:</span>
            <ul class="list-disc list-inside space-y-0.5 text-purple-800">
                <li><strong>Dasar Pemrosesan & Persetujuan (Pasal 20):</strong> Pemrosesan data pribadi wajib memiliki persetujuan sah (*explicit consent*) atau pemenuhan kewajiban perjanjian kerja statutori.</li>
                <li><strong>Hak Portabilitas Data (Pasal 13):</strong> Karyawan berhak memperoleh dan/atau menggunakan Data Pribadi miliknya dari Pengendali Data dalam bentuk yang terstruktur dan lazim dibaca sistem komputer.</li>
                <li><strong>Hak Penarikan Persetujuan & Pemusnahan (Pasal 8 & 9):</strong> Subjek data berhak menarik persetujuan dan meminta pemusnahan data jika telah habis masa retensi data ketenagakerjaan.</li>
                <li><strong>Notifikasi Insiden Kebocoran 72 Jam (Pasal 46):</strong> Dalam hal terjadi kegagalan pelindungan data pribadi, Pengendali wajib menyampaikan pemberitahuan tertulis paling lambat **3 x 24 jam (72 jam)** kepada Lembaga Otoritas PDP dan subjek data.</li>
            </ul>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Persetujuan Sah Aktif</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['total_consents'] }} Berkas</div>
            <span class="text-[11px] text-emerald-600 font-medium">Explicit Consent Granted</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">DSAR Sedang Diproses</span>
            <div class="text-xl font-black text-amber-600 mt-1">{{ $stats['pending_dsar'] }} Berkas</div>
            <span class="text-[11px] text-slate-500 font-medium">Batas pemenuhan 72 jam kerja</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">DSAR Selesai Dipenuhi</span>
            <div class="text-xl font-black text-purple-600 mt-1">{{ $stats['fulfilled_dsar'] }} Berkas</div>
            <span class="text-[11px] text-purple-500 font-medium">Hak portabilitas terkirim</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Insiden Kebocoran Tercatat</span>
            <div class="text-xl font-black {{ $stats['total_breaches'] > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">
                {{ $stats['total_breaches'] }} Kasus
            </div>
            <span class="text-[11px] {{ $stats['total_breaches'] > 0 ? 'text-rose-500 font-bold' : 'text-emerald-600' }}">
                {{ $stats['total_breaches'] > 0 ? 'Semua teratasi dalam 72 jam' : 'Nihil insiden data' }}
            </span>
        </div>
    </div>

    <!-- Section 1: DSAR Requests Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="file-check-2" class="w-4 h-4 text-purple-600"></i>
            Daftar Permohonan Hak Subjek Data / DSAR (Pasal 5 - 15 UU PDP)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">No. Tiket DSAR</th>
                        <th class="py-3 px-3">Karyawan Pemohon</th>
                        <th class="py-3 px-3">Tipe Hak Diajukan</th>
                        <th class="py-3 px-3">Tgl Diajukan</th>
                        <th class="py-3 px-3">Batas Waktu (72 Jam)</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dsarRequests as $req)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">{{ $req->request_number }}</td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $req->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $req->employee->department->name ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-50 text-purple-800 border border-purple-100">
                                    {{ str_replace('_', ' ', $req->request_type) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-500">{{ \Carbon\Carbon::parse($req->request_date)->format('d M Y') }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-rose-600">{{ \Carbon\Carbon::parse($req->completion_deadline)->format('d M Y') }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $req->status === 'fulfilled' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('pdp.dsar.export', $req) }}" target="_blank" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-600 text-white hover:bg-purple-700 transition inline-flex items-center gap-1">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    Ekspor Portabilitas
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Belum ada permohonan hak DSAR diajukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $dsarRequests->links() }}
        </div>
    </div>

    <!-- Section 2: Consent Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="check-square-2" class="w-4 h-4 text-emerald-600"></i>
            Log Persetujuan Pemrosesan Data Pribadi (Consent Trail)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Karyawan</th>
                        <th class="py-3 px-3">Tipe Persetujuan</th>
                        <th class="py-3 px-3">Tujuan Pemrosesan</th>
                        <th class="py-3 px-3">Waktu & IP Address</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($consentLogs as $consent)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $consent->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $consent->employee->nik }}</span>
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">{{ str_replace('_', ' ', $consent->consent_type) }}</td>
                            <td class="py-3 px-3 text-slate-600 max-w-xs">{{ $consent->purpose_description }}</td>
                            <td class="py-3 px-3 text-slate-500">
                                <span>{{ $consent->consented_at->format('d M Y H:i') }}</span>
                                <span class="text-[10px] text-slate-400 font-mono block">IP: {{ $consent->ip_address ?? '127.0.0.1' }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $consent->status === 'granted' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $consent->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($consent->status === 'granted')
                                    <form action="{{ route('pdp.consents.revoke', $consent) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded text-[11px] font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 transition">
                                            Cabut Persetujuan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] text-slate-400">Dicabut</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada riwayat persetujuan tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $consentLogs->links() }}
        </div>
    </div>

    <!-- Section 3: Data Breach Logs -->
    @if($breachLogs->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                Log Insiden Kegagalan Pelindungan Data Pribadi (Pasal 46)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                        <tr>
                            <th class="py-3 px-3">No. Insiden</th>
                            <th class="py-3 px-3">Jenis Insiden</th>
                            <th class="py-3 px-3">Waktu Deteksi</th>
                            <th class="py-3 px-3">Data Terdampak</th>
                            <th class="py-3 px-3">Notifikasi Otoritas (Maks 72 Jam)</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($breachLogs as $breach)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3 font-mono font-bold text-slate-900">{{ $breach->incident_number }}</td>
                                <td class="py-3 px-3 font-semibold text-rose-700">{{ str_replace('_', ' ', $breach->breach_type) }}</td>
                                <td class="py-3 px-3 text-slate-500">{{ $breach->detected_at->format('d M Y H:i') }}</td>
                                <td class="py-3 px-3 font-bold text-slate-900">{{ number_format($breach->affected_records_count) }} Record</td>
                                <td class="py-3 px-3 text-emerald-700 font-semibold">
                                    <i data-lucide="check" class="w-3.5 h-3.5 inline"></i> Terkirim ({{ $breach->authority_notified_at->diffForHumans($breach->detected_at) }})
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                        {{ $breach->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Modal Catat Persetujuan -->
    <div x-show="consentModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Catat Persetujuan Pemrosesan Data Pribadi</h3>
                <button @click="consentModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('pdp.consents.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan</label>
                    <select name="employee_id" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Jenis Persetujuan Data</label>
                    <select name="consent_type" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="employment_contract">Perjanjian Kerja & Administrasi Kepegawaian</option>
                        <option value="biometrics_attendance">Data Biometrik Wajah Presensi Kerja</option>
                        <option value="health_insurance_sharing">Pemberian Data Kesehatan ke BPJS & Asuransi CoB</option>
                        <option value="salary_banking">Pemberian Nomor Rekening & Gaji ke Bank Payroll</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tujuan Pemrosesan Spesifik (Pasal 21)</label>
                    <textarea name="purpose_description" rows="2" required class="w-full rounded-xl border-slate-200 text-xs">Pemrosesan data identitas untuk pemenuhan kewajiban penggajian, kepesertaan BPJS, dan hak ketenagakerjaan statutori.</textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="consentModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-purple-600 text-white font-semibold hover:bg-purple-700">Simpan Persetujuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Permohonan Hak DSAR -->
    <div x-show="dsarModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Registrasi Permohonan Hak Subjek Data (DSAR)</h3>
                <button @click="dsarModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('pdp.dsar.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan Pemohon</label>
                    <select name="employee_id" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tipe Hak yang Dimohonkan</label>
                    <select name="request_type" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="data_portability">Hak Portabilitas Data Pribadi (Pasal 13)</option>
                        <option value="data_access">Hak Akses & Memperoleh Salinan Data (Pasal 7)</option>
                        <option value="data_rectification">Hak Koreksi & Pembaruan Data (Pasal 6)</option>
                        <option value="data_erasure">Hak Penghapusan / Pemusnahan Data (Pasal 8)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Keterangan / Ruang Lingkup Permohonan</label>
                    <textarea name="reason_or_scope" rows="2" class="w-full rounded-xl border-slate-200 text-xs" placeholder="Permintaan salinan seluruh riwayat kontrak kerja dan kompensasi dalam format JSON/PDF..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="dsarModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Kirim Permohonan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Lapor Insiden Kebocoran -->
    <div x-show="breachModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Catat Insiden Kebocoran Data (Notifikasi 72 Jam)</h3>
                <button @click="breachModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('pdp.breaches.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tipe Insiden Keamanan</label>
                    <select name="breach_type" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="unauthorized_access">Akses Tidak Sah / Anomali Akun</option>
                        <option value="accidental_leak">Kebocoran Tidak Disengaja (Salah Kirim Lampiran)</option>
                        <option value="credential_theft">Pencurian Kredensial / Phishing</option>
                        <option value="ransomware_threat">Serangan Ransomware / Malware</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Perkiraan Jumlah Record Data Terdampak</label>
                    <input type="number" name="affected_records_count" value="5" min="1" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kronologi & Deskripsi Insiden</label>
                    <textarea name="incident_description" rows="2" required class="w-full rounded-xl border-slate-200 text-xs" placeholder="Penjelasan temuan anomali oleh sistem deteksi keamanan..."></textarea>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Langkah Mitigasi & Remediasi</label>
                    <textarea name="remediation_measures" rows="2" required class="w-full rounded-xl border-slate-200 text-xs" placeholder="Isolasi sesi, rotasi kunci enkripsi, dan pemberitahuan subjek data..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="breachModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-600 text-white font-semibold hover:bg-rose-700">Kirim Notifikasi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
