# PROJECT: HRIS SaaS INDONESIA
## Human Resource Information System — Multi Company Platform

Bertindaklah sebagai Senior Software Architect, Senior Laravel Developer, UI/UX Designer, Database Architect, DevOps Engineer, QA Engineer, dan Business Analyst.

Bangun aplikasi HRIS SaaS (Human Resource Information System) bernama RiHRIS yang production-ready, modular, scalable, secure, responsive, dan dapat digunakan oleh banyak perusahaan dalam satu platform.

Aplikasi ditujukan untuk pasar Indonesia. Seluruh fitur HR, payroll, perpajakan, BPJS, THR, kontrak kerja, cuti, absensi, dan administrasi karyawan harus mempertimbangkan kebutuhan serta regulasi ketenagakerjaan Indonesia.

Jangan hanya membuat prototype atau tampilan statis. Seluruh menu, form, tombol, tabel, dashboard, CRUD, autentikasi, workflow, dan laporan harus berfungsi serta terhubung dengan database.

---

# 1. TECHNOLOGY STACK

Gunakan teknologi berikut:

Backend:
- Laravel versi stabil terbaru yang kompatibel dengan PHP.
- PHP versi stabil yang kompatibel.
- Laravel MVC Architecture.
- Laravel Service Layer.
- Laravel Form Request Validation.
- Laravel Policies & Gates.
- Laravel Queue.
- Laravel Scheduler.
- Laravel Notifications.
- REST API untuk integrasi eksternal jika dibutuhkan.

Frontend:
- Laravel Blade sebagai frontend utama.
- Tailwind CSS.
- Alpine.js untuk interaksi frontend.
- Vite untuk asset bundling.
- Chart.js untuk grafik dashboard.
- Gunakan komponen Blade reusable.

Database:
- PostgreSQL versi stabil.
- Gunakan foreign key, index, constraints, dan transaction.
- Gunakan database migration dan seeder.

Infrastructure:
- Docker.
- Docker Compose.
- Nginx.
- PHP-FPM.
- PostgreSQL.
- Redis.
- Queue Worker.
- Scheduler.

Storage:
- Local storage untuk development.
- S3-compatible storage untuk production.
- Mendukung upload foto, dokumen, kontrak, dan lampiran.

Testing:
- PHPUnit atau Pest.
- Laravel Feature Testing.
- Laravel Unit Testing.
- Database integration testing.

Jangan menggunakan React atau Next.js. Frontend harus tetap berada dalam satu aplikasi Laravel menggunakan Blade, Tailwind CSS, dan Alpine.js.

---

# 2. APPLICATION ARCHITECTURE

Gunakan modular monolith architecture.

Pisahkan aplikasi berdasarkan domain bisnis agar setiap modul mudah dikembangkan, diuji, dan dipelihara.

Struktur domain:

- Core
- Tenant Management
- User Management
- Organization Management
- Employee Management
- Attendance
- Leave Management
- Payroll
- Tax Management
- BPJS Management
- Recruitment
- Onboarding
- Performance Management
- Learning & Development
- Reimbursement
- Contract Management
- Employee Relations
- Reporting
- Notification
- Workflow
- CMS
- System Administration

Pisahkan tanggung jawab Controller, Service, Repository jika diperlukan, Model, Request, Policy, Event, Listener, Job, dan Resource.

Jangan menempatkan seluruh business logic di Controller.

---

# 3. MULTI-TENANT SAAS ARCHITECTURE

Aplikasi harus mendukung banyak perusahaan dalam satu aplikasi.

Satu instalasi aplikasi dapat digunakan oleh banyak perusahaan secara independen.

Contoh:

Platform HRIS
├── Company A
│   ├── HR Admin
│   ├── Manager
│   └── Employees
├── Company B
│   ├── HR Admin
│   ├── Manager
│   └── Employees
└── Company C
    ├── HR Admin
    ├── Manager
    └── Employees

Gunakan shared database dengan tenant_id untuk data tenant-owned.

