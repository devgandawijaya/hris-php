# DOKUMEN IMPLEMENTASI PHASE 15: TALENT RETENTION, INTERNAL JOB POSTING (IJP), ONBOARDING MENTORSHIP & BUDDY, EXIT ASSET CLEARANCE (BAST & REVOKASI SSO), DAN AUDIT STATUTORI KETENAGAKERJAAN (WLTK UU NO. 7/1981 & KUOTA DISABILITAS UU NO. 8/2016)

**Platform:** RiHRIS — Multi-Company SaaS HRIS Platform Indonesia  
**Modul:**  
1. Wajib Lapor Ketenagakerjaan Perusahaan (WLTK / WLKP UU No. 7/1981 jo. Permenaker No. 18/2017) & Generator Berkas Ekspor Daring Kemnaker  
2. Internal Job Posting (IJP) & Talent Mobility Marketplace (Gating Tenure, Performa & Persetujuan Pelepasan Manajer)  
3. Onboarding Buddy & Program Mentorship 90 Hari (Milestone Check-in Day 30, Day 60, Day 90 & Evaluasi 2-Arah)  
4. Exit Asset Clearance Handover & BAST Pengembalian Aset Terotomasi dengan De-provisioning Akses SSO (Google Workspace, Slack, VPN, HRIS)  
5. Audit Kepatuhan Kuota Tenaga Kerja Penyandang Disabilitas (Pasal 53 UU No. 8/2016) & Inventarisasi Akomodasi yang Layak (Reasonable Accommodation)  
**Status Implementasi:** Production Ready (Completed)

---

## 1. DESKRIPSI & LANDASAN HUKUM INDONESIA

Fase 15 melengkapi pilar retensi talenta internal, akselerasi adaptasi karyawan baru, pengamanan inventaris fisik & digital saat offboarding, serta kepatuhan statutori terhadap pelaporan ketenagakerjaan tahunan dan kuota inklusi disabilitas:

### 1.1 Wajib Lapor Ketenagakerjaan Perusahaan (UU No. 7/1981 & Permenaker No. 18/2017)
- **Landasan Hukum**:
  - **UU No. 7 Tahun 1981** tentang Wajib Lapor Ketenagakerjaan di Perusahaan.
  - **Permenaker No. 18 Tahun 2017** tentang Tata Cara Pelaporan Ketenagakerjaan Secara Elektronik (*WLKP Online*).
- **Kewajiban Yuridis**:
  - Pengusaha atau pengurus wajib melaporkan keadaan ketenagakerjaan secara berkala **setiap 1 (satu) tahun sekali** kepada Menteri Ketenagakerjaan atau pejabat yang ditunjuk (Pasal 6 ayat 1 UU 7/1981).
  - Masa berlaku pelaporan tahunan adalah **tepat 1 (satu) tahun** kalender sejak tanggal pengesahan tanda terima Disnaker.
  - Komponen data yang wajib dilaporkan:
    1. Identitas Badan Usaha (Nama PT, NIB, NPWP, Alamat, KBLI).
    2. Komposisi Tenaga Kerja (Pekerja WNI Pria/Wanita, Tenaga Kerja Asing/TKA Pria/Wanita, Tenaga Kerja Penyandang Disabilitas).
    3. Hubungan Kerja (PKWTT Tetap vs PKWT Kontrak Waktu Tertentu).
    4. Struktur Pengupahan (Upah Pokok Terendah sesuai UMK/UMP dan Upah Pokok Tertinggi).
    5. Jaminan Sosial & Kelembagaan Hubungan Industrial (Kepesertaan BPJS Ketenagakerjaan, BPJS Kesehatan, Peraturan Perusahaan/PKB, dan LKS Bipartit).
- **Fitur Otomasi Sistem**:
  - Generator draft agregasi real-time dari database karyawan aktif perusahaan.
  - Ekspor format JSON resmi WLKP Kemnaker (`wlkp.kemnaker.go.id`) dan format CSV inspeksi Disnaker.
  - Pemantauan otomatis masa berlaku laporan dengan alert dini H-60 dan H-30 sebelum jatuh tempo.

