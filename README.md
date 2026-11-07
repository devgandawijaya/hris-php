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

## 📑 DAFTAR DOKUMENTASI LENGKAP (17 MODUL TEKNIS)

Seluruh rancangan arsitektur, kepatuhan hukum, dan spesifikasi sistem terdokumentasi secara lengkap pada direktori `docs/`:

1. [01. Software Requirements Specification (SRS)](docs/01_SOFTWARE_REQUIREMENTS_SPECIFICATION.md)
2. [02. System Architecture Document (SAD)](docs/02_SYSTEM_ARCHITECTURE.md)
3. [03. Multi-Tenant Architecture & Data Isolation Blueprint](docs/03_MULTI_TENANT_ARCHITECTURE.md)
4. [04. Database ERD & PostgreSQL Data Dictionary](docs/04_DATABASE_ERD_AND_DATA_DICTIONARY.md)
5. [05. UI/UX Design System & Layout Guidelines](docs/05_UI_UX_DESIGN_SYSTEM.md)
6. [06. Phase 3: Payroll, Tax & Indonesian Compliance](docs/06_PHASE_3_PAYROLL_TAX_COMPLIANCE.md)
7. [07. Phase 4: Talent Management, ATS & Appraisals](docs/07_PHASE_4_TALENT_MANAGEMENT.md)
8. [08. Phase 5: Enterprise Systems, Workflow & Security](docs/08_PHASE_5_ENTERPRISE_SYSTEMS.md)
9. [09. REST API v1 & Webhooks Specification](docs/09_REST_API_SPECIFICATION.md)
10. [10. Backup & Disaster Recovery Guide](docs/10_BACKUP_AND_RESTORE_GUIDE.md)
11. [11. Production Deployment & Maintenance Guide](docs/11_PRODUCTION_DEPLOYMENT_GUIDE.md)
12. [12. Phase 6: Employee Relations, Disciplinary & Engagement](docs/12_PHASE_6_EMPLOYEE_RELATIONS_AND_ENGAGEMENT.md)
13. [13. Phase 7: Career Transitions, Severance & Notifications](docs/13_PHASE_7_CAREER_TRANSITIONS_AND_SEVERANCE.md)
14. [14. Phase 8: Financial Well-being & Advanced Engagement](docs/14_PHASE_8_FINANCIAL_WELLBEING_AND_ENGAGEMENT.md)
15. [15. Phase 9: Competency Skill Matrix, Succession & Background Cron](docs/15_PHASE_9_SKILL_MATRIX_AND_SUCCESSION.md)
16. [16. Phase 10: Appraisals 360°, Extended Profile, Bank Disbursal & UMK/BPJS Gateway](docs/16_PHASE_10_APPRAISALS_EXTENDED_PROFILE_AND_BANK_DISBURSAL.md)
17. [17. Phase 11: Wage Scales Permenaker 1/2017, SPPD Business Travel, Offboarding Clearance & e-Bupot 21/26](docs/17_PHASE_11_WAGE_SCALES_SPPD_CLEARANCE_AND_EBUPOT.md)
18. [18. Phase 12: Digital Contracts UU ITE, Probation Pasal 60, SaaS Midtrans Gateway, Kirkpatrick L&D & FWA](docs/18_PHASE_12_DIGITAL_CONTRACTS_PROBATION_GATEWAY_AND_LND.md)

---

## 🚀 STATUS IMPLEMENTASI 12 PHASE ROADMAP

