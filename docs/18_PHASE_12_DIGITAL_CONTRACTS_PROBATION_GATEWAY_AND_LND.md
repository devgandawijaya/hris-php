# DOKUMEN IMPLEMENTASI PHASE 12: DIGITAL EMPLOYMENT CONTRACTS & UU ITE E-SIGNATURES, PROBATION EVALUATION & SK PKWTT CONVERSION (PASAL 60 UU KETENAGAKERJAAN), SAAS PAYMENT GATEWAY (MIDTRANS SNAP & WEBHOOKS), KIRKPATRICK L&D EVALUATION (LEVEL 1-4), DAN FLEXIBLE WORKING ARRANGEMENT (FWA WFH/WFO)

**Platform:** RiHRIS — Multi-Company SaaS HRIS Indonesia  
**Modul:**  
1. Perjanjian Kerja Digital & Tanda Tangan Elektronik Bersertifikasi (UU ITE No. 1/2024 & PP No. 71/2019)  
2. Evaluasi Masa Percobaan 3 Bulan & Konversi Otomatis SK PKWTT (Pasal 60 UU Ketenagakerjaan No. 13/2003 & PP No. 35/2021)  
3. SaaS Payment Gateway (Midtrans Snap Integration & SHA-512 Secure Webhooks)  
4. Evaluasi Pelatihan Komprehensif Model Kirkpatrick (Level 1-4) & E-Sertifikat Digital Terverifikasi  
5. Flexible Working Arrangement (FWA WFH/WFO Scheduler & Attendance Rule Validation)  
**Status Implementasi:** Production Ready (Completed)

---

## 1. DESKRIPSI & LANDASAN HUKUM INDONESIA

Fase 12 melengkapi kapabilitas enterprise dan kepatuhan hukum digital tertinggi bagi sistem HRIS SaaS multi-tenant di Indonesia:

### 1.1 Digital Employment Contracts & E-Signatures (UU ITE No. 1/2024 & PP No. 71/2019)
- **Landasan Hukum**:
  - **UU No. 1 Tahun 2024** (Perubahan Kedua atas UU No. 11/2008 tentang Informasi dan Transaksi Elektronik / UU ITE): Pasal 11 mengatur syarat keabsahan tanda tangan elektronik (TTE) yang memiliki kekuatan dan akibat hukum yang sah.
  - **PP No. 71 Tahun 2019** tentang Penyelenggaraan Sistem dan Transaksi Elektronik (PSTE).
- **Mekanisme Integritas & Keamanan**:
  - Penandatanganan dua pihak secara asinkron: Pihak Pertama (Direksi/Pemberi Kerja) dan Pihak Kedua (Karyawan via ESS Portal).
  - Perekaman jejak audit yuridis (*Audit Trail*): Timestamp penandatanganan berstandar ISO 8601, alamat IP penandatangan, identitas terverifikasi, dan user agent.
  - Segel Kriptografis (*Cryptographic Hash Seal*): Setelah kedua pihak menandatangani, sistem otomatis membubuhkan segel `hash('sha256', $contractData)` anti-tamper untuk menjamin naskah tidak mengalami alterasi setelah disahkan.

### 1.2 Evaluasi Masa Percobaan & Konversi SK PKWTT (Pasal 60 UU No. 13/2003 & PP No. 35/2021)
- **Ketentuan Yuridis Ketat**:
  - Masa percobaan kerja (*probation*) **hanya dapat dipersyaratkan untuk Perjanjian Kerja Waktu Tidak Tertentu (PKWTT)**.
  - Durasi masa percobaan dibatasi maksimal **3 (tiga) bulan**.
  - Klausul masa percobaan wajib dicantumkan dalam perjanjian kerja atau surat pengangkatan.
- **Formula Penilaian Bobot Komposit**:
  $$\text{Final Score} = (\text{Technical Competence} \times 40\%) + (\text{Attendance \& Discipline} \times 30\%) + (\text{Culture \& Integrity} \times 30\%)$$
