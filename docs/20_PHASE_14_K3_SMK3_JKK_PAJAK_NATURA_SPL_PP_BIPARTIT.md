# DOKUMEN IMPLEMENTASI PHASE 14: KESELAMATAN & KESEHATAN KERJA (K3 SMK3 & KLAIM JKK BPJS), PAJAK NATURA & KENIKMATAN (PMK 66/2023), SURAT PERINTAH LEMBUR (SPL PP 35/2021 LIMIT VALIDATOR), PERATURAN PERUSAHAAN (PP/PKB) REGISTER, DAN FORUM LKS BIPARTIT (PERMENAKER 32/2008)

**Platform:** RiHRIS — Multi-Company SaaS HRIS Indonesia  
**Modul:**  
1. Sistem Manajemen Keselamatan dan Kesehatan Kerja (SMK3 UU No. 1/1970 & PP No. 50/2012) & Klaim JKK BPJS Ketenagakerjaan (Tahap I & Tahap II)  
2. Pajak Imbalan Natura dan/atau Kenikmatan (PMK No. 66 Tahun 2023 & UU HPP No. 7/2021)  
3. Surat Perintah Lembur (SPL) Terpadu & Validasi Batas Maksimal Kerja Lembur (Pasal 26 PP No. 35 Tahun 2021)  
4. Register Peraturan Perusahaan (PP) & Perjanjian Kerja Bersama (PKB) dengan Peringatan Perpanjangan Disnaker (H-90)  
5. Forum Lembaga Kerja Sama (LKS) Bipartit (Permenaker No. 32 Tahun 2008 & Pasal 106 UU No. 13/2003)  
**Status Implementasi:** Production Ready (Completed)

---

## 1. DESKRIPSI & LANDASAN HUKUM INDONESIA

Fase 14 melengkapi perlindungan keselamatan kerja tenaga kerja, kepatuhan perpajakan natura terbaru, pembatasan lembur yuridis ketat, dan iklim hubungan industrial yang harmonis:

### 1.1 K3 SMK3 & Klaim JKK BPJS Ketenagakerjaan (UU No. 1/1970 & PP No. 50/2012)
- **Landasan Hukum**:
  - **UU No. 1 Tahun 1970** tentang Keselamatan Kerja.
  - **PP No. 50 Tahun 2012** tentang Penerapan Sistem Manajemen Keselamatan dan Kesehatan Kerja (SMK3).
  - **PP No. 44 Tahun 2015** tentang Penyelenggaraan Program Jaminan Kecelakaan Kerja (JKK) dan Jaminan Kematian (JKM).
- **Mekanisme Pelaporan & Klaim**:
  - Perekaman insiden kecelakaan kerja dengan pengelompokan keparahan: *Near Miss* (Hampir Celaka), *Minor First Aid* (P3K), *Lost Time Injury* (LTI / Cidera dengan Hari Kerja Hilang), dan *Fatality*.
  - Analisis akar penyebab (*Root Cause Analysis* 5-Why / Fishbone) dan Tindakan Perbaikan & Pencegahan (CAPA).
  - Integrasi Klaim JKK BPJS Ketenagakerjaan:
    - **Tahap I**: Wajib dilaporkan paling lambat $2 \times 24$ jam sejak terjadinya kecelakaan kerja ke kantor BPJS Ketenagakerjaan terdaftar.
    - **Tahap II**: Penetapan klaim akhir setelah pekerja dinyatakan sembuh, cacat, atau meninggal dunia oleh dokter pemeriksa.
    - **Santunan Sementara Tidak Mampu Bekerja (STMB)**: Menjamin 100% upah untuk 6 bulan pertama, 100% upah untuk 6 bulan kedua, dan 50% upah untuk bulan seterusnya hingga sembuh total.
  - Surveilans MCU K3 Berkala: Perekaman tekanan darah, kolesterol, gula darah, dan penetapan kategori kelayakan kerja (*Fit to Work*, *Fit with Restriction*, *Temporarily Unfit*, *Unfit*).

### 1.2 Pajak Natura & Kenikmatan (PMK No. 66 Tahun 2023)
- **Landasan Hukum**:
  - **PMK No. 66 Tahun 2023** tentang Perlakuan Pajak Penghasilan atas Penggantian atau Imbalan Sehubungan dengan Pekerjaan atau Jasa dalam Bentuk Natura dan/atau Kenikmatan.
  - **UU No. 7 Tahun 2021** tentang Harmonisasi Peraturan Perpajakan (UU HPP).
