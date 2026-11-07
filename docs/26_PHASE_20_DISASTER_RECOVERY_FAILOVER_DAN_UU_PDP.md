# DOKUMEN IMPLEMENTASI PHASE 20: ENTERPRISE DISASTER RECOVERY & MULTI-REGION FAILOVER, ZERO TRUST HSM ENCRYPTION, SERTA KEPATUHAN UU PELINDUNGAN DATA PRIBADI (UU NO. 27/2022 PDP)

**Platform:** RiHRIS — Multi-Company SaaS HRIS Platform Indonesia  
**Modul:**  
1. Arsitektur Multi-Region Disaster Recovery (DR) & Failover Otomatis (Jakarta Primary `ap-southeast-3` vs Singapore Standby `ap-southeast-1`)  
2. Service Level Agreement (SLA) Resiliensi Bencana: RPO $\le 5$ Menit & RTO $\le 15$ Menit, Replikasi Sinkron, serta Simulasi Latihan Bencana Rutin (*Automated DR Drill Runs*)  
3. Zero Trust Security Architecture: Enkripsi Kolom Sensitif (AES-256-GCM Envelope Encryption) & Hardware Security Module (HSM FIPS 140-2 Level 3 / Cloud KMS) sesuai Pasal 35 UU PDP  
4. Tata Kelola Persetujuan Pemrosesan Data Pribadi Karyawan (*Explicit Consent Tracking & Revocation*) sesuai Pasal 9 & Pasal 20 s/d 24 UU PDP  
5. Pemenuhan Hak Subjek Data (*Data Subject Access Requests / DSAR*): Portabilitas Data Terstruktur JSON (Pasal 13), Hak Akses (Pasal 7), Ralat (Pasal 6), & Penghapusan/Anonimisasi (Pasal 8)  
6. Manajemen Insiden Kebocoran Data (*Data Breach Notification Audit*) dengan Batas Waktu Wajib Notifikasi Maksimal 72 Jam (3 x 24 Jam) sesuai Pasal 46 UU PDP  
**Status Implementasi:** Production Ready (Completed)

---

## 1. DESKRIPSI & LANDASAN HUKUM INDONESIA

Fase 20 merupakan fase puncak dari roadmap enterprise 20-fase RiHRIS. Fase ini menghadirkan benteng ketahanan infrastruktur (*high availability & business continuity*) berstandar perbankan/keuangan serta kepatuhan hukum penuh terhadap rezim privasi data tertinggi di Indonesia:

### 1.1 Kepatuhan UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)
- **Dasar Hukum**:
  - **UU No. 27 Tahun 2022** tentang Pelindungan Data Pribadi (UU PDP) yang berlaku secara mengikat bagi seluruh pengendali data pribadi korporasi di Indonesia.
- **Prinsip Utama yang Ditegakkan**:
  1. **Dasar Pemrosesan & Persetujuan Eksplisit (*Consent Management*) — Pasal 20 s/d 24**:
     Pengendali data pribadi (Perusahaan/HR) wajib memiliki dasar pemrosesan yang sah, baik persetujuan eksplisit (*explicit consent*) yang terekam jejak auditnya (waktu persetujuan, IP address, user agent, dan tujuan pemrosesan) maupun dasar pelaksanaan perjanjian kerja.
  2. **Hak Penarikan Persetujuan (*Consent Revocation*) — Pasal 9**:
     Subjek data (karyawan) berhak menarik kembali persetujuan pemrosesan data pribadinya sewaktu-waktu secara sah dan terdokumentasi dalam audit log.
  3. **Hak Portabilitas Data Pribadi (*Data Portability*) — Pasal 13**:
     Subjek data berhak memperoleh data pribadinya dalam format yang terstruktur, umum digunakan, dan dapat dibaca oleh sistem elektronik (*machine-readable JSON format*). RiHRIS menyediakan modul ekspor paket portabilitas data lengkap yang mencakup identitas, riwayat posisi/kontrak, dan catatan *consent*.
  4. **Hak Subjek Data Lainnya (Pasal 6, 7, 8)**:
     Meliputi hak pemutakhiran/ralat (*rectification*), hak akses (*access*), serta hak penghapusan/pemusnahan (*erasure/anonymization*).
  5. **Batas Waktu Pemenuhan Hak (SLA Pemenuhan)**:
     Permintaan DSAR wajib diselesaikan maksimal dalam **3 x 24 jam (72 jam kerja)** sejak pengajuan terverifikasi.
  6. **Notifikasi Kebocoran Data Wajib 72 Jam — Pasal 46**:
     Dalam hal terjadi kegagalan pelindungan data pribadi (*data breach incident*), pengendali data wajib menyampaikan pemberitahuan tertulis kepada lembaga otoritas pelindungan data pribadi dan subjek data pribadi paling lambat **3 x 24 jam (72 jam)** sejak insiden terdeteksi. RiHRIS mencatat selisih waktu `detected_at` vs `authority_notified_at` dan memberlakukan audit SLA kepatuhan 72 jam secara otomatis.

