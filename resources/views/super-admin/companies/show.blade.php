@extends('layouts.app')

@section('title', 'Detail Perusahaan: ' . $tenant->name)

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('super.admin.companies.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar Perusahaan
        </a>

        <div class="flex items-center gap-2">
            <form action="{{ route('super.admin.impersonate.start', $tenant->id) }}" method="POST" onsubmit="return confirm('Mulai sesi impersonasi ke {{ $tenant->name }}? Anda akan masuk sebagai owner perusahaan.');">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-slate-900 transition shadow-sm inline-flex items-center gap-1.5">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    Login Sebagai Admin (Impersonate)
                </button>
            </form>

            <form action="{{ route('super.admin.companies.toggle-status', $tenant->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status perusahaan ini?');">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $tenant->status === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                    {{ $tenant->status === 'active' ? 'Suspend Perusahaan' : 'Aktifkan Perusahaan' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Company Header Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-red-50 text-[#DC2626] font-black text-xl flex items-center justify-center border border-red-200">
                {{ strtoupper(substr($tenant->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl font-bold text-slate-900">{{ $tenant->name }}</h1>
                    @if($tenant->status === 'active')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                    @elseif($tenant->status === 'trial')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Trial (s/d {{ $tenant->trial_ends_at?->format('d M Y') }})</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">{{ $tenant->status }}</span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    <span>Slug: <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-slate-700">{{ $tenant->slug }}</code></span>
                    <span>UUID: <code class="font-mono text-slate-400">{{ $tenant->uuid }}</code></span>
                    <span>Terdaftar: {{ $tenant->created_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-right">
            <div class="text-[10px] uppercase font-bold text-slate-400">Paket Langganan Aktif</div>
            <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $tenant->activeSubscription?->plan?->name ?? 'Free Trial' }}</div>
            <form action="{{ route('super.admin.companies.update-plan', $tenant->id) }}" method="POST" class="mt-2 flex items-center justify-end gap-1.5">
                @csrf
                <select name="plan_id" class="px-2 py-1 bg-white border border-slate-300 rounded text-[11px] font-semibold">
                    @foreach(\App\Models\SubscriptionPlan::where('is_active', true)->get() as $p)
                        <option value="{{ $p->id }}" {{ $tenant->activeSubscription?->plan_id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                <select name="billing_cycle" class="px-2 py-1 bg-white border border-slate-300 rounded text-[11px]">
                    <option value="monthly" {{ $tenant->activeSubscription?->billing_cycle === 'monthly' ? 'selected' : '' }}>Bln</option>
                    <option value="yearly" {{ $tenant->activeSubscription?->billing_cycle === 'yearly' ? 'selected' : '' }}>Thn</option>
                </select>
                <button type="submit" class="px-2.5 py-1 bg-slate-900 text-white rounded text-[11px] font-bold hover:bg-black transition">Ubah</button>
            </form>
        </div>
    </div>

    <!-- 2 Column Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Legal & Contact Info -->
        @php $primaryCompany = $tenant->companies->first(); @endphp
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-red-600"></i>
                Informasi Legal Perusahaan
            </h2>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Nama Legal Akta:</span>
                    <span class="font-semibold text-slate-900">{{ $primaryCompany?->legal_name ?? $tenant->name }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">NPWP Perusahaan:</span>
                    <span class="font-mono text-slate-900">{{ $primaryCompany?->npwp ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Email Resmi:</span>
                    <span class="font-medium text-slate-900">{{ $primaryCompany?->email ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Telepon Kantor:</span>
                    <span class="font-medium text-slate-900">{{ $primaryCompany?->phone ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Alamat Kantor Pusat:</span>
                    <span class="font-medium text-slate-900 text-right max-w-xs">{{ $primaryCompany?->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Indonesian Compliance Settings -->
        @php $settings = $primaryCompany?->settings; @endphp
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                Kebijakan Kepatuhan Ketenagakerjaan (PP 35/2021)
            </h2>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Hari Kerja Mingguan:</span>
                    <span class="font-semibold text-slate-900">{{ $settings?->work_days_per_week ?? 5 }} Hari / Minggu</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Jam Kerja Normal Harian:</span>
                    <span class="font-semibold text-slate-900">{{ $settings?->regular_work_hours_per_day ?? 8.0 }} Jam / Hari</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Toleransi Keterlambatan:</span>
                    <span class="font-semibold text-slate-900">{{ $settings?->late_tolerance_minutes ?? 15 }} Menit</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Batas Atas BPJS Kesehatan:</span>
                    <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($settings?->bpjs_kes_max_cap ?? 12000000, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Batas Atas BPJS TK (JP):</span>
                    <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($settings?->bpjs_tk_jp_max_cap ?? 10042300, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Tanggal Cut-Off & Gajian:</span>
                    <span class="font-semibold text-slate-900">Cut-Off Tgl {{ $settings?->payroll_cutoff_day ?? 20 }} • Bayar Tgl {{ $settings?->payroll_payment_day ?? 25 }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Users in this Tenant -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
            Daftar Pengguna Terkait dalam Perusahaan Ini
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-4 py-2.5 rounded-l-lg">Nama Pengguna</th>
                        <th class="px-4 py-2.5">Email</th>
                        <th class="px-4 py-2.5">Status Akses</th>
                        <th class="px-4 py-2.5 rounded-r-lg">Tanggal Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tenant->users as $u)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $u->name }}</td>
                            <td class="px-4 py-3 font-mono">{{ $u->email }}</td>
                            <td class="px-4 py-3">
                                @if($u->pivot->is_owner)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Company Owner</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Anggota</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $u->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Tidak ada pengguna terkait.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