- **Ambang Batas Kelulusan (*Passing Grade*)**:
  - $\ge 75.00$ Poin $\rightarrow$ **LULUS (Passed PKWTT)**:
    - Status kepegawaian otomatis terkonversi menjadi `pkwtt` (Karyawan Tetap).
    - Sistem menerbitkan **Surat Keputusan (SK) Direksi Pengangkatan Karyawan Tetap** dengan penomoran resmi: `NOMOR/SK-DIR/PKWTT/TAHUN`.
  - $< 75.00$ Poin $\rightarrow$ **TIDAK LULUS (Failed Terminated)**:
    - Hubungan kerja berakhir demi hukum pada akhir masa percobaan tanpa kewajiban kompensasi pesangon (Pasal 60 ayat 2 UU No. 13/2003).

### 1.3 SaaS Payment Gateway: Midtrans Snap & Webhook Notification
- **Arsitektur Pembayaran SaaS**:
  - Integrasi Midtrans Snap API untuk pembayaran faktur langganan (*subscription invoices*) secara instan.
  - Mendukung metode pembayaran modern Indonesia: QRIS (GoPay, OVO, ShopeePay, Dana), Virtual Account bank nasional (BCA, Mandiri, BNI, BRI, Permata), dan Kartu Kredit berstandar PCI-DSS.
- **Validasi Webhook SHA-512**:
  - Sistem memvalidasi keaslian callback notifikasi menggunakan enkripsi tanda tangan SHA-512:
    $$\text{Signature} = \text{hash}('sha512', \text{order\_id} . \text{status\_code} . \text{gross\_amount} . \text{ServerKey})$$
  - Pada status `settlement` atau `capture`, sistem otomatis menandai tagihan menjadi `paid`, mencatat tanggal bayar, dan memperpanjang masa aktif langganan tenant.
  - Dilengkapi fitur *Instant Sandbox Simulation* untuk pengujian tanpa kartu kredit asli di lingkungan *staging/development*.

### 1.4 Evaluasi Pelatihan Model Kirkpatrick (Level 1 s/d 4) & E-Sertifikat Terverifikasi
- **Kerangka Kerja Kirkpatrick L&D**:
  - **Level 1 (Reaction)**: Umpan balik kepuasan dan keterlibatan peserta pelatihan terhadap kualitas materi dan instruktur (Skala 1.0 - 5.0).
  - **Level 2 (Learning)**: Pengukuran penguasaan kompetensi kognitif melalui uji awal (*Pre-Test*) dan uji akhir (*Post-Test*), dengan kalkulasi *Normalized Learning Gain*:
    $$\text{Gain } \% = \frac{\text{Post-Test} - \text{Pre-Test}}{100 - \text{Pre-Test}} \times 100\%$$
  - **Level 3 (Behavior)**: Observasi penerapan keterampilan baru di lingkungan kerja nyata oleh atasan langsung (Supervisor) minimal 30 hari pasca pelatihan.
  - **Level 4 (Results)**: Pengukuran dampak bisnis terhadap produktivitas tim dan eliminasi insiden operasional.
- **Sertifikat Kelulusan Digital**:
  - Penerbitan sertifikat digital resmi dengan orientasi landscape, kode kredensial unik `CERT-LND-YYYY-XXXX`, dan hash integritas verifikasi SHA256.

### 1.5 Flexible Working Arrangement (FWA WFH/WFO Scheduler)
- **Manajemen Kerja Fleksibel**:
  - Penjadwalan mingguan dan harian karyawan untuk moda kerja: **WFH (Work From Home)**, **WFO (Work From Office)**, **Hybrid**, dan **Dinas Luar**.
  - Alur persetujuan (*Approval Workflow*) oleh atasan langsung melalui ESS portal.
  - Validasi kepatuhan presensi: Pengecekan otomatis batas toleransi geolokasi saat absensi presensi harian berdasarkan status persetujuan FWA pada hari berjalan.