---

### 1.2 Zero Trust Security & Enkripsi Kriptografi Tingkat Kolom (Pasal 35 UU PDP)
- **Dasar Hukum**:
  - **Pasal 35 UU No. 27/2022**: Pengendali data pribadi wajib melindungi dan memastikan keamanan data pribadi dengan menyusun dan menerapkan langkah teknis operasional untuk melindungi data dari pemrosesan yang tidak sah.
  - **Peraturan BSSN No. 4 Tahun 2021** tentang Pedoman Manajemen Keamanan Informasi SPBE dan Standar Kriptografi.
  - **FIPS 140-2 Level 3**: Standar pengelolaan kunci enkripsi berbasis Hardware Security Module (HSM) / Cloud KMS.
- **Implementasi Arsitektur Keamanan**:
  - **Envelope Encryption**: Kolom-kolom data pribadi spesifik dan rahasia dienkripsi di tingkat basis data menggunakan algoritma **AES-256-GCM**:
    - Nomor Induk Kependudukan (NIK) — Data Spesifik (Pasal 4 ayat 2).
    - Nomor Pokok Wajib Pajak (NPWP) — Data Keuangan.
    - Nomor Rekening Bank Payroll (*Bank Account Number*) — Data Keuangan.
    - Gaji Pokok & Kompensasi Remunerasi (*Basic Salary*) — Data Rahasia Korporat.
  - **Zero Trust Principles**:
    - *Never Trust, Always Verify*: Setiap akses diverifikasi secara kontekstual berbasis role RBAC.
    - *Least Privilege*: Pembatasan akses ke data sensitif dengan masking dinamis (misal `317101******0001`).
    - *Full Audit Trail*: Seluruh aktivitas baca/tulis/ekspor data sensitif tercatat permanen di sistem audit log.

---

### 1.3 Enterprise Multi-Region Disaster Recovery & High-Availability Failover
- **Standar & Benchmark**:
  - **ISO 22301:2019**: *Business Continuity Management Systems*.
  - **POJK No. 11/POJK.03/2022** tentang Penyelenggaraan Teknologi Informasi oleh Bank Umum (Ketentuan DRC, RPO, dan RTO).
- **Target SLA Ketahanan Sistem**:
  - **Recovery Point Objective (RPO)**: $\le \mathbf{5\text{ Menit}}$ (Toleransi kehilangan data maksimal 5 menit melalui replikasi sinkron antar wilayah).
  - **Recovery Time Objective (RTO)**: $\le \mathbf{15\text{ Menit}}$ (Waktu maksimal pemulihan sistem penuh setelah bencana terjadi).
  - **Topologi Multi-Region**:
    - **Primary Region**: Jakarta (`ap-southeast-3`).
    - **Secondary / Standby Region**: Singapore (`ap-southeast-1`).
    - **Mode Replikasi**: Sinkron (*Synchronous Replication*) dengan replikasi lag aktif terpantau ($< 100\text{ ms}$).
  - **Simulasi Uji Bencana Otomatis (*Automated DR Drill Runs*)**:
    Sistem menyelenggarakan dan mencatat audit pelaksanaan latihan berkala penanganan kegagalan pusat data (*datacenter outage, network blackhole, power failure*) dengan mengukur durasi failover aktual dan integritas record data untuk memastikan status `passed` terhadap target SLA.

