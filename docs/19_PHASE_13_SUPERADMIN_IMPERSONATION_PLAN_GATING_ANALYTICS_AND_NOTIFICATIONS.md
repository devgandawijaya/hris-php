# DOKUMEN IMPLEMENTASI PHASE 13: SUPER ADMIN PLATFORM MANAGEMENT & TENANT IMPERSONATION, SAAS PLAN FEATURE GATING, CMS LANDING CRM LEADS, ADVANCED HR ANALYTICS (TURNOVER & BRADFORD FACTOR), DAN MULTI-CHANNEL NOTIFICATION HUB (EMAIL & WHATSAPP)

**Platform:** RiHRIS — Multi-Company SaaS HRIS Indonesia  
**Modul:**  
1. Super Admin Tenant Impersonation & Platform Diagnostics (MRR/ARR Telemetry & PostgreSQL Health)  
2. SaaS Subscription Plan Feature Gating & Quota Middleware (Employee Headcount & Premium Flags)  
3. Inbound CMS Lead Capture & Contact Messages CRM (Pipeline: New, Contacted, Qualified, Converted)  
4. Advanced Executive HR Analytics Hub (Rumus Baku Turnover Rate, Indeks Bradford Factor Ketidakhadiran, Piramida Demografi, & Ekspor CSV)  
5. Enterprise Multi-Channel Notification Hub (WhatsApp & Email Template Engine & Live Dispatch Simulator)  
**Status Implementasi:** Production Ready (Completed)

---

## 1. DESKRIPSI & STANDAR ARSITEKTUR ENTERPRISE

Fase 13 menyempurnakan tata kelola platform multi-tenant, monetisasi SaaS berbasis kuota dan pembatasan fitur, visibilitas analitik eksekutif C-Level, serta otomasi komunikasi karyawan multi-kanal:

### 1.1 Super Admin Tenant Impersonation & Sesi Aman
- **Kemampuan Impersonasi Multi-Tenant**:
  - Super Administrator platform dapat masuk (*login-as*) ke perusahaan penyewa mana pun dengan satu klik untuk melakukan *troubleshooting*, audit data, atau konfigurasi awal tanpa meminta kata sandi pemilik perusahaan.
  - Sesi asli Super Admin dienkripsi dan disimpan dalam session (`impersonator_super_admin_id`).
  - Bar notifikasi mengambang (*sticky top bar*) berwarna kuning/amber tampil secara konsisten di seluruh aplikasi dengan tombol "Kembali ke Super Admin Portal" (`POST /super-admin/impersonate/leave`).
  - Jejak audit yuridis (*Audit Trail*) dicatat lengkap pada tabel `audit_logs` saat sesi dimulai dan saat sesi diakhiri.

### 1.2 SaaS Subscription Plan Feature Gating & Quota Enforcement
- **Pembatasan Berdasarkan Paket Langganan**:
  - **Kuota Karyawan (`max_employees`)**: Sistem memvalidasi jumlah karyawan aktif sebelum mengizinkan penambahan karyawan baru (`canAddEmployee`). Jika kuota terlampaui, sistem menolak pembuatan karyawan baru dan menampilkan alert upgrade paket.
  - **Flag Fitur Premium**:
    - `has_api_access`: Akses REST API v1 eksternal.
    - `has_whatsapp`: Notifikasi otomatis WhatsApp.
    - `has_custom_workflows`: Mesin workflow persetujuan multi-level.
    - `has_bank_disbursal`: Generator berkas transfer massal bank nasional (BCA, Mandiri, BNI, BRI).
    - `has_ebupot_export`: Generator bukti potong 1721-A1 & ekspor massal CSV e-Bupot 21/26.
    - `has_digital_contracts`: Penandatanganan digital berintegritas UU ITE & segel SHA256.
  - **Middleware `EnsurePlanFeatureAccess`**: Menolak akses route jika tenant tidak memiliki hak akses fitur pada paket langganannya.

