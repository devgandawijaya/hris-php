<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CmsSlider;
use App\Models\CmsFeature;
use App\Models\CmsModule;
use App\Models\CmsFaq;
use App\Models\CmsTestimonial;
use App\Models\CmsSetting;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Hero Sliders (4 Core Pillars of RiHRIS)
        $sliders = [
            [
                'title' => 'Platform HRIS SaaS Terlengkap & Patuh Regulasi Indonesia',
                'subtitle' => 'Kelola multi-perusahaan, absensi GPS geofence anti-fake GPS, shift 24/7, perhitungan PPh 21 TER 2024, BPJS Kesehatan & Ketenagakerjaan otomatis dalam satu ekosistem terpadu.',
                'image_url' => '/images/hero-dashboard-preview.svg',
                'cta_text' => 'Mulai Uji Coba Gratis 14 Hari',
                'cta_url' => '/register',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Kalkulasi Payroll & PPh 21 TER 2024 Otomatis Sesuai PMK 168/2023',
                'subtitle' => 'Perhitungan pajak bulanan Kategori A, B, C, rekonsiliasi Pasal 17 Desember, 5 program BPJS Ketenagakerjaan & BPJS Kesehatan (Cap Rp 12 Juta), lembur 1/173, serta ekspor file transfer bank (BCA, Mandiri, BRI).',
                'image_url' => '/images/hero-payroll-preview.svg',
                'cta_text' => 'Coba Simulasi Payroll & Pajak',
                'cta_url' => '/#tax-simulator',
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Digital Smart Contract PKWT/PKWTT & Kepatuhan Statutori PP 35/2021',
                'subtitle' => 'Tanda tangan digital e-Seal kriptografi SHA-256 anti-manipulasi, hitung otomatis uang kompensasi berakhirnya kontrak PKWT, Surat Peringatan (SP 1, 2, 3), perundingan Bipartit/Tripartit PHI, dan pelaporan WLTK Kemnaker.',
                'image_url' => '/images/hero-compliance-preview.svg',
                'cta_text' => 'Pelajari Modul Kepatuhan PP 35',
                'cta_url' => '/#why-us',
                'order_index' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'K3 SMK3, Satgas PPKS & Pelindungan Data Pribadi (UU PDP No. 27/2022)',
                'subtitle' => 'Pelaporan kecelakaan kerja & klaim BPJS JKK terintegrasi, brankas rahasia investigasi kekerasan seksual (UU TPKS), manajemen Koperasi Karyawan, hak subjek data DSAR, dan Zero-Trust security RBAC.',
                'image_url' => '/images/hero-enterprise-preview.svg',
                'cta_text' => 'Eksplorasi Modul Enterprise',
                'cta_url' => '/#modules',
                'order_index' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($sliders as $slider) {
            CmsSlider::updateOrCreate(['title' => $slider['title']], $slider);
        }

        // 2. Features
        $features = [
            [
                'title' => 'Multi-Company Architecture',
                'description' => 'Satu akun dapat mengelola berbagai holding & anak perusahaan secara terisolasi penuh dengan tenant_id Global Scope.',
                'icon' => 'building-2',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'title' => '100% Indonesian Compliance',
                'description' => 'Mendukung UU Cipta Kerja No. 6/2023, PP 35/2021 (lembur & kompensasi PKWT), serta PPh 21 TER Kategori A, B, C PMK 168/2023.',
                'icon' => 'shield-check',
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Absensi Geofence & Selfie Anti-Fake GPS',
                'description' => 'Geofencing radius kantor presisi tinggi dengan validasi swafoto real-time dan deteksi mock-location pada perangkat seluler.',
                'icon' => 'clock',
                'order_index' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Employee Self-Service (ESS)',
                'description' => 'Karyawan mengajukan cuti bertingkat, klaim SPL lembur, unduh slip gaji PDF terenkripsi, dan ajukan kasbon koperasi mandiri.',
                'icon' => 'users',
                'order_index' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Smart Contract SHA-256 Digital Seal',
                'description' => 'Penerbitan PKWT/PKWTT elektronik berstempel verifikasi QR Code dan hash integritas dokumen tanpa risiko pemalsuan berkas.',
                'icon' => 'file-check-2',
                'order_index' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'K3 SMK3 & Integrasi JKK BPJS TK',
                'description' => 'Pelaporan insiden K3, matriks investigasi akar masalah 5-Why, dan otomasi klaim santunan JKK ke BPJS Ketenagakerjaan.',
                'icon' => 'alert-triangle',
                'order_index' => 6,
                'is_active' => true,
            ],
            [
                'title' => 'Perlindungan Kerahasiaan Satgas PPKS',
                'description' => 'Ruang pelaporan aman tertutup untuk penanganan kekerasan seksual sesuai mandat Kemendikbudristek & UU TPKS.',
                'icon' => 'lock',
                'order_index' => 7,
                'is_active' => true,
            ],
            [
                'title' => 'Pelindungan Data Pribadi (UU PDP No. 27/2022)',
                'description' => 'Portal Hak Akses Subjek Data (DSAR), audit trail akses berkas medis, dan enkripsi data karyawan setara standar perbankan.',
                'icon' => 'database',
                'order_index' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($features as $feature) {
            CmsFeature::updateOrCreate(['title' => $feature['title']], $feature);
        }

        // 3. Modules (Covering 20 Phases)
        $modules = [
            [
                'name' => 'Employee Master & Profile',
                'slug' => 'employee-master',
                'description' => 'Pencatatan data induk karyawan, riwayat pendidikan, keluarga, dokumen identitas (KTP, NPWP, BPJS), dan status kontrak PKWT/PKWTT.',
                'icon' => 'users',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Attendance GPS & Shift 24/7',
                'slug' => 'attendance-shifts',
                'description' => 'Pengaturan shift bergilir 24 jam, toleransi keterlambatan, geofence radius kantor, kalender libur nasional SKB 3 Menteri, dan QR Kiosk.',
                'icon' => 'clock',
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Leave & Permission Engine',
                'slug' => 'leave-permission',
                'description' => 'Manajemen cuti tahunan, cuti melahirkan 3 bulan, izin sakit dengan unggah surat dokter, dan persetujuan bertingkat hingga Direksi.',
                'icon' => 'calendar-off',
                'order_index' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Payroll, BPJS & PPh 21 TER',
                'slug' => 'payroll-tax',
                'description' => 'Kalkulasi take-home-pay, potongan kasbon, 5 program BPJS TK, BPJS Kesehatan cap Rp 12 juta, PPh 21 TER A/B/C, dan slip gaji PDF password-protected.',
                'icon' => 'banknote',
                'order_index' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Overtime SPL & Reimbursement',
                'slug' => 'overtime-reimbursement',
                'description' => 'Penerbitan Surat Perintah Lembur (SPL) sesuai rumus 1/173 PP 35/2021 dan reimbursement kuitansi digital dengan approval Keuangan.',
                'icon' => 'receipt',
                'order_index' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Digital PKWT & Kompensasi PP 35',
                'slug' => 'contract-compliance',
                'description' => 'E-Signature SHA-256, pengingat kadaluwarsa kontrak, kalkulator kompensasi akhir kontrak PKWT, dan penerbitan SP 1, 2, 3.',
                'icon' => 'file-check-2',
                'order_index' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Talent, KPI & Kirkpatrick L&D',
                'slug' => 'performance-appraisal',
                'description' => 'Penilaian berkala KPI/OKR, review performa 360 derajat, matriks evaluasi pelatihan 4 level Kirkpatrick, dan pemetaan suksesi talenta.',
                'icon' => 'trending-up',
                'order_index' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'WLTK, JKP & Bipartit PHI',
                'slug' => 'legal-statutory',
                'description' => 'Ekspor pelaporan WLTK Kemnaker No. 7/1981, sertifikat klaim JKP BPJS bagi pekerja ter-PHK, dan risalah perundingan Bipartit / Mediasi Tripartit.',
                'icon' => 'scale',
                'order_index' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'K3 Keselamatan Kerja & BPJS JKK',
                'slug' => 'k3-safety',
                'description' => 'Manajemen keselamatan kerja SMK3 PP 50/2012, pelaporan insiden darurat, investigasi 5-Why, dan pengajuan santunan JKK BPJS.',
                'icon' => 'shield-alert',
                'order_index' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Satgas PPKS & Kerahasiaan TPKS',
                'slug' => 'ppks-protection',
                'description' => 'Saluran pelaporan kekerasan seksual dengan enkripsi end-to-end, perlindungan identitas korban/saksi, dan manajemen satgas khusus.',
                'icon' => 'lock',
                'order_index' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Koperasi Karyawan & Pinjaman',
                'slug' => 'employee-cooperative',
                'description' => 'Simpan pinjam internal karyawan, auto-deduction angsuran langsung pada slip gaji, dan laporan dividen SHU akhir tahun buku.',
                'icon' => 'wallet',
                'order_index' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Pelindungan Data Pribadi (UU PDP 27/2022)',
                'slug' => 'pdp-compliance',
                'description' => 'Audit log hak subjek data (DSAR), persetujuan pemrosesan data (consent), penunjukan DPO, dan kepatuhan transfer data lintas entitas.',
                'icon' => 'shield',
                'order_index' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($modules as $module) {
            CmsModule::updateOrCreate(['slug' => $module['slug']], $module);
        }

        // 4. Testimonials
        $testimonials = [
            [
                'author_name' => 'Hendro Prasetyo',
                'author_title' => 'Chief Human Resources Officer',
                'company_name' => 'PT Nusantara Sentra Abadi (Holding 5 Entitas)',
                'company_logo_url' => '',
                'quote' => 'RiHRIS memudahkan kami mengelola 1.200 karyawan di 5 anak perusahaan dalam satu dashboard. Perhitungan PPh 21 TER 2024 dan potongan 5 program BPJS-nya akurat 100% tanpa selisih sepeser pun!',
                'rating' => 5,
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'author_name' => 'Siti Rahmawati',
                'author_title' => 'Head of People & Culture',
                'company_name' => 'CV Logistik Prima Express',
                'company_logo_url' => '',
                'quote' => 'Absensi Geofence GPS & Swafoto memecahkan masalah kecurangan presensi driver dan staf logistik lapangan. Rekap payroll bulanan yang dulunya butuh 5 hari kini selesai dalam hitungan menit.',
                'rating' => 5,
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'author_name' => 'Bambang Triyono, S.H.',
                'author_title' => 'VP Legal & Industrial Relations',
                'company_name' => 'PT Megah Manufaktur Indonesia',
                'company_logo_url' => '',
                'quote' => 'Fitur kalkulasi kompensasi PKWT PP 35/2021 dan e-Sign ber-hash SHA-256 sangat menyelamatkan audit ketenagakerjaan kami. Laporan WLTK Kemnaker juga terbit otomatis tanpa repot rekap manual.',
                'rating' => 5,
                'order_index' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            CmsTestimonial::updateOrCreate(['author_name' => $testimonial['author_name']], $testimonial);
        }

        // 5. FAQs
        $faqs = [
            [
                'question' => 'Apakah RiHRIS sudah mengadopsi tarif efektif PPh 21 TER terbaru sesuai PMK 168/2023?',
                'answer' => 'Ya, RiHRIS sudah mengimplementasikan penuh PP No. 58 Tahun 2023 dan PMK No. 168 Tahun 2023 untuk Kategori TER A, B, dan C berdasarkan status PTKP. Sistem juga otomatis melakukan rekonsiliasi masa pajak terakhir (Desember) menggunakan tarif Pasal 17 UU HPP serta memfasilitasi ekspor formulir 1721-A1 dan skema e-Bupot.',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'Bagaimana sistem mengisolasi data antar anak perusahaan (Multi-Tenant)?',
                'answer' => 'RiHRIS menggunakan arsitektur Multi-Tenant dengan Global Query Scope di level framework Eloquent ORM. Setiap query database secara otomatis dibatasi oleh tenant_id aktif pengguna. Manajemen grup holding dapat memonitor seluruh anak perusahaan, sementara admin cabang hanya dapat mengakses perusahaannya sendiri.',
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'Bagaimana kepatuhan kompensasi PKWT dan lembur menurut PP 35/2021?',
                'answer' => 'RiHRIS secara otomatis menghitung kewajiban uang kompensasi berakhirnya kontrak PKWT berdasarkan formula resmi (Masa Kerja / 12 x 1 Bulan Upah). Selain itu, kalkulator lembur menerapkan ketentuan resmi 1/173 upah sebulan dengan tarif bertingkat 1.5x jam pertama dan 2x jam berikutnya serta tarif lembur hari libur resmi.',
                'order_index' => 3,
                'is_active' => true,
            ],
            [
                'question' => 'Bagaimana RiHRIS menjamin kepatuhan Pelindungan Data Pribadi (UU No. 27 Tahun 2022)?',
                'answer' => 'RiHRIS dirancang siap regulasi PDP: menerapkan prinsip enkripsi data saat diam (at-rest) dan transit (TLS/HTTPS), logging audit akses terhadap data sensitif karyawan (gaji, data medis, nomor KTP/KK), serta modul resmi Subject Access Request (DSAR) agar karyawan dapat meminta hak salinan atau pembaruan datanya.',
                'order_index' => 4,
                'is_active' => true,
            ],
            [
                'question' => 'Apakah ada batasan fitur selama masa uji coba gratis 14 hari?',
                'answer' => 'Masa uji coba gratis 14 hari memberikan akses penuh untuk mencoba seluruh modul operasional presensi, cuti, pengarsipan data karyawan, simulasi payroll, hingga portal ESS karyawan, tanpa dipungut biaya dan tanpa perlu kartu kredit di awal pendaftaran.',
                'order_index' => 5,
                'is_active' => true,
            ],
            [
                'question' => 'Apakah bisa dilakukan integrasi transfer bank payroll secara massal (bulk disbursal)?',
                'answer' => 'Ya. RiHRIS menghasilkan berkas transfer penggajian siap unggah sesuai format standar bank-bank besar Indonesia seperti Bank Central Asia (BCA Payroll / KlikBCA Bisnis), Bank Mandiri (MCM Mandiri Corporate), Bank Rakyat Indonesia (BRI Cash Management), dan BNI.',
                'order_index' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            CmsFaq::updateOrCreate(['question' => $faq['question']], $faq);
        }

        // 6. Settings
        $settings = [
            'site_name' => 'RiHRIS',
            'site_tagline' => 'Human Resource Information System — Multi Company Platform Patuh Regulasi Indonesia',
            'contact_email' => 'halo@rihris.id',
            'contact_phone' => '+62 21 8062 8900',
            'contact_whatsapp' => '+62 812 8899 7700',
            'contact_address' => 'One Pacific Place Suite 1500, SCBD Jl. Jenderal Sudirman Kav. 52-53, Jakarta Selatan 12190',
            'seo_title' => 'RiHRIS — Software HRIS SaaS Multi-Company Patuh Regulasi Indonesia',
            'seo_description' => 'Aplikasi HRIS SaaS Indonesia: Kelola absensi GPS, cuti, penggajian, PPh 21 TER, BPJS Kesehatan & Ketenagakerjaan dengan akurat.',
        ];

        foreach ($settings as $key => $val) {
            CmsSetting::set($key, $val);
        }
    }
}