---

## 2. SKEMA DATABASE & MODEL (5 TABEL)

Migrasi Phase 20: [`database/migrations/2026_01_20_000001_create_phase20_pdp_and_disaster_recovery_tables.php`](file:///Users/legacygandawijaya/Documents/hris-php/database/migrations/2026_01_20_000001_create_phase20_pdp_and_disaster_recovery_tables.php)

```
+-------------------------------------------------------------------------------------------------+
|                                 DATABASE SCHEMA PHASE 20                                        |
+-------------------------------------------------------------------------------------------------+
| pdp_consent_logs                                                                                |
| - id, tenant_id, company_id, employee_id, consent_type, purpose_description                    |
| - ip_address, user_agent, consented_at, revoked_at, status (granted/revoked)                    |
+-------------------------------------------------------------------------------------------------+
| pdp_dsar_requests                                                                               |
| - id, tenant_id, company_id, employee_id, request_number, request_type                          |
| - reason_or_scope, request_date, completion_deadline, fulfilled_at, handled_by_user_id          |
| - resolution_notes, status (pending/in_progress/fulfilled/rejected)                             |
+-------------------------------------------------------------------------------------------------+
| pdp_breach_incident_logs                                                                        |
| - id, tenant_id, company_id, incident_number, breach_type, detected_at, affected_records_count   |
| - authority_notified_at, subjects_notified_at, incident_description, remediation_measures       |
| - status (detected/investigating/notified/resolved)                                             |
+-------------------------------------------------------------------------------------------------+
| disaster_recovery_configs                                                                       |
| - id, tenant_id, company_id, primary_region, standby_region, replication_mode                   |
| - rpo_target_minutes, rto_target_minutes, current_replication_lag_ms, health_status              |
| - last_heartbeat_at                                                                             |
+-------------------------------------------------------------------------------------------------+
| disaster_recovery_drill_logs                                                                    |
| - id, tenant_id, company_id, drill_date, scenario_type, actual_failover_duration_seconds        |
| - data_loss_records_count, rpo_target_achieved, rto_target_achieved, audit_summary              |
| - lead_auditor_name, status (passed/passed_with_notes/failed)                                   |
+-------------------------------------------------------------------------------------------------+
```

### Model Eloquent:
1. [`app/Models/PdpConsentLog.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/PdpConsentLog.php): Log persetujuan eksplisit pemrosesan data & penarikan persetujuan (*revocation*).
2. [`app/Models/PdpDsarRequest.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/PdpDsarRequest.php): Permohonan pemenuhan hak subjek data (portabilitas, akses, perbaikan, penghapusan).
3. [`app/Models/PdpBreachIncidentLog.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/PdpBreachIncidentLog.php): Pencatatan insiden kebocoran data dengan audit waktu notifikasi 72 jam.
4. [`app/Models/DisasterRecoveryConfig.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/DisasterRecoveryConfig.php): Konfigurasi target RPO, RTO, wilayah utama & siaga, serta status detak jantung (*heartbeat*).
5. [`app/Models/DisasterRecoveryDrillLog.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Models/DisasterRecoveryDrillLog.php): Hasil uji simulasi pemulihan bencana dan verifikasi pencapaian SLA.

---

## 3. LAYANAN SERVICE & LOGIKA BISNIS

### 3.1 PdpComplianceService
File: [`app/Services/Compliance/PdpComplianceService.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Compliance/PdpComplianceService.php)
- `recordConsent()`: Merekam persetujuan eksplisit karyawan dengan stempel waktu, IP, dan user agent.
- `revokeConsent()`: Menandai persetujuan sebagai *revoked* dengan tanggal pencabutan.
- `submitDsarRequest()`: Mendaftarkan permohonan hak PDP, memvalidasi tipe permohonan, dan menetapkan batas waktu penyelesaian 3 hari kerja.
- `exportPortabilityPackage()`: Menghasilkan paket JSON terstruktur data pribadi karyawan (data diri, pekerjaan, riwayat persetujuan) dan menandai status permohonan sebagai `fulfilled`.
- `recordDataBreach()`: Mencatat insiden kebocoran data dan memvalidasi kepatuhan batas waktu pelaporan otoritas dalam 72 jam (Pasal 46).

### 3.2 DisasterRecoveryService
File: [`app/Services/Infrastructure/DisasterRecoveryService.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Infrastructure/DisasterRecoveryService.php)
- `getOrCreateConfig()`: Mengambil atau menginisialisasi konfigurasi failover multi-region (Jakarta vs Singapore).
- `executeDrillRun()`: Menjalankan simulasi latihan failover, membandingkan durasi failover aktual dengan target RTO serta kehilangan record data dengan target RPO untuk menentukan hasil penilaian (`passed` / `passed_with_notes`).
- `triggerFailover()`: Mengalihkan operasional sistem dari wilayah utama ke wilayah siaga dan memperbarui status menjadi `failed_over`.

### 3.3 ZeroTrustSecurityService
File: [`app/Services/Security/ZeroTrustSecurityService.php`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Security/ZeroTrustSecurityService.php)
- `auditDataProtectionSecurity()`: Melakukan audit menyeluruh atas kepatuhan Pasal 35 UU PDP, mengevaluasi status enkripsi AES-256-GCM pada kolom sensitif (NIK, NPWP, Rekening, Gaji), integrasi Cloud KMS / HSM FIPS 140-2 Level 3, dan menghasilkan skor postur keamanan Zero Trust ($\ge 95\%$).

---

## 4. KONTROLER & ROUTE

### Rute Web (`routes/web.php`):
- `GET /company/pdp` &rarr; `PdpComplianceController@index` (Dasbor Privasi & Kepatuhan PDP)
- `POST /company/pdp/consent` &rarr; `PdpComplianceController@storeConsent` (Pencatatan Persetujuan Eksplisit)
- `POST /company/pdp/consent/{id}/revoke` &rarr; `PdpComplianceController@revokeConsent` (Penarikan Persetujuan)
- `POST /company/pdp/dsar` &rarr; `PdpComplianceController@storeDsar` (Pengajuan Permohonan Hak PDP)
- `POST /company/pdp/dsar/{id}/export` &rarr; `PdpComplianceController@exportDsarPortability` (Ekspor Portabilitas Data)
- `POST /company/pdp/breach` &rarr; `PdpComplianceController@storeBreachIncident` (Pencatatan Insiden & Notifikasi 72 Jam)
- `GET /company/disaster-recovery` &rarr; `DisasterRecoveryController@index` (Dasbor Monitoring DRC & Multi-Region)
- `POST /company/disaster-recovery/drill` &rarr; `DisasterRecoveryController@runDrill` (Eksekusi Latihan Bencana)
- `POST /company/disaster-recovery/failover` &rarr; `DisasterRecoveryController@triggerFailover` (Simulasi Failover Darurat)
- `GET /company/security/audit` &rarr; `ZeroTrustSecurityController@audit` (Audit Keamanan Zero Trust & Kriptografi)

---

## 5. TAMPILAN ANTARMUKA PENGGUNA (VIEWS)

1. [`resources/views/company/pdp/index.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/pdp/index.blade.php): Dasbor Kepatuhan UU PDP No. 27/2022, pencatatan persetujuan, manajemen DSAR, dan audit insiden kebocoran 72 jam.
2. [`resources/views/company/pdp/export_dsar.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/pdp/export_dsar.blade.php): Tampilan pratinjau paket portabilitas data terstruktur (*machine-readable JSON*) siap unduh.
3. [`resources/views/company/dr/index.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/dr/index.blade.php): Monitoring real-time DRC Jakarta vs Singapore, indikator RPO & RTO, pengujian drill run, dan simulasi failover.
4. [`resources/views/company/dr/security_audit.blade.php`](file:///Users/legacygandawijaya/Documents/hris-php/resources/views/company/dr/security_audit.blade.php): Dasbor Audit Zero Trust & Kriptografi HSM FIPS 140-2 Level 3 sesuai Pasal 35 UU PDP.

---

## 6. PENGUJIAN OTOMATIS (FEATURE TEST SUITE)

File Pengujian: [`tests/Feature/AdvancedEnterpriseCompliancePhase20Test.php`](file:///Users/legacygandawijaya/Documents/hris-php/tests/Feature/AdvancedEnterpriseCompliancePhase20Test.php)  
Jumlah Test: **12 Metode Pengujian**:
1. `test_pdp_consent_logging_and_revocation`: Memverifikasi pencatatan persetujuan eksplisit dan penarikan persetujuan (*revocation*) sesuai Pasal 9 UU PDP.
2. `test_pdp_dsar_portability_package_export`: Memverifikasi permohonan hak portabilitas dan pembentukan paket JSON terstruktur data pribadi (Pasal 13).
3. `test_pdp_dsar_completion_deadline_enforcement`: Memverifikasi batas waktu penyelesaian maksimal 3 hari kerja sejak pengajuan.
4. `test_pdp_dsar_rejects_invalid_request_type`: Memverifikasi penolakan jenis permohonan hak PDP yang tidak valid (*InvalidArgumentException*).
5. `test_pdp_data_breach_incident_notification_within_seventy_two_hours`: Memverifikasi audit kepatuhan waktu pelaporan insiden kebocoran data dalam $\le 72$ jam (Pasal 46).
6. `test_disaster_recovery_config_initialization_and_targets`: Menguji inisialisasi konfigurasi multi-region Jakarta-Singapore dengan RPO 5 menit dan RTO 15 menit.
7. `test_disaster_recovery_drill_run_success_when_rpo_and_rto_achieved`: Menguji skenario simulasi pemulihan bencana yang memenuhi target SLA dengan status `passed`.
8. `test_disaster_recovery_drill_run_notes_when_rto_or_rpo_exceeded`: Menguji simulasi yang melebihi batas waktu failover atau mengalami data loss dengan status `passed_with_notes`.
9. `test_disaster_recovery_emergency_failover_region_switch`: Menguji pengalihan wilayah utama ke wilayah cadangan (*failover switch*) dan perubahan status kesehatan sistem.
10. `test_zero_trust_security_sensitive_columns_encryption_audit`: Memverifikasi enkripsi AES-256-GCM pada kolom sensitif NIK, NPWP, Rekening, dan Gaji Pokok.
11. `test_zero_trust_security_score_calculation`: Memverifikasi kalkulasi skor keamanan Zero Trust ($\ge 95\%$) dan standar pengelolaan kunci HSM FIPS 140-2.
12. `test_pdp_and_dr_model_relationships`: Memverifikasi integritas seluruh relasi Eloquent antara entitas Perusahaan, Karyawan, Consent, DSAR, Breach Log, DR Config, dan DR Drill Log.

---

## 7. RINGKASAN KEBERHASILAN IMPLEMENTASI

Dengan tuntasnya Phase 20, RiHRIS telah resmi menggenapi **seluruh 20 fase roadmap enterprise HRIS Indonesia**, menjadikannya platform SDM multi-tenant paling lengkap, kokoh, dan patuh hukum di Indonesia yang mencakup:
- Seluruh aspek ketenagakerjaan: UU No. 13/2003, UU Cipta Kerja No. 6/2023, PP 35/2021, PP 36/2021, PP 58/2023 TER PPh 21, BPJS Ketenagakerjaan & Kesehatan, THR Keagamaan, P2K3, dan Flex-Benefits.
- Tata kelola modern: ESG Reporting POJK 51/2017, Emisi Karbon Scope 3, Ergonomi K3 Permenaker 5/2018.
- Ketahanan & Keamanan Enterprise: Arsitektur Multi-Region High Availability Disaster Recovery, Zero Trust HSM Encryption, serta kepatuhan penuh Undang-Undang Pelindungan Data Pribadi (UU No. 27/2022).