### 1.3 CMS Inbound Leads CRM
- **Pipeline Manajemen Prospek Calon Tenant**:
  - Menangkap data calon pelanggan dari formulir kontak publik landing page (`cms_contact_messages`).
  - Mengelola siklus konversi prospek: `new` $\rightarrow$ `contacted` $\rightarrow$ `qualified` $\rightarrow$ `converted` (menjadi tenant) / `closed`.
  - Perekaman catatan interaksi (*follow-up notes*), timestamp, dan identitas Super Admin penanggung jawab.

### 1.4 Advanced Executive HR Analytics Hub
- **Rumus Baku Turnover / Attrition Rate Karyawan**:
  $$\text{Turnover Rate (\%)} = \frac{\text{Jumlah Karyawan Resign / PHK dalam Periode}}{\frac{\text{Headcount Awal Tahun} + \text{Headcount Akhir Tahun}}{2}} \times 100\%$$
  $$\text{Retention Rate (\%)} = 100\% - \text{Turnover Rate (\%)} $$
- **Indeks Bradford Factor untuk Analisis Ketidakhadiran Sporadis**:
  $$B = S^2 \times D$$
  - Di mana $S$ = Jumlah kejadian/periode (*spells of absence*), dan $D$ = Total hari ketidakhadiran (*total days absent*).
  - Mengidentifikasi absensi singkat berulang yang lebih merusak produktivitas operasional dibandingkan satu periode sakit berkepanjangan:
    - Skor $< 50$: Risiko Rendah (Normal).
    - Skor $50 - 199$: Risiko Sedang (Pola ketidakhadiran terdeteksi).
    - Skor $200 - 499$: Risiko Tinggi (Perlu pembinaan supervisor).
    - Skor $\ge 500$: Risiko Kritis (Pemeriksaan disiplin & SP).
- **Distribusi Demografis & Rasio Biaya Tenaga Kerja (Labor Cost)**:
  - Visualisasi gender, rentang usia, masa kerja (*tenure*), dan tipe perjanjian kerja.
  - Komparasi total kompensasi (upah bruto, BPJS perusahaan, lembur, dan pajak).
  - Ekspor laporan eksekutif lengkap ke format streaming CSV.

### 1.5 Enterprise Multi-Channel Notification Hub (WhatsApp & Email)
- **Mesin Naskah Template Dinamis**:
  - Pengelolaan naskah pesan dengan placeholder variabel: `{employee_name}`, `{company_name}`, `{department}`, `{title}`, `{amount}`, `{event_date}`.
  - Dukungan dua kanal utama: WhatsApp Text via gateway API dan Email surat resmi via SMTP.
  - Simulator pengujian transmisi (*live dispatcher*) untuk menguji keterbacaan pesan ke nomor/email pegawai tertentu sebelum diterapkan secara otomatis.
  - Log audit pengiriman lengkap (`notification_dispatch_logs`) dengan status pengiriman dan timestamp.

---

## 2. ARSITEKTUR STRUKTUR DATABASE

Migration: `database/migrations/2026_01_13_000001_create_phase13_super_admin_gating_analytics_and_channels_tables.php`

1. **`subscription_plans` (Kolom Tambahan Feature Flags)**:
   - `has_api_access`: Boolean flag hak akses API v1.
   - `has_whatsapp`: Boolean flag notifikasi WhatsApp.
   - `has_custom_workflows`: Boolean flag kustomisasi alur persetujuan.
   - `has_bank_disbursal`: Boolean flag ekspor mass disbursal perbankan.
   - `has_ebupot_export`: Boolean flag ekspor statutori e-Bupot CSV.
   - `has_digital_contracts`: Boolean flag penandatanganan elektronik UU ITE.

2. **`cms_contact_messages` (Kolom Tambahan CRM Leads)**:
   - `status`: Status tindak lanjut (`new`, `contacted`, `qualified`, `converted`, `closed`).
   - `notes`: Catatan negosiasi / follow-up internal Super Admin.
   - `contacted_by_user_id`: Foreign key user admin penanggung jawab.
   - `contacted_at`: Timestamp saat pertama kali dihubungi.

