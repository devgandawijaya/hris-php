# SOFTWARE REQUIREMENTS SPECIFICATION (SRS)
## RiHRIS — Human Resource Information System (Multi-Company SaaS Indonesia)

**Document Version:** 1.0.0  
**Status:** Approved / Architecture Baseline  
**Target Market:** Republik Indonesia  
**Compliant with:** UU No. 13/2003, UU No. 6/2023 (Cipta Kerja), PP No. 35/2021, PP No. 36/2021, PP No. 51/2023, PMK No. 168/2023, PP No. 58/2023 (PPh 21 TER), BPJS Ketenagakerjaan & BPJS Kesehatan Regulations.

---

## 1. PENDAHULUAN & TUJUAN PRODUK

### 1.1 Latar Belakang
Pengelolaan Sumber Daya Manusia (SDM) di Indonesia memiliki kompleksitas regulasi yang tinggi, meliputi perhitungan Pajak Penghasilan (PPh 21) dengan mekanisme Tarif Efektif Rata-Rata (TER), iuran Badan Penyelenggara Jaminan Sosial (BPJS Ketenagakerjaan dan BPJS Kesehatan), Tunjangan Hari Raya (THR) Keagamaan, regulasi Perjanjian Kerja Waktu Tertentu (PKWT) dan Tidak Tertentu (PKWTT), uang kompensasi PKWT sesuai PP 35/2021, hingga pesangon Pemutusan Hubungan Kerja (PHK).

**RiHRIS** adalah platform Software-as-a-Service (SaaS) Multi-Tenant yang dirancang secara khusus untuk memenuhi seluruh regulasi ketenagakerjaan dan perpajakan Indonesia, dengan arsitektur modular monolith yang tangguh, aman, scalable, dan modern.

### 1.2 Tujuan Utama
1. **Multi-Tenancy Sejati**: Mengakomodasi ribuan entitas perusahaan independen dalam satu platform terpadu dengan isolasi data tingkat tinggi (`tenant_id`).
2. **Kepatuhan Regulasi Indonesia (100% Indonesian HR Compliance)**: Perhitungan otomatis PPh 21 TER (Kategori A, B, C), BPJS Kesehatan, 5 program BPJS Ketenagakerjaan (JKK, JKM, JHT, JP, JKP), THR, lembur PP 35/2021, dan kompensasi PKWT.
3. **End-to-End Modular Monolith**: Menghubungkan seluruh siklus hidup karyawan mulai dari rekrutmen, onboarding, kehadiran geolokasi, manajemen cuti, penggajian (payroll), evaluasi kinerja (KPI/OKR), hingga terminasi/offboarding.
4. **Self-Service & Delegated Approvals**: Portal Employee Self-Service (ESS) berbasis web responsif yang memudahkan absensi selfie/GPS, pengajuan cuti, lembur, klaim reimbursement, dan slip gaji elektronik.
5. **Content Management System (CMS) Terintegrasi**: Pengelolaan landing page publik dinamis (slider, modul, harga, testimoni, FAQ) langsung dari Super Admin panel tanpa perubahan kode sumber.

---

## 2. PENGGUNA SISTEM (USER PERSONAS & ROLES)

Sistem mengadopsi Role-Based Access Control (RBAC) dengan 14 peran standar berjenjang:

| Role ID | Role Name | Lingkup Akses & Tanggung Jawab Utama |
|---|---|---|
| 1 | **Super Admin** | Akses platform global, manajemen penyewa (tenants), paket langganan (subscription), CMS landing page, audit log platform, dan konfigurasi sistem. |
| 2 | **SaaS Administrator** | Pengawasan teknis platform, monitor job queue, kesehatan sistem, dan integrasi payment gateway. |
| 3 | **Company Owner** | Pemilik legal perusahaan tenant. Memiliki akses penuh terhadap konfigurasi perusahaan, persetujuan payroll final, dan tagihan SaaS. |
| 4 | **Company Admin** | Administrator utama tenant. Mengelola struktur organisasi, master data karyawan, pengaturan absensi, dan kebijakan internal. |
| 5 | **HR Director** | Eksekutif HR. Melihat analitik strategis, metrik turnover, perencanaan anggaran gaji, dan persetujuan kebijakan level tinggi. |
| 6 | **HR Manager** | Manajer operasional HR. Menyetujui cuti, lembur, mutasi, kontrak kerja, SP/sanksi disipliner, dan pipeline rekrutmen. |
| 7 | **HR Staff** | Operator harian HR. Mengelola biodata karyawan, verifikasi dokumen, rekap presensi, dan logistik pelatihan. |
| 8 | **Payroll Staff** | Spesialis kompensasi & benefit. Memproses kalkulasi gaji bulanan, BPJS, PPh 21, potongan kasbon, lembur, dan pencetakan slip gaji. |
| 9 | **Finance** | Keuangan perusahaan. Verifikasi transfer bank payroll, approval klaim reimbursement, dan audit pembayaran pajak/BPJS. |
| 10 | **Department Head** | Kepala departemen. Menyetujui KPI tim, permohonan cuti, permohonan lembur, dan evaluasi probation karyawan baru. |
| 11 | **Manager** | Manajer lini. Memantau kehadiran harian tim, delegasi tugas, dan penilaian kinerja semesteran/tahunan. |
| 12 | **Team Leader** | Pemimpin regu kerja. Validasi awal lembur, presensi harian shift, dan izin kerja harian. |
| 13 | **Employee** | Karyawan umum. Akses portal ESS: clock in/out GPS, cek jatah cuti, ajukan reimbursement, unduh slip gaji, update data keluarga. |
| 14 | **Auditor** | Akses baca (read-only) terhadap catatan kepatuhan ketenagakerjaan, bukti potong pajak, log absensi, dan laporan penggajian. |

