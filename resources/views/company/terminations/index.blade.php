@extends('layouts.app')

@section('title', 'Kompensasi PHK & Pesangon (PP 35/2021)')

@section('content')
<div class="space-y-6" x-data="{ 
    openModal: false,
    selectedEmpId: '',
    employeesList: {{ Js::from($employees) }},
    terminationType: 'resignation',
    terminationDate: '{{ date('Y-m-d') }}',
    lastSalary: 0,
    remainingLeaveDays: 0,
    separationPay: 0,
    calcResult: null,
    isCalculating: false,

    onEmployeeSelect() {
        const emp = this.employeesList.find(e => e.id == this.selectedEmpId);
        if (emp) {
            this.lastSalary = emp.base_salary ? parseFloat(emp.base_salary) : 0;
            this.runCalculation();
        }
    },

    runCalculation() {
        if (!this.selectedEmpId) return;
        this.isCalculating = true;

        fetch('{{ route('company.terminations.calculate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                employee_id: this.selectedEmpId,
                termination_type: this.terminationType,
                termination_date: this.terminationDate,
                last_salary: this.lastSalary,
                remaining_leave_days: this.remainingLeaveDays,
                separation_pay_amount: this.separationPay
            })
        })
        .then(res => res.json())
        .then(data => {
            this.calcResult = data;
            this.isCalculating = false;
        })
        .catch(err => {
            console.error(err);
            this.isCalculating = false;
        });
    }
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Kompensasi PHK & Pesangon Karyawan</h1>
            <p class="text-sm text-slate-500 mt-1">Kalkulator dan administrasi resmi Uang Pesangon (UP), UPMK, UPH, dan Uang Pisah sesuai PP No. 35/2021 & UU Cipta Kerja.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                Hitung & Terbitkan SK PHK
            </button>
        </div>
    </div>

    <!-- Statutory Law Reminder Alert -->
    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-950 flex items-start gap-3 text-xs leading-relaxed">
        <i data-lucide="scale" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5"></i>
        <div>
            <span class="font-bold">Ketentuan Perhitungan Kompensasi PHK (PP No. 35 Tahun 2021 Pasal 40-52):</span>
            Kompensasi pemutusan hubungan kerja terdiri atas <strong>Uang Pesangon (UP)</strong> (1 s/d 9 bulan upah), <strong>Uang Penghargaan Masa Kerja (UPMK)</strong> (2 s/d 10 bulan upah), <strong>Uang Penggantian Hak (UPH)</strong> (sisa cuti tahunan dll), serta <strong>Uang Pisah</strong> bagi karyawan yang mengundurkan diri atas kemauan sendiri sesuai ketentuan PKB/PP Perusahaan.
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Terminasi</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_terminations']) }} Karyawan</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Seluruh berkas terminasi & PHK</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Kompensasi Diterbitkan</div>
            <div class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($stats['total_severance_payout'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Beban pesangon legal</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Telah Dibayarkan (Paid)</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['paid_count']) }} Berkas</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Lunas & diserahterimakan</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu Pencairan</div>
            <div class="text-xl font-black text-amber-600 mt-1">Rp {{ number_format($stats['pending_payout'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Outstanding payout</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Daftar Kompensasi & Pesangon PHK Karyawan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Catatan hukum penyelesaian pemutusan hubungan kerja dan hak finansial pekerja.</p>
            </div>

            <!-- Filter Status -->
            <form action="{{ route('company.terminations.index') }}" method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Status</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. SK & Tgl PHK</th>
                        <th class="py-3 px-4">Karyawan & Masa Kerja</th>
                        <th class="py-3 px-4">Alasan PHK</th>
                        <th class="py-3 px-4">Uang Pesangon (UP)</th>
                        <th class="py-3 px-4">UPMK & UPH</th>
                        <th class="py-3 px-4">Total Kompensasi</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($terminations as $t)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $t->letter_number }}</div>
                                <div class="text-[10px] text-slate-400">{{ $t->termination_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $t->employee?->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $t->employee?->employee_code }} &bull; Masa Kerja: <strong class="text-slate-700">{{ $t->years_of_service }} thn {{ $t->months_of_service }} bln</strong></div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($t->termination_type === 'resignation')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-800">
                                        Resign Sukarela
                                    </span>
                                @elseif($t->termination_type === 'efficiency')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                                        Efisiensi Perusahaan (0.5x)
                                    </span>
                                @elseif($t->termination_type === 'sp3_violation')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-red-100 text-red-800">
                                        Pelanggaran SP3 (0.5x)
                                    </span>
                                @elseif($t->termination_type === 'pension')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-800">
                                        Pensiun Normal (1.75x)
                                    </span>
                                @elseif($t->termination_type === 'medical' || $t->termination_type === 'deceased')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 text-indigo-800">
                                        Sakit/Meninggal (2.0x)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">
                                        Kesepakatan (1.0x)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">Rp {{ number_format($t->severance_amount, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-400">Faktor: {{ $t->severance_multiplier }}x upah</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-[11px]">
                                <div>UPMK: <strong class="text-slate-800">Rp {{ number_format($t->upmk_amount, 0, ',', '.') }}</strong></div>
                                <div class="text-slate-500">UPH: Rp {{ number_format($t->uph_amount, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-black text-slate-900 text-sm">Rp {{ number_format($t->total_severance, 0, ',', '.') }}</div>
                                @if($t->separation_pay_amount > 0)
                                    <div class="text-[10px] text-slate-400">Termasuk Uang Pisah: Rp {{ number_format($t->separation_pay_amount, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($t->status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check-check" class="w-3 h-3"></i> Lunas (Paid)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Disetujui
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($t->status !== 'paid')
                                    <form action="{{ route('company.terminations.pay', $t->id) }}" method="POST" onsubmit="return confirm('Tandai pembayaran pesangon ini telah lunas diserahkan ke pekerja?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-bold transition text-xs border border-emerald-200">
                                            Tandai Lunas
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] text-slate-400 italic">Lunas pada {{ $t->paid_at?->format('d/m/Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <i data-lucide="file-check-2" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Tidak ada catatan kompensasi PHK atau pesangon.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($terminations->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $terminations->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Statutory Severance Calculator -->
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openModal = false" class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="calculator" class="w-5 h-5 text-red-600"></i>
                    Kalkulator Pesangon Resmi (PP 35/2021)
                </div>
                <button @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.terminations.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" x-model="selectedEmpId" @change="onEmployeeSelect" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan PHK / Resign --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_code }} - {{ $emp->full_name }} (Join: {{ $emp->join_date ? \Carbon\Carbon::parse($emp->join_date)->format('d/m/Y') : '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Terminasi / PHK <span class="text-red-500">*</span></label>
                        <select name="termination_type" x-model="terminationType" @change="runCalculation" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="resignation">Mengundurkan Diri (Resign Sukarela)</option>
                            <option value="efficiency">Efisiensi karena Kerugian (0.5x)</option>
                            <option value="closing">Perusahaan Tutup / Force Majeure (0.5x)</option>
                            <option value="sp3_violation">Pelanggaran SP3 Berturut-turut (0.5x)</option>
                            <option value="pension">Mencapai Usia Pensiun Normal (1.75x)</option>
                            <option value="medical">Sakit Berkepanjangan > 12 Bln (2.0x)</option>
                            <option value="deceased">Karyawan Meninggal Dunia (2.0x)</option>
                            <option value="other">Kesepakatan Bersama / Lainnya (1.0x)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Efektif PHK <span class="text-red-500">*</span></label>
                        <input type="date" name="termination_date" x-model="terminationDate" @change="runCalculation" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Upah Pokok Terakhir <span class="text-red-500">*</span></label>
                        <input type="number" step="1000" name="last_salary" x-model="lastSalary" @input="runCalculation" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sisa Hari Cuti</label>
                        <input type="number" min="0" name="remaining_leave_days" x-model="remainingLeaveDays" @input="runCalculation" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Uang Pisah Tambahan</label>
                        <input type="number" step="1000" name="separation_pay_amount" x-model="separationPay" @input="runCalculation" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <!-- LIVE STATUTORY PREVIEW CARD -->
                <template x-if="calcResult">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                            <span class="font-bold text-slate-700">Masa Kerja Dihitung:</span>
                            <span class="font-bold text-slate-900" x-text="calcResult.years_of_service + ' Tahun ' + calcResult.months_of_service + ' Bulan (' + calcResult.total_months + ' Bulan Total)'"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600">
                            <div>
                                <span>Uang Pesangon (UP):</span>
                                <strong class="block text-slate-900" x-text="'Rp ' + Number(calcResult.severance_amount).toLocaleString('id-ID') + ' (' + calcResult.base_severance_months + ' bln x ' + calcResult.severance_multiplier + 'x)'"></strong>
                            </div>
                            <div>
                                <span>Penghargaan Masa Kerja (UPMK):</span>
                                <strong class="block text-slate-900" x-text="'Rp ' + Number(calcResult.upmk_amount).toLocaleString('id-ID') + ' (' + calcResult.base_upmk_months + ' bln upah)'"></strong>
                            </div>
                            <div>
                                <span>Penggantian Hak / UPH (Cuti):</span>
                                <strong class="block text-slate-900" x-text="'Rp ' + Number(calcResult.uph_amount).toLocaleString('id-ID') + ' (' + calcResult.remaining_leave_days + ' hari cuti)'"></strong>
                            </div>
                            <div>
                                <span>Uang Pisah Kebijakan:</span>
                                <strong class="block text-slate-900" x-text="'Rp ' + Number(calcResult.separation_pay_amount).toLocaleString('id-ID')"></strong>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                            <span class="font-bold text-slate-900 text-xs">Total Hak Kompensasi PHK:</span>
                            <span class="font-black text-red-600 text-base" x-text="'Rp ' + Number(calcResult.total_severance).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Kesepakatan / Perjanjian Bersama</label>
                    <textarea name="notes" rows="2" placeholder="Tuliskan catatan Berita Acara Mediasi atau persetujuan kedua belah pihak..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#DC2626] hover:bg-[#991B1B] rounded-xl shadow-sm transition">
                        Simpan & Terbitkan SK PHK
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