3. **`notification_templates`**:
   - `id`, `tenant_id` (nullable untuk template bawaan sistem), `code`, `name`.
   - `channel`: Kanal (`whatsapp`, `email`).
   - `category`: Kategori (`contract`, `payroll`, `attendance`, `disciplinary`, `general`).
   - `subject`: Subjek email (nullable).
   - `body_template`: Teks naskah dengan variabel placeholder `{variabel}`.
   - `variables`: JSON daftar placeholder yang didukung.
   - `is_active`: Status keaktifan template.

4. **`notification_dispatch_logs`**:
   - `id`, `tenant_id`, `template_id`.
   - `channel`, `recipient_name`, `recipient_target` (nomor ponsel atau alamat email).
   - `subject`, `message_preview`, `status` (`sent`, `pending`, `failed`, `simulated`).
   - `sent_at`, `error_message`, `dispatched_by_user_id`.

---

## 3. LAPISAN SERVICE & CONTROLLER

1. **`App\Services\SuperAdmin\ImpersonationService`**:
   - `impersonateTenant(Tenant $tenant, User $superAdmin): User`: Mencari owner/admin tenant, menyimpan ID super admin pada sesi terenkripsi, mencatat `AuditLog`, dan melakukan `Auth::login()`.
   - `leaveImpersonation(): ?User`: Mengembalikan otentikasi ke akun Super Admin asli, membersihkan sesi, dan mencatat `AuditLog`.
   - `isImpersonating(): bool`: Pemeriksaan status impersonasi sesi aktif.

2. **`App\Services\SuperAdmin\PlatformHealthService`**:
   - `getFinancialMetrics(): array`: Perhitungan MRR, ARR ($MRR \times 12$), ARPU, dan total akumulasi faktur lunas/tertunda.
   - `getSystemHealth(): array`: Diagnostik versi PHP, Laravel, status koneksi database, ukuran tabel utama, dan penggunaan memori.

3. **`App\Services\Subscription\PlanGatingService`**:
   - `getEmployeeUsage(Tenant $tenant): array`: Rasio pemakaian kuota karyawan (`current`, `max`, `percentage`, `is_exceeded`).
   - `canAddEmployee(Tenant $tenant): bool`: Validasi apakah tenant diizinkan menambah karyawan baru.
   - `hasFeature(Tenant $tenant, string $featureKey): bool`: Validasi flag akses modul per paket.

4. **`App\Services\Analytics\AdvancedHrAnalyticsService`**:
   - `getTurnoverMetrics(Company, ?int $year): array`: Kalkulasi formula turnover rate & retention rate.
   - `getBradfordFactorMetrics(Company, ?int $year): array`: Kalkulasi indeks Bradford $B = S^2 \times D$ dan kategorisasi risiko.
   - `getDemographics(Company): array`: Distribusi gender, usia, masa kerja, dan status kontrak.
   - `getLaborCostMetrics(Company, ?int $year): array`: Komparasi biaya upah dan tunjangan perusahaan.
   - `exportAdvancedMetricsCsv(Company, ?int $year): string`: Generator ekspor CSV streaming laporan eksekutif.

5. **`App\Services\Notification\MultiChannelNotificationService`**:
   - `render(NotificationTemplate, Employee, array $params): string`: Penggantian placeholder variabel secara dinamis.
   - `dispatch(NotificationTemplate, Employee, array $params, ?User $sender): NotificationDispatchLog`: Eksekusi pengiriman dan pencatatan log audit.
   - `simulateDispatch(NotificationTemplate, Employee, array $params): array`: Pratinjau pesan tanpa mencatat log.

6. **Controllers**:
   - `App\Http\Controllers\SuperAdmin\ImpersonationController`: Manajemen mulai dan selesai impersonasi.
   - `App\Http\Controllers\SuperAdmin\PlatformHealthController`: Tampilan diagnostik server & metrik keuangan SaaS.
   - `App\Http\Controllers\SuperAdmin\SubscriptionPlanController`: Manajemen paket langganan dan batasan fitur.
   - `App\Http\Controllers\SuperAdmin\CmsLeadController`: Pipeline CRM prospek formulir kontak landing page.
   - `App\Http\Controllers\Company\AdvancedHrAnalyticsController`: Dasbor analitik turnover, Bradford, dan ekspor CSV.
   - `App\Http\Controllers\Company\NotificationHubController`: Hub template pesan multi-channel & simulator pengujian.