---

## 3. SPESIFIKASI KEPATUHAN REGULASI INDONESIA

### 3.1 Pajak Penghasilan Pasal 21 (PPh 21 TER 2024 - PMK 168/2023 & PP 58/2023)
Sistem mengimplementasikan skema perhitungan pajak PPh 21 terbaru:
1. **Penetapan Kategori TER Bulanan**:
   - **TER Kategori A**: TK/0 (PTKP Rp 54.000.000), TK/1 (PTKP Rp 58.500.000), K/0 (PTKP Rp 58.500.000). Tarif bertingkat 0% sampai 34%.
   - **TER Kategori B**: TK/2 (Rp 63.000.000), TK/3 (Rp 67.500.000), K/1 (Rp 63.000.000), K/2 (Rp 67.500.000). Tarif bertingkat 0% sampai 34%.
   - **TER Kategori C**: K/3 (PTKP Rp 72.000.000). Tarif bertingkat 0% sampai 34%.
2. **Kalkulasi Masa Pajak Bulanan (Januari - November)**:
   $$\text{PPh 21 Bulanan} = \text{Penghasilan Bruto Sebulan} \times \text{Tarif TER Sesuai Layer}$$
3. **Kalkulasi Masa Pajak Terakhir (Desember / Masa Berhenti Bekerja)**:
   - Dihitung menggunakan Tarif Pasal 17 ayat (1) huruf a UU HPP atas Penghasilan Kena Pajak (PKP) disetahunkan dikurangi akumulasi PPh 21 yang telah dipotong pada masa-masa sebelumnya.
   - Penghasilan Bruto Disetahunkan - Biaya Jabatan (5%, maks Rp 6.000.000/tahun atau Rp 500.000/bulan) - Iuran Pensiun/JHT yang dibayar karyawan - PTKP = PKP.
   - Lapisan Tarif Pasal 17:
     - Rp 0 - Rp 60.000.000: 5%
     - > Rp 60.000.000 - Rp 250.000.000: 15%
     - > Rp 250.000.000 - Rp 500.000.000: 25%
     - > Rp 500.000.000 - Rp 5.000.000.000: 30%
     - > Rp 5.000.000.000: 35%
4. **Pegawai Tanpa NPWP/NIK Valid**: Penerapan surcharge tarif 20% lebih tinggi jika dikonfigurasikan atau penyesuaian NIK sebagai NPWP 16 digit.

### 3.2 BPJS Ketenagakerjaan (5 Program Wajib)
1. **JHT (Jaminan Hari Tua)**: 5.70% total (3.70% Ditanggung Pemberi Kerja, 2.00% Ditanggung Pekerja).
2. **JKK (Jaminan Kecelakaan Kerja)**: 100% Ditanggung Pemberi Kerja berdasarkan tingkat risiko kelompok usaha perusahaan (Sangat Rendah 0.24%, Rendah 0.54%, Sedang 0.89%, Tinggi 1.27%, Sangat Tinggi 1.74%).
3. **JKM (Jaminan Kematian)**: 0.30% Ditanggung Pemberi Kerja.
4. **JP (Jaminan Pensiun)**: 3.00% total (2.00% Ditanggung Pemberi Kerja, 1.00% Ditanggung Pekerja) dengan batas upah tertinggi (ceiling) yang dapat diperbarui secara dinamis per tahun (misal Rp 10.042.300/bulan).
5. **JKP (Jaminan Kehilangan Pekerjaan)**: Didanai iuran pemerintah dan rekomposisi iuran JKK/JKM sesuai regulasi yang berlaku.

