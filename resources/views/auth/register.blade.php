@extends('layouts.auth')

@section('title', 'Daftar Perusahaan Baru')

@section('content')
<div class="mb-6">
    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold mb-2">
        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-600"></i>
        Uji Coba Gratis 14 Hari Tanpa Kartu Kredit
    </div>
    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Daftarkan Perusahaan Anda</h2>
    <p class="text-xs text-slate-500 mt-1">Buat ruang kerja multi-tenant baru yang patuh regulasi Indonesia.</p>
</div>

@if($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
        @foreach($errors->all() as $err)
            <div class="flex items-center gap-1.5">
                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                <span>{{ $err }}</span>
            </div>
        @endforeach
    </div>
@endif

<form action="{{ route('register') }}" method="POST" class="space-y-4">
    @csrf

    <div>
        <label for="company_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Perusahaan / Organisasi *</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i data-lucide="building-2" class="w-4 h-4"></i>
            </div>
            <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="PT Maju Bersama Digital">
        </div>
    </div>

    <div>
        <label for="company_email" class="block text-xs font-semibold text-slate-700 mb-1">Email Resmi Perusahaan *</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </div>
            <input type="email" id="company_email" name="company_email" value="{{ old('company_email') }}" required
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="admin@perusahaan.co.id">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="pic_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap PIC *</label>
            <input type="text" id="pic_name" name="pic_name" value="{{ old('pic_name') }}" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="Budi Setiawan">
        </div>
        <div>
            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp *</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="+628123456789">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi (Min. 8 Karakter) *</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <input type="password" id="password" name="password" required
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="••••••••">
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi *</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="••••••••">
        </div>
    </div>

    <div class="pt-2">
        <label class="flex items-start gap-2.5 cursor-pointer">
            <input type="checkbox" name="terms" value="1" required class="w-4 h-4 mt-0.5 rounded border-slate-300 text-[#DC2626] focus:ring-red-500">
            <span class="text-xs text-slate-600 leading-normal">
                Saya menyetujui <a href="#" class="text-[#DC2626] underline">Syarat & Ketentuan</a> serta Kebijakan Kepatuhan Ketenagakerjaan RiHRIS.
            </span>
        </label>
    </div>

    <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-[#DC2626] hover:bg-[#991B1B] shadow-sm shadow-red-600/30 transition flex items-center justify-center gap-2">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        Mulai Ruang Kerja RiHRIS
    </button>
</form>

<div class="mt-6 pt-5 border-t border-slate-100 text-center">
    <p class="text-xs text-slate-600">
        Sudah memiliki akun terdaftar?
        <a href="{{ route('login') }}" class="font-semibold text-[#DC2626] hover:underline">Masuk di sini</a>
    </p>
</div>
@endsection