---

## 4. BLADE VIEWS & USER INTERFACE

1. `resources/views/super-admin/health/index.blade.php`: Dasbor telemetri SaaS dengan kartu metrik MRR, ARR, ARPU, grafik distribusi paket langganan, dan diagnostik server.
2. `resources/views/super-admin/plans/index.blade.php`: Manajemen paket langganan dengan kartu harga bulanan/tahunan, kuota karyawan maksimal, kapasitas storage, dan flag aktivasi fitur.
3. `resources/views/super-admin/cms/leads.blade.php`: Dasbor CRM prospek formulir kontak landing page dengan filter status, metrik konversi, dan modal pembaruan catatan follow-up.
4. `resources/views/super-admin/companies/show.blade.php`: Tombol aksi "Login Sebagai Admin (Impersonate)" dan formulir perubahan paket langganan perusahaan.
5. `resources/views/company/analytics/advanced.blade.php`: Dasbor eksekutif C-Level dengan indikator Turnover Rate tahunan, tabel Bradford Factor absensi sporadis, piramida demografi, dan tombol unduh CSV.
6. `resources/views/company/notifications/hub.blade.php`: Hub notifikasi WhatsApp & Email dengan katalog template naskah, modal pengujian langsung ke nomor pegawai, dan tabel riwayat pengiriman.
7. `resources/views/layouts/app.blade.php`: Bar peringatan mengambang (*sticky top banner*) mode impersonasi aktif dan pembaruan tautan navigasi Super Admin serta Perusahaan.

---

## 5. PENGUJIAN OTOMATIS (AUTOMATED FEATURE TESTS)

Berkas Test: `tests/Feature/SuperAdminPlatformAndAdvancedAnalyticsPhase13Test.php`  
Terdiri dari 11 skenario pengujian komprehensif:

1. `test_super_admin_can_impersonate_tenant_owner_and_sets_audit_trail`: Verifikasi inisiasi sesi impersonasi Super Admin ke akun owner tenant dan pencatatan audit log `superadmin.impersonate_start`.
2. `test_impersonated_user_can_leave_and_reverts_to_super_admin`: Verifikasi pemulihan sesi Super Admin asli, penghapusan token sesi impersonasi, dan pencatatan audit log `superadmin.impersonate_leave`.
3. `test_plan_gating_service_enforces_max_employees_quota`: Pengujian batasan kuota karyawan maksimal dan pemblokiran penambahan karyawan saat kuota paket tercapai.
4. `test_plan_gating_service_feature_matrix_and_flags`: Pengujian validasi flag fitur paket (REST API, WhatsApp, Custom Workflows, dll).
5. `test_super_admin_can_change_tenant_subscription_plan`: Pengujian pengubahan paket langganan tenant dan siklus penagihan oleh Super Admin.
6. `test_platform_health_service_computes_accurate_mrr_arr_and_system_stats`: Pengujian akurasi matematis perhitungan MRR, ARR, dan diagnostik database PostgreSQL.
7. `test_super_admin_cms_lead_management_updates_crm_status_and_notes`: Pengujian pembaruan status prospek lead landing page (`qualified`) dan pencatatan admin reviewer.
8. `test_advanced_hr_analytics_turnover_rate_equation`: Pengujian formula matematis baku turnover rate dan retention rate karyawan.
9. `test_advanced_hr_analytics_bradford_factor_absenteeism_index`: Pengujian kalkulasi indeks Bradford ($B = S^2 \times D$) pada multiple absensi non-kontinu.
10. `test_multi_channel_notification_service_dispatches_templated_messages`: Pengujian render variabel dinamis template dan pencatatan log transmisi notifikasi multi-kanal.
11. `test_advanced_hr_analytics_csv_export_streams_valid_content`: Pengujian streaming ekspor berkas CSV laporan eksekutif analitik SDM.