### 3.3 BPJS Kesehatan
1. **Iuran Total 5%**: 4.00% Ditanggung Pemberi Kerja, 1.00% Ditanggung Pekerja.
2. **Batas Upah Maksimal**: Batas upah tertinggi perhitungan iuran BPJS Kesehatan dikonfigurasikan secara dinamis (default Rp 12.000.000). Maksimal iuran pekerja Rp 120.000/bulan, pemberi kerja Rp 480.000/bulan.
3. **Cakupan Tanggungan**: Meliputi 5 orang (pekerja, pasangan sah, dan 3 anak yang sah sesuai ketentuan).

### 3.4 Tunjangan Hari Raya (THR) Keagamaan (Permenaker No. 6/2016)
1. Masa kerja $\ge 12$ bulan terus menerus: 1 (satu) bulan upah penuh (Upah Pokok + Tunjangan Tetap).
2. Masa kerja 1 bulan s.d. < 12 bulan: Dihitung proporsional:
   $$\text{THR Pro-rata} = \frac{\text{Masa Kerja (Bulan)}}{12} \times 1\text{ Bulan Upah}$$
3. Waktu Pembayaran: Wajib dibayarkan paling lambat 7 hari sebelum hari raya keagamaan karyawan.

### 3.5 Perhitungan Lembur PP No. 35/2021
1. **Upah Sejam Lembur**:
   $$\text{Upah Sejam} = \frac{1}{173} \times \text{Upah Sebulan (Gaji Pokok + Tunjangan Tetap)}$$
2. **Hari Kerja Biasa**:
   - Jam ke-1: $1.5 \times \text{Upah Sejam}$
   - Jam ke-2 dan seterusnya: $2.0 \times \text{Upah Sejam}$
3. **Hari Libur Resmi / Istirahat Mingguan (6 Hari Kerja)**:
   - Jam ke-1 s.d. jam ke-7: $2.0 \times \text{Upah Sejam}$
   - Jam ke-8: $3.0 \times \text{Upah Sejam}$
   - Jam ke-9 & ke-10: $4.0 \times \text{Upah Sejam}$
4. **Hari Libur Resmi / Istirahat Mingguan (5 Hari Kerja)**:
   - Jam ke-1 s.d. jam ke-8: $2.0 \times \text{Upah Sejam}$
   - Jam ke-9: $3.0 \times \text{Upah Sejam}$
   - Jam ke-10 s.d. ke-12: $4.0 \times \text{Upah Sejam}$

### 3.6 Uang Kompensasi PKWT (Pasal 61A UU Ketenagakerjaan & PP 35/2021)
1. Diberikan saat berakhirnya jangka waktu PKWT bagi pekerja yang telah mempunyai masa kerja minimal 1 bulan terus-menerus.
2. Rumus:
   $$\text{Uang Kompensasi} = \frac{\text{Masa Kerja (Bulan)}}{12} \times 1\text{ Bulan Upah}$$

---

## 4. RUANG LINGKUP MODUL FUNGSIONAL

### MODUL 1: Core & Multi-Tenant Management
- Multi-company registration dengan isolasi instan (`tenant_id`).
- Company settings (Logo, jam kerja mingguan 40 jam, toleransi keterlambatan, metode pembulatan gaji).
- Multi-domain / subdomain routing siap pakai.
- Subscription billing tracker (Starter, Professional, Enterprise, Free Trial).

### MODUL 2: User & Role Management (RBAC)
- Manajemen pengguna tenant dan super admin.
- 14 granular default roles dengan matriks permissions.
- Audit trail untuk setiap mutasi izin dan login user.

### MODUL 3: Organization Structure Management
- Hirarki organisasi: Company -> Branch -> Division -> Department -> Position -> Job Level / Grade.
- Visual Organization Chart interaktif dengan reporting lines yang jelas.

### MODUL 4: Employee Information Management
- Biodata komprehensif (NIK KTP, NPWP 16 digit, No BPJS KTK, No BPJS KS, status PTKP, status pernikahan, bank rekening).
- Riwayat keluarga, kontak darurat, riwayat pendidikan, riwayat pekerjaan sebelumnya.
- Manajemen berkas digital (KTP, CV, Paklaring, Ijazah, Kontrak) dengan secure tenant storage isolation.
- Catatan mutasi, promosi, demosi, dan status kepegawaian (Tetap, Kontrak, Magang, Probation).

### MODUL 5: Attendance & Working Schedule
- Presensi mobile/web dengan validasi GPS Radius Geofencing (koordinat kantor dan radius meter toleransi).
- Presensi foto/selfie verification.
- Manajemen shift (Shift Pagi, Siang, Malam, Normal) dan roster kerja mingguan/bulanan.
- Deteksi otomatis keterlambatan (late arrival) dan pulang lebih cepat (early out).
- Form pengajuan koreksi presensi (lupa clock in/out) dengan alur persetujuan bertingkat.