### 1.2 Internal Job Posting (IJP) & Talent Mobility Marketplace
- **Tujuan Bisnis**:
  - Meningkatkan retensi talenta terbaik (*Talent Retention*) dengan memberikan peluang mobilitas karir horizontal (rotasi) dan vertikal (promosi) di dalam grup/perusahaan.
- **Aturan Gating & Validasi Otomatis**:
  - **Syarat Masa Kerja (Tenure Requirement)**: Karyawan wajib memiliki masa kerja minimum di posisi saat ini (standar 12 bulan) sebelum dapat mengajukan lamaran internal.
  - **Syarat Kedisiplinan**: Karyawan tidak sedang menjalani sanksi disiplin aktif (wajib bebas Surat Peringatan / SP1-SP3).
  - **Syarat Nilai Kinerja**: Karyawan wajib memiliki skor evaluasi kinerja terakhir yang memenuhi passing grade lowongan (standar rating $\ge 3.00$).
- **Alur Persetujuan 3-Tahap**:
  1. *Origin Manager Release Approval*: Persetujuan pelepasan dari Manajer Departemen saat ini.
  2. *Hiring Manager & HR Interview*: Penilaian kecakapan teknis dan wawancara kecocokan budaya kerja.
  3. *Final Decision & Transfer Date*: Penetapan tanggal efektif mutasi/promosi internal.

### 1.3 Onboarding Buddy & Program Mentorship 90 Hari
- **Tujuan Program**:
  - Menurunkan *early turnover rate* (pengunduran diri selama masa probation) dengan memasangkan karyawan baru (*Mentee*) dengan Senior Buddy (*Mentor*) terverifikasi sejak hari pertama masuk kerja.
- **Milestone Check-in Terstruktur**:
  - **Day 30**: Pengenalan budaya tim, instalasi tools kerja, dan orientasi proses bisnis inti.
  - **Day 60**: Evaluasi kemandirian tugas, pemecahan hambatan teknis, dan integrasi lintas departemen.
  - **Day 90**: Refleksi masa probation, kesiapan penilaian konversi SK PKWTT tetap, dan rencana pengembangan karir lanjutan.
- **Evaluasi Dua Arah (2-Way Feedback)**:
  - Skor kepuasan mentee (Skala 1-5) dan masukan mentoring.
  - Skor evaluasi adaptabilitas dan *cultural fit* dari Senior Buddy (Skala 1-5).
  - Penyelesaian otomatis status program setelah ketiga milestone terpenuhi.

### 1.4 Exit Asset Handover BAST & Automated SSO Revocation
- **Tujuan Operasional & Keamanan Informasi**:
  - Memastikan seluruh inventaris kantor fisik (laptop, smartphone, kartu akses gedung, kunci, kendaraan) diserahkan secara tertib saat offboarding karyawan.
  - Menerbitkan **Berita Acara Serah Terima (BAST) Pengembalian Aset** bernomor seri resmi (`BAST-ASSET-YYYY-XXXX`) dengan tanda tangan digital karyawan dan petugas IT/HR.
  - Perhitungan otomatis kewajiban ganti rugi jika aset dikembalikan dalam kondisi rusak berat atau hilang (*lost*).
  - **Pencabutan Hak Akses Otomatis (Automated SSO & IT De-provisioning)**: Saat BAST ditandatangani, sistem secara otomatis mencatat dan memicu pemutusan akses terhadap Google Workspace / Email Perusahaan, Slack, VPN WireGuard/RADIUS, Microsoft 365, dan portal HRIS.