- **Aturan Fiskal & Batasan Bebas Pajak**:
  - Makanan/minuman bagi seluruh pegawai: Dikecualikan dari objek PPh 21 tanpa batasan nominal.
  - Kupon/voucher sembako & bahan makanan: Dikecualikan hingga nilai **Rp 3.000.000 per pegawai per bulan** (Pasal 3 ayat 2 huruf b).
  - Fasilitas tempat tinggal/apartemen dan kendaraan dinas non-daerah tertentu: Objek pemotongan PPh 21 atas selisih nilai pasar wajar di atas batasan non-pajak.
- **Formula Penambahan DPP PPh 21**:
  $$\text{Taxable Natura} = \max(0, \text{Gross Market Value} - \text{Non-Taxable Threshold})$$
  Nilai natura kena pajak otomatis ditambahkan ke Penghasilan Bruto bulanan karyawan untuk perhitungan pemotongan PPh 21 TER (PMK 168/2023).

### 1.3 Surat Perintah Lembur (SPL) & Batas Maksimal (PP No. 35/2021 Pasal 26)
- **Landasan Hukum**:
  - **PP No. 35 Tahun 2021 Pasal 26**:
    - **Ayat (1)**: Waktu kerja lembur hanya dapat dilakukan paling banyak **4 (empat) jam dalam 1 (satu) hari** dan **18 (delapan belas) jam dalam 1 (satu) minggu** pada hari kerja biasa.
    - **Ayat (2)**: Ketentuan batas lembur tidak termasuk kerja lembur yang dilakukan pada waktu istirahat mingguan atau hari libur resmi.
    - **Ayat (3)**: Untuk melakukan kerja lembur harus ada perintah dari Pengusaha (SPL) dan persetujuan tertulis dari Pekerja/Buruh bersangkutan.
- **Validasi Otomatis Sistem**:
  - Sistem menolak pembuatan SPL hari kerja jika durasi $> 4.0$ jam.
  - Sistem menghitung akumulasi jam lembur hari kerja dalam minggu berjalan; jika total $> 18.0$ jam, sistem memblokir form dengan pesan kesalahan kepatuhan statutori.
  - Menyediakan alur persetujuan dua arah: Persetujuan Atasan (Manager Approval) dan Pencatatan Persetujuan Tertulis Karyawan (*Employee Consent*).

### 1.4 Register Peraturan Perusahaan (PP) & PKB (UU No. 13/2003 & H-90 Renewal)
- **Landasan Hukum**:
  - **UU No. 13 Tahun 2003 Pasal 108 s/d 114**:
    - Pengusaha yang mempekerjakan sekurang-kurangnya 10 (sepuluh) orang pekerja wajib membuat Peraturan Perusahaan (PP).
    - Masa berlaku Peraturan Perusahaan paling lama **2 (dua) tahun** dan wajib diperbaharui setelah habis masa berlakunya (Pasal 111 ayat 3).
    - Pengesahan resmi oleh Kepala Dinas Tenaga Kerja setempat.
- **Early Warning System H-90**:
  - Notifikasi otomatis pada dashboard jika masa berlaku PP/PKB tersisa $\le 90$ hari agar tim HR dapat menyusun draf pembaharuan dan mendaftarkannya ke Disnaker sebelum kedaluwarsa.

### 1.5 Forum Lembaga Kerja Sama (LKS) Bipartit (Permenaker No. 32 Tahun 2008)
- **Landasan Hukum**:
  - **Pasal 106 UU No. 13 Tahun 2003**: Setiap perusahaan yang mempekerjakan 50 (lima puluh) orang pekerja atau lebih wajib membentuk LKS Bipartit.
  - **Permenaker No. 32 Tahun 2008** tentang Tata Cara Pembentukan dan Susunan Keanggotaan Lembaga Kerja Sama Bipartit:
    - **Pasal 6**: Susunan keanggotaan beranggotakan unsur Pengusaha dan unsur Pekerja/Buruh dengan perbandingan **1 : 1**.
    - **Pasal 13**: LKS Bipartit mengadakan pertemuan sekurang-kurangnya 1 (satu) kali dalam sebulan.
    - **Pasal 15**: Pengurus LKS Bipartit melaporkan kegiatan kepada instansi ketenagakerjaan (Disnaker) sekurang-kurangnya **6 (enam) bulan sekali**.

---

## 2. ARSITEKTUR STRUKTUR DATABASE

Migration: `database/migrations/2026_01_14_000001_create_phase14_k3_natura_spl_and_bipartite_tables.php`

1. **`workplace_incidents`**:
   - `id`, `tenant_id`, `company_id`, `incident_number` (`INC/K3/YYYY/XXXX`).
   - `incident_date`, `incident_time`, `location`, `employee_id`.
   - `severity` (`near_miss`, `minor_first_aid`, `lost_time_injury_lti`, `fatality`).
   - `injury_type`, `lost_workdays`, `description`, `root_cause_analysis`, `corrective_actions`, `status`.
   - `reported_by_user_id`, `investigated_by_user_id`.