---

## 2. ARSITEKTUR STRUKTUR DATABASE

Migration: `database/migrations/2026_01_12_000001_create_phase12_contracts_probation_gateway_and_lnd_tables.php`

1. **`employment_contracts` (Kolom Tambahan E-Signature)**:
   - `contract_body`: Teks klausul perjanjian kerja standar.
   - `employee_signature`: Data tanda tangan digital pihak pekerja.
   - `employee_signed_at`: Timestamp penandatanganan pihak pekerja.
   - `employee_sign_ip`: Alamat IP perangkat pekerja saat menandatangani.
   - `company_signature`: Data tanda tangan digital pihak manajemen.
   - `company_signed_at`: Timestamp penandatanganan pihak manajemen.
   - `company_sign_ip`: Alamat IP perangkat pejabat manajemen.
   - `signer_name`: Nama pejabat penandatangan perusahaan.
   - `signer_position`: Jabatan pejabat penandatangan.
   - `signed_hash`: Hash kriptografis SHA256 integritas dokumen.
   - `is_digitally_signed`: Boolean flag status keabsahan dokumen penuh.

2. **`probation_evaluations`**:
   - `id`, `tenant_id`, `company_id`, `employee_id`, `evaluator_user_id`.
   - `evaluation_date`: Tanggal dilakukannya penilaian probation.
   - `technical_competence_score`: Nilai kompetensi teknis (0-100, bobot 40%).
   - `attendance_discipline_score`: Nilai kehadiran dan disiplin (0-100, bobot 30%).
   - `culture_integrity_score`: Nilai integrasi budaya dan attitude (0-100, bobot 30%).
   - `final_score`: Skor komposit tertimbang.
   - `decision`: Keputusan (`passed_pkwtt`, `extended`, `failed_terminated`).
   - `sk_number`: Nomor Surat Keputusan Pengangkatan Karyawan Tetap.
   - `evaluator_notes`, `development_recommendations`, `decided_at`.

3. **`payment_gateway_transactions`**:
   - `id`, `tenant_id`, `subscription_invoice_id`.
   - `gateway_provider`: Provider (`midtrans`, `xendit`, `sandbox`).
   - `transaction_id`, `order_id`: ID pesanan unik.
   - `gross_amount`: Total nominal pembayaran.
   - `payment_type`: Tipe pembayaran (`qris`, `bca_va`, `mandiri_va`, `bni_va`, `credit_card`).
   - `transaction_status`: Status (`pending`, `settlement`, `capture`, `expire`, `cancel`, `deny`).
   - `signature_hash`: Hash verifikasi SHA512 Midtrans.
   - `snap_token`, `payment_url`, `raw_payload`, `paid_at`.

4. **`training_kirkpatrick_evaluations`**:
   - `id`, `tenant_id`, `training_enrollment_id`.
   - `pre_test_score`, `post_test_score`: Skor uji awal dan uji akhir (0-100).
   - `learning_gain_percentage`: Persentase peningkatan penguasaan materi (Level 2).
   - `level1_reaction_score`, `level1_feedback`: Kepuasan peserta (Level 1, skala 1-5).
   - `level3_behavior_score`, `level3_notes`: Penilaian perilaku oleh supervisor (Level 3, skala 1-5).
   - `level3_evaluated_by`, `level3_evaluated_at`, `status`.

5. **`training_certificates`**:
   - `id`, `tenant_id`, `training_enrollment_id`.
   - `certificate_number`: Nomor unik sertifikat (`CERT-LND-YYYY-XXXX`).
   - `recipient_name`, `course_title`, `completion_date`.
   - `score_achieved`, `grade_label`: Predikat kelulusan.
   - `verification_hash`: Hash SHA256 anti-pemalsuan.
   - `signer_name`, `signer_title`: Penandatangan sertifikat (Instruktur & Direktur HR).

