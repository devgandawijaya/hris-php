# RiHRIS — Human Resource Information System (SaaS Indonesia)
## Multi-Company Platform with 100% Indonesian Labor & Tax Compliance

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16%2B-336791.svg)](https://www.postgresql.org)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-3.4%2B-38B2AC.svg)](https://tailwindcss.com)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg)](https://docker.com)
[![Compliance](https://img.shields.io/badge/Compliance-PP%2035%2F2021%20%7C%20PMK%20168%2F2023-emerald.svg)](#indonesian-hr-compliance)

RiHRIS adalah aplikasi Software-as-a-Service (SaaS) Multi-Tenant berbasis arsitektur **Modular Monolith** yang dirancang khusus untuk pasar korporasi dan UKM di Indonesia. Sistem ini mengintegrasikan seluruh administrasi SDM, presensi geofencing GPS, manajemen cuti, penggajian otomatis, dan kepatuhan regulasi ketenagakerjaan dalam satu platform terpusat.

---

## 📑 DAFTAR DOKUMENTASI ARSITEKTUR LENGKAP

Seluruh rancangan arsitektur dan spesifikasi sistem telah didokumentasikan secara komprehensif pada direktori `docs/`:

1. [01. Software Requirements Specification (SRS)](docs/01_SOFTWARE_REQUIREMENTS_SPECIFICATION.md)
2. [02. System Architecture Document (SAD)](docs/02_SYSTEM_ARCHITECTURE.md)
3. [03. Multi-Tenant Architecture & Data Isolation Blueprint](docs/03_MULTI_TENANT_ARCHITECTURE.md)
4. [04. Database ERD & PostgreSQL Data Dictionary](docs/04_DATABASE_ERD_AND_DATA_DICTIONARY.md)
5. [05. UI/UX Design System & Layout Guidelines](docs/05_UI_UX_DESIGN_SYSTEM.md)

---

## 🇮🇩 100% INDONESIAN HR & TAX COMPLIANCE

RiHRIS dibangun dengan kepatuhan hukum sebagai pilar utama tanpa mengunci tarif atau rumus di dalam kode (dapat dikonfigurasi secara dinamis):

1. **PPh 21 TER 2024 (PP No. 58 Tahun 2023 & PMK No. 168 Tahun 2023)**:
   - Kategori A: PTKP TK/0, TK/1, K/0 (Tarif 0% - 34%).
   - Kategori B: PTKP TK/2, TK/3, K/1, K/2 (Tarif 0% - 34%).
   - Kategori C: PTKP K/3 (Tarif 0% - 34%).
   - Rekonsiliasi masa pajak terakhir (Desember) menggunakan lapisan tarif progresif Pasal 17 UU HPP.
2. **BPJS Ketenagakerjaan (5 Program Wajib)**:
   - **JHT (Jaminan Hari Tua)**: Pemberi Kerja 3.70%, Pekerja 2.00%.
   - **JKM (Jaminan Kematian)**: Pemberi Kerja 0.30%.
   - **JKK (Jaminan Kecelakaan Kerja)**: Tarif bertingkat sesuai kelas risiko (0.24% s.d. 1.74%).
   - **JP (Jaminan Pensiun)**: Pemberi Kerja 2.00%, Pekerja 1.00% dengan batas atas upah (cap) resmi.
   - **JKP (Jaminan Kehilangan Pekerjaan)**.
3. **BPJS Kesehatan**:
   - Iuran total 5% (Pemberi Kerja 4.00%, Pekerja 1.00%) dengan batas atas upah Rp 12.000.000.
4. **Perhitungan Lembur (PP No. 35 Tahun 2021)**:
   - Upah sejam lembur dihitung berdasarkan rumus resmi: $\frac{1}{173} \times \text{Upah Sebulan}$.
   - Hari kerja biasa: Jam ke-1 x 1.5, jam ke-2 dst x 2.0.
   - Hari istirahat/libur resmi: Perhitungan bertingkat hingga x 4.0.
5. **Uang Kompensasi PKWT (Pasal 61A UU Cipta Kerja)**:
   - Kalkulasi otomatis saat kontrak berakhir: $\frac{\text{Masa Kerja (Bulan)}}{12} \times 1\text{ Bulan Upah}$.
6. **Tunjangan Hari Raya (THR) Keagamaan (Permenaker No. 6/2016)**:
   - Masa kerja $\ge 12$ bulan = 1 bulan upah penuh; < 12 bulan dihitung pro-rata.

---

## 🏢 MULTI-TENANT SAAS ARCHITECTURE

- **Model**: Shared Database with `tenant_id` Discriminator & Eloquent Global Scoping (`TenantScope`).
- **Isolasi Mutlak**: Setiap query transaksi, penyimpanan berkas dokumen, ekspor laporan, dan notifikasi terikat dengan tenant aktif.
- **Hierarki Organisasi**: `Tenant` -> `Company` -> `Branch` -> `Department` -> `Position` -> `Employee`.
- **Role-Based Access Control (RBAC)**: 14 default roles mulai dari Super Admin, Company Owner, HR Manager, Finance, hingga Employee.

---

## 🎨 UI/UX DESIGN SYSTEM

Mengikuti pedoman warna dan layout yang presisi:
- **Primary Color**: Merah Korporat (`#DC2626` / `red-600`) & Deep Dark Red (`#991B1B`).
- **Surface**: Light Gray (`#F8FAFC`).
- **Text**: Dark Slate (`#1E293B`) & Gray (`#64748B`).
- **Container Layout**:
  - Landing Page: Centered Container max-width `1200px`.
  - Dashboard Content: Centered Container max-width `1440px`.
  - Mobile: Slide drawer sidebar yang responsif tanpa pergeseran layout.
- **Komponen**: Lucide SVG Icons, Alpine.js interactive toggles & sliders.

---

## 🚀 PANDUAN INSTALASI & MENJALANKAN DENGAN DOCKER COMPOSE

Aplikasi telah dilengkapi kontainer Docker Compose multi-service siap pakai:

### 1. Prasyarat
- Docker Engine 24+ & Docker Compose v2+
- Port 8080 (Web Nginx), Port 5432 (PostgreSQL), dan Port 6379 (Redis) tersedia.

### 2. Menjalankan Kontainer
```bash
# 1. Jalankan seluruh service (app, nginx, postgres, redis, queue, scheduler)
docker compose up -d --build

# 2. Jalankan migrasi database
docker compose exec app php artisan migrate

# 3. Jalankan master seeder (Roles, Plans, CMS, SuperAdmin, Demo Company)
docker compose exec app php artisan db:seed

# 4. Bersihkan cache optimasi
docker compose exec app php artisan optimize:clear
```

### 3. Mengakses Aplikasi di Browser
- **Public Landing Page**: [http://localhost:8080](http://localhost:8080)
- **Login Portal**: [http://localhost:8080/login](http://localhost:8080/login)
- **Registrasi SaaS**: [http://localhost:8080/register](http://localhost:8080/register)

---

## 🔑 KREDENSIAL BAWAAN (DEFAULT ACCOUNTS)

Setelah menjalankan `php artisan db:seed`, akun berikut siap digunakan untuk uji coba:

### A. Super Administrator Platform
- **URL Login**: [http://localhost:8080/login](http://localhost:8080/login)
- **Email**: `superadmin@rihris.id`
- **Password**: `Password123#`
- **Akses**: Pengelolaan seluruh tenant, penangguhan (suspend/activate), manajemen CMS landing page, dan audit logs.

### B. Demo Perusahaan (PT Solusi Digital Nusantara)
- **Company Owner**:
  - **Email**: `owner@solusidigital.id`
  - **Password**: `Password123#`
- **HR Manager**:
  - **Email**: `hr@solusidigital.id`
  - **Password**: `Password123#`
- **Employee (Budi Santoso - Lead Developer)**:
  - **Email**: `budi@solusidigital.id`
  - **Password**: `Password123#`
  - **Akses**: Portal Employee Self-Service (ESS), clock-in/out GPS simulasi, pengajuan cuti, dan cek slip gaji.

---

## 🧪 MENJALANKAN AUTOMATED TESTS

Untuk menjalankan pengujian unit dan fitur:

```bash
docker compose exec app php artisan test
```

Pengujian mencakup:
1. `ComplianceCalculationTest`: Verifikasi akurasi matematis PPh 21 TER (Kategori A, B, C), batas atas upah BPJS Kesehatan & Ketenagakerjaan, lembur 1/173 PP 35/2021, dan kompensasi PKWT.
2. `MultiTenancyIsolationTest`: Verifikasi pemisahan data antar-perusahaan via `TenantScope`.
3. `AuthenticationAndRegistrationTest`: Verifikasi auto-provisioning SaaS multi-tenant saat registrasi.
4. `LandingPageCmsTest`: Verifikasi rendering dinamis CMS landing page dan form konsultasi.

---
*RiHRIS — Solusi HRIS SaaS Terpercaya untuk Bisnis Indonesia.*

