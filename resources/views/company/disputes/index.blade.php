@extends('layouts.app')

@section('title', 'Penyelesaian Perselisihan Hubungan Industrial (PPHI)')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, recModal: false, activeDispute: null }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                    Kepatuhan UU No. 2 Tahun 2004
                </span>
                <span class="text-xs text-slate-400">• PPHI & Mediasi Tripartit Disnaker</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Penyelesaian Perselisihan Hubungan Industrial (PPHI)</h1>
            <p class="text-xs text-slate-500 mt-1">Perekaman eskalasi perselisihan bipartit ke tahap mediasi Disnaker, Anjuran Tertulis Mediator, Perjanjian Bersama (PB), dan pengawalan gugatan PHI.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Catat Kasus Perselisihan Baru
            </button>
        </div>
    </div>

    <!-- Statutory Mandate Banner -->
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs text-rose-900 flex items-start gap-3">
        <i data-lucide="scale" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan UU No. 2 Tahun 2004 tentang PPHI:</span>
            <ul class="list-disc list-inside space-y-0.5 text-rose-800">
                <li>Meliputi 4 jenis perselisihan: <strong>Perselisihan Hak</strong>, <strong>Perselisihan Kepentingan</strong>, <strong>Perselisihan PHK</strong>, dan <strong>Perselisihan Antar Serikat Pekerja</strong>.</li>
                <li>Wajib melalui perundingan bipartit (maks. 30 hari kerja); jika gagal (*bipartit deadlock*), perselisihan dicatatkan ke Dinas Tenaga Kerja untuk <strong>Mediasi Tripartit</strong>.</li>
                <li>Mediator mengeluarkan <strong>Anjuran Tertulis</strong> dalam waktu selambat-lambatnya 30 hari kerja; jika disetujui dibuat Perjanjian Bersama (PB) yang didaftarkan di Pengadilan Hubungan Industrial (PHI).</li>
            </ul>
        </div>
    </div>

    <!-- Disputes Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Histori Perkara Perselisihan Hubungan Industrial</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">No. Perkara / Tanggal</th>
                        <th class="px-4 py-3 font-bold">Jenis Perselisihan</th>
                        <th class="px-4 py-3 font-bold">Pihak Pekerja / Serikat</th>
                        <th class="px-4 py-3 font-bold">Mediator / Kantor Disnaker</th>
                        <th class="px-4 py-3 font-bold">Anjuran Tertulis</th>
                        <th class="px-4 py-3 font-bold">Status Penyelesaian</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($disputes as $d)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-800 font-mono">{{ $d->case_number }}</span>
                                <div class="text-[10px] text-slate-400">Sidang: {{ $d->mediation_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $d->dispute_type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-800 font-semibold">
                                {{ $d->employee ? $d->employee->full_name : $d->employee_name_external }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="font-semibold">{{ $d->disnaker_mediator_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $d->disnaker_office }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($d->mediator_written_recommendation)
                                    <div class="line-clamp-2 text-[11px]">{{ $d->mediator_written_recommendation }}</div>
                                @else
                                    <button @click="activeDispute = {{ $d->toJson() }}; recModal = true" class="text-blue-600 font-bold hover:underline text-[10px]">
                                        + Catat Anjuran Tertulis
                                    </button>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($d->settlement_status === 'peaceful_agreement_pb')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Perjanjian Bersama (PB)
                                    </span>
                                    @if($d->joint_agreement_number)
                                        <div class="text-[9px] text-slate-400 font-mono mt-0.5">{{ $d->joint_agreement_number }}</div>
                                    @endif
                                @elseif($d->settlement_status === 'escalated_to_phi_court')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Gugatan Pengadilan PHI
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Tahap Mediasi Tripartit
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <a href="{{ route('company.disputes.print', $d->id) }}" target="_blank" class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition inline-block text-[11px] font-semibold">
                                    Cetak Berkas
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Belum ada perkara perselisihan hubungan industrial yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Catat Perkara Baru -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="createModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Catat Kasus Perselisihan Hubungan Industrial</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.disputes.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Klasifikasi Perselisihan (UU No. 2/2004)</label>
                    <select name="dispute_type" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        <option value="perselisihan_hak">Perselisihan Hak (Pelanggaran Peraturan/Norma Kerja)</option>
                        <option value="perselisihan_kepentingan">Perselisihan Kepentingan (Syarat Kerja Baru/Kenaikan Upah)</option>
                        <option value="perselisihan_phk">Perselisihan Pemutusan Hubungan Kerja (PHK & Pesangon)</option>
                        <option value="perselisihan_antar_serikat">Perselisihan Antar Serikat Pekerja dalam 1 Perusahaan</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan Terkait</label>
                        <select name="employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                            <option value="">-- Non-Karyawan / Kuasa Serikat --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Serikat / Kuasa Hukum</label>
                        <input type="text" name="employee_name_external" placeholder="Contoh: PUK Serikat Pekerja Mandiri" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Mediator Hubungan Industrial</label>
                        <input type="text" name="disnaker_mediator_name" placeholder="Contoh: Drs. Bambang Sutrisno, M.H." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kantor Disnaker Penyelenggara</label>
                        <input type="text" name="disnaker_office" value="Suku Dinas Tenaga Kerja dan Transmigrasi Jakarta Selatan" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Sidang Mediasi Tripartit</label>
                    <input type="date" name="mediation_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kronologi & Pokok Perselisihan</label>
                    <textarea name="dispute_chronology" rows="3" placeholder="Uraikan duduk perkara, tuntutan pihak pekerja, dan posisi perundingan bipartit yang gagal..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Simpan Kasus Mediasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Catat Anjuran Tertulis Mediator -->
    <div x-show="recModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="recModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Catat Anjuran Tertulis & Sikap Para Pihak</h3>
                <button @click="recModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/company/disputes') }}/' + (activeDispute ? activeDispute.id : '') + '/recommendation'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Anjuran Tertulis Mediator Disnaker</label>
                    <textarea name="mediator_written_recommendation" rows="4" placeholder="Ketik amar anjuran tertulis resmi yang dikeluarkan mediator Disnaker..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="recModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Simpan Anjuran Tertulis</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
