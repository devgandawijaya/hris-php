# UI/UX DESIGN SYSTEM GUIDE
## RiHRIS — Design Tokens, Component Standards & Layout Specifications

**Primary Color:** Red (`#DC2626` / Tailwind `red-600`)  
**Secondary Color:** Dark Red (`#991B1B` / Tailwind `red-800`)  
**Text Primary:** Dark Slate (`#1E293B` / Tailwind `slate-800`)  
**Text Secondary:** Slate Gray (`#64748B` / Tailwind `slate-500`)  
**Surface & Background:** White (`#FFFFFF`) & Crisp Surface (`#F8FAFC` / Tailwind `slate-50`)  
**Container Rules:** Landing Page (Max `1200px`), Dashboard Content (Max `1440px`)

---

## 1. COLOR PALETTE & DESIGN TOKENS

```
========================================================================
TOKEN NAME         HEX VALUE    TAILWIND CLASS     USAGE
========================================================================
Primary            #DC2626      bg-red-600         Tombol CTA utama, active state, logo brand
Primary Dark       #991B1B      bg-red-800         Hover state tombol utama, header dark accent
Primary Light      #FEE2E2      bg-red-100         Badge background, warning/danger highlight
Secondary Slate    #1E293B      text-slate-800     Heading utama, teks teks data penting
Secondary Gray     #64748B      text-slate-500     Subheading, caption, placeholder, deskripsi
Surface BG         #F8FAFC      bg-slate-50        Background dashboard body, secondary card
Border Subtle      #E2E8F0      border-slate-200   Garis pemisah tabel, border input, border card
Success            #16A34A      text-green-600     Status kehadiran tepat waktu, approval, paid
Warning            #F59E0B      text-amber-500     Status pending, toleransi keterlambatan
Danger             #DC2626      text-red-600       Status rejected, terminated, telat parah
Info               #2563EB      text-blue-600      Pengumuman, shift normal, instruksi
========================================================================
```

---

## 2. ATURAN TATA LETAK & CONTAINER (LAYOUT RULES)

> [!IMPORTANT]
> Jangan biarkan konten memanjang tak terbatas memenuhi layar monitor ultra-wide. Seluruh halaman wajib dibungkus dalam container terpusat dengan padding samping yang nyaman.

### 2.1 Public Landing Page
- **Outer Shell**: `min-h-screen bg-white`
- **Section Background**: Dapat melebar penuh (full width) untuk warna background atau gradient halus.
- **Section Inner Container**: Wajib menggunakan `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8` (maksimal 1200px - 1280px).
- **Hero Slider**: Proporsional, tinggi sekitar 480px - 580px, teks terpusat atau 2 kolom (teks kiri, ilustrasi kanan), navigasi panah kiri-kanan dan indikator dots.

### 2.2 Dashboard Layout (Super Admin, Company Admin, HR, ESS)
- **Top Navigation Bar**: Tinggi 64px, background putih bersih dengan border bawah `border-b border-slate-200`. Berisi logo, company switcher, notifikasi lonceng, dan profil user avatar.
- **Sidebar**: Lebar desktop 260px (`w-64`), fix posisi kiri dengan scroll mandiri. Pada layar tablet/mobile (< 1024px), sidebar otomatis tersembunyi dan bertransformasi menjadi **slide drawer** yang digerakkan oleh toggle Alpine.js (`x-data="{ sidebarOpen: false }"`).
- **Main Content Area**:
  - `flex-1 bg-slate-50 min-h-screen`
  - Container pembungkus utama: `max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8`
  - Konten tidak menempel ke tepi layar desktop ultra-wide (terdapat margin kiri-kanan proporsional).

---

## 3. SPESIFIKASI KOMPONEN BLADE REUSABLE

### 3.1 Tombol (Button Component)
- **Primary**: `bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition inline-flex items-center gap-2 focus:ring-2 focus:ring-red-500 focus:ring-offset-2`
- **Secondary**: `bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium px-4 py-2 rounded-lg shadow-sm transition inline-flex items-center gap-2`
- **Danger**: `bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition inline-flex items-center gap-2`
- **Ghost**: `text-slate-600 hover:text-red-600 hover:bg-red-50 font-medium px-3 py-1.5 rounded-lg transition`

### 3.2 Form Input & Select
- Label: `block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5`
- Input: `w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition`
- Input Error State: `border-red-500 focus:border-red-500 focus:ring-red-500/20`
- Error Message: `text-xs text-red-600 mt-1 flex items-center gap-1`

### 3.3 Kartu & Panel (Card Component)
- `bg-white rounded-xl border border-slate-200/80 shadow-sm p-6`
- Card Header: `flex items-center justify-between pb-4 border-b border-slate-100 mb-6`
- Stat Card: Nilai angka berukuran besar (`text-2xl sm:text-3xl font-bold text-slate-900`), label ringkas, dan icon berlatar warna soft di sudut kanan atas.

### 3.4 Tabel Data (Data Table Component)
- Wrapper: `overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm`
- Header Row: `bg-slate-50/80 border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wider text-slate-600`
- Table Cell: `px-6 py-4 text-sm text-slate-700 whitespace-nowrap`
- Row Hover: `hover:bg-slate-50/60 transition`
- Empty State: Ilustrasi icon folder kosong, judul ramah "Tidak ada data ditemukan", subjudul penjelasan, dan tombol aksi untuk menambah data baru.

### 3.5 Status Badges
- **Active / Present / Approved**: `inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200`
- **Pending / In Review**: `inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200`
- **Late / Rejected / Terminated**: `inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200`
- **Trial / Special**: `inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200`

---

## 4. INTEGRASI IKON LUCIDE

Menggunakan set ikon SVG Lucide yang konsisten dan elegan (stroke-width 1.75):
- Dashboard: `layout-dashboard`
- Karyawan: `users`, `user-plus`, `user-check`
- Presensi: `clock`, `calendar`, `map-pin`
- Cuti: `calendar-off`, `sun`
- Penggajian: `banknote`, `receipt`, `calculator`
- Pajak & BPJS: `shield-check`, `percent`
- CMS: `globe`, `sliders`, `help-circle`
- Pengaturan: `settings`, `sliders-horizontal`, `log-out`

---
*Panduan UI/UX Design System ini wajib dipatuhi untuk menjamin konsistensi estetika dan kemudahan navigasi pengguna.*

