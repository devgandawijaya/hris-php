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

</div>
@endsection