2. **`bpjs_jkk_claims`**:
   - `id`, `tenant_id`, `workplace_incident_id`, `claim_number` (`JKK-BPJS-YYYY-XXXX`).
   - `stage` (`tahap_1`, `tahap_2`), `medical_facility_name`.
   - `estimated_medical_expense`, `approved_bpjs_coverage`, `temporary_disability_allowance` (Santunan STMB).
   - `claim_status` (`draft`, `submitted_to_bpjs`, `verified`, `reimbursed`, `rejected`).
   - `submitted_at`, `settled_at`, `bpjs_notes`.

3. **`employee_mcu_records`**:
   - `id`, `tenant_id`, `employee_id`, `mcu_date`, `clinic_provider`.
   - `blood_pressure`, `cholesterol`, `blood_sugar`.
   - `fitness_category` (`fit_to_work`, `fit_with_restriction`, `temporarily_unfit`, `unfit`).
   - `doctor_notes`, `follow_up_recommendation`.

4. **`fringe_benefits`**:
   - `id`, `tenant_id`, `company_id`, `employee_id`, `tax_year`, `period_month`.
   - `benefit_type`, `description`, `gross_market_value`, `non_taxable_threshold`.
   - `taxable_natura_value`, `is_taxable_object`, `fiscal_notes`.

5. **`overtime_orders`**:
   - `id`, `tenant_id`, `company_id`, `order_number` (`SPL/YYYY/MM/XXXX`).
   - `employee_id`, `overtime_date`, `start_time`, `end_time`.
   - `planned_duration_hours`, `actual_duration_hours`, `reason_task_description`, `is_day_off`.
   - `manager_approved_by`, `manager_approved_at`, `employee_consented_at`, `status`.

6. **`company_regulations`**:
   - `id`, `tenant_id`, `company_id`, `document_type` (`peraturan_perusahaan_pp`, `perjanjian_kerja_bersama_pkb`).
   - `title`, `reg_number`, `effective_start_date`, `effective_end_date` (maks 2 tahun), `disnaker_office`, `status`.

7. **`bipartite_meetings`**:
   - `id`, `tenant_id`, `company_id`, `meeting_number` (`BIPARTIT/YYYY/MM/XXXX`).
   - `meeting_date`, `meeting_title`, `agenda_topics`.
   - `employer_representatives` (JSON array), `worker_representatives` (JSON array 1:1).
   - `meeting_minutes`, `action_items`, `disnaker_reported_at`, `status`.

---

## 3. LAPISAN SERVICE & CONTROLLER

1. **`App\Services\Safety\K3IncidentService`**:
   - `reportIncident(Company, Employee, array $data, ?User $reporter)`: Perekaman insiden K3 & kalkulasi hari kerja hilang LTI.
   - `fileBpjsJkkClaim(WorkplaceIncident, array $claimData)`: Pengajuan klaim JKK Tahap I/II & perhitungan santunan STMB.
   - `settleJkkClaim(BpjsJkkClaim, float $approvedCoverage, ?string $notes)`: Penyelesaian klaim penggantian biaya & penutupan insiden.
   - `recordMcu(Employee, array $mcuData)`: Perekaman hasil pemeriksaan kesehatan berkala.
   - `getK3Metrics(Company, ?int $year)`: Metrik insiden tahunan & lost workdays.

2. **`App\Services\Tax\FringeBenefitTaxService`**:
   - `recordBenefit(Company, Employee, array $data)`: Penilaian fasilitas natura PMK 66/2023 & perhitungan selisih objek pajak.
   - `getMonthlyNaturaTaxAddition(Employee, int $year, int $month)`: Total natura kena pajak untuk DPP PPh 21 slip gaji.

3. **`App\Services\Overtime\SplComplianceService`**:
   - `createOrder(Company, Employee, array $data)`: Pembuatan SPL resmi dengan validasi limit harian ($\le 4\text{ jam}$) & mingguan ($\le 18\text{ jam}$).
   - `approveOrder(OvertimeOrder, User $manager)`: Otorisasi atasan langsung.
   - `giveEmployeeConsent(OvertimeOrder)`: Perekaman persetujuan tertulis pekerja Pasal 26 ayat 3.
   - `verifyActualOvertime(OvertimeOrder, float $actualHours)`: Rekonsiliasi durasi aktual.

