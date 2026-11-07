# DOKUMEN IMPLEMENTASI PHASE 11: STRUKTUR & SKALA UPAH (PERMENAKER 1/2017), SPPD BISNIS, CLEARANCE OFFBOARDING & E-BUPOT 21/26

**Platform:** RiHRIS — Multi-Company SaaS HRIS Indonesia  
**Modul:** Struktur dan Skala Upah Resmi Permenaker No. 1/2017 & Compa-Ratio, Surat Perintah Perjalanan Dinas (SPPD) & Rekonsiliasi Settlement Uang Muka, Matriks Offboarding Clearance 4 Pilar & Surat Pengalaman Kerja (Paklaring) KUHPerdata Pasal 1603p, Generator Formulir 1721-A1 & e-Bupot 21/26 (PER-2/PJ/2024), serta Bagan Struktur Organisasi Interaktif.  
**Status Implementasi:** Production Ready (Completed)

---

## 1. DESKRIPSI & LANDASAN HUKUM INDONESIA

Fase 11 menghadirkan kepatuhan korporasi tingkat lanjut dan tata kelola administrasi operasional bisnis Indonesia:

1. **Struktur dan Skala Upah (Permenaker No. 1 Tahun 2017 & PP No. 36/2021)**:
   - **Kewajiban Hukum**: Pasal 92 UU Ketenagakerjaan jo. Permenaker 1/2017 mewajibkan setiap perusahaan menyusun struktur dan skala upah berdasarkan golongan, jabatan, masa kerja, pendidikan, dan kompetensi.
   - **Metode Skala Bertingkat**:
     - Rentang (Spread): $\text{Spread } \% = \frac{\text{Max Salary} - \text{Min Salary}}{\text{Min Salary}} \times 100\%$
     - Nilai Tengah (Midpoint): $\text{Midpoint} = \frac{\text{Min Salary} + \text{Max Salary}}{2}$
     - Progresi Midpoint antar Golongan: $\text{Progression } \% = \frac{\text{Mid}_N - \text{Mid}_{N-1}}{\text{Mid}_{N-1}} \times 100\%$
   - **Compa-Ratio Engine**:
     $$\text{Compa-Ratio } \% = \frac{\text{Gaji Pokok Karyawan}}{\text{Midpoint Golongan}} \times 100\%$$
     - Klasifikasi:
       - `< 80%`: *Underpaid* (Di bawah upah minimum band golongan)
       - `80% - 99.9%`: *Penetration Zone* (Tahap penyesuaian/pembelajaran)
       - `100.0%`: *Market Parity* (Tepat di nilai tengah pasar)
       - `100.1% - 120%`: *Premium Zone* (Kinerja tinggi / senioritas)
       - `> 120%`: *Red Circle* (Di atas plafon maksimum skala)
   - **Audit Kepatuhan Kemnaker**: Laporan audit otomatis siap ekspor CSV untuk inspeksi Pengawas Ketenagakerjaan.

2. **Surat Perintah Perjalanan Dinas (SPPD) & Settlement Kasbon**:
   - Penerbitan penugasan resmi dengan format penomoran kedinasan Indonesia: `SPPD/TAHUN/BULAN_ROMAWI/NOMOR`.
   - Permohonan dan pencairan Uang Muka Perjalanan Dinas (*Cash Advance*).
   - Pencatatan realisasi pengeluaran: Uang saku harian (*Per Diem*) berdasarkan grade, tiket pesawat/kereta, akomodasi hotel, dan jamuan klien beserta bukti kwitansi.
   - Rekonsiliasi & Settlement Otomatis: Menghitung selisih uang muka vs kwitansi (Kurang bayar = reimbursement ke pegawai, Lebih bayar = pengembalian ke kas perusahaan).
   - Dokumen Cetak SPPD Resmi standar penugasan korporasi & instansi Indonesia.

3. **Matriks Offboarding Clearance 4 Pilar & Surat Paklaring (KUHPerdata 1603p)**:
   - **4 Pilar Departemen**:
     1. **HR**: Penyerahan kartu identitas kerja (ID Card), pengembalian kartu asuransi, dan penutupan exit interview.
     2. **IT**: Penyerahan laptop dan perangkat periferal, pencabutan akun email korporat, revokasi SSO, dan wipe data sensitif.
     3. **Finance**: Pelunasan pinjaman karyawan, penyelesaian kasbon/advance SPPD, dan kartu kredit korporat.
     4. **Atasan Langsung (User)**: Serah terima dokumen pekerjaan, transfer knowledge, dan penyerahan repositori project.
   - **Surat Pengalaman Kerja (Paklaring)**: Diterbitkan otomatis sesuai Pasal 1603p Kitab Undang-Undang Hukum Perdata (KUHPerdata) dengan penomoran baku, keterangan masa kerja, penilaian integritas dan kinerja.

