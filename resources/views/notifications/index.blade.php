@extends('layouts.app')

@section('title', 'Pusat Notifikasi & Pemberitahuan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-red-50 text-[#DC2626] rounded-xl border border-red-100">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pusat Notifikasi</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Semua peringatan sistem, persetujuan alur kerja, pengumuman, dan pembaruan operasional.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(($unreadCount ?? 0) > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-[#DC2626] border border-red-200">
                    <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                    {{ $unreadCount }} Belum Dibaca
                </span>
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        <i data-lucide="check-check" class="w-4 h-4"></i> Tandai Semua Sudah Dibaca
                    </button>
                </form>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Semua Notifikasi Telah Dibaca
                </span>
            @endif
        </div>
    </div>

    <!-- Notification List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="{ filter: 'all' }">
        <!-- Filter Tabs -->
        <div class="flex border-b border-slate-100 px-6 pt-4 gap-6 text-xs font-semibold">
            <button @click="filter = 'all'" :class="filter === 'all' ? 'text-[#DC2626] border-b-2 border-[#DC2626] pb-3 font-bold' : 'text-slate-500 hover:text-slate-800 pb-3 transition'">
                Semua ({{ count($notifications ?? []) }})
            </button>
            <button @click="filter = 'unread'" :class="filter === 'unread' ? 'text-[#DC2626] border-b-2 border-[#DC2626] pb-3 font-bold' : 'text-slate-500 hover:text-slate-800 pb-3 transition'">
                Belum Dibaca ({{ $unreadCount ?? 0 }})
            </button>
            <button @click="filter = 'read'" :class="filter === 'read' ? 'text-[#DC2626] border-b-2 border-[#DC2626] pb-3 font-bold' : 'text-slate-500 hover:text-slate-800 pb-3 transition'">
                Sudah Dibaca ({{ max(0, count($notifications ?? []) - ($unreadCount ?? 0)) }})
            </button>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($notifications ?? [] as $notification)
                <div x-show="filter === 'all' || (filter === 'unread' && !{{ $notification->is_read ? 'true' : 'false' }}) || (filter === 'read' && {{ $notification->is_read ? 'true' : 'false' }})"
                     class="p-5 flex items-start justify-between gap-4 transition hover:bg-slate-50/80 {{ !$notification->is_read ? 'bg-red-50/20' : '' }}">
                    <div class="flex items-start gap-4">
                        <div class="p-2.5 rounded-xl mt-0.5 {{ !$notification->is_read ? 'bg-red-100 text-[#DC2626]' : 'bg-slate-100 text-slate-500' }}">
                            @if(str_contains(strtolower($notification->type ?? ''), 'alert') || str_contains(strtolower($notification->type ?? ''), 'urgent'))
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            @elseif(str_contains(strtolower($notification->type ?? ''), 'approval') || str_contains(strtolower($notification->type ?? ''), 'success'))
                                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            @elseif(str_contains(strtolower($notification->type ?? ''), 'contract') || str_contains(strtolower($notification->type ?? ''), 'document'))
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            @else
                                <i data-lucide="bell" class="w-5 h-5"></i>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold text-slate-900 {{ !$notification->is_read ? 'font-bold' : '' }}">
                                    {{ $notification->title }}
                                </h3>
                                @if(!$notification->is_read)
                                    <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $notification->message }}</p>
                            <div class="flex items-center gap-3 pt-1 text-[11px] text-slate-400">
                                <span><i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>{{ $notification->created_at ? $notification->created_at->diffForHumans() : 'Baru saja' }}</span>
                                @if($notification->type)
                                    <span>•</span>
                                    <span class="uppercase font-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">{{ $notification->type }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#DC2626] hover:bg-red-700 text-white rounded-lg text-xs font-medium transition shadow-sm">
                                Buka <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif

                        @if(!$notification->is_read)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" title="Tandai sudah dibaca" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded-lg transition">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-16 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 border border-slate-200">
                        <i data-lucide="bell-off" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Tidak Ada Notifikasi Baru</h3>
                    <p class="text-xs text-slate-500 mt-1">Anda telah memproses semua notifikasi dan pembaruan sistem.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