6. **`fwa_schedules`**:
   - `id`, `tenant_id`, `company_id`, `employee_id`.
   - `date`: Tanggal penugasan kerja fleksibel.
   - `work_mode`: Moda kerja (`wfo`, `wfh`).
   - `reason`: Alasan / uraian tugas.
   - `status`: Status persetujuan (`pending`, `approved`, `rejected`).
   - `approved_by`, `approved_at`.

---

## 3. LAPISAN SERVICE & CONTROLLER

1. **`App\Services\Contract\DigitalContractService`**:
   - `draftContract(Employee, array $data)`: Pembuatan draft perjanjian kerja lengkap dengan klausul standar Indonesia.
   - `signByEmployee(EmploymentContract, string $signatureData, string $ipAddress)`: Pembubuhan TTE pegawai.
   - `signByCompany(EmploymentContract, string $signatureData, User $signer, string $ipAddress)`: Pembubuhan TTE pejabat perusahaan.
   - `verifyAndSealIfComplete(EmploymentContract)`: Pengecekan tanda tangan ganda dan pembubuhan segel SHA256.
2. **`App\Services\Probation\ProbationEvaluationService`**:
   - `evaluateProbation(Employee, User $evaluator, array $data)`: Kalkulasi skor komposit, konversi otomatis status PKWTT, dan penerbitan SK Direksi.
3. **`App\Services\Payment\PaymentGatewayService`**:
   - `createCheckoutTransaction(SubscriptionInvoice, string $provider, string $paymentType)`: Pembuatan token Midtrans Snap.
   - `handleMidtransWebhook(array $payload, ?string $serverKey)`: Validasi signature SHA512 dan pelunasan otomatis tagihan.
   - `simulateSandboxPayment(PaymentGatewayTransaction)`: Simulasi instant checkout testing sandbox.
4. **`App\Services\Learning\KirkpatrickEvaluationService`**:
   - `recordTestScores(TrainingEnrollment, float $preTest, float $postTest)`: Perekaman nilai uji dan perhitungan delta gain %.
   - `submitReaction(TrainingKirkpatrickEvaluation, float $reactionScore, ?string $feedback)`: Rekam Level 1.
   - `submitBehaviorAssessment(TrainingKirkpatrickEvaluation, User $supervisor, float $behaviorScore, ?string $notes)`: Rekam Level 3.
   - `issueCertificate(TrainingEnrollment, User $issuer)`: Penerbitan sertifikat digital terverifikasi.
5. **`App\Services\Attendance\FwaService`**:
   - `requestFwa(Employee, string $date, string $workMode, ?string $reason)`: Pengajuan jadwal kerja fleksibel.
   - `processApproval(FwaSchedule, User $approver, bool $approved)`: Otorisasi manajerial.
   - `checkAttendancePolicy(Employee, string $date)`: Pengecekan kebijakan presensi harian.

---

## 4. BLADE VIEWS & USER INTERFACE

