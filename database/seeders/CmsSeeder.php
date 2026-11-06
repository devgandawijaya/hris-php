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
        // 1. Hero Sliders
        $sliders = [
            [
                'title' => 'Platform HRIS SaaS Terlengkap & Patuh Regulasi Indonesia',
                'subtitle' => 'Kelola multi-perusahaan, absensi GPS, cuti, perhitungan PPh 21 TER 2024, BPJS Kesehatan & Ketenagakerjaan otomatis dalam satu ekosistem.',
                'image_url' => '/images/hero-dashboard-preview.svg',
                'cta_text' => 'Mulai Uji Coba Gratis 14 Hari',
                'cta_url' => '/register',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Kalkulasi Payroll & PPh 21 TER Otomatis Tanpa Pusing',
                'subtitle' => 'Sesuai PMK 168/2023 & PP 35/2021. Potongan pajak bulanan, perhitungan lembur 1/173, dan slip gaji elektronik siap kirim.',
                'image_url' => '/images/hero-payroll-preview.svg',
                'cta_text' => 'Jelajahi Fitur Penggajian',
                'cta_url' => '/#modules',
                'order_index' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($sliders as $slider) {
            CmsSlider::firstOrCreate(['title' => $slider['title']], $slider);
        }

        // 2. Features
        $features = [
            [
                'title' => 'Multi-Company Architecture',
                'description' => 'Satu akun dapat mengelola berbagai anak perusahaan secara independen dengan isolasi data yang aman dan terjamin.',
                'icon' => 'building-2',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'title' => '100% Indonesian Compliance',
                'description' => 'Mendukung UU Cipta Kerja No. 6/2023, PP 35/2021 (lembur & kompensasi PKWT), serta PPh 21 TER Kategori A, B, C.',
                'icon' => 'shield-check',
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Absensi GPS & Selfie Anti-Kecurangan',
                'description' => 'Geofencing radius kantor akurat dengan validasi swafoto real-time di perangkat mobile dan desktop karyawan.',
                'icon' => 'clock',
                'order_index' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Employee Self-Service (ESS)',
                'description' => 'Karyawan dapat mengajukan cuti, klaim lembur, unduh slip gaji, dan memperbarui biodata pribadi secara mandiri.',
                'icon' => 'users',
                'order_index' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($features as $feature) {
            CmsFeature::firstOrCreate(['title' => $feature['title']], $feature);
        }

        // 3. Modules
        $modules = [
            [
                'name' => 'Employee Master & Profile',
                'slug' => 'employee-master',
                'description' => 'Pencatatan data induk karyawan, riwayat pendidikan, riwayat keluarga, arsip dokumen legal, dan status PKWT/PKWTT.',
                'icon' => 'users',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Attendance & Shift Management',
                'slug' => 'attendance-shifts',
                'description' => 'Pengaturan shift kerja fleksibel, toleransi keterlambatan, kalender libur nasional SKB 3 Menteri, dan rekap otomatis.',
                'icon' => 'clock',
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Leave & Permission Engine',
                'slug' => 'leave-permission',
                'description' => 'Manajemen cuti tahunan, cuti melahirkan 3 bulan, izin sakit, dan alur persetujuan bertingkat hingga level Direksi.',
                'icon' => 'calendar-off',
                'order_index' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Payroll, BPJS & PPh 21 TER',
                'slug' => 'payroll-tax',
                'description' => 'Perhitungan gaji bersih, tunjangan, potongan kasbon, 5 program BPJS Ketenagakerjaan, BPJS Kesehatan, dan cetak slip gaji PDF.',
                'icon' => 'banknote',
                'order_index' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Overtime & Reimbursement',
                'slug' => 'overtime-reimbursement',
                'description' => 'Pengajuan lembur sesuai rumus PP 35/2021 dan reimbursement kuitansi digital dengan verifikasi bagian Keuangan.',
                'icon' => 'receipt',
                'order_index' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Contract & Compliance',
                'slug' => 'contract-compliance',
                'description' => 'Pengingat masa berlaku kontrak PKWT, estimasi kompensasi berakhirnya kontrak, dan penerbitan Surat Peringatan (SP 1, 2, 3).',
                'icon' => 'briefcase',
                'order_index' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Performance & Appraisal',
                'slug' => 'performance-appraisal',
                'description' => 'Penilaian berkala Key Performance Indicators (KPI) dan Objectives & Key Results (OKR) terintegrasi dengan kenaikan berkala.',
                'icon' => 'trending-up',
                'order_index' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Organization & Reporting',
                'slug' => 'organization-reporting',
                'description' => 'Bagan struktur organisasi interaktif, headcount analytics, rasio turnover, dan ekspor laporan ke Excel/PDF.',
                'icon' => 'building-2',
                'order_index' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($modules as $module) {
            CmsModule::firstOrCreate(['slug' => $module['slug']], $module);
        }

        // 4. Testimonials
        $testimonials = [
            [
                'author_name' => 'Hendro Prasetyo',
                'author_title' => 'Chief Human Resources Officer',
                'company_name' => 'PT Nusantara Sentra Abadi',
                'company_logo_url' => '',
                'quote' => 'RiHRIS memudahkan pengelolaan 4 anak perusahaan kami dalam satu pintu. Perhitungan PPh 21 TER 2024 dan potongan BPJS-nya akurat 100%!',
                'rating' => 5,
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'author_name' => 'Siti Rahmawati',
                'author_title' => 'Head of People & Culture',
                'company_name' => 'CV Logistik Prima Express',
                'company_logo_url' => '',
                'quote' => 'Absensi GPS sangat membantu tim operasional lapangan kami. Tidak ada lagi kecurangan lokasi dan rekap gaji bulanan selesai 3 hari lebih cepat.',
                'rating' => 5,
                'order_index' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            CmsTestimonial::firstOrCreate(['author_name' => $testimonial['author_name']], $testimonial);
        }

        // 5. FAQs
        $faqs = [
            [
                'question' => 'Apakah RiHRIS sudah mendukung tarif efektif PPh 21 TER terbaru?',
                'answer' => 'Ya, RiHRIS sudah mengimplementasikan penuh PP No. 58 Tahun 2023 dan PMK No. 168 Tahun 2023 untuk Kategori TER A, B, dan C, serta perhitungan masa pajak terakhir (Desember) sesuai Pasal 17 UU HPP.',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'Bagaimana sistem menjamin keamanan data antar perusahaan yang berbeda?',
                'answer' => 'RiHRIS menggunakan arsitektur Multi-Tenant dengan Global Query Scope di level framework, membatasi setiap query dengan tenant_id aktif secara ketat sehingga tidak ada kemungkinan data bocor antar-perusahaan.',
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'Berapa lama masa uji coba gratis dan apa saja batasannya?',
                'answer' => 'Masa uji coba gratis berlaku selama 14 hari dengan kuota hingga 20 karyawan dan akses penuh ke modul presensi, data karyawan, dan pengajuan cuti.',
                'order_index' => 3,
                'is_active' => true,
            ],
            [
                'question' => 'Apakah saya bisa mengekspor laporan gaji dan kehadiran ke format Excel dan PDF?',
                'answer' => 'Tentu saja. RiHRIS menyediakan fitur ekspor rekapitulasi gaji, daftar transfer bank, slip gaji individual, dan rekap kehadiran harian ke format CSV, Excel, dan PDF.',
                'order_index' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            CmsFaq::firstOrCreate(['question' => $faq['question']], $faq);
        }

        // 6. Settings
        $settings = [
            'site_name' => 'RiHRIS',
            'site_tagline' => 'Human Resource Information System — Multi Company Platform',
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