### 1.5 Audit Kuota Ketenagakerjaan Disabilitas (UU No. 8/2016 Pasal 53)
- **Landasan Hukum**:
  - **UU No. 8 Tahun 2016 tentang Penyandang Disabilitas (Pasal 53 ayat 1 & 2)**:
    - *Ayat (1)*: Pemerintah, Pemda, BUMN, dan BUMD wajib mempekerjakan paling sedikit **2% (dua persen)** Penyandang Disabilitas dari total jumlah pekerja.
    - *Ayat (2)*: Perusahaan swasta wajib mempekerjakan paling sedikit **1% (satu persen)** Penyandang Disabilitas dari total jumlah pekerja.
  - **PP No. 60 Tahun 2020** tentang Unit Layanan Disabilitas Ketenagakerjaan.
- **Formula Audit Kepatuhan**:
  $$\text{Target Minimal Kuota} = \left\lceil \frac{\text{Total Tenaga Kerja Aktif} \times \text{Kuota Wajib Sah}}{100} \right\rceil$$
  $$\text{Rasio Aktual} = \left( \frac{\text{Pekerja Disabilitas Terdaftar}}{\text{Total Tenaga Kerja Aktif}} \right) \times 100\%$$
  $$\text{Shortfall (Kekurangan)} = \max(0, \text{Target Minimal Kuota} - \text{Pekerja Disabilitas})$$
- **Inventarisasi Akomodasi yang Layak (Reasonable Accommodation)**:
  - Perekaman ragam disabilitas: Fisik (Daksa), Sensorik (Netra/Rungu/Wicara), Intelektual, Mental/Psikososial, dan Ganda.
  - Perekaman fasilitas aksesibilitas tempat kerja: Ramp kursi roda, toilet difabel, software screen reader, meja ergonomis, dan rencana pendampingan evakuasi darurat K3.
  - Generator Surat Keterangan / Laporan Kepatuhan Kuota Disabilitas untuk inspeksi pengawas Disnaker.

---

## 2. STRUKTUR DATABASE & MODEL ELOQUENT