Ketentuan:
- Setiap company mempunyai tenant_id unik.
- Data setiap company harus terisolasi.
- User tidak boleh membaca data company lain.
- Semua query tenant-owned harus menggunakan tenant context.
- Gunakan middleware untuk mendeteksi tenant aktif.
- Gunakan Laravel Global Scope atau mekanisme tenant scoping yang aman.
- Gunakan Policies untuk validasi akses.
- Hindari kebocoran data antar-tenant.
- Semua transaksi harus memvalidasi kepemilikan tenant.
- Super Admin dapat mengakses seluruh company sesuai permission.
- User dapat memiliki akses ke lebih dari satu company jika diizinkan.
- Mendukung custom subdomain per company pada pengembangan lanjutan.
- Mendukung company subscription dan subscription plan.
- Mendukung trial, active, suspended, expired.

Contoh tabel:
- tenants
- companies
- company_settings
- subscriptions
- subscription_plans
- subscription_invoices
- tenant_domains
- tenant_user_access

Pastikan tenant isolation juga diterapkan pada export, import, upload file, report, background jobs, notification, dan API.

---

# 4. UI/UX DESIGN SYSTEM

Buat desain modern, profesional, clean, minimalis, enterprise-grade, dan mudah digunakan.

Color palette:
- Primary: Red (#DC2626)
- Secondary: Dark Red (#991B1B)
- Background: White (#FFFFFF)
- Surface: Light Gray (#F8FAFC)
- Text Primary: Dark Slate (#1E293B)
- Text Secondary: Gray (#64748B)
- Success: Green (#16A34A)
- Warning: Amber (#F59E0B)
- Danger: Red (#DC2626)
- Info: Blue (#2563EB)

Design requirements:
- Responsive desktop, tablet, dan mobile.
- Gunakan consistent spacing.
- Gunakan typography modern.
- Card dengan radius yang proporsional.
- Shadow tipis dan subtle border.
- Gunakan icon library Lucide.
- Form harus rapi dan mudah dibaca.
- Tabel mendukung search, filter, sorting, pagination.
- Modal dan dialog konsisten.
- Gunakan toast notification.
- Loading state.
- Empty state.
- Error state.
- Confirmation dialog untuk destructive action.

PENTING:
Jangan membuat layout terlalu lebar.

Gunakan centered container:
- Landing page: max-width sekitar 1200px.
- Dashboard content: max-width sekitar 1440px.
- Konten harus memiliki margin kiri dan kanan yang seimbang.
- Jangan memaksakan seluruh konten memenuhi lebar layar.
- Sidebar dashboard harus proporsional.
- Pada mobile, sidebar berubah menjadi responsive drawer.

Gunakan design system yang konsisten di seluruh aplikasi.

---

# 5. PUBLIC LANDING PAGE

Ketika pengguna membuka domain utama, tampilkan landing page publik.

Landing page harus memiliki:

A. Navbar
- Logo aplikasi.
- Home.
- Features.
- Modules.
- Pricing.
- About Us.
- Contact.
- Login.
- Register / Get Started.

B. Hero Slider
- Multiple slide.
- Background image atau illustration.
- Heading.
- Subheading.
- CTA button.
- Navigation arrows.
- Pagination dots.
- Responsive image.
- Animasi transisi yang halus.

C. Features Section
- Employee Management.
- Attendance.
- Payroll.
- Leave Management.
- Recruitment.
- Performance Management.
- Employee Self-Service.

D. Modules Section
- Grid modul HRIS.
- Icon.
- Title.
- Description.
- Detail page atau modal.

E. Why Choose Us
- Multi-company.
- Secure.
- Easy to use.
- Automated HR.
- Indonesian HR compliance.

F. Statistics Section
- Companies.
- Employees.
- Attendance.
- Payroll processed.

Jangan menggunakan angka statistik palsu sebagai data aktual. Gunakan placeholder konfigurasi atau data yang benar-benar tersedia.

G. Pricing Section
- Free Trial.
- Starter.
- Professional.
- Enterprise.
- Pricing configurable dari Super Admin.

H. Testimonials
- Customer feedback.
- Company logo.
- Customer name.
- Position.

I. FAQ Section
- Pertanyaan dan jawaban.
- Accordion.

J. Contact Section
- Contact form.
- Email.
- Phone.
- WhatsApp.
- Address.

K. Footer
- Logo.
- Company information.
- Quick links.
- Product links.
- Social media.
- Privacy policy.
- Terms and conditions.
- Copyright.

LANDING PAGE DESIGN:
- Gunakan centered container.
- Maksimum lebar konten 1200px.
- Jangan full width untuk seluruh isi halaman.
- Background section boleh full width, tetapi konten tetap centered.
- Berikan white space yang cukup.
- Jangan membuat slider terlalu tinggi.
- Pastikan layout profesional dan tidak berantakan.

---

# 6. CMS MANAGEMENT — SUPER ADMIN

Seluruh konten landing page harus dapat dikontrol melalui dashboard Super Admin.

Buat CMS dengan fitur CRUD:

- Website configuration.
- Logo management.
- Favicon.
- Navbar menu.
- Hero slider.
- Hero title.
- Hero subtitle.
- Hero image.
- CTA buttons.
- Features.
- HRIS modules.
- About us.
- Statistics.
- Pricing plans.
- Testimonials.
- FAQ.
- Contact information.
- Social media.
- Footer content.
- SEO title.
- SEO description.
- SEO keywords.
- Open Graph image.

Ketentuan:
- Admin dapat menambah, mengedit, menghapus, mengurutkan, dan mengaktifkan/nonaktifkan konten.
- Upload image dengan preview.
- Drag and drop sorting jika memungkinkan.
- Draft dan Published status.
- Perubahan CMS langsung terlihat pada landing page.
- Simpan semua konten dalam database.
- Jangan hardcode konten landing page.

---

# 7. AUTHENTICATION & REGISTRATION

Buat halaman authentication yang modern.

Halaman utama:
- Login.
- Register.
- Forgot Password.
- Reset Password.
- Email Verification.

Login:
- Email.
- Password.
- Remember Me.
- Forgot Password.
- Login button.

Register:
- Company Name.
- Company Email.
- PIC Name.
- Phone Number.
- Password.
- Confirm Password.
- Accept Terms and Conditions.

Proses registrasi SaaS:

1. Pengguna mengisi form registrasi.
2. Sistem melakukan validasi.
3. Sistem membuat tenant baru.
4. Sistem membuat company.
5. Sistem membuat user pemilik perusahaan.
6. Sistem membuat company settings default.
7. Sistem menetapkan role Company Owner.
8. Sistem membuat subscription trial jika sesuai konfigurasi.
9. Sistem mengirim email verifikasi.
10. User diarahkan ke onboarding company.

Jangan memberikan akses Super Admin melalui registrasi publik.

---

# 8. USER ROLES & PERMISSIONS

Gunakan Role Based Access Control (RBAC).

Default roles:

1. Super Admin
2. SaaS Administrator
3. Company Owner
4. Company Admin
5. HR Director
6. HR Manager
7. HR Staff
8. Payroll Staff
9. Finance
10. Department Head
11. Manager
12. Team Leader
13. Employee
14. Auditor

Setiap role memiliki permission granular.

Contoh:
- employee.view
- employee.create
- employee.update
- employee.delete
- attendance.view
- attendance.approve
- payroll.view
- payroll.process
- payroll.approve
- leave.request
- leave.approve
- contract.manage
- reports.export

Buat UI untuk mengelola role dan permission.

---

# 9. DASHBOARD

Buat dashboard berbeda sesuai role.

SUPER ADMIN DASHBOARD:
- Total companies.
- Active companies.
- Trial companies.
- Suspended companies.
- Total users.
- Total employees.
- Subscription revenue.
- Subscription analytics.
- Recent company registrations.
- System health.
- Audit activity.

COMPANY ADMIN DASHBOARD:
- Total employees.
- Active employees.
- New employees.
- Attendance today.
- Absent today.
- Late employees.
- Pending leave requests.
- Pending approvals.
- Upcoming contract expirations.
- Payroll overview.
- Employee distribution.
- Department distribution.

HR DASHBOARD:
- Employee overview.
- Attendance monitoring.
- Leave requests.
- Contract expiration.
- Recruitment pipeline.
- Performance review.
- HR task list.

EMPLOYEE DASHBOARD:
- Personal profile.
- Today's attendance.
- Clock in / Clock out.
- Attendance history.
- Leave balance.
- Leave request.
- Payslip.
- Upcoming holidays.
- Announcements.
- Pending tasks.

Gunakan Chart.js untuk visualisasi data.

Pastikan semua dashboard menggunakan data database aktual dan tenant context yang benar.

---

# 10. CORE HR MODULES

Buat seluruh modul berikut.

## A. Employee Management
- Employee master data.
- Employee ID.
- Personal information.
- Family information.
- Emergency contacts.
- Education history.
- Employment history.
- Employee documents.
- Employee photo.
- Employment status.
- Join date.
- Termination date.
- Employee mutation.
- Promotion.
- Demotion.
- Employee status history.

## B. Organization Management
- Company.
- Branch.
- Division.
- Department.
- Position.
- Job level.
- Job grade.
- Organization chart.
- Reporting structure.

## C. Attendance Management
- Clock in.
- Clock out.
- GPS attendance.
- Selfie attendance.
- QR attendance.
- Shift schedule.
- Work calendar.
- Late arrival.
- Early leave.
- Absent.
- Attendance correction.
- Attendance approval.
- Attendance report.

## D. Leave & Permission Management
- Annual leave.
- Sick leave.
- Maternity leave.
- Paternity leave.
- Unpaid leave.
- Special leave.
- Permission.
- Leave balance.
- Leave accrual.
- Leave request.
- Approval workflow.
- Leave calendar.

## E. Overtime Management
- Overtime request.
- Overtime approval.
- Overtime calculation.
- Overtime history.
- Overtime report.
- Integration with payroll.

## F. Payroll Management
- Salary structure.
- Basic salary.
- Fixed allowance.
- Variable allowance.
- Overtime.
- Bonus.
- Incentive.
- Deduction.
- Loans.
- Payroll period.
- Payroll processing.
- Payroll approval.
- Payslip.
- Payroll history.
- Payroll export.

## G. BPJS Management
- BPJS Kesehatan.
- BPJS Ketenagakerjaan.
- Employee contribution.
- Employer contribution.
- JHT.
- JKK.
- JKM.
- JP.
- JKP.
- Contribution calculation.
- BPJS reports.

## H. Tax Management
- Employee NPWP / NIK.
- Tax status PTKP.
- PPh 21.
- TER calculation.
- Annual tax reconciliation.
- Tax allowance.
- Tax deductions.
- Tax reports.
- Bukti potong integration/export.

Gunakan aturan perpajakan yang berlaku dan dapat diperbarui melalui konfigurasi. Jangan mengunci tarif pajak ke dalam kode program.

## I. THR & Bonus Management
- THR calculation.
- Masa kerja.
- THR eligibility.
- THR payment.
- Bonus calculation.
- Incentive management.
- Payment history.

## J. Recruitment Management
- Job vacancies.
- Candidate database.
- Job applications.
- CV upload.
- Recruitment pipeline.
- Interview schedule.
- Interview assessment.
- Job offer.
- Recruitment reports.

## K. Onboarding & Offboarding
- Employee onboarding.
- Checklist.
- Document verification.
- Asset assignment.
- Probation monitoring.
- Contract renewal.
- Resignation.
- Termination.
- Exit interview.
- Clearance.
- Asset return.

## L. Contract Management
- PKWT.
- PKWTT.
- Internship agreement.
- Contract start date.
- Contract end date.
- Probation.
- Contract renewal.
- Contract reminder.
- Contract history.
- Digital document archive.

## M. Performance Management
- KPI.
- OKR.
- Performance appraisal.
- Self assessment.
- Manager assessment.
- 360-degree feedback.
- Performance review.
- Performance improvement plan.

## N. Learning & Development
- Training.
- Training schedule.
- Training enrollment.
- Learning materials.
- Certification.
- Skill matrix.
- Training evaluation.
- Career development.

## O. Reimbursement & Expense
- Medical reimbursement.
- Transportation.
- Meal.
- Business trip.
- Travel expenses.
- Receipt upload.
- Approval.
- Reimbursement payment.

## P. Employee Self-Service
- Profile management.
- Attendance history.
- Leave request.
- Payslip.
- Document download.
- Personal data update.
- Reimbursement request.
- Announcement.
- HR service request.

## Q. Employee Relations & Disciplinary
- Employee warning letter.
- SP1.
- SP2.
- SP3.
- Disciplinary history.
- Employee grievance.
- Resignation.
- Termination.
- Exit management.

Implementasikan sesuai peraturan ketenagakerjaan Indonesia dan konfigurasi perusahaan.

## R. Employee Engagement
- Announcement.
- Employee survey.
- Feedback.
- Company event.
- Employee recognition.
- HR helpdesk.

## S. HR Analytics & Reporting
- Headcount report.
- Attendance report.
- Payroll report.
- Leave report.
- Employee turnover.
- Recruitment report.
- Performance report.
- Labor cost.
- Employee demographics.

---

# 11. INDONESIAN HR COMPLIANCE

Semua perhitungan dan administrasi HR harus mempertimbangkan regulasi Indonesia.

Buat konfigurasi yang dapat diperbarui untuk:

- UU Ketenagakerjaan beserta perubahannya.
- PP 35 Tahun 2021 dan perubahan/peraturan pelaksana yang berlaku.
- Ketentuan PKWT dan PKWTT.
- Uang kompensasi PKWT.
- Pesangon dan hak PHK.
- THR keagamaan.
- Upah minimum.
- Perhitungan lembur.
- BPJS Kesehatan.
- BPJS Ketenagakerjaan.
- PPh 21.
- Cuti dan istirahat.
- Hari libur nasional.
- Cuti bersama.

Jangan mengasumsikan seluruh perusahaan memiliki aturan yang sama.

Buat configurable policy:
- Company policy.
- Employment policy.
- Leave policy.
- Payroll policy.
- Overtime policy.
- Tax policy.
- BPJS policy.

Setiap perubahan aturan harus memiliki effective date dan version history.

Untuk perhitungan legal, gunakan sumber regulasi resmi yang berlaku. Jangan membuat klaim compliance tanpa validasi.

---

# 12. WORKFLOW & APPROVAL ENGINE

Buat workflow approval yang configurable.

Contoh leave approval:

Employee
  -> Team Leader
  -> Department Head
  -> HR

Contoh payroll approval:

Payroll Staff
  -> HR Manager
  -> Finance
  -> Company Owner

Contoh reimbursement:

Employee
  -> Manager
  -> Finance
  -> Paid

Fitur:
- Multi-level approval.
- Conditional approval.
- Approver delegation.
- Approval history.
- Rejection reason.
- Resubmit.
- Notification.
- Approval deadline.
- Audit trail.

Workflow harus bisa dikonfigurasi tanpa mengubah source code untuk setiap jenis transaksi.

---

# 13. NOTIFICATION SYSTEM

Buat notification center.

Channel:
- In-app notification.
- Email notification.
- WhatsApp integration (opsional).

Event:
- Employee registration.
- Leave request.
- Leave approval.
- Attendance correction.
- Contract expiration.
- Payroll processed.
- Payslip published.
- Overtime approval.
- Reimbursement approval.
- Upcoming probation review.

Gunakan queue untuk proses asynchronous.

---

# 14. DATABASE DESIGN

Gunakan PostgreSQL.

Buat database schema berdasarkan domain.

Minimal master tables:

- tenants
- companies
- company_settings
- branches
- departments
- divisions
- positions
- job_levels
- employees
- employee_profiles
- employee_documents
- employee_employment_histories
- users
- roles
- permissions
- role_permissions
- user_roles

Attendance:
- attendance_logs
- attendance_records
- attendance_corrections
- shifts
- shift_assignments
- work_schedules
- holidays
- overtime_requests

Leave:
- leave_types
- leave_policies
- leave_balances
- leave_requests
- leave_approvals

Payroll:
- salary_components
- employee_salaries
- payroll_periods
- payrolls
- payroll_details
- payroll_adjustments
- payslips

Compliance:
- bpjs_profiles
- tax_profiles
- tax_configurations
- thr_records
- employment_contracts
- employee_warnings
- termination_records

Recruitment:
- job_vacancies
- candidates
- applications
- interviews
- job_offers

Performance:
- kpis
- okrs
- performance_reviews
- performance_assessments

Workflow:
- workflows
- workflow_steps
- approval_requests
- approval_histories

CMS:
- cms_pages
- cms_sliders
- cms_sections
- cms_features
- cms_faqs
- cms_testimonials
- cms_pricing_plans
- cms_settings
- cms_media

SaaS:
- subscription_plans
- subscriptions
- subscription_invoices
- tenant_domains

System:
- notifications
- audit_logs
- system_settings
- integrations

Ketentuan:
- Semua tabel tenant-owned harus memiliki tenant_id.
- Gunakan UUID atau ULID untuk primary key jika sesuai.
- Gunakan timestamps.
- Gunakan soft deletes jika sesuai.
- Tambahkan indexes.
- Tambahkan foreign keys.
- Gunakan numeric/decimal untuk nominal uang, bukan floating point.
- Simpan nominal uang dan mata uang secara konsisten.
- Gunakan transaksi database untuk payroll dan transaksi penting.
- Jangan menyimpan password secara plain text.
- Buat migration, seeder, factory, dan ERD.

Jangan membuat semua tabel sekaligus tanpa memahami relasinya. Susun berdasarkan domain dan dokumentasikan relasi database.

---

# 15. SECURITY

Implementasikan:

- Secure authentication.
- Password hashing.
- CSRF protection.
- XSS protection.
- SQL injection protection.
- Laravel validation.
- RBAC.
- Tenant isolation.
- Rate limiting.
- Secure file upload.
- Audit logs.
- Session security.
- Login attempt protection.
- Password reset.
- Email verification.
- Sensitive data protection.
- Backup strategy.

Data payroll, identitas, pajak, dan dokumen karyawan harus memiliki kontrol akses yang ketat.

---

# 16. DOCKER COMPOSE

Aplikasi harus berjalan menggunakan Docker Compose.

Buat:

- Dockerfile
- docker-compose.yml
- docker-compose.prod.yml
- .env.example
- nginx/default.conf
- PostgreSQL configuration
- Redis configuration
- PHP configuration
- Queue worker configuration
- Scheduler configuration

Services:

1. app
2. nginx
3. postgres
4. redis
5. queue
6. scheduler

Gunakan persistent volume untuk PostgreSQL dan storage.

Sediakan healthcheck untuk service penting.

Pastikan Docker Compose dapat menjalankan aplikasi dengan satu perintah:

docker compose up -d --build

Sediakan perintah:

docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan optimize:clear
docker compose logs -f

Jangan mengasumsikan service sudah tersedia tanpa memeriksa statusnya.

---

# 17. SUPER ADMIN PANEL

Buat dashboard khusus Super Admin.

Fitur:
- Company management.
- Tenant management.
- User management.
- Subscription management.
- Pricing management.
- Trial management.
- CMS management.
- Global configuration.
- System monitoring.
- Audit logs.
- Platform reports.
- Support management.

Super Admin dapat:
- Membuat company.
- Mengedit company.
- Suspend company.
- Activate company.
- Mengelola paket.
- Melihat status subscription.
- Mengelola landing page.
- Mengelola konfigurasi platform.

Semua tindakan administratif harus tercatat dalam audit log.

---

# 18. SAAS SUBSCRIPTION MANAGEMENT

Buat subscription management.

Paket:
- Free Trial.
- Starter.
- Professional.
- Enterprise.

Setiap paket memiliki:
- Harga.
- Durasi.
- Maximum employees.
- Maximum admins.
- Available modules.
- Storage limit.
- Feature limits.
- Trial duration.

Fitur:
- Subscription activation.
- Upgrade.
- Downgrade.
- Renewal.
- Expiration.
- Suspension.
- Invoice history.
- Payment status.

Siapkan payment gateway integration interface agar Midtrans atau Xendit dapat ditambahkan kemudian.

Jangan membuat transaksi pembayaran palsu.

---

# 19. REPORTING & EXPORT

Setiap modul yang memiliki data transaksi harus mendukung laporan.

Format:
- PDF.
- Excel.
- CSV.

Tambahkan:
- Filter tanggal.
- Filter department.
- Filter branch.
- Filter employee.
- Filter status.
- Search.
- Pagination.

Pastikan export tetap mengikuti tenant scope dan permission user.

---

# 20. PROJECT STRUCTURE

Gunakan struktur modular yang mudah dipelihara.

Contoh:

app/
├── Modules/
│   ├── Core/
│   ├── Tenant/
│   ├── Employee/
│   ├── Organization/
│   ├── Attendance/
│   ├── Leave/
│   ├── Payroll/
│   ├── Recruitment/
│   ├── Performance/
│   ├── Compliance/
│   ├── Workflow/
│   ├── CMS/
│   └── Subscription/
├── Http/
├── Models/
├── Providers/
└── Support/

resources/
├── views/
│   ├── layouts/
│   ├── components/
│   ├── public/
│   ├── auth/
│   ├── super-admin/
│   ├── company/
│   ├── hr/
│   └── employee/
├── css/
└── js/

database/
├── migrations/
├── seeders/
└── factories/

docker/
├── nginx/
├── php/
└── postgres/

tests/
├── Feature/
└── Unit/

---

# 21. DEVELOPMENT PHASES

Jangan membangun semua modul sekaligus.

Lakukan secara bertahap.

PHASE 1 — FOUNDATION:
- Laravel setup.
- PostgreSQL.
- Docker Compose.
- Authentication.
- Multi-tenancy.
- RBAC.
- Company registration.
- Super Admin.
- CMS.
- Public landing page.

PHASE 2 — CORE HR:
- Employee Management.
- Organization Management.
- Attendance.
- Shift Management.
- Leave Management.
- Employee Self-Service.

PHASE 3 — PAYROLL:
- Payroll.
- BPJS.
- Tax.
- THR.
- Overtime.
- Reimbursement.
- Contract Management.

PHASE 4 — TALENT:
- Recruitment.
- Onboarding.
- Performance.
- KPI / OKR.
- Training.
- Career Management.

PHASE 5 — ENTERPRISE:
- Workflow Engine.
- Analytics.
- Advanced Reporting.
- Subscription Billing.
- API Integration.
- WhatsApp Integration.
- Advanced Security.

---

# 22. AI DEVELOPMENT RULES

Ikuti aturan berikut selama development:

1. Analisis requirement sebelum menulis kode.
2. Buat architecture plan.
3. Buat database ERD.
4. Buat migration berdasarkan domain.
5. Implementasikan backend dan frontend secara terintegrasi.
6. Jangan membuat dummy button.
7. Jangan membuat halaman kosong.
8. Jangan menggunakan mock data sebagai data production.
9. Pastikan seluruh form memiliki validasi.
10. Pastikan seluruh CRUD berfungsi.
11. Pastikan semua route memiliki middleware dan authorization.
12. Pastikan semua data tenant terisolasi.
13. Pastikan responsive design.
14. Pastikan setiap modul memiliki loading, empty, success, dan error state.
15. Buat automated tests.
16. Jalankan migration dan test.
17. Perbaiki error sebelum melanjutkan.
18. Dokumentasikan perubahan.
19. Jangan menghapus fitur yang sudah berfungsi ketika mengembangkan modul baru.
20. Jangan mengklaim fitur selesai jika belum diuji.

Jika menemukan requirement yang belum jelas, dokumentasikan asumsi dan pilih pendekatan yang aman, modular, dan dapat dikonfigurasi.

---

# 23. REQUIRED DELIVERABLES

Hasil akhir harus mencakup:

1. Source code aplikasi Laravel.
2. PostgreSQL database schema.
3. Database ERD.
4. Docker Compose configuration.
5. Responsive landing page.
6. CMS Super Admin.
7. Login dan Register.
8. Multi-tenant SaaS.
9. Role and Permission.
10. Dashboard berdasarkan role.
11. Modul HRIS.
12. Indonesian HR compliance configuration.
13. REST API documentation.
14. Installation guide.
15. Environment configuration.
16. Database seeders.
17. Automated tests.
18. README.md.
19. Backup and restore guide.
20. Production deployment guide.

---

# 24. FIRST EXECUTION TASK

Mulai development dengan urutan berikut:

STEP 1:
Analisis requirement dan buat Software Requirements Specification (SRS).

STEP 2:
Buat System Architecture Document.

STEP 3:
Buat Multi-Tenant Architecture Design.

STEP 4:
Buat Database ERD dan data dictionary.

STEP 5:
Buat UI/UX design system dan halaman utama.

STEP 6:
Buat Docker Compose dan Laravel foundation.

STEP 7:
Implementasikan PHASE 1 sampai selesai.

STEP 8:
Jalankan seluruh automated tests.

STEP 9:
Perbaiki seluruh error.

STEP 10:
Dokumentasikan hasil implementasi dan lanjutkan ke PHASE berikutnya.

PENTING:
Jangan hanya memberikan rencana atau contoh kode. Setelah analisis selesai, lanjutkan membuat file dan mengimplementasikan aplikasi langsung di workspace project.

Prioritaskan aplikasi yang benar-benar berjalan, bukan sekadar desain visual.

Setiap phase harus menghasilkan fitur yang dapat dijalankan, diuji, dan didemonstrasikan sebelum melanjutkan ke phase berikutnya.

END OF PROJECT SPECIFICATION.