4. **`App\Services\Compliance\CompanyRegulationService`**:
   - `registerRegulation(Company, array $data)`: Pendaftaran PP/PKB dengan pembatasan durasi maksimal 2 tahun (Pasal 111 ayat 3).
   - `getRenewalAlerts(Company)`: Peringatan kedaluwarsa H-90 perpanjangan Disnaker.

5. **`App\Services\Relations\BipartiteService`**:
   - `recordMeeting(Company, array $data)`: Perekaman risalah sidang bipartit dengan validasi kuorum perwakilan seimbang 1:1.
   - `markReportedToDisnaker(BipartiteMeeting, ?string $reportDate)`: Pelaporan semesteran ke Disnaker.

---

## 4. BLADE VIEWS & USER INTERFACE

1. `resources/views/company/k3/index.blade.php`: Dasbor manajemen K3 & SMK3 dengan kartu metrik insiden, tabel buku register kecelakaan kerja, tombol ajukan klaim JKK, dan surveilans MCU.
2. `resources/views/company/k3/jkk_claim.blade.php`: Berkas cetak resmi Formulir Laporan Kasus Kecelakaan Kerja JKK BPJS Ketenagakerjaan (Tahap I / Tahap II) lengkap dengan data pasien, fasilitas PLKK, dan santunan STMB.
3. `resources/views/company/fringe_benefits/index.blade.php`: Register pajak natura & kenikmatan PMK 66/2023, filter masa pajak, komparasi nilai pasar wajar vs batas bebas pajak, dan modal input fasilitas.
4. `resources/views/company/spl/index.blade.php`: Manajemen Surat Perintah Lembur (SPL) PP 35/2021, meteran batas kepatuhan 4 jam/hari dan 18 jam/minggu, tombol otorisasi atasan, dan persetujuan pekerja.
5. `resources/views/company/regulations/index.blade.php`: Buku register Peraturan Perusahaan (PP) & PKB, kartu masa berlaku 2 tahun, dan banner peringatan H-90 perpanjangan Disnaker.
6. `resources/views/company/bipartite/index.blade.php`: Forum LKS Bipartit Permenaker 32/2008, rekapitulasi keanggotaan 1:1 pengusaha & pekerja, notulen sidang, dan tombol pelaporan 6 bulanan Disnaker.

---

## 5. PENGUJIAN OTOMATIS (AUTOMATED FEATURE TESTS)

Berkas Test: `tests/Feature/AdvancedEnterpriseCompliancePhase14Test.php`  
Terdiri dari 11 skenario pengujian komprehensif:

1. `test_k3_incident_reporting_with_lti_lost_workdays`: Verifikasi pencatatan insiden K3 severity LTI dan penghitungan hari kerja hilang.
2. `test_bpjs_jkk_claim_filing_and_stmb_allowance_calculation`: Verifikasi pembuatan klaim JKK BPJS dan formula santunan STMB upah penuh.
3. `test_bpjs_jkk_claim_settlement_reimburses_and_closes_incident`: Pengujian penyelesaian klaim pencairan dana BPJS dan penutupan status insiden.
4. `test_k3_mcu_surveillance_record_and_fitness_category`: Pengujian rekam MCU kesehatan kerja berkala dan kategori kebugaran (*fit to work*).
5. `test_fringe_benefit_tax_pmk_66_threshold_exemption_and_taxable_addition`: Pengujian pemotongan pajak natura voucher sembako di atas ambang batas Rp 3.000.000 (PMK 66/2023).
6. `test_fringe_benefit_tax_non_taxable_when_under_threshold`: Pengujian fasilitas natura di bawah batas ambang fiskal menghasilkan Rp 0 objek PPh 21.
7. `test_spl_overtime_daily_limit_validation_blocks_exceeding_4_hours`: Pengujian pemblokiran durasi lembur $> 4.0$ jam pada hari kerja sesuai Pasal 26 PP 35/2021.
8. `test_spl_overtime_weekly_limit_validation_blocks_exceeding_18_hours`: Pengujian pemblokiran akumulasi lembur mingguan $> 18.0$ jam pada hari kerja biasa.
9. `test_spl_overtime_approval_and_employee_written_consent`: Pengujian otorisasi manajerial dan persetujuan tertulis pekerja (Pasal 26 ayat 3 PP 35/2021).
10. `test_company_regulation_two_year_statutory_validity_and_h90_alerts`: Pengujian pembatasan masa berlaku PP maksimal 2 tahun (UU 13/2003 Pasal 111) dan deteksi peringatan kedaluwarsa H-90.
11. `test_lks_bipartite_meeting_requires_equal_representation_and_disnaker_reporting`: Pengujian komposisi perwakilan berimbang 1:1 dan pelaporan semesteran Disnaker (Permenaker 32/2008).