| Phase | Modul & Cakupan Utama | Status |
|---|---|---|
| **Phase 1: Foundation & SaaS** | Multi-Tenancy (`TenantScope`), RBAC 14 Roles, CMS Landing Page, Super Admin Panel, Registrasi SaaS & Onboarding Perusahaan. | ✅ Complete |
| **Phase 2: Core HR** | Master Karyawan, Struktur Organisasi, Presensi Geofencing GPS, Shift Kerja, Manajemen Cuti, SPL Lembur, dan Portal Mandiri ESS. | ✅ Complete |
| **Phase 3: Payroll & Compliance** | Engine Payroll, BPJS 5 Program, PPh 21 TER (PMK 168/2023), Rekonsiliasi 1721-A1, THR Permenaker 6/2016, Klaim Reimbursement, dan Kompensasi PKWT PP 35/2021. | ✅ Complete |
| **Phase 4: Talent Management** | Rekrutmen ATS Pipeline, Konversi Pelamar ke Karyawan, Checklist Onboarding/Offboarding, Manajemen Aset, Penilaian Kinerja KPI/OKR 30/70, dan Pelatihan L&D Bersertifikat. | ✅ Complete |
| **Phase 5: Enterprise Systems** | Workflow Engine Multi-Level, Eksekutif Analitik C-Level & Streaming CSV, SaaS Subscription Billing & Metering, REST API v1, Webhooks, Notifikasi WhatsApp, dan Audit Trail & IP Whitelist. | ✅ Complete |
| **Phase 6: Relations & Engagement** | Hubungan Industrial & Surat Peringatan (SP1, SP2, SP3, masa berlaku 6 bulan Pasal 161 UU Ketenagakerjaan), Whistleblowing & Aduan Anonim, Papan Pengumuman Internal & Pinning, dan HR Helpdesk Service Request (Paklaring & Surat Keterangan). | ✅ Complete |
| **Phase 7: Transitions & Notifications** | Pergerakan Karir (Mutasi, Promosi, Demosi, SK Direksi), Kompensasi PHK & Pesangon (PP 35/2021 & UU Cipta Kerja: UP, UPMK, UPH, Uang Pisah), In-App Notification Center & Unread Badges. | ✅ Complete |
| **Phase 8: Financial Well-being & Engagement** | Fasilitas Pinjaman Karyawan & Kasbon Darurat (Bunga 0%, Angsuran Payroll), Survei Kepuasan Kerja & Metrik eNPS, Wall of Fame (Kudos), dan Kalender Agenda Acara Perusahaan. | ✅ Complete |
| **Phase 9: Competency, Succession & Cron** | Kamus Kompetensi & Standar Jabatan (Level 1-5), Matriks Kesenjangan (Skill Gap Analysis), Rencana Suksesi Kepemimpinan (Ready Now / 1 Yr / 2+ Yrs), dan Background Cron Workers Kepatuhan Ketenagakerjaan (PP 35/2021 PKWT H-30, SP 6 Bulan Pasal 161, Probasi 3 Bulan Pasal 60). | ✅ Complete |
| **Phase 10: Appraisals, Lifecycle & Bank Disbursal** | Evaluasi Kinerja 360° Multi-Penilai & Bimbingan PIP, Profil Mandiri Karyawan Lengkap (Keluarga, Pendidikan, Pengalaman Kerja, Kontak Darurat, Supervisor Atasan), Generator Mass Disbursal Perbankan Nasional (BCA KlikBCA, Mandiri MCM, BNI Direct, BRI CMS), Audit Kepatuhan Upah Minimum Regional (UMK PP 51/2023), Berkas Statutori BPJS SIPP Online & e-Dabu, serta Kios Presensi QR Touchless Geofencing. | ✅ Complete |
| **Phase 11: Wage Scale, SPPD, Clearance & e-Bupot** | Struktur & Skala Upah Permenaker 1/2017 & Compa-Ratio Audit, SPPD Perjalanan Dinas & Rekonsiliasi Settlement Uang Muka Kasbon, Matriks Offboarding Clearance 4 Pilar & Surat Paklaring Resmi (KUHPerdata Pasal 1603p), Generator Bukti Potong 1721-A1 & Ekspor Masal CSV e-Bupot 21/26 (PER-2/PJ/2024), serta Bagan Struktur Organisasi Interaktif Rekursif. | ✅ Complete |
| **Phase 12: Digital Contracts, Probation, Midtrans & L&D** | Perjanjian Kerja Digital & Tanda Tangan Elektronik Bersertifikasi (UU ITE No. 1/2024 & PP 71/2019, Audit Trail IP & Cryptographic SHA256 Integrity Seal), Evaluasi Masa Percobaan 3 Bulan Berbobot Komposit & Konversi Otomatis SK Direksi PKWTT (Pasal 60 UU No. 13/2003 & PP 35/2021), SaaS Payment Gateway (Midtrans Snap Checkout & SHA-512 Secure Webhooks), Evaluasi Pelatihan Model Kirkpatrick (Level 1 Reaksi, Level 2 Pre/Post-Test Gain %, Level 3 Perilaku, Level 4 Hasil) & E-Sertifikat Digital Terverifikasi, serta Flexible Working Arrangement (FWA WFH/WFO Scheduler & Attendance Policy Rule Validator). | ✅ Complete |

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
   - **JP (Jaminan Pensiun)**: Pemberi Kerja 2.00%, Pekerja 1.00% dengan batas atas upah (cap) resmi Rp 10.042.300.
   - **JKP (Jaminan Kehilangan Pekerjaan)**.