4. **Formulir 1721-A1 & e-Bupot 21/26 (PER-2/PJ/2024)**:
   - Generator Formulir Bukti Potong 1721-A1 lengkap dengan 21 baris rincian baku Direktorat Jenderal Pajak (DJP).
   - Perhitungan otomatis Biaya Jabatan (5% maks. Rp 6.000.000/tahun), iuran pensiun/JHT pekerja, PTKP 2024, PKP, dan PPh 21 Terutang.
   - Generator berkas ekspor CSV e-Bupot 21/26 resmi dengan pembatas titik koma (`;`) untuk unggah masal ke portal DJP Online.
   - Portal mandiri ESS: Pegawai dapat melihat dan mencetak Formulir 1721-A1 miliknya untuk pelaporan SPT Tahunan Orang Pribadi.

5. **Bagan Struktur Organisasi Interaktif (Org Chart Visualizer)**:
   - Rekursif hirarki visual pohon kepemimpinan dari pucuk pimpinan (Direksi) hingga bawahan langsung.
   - Kartu profil interaktif dengan foto, jabatan, departemen, golongan upah, serta headcount total tim langsung dan tidak langsung (*Span of Control*).

---

## 2. PERUBAHAN DATABASE (MIGRATION)

Migration: `database/migrations/2026_01_11_000001_create_phase11_wage_scale_sppd_clearance_and_ebupot_tables.php`

Tabel yang dibuat:
1. `job_grades`: Master golongan jabatan, upah minimum, midpoint, upah maksimum, spread %, progresi midpoint.
2. `business_trip_orders`: Master surat perintah perjalanan dinas (SPPD), durasi, moda transportasi, per diem rate, estimasi anggaran.
3. `business_trip_advances`: Catatan pencairan uang muka dinas dan referensi transfer.
4. `business_trip_expenses`: Item pengeluaran realisasi kwitansi perjalanan dinas.
5. `business_trip_settlements`: Rekonsiliasi audit keuangan dan sisa lebih/kurang bayar.
6. `offboarding_clearances`: Checklist serah terima 4 departemen (HR, IT, Finance, Supervisor).
7. `work_experience_letters`: Surat Pengalaman Kerja (Paklaring) berpenomoran unik.
8. `tax_form1721_a1_records`: Dokumen bukti pemotongan PPh 21 tahunan format e-Bupot 21/26.
9. Modifikasi tabel `employees`: Menambahkan foreign key `job_grade_id`.

---

## 3. ARSITEKTUR LAYANAN (SERVICES LAYER)

- [`WageScaleService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Compensation/WageScaleService.php):
  - `upsertJobGrade`: Kalkulasi otomatis spread margin dan midpoint.
  - `recalculateMidpointProgressions`: Menghitung kenaikan midpoint bertingkat.
  - `performWageScaleAudit`: Audit compa-ratio seluruh pegawai aktif dan klasifikasi status skala.
- [`BusinessTripService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Travel/BusinessTripService.php):
  - `generateSppdNumber`: Penomoran baku format romawi dan sequence bulanan.
  - `createTripOrder`: Registrasi SPPD dan permohonan uang muka.
  - `processApproval`: Otorisasi manajerial dan persetujuan uang muka.
  - `disburseAdvance`: Pencairan dana operasional oleh finance.
  - `addExpense`: Verifikasi bukti kwitansi pengeluaran dinas.
  - `finalizeSettlement`: Rekonsiliasi akhir total uang muka vs kwitansi.
- [`ClearanceService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Offboarding/ClearanceService.php):
  - `initiateClearance`: Registrasi proses offboarding pegawai resign/PHK.
  - `signOffDepartment`: Validasi serah terima departemen HR/IT/Finance/Supervisor.
  - `issuePaklaring`: Penerbitan otomatis Surat Pengalaman Kerja (Paklaring).
- [`Ebupot21ExportService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Tax/Ebupot21ExportService.php):
  - `generateForm1721A1`: Kompilasi 21 komponen rincian bukti potong tahunan.
  - `generateEbupotCsv`: Generator berkas impor masal resmi format DJP Online PER-2/PJ/2024.
