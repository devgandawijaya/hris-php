@extends('layouts.app')

@section('title', 'Pensiun Dini Sukarela (VSS) & Program DPLK')

@section('content')
<div class="space-y-6" x-data="{ programModal: false, applyModal: false, dplkModal: false, selectedProgramId: null }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Kepatuhan PP 35/2021 & UU No. 4/2023 (UU P2SK)
                </span>
                <span class="text-xs text-slate-400">• Rasionalisasi Sukarela & Tabungan Pensiun Lembaga Keuangan</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Pensiun Dini Sukarela (VSS) & Program DPLK</h1>
            <p class="text-xs text-slate-500 mt-1">Kalkulasi paket pesangon pensiun dini berlipat (Golden Handshake) dan pengelolaan iuran DPLK pemberi kerja serta pekerja.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="programModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buka Program VSS Baru
            </button>
            <button @click="dplkModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="landmark" class="w-4 h-4"></i>
                Daftarkan DPLK
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-xs text-emerald-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Program Pensiun Dini (VSS) & DPLK (UU No. 4/2023 P2SK):</span>
            <ul class="list-disc list-inside space-y-0.5 text-emerald-800">
                <li>VSS merupakan program pemutusan hubungan kerja atas dasar kesepakatan sukarela tanpa paksaan, dengan insentif kompensasi di atas standar normatif PP 35/2021 (faktor pengali $1.5\times$ s/d $2.0\times$ PMTK).</li>
                <li>Pekerja yang disetujui berhak atas Uang Pesangon berlipat, UPMK, UPH, dan tambahan dana tunai *Golden Handshake*.</li>
                <li>Iuran DPLK (Dana Pensiun Lembaga Keuangan) dialokasikan secara transparan (standar $5\%$ pemberi kerja & $2.5\%$ pekerja) dan dipisahkan pada rekening kustodian perbankan.</li>
            </ul>
        </div>
    </div>

    <!-- Programs Grid -->
    <div class="space-y-4">
        @forelse($programs as $program)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">{{ $program->title }}</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $program->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $program->status }}
                            </span>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">{{ $program->program_code }} • Periode: {{ $program->open_date->format('d M Y') }} s/d {{ $program->close_date->format('d M Y') }}</span>
                    </div>
                    @if($program->status === 'open')
                        <button @click="selectedProgramId = {{ $program->id }}; applyModal = true" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition flex items-center gap-1.5">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            Ajukan Karyawan
                        </button>
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">Pengali Pesangon (Multiplier):</span>
                        <span class="font-black text-rose-600 text-sm">{{ number_format($program->severance_multiplier, 2) }}x PMTK</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Bonus Golden Handshake:</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format($program->additional_golden_handshake_bonus, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Kuota Pemohon:</span>
                        <span class="font-bold text-slate-800">{{ $program->approved_applicants_count }} / {{ $program->max_quota }} Orang Disetujui</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Permohonan Masuk:</span>
                        <span class="font-semibold text-slate-800">{{ $program->applications->count() }} Permohonan</span>
                    </div>
                </div>

                <!-- Applications Table -->
                @if($program->applications->isNotEmpty())
                    <div class="pt-2 border-t border-slate-100 overflow-x-auto">
                        <span class="text-[10px] font-bold uppercase text-slate-400 mb-2 block">Daftar Permohonan Karyawan:</span>
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="p-2">Karyawan</th>
                                    <th class="p-2">Masa Kerja</th>
                                    <th class="p-2">Gaji Terakhir</th>
                                    <th class="p-2">Estimasi Pesangon Berlipat</th>
                                    <th class="p-2">Total Paket Diterima</th>
                                    <th class="p-2">Status</th>
                                    <th class="p-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($program->applications as $app)
                                    <tr>
                                        <td class="p-2 font-semibold text-slate-800">{{ $app->employee->full_name }}</td>
                                        <td class="p-2">{{ $app->tenure_years }} Tahun</td>
                                        <td class="p-2">Rp {{ number_format($app->last_basic_salary, 0, ',', '.') }}</td>
                                        <td class="p-2 font-bold text-slate-900">Rp {{ number_format($app->calculated_severance_amount, 0, ',', '.') }}</td>
                                        <td class="p-2 font-black text-rose-600">Rp {{ number_format($app->total_package_received, 0, ',', '.') }}</td>
                                        <td class="p-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $app->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $app->status }}
                                            </span>
                                        </td>
                                        <td class="p-2 text-right">
                                            @if($app->status === 'submitted')
                                                <form action="{{ route('vss.approve', $app->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 rounded bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition text-[11px]">
                                                        Setujui VSS
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-[10px] text-slate-400">Efektif: {{ $app->settlement_effective_date ? $app->settlement_effective_date->format('d M Y') : '-' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                Belum ada program pensiun dini sukarela yang dibuka.
            </div>
        @endforelse
    </div>

    <!-- DPLK Overview Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Kepesertaan Dana Pensiun Lembaga Keuangan (DPLK UU P2SK)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="p-2.5">Karyawan</th>
                        <th class="p-2.5">Lembaga Penyelenggara DPLK</th>
                        <th class="p-2.5">Nomor Rekening</th>
                        <th class="p-2.5">Iuran Perusahaan</th>
                        <th class="p-2.5">Iuran Karyawan</th>
                        <th class="p-2.5">Akumulasi Saldo</th>
                        <th class="p-2.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dplkList as $dplk)
                        <tr>
                            <td class="p-2.5 font-semibold text-slate-800">{{ $dplk->employee->full_name }}</td>
                            <td class="p-2.5">{{ $dplk->dplk_provider_name }}</td>
                            <td class="p-2.5 font-mono text-slate-600">{{ $dplk->account_number }}</td>
                            <td class="p-2.5 font-bold text-emerald-700">{{ number_format($dplk->employer_contribution_percentage, 1) }}%</td>
                            <td class="p-2.5 font-bold text-slate-700">{{ number_format($dplk->employee_contribution_percentage, 1) }}%</td>
                            <td class="p-2.5 font-black text-slate-900">Rp {{ number_format($dplk->current_accumulated_fund, 0, ',', '.') }}</td>
                            <td class="p-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800">{{ $dplk->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-slate-400 text-xs">Belum ada data kepesertaan DPLK.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Program Modal -->
    <div x-show="programModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="programModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Buka Program Pensiun Dini Sukarela (VSS) Baru</h3>
                <button @click="programModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('vss.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Judul Program VSS *</label>
                    <input type="text" name="title" required placeholder="Program Pensiun Dini Gelombang I 2026" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Dibuka *</label>
                        <input type="date" name="open_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Ditutup *</label>
                        <input type="date" name="close_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Multiplier Pesangon *</label>
                        <input type="number" step="0.1" name="severance_multiplier" required value="1.5" min="1.0" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Bonus Tali Asih (Rp)</label>
                        <input type="number" name="additional_golden_handshake_bonus" value="15000000" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Target Kuota *</label>
                        <input type="number" name="max_quota" required value="10" min="1" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="programModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-xl hover:bg-[#991B1B] font-semibold">Publikasikan Program</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Apply Modal -->
    <div x-show="applyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="applyModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Daftarkan Karyawan untuk Pensiun Dini</h3>
                <button @click="applyModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="'{{ url('vss') }}/' + selectedProgramId + '/apply'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Pilih Karyawan Pemohon *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_number }} - {{ $emp->full_name }} (Gaji: Rp {{ number_format($emp->basic_salary, 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="applyModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-xl hover:bg-[#991B1B] font-semibold">Proses Kalkulasi & Ajukan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DPLK Modal -->
    <div x-show="dplkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="dplkModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Daftarkan Kepesertaan DPLK Karyawan</h3>
                <button @click="dplkModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('vss.dplk.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Karyawan *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_number }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Lembaga DPLK *</label>
                        <select name="dplk_provider_name" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                            <option value="DPLK Bank Mandiri">DPLK Bank Mandiri</option>
                            <option value="DPLK BNI">DPLK BNI Simponi</option>
                            <option value="DPLK BRI">DPLK BRI</option>
                            <option value="DPLK Manulife Indonesia">DPLK Manulife Indonesia</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nomor Rekening DPLK *</label>
                        <input type="text" name="account_number" required placeholder="8821092812" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Iuran Perusahaan (%) *</label>
                        <input type="number" step="0.1" name="employer_contribution_percentage" required value="5.0" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Iuran Pekerja (%) *</label>
                        <input type="number" step="0.1" name="employee_contribution_percentage" required value="2.5" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="dplkModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-semibold">Simpan DPLK</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