3. **BPJS Kesehatan**:
   - Iuran total 5% (Pemberi Kerja 4.00%, Pekerja 1.00%) dengan batas atas upah Rp 12.000.000.
4. **Perhitungan Lembur (PP No. 35 Tahun 2021)**:
   - Upah sejam lembur dihitung berdasarkan rumus resmi: $\frac{1}{173} \times \text{Upah Sebulan}$.
   - Hari kerja biasa: Jam ke-1 x 1.5, jam ke-2 dst x 2.0.
   - Hari istirahat/libur resmi: Perhitungan bertingkat hingga x 4.0.
5. **Uang Kompensasi PKWT (Pasal 61A UU Cipta Kerja & PP 35/2021)**:
   - Kalkulasi otomatis saat kontrak berakhir atau diperpanjang: $\frac{\text{Masa Kerja (Bulan)}}{12} \times 1\text{ Bulan Upah}$.
6. **Tunjangan Hari Raya (THR) Keagamaan (Permenaker No. 6/2016)**:
   - Masa kerja $\ge 12$ bulan = 1 bulan upah penuh; 1 s.d. < 12 bulan dihitung pro-rata.
7. **Surat Peringatan & Disiplin Kerja (Pasal 161 UU Ketenagakerjaan & PP 35/2021)**:
   - SP 1, SP 2, SP 3 dengan masa berlaku otomatis 6 bulan, penomoran terstandar, dan perlindungan aduan anonim (zero-retaliation).
8. **Kompensasi PHK & Pesangon (PP No. 35/2021 Pasal 40-52 & Pasal 156 UU Cipta Kerja)**:
   - Otomatisasi Uang Pesangon (UP 1-9 bulan upah), Uang Penghargaan Masa Kerja (UPMK 2-10 bulan upah), Uang Penggantian Hak (UPH sisa cuti dll), dan faktor pengali alasan PHK (0.0x s.d. 2.0x).
9. **Fasilitas Pinjaman Karyawan & Pemotongan Upah (PP 36/2021 tentang Pengupahan)**:
   - Batasan pemotongan upah berkala untuk cicilan pinjaman karyawan secara tertib dan transparan tanpa melanggar ketentuan batas upah minimum.

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
- **Komponen**: Lucide SVG Icons, Alpine.js interactive toggles & sliders, Chart.js graphs.

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

Setelah menjalankan `php artisan db:seed`, akun berikut siap digunakan untuk demonstrasi:

### A. Super Administrator Platform
- **URL Login**: [http://localhost:8080/login](http://localhost:8080/login)
- **Email**: `superadmin@rihris.id`
- **Password**: `Password123#`
- **Akses**: Pengelolaan seluruh tenant, penangguhan (suspend/activate), manajemen CMS landing page, dan audit logs platform.

### B. Demo Perusahaan (PT Solusi Digital Nusantara)
- **Company Owner**:
  - **Email**: `owner@solusidigital.id`
  - **Password**: `Password123#`
  - **Akses**: Seluruh fitur HR, Payroll approval, billing invoice, manajemen KPI, dan eksekutif analitik.
- **HR Manager**:
  - **Email**: `hr@solusidigital.id`
  - **Password**: `Password123#`
  - **Akses**: Operasional karyawan, absensi, cuti, rekrutmen, onboarding aset, penggajian, dan integrasi API.
- **Employee (Budi Santoso - Lead Developer)**:
  - **Email**: `budi@solusidigital.id`
  - **Password**: `Password123#`
  - **Akses**: Portal Employee Self-Service (ESS), clock-in/out GPS simulasi, pengajuan cuti, klaim reimbursement, evaluasi mandiri KPI, dan cek slip gaji.

---

## 🧪 MENJALANKAN AUTOMATED TESTS

Untuk menjalankan seluruh suite pengujian otomatis:

```bash
docker compose exec app php artisan test
```

### Cakupan Pengujian:
1. `ComplianceCalculationTest`: Verifikasi akurasi matematis PPh 21 TER (Kategori A, B, C), batas atas upah BPJS Kesehatan & Ketenagakerjaan, lembur 1/173 PP 35/2021, dan kompensasi PKWT.
2. `MultiTenancyIsolationTest`: Verifikasi pemisahan data antar-perusahaan via `TenantScope`.
3. `AuthenticationAndRegistrationTest`: Verifikasi auto-provisioning SaaS multi-tenant saat registrasi.
4. `LandingPageCmsTest`: Verifikasi rendering dinamis CMS landing page dan form konsultasi.
5. `CoreHrPhase2Test`: Pengujian CRUD karyawan, shift jadwal, koreksi presensi geofencing, dan approval cuti.
6. `PayrollAndTaxPhase3Test`: Pengujian payroll run, THP, rekonsiliasi akhir tahun 1721-A1, THR Permenaker 6/2016, dan reimbursement.
7. `TalentManagementPhase4Test`: Pengujian rekrutmen lowongan, ATS stage, konversi pelamar ke karyawan, onboarding checklist, inventaris aset kantor, review performa 30/70, dan sertifikat pelatihan.
8. `EnterprisePhase5Test`: Pengujian workflow approval berjenjang, metrik eksekutif analitik, billing invoice, REST API v1, webhook deliveries, dan IP whitelist korporat.
9. `EmployeeRelationsAndEngagementPhase6Test`: Pengujian surat peringatan (SP 1-3) berpenomoran resmi, masa berlaku 6 bulan Pasal 161 UU 13/2003, aduan whistleblowing anonim, dan HR helpdesk paklaring.
10. `CareerTransitionsAndSeverancePhase7Test`: Pengujian mutasi/promosi jabatan, kalkulasi kompensasi pesangon PHK PP 35/2021 Pasal 40-52 & Pasal 156 UU Cipta Kerja, dan notification bell real-time.
11. `FinancialWellbeingAndEngagementPhase8Test`: Pengujian pengajuan kasbon darurat, pembentukan jadwal angsuran payroll PP 36/2021, kalkulasi analitik eNPS (-100 s.d. +100), dan Wall of Recognition.
12. `SkillMatrixAndSuccessionPhase9Test`: Pengujian kamus keahlian 5 level kemahiran, matriks kesenjangan (skill gap), rekomendasi pelatihan LMS, suksesi posisi kunci Ready Now, dan 4 background worker cron scheduler (PP 35/2021 PKWT H-30, auto-expire SP 6 bulan Pasal 161, monitor probasi PKWTT 3 bulan Pasal 60).

---
*RiHRIS — Solusi HRIS SaaS Terpercaya untuk Bisnis Indonesia.*