- [`OrgChartService`](file:///Users/legacygandawijaya/Documents/hris-php/app/Services/Organization/OrgChartService.php):
  - `buildHierarchy`: Generator pohon hirarki rekursif dengan span of control.

---

## 4. DAFTAR RUTE WEB BARU

```php
// Struktur & Skala Upah Permenaker 1/2017
Route::get('/wage-scales', [WageScaleController::class, 'index'])->name('company.wage-scales.index');
Route::post('/wage-scales/grades', [WageScaleController::class, 'storeGrade'])->name('company.wage-scales.grades.store');
Route::post('/wage-scales/assign/{employee}', [WageScaleController::class, 'assignEmployee'])->name('company.wage-scales.assign');
Route::get('/wage-scales/export', [WageScaleController::class, 'exportAudit'])->name('company.wage-scales.export');

// SPPD & Realisasi Kwitansi Perjalanan Dinas
Route::get('/business-trips', [BusinessTripController::class, 'index'])->name('company.business-trips.index');
Route::post('/business-trips', [BusinessTripController::class, 'store'])->name('company.business-trips.store');
Route::get('/business-trips/{order}', [BusinessTripController::class, 'show'])->name('company.business-trips.show');
Route::get('/business-trips/{order}/print', [BusinessTripController::class, 'print'])->name('company.business-trips.print');
Route::post('/business-trips/{order}/approve', [BusinessTripController::class, 'approve'])->name('company.business-trips.approve');
Route::post('/business-trips/advances/{advance}/disburse', [BusinessTripController::class, 'disburseAdvance'])->name('company.business-trips.advances.disburse');
Route::post('/business-trips/{order}/expenses', [BusinessTripController::class, 'addExpense'])->name('company.business-trips.expenses.store');
Route::post('/business-trips/{order}/settle', [BusinessTripController::class, 'settle'])->name('company.business-trips.settle');

// Offboarding Clearance 4 Pilar & Paklaring
Route::get('/clearances', [OffboardingClearanceController::class, 'index'])->name('company.clearances.index');
Route::post('/clearances', [OffboardingClearanceController::class, 'store'])->name('company.clearances.store');
Route::get('/clearances/{clearance}', [OffboardingClearanceController::class, 'show'])->name('company.clearances.show');
Route::post('/clearances/{clearance}/signoff/{department}', [OffboardingClearanceController::class, 'signOff'])->name('company.clearances.signoff');
Route::post('/clearances/{clearance}/paklaring', [OffboardingClearanceController::class, 'issuePaklaring'])->name('company.clearances.paklaring.issue');
Route::get('/clearances/paklaring/{letter}/print', [OffboardingClearanceController::class, 'printPaklaring'])->name('company.clearances.paklaring.print');

// e-Bupot 21/26 & Formulir 1721-A1 DJP Online
Route::get('/ebupot', [TaxEbupotController::class, 'index'])->name('company.taxes.ebupot.index');
Route::post('/ebupot/generate', [TaxEbupotController::class, 'generate'])->name('company.taxes.ebupot.generate');
Route::get('/ebupot/form1721a1/{record}', [TaxEbupotController::class, 'showForm'])->name('company.taxes.ebupot.form1721a1');
Route::get('/ebupot/export-csv', [TaxEbupotController::class, 'exportCsv'])->name('company.taxes.ebupot.export');

// Bagan Organisasi Interaktif
Route::get('/orgchart', [OrgChartController::class, 'index'])->name('company.orgchart.index');

// Portal ESS Pegawai
Route::post('/ess/business-trip', [EmployeeDashboardController::class, 'submitEssBusinessTrip'])->name('ess.business-trip.submit');
Route::post('/ess/business-trip/{order}/expense', [EmployeeDashboardController::class, 'addEssBusinessTripExpense'])->name('ess.business-trip.expense');
Route::get('/ess/form1721a1/{record}', [EmployeeDashboardController::class, 'viewEssForm1721A1'])->name('ess.form1721a1.show');
Route::get('/ess/paklaring/{letter}', [EmployeeDashboardController::class, 'viewEssPaklaring'])->name('ess.paklaring.show');
```

---

## 5. PENGUJIAN OTOMATIS (AUTOMATED FEATURE TESTS)

Test Suite: [`tests/Feature/AdvancedEnterpriseCompliancePhase11Test.php`](file:///Users/legacygandawijaya/Documents/hris-php/tests/Feature/AdvancedEnterpriseCompliancePhase11Test.php)

10 Kasus Pengujian:
1. `test_can_manage_job_grades_and_calculate_wage_scale_spread_and_midpoint`: Validasi formula spread margin dan progresi midpoint.
2. `test_wage_scale_compa_ratio_audit_accurately_detects_band_position`: Deteksi posisi parity, underpaid, dan red circle.
3. `test_can_create_sppd_business_trip_with_cash_advance`: Validasi nomor SPPD baku dan permohonan uang muka.
4. `test_sppd_approval_and_finance_disbursement_lifecycle`: Alur persetujuan atasan dan pencairan dana finance.
5. `test_sppd_expense_logging_and_settlement_reconciliation`: Rekonsiliasi kwitansi dan perhitungan reimbursement/refund balance.
6. `test_offboarding_clearance_lifecycle_across_4_departments`: Verifikasi checklist 4 pilar (HR, IT, Finance, Supervisor).
7. `test_issue_and_verify_paklaring_work_experience_letter`: Validasi penerbitan Surat Pengalaman Kerja KUHPerdata 1603p.
8. `test_tax_form_1721_a1_generation_and_ebupot_csv_export`: Validasi baris bukti potong 1721-A1 dan format impor masal e-Bupot 21/26.
9. `test_organization_chart_hierarchical_tree_builder`: Validasi pohon hirarki visual dan kalkulasi span of control.
10. `test_ess_business_trip_submission_and_tax_form_download`: Pengajuan dinas dari ESS dan akses cetak dokumen resmi mandiri.