### MODUL 6: Leave & Absence Management
- Master tipe cuti (Cuti Tahunan, Sakit Berbayar, Cuti Melahirkan 3 bulan, Cuti Ayah/Istri Melahirkan 2 hari, Cuti Menikah 3 hari, Izin Kematian Keluarga, Unpaid Leave).
- Mekanisme accrual cuti tahunan (default 12 hari per tahun setelah 1 tahun masa kerja).
- Kalender cuti bersama dan hari libur nasional (SKB 3 Menteri) yang dapat di-update per tahun.
- Form pengajuan cuti terintegrasi kalender dan alur approval berjenjang.

### MODUL 7: Overtime & Reimbursement
- Pengajuan Surat Perintah Lembur (SPL) pra-lembur dan realisasi paska-lembur.
- Perhitungan nominal lembur otomatis terhubung ke komponen payroll.
- Klaim reimbursement (kesehatan, transportasi, akomodasi, kacamata) dengan upload foto kuitansi/struk.

### MODUL 8: Payroll, Tax, & BPJS Engine
- Salary component builder (Gaji Pokok, Tunjangan Jabatan, Tunjangan Makan, Tunjangan Transport, Bonus, Insentif, Potongan Absensi, Potongan Kasbon, Potongan BPJS & Pajak).
- Payroll period run (Cut-off date presensi dan tanggal gajian bulanan).
- Automatic bulk payroll calculation dalam transaksi database terisolasi.
- Slip Gaji PDF berformat rapi dan terproteksi.
- Ekspor rekap gaji bank (BCA, Mandiri, BNI, BRI) format CSV/Excel.

### MODUL 9: Contract & Compliance Management
- Monitor jatuh tempo PKWT (peringatan H-60, H-30, H-7 sebelum kontrak habis).
- Perhitungan otomatis estimasi uang kompensasi PKWT PP 35/2021.
- Penerbitan Surat Peringatan bertingkat (SP 1, SP 2, SP 3) dengan masa berlaku 6 bulan sesuai UU.
- Pencatatan PHK dan kalkulasi hak pesangon (UP), uang penghargaan masa kerja (UPMK), dan uang penggantian hak (UPH).

### MODUL 10: Performance Management (KPI & OKR)
- Template KPI per jabatan dan bobot persentase penilaian.
- Self-assessment karyawan dan manager-review berbobot.
- Performance Improvement Plan (PIP) bagi nilai di bawah standar.

### MODUL 11: Recruitment & Onboarding
- Job vacancy posting internal dan publik.
- Candidate database dan screening status pipeline (Applied -> Shortlisted -> Interview -> Offered -> Hired).
- Checklist onboarding otomatis karyawan baru (penyerahan aset laptop/ID card, upload dokumen legal, pengenalan tim).

### MODUL 12: Content Management System (CMS) & Landing Page
- Pengelolaan penuh landing page publik via Super Admin UI.
- CRUD Hero Slider (judul, subjudul, gambar, link CTA, urutan).
- CRUD Modul HRIS, Fitur Unggulan, Paket Harga SaaS, Testimoni Klien, FAQ Accordion, dan Form Kontak Masuk.
- Konfigurasi SEO Meta Tags, Logo, Favicon, dan info kontak perusahaan pengembang.

---

## 5. PERSYARATAN NON-FUNGSIONAL (NFR)

1. **Security & Data Privacy**:
   - Enkripsi password menggunakan `bcrypt` dengan cost factor 12.
   - Tenant isolation enforced via Global Query Scopes & Middleware pada setiap layer query Eloquent.
   - Proteksi ketat terhadap CSRF, XSS, SQL Injection, dan Path Traversal.
   - Audit trail lengkap menyimpan IP address, User Agent, Timestamp, Tenant ID, dan delta perubahan data (old values vs new values).
2. **Performance & Scalability**:
   - Query response time rata-rata di bawah 150ms untuk halaman operasional standar.
   - Pemanfaatan composite index pada kombinasi kolom `(tenant_id, created_at)`, `(tenant_id, employee_id)`, dll.
   - Seluruh kalkulasi nominal uang wajib menggunakan tipe data `NUMERIC(15,2)` untuk mencegah pembulatan floating point error.
3. **Responsive UI/UX Standards**:
   - Mengikuti desain palet warna primer Red `#DC2626`, Dark Red `#991B1B`, Slate `#1E293B`, Surface `#F8FAFC`.
   - Layout landing page menggunakan container terpusat dengan `max-width: 1200px`.
   - Layout dashboard menggunakan container terpusat dengan `max-width: 1440px`.
   - Mobile navigation berupa slide drawer yang smooth tanpa pergeseran horizontal layout.
   - Seluruh form dilengkapi feedback validasi real-time, alert konfirmasi aksi destruktif, empty states, dan loading skeletons.

---
*Dokumen ini merupakan acuan mutlak bagi seluruh implementasi kode program RiHRIS.*