1. `resources/views/company/contracts/digital_index.blade.php`: Dasbor manajemen kontrak digital dengan metrik status TTE, tabel audit trail, dan modal pembuat perjanjian kerja baru.
2. `resources/views/company/contracts/digital_show.blade.php`: Tampilan detail kontrak, sertifikat segel kriptografis UU ITE, catatan IP & waktu penandatanganan, serta tombol eksekusi TTE.
3. `resources/views/company/contracts/digital_print.blade.php`: Berkas cetak resmi perjanjian kerja (PKWT/PKWTT) standar hukum ketenagakerjaan dengan blok TTE dan hash keutuhan dokumen.
4. `resources/views/company/probation/index.blade.php`: Dasbor evaluasi masa percobaan 3 bulan, metrik kelulusan, dan modal penilaian komposit (Teknis 40%, Kehadiran 30%, Budaya 30%).
5. `resources/views/company/probation/show.blade.php`: Rincian skor evaluasi karyawan, catatan evaluator, dan status konversi PKWTT.
6. `resources/views/company/probation/print_sk.blade.php`: Format resmi cetak Surat Keputusan (SK) Direksi Pengangkatan Karyawan Tetap berlandaskan Pasal 60 UU No. 13/2003.
7. `resources/views/company/billing/checkout.blade.php`: Halaman checkout pembayaran Midtrans Snap embed & panel simulasi sandbox instant.
8. `resources/views/company/training/kirkpatrick.blade.php`: Dasbor evaluasi 4-level model Kirkpatrick (Reaksi, Pembelajaran, Perilaku, Hasil) & penerbitan e-sertifikat.
9. `resources/views/company/training/certificate.blade.php`: Berkas cetak E-Sertifikat resmi orientasi landscape dengan bingkai emas elegan, credential ID, dan QR hash verifikasi.
10. `resources/views/company/fwa/index.blade.php`: Manajemen jadwal kerja fleksibel (WFH / WFO / Hybrid) dan persetujuan manajerial.
11. `resources/views/employee/dashboard.blade.php`:
    - Tab Kontrak: Notifikasi dan form pembubuhan TTE mandiri oleh karyawan (UU ITE).
    - Tab FWA: Form mandiri permohonan kerja fleksibel (WFH) dan kalender jadwal.
    - Tab E-Sertifikat: Galeri sertifikat kompetensi berintegritas tinggi milik pekerja.

---

## 5. PENGUJIAN OTOMATIS (AUTOMATED FEATURE TESTS)

Berkas Test: `tests/Feature/AdvancedEnterpriseCompliancePhase12Test.php`  
Terdiri dari 11 skenario pengujian komprehensif:

1. `test_digital_contract_drafting_with_indonesian_legal_clauses`: Verifikasi pembuatan draft PKWT/PKWTT dengan klausul PP No. 35/2021 dan format nomor surat otomatis.
2. `test_digital_contract_dual_signing_and_sha256_cryptographic_seal`: Pengujian tanda tangan berurutan (Pekerja & Perusahaan), pencatatan IP address, dan pembubuhan segel SHA256 anti-tamper.
3. `test_probation_evaluation_weighted_scoring_formula`: Uji akurasi matematis bobot komposit (40% Teknis + 30% Kehadiran + 30% Budaya).
4. `test_probation_passed_promotes_employee_to_pkwtt_and_issues_sk`: Uji kelulusan probation ($\ge 75$), konversi otomatis status menjadi `pkwtt`, dan penerbitan SK Direksi.
5. `test_probation_failed_terminates_employment_pursuant_to_pasal_60`: Uji ketidaklulusan probation ($< 75$) dan terminasi hubungan kerja tanpa pesangon sesuai Pasal 60 UU 13/2003.
6. `test_payment_gateway_checkout_creates_transaction_and_snap_token`: Uji pembuatan order Midtrans dan penyerahan Snap token & URL pembayaran.
7. `test_payment_gateway_webhook_settlement_marks_invoice_paid`: Uji callback webhook dengan tanda tangan SHA512 dan pelunasan otomatis invoice langganan tenant.
8. `test_payment_gateway_simulate_sandbox_success`: Uji simulasi pembayaran instant sandbox untuk lingkungan pengujian.
9. `test_kirkpatrick_four_level_training_evaluation_and_learning_gain`: Uji formula gain % Pre-Test (60) vs Post-Test (90) menghasilkan $+75.0\%$ peningkatan kompetensi, pengisian Level 1 reaksi dan Level 3 perilaku kerja.
10. `test_kirkpatrick_digital_certificate_issuance_and_integrity_hash`: Uji penerbitan sertifikat digital berpenomoran unik dan hash verifikasi 64-karakter.
11. `test_flexible_working_arrangement_fwa_request_and_manager_approval`: Uji pengajuan FWA WFH, persetujuan manajer, dan validasi izin presensi.