### 2.1 File Migrasi
- [`database/migrations/2026_01_15_000001_create_phase15_wltk_ijp_mentorship_and_accessibility_tables.php`](file:///Users/legacygandawijaya/Documents/hris-php/database/migrations/2026_01_15_000001_create_phase15_wltk_ijp_mentorship_and_accessibility_tables.php)

### 2.2 Tabel-Tabel Baru & Kolom
1. **`employees` (Alteration)**:
   - `citizenship`: varchar(10) default 'WNI' (`WNI`, `WNA`).
   - `has_disability`: boolean default false.
2. **`wltk_reports`**:
   - `id`, `tenant_id`, `company_id`, `report_number`, `reporting_year`, `report_period`, `status`, `submission_date`, `valid_until`, `company_registration_number`, `kbli_code`, `total_workers`, `total_wni_male`, `total_wni_female`, `total_wna_male`, `total_wna_female`, `total_pkwtt`, `total_pkwt`, `total_disability_workers`, `lowest_wage`, `highest_wage`, `bpjs_tk_registered`, `bpjs_kes_registered`, `has_pp_or_pkb`, `has_lks_bipartit`, `raw_payload_json`, `disnaker_receipt_number`, `notes`, `timestamps`.
3. **`ijp_vacancies`**:
   - `id`, `tenant_id`, `company_id`, `department_id`, `position_id`, `vacancy_code`, `title`, `description`, `requirements`, `min_tenure_months`, `min_performance_score`, `open_slots`, `application_deadline`, `status`, `created_by_user_id`, `timestamps`.
4. **`ijp_applications`**:
   - `id`, `tenant_id`, `ijp_vacancy_id`, `employee_id`, `current_department_id`, `current_position_id`, `application_date`, `motivation_letter`, `portfolio_url`, `current_tenure_months`, `latest_performance_score`, `has_active_warning`, `is_eligible`, `ineligibility_reason`, `manager_approval_status`, `manager_approval_notes`, `manager_approved_at`, `hr_interview_status`, `hr_interview_notes`, `final_decision`, `transfer_effective_date`, `timestamps`.
5. **`mentorship_pairs`**:
   - `id`, `tenant_id`, `company_id`, `mentee_employee_id`, `mentor_employee_id`, `start_date`, `end_date`, `status`, `program_objectives`, `timestamps`.
6. **`mentorship_checkins`**:
   - `id`, `tenant_id`, `mentorship_pair_id`, `milestone` (`day_30`, `day_60`, `day_90`), `scheduled_date`, `completed_date`, `status`, `agenda_topics`, `mentee_satisfaction_score`, `mentee_feedback`, `mentor_adaptation_score`, `mentor_notes`, `action_plan`, `timestamps`.
7. **`asset_handover_basts`**:
   - `id`, `tenant_id`, `company_id`, `bast_number`, `employee_id`, `offboarding_clearance_id`, `handover_date`, `status`, `officer_name`, `officer_position`, `employee_signature_token`, `officer_signature_token`, `signed_at`, `total_compensation_due`, `notes`, `timestamps`.
8. **`asset_handover_items`**:
   - `id`, `asset_handover_bast_id`, `company_asset_id`, `asset_name`, `serial_number`, `condition_on_return` (`good`, `minor_damage`, `severe_damage`, `lost`), `compensation_amount`, `notes`, `timestamps`.
9. **`system_access_revocation_logs`**:
   - `id`, `tenant_id`, `company_id`, `employee_id`, `asset_handover_bast_id`, `system_name`, `account_identifier`, `revocation_status`, `revoked_at`, `revoked_by_user_id`, `api_response_log`, `timestamps`.
10. **`disability_employment_audits`**:
    - `id`, `tenant_id`, `company_id`, `audit_code`, `audit_year`, `audit_date`, `entity_type`, `total_active_workforce`, `disability_workforce_count`, `statutory_quota_percentage`, `actual_quota_percentage`, `statutory_headcount_required`, `headcount_shortfall`, `compliance_status`, `physical_accessibility_score`, `digital_accessibility_score`, `reasonable_accommodations_summary`, `recommendations`, `audited_by_user_id`, `timestamps`.
11. **`employee_disability_profiles`**:
    - `id`, `tenant_id`, `company_id`, `employee_id`, `disability_category`, `medical_certificate_number`, `accommodations_provided`, `accessibility_needs`, `emergency_evacuation_assistance_plan`, `timestamps`.

### 2.3 Model Eloquent
- [`app/Models/WltkReport.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/WltkReport.php)
- [`app/Models/IjpVacancy.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/IjpVacancy.php)
- [`app/Models/IjpApplication.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/IjpApplication.php)
- [`app/Models/MentorshipPair.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/MentorshipPair.php)
- [`app/Models/MentorshipCheckin.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/MentorshipCheckin.php)
- [`app/Models/AssetHandoverBast.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/AssetHandoverBast.php)
- [`app/Models/AssetHandoverItem.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/AssetHandoverItem.php)
- [`app/Models/SystemAccessRevocationLog.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/SystemAccessRevocationLog.php)
- [`app/Models/DisabilityEmploymentAudit.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/DisabilityEmploymentAudit.php)
- [`app/Models/EmployeeDisabilityProfile.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/EmployeeDisabilityProfile.php)

---

## 3. ARSITEKTUR SERVICE & LOGIKA BISNIS

### 3.1 [`WltkComplianceService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Compliance/WltkComplianceService.php)
- `generateDraftReport(Company $company, int $year, string $period = 'annual'): WltkReport`: Menghitung total tenaga kerja aktif, segregasi WNI/WNA, status PKWTT/PKWT, tenaga kerja disabilitas, upah terendah/tertinggi, memeriksa kelengkapan BPJS, PP, dan LKS Bipartit, serta menghasilkan nomor laporan berseri unik `WLTK/{YEAR}/{INCREMENT}`.
- `markSubmitted(WltkReport $report, string $receiptNumber, ?string $submissionDate = null): WltkReport`: Mengunci status laporan menjadi `submitted_kemnaker`, menyimpan nomor tanda terima Disnaker, dan mengaktifkan masa berlaku sah tepat 1 (satu) tahun ke depan.
- `exportKemnakerJson(WltkReport $report): array`: Menghasilkan struktur JSON standar yang siap diunggah ke portal resmi WLKP Kemnaker.
- `exportKemnakerCsv(WltkReport $report): string`: Menghasilkan berkas CSV terstruktur untuk inspeksi pengawas ketenagakerjaan Disnaker.
- `checkRenewalDueAlerts(int $tenantId, int $daysThreshold = 30): Collection`: Mengidentifikasi laporan tahunan yang akan kadaluarsa dalam 30-60 hari ke depan.

### 3.2 [`IjpMarketplaceService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Talent/IjpMarketplaceService.php)
- `createVacancy(array $data, int $userId): IjpVacancy`: Membuat lowongan internal baru bersandi `IJP-YYYY-XXXX` dengan parameter masa kerja minimum, batas nilai appraisal, dan kuota.
- `validateEligibility(Employee $employee, IjpVacancy $vacancy): array`: Melakukan tiga lapis validasi kelayakan pelamar: masa kerja saat ini $\ge$ kuota tenure, status sanksi Surat Peringatan (SP), dan skor review kinerja terakhir.
- `applyVacancy(Employee $employee, IjpVacancy $vacancy, array $data): IjpApplication`: Mencatat lamaran internal dan mencegah duplikasi aplikasi pada lowongan yang sama.
- `reviewManagerApproval(IjpApplication $application, bool $approved, ?string $notes = null): IjpApplication`: Mencatat rekomendasi persetujuan/pelepasan manajer departemen asal.
- `reviewHrDecision(IjpApplication $application, string $decision, ?string $effectiveDate = null, ?string $notes = null): IjpApplication`: Menetapkan hasil akhir interview HR (accepted, offered, rejected) beserta tanggal efektif mutasi.

### 3.3 [`MentorshipService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Talent/MentorshipService.php)
- `assignMentor(Employee $mentee, Employee $mentor, string $startDate, ?string $objectives = null): MentorshipPair`: Memasangkan karyawan baru dengan senior buddy untuk siklus 90 hari, dan secara otomatis menjadwalkan 3 milestone check-in (Hari ke-30, ke-60, dan ke-90).
- `submitCheckin(MentorshipCheckin $checkin, array $data): MentorshipCheckin`: Mencatat evaluasi dua arah (skor kepuasan mentee 1-5 dan skor adaptabilitas mentor 1-5); otomatis menyelesaikan status pasangan jika seluruh 3 milestone rampung.
- `getMentorshipSummary(int $tenantId): array`: Menghitung analitik pasangan aktif, rata-rata kepuasan bimbingan, dan persentase kelulusan program buddy.

### 3.4 [`ExitAssetClearanceService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Offboarding/ExitAssetClearanceService.php)
- `createBast(Employee $employee, array $itemsData, string $officerName, string $officerPosition, ?int $clearanceId = null): AssetHandoverBast`: Menerbitkan BAST berseri `BAST-ASSET-YYYY-XXXX`, menghitung akumulasi beban ganti rugi barang rusak/hilang.
- `signAndCompleteBast(AssetHandoverBast $bast, string $employeeSignature, string $officerSignature): AssetHandoverBast`: Mengesahkan BAST dengan tanda tangan digital, memperbarui status fisik inventaris (`CompanyAsset`) menjadi `available` atau `retired`, dan memicu revokasi akses digital otomatis.
- `triggerAutomatedAccessRevocation(AssetHandoverBast $bast): void`: Mencatat log de-provisioning otomatis pada seluruh sistem korporat (Google Workspace, Slack, VPN, M365, HRIS).
- `revokeSystemAccess(Employee $employee, string $systemName, ?User $revoker = null): SystemAccessRevocationLog`: Fasilitas revokasi akses per-sistem secara manual.

### 3.5 [`DisabilityComplianceAuditService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Compliance/DisabilityComplianceAuditService.php)
- `performAudit(Company $company, string $entityType = 'private_sector', ?int $auditedByUserId = null): DisabilityEmploymentAudit`: Menghitung total tenaga kerja aktif, membandingkan persentase pekerja disabilitas aktual terhadap kuota sah (1% swasta / 2% BUMN per Pasal 53 UU 8/2016), menghitung kekurangan headcount, dan menentukan status kepatuhan (*compliant*, *near_target*, *non_compliant*).
- `registerDisabilityProfile(Employee $employee, array $data): EmployeeDisabilityProfile`: Mencatat kategori ragam disabilitas, nomor surat keterangan medis, dan fasilitas akomodasi yang layak.
- `generateDisnakerComplianceReport(DisabilityEmploymentAudit $audit): array`: Menghasilkan laporan kepatuhan komprehensif untuk inspeksi Unit Layanan Disabilitas (ULD) Ketenagakerjaan.

---

## 4. CONTROLLER & VIEW BLADE

### 4.1 Controllers
1. [`WltkComplianceController`](file:///Users/legacygandawijaya/Documents/hris-php/app/Http/Controllers/Company/WltkComplianceController.php): `index`, `store`, `show`, `submitReceipt`, `exportJson`, `exportCsv`.
2. [`IjpMarketplaceController`](file:///Users/legacygandawijaya/Documents/hris-php/app/Http/Controllers/Company/IjpMarketplaceController.php): `index`, `storeVacancy`, `apply`, `approveManager`, `decideHr`.
3. [`MentorshipController`](file:///Users/legacygandawijaya/Documents/hris-php/app/Http/Controllers/Company/MentorshipController.php): `index`, `storePair`, `completeCheckin`.
4. [`AssetHandoverController`](file:///Users/legacygandawijaya/Documents/hris-php/app/Http/Controllers/Company/AssetHandoverController.php): `index`, `storeBast`, `show`, `signBast`, `printBast`, `revokeAccess`.
5. [`DisabilityAuditController`](file:///Users/legacygandawijaya/Documents/hris-php/app/Http/Controllers/Company/DisabilityAuditController.php): `index`, `runAudit`, `storeProfile`, `printReport`.

### 4.2 Views
- [`resources/views/company/wltk/index.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/wltk/index.blade.php): Dashboard laporan WLTK, alert jatuh tempo perpanjangan tahunan, modal generate berkas.
- [`resources/views/company/wltk/show.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/wltk/show.blade.php): Detail laporan WLTK lengkap, rincian demografi tenaga kerja, form kunci tanda terima Kemnaker, dan tombol ekspor.
- [`resources/views/company/ijp/index.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/ijp/index.blade.php): Marketplace lowongan internal, kartu lowongan aktif, form lamaran internal, dan alur approval manajer/HR.
- [`resources/views/company/mentorship/index.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/mentorship/index.blade.php): Metrik program buddy, tabel penugasan mentor, progress tracker 30-60-90 hari, dan modal submit evaluasi.
- [`resources/views/company/asset_handover/index.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/asset_handover/index.blade.php): Histori BAST, log revokasi SSO, modal pembuatan BAST dengan tabel inventaris dinamis.
- [`resources/views/company/asset_handover/show.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/asset_handover/show.blade.php): Rincian BAST, form tanda tangan digital, dan daftar sistem yang telah dicabut aksesnya.
- [`resources/views/company/asset_handover/print_bast.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/asset_handover/print_bast.blade.php): Format cetak resmi Berita Acara Serah Terima Aset dengan kop surat, tabel item, dan blok tanda tangan dua pihak.
- [`resources/views/company/disability_audit/index.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/disability_audit/index.blade.php): Metrik pemenuhan kuota 1%/2%, histori audit berkala, pendaftaran profil karyawan disabilitas, dan tabel fasilitas akomodasi.
- [`resources/views/company/disability_audit/print_report.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/disability_audit/print_report.blade.php): Surat Keterangan / Laporan Hasil Audit Ketenagakerjaan Inklusif siap cetak untuk inspeksi pengawas Disnaker.

---

## 5. PENGUJIAN OTOMATIS & INTEGRITAS KODE

File test: [`tests/Feature/AdvancedEnterpriseCompliancePhase15Test.php`](file:///Users/legacygandawijaya/Documents/hris-php/tests/Feature/AdvancedEnterpriseCompliancePhase15Test.php)

| No | Nama Skenario Test | Aspek yang Diverifikasi |
|---|---|---|
| 1 | `test_wltk_draft_report_generation_aggregates_demographics_wages_and_statutory_status` | Agregasi otomatis seluruh karyawan aktif, segregasi gender/kewarganegaraan, penentuan rentang upah, dan verifikasi jaminan sosial. |
| 2 | `test_wltk_submission_receipt_activates_one_year_statutory_validity_and_renewal_alerts` | Pencatatan nomor tanda terima Disnaker, penetapan validitas tahunan ($+1$ tahun), dan aktivasi peringatan dini renewal. |
| 3 | `test_wltk_export_json_and_csv_generation` | Validitas format berkas JSON Kemnaker dan integritas baris data CSV inspeksi. |
| 4 | `test_ijp_create_vacancy_with_tenure_and_performance_gating` | Pembuatan lowongan internal dengan syarat masa kerja minimum dan passing grade appraisal. |
| 5 | `test_ijp_eligibility_validation_identifies_ineligible_due_to_tenure_or_active_sp` | Validasi kelayakan pelamar: mendeteksi ketidaklayakan akibat masa kerja $< 12$ bulan atau adanya Surat Peringatan aktif. |
| 6 | `test_ijp_application_workflow_manager_approval_and_hr_final_acceptance` | Alur pengajuan lamaran, persetujuan manajer asal, pencegahan duplikasi lamaran, dan keputusan mutasi akhir HR. |
| 7 | `test_onboarding_buddy_mentorship_assignment_and_90_day_milestone_auto_scheduling` | Penugasan Senior Buddy 90 hari dan otomasi penjadwalan milestone Hari ke-30, ke-60, dan ke-90. |
| 8 | `test_onboarding_buddy_checkin_completion_and_auto_completion_of_mentorship_pair` | Pengisian evaluasi dua arah dan transisi otomatis status pasangan menjadi *completed* saat ketiga milestone selesai. |
| 9 | `test_asset_handover_bast_creation_and_damage_compensation_due_calculation` | Pembuatan draft BAST, inventarisasi barang serah terima, dan kalkulasi ganti rugi barang rusak/hilang. |
| 10 | `test_asset_handover_digital_signing_updates_inventory_and_triggers_automated_sso_revocation` | Tanda tangan digital BAST, pengembalian status aset ke inventaris (`available`), dan pencatatan log revokasi SSO otomatis. |
| 11 | `test_disability_employment_quota_audit_under_uu_8_2016_pasal_53` | Perhitungan kuota sah 1% (swasta) / 2% (BUMN), pendaftaran profil disabilitas, evaluasi akomodasi, dan transisi status kepatuhan. |

---

## 6. KESIMPULAN

Implementasi Phase 15 melengkapi RiHRIS sebagai platform Human Resource Information System enterprise Indonesia yang tidak hanya memenuhi kepatuhan regulasi ketenagakerjaan secara rigid (UU No. 7/1981 dan UU No. 8/2016), tetapi juga menyediakan kapabilitas retensi dan keamanan talenta modern (Internal Mobility Marketplace, Onboarding Buddy Mentorship, dan Automated IT Offboarding Asset BAST). Seluruh modul telah diuji, terhubung dengan struktur multi-tenant, dan siap digunakan pada lingkungan produksi.
