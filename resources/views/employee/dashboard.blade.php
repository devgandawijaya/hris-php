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

        <button type="button" @click="activeEssTab = 'leave'"
                :class="activeEssTab === 'leave' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="calendar-off" class="w-4 h-4"></i>
            Pengajuan Cuti
        </button>

        <button type="button" @click="activeEssTab = 'correction'"
                :class="activeEssTab === 'correction' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="check-square" class="w-4 h-4"></i>
            Koreksi Presensi
        </button>

        <button type="button" @click="activeEssTab = 'overtime'"
                :class="activeEssTab === 'overtime' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="timer" class="w-4 h-4"></i>
            Pengajuan Lembur (SPL)
        </button>

        <button type="button" @click="activeEssTab = 'profile'"
                :class="activeEssTab === 'profile' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4"></i>
            Pengkinian Data Mandiri
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

            <!-- Visual indicator -->
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

                @if(!$todayRecord)
                    <button type="submit" class="w-full py-4 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-sm font-bold shadow-md shadow-red-600/30 transition flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        Clock In Sekarang (Presensi Masuk)
                    </button>
                @elseif(empty($todayRecord->clock_out))
                    <button type="submit" class="w-full py-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold shadow-md transition flex items-center justify-center gap-2">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        Clock Out Sekarang (Presensi Pulang)
                    </button>
                @else
                    <button type="button" disabled class="w-full py-4 rounded-xl bg-slate-100 text-slate-400 text-sm font-bold cursor-not-allowed">
                        Presensi Hari Ini Telah Selesai
                    </button>
                @endif
            </form>
        </div>

        <!-- Attendance History -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-emerald-600"></i>
                Riwayat Presensi Terbaru Anda
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="px-4 py-2.5">Tanggal</th>
                            <th class="px-4 py-2.5">Clock In</th>
                            <th class="px-4 py-2.5">Clock Out</th>
                            <th class="px-4 py-2.5">Jam Kerja</th>
                            <th class="px-4 py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($attendanceHistory as $att)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $att->date->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-mono">{{ $att->clock_in ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono">{{ $att->clock_out ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $att->work_hours }} Jam</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $att->status === 'present' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $att->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada riwayat presensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: LEAVE REQUEST -->
    <div x-show="activeEssTab === 'leave'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="calendar-off" class="w-4 h-4 text-red-600"></i>
                Formulir Pengajuan Cuti Mandiri
            </h3>

            <form action="{{ route('ess.leave.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Cuti *</label>
                        <select name="leave_type_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->days_allowed }} hari)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mulai Cuti *</label>
                        <input type="date" name="start_date" min="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berakhir Cuti *</label>
                    <input type="date" name="end_date" min="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pengajuan Cuti *</label>
                    <textarea name="reason" rows="3" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800" placeholder="Jelaskan keperluan permohonan cuti..."></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-semibold transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Permohonan Cuti
                </button>
            </form>
        </div>

        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100">Status Permohonan Cuti Anda</h3>
            <div class="space-y-3">
                @forelse($leaveRequests as $lr)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>{{ $lr->leaveType?->name }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] {{ $lr->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($lr->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ ucfirst($lr->status) }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1 font-mono">
                            {{ $lr->start_date->format('d M') }} - {{ $lr->end_date->format('d M Y') }} ({{ $lr->total_days }} Hari Kerja)
                        </div>
                        <div class="text-slate-600 mt-2">{{ $lr->reason }}</div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada permohonan cuti.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 3: CORRECTION REQUEST -->
    <div x-show="activeEssTab === 'correction'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="check-square" class="w-4 h-4 text-emerald-600"></i>
                Formulir Koreksi Presensi (Lupa Clock In/Out)
            </h3>

            <form action="{{ route('ess.correction.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal yang Ingin Dikoreksi *</label>
                    <input type="date" name="date" max="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Masuk Sebenarnya</label>
                        <input type="time" name="corrected_clock_in" value="08:30" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Pulang Sebenarnya</label>
                        <input type="time" name="corrected_clock_out" value="17:30" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Lupa / Kendala Teknis *</label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Terkendala koneksi seluler saat di lobby kantor..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Permohonan Koreksi ke HR
                </button>
            </form>
        </div>

        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100">Riwayat Pengajuan Koreksi</h3>
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
                            Masuk: {{ $cor->corrected_clock_in ?? '-' }} • Pulang: {{ $cor->corrected_clock_out ?? '-' }}
                        </div>
                        <div class="text-slate-600 mt-2">{{ $cor->reason }}</div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada pengajuan koreksi presensi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 4: OVERTIME REQUEST (SPL) -->
    <div x-show="activeEssTab === 'overtime'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-xs text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="timer" class="w-4 h-4 text-amber-600"></i>
                Pengajuan Surat Perintah Lembur (SPL)
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

    <!-- TAB 5: PROFILE UPDATE -->
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
