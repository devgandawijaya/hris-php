@extends('layouts.app')

@section('title', 'Employee Self-Service (ESS)')

@section('content')
<div class="space-y-6" x-data="{ activeEssTab: 'clock' }">

    <!-- ESS Header Banner -->
    <div class="bg-gradient-to-r from-red-600 via-red-700 to-red-800 rounded-2xl p-6 sm:p-8 text-white shadow-lg shadow-red-600/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold backdrop-blur-sm">
                <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                Portal Mandiri Karyawan
            </span>
            <h1 class="text-2xl sm:text-3xl font-black mt-2 tracking-tight">
                Halo, {{ $employee?->full_name ?? auth()->user()->name }}!
            </h1>
            <p class="text-xs sm:text-sm text-red-100 mt-1">
                NIK: <span class="font-mono font-bold">{{ $employee?->employee_number ?? '-' }}</span> • Jabatan: <strong>{{ $employee?->position?->title ?? 'Staff' }}</strong> ({{ $employee?->department?->name ?? 'Perusahaan' }})
            </p>
        </div>

        <div class="text-right sm:border-l sm:border-red-500/50 sm:pl-6">
            <div class="text-[10px] uppercase font-bold text-red-200">Saldo Cuti Tahunan</div>
            <div class="text-3xl font-black text-white font-mono mt-0.5">12 Hari</div>
            <div class="text-[11px] text-red-200 mt-0.5">Tahun Berjalan {{ date('Y') }}</div>
        </div>
    </div>

    <!-- Company Announcements Bulletin (Phase 6) -->
    @if(isset($announcements) && $announcements->isNotEmpty())
        <div class="space-y-3">
            @foreach($announcements as $ann)
                <div class="p-4 rounded-2xl border {{ $ann->is_pinned ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-400/20' : 'bg-white border-slate-200' }} shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $ann->category === 'urgent' ? 'bg-red-100 text-red-600' : ($ann->category === 'holiday' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600') }} flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="{{ $ann->category === 'urgent' ? 'alert-triangle' : ($ann->category === 'holiday' ? 'calendar-heart' : 'megaphone') }}" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                @if($ann->is_pinned)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black bg-amber-200 text-amber-900 uppercase">
                                        <i data-lucide="pin" class="w-2.5 h-2.5"></i> Penting
                                    </span>
                                @endif
                                <h3 class="text-sm font-bold text-slate-900">{{ $ann->title }}</h3>
                            </div>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $ann->content }}</p>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-400 shrink-0 sm:text-right">
                        <div>{{ $ann->published_at->format('d M Y') }}</div>
                        <div class="text-[10px] font-medium text-slate-500">{{ $ann->createdBy?->name ?? 'HR Management' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- ESS Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
        <button type="button" @click="activeEssTab = 'clock'"
                :class="activeEssTab === 'clock' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="clock" class="w-4 h-4"></i>
            Presensi Masuk / Pulang
        </button>

        <button type="button" @click="activeEssTab = 'payslips'"
                :class="activeEssTab === 'payslips' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="receipt" class="w-4 h-4"></i>
            Slip Gaji Saya
        </button>

        <button type="button" @click="activeEssTab = 'reimbursement'"
                :class="activeEssTab === 'reimbursement' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="wallet" class="w-4 h-4"></i>
            Klaim Reimbursement
        </button>

        <button type="button" @click="activeEssTab = 'leave'"
                :class="activeEssTab === 'leave' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="calendar-off" class="w-4 h-4"></i>
            Pengajuan Cuti
        </button>

        <button type="button" @click="activeEssTab = 'overtime'"
                :class="activeEssTab === 'overtime' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="timer" class="w-4 h-4"></i>
            Lembur (SPL)
        </button>

        <button type="button" @click="activeEssTab = 'correction'"
                :class="activeEssTab === 'correction' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="check-square" class="w-4 h-4"></i>
            Koreksi Presensi
        </button>

        <button type="button" @click="activeEssTab = 'contracts'"
                :class="activeEssTab === 'contracts' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="file-signature" class="w-4 h-4"></i>
            Kontrak Kerja
        </button>

        <button type="button" @click="activeEssTab = 'performance'"
                :class="activeEssTab === 'performance' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="target" class="w-4 h-4"></i>
            Kinerja & Evaluasi
        </button>

        <button type="button" @click="activeEssTab = 'assets-training'"
                :class="activeEssTab === 'assets-training' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="award" class="w-4 h-4"></i>
            Aset & Pelatihan
        </button>

        <button type="button" @click="activeEssTab = 'grievance-helpdesk'"
                :class="activeEssTab === 'grievance-helpdesk' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="life-buoy" class="w-4 h-4"></i>
            Layanan HR & Aspirasi
        </button>

        <button type="button" @click="activeEssTab = 'career'"
                :class="activeEssTab === 'career' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="trending-up" class="w-4 h-4"></i>
            Riwayat Karir
        </button>

        <button type="button" @click="activeEssTab = 'loans-wellbeing'"
                :class="activeEssTab === 'loans-wellbeing' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="wallet" class="w-4 h-4"></i>
            Kasbon & Apresiasi
        </button>

        <button type="button" @click="activeEssTab = 'skills-career'"
                :class="activeEssTab === 'skills-career' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="award" class="w-4 h-4"></i>
            Keahlian & Karir
        </button>

        <button type="button" @click="activeEssTab = 'profile'"
                :class="activeEssTab === 'profile' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4"></i>
            Data Mandiri
        </button>
    </div>

    <!-- TAB 1: CLOCK IN / OUT -->
    <div x-show="activeEssTab === 'clock'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Interactive Clock Card -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Hari Ini</span>
                <span class="text-xs font-bold text-red-600 font-mono">{{ now()->format('d M Y') }}</span>
            </div>

            <div class="text-center py-6 bg-slate-50 rounded-2xl border border-slate-200/60">
                <div class="text-xs text-slate-500 mb-1">Status Kehadiran</div>
                @if(!$todayRecord)
                    <div class="text-2xl font-black text-slate-800">Belum Clock In</div>
                    <div class="text-xs text-amber-600 mt-1 flex items-center justify-center gap-1 font-semibold">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Jam masuk reguler: 08:30 WIB
                    </div>
                @elseif($todayRecord && empty($todayRecord->clock_out))
                    <div class="text-2xl font-black text-emerald-600">Sudah Masuk ({{ $todayRecord->clock_in }})</div>
                    <div class="text-xs text-slate-500 mt-1 font-medium">Sedang Bekerja • Lakukan Clock Out saat jam pulang</div>
                @else
                    <div class="text-2xl font-black text-blue-600">Selesai Bekerja</div>
                    <div class="text-xs text-slate-500 mt-1">Masuk: {{ $todayRecord->clock_in }} • Pulang: {{ $todayRecord->clock_out }} ({{ $todayRecord->work_hours }} jam)</div>
                @endif
            </div>

            <div class="flex items-center gap-2.5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800">
                <i data-lucide="map-pin" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span>Radius Geofencing Kantor: Anda berada di area radius 100m kantor.</span>
            </div>

            <form action="{{ route('ess.clock') }}" method="POST">
                @csrf
                <input type="hidden" name="latitude" value="-6.225588">
                <input type="hidden" name="longitude" value="106.811802">

                @if(!$todayRecord || empty($todayRecord->clock_in))
                    <button type="submit" class="w-full py-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm tracking-wide transition shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        Catat Masuk (Clock In)
                    </button>
                @elseif(empty($todayRecord->clock_out))
                    <button type="submit" class="w-full py-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-sm tracking-wide transition shadow-lg shadow-rose-600/20 flex items-center justify-center gap-2">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        Catat Pulang (Clock Out)
                    </button>
                @else
                    <div class="p-3 text-center text-xs font-semibold text-slate-500 bg-slate-100 rounded-xl">
                        Presensi hari ini telah lengkap.
                    </div>
                @endif
            </form>
        </div>

        <!-- Attendance History -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center justify-between">
                <span>Riwayat Presensi Terakhir</span>
                <span class="text-slate-400 font-normal">7 Hari Terakhir</span>
            </h3>

            <div class="divide-y divide-slate-100">
                @forelse($attendanceHistory as $item)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-900">{{ $item->date->format('d M Y') }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                Masuk: {{ $item->clock_in ?? '-' }} • Pulang: {{ $item->clock_out ?? '-' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $item->status === 'present' ? 'bg-emerald-50 text-emerald-700' : ($item->status === 'late' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($item->status) }}
                            </span>
                            @if($item->work_hours > 0)
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $item->work_hours }} Jam Kerja</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada riwayat presensi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 2: SLIP GAJI SAYA (PHASE 3) -->
    <div x-show="activeEssTab === 'payslips'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-sm text-slate-900">Arsip Slip Gaji Saya</h3>
                <p class="text-xs text-slate-500 mt-0.5">Lihat rincian penerimaan gaji, potongan BPJS, dan PPh 21 TER setiap bulan.</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($recentPayrolls as $pay)
                <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="font-bold text-slate-900 text-sm">{{ $pay->payrollPeriod->name }}</div>
                        <div class="text-slate-500 mt-0.5">
                            Tanggal Pembayaran: {{ $pay->payrollPeriod->payment_date->format('d M Y') }} •
                            Gaji Pokok: Rp {{ number_format($pay->basic_salary, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <div class="text-[10px] uppercase font-bold text-slate-400">Take Home Pay</div>
                            <div class="text-base font-black text-[#DC2626]">Rp {{ number_format($pay->take_home_pay, 0, ',', '.') }}</div>
                        </div>

                        <a href="{{ route('ess.payslip.show', $pay->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition shadow-sm">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                            <span>Buka Slip Gaji</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-xs text-slate-400">
                    <i data-lucide="receipt" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                    Belum ada slip gaji yang diterbitkan untuk akun Anda.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 3: REIMBURSEMENT (PHASE 3) -->
    <div x-show="activeEssTab === 'reimbursement'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Submit Reimbursement Form -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-red-600"></i>
                Ajukan Klaim Reimbursement Baru
            </h3>

            <form action="{{ route('ess.reimbursement.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Biaya *</label>
                    <select name="category" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                        <option value="medical">Biaya Medis & Obat</option>
                        <option value="transport">Transportasi & Tol</option>
                        <option value="meal">Uang Makan Lembur</option>
                        <option value="business_trip">Perjalanan Dinas</option>
                        <option value="operational">Operasional Kantor</option>
                        <option value="other">Lain-lain</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pengeluaran *</label>
                    <input type="date" name="expense_date" required value="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keperluan / Judul Pengeluaran *</label>
                    <input type="text" name="title" required placeholder="Contoh: Tiket Kereta Dinas Bandung" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Total Nominal Biaya (Rp) *</label>
                    <input type="number" name="amount" min="1000" step="500" required placeholder="Contoh: 350000" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Bukti Nota / Kuitansi</label>
                    <input type="file" name="receipt" accept="image/*,application/pdf" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Tambahan</label>
                    <textarea name="description" rows="2" placeholder="Catatan opsional..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-semibold transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Pengajuan Reimbursement
                </button>
            </form>
        </div>

        <!-- History of Reimbursements -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center justify-between">
                <span>Riwayat Klaim Reimbursement</span>
                <span class="text-slate-400 font-normal">Daftar Pengajuan</span>
            </h3>

            <div class="divide-y divide-slate-100">
                @forelse($reimbursements as $rb)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-900">{{ $rb->title }}</div>
                            <div class="text-[11px] text-slate-500">
                                {{ $rb->expense_date->format('d M Y') }} • <span class="uppercase font-semibold text-slate-700">{{ $rb->category }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-slate-900">Rp {{ number_format($rb->amount, 0, ',', '.') }}</div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold mt-1
                                @if($rb->status === 'paid') bg-emerald-50 text-emerald-700
                                @elseif($rb->status === 'approved_finance') bg-blue-50 text-blue-700
                                @elseif($rb->status === 'approved_manager') bg-amber-50 text-amber-700
                                @elseif($rb->status === 'rejected') bg-rose-50 text-rose-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $rb->status)) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada pengajuan reimbursement.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 4: LEAVE -->
    <div x-show="activeEssTab === 'leave'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-red-600"></i>
                Formulir Pengajuan Cuti Mandiri
            </h3>

            <form action="{{ route('ess.leave.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Cuti *</label>
                    <select name="leave_type_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }} (Maks. {{ $type->days_allowed }} hari)</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mulai Tanggal *</label>
                        <input type="date" name="start_date" required min="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sampai Tanggal *</label>
                        <input type="date" name="end_date" required min="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pengajuan Cuti *</label>
                    <textarea name="reason" rows="3" required placeholder="Berikan keterangan keperluan cuti..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-semibold transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Pengajuan Cuti
                </button>
            </form>
        </div>

        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100">Riwayat Pengajuan Cuti</h3>
            <div class="space-y-3">
                @forelse($leaveRequests as $req)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>{{ $req->leaveType->name }} ({{ $req->total_days }} Hari)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] {{ $req->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($req->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1">
                            {{ $req->start_date->format('d M Y') }} s.d. {{ $req->end_date->format('d M Y') }}
                        </div>
                        <div class="text-slate-600 mt-2">{{ $req->reason }}</div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada pengajuan cuti.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 5: OVERTIME -->
    <div x-show="activeEssTab === 'overtime'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-red-600"></i>
                Formulir Surat Perintah Lembur (SPL)
            </h3>

            <form action="{{ route('ess.overtime.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lembur *</label>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi Lembur (Jam) *</label>
                        <input type="number" step="0.5" name="hours" value="2.0" min="0.5" max="12" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_holiday" value="1" class="w-4 h-4 rounded border-slate-300 text-[#DC2626]">
                        <span class="text-xs text-slate-700">Lembur pada Hari Libur Resmi / Istirahat Mingguan</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tujuan & Uraian Pekerjaan Lembur *</label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Deployment sistem pembaruan versi dan migrasi database..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-semibold transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Pengajuan SPL
                </button>
            </form>
        </div>

        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100">Riwayat Pengajuan Lembur</h3>
            <div class="space-y-3">
                @forelse($overtimeRequests as $ot)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>Tanggal: {{ $ot->date->format('d M Y') }} ({{ $ot->hours }} Jam)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] {{ $ot->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($ot->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ ucfirst($ot->status) }}
                            </span>
                        </div>
                        @if($ot->status === 'approved')
                            <div class="text-[11px] text-emerald-600 font-bold mt-1 font-mono">
                                Upah Lembur PP 35: Rp {{ number_format($ot->calculated_pay, 0, ',', '.') }}
                            </div>
                        @endif
                        <div class="text-slate-600 mt-2">{{ $ot->reason }}</div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada pengajuan lembur.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 6: CORRECTION -->
    <div x-show="activeEssTab === 'correction'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-red-600"></i>
                Formulir Koreksi Presensi (Lupa Absen)
            </h3>

            <form action="{{ route('ess.correction.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Presensi *</label>
                    <input type="date" name="date" required max="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Koreksi Jam Masuk</label>
                        <input type="time" name="corrected_clock_in" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Koreksi Jam Pulang</label>
                        <input type="time" name="corrected_clock_out" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Koreksi *</label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Kendala jaringan pada ponsel saat berada di lokasi kantor..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-semibold transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Permohonan Koreksi
                </button>
            </form>
        </div>

        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100">Riwayat Permohonan Koreksi</h3>
            <div class="space-y-3">
                @forelse($corrections as $cor)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>Tanggal: {{ $cor->date->format('d M Y') }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] {{ $cor->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($cor->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ ucfirst($cor->status) }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1 font-mono">
                            Jam Masuk: {{ $cor->corrected_clock_in ?? '-' }} • Jam Pulang: {{ $cor->corrected_clock_out ?? '-' }}
                        </div>
                        <div class="text-slate-600 mt-2">{{ $cor->reason }}</div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada riwayat permohonan koreksi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 7: CONTRACTS (PHASE 3) -->
    <div x-show="activeEssTab === 'contracts'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-sm text-slate-900">Status Perjanjian Kerja & Hak Kompensasi PKWT</h3>
                <p class="text-xs text-slate-500 mt-0.5">Informasi masa berlaku hubungan kerja dan ketentuan PP No. 35/2021 Pasal 61A.</p>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($contracts as $ctr)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 text-xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="font-bold text-slate-900 text-sm">No. Kontrak: {{ $ctr->contract_number }}</span>
                            <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($ctr->type === 'pkwt') bg-amber-100 text-amber-800
                                @elseif($ctr->type === 'pkwtt') bg-emerald-100 text-emerald-800
                                @else bg-slate-200 text-slate-800 @endif">
                                {{ $ctr->type }}
                            </span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $ctr->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ ucfirst(str_replace('_', ' ', $ctr->status)) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[11px]">
                        <div>
                            <span class="text-slate-400 block">Tanggal Mulai:</span>
                            <strong class="text-slate-800">{{ $ctr->start_date->format('d M Y') }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Tanggal Berakhir:</span>
                            <strong class="text-slate-800">{{ $ctr->end_date ? $ctr->end_date->format('d M Y') : 'Permanen' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Upah Pokok Perjanjian:</span>
                            <strong class="text-slate-800">Rp {{ number_format($ctr->monthly_wage, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Hak Kompensasi PP 35:</span>
                            <strong class="text-purple-700">Rp {{ number_format($ctr->compensation_calculated, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-slate-400">Belum ada data kontrak kerja terdaftar.</div>
            @endforelse
        </div>
    </div>

    <!-- TAB 8: PROFILE UPDATE -->
    <div x-show="activeEssTab === 'profile'" x-cloak class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
            Pengkinian Nomor Kontak & Rekening Bank Mandiri
        </h3>

        <form action="{{ route('ess.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $employee?->phone ?? auth()->user()->phone) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $employee?->birth_place) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $employee?->birth_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee?->bank_name) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $employee?->bank_account_number) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemilik Rekening</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $employee?->bank_account_holder) }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                </div>
            </div>

            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-semibold transition shadow-sm">
                Simpan Perubahan
            </button>
        </form>
    </div>

    <!-- TAB 9: KINERJA & EVALUASI SAYA -->
    <div x-show="activeEssTab === 'performance'" x-cloak class="space-y-6">
        <!-- Section A: Target Sasaran Kerja (KPI / OKR) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                        <i data-lucide="target" class="w-4 h-4 text-red-600"></i>
                        Sasaran Kinerja Individu (KPI / OKR)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui realisasi pencapaian indikator kinerja utama Anda secara berkala.</p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($kpiGoals as $goal)
                    @php
                        $target = (float) $goal->target_value;
                        $current = (float) $goal->current_value;
                        $pct = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
                    @endphp
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="space-y-0.5">
                                <span class="font-black text-slate-900 text-sm">{{ $goal->title }}</span>
                                <div class="text-xs text-slate-500">{{ $goal->description ?? 'Tidak ada catatan sasaran.' }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-200 text-slate-700">
                                    Bobot: {{ $goal->weight }}%
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    @if($goal->status === 'achieved') bg-emerald-100 text-emerald-800
                                    @elseif($goal->status === 'in_progress') bg-blue-100 text-blue-800
                                    @else bg-slate-200 text-slate-700 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $goal->status)) }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-slate-500">Pencapaian: {{ number_format($current) }} / {{ number_format($target) }} {{ $goal->unit }}</span>
                                <span class="font-bold {{ $pct >= 100 ? 'text-emerald-600' : 'text-slate-700' }}">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full {{ $pct >= 100 ? 'bg-emerald-600' : ($pct >= 50 ? 'bg-blue-600' : 'bg-amber-500') }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        <!-- Update Progress Form -->
                        <form action="{{ route('ess.kpi-progress.update', $goal->id) }}" method="POST" class="pt-2 border-t border-slate-200/80 flex items-center gap-2 justify-end">
                            @csrf
                            <label class="text-[11px] font-bold text-slate-500">Update Realisasi:</label>
                            <input type="number" step="any" name="current_value" value="{{ $current }}" required class="w-24 px-2 py-1 text-xs rounded-lg border border-slate-300 font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-red-500">
                            <span class="text-[11px] text-slate-400 font-medium">{{ $goal->unit }}</span>
                            <button type="submit" class="px-3 py-1 rounded-lg bg-slate-900 text-white text-[11px] font-bold hover:bg-slate-800 transition">
                                Simpan
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada sasaran KPI/OKR yang ditetapkan untuk periode ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Section B: Evaluasi Mandiri (Self-Appraisal) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-purple-600"></i>
                    Formulir Evaluasi Mandiri (Self-Appraisal)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Sampaikan penilaian refleksi diri Anda (skala 0 - 100) dan catatan pencapaian selama siklus evaluasi.</p>
            </div>

            @if($activeCycles->isNotEmpty())
                <form action="{{ route('ess.self-appraisal.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Siklus Evaluasi <span class="text-red-500">*</span></label>
                            <select name="performance_cycle_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                @foreach($activeCycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Skor Penilaian Diri (0 - 100) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" min="0" max="100" name="self_rating" required placeholder="Contoh: 85.0" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Uraian Refleksi & Pencapaian Utama <span class="text-red-500">*</span></label>
                        <textarea name="self_feedback" rows="4" required placeholder="Jelaskan kontribusi, keberhasilan proyek, kendala yang dihadapi, serta rencana pengembangan diri Anda..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-bold transition shadow-sm shadow-red-500/20">
                        Kirimkan Evaluasi Mandiri
                    </button>
                </form>
            @else
                <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                    Saat ini tidak ada siklus evaluasi kinerja yang berstatus aktif.
                </div>
            @endif
        </div>

        <!-- Section C: Riwayat Hasil Evaluasi (Performance Reviews) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-sm text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i>
                Riwayat Hasil Penilaian Kinerja
            </h3>

            <div class="space-y-3">
                @forelse($performanceReviews as $rev)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3 text-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <span class="font-bold text-slate-900 text-sm">Siklus: {{ $rev->performanceCycle?->name ?? 'Periode Evaluasi' }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                @if($rev->status === 'completed') bg-emerald-100 text-emerald-800
                                @elseif($rev->status === 'manager_reviewed') bg-blue-100 text-blue-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $rev->status)) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white p-3 rounded-lg border border-slate-100 text-center">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Skor Mandiri</span>
                                <span class="font-black text-slate-900 text-base">{{ $rev->self_rating !== null ? number_format($rev->self_rating, 1) : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Skor Atasan</span>
                                <span class="font-black text-blue-600 text-base">{{ $rev->manager_rating !== null ? number_format($rev->manager_rating, 1) : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Nilai Akhir (Weighted)</span>
                                <span class="font-black text-emerald-600 text-base">{{ $rev->final_score !== null ? number_format($rev->final_score, 1) : '-' }}</span>
                            </div>
                        </div>

                        @if($rev->manager_feedback)
                            <div class="p-3 rounded-lg bg-emerald-50/60 border border-emerald-100 text-slate-700">
                                <span class="font-bold text-emerald-900 block text-[11px] mb-0.5">Ulasan & Rekomendasi Atasan:</span>
                                {{ $rev->manager_feedback }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">Belum ada hasil penilaian kinerja yang diterbitkan.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 10: ASET & PELATIHAN SAYA -->
    <div x-show="activeEssTab === 'assets-training'" x-cloak class="space-y-6">
        <!-- Section A: Fasilitas & Aset Perusahaan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i data-lucide="laptop" class="w-4 h-4 text-blue-600"></i>
                    Aset & Fasilitas Kerja yang Dipinjamkan
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar inventaris dan perangkat operasional yang menjadi tanggung jawab Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($assignedAssets as $asset)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">{{ $asset->name }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800">
                                {{ $asset->category }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600">
                            <div>
                                <span class="text-slate-400 block">Kode Aset:</span>
                                <strong class="font-mono text-slate-800">{{ $asset->asset_code }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Nomor Seri:</span>
                                <strong class="font-mono text-slate-800">{{ $asset->serial_number ?? '-' }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Tanggal Serah Terima:</span>
                                <strong class="text-slate-800">{{ $asset->assigned_date ? $asset->assigned_date->format('d M Y') : '-' }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Status:</span>
                                <strong class="capitalize text-slate-800">{{ $asset->status }}</strong>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 py-6 text-center text-xs text-slate-400">Tidak ada aset atau inventaris yang terdaftar atas nama Anda saat ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Section B: Onboarding / Offboarding Checklist Saya -->
        @if($myChecklists->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                        <i data-lucide="check-square" class="w-4 h-4 text-emerald-600"></i>
                        Checklist Orientasi & Serah Terima
                    </h3>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($myChecklists as $chk)
                        <div class="py-3 flex items-center justify-between gap-4 text-xs">
                            <div class="space-y-0.5">
                                <span class="font-bold text-slate-900">{{ $chk->title }}</span>
                                <div class="text-[11px] text-slate-500 capitalize">Tipe: {{ $chk->type }} • Status: {{ $chk->is_completed ? 'Selesai' : 'Pending' }}</div>
                            </div>
                            <div>
                                @if($chk->is_completed)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        Menunggu
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section C: Riwayat Pelatihan & Sertifikat Digital -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i data-lucide="graduation-cap" class="w-4 h-4 text-purple-600"></i>
                    Program Pelatihan & Sertifikat Digital
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Riwayat partisipasi pengembangan kompetensi dan nomor sertifikat yang telah diterbitkan.</p>
            </div>

            <div class="space-y-3">
                @forelse($myTrainings as $trn)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3 text-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="font-bold text-slate-900 text-sm">{{ $trn->trainingCourse?->title }}</span>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Trainer: {{ $trn->trainingCourse?->trainer_name ?? 'Internal HR' }} • Lokasi: {{ $trn->trainingCourse?->location }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-100 text-purple-800">
                                    {{ $trn->trainingCourse?->category }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $trn->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $trn->status === 'completed' ? 'Lulus' : 'Terdaftar' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-white p-3 rounded-lg border border-slate-100 text-[11px]">
                            <div>
                                <span class="text-slate-400 block">Jadwal:</span>
                                <strong class="text-slate-800">{{ $trn->trainingCourse?->start_date?->format('d M Y') }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Nilai Kelulusan:</span>
                                <strong class="text-slate-800">{{ $trn->score !== null ? number_format($trn->score, 1) : '-' }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Nomor Sertifikat:</span>
                                <strong class="font-mono text-purple-700 font-bold">{{ $trn->certificate_number ?? '-' }}</strong>
                            </div>
                        </div>

                        @if($trn->feedback)
                            <div class="text-[11px] text-slate-600 bg-slate-100/60 p-2.5 rounded-lg">
                                <span class="font-bold text-slate-700">Catatan/Feedback:</span> {{ $trn->feedback }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada riwayat pelatihan atau sertifikat terdaftar.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 11: LAYANAN HR & PENGADUAN (PHASE 6) -->
    <div x-show="activeEssTab === 'grievance-helpdesk'" x-cloak class="space-y-6">

        <!-- Disciplinary Standing Card -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 font-bold text-sm text-slate-900">
                    <i data-lucide="shield" class="w-4 h-4 text-slate-600"></i>
                    Status Kepatuhan Disiplin Karyawan
                </div>
                <span class="text-[11px] text-slate-400">Pasal 161 UU Ketenagakerjaan</span>
            </div>

            <div class="mt-4">
                @if($myWarnings->isEmpty())
                    <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs">
                        <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-600 shrink-0"></i>
                        <div>
                            <div class="font-bold text-sm">Catatan Disiplin Baik (Good Standing)</div>
                            <div class="text-[11px] text-emerald-700 mt-0.5">Tidak ada Surat Peringatan (SP) yang aktif atas nama Anda. Pertahankan dedikasi dan profesionalisme kerja Anda!</div>
                        </div>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($myWarnings as $warn)
                            <div class="p-4 rounded-xl border {{ $warn->status === 'active' ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200 bg-slate-50' }} text-xs space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 font-mono">{{ $warn->letter_number }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $warn->warning_level === 'sp3' ? 'bg-red-100 text-red-800' : ($warn->warning_level === 'sp2' ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ strtoupper($warn->warning_level) }}
                                        </span>
                                    </div>
                                    <div>
                                        @if($warn->status === 'active')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Aktif hingga {{ $warn->valid_until->format('d/m/Y') }} (Masa berlaku 6 bln)
                                            </span>
                                        @elseif($warn->status === 'revoked')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                                Dicabut HR
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-500">
                                                Kedaluwarsa
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-slate-700"><strong class="text-slate-900">Alasan:</strong> {{ $warn->reason }}</div>
                                @if($warn->action_plan)
                                    <div class="text-slate-600 bg-white/80 p-2 rounded border border-slate-200/60 text-[11px]">
                                        <strong class="text-slate-800">Rencana Perbaikan:</strong> {{ $warn->action_plan }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 2-Column Grid: HR Helpdesk & Grievance Whistleblowing -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- COLUMN 1: HR HELPDESK & PERMOHONAN LAYANAN -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                        <i data-lucide="life-buoy" class="w-4 h-4 text-blue-600"></i>
                        Permohonan Layanan HR (Helpdesk)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ajukan permohonan surat keterangan kerja (paklaring), referensi bank/visa, atau konsultasi BPJS.</p>
                </div>

                <!-- Form Buat Tiket -->
                <form action="{{ route('ess.helpdesk.submit') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori Layanan <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="work_certificate">Surat Keterangan Kerja (Paklaring)</option>
                            <option value="bank_reference">Surat Referensi Pengajuan Bank / Visa / KPR</option>
                            <option value="data_correction">Permohonan Koreksi Data Karyawan</option>
                            <option value="bpjs_inquiry">Pertanyaan & Klaim BPJS TK / Kesehatan</option>
                            <option value="general">Layanan & Pertanyaan Umum Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Subjek Permohonan <span class="text-red-500">*</span></label>
                        <input type="text" name="subject" required placeholder="Contoh: Mohon Surat Keterangan Kerja untuk Pengajuan KPR BTN" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rincian & Keterangan Tambahan <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="3" required placeholder="Jelaskan keperluan, instansi tujuan, atau data yang ingin diperbaiki..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-sm">
                            Kirim Tiket Layanan HR
                        </button>
                    </div>
                </form>

                <!-- Riwayat Tiket Saya -->
                <div class="pt-4 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-800 mb-3">Riwayat Tiket Layanan Saya</h4>
                    <div class="space-y-2.5">
                        @forelse($myHelpdeskTickets as $t)
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/60 text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 font-mono">{{ $t->ticket_number }}</span>
                                    @if($t->status === 'open')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                                    @elseif($t->status === 'in_progress')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Diproses</span>
                                    @elseif($t->status === 'resolved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Selesai</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Ditutup</span>
                                    @endif
                                </div>
                                <div class="font-medium text-slate-800">{{ $t->subject }}</div>
                                <div class="text-[11px] text-slate-500">{{ $t->description }}</div>
                                @if($t->resolution_notes)
                                    <div class="p-2 mt-1 rounded bg-emerald-50 text-emerald-900 border border-emerald-200 text-[11px]">
                                        <strong class="text-emerald-950">Respon HR:</strong> {{ $t->resolution_notes }}
                                    </div>
                                @endif
                                <div class="text-[10px] text-slate-400 pt-0.5">{{ $t->created_at->format('d M Y H:i') }}</div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-xs text-slate-400">Belum ada riwayat tiket layanan diajukan.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- COLUMN 2: KANAL PENGADUAN & WHISTLEBLOWING -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600"></i>
                        Kanal Pengaduan & Aspirasi Karyawan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Sampaikan keluhan kerja, perlakuan tidak adil, atau pelanggaran etika dengan jaminan kerahasiaan penuh.</p>
                </div>

                <!-- Form Pengaduan -->
                <form action="{{ route('ess.grievance.submit') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori Masalah <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="workplace_conflict">Perselisihan / Konflik Rekan Kerja</option>
                            <option value="harassment">Pelecehan Seksual / Bullying / Intimidasi</option>
                            <option value="unfair_treatment">Perlakuan Tidak Adil / Diskriminasi Atasan</option>
                            <option value="compensation">Perselisihan Kompensasi / Jam Kerja / Lembur</option>
                            <option value="facility">Fasilitas / Kesehatan & Keselamatan Kerja (K3)</option>
                            <option value="other">Keluhan Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Subjek Pokok Masalah <span class="text-red-500">*</span></label>
                        <input type="text" name="subject" required placeholder="Contoh: Ketidaksesuaian Pembagian Beban Kerja & Sikap Diskriminatif" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kronologi & Rincian Kejadian <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="3" required placeholder="Ceritakan kronologi, waktu kejadian, pihak yang terlibat, dan bukti terkait jika ada..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    <div class="p-3 bg-indigo-50/70 rounded-xl border border-indigo-200/80 flex items-start gap-2.5">
                        <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1" class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                        <label for="is_anonymous" class="text-xs text-indigo-950 font-medium cursor-pointer leading-tight">
                            <strong class="font-bold">Kirim Secara Anonim (Whistleblowing)</strong><br>
                            Identitas dan nama Anda akan disamarkan dari pihak terlapor dan atasan demi keamanan Anda.
                        </label>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition shadow-sm">
                            Kirim Aduan Secara Aman
                        </button>
                    </div>
                </form>

                <!-- Riwayat Aduan Saya -->
                <div class="pt-4 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-800 mb-3">Riwayat Status Aduan Saya</h4>
                    <div class="space-y-2.5">
                        @forelse($myGrievances as $gr)
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/60 text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $gr->category) }}</span>
                                    @if($gr->status === 'submitted')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Terkirim</span>
                                    @elseif($gr->status === 'in_review')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800">Review HR</span>
                                    @elseif($gr->status === 'investigating')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">Investigasi</span>
                                    @elseif($gr->status === 'resolved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Selesai</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Ditutup</span>
                                    @endif
                                </div>
                                <div class="font-medium text-slate-800">{{ $gr->subject }}</div>
                                <div class="text-[11px] text-slate-500">{{ $gr->description }}</div>
                                @if($gr->resolution_notes)
                                    <div class="p-2 mt-1 rounded bg-emerald-50 text-emerald-900 border border-emerald-200 text-[11px]">
                                        <strong class="text-emerald-950">Hasil Penyelesaian / Mediasi HR:</strong> {{ $gr->resolution_notes }}
                                    </div>
                                @endif
                                <div class="text-[10px] text-slate-400 pt-0.5">{{ $gr->created_at->format('d M Y H:i') }}</div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-xs text-slate-400">Belum ada aduan atau aspirasi yang diajukan.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- TAB 12: RIWAYAT KARIR & MUTASI (PHASE 7) -->
    <div x-show="activeEssTab === 'career'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i>
                        Jejak Karir & Promosi Anda
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan resmi kenaikan jenjang, rotasi divisi, dan pencapaian profesional Anda di perusahaan.</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($myMutations as $mut)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2 text-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if($mut->type === 'promotion')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">
                                        Promosi Jabatan
                                    </span>
                                @elseif($mut->type === 'transfer')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-800">
                                        Mutasi / Rotasi
                                    </span>
                                @elseif($mut->type === 'demotion')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800">
                                        Demosi
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-100 text-purple-800">
                                        Penyesuaian Gaji
                                    </span>
                                @endif
                                <span class="text-slate-400">&bull;</span>
                                <span class="font-bold text-slate-800">Berlaku Efektif: {{ $mut->effective_date->format('d M Y') }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-white p-3 rounded-lg border border-slate-100 text-[11px] mt-2">
                            <div>
                                <span class="text-slate-400 block">Departemen:</span>
                                <div class="text-slate-700 font-medium">
                                    {{ $mut->fromDepartment?->name ?? '-' }} &rarr; <strong class="text-slate-900">{{ $mut->toDepartment?->name ?? '-' }}</strong>
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Posisi / Jabatan:</span>
                                <div class="text-slate-700 font-medium">
                                    {{ $mut->fromPosition?->title ?? '-' }} &rarr; <strong class="text-emerald-700">{{ $mut->toPosition?->title ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        @if($mut->notes)
                            <div class="text-[11px] text-slate-600 bg-slate-100/60 p-2.5 rounded-lg mt-2">
                                <strong class="text-slate-700">Catatan SK:</strong> {{ $mut->notes }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        <i data-lucide="milestone" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        Belum ada catatan mutasi atau promosi karir.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 13: KASBON, SURVEI & APRESIASI (PHASE 8) -->
    <div x-show="activeEssTab === 'loans-wellbeing'" x-cloak class="space-y-6">

        <!-- Grid: Kasbon & Pinjaman Karyawan -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Form Pengajuan Kasbon -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                        <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
                        Pengajuan Kasbon & Pinjaman Darurat
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Fasilitas bantuan keuangan darurat dengan pemotongan angsuran otomatis melalui payroll.</p>
                </div>

                <form action="{{ route('ess.loan.apply') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipe Pinjaman <span class="text-red-500">*</span></label>
                        <select name="loan_type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="salary_advance">Kasbon Gaji Darurat (Bunga 0%)</option>
                            <option value="emergency">Bantuan Medis / Kemalangan</option>
                            <option value="education">Biaya Pendidikan Anak</option>
                            <option value="housing">Perbaikan Rumah / Tempat Tinggal</option>
                            <option value="other">Kebutuhan Penting Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Diajukan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" step="50000" min="100000" name="amount" required placeholder="Contoh: 2000000" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <span class="text-[10px] text-slate-400">Maksimal 50% dari total gaji pokok bulanan</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jangka Waktu Angsuran (Bulan) <span class="text-red-500">*</span></label>
                        <select name="term_months" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="1">1 Bulan (Potong gaji bulan depan)</option>
                            <option value="2">2 Bulan</option>
                            <option value="3" selected>3 Bulan</option>
                            <option value="6">6 Bulan</option>
                            <option value="12">12 Bulan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keperluan Penggunaan Dana <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="2" required placeholder="Jelaskan kebutuhan dana mendesak Anda..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                            Kirim Pengajuan Kasbon
                        </button>
                    </div>
                </form>
            </div>

            <!-- Riwayat Pinjaman & Jadwal Cicilan -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Riwayat Fasilitas Pinjaman Saya</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pantau status persetujuan dan rincian angsuran pemotongan gaji.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($myLoans as $ln)
                        <div class="p-4 rounded-xl border {{ $ln->status === 'active' ? 'border-blue-200 bg-blue-50/30' : 'border-slate-200 bg-slate-50/50' }} text-xs space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 font-mono">{{ $ln->loan_number }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ str_replace('_', ' ', $ln->loan_type) }}
                                    </span>
                                </div>
                                <div>
                                    @if($ln->status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu Review</span>
                                    @elseif($ln->status === 'active')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Aktif Berjalan</span>
                                    @elseif($ln->status === 'completed')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Ditolak</span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 bg-white p-3 rounded-lg border border-slate-100 text-[11px]">
                                <div>
                                    <span class="text-slate-400 block">Pokok Pinjaman:</span>
                                    <strong class="text-slate-800">Rp {{ number_format($ln->amount, 0, ',', '.') }}</strong>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Cicilan per Bulan:</span>
                                    <strong class="text-slate-800">Rp {{ number_format($ln->monthly_installment, 0, ',', '.') }} ({{ $ln->term_months }}x)</strong>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Sisa Kewajiban:</span>
                                    <strong class="text-blue-700 font-bold">Rp {{ number_format($ln->remaining_amount, 0, ',', '.') }}</strong>
                                </div>
                            </div>

                            <!-- Installment pills -->
                            @if($ln->installments->isNotEmpty())
                                <div class="pt-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Status Cicilan:</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($ln->installments as $ins)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold
                                                {{ $ins->status === 'paid' || $ins->status === 'deducted_from_payroll' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                                #{{ $ins->installment_number }}: {{ $ins->status === 'paid' || $ins->status === 'deducted_from_payroll' ? 'Lunas' : 'Rp ' . number_format($ins->amount, 0, ',', '.') }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            <i data-lucide="shield-check" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            Anda tidak memiliki riwayat pinjaman aktif.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Section: Survei Kepuasan Kerja & eNPS Aktif -->
        @if(isset($activeSurveys) && $activeSurveys->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                            <i data-lucide="smile" class="w-4 h-4 text-amber-500"></i>
                            Survei Suara Karyawan & eNPS (Sedang Berlangsung)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Suara dan aspirasi Anda sangat berharga untuk membangun lingkungan kerja yang lebih baik.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        {{ $activeSurveys->count() }} Survei Tersedia
                    </span>
                </div>

                <div class="space-y-4">
                    @foreach($activeSurveys as $survey)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-sm text-slate-900">{{ $survey->title }}</h4>
                                <span class="text-[10px] text-slate-400">Berakhir: {{ $survey->end_date->format('d M Y') }}</span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $survey->description }}</p>

                            <!-- Form Response -->
                            <form action="{{ route('ess.surveys.submit', $survey->id) }}" method="POST" class="pt-2 border-t border-slate-200 space-y-3 text-xs">
                                @csrf
                                <div>
                                    <label class="block font-bold text-slate-800 mb-1.5">
                                        Seberapa besar kemungkinan Anda merekomendasikan perusahaan ini sebagai tempat bekerja yang baik? (Skor 0 - 10)
                                    </label>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @for($i = 0; $i <= 10; $i++)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="enps_score" value="{{ $i }}" class="peer sr-only" required>
                                                <div class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center font-bold text-xs peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600 hover:bg-slate-100 transition">
                                                    {{ $i }}
                                                </div>
                                            </label>
                                        @endfor
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400 mt-1 max-w-sm">
                                        <span>0: Sangat Tidak Mungkin</span>
                                        <span>10: Sangat Mungkin (Promoter)</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-800 mb-1">
                                        Kritik, Masukan & Saran Perbaikan Anda (Opsional, Terjamin Anonim):
                                    </label>
                                    <textarea name="feedback" rows="2" placeholder="Bagikan pendapat jujur Anda demi kemajuan bersama..." class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition shadow-sm">
                                        Kirim Tanggapan Survei
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 2-Column: Wall of Recognition & Agenda Acara -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Wall of Recognition (Kudos Saya) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 text-amber-500"></i>
                            Apresiasi & Penghargaan Saya (Kudos)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pujian dan ucapan terima kasih atas kerja keras Anda.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($myRecognitions as $rec)
                        <div class="p-3.5 rounded-xl border border-amber-200/60 bg-amber-50/40 text-xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800">
                                    {{ str_replace('_', ' ', $rec->badge_type) }}
                                </span>
                                <span class="text-[10px] text-slate-400">{{ $rec->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="font-bold text-slate-900">{{ $rec->title }}</div>
                            <p class="text-[11px] text-slate-600 italic">"{{ $rec->message }}"</p>
                            <div class="text-[10px] text-slate-500 pt-1">
                                Pemberi: <strong class="text-slate-800">{{ $rec->sender?->name ?? 'HR Team' }}</strong>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            <i data-lucide="sparkles" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            Belum ada lencana apresiasi yang diterima. Terus berikan karya terbaik Anda!
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Kalender Agenda Perusahaan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-blue-600"></i>
                            Agenda & Kegiatan Perusahaan Mendatang
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Jadwal Town Hall, gathering, dan kegiatan resmi internal.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($upcomingEvents as $evt)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900 text-sm">{{ $evt->title }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-800">
                                    {{ $evt->event_type }}
                                </span>
                            </div>
                            <div class="text-slate-600 text-[11px]">{{ $evt->description }}</div>
                            <div class="flex items-center gap-3 pt-1 text-[10px] text-slate-500 font-medium">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                                    {{ $evt->start_time->format('d M Y, H:i') }} WIB
                                </span>
                                @if($evt->location)
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                        {{ $evt->location }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            <i data-lucide="calendar-check" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            Tidak ada agenda kegiatan terdekat yang dijadwalkan.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- TAB 14: KEAHLIAN & JALUR KARIR (PHASE 9) -->
    <div x-show="activeEssTab === 'skills-career'" x-cloak class="space-y-6">
        <!-- Overview Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-6 border-b border-slate-100 gap-4">
                <div>
                    <span class="text-xs font-bold text-red-600 uppercase tracking-wider">Profil Kompetensi Jabatan</span>
                    <h2 class="text-xl font-black text-slate-900 mt-1">Standar Keahlian: {{ $employee?->position?->title ?? 'Staf' }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Departemen {{ $employee?->department?->name ?? 'Umum' }} • Pemetaan kesesuaian target kompetensi dengan tingkat kemahiran aktual.</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-4 shrink-0">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Kecocokan Standar</div>
                        <div class="text-2xl font-black {{ ($mySkillAnalysis['overall_match_percentage'] ?? 0) >= 80 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $mySkillAnalysis['overall_match_percentage'] ?? 0 }}%
                        </div>
                    </div>
                    <div class="border-l border-slate-200 pl-4">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Standar Terpenuhi</div>
                        <div class="text-sm font-bold text-slate-800">
                            {{ $mySkillAnalysis['met_requirements'] ?? 0 }} / {{ $mySkillAnalysis['total_requirements'] ?? 0 }} Skill
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skill Breakdown List -->
            <div class="mt-6">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Daftar Keahlian & Kesenjangan (Gap)</h3>
                @if(empty($mySkillAnalysis['skills']))
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada standar kompetensi jabatan yang ditetapkan untuk posisi Anda oleh HR.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($mySkillAnalysis['skills'] as $sk)
                            <div class="p-4 rounded-xl border {{ $sk['is_met'] ? 'border-emerald-200 bg-emerald-50/20' : 'border-amber-200 bg-amber-50/20' }} space-y-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $sk['skill_name'] }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $sk['category_name'] }}</div>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sk['is_met'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $sk['is_met'] ? 'Sesuai Standar' : 'Perlu Peningkatan' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-[11px] font-medium pt-1">
                                    <span class="text-slate-600">Level Anda: <strong>Lv {{ $sk['current_level'] }}/5</strong></span>
                                    <span class="text-slate-600">Target Wajib: <strong>Lv {{ $sk['required_level'] }}/5</strong></span>
                                </div>

                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $sk['is_met'] ? 'bg-emerald-500' : 'bg-amber-500' }}"
                                         style="width: {{ $sk['match_percentage'] }}%"></div>
                                </div>

                                @if($sk['certification_name'])
                                    <div class="text-[10px] text-slate-500 flex items-center gap-1 pt-1">
                                        <i data-lucide="award" class="w-3.5 h-3.5 text-blue-500"></i>
                                        Sertifikasi: <strong>{{ $sk['certification_name'] }}</strong>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Recommended LMS Training Courses -->
            @if(!empty($mySkillAnalysis['recommended_trainings']) && count($mySkillAnalysis['recommended_trainings']) > 0)
                <div class="mt-8 p-5 rounded-xl bg-blue-50/70 border border-blue-200">
                    <div class="flex items-center gap-2 text-xs font-bold text-blue-900 mb-3">
                        <i data-lucide="graduation-cap" class="w-4 h-4 text-blue-600"></i>
                        Rekomendasi Pelatihan Perusahaan Untuk Peningkatan Kompetensi (LMS):
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($mySkillAnalysis['recommended_trainings'] as $t)
                            <div class="p-3 bg-white rounded-lg border border-blue-100 text-xs flex items-center justify-between shadow-2xs">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $t->title }}</div>
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        Trainer: {{ $t->trainer_name ?? 'Internal' }} • {{ $t->location }}
                                    </div>
                                    <div class="text-[10px] text-blue-600 font-semibold mt-1">
                                        Mulai: {{ $t->start_date?->format('d M Y') }}
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800">
                                    Tersedia
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
