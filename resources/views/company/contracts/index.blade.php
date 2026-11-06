@extends('layouts.app')

@section('title', 'Manajemen Kontrak PKWT & Kompensasi PP 35/2021')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false, openRenewModal: false, renewActionUrl: '', renewContractNumber: '' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Kontrak PKWT & Kompensasi</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                    PP 35/2021 Pasal 61A
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Pantau masa berlaku kontrak kerja, peringatan kedaluwarsa, dan perhitungan otomatis uang kompensasi PKWT.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Buat Kontrak Baru
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900 text-xs flex items-start gap-3">
        <i data-lucide="scale" class="w-5 h-5 text-purple-600 shrink-0 mt-0.5"></i>
        <div>
            <strong class="font-bold">Ketentuan Kompensasi PKWT PP No. 35/2021:</strong>
            <p class="mt-0.5 text-purple-800 leading-relaxed">
                Pengusaha wajib memberikan uang kompensasi kepada pekerja PKWT yang telah mempunyai masa kerja minimal 1 (satu) bulan secara terus-menerus saat kontrak berakhir atau diperpanjang. Rumus perhitungan: <code>(Masa Kerja dalam Bulan / 12) &times; 1 Bulan Upah</code>.
            </p>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kontrak Aktif</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total_active']) }} Kontrak</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Sedang berjalan</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Berakhir &le; 30 Hari</div>
            <div class="text-xl font-black text-amber-600 mt-1">{{ number_format($stats['expiring_soon']) }} Kontrak</div>
            <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Perlu tindak lanjut / perpanjangan</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Kompensasi Terutang</div>
            <div class="text-lg font-black text-[#DC2626] mt-1 truncate">Rp {{ number_format($stats['total_compensation_due'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Kewajiban PP 35/2021</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Karyawan Tetap (PKWTT)</div>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($stats['total_pkwtt']) }} Orang</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Perjanjian Kerja Waktu Tidak Tertentu</div>
        </div>
    </div>

    <!-- Filter & Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <h2 class="text-base font-bold text-slate-900">Daftar Kontrak Kerja & Kewajiban Kompensasi</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('company.contracts.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
                <a href="{{ route('company.contracts.index', ['status' => 'active']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'active' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Aktif</a>
                <a href="{{ route('company.contracts.index', ['status' => 'expiring_soon']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'expiring_soon' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Segera Berakhir</a>
                <a href="{{ route('company.contracts.index', ['status' => 'expired']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'expired' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Kedaluwarsa</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">No. Kontrak & Jenis</th>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Periode Kontrak</th>
                        <th class="px-5 py-3.5 text-right">Upah Pokok</th>
                        <th class="px-5 py-3.5 text-right font-black text-purple-900">Kompensasi PP 35/2021</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($contracts as $c)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="file-signature" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $c->contract_number }}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase
                                        @if($c->type === 'pkwt') bg-amber-50 text-amber-700
                                        @elseif($c->type === 'pkwtt') bg-emerald-50 text-emerald-700
                                        @elseif($c->type === 'probation') bg-blue-50 text-blue-700
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $c->type }}
                                    </span>
                                    @if($c->renewal_count > 0)
                                        <span class="text-[10px] text-slate-500 font-medium">Perpanjangan ke-{{ $c->renewal_count }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $c->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $c->employee->employee_number }} • {{ $c->employee->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <div>{{ $c->start_date->format('d M Y') }} s.d. {{ $c->end_date ? $c->end_date->format('d M Y') : 'Permanen' }}</div>
                                @if($c->end_date && $c->status === 'expiring_soon')
                                    <div class="text-[10px] font-bold text-amber-600 mt-0.5">
                                        Sisa {{ round((strtotime($c->end_date) - strtotime(today())) / 86400) }} hari lagi
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-slate-800">
                                Rp {{ number_format($c->monthly_wage, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($c->type === 'pkwt' && $c->compensation_calculated > 0)
                                    <div class="font-black text-purple-900">
                                        Rp {{ number_format($c->compensation_calculated, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] font-bold {{ $c->compensation_paid ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $c->compensation_paid ? 'Lunas Dibayar' : 'Belum Dibayar' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($c->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @elseif($c->status === 'expiring_soon')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Segera Berakhir
                                    </span>
                                @elseif($c->status === 'renewed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Diperpanjang
                                    </span>
                                @elseif($c->status === 'expired')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Kedaluwarsa
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($c->type === 'pkwt' && $c->compensation_calculated > 0 && !$c->compensation_paid)
                                        <form action="{{ route('company.contracts.mark-compensation', $c->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-purple-600 text-white font-bold text-[10px] hover:bg-purple-700 transition" title="Tandai Kompensasi Lunas">
                                                Bayar Kompensasi
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($c->status, ['active', 'expiring_soon']))
                                        <button @click="renewActionUrl = '{{ route('company.contracts.renew', $c->id) }}'; renewContractNumber = '{{ $c->contract_number }}-EXT'; openRenewModal = true"
                                                class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 font-bold text-[10px] text-slate-700 transition">
                                            Perpanjang
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="file-signature" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Belum ada kontrak kerja tersimpan.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Buat Kontrak Baru"</strong> untuk mendaftarkan perjanjian kerja PKWT/PKWTT.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contracts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Buat Kontrak Baru -->
    <div x-show="openCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openCreateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Buat Perjanjian Kerja Baru</h3>
                <button @click="openCreateModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.contracts.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }}) - Gaji Pokok: Rp {{ number_format($emp->basic_salary, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Surat Kontrak <span class="text-red-500">*</span></label>
                        <input type="text" name="contract_number" required placeholder="PKWT/SDN/{{ date('Y') }}/001"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Perjanjian <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="pkwt">PKWT (Waktu Tertentu / Kontrak)</option>
                            <option value="pkwtt">PKWTT (Tetap / Permanen)</option>
                            <option value="probation">Masa Percobaan (Probation)</option>
                            <option value="internship">Magang (Internship)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required value="{{ now()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Berakhir (Kosongkan jika PKWTT)</label>
                        <input type="date" name="end_date" value="{{ now()->addYear()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Upah Pokok Bulanan (Dasar Kompensasi) <span class="text-red-500">*</span></label>
                    <input type="number" name="monthly_wage" required min="0" step="50000" placeholder="Contoh: 6000000"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-[11px] text-slate-400 mt-1">Uang kompensasi PP 35/2021 akan dihitung otomatis: <code>(Bulan / 12) &times; Upah</code>.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unggah Berkas Dokumen Kontrak (PDF)</label>
                    <input type="file" name="document" accept="application/pdf"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Ketentuan khusus atau klausul penting..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Kontrak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Perpanjangan Kontrak -->
    <div x-show="openRenewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openRenewModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-2">Perpanjangan Kontrak Kerja (Addendum)</h3>
            <p class="text-xs text-slate-500 mb-4">Perpanjang masa berlaku kontrak dengan nomor addendum baru.</p>

            <form :action="renewActionUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Kontrak Baru / Addendum <span class="text-red-500">*</span></label>
                    <input type="text" name="contract_number" required :value="renewContractNumber"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Perjanjian <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="pkwt">PKWT (Perpanjangan Waktu Tertentu)</option>
                        <option value="pkwtt">Pengangkatan PKWTT (Karyawan Tetap)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required value="{{ now()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Berakhir Baru</label>
                        <input type="date" name="end_date" value="{{ now()->addYear()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Upah Pokok Baru (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="monthly_wage" required min="0" step="50000" placeholder="Contoh: 6500000"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unggah Addendum Kontrak (PDF)</label>
                    <input type="file" name="document" accept="application/pdf"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="openRenewModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 transition shadow-sm">
                        Proses Perpanjangan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
