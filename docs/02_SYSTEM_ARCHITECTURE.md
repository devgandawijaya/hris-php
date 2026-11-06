# SYSTEM ARCHITECTURE DOCUMENT (SAD)
## RiHRIS — Modular Monolith Architecture Blueprint

**Version:** 1.0.0  
**Pattern:** Modular Monolith with Domain-Driven Structure  
**Language & Framework:** PHP 8.2+ / Laravel 11  
**Frontend Engine:** Laravel Blade + Tailwind CSS + Alpine.js + Vite  
**Database & Cache:** PostgreSQL 16+ & Redis 7+

---

## 1. ARSITEKTUR KONSEPTUAL TINGGI (HIGH LEVEL ARCHITECTURE)

RiHRIS mengadopsi pendekatan **Modular Monolith Architecture**. Seluruh fungsionalitas berada dalam satu kesatuan basis kode Laravel yang deployable secara mandiri, namun dipartisi secara ketat berdasarkan bounded context domain bisnis.

```
                             +-------------------------------+
                             |    Client Browser / Mobile    |
                             +---------------+---------------+
                                             |
                                    HTTPS / Port 443
                                             |
                             +---------------v---------------+
                             |       Nginx Reverse Proxy     |
                             +---------------+---------------+
                                             |
                                  FastCGI / Port 9000
                                             |
                   +-------------------------v-------------------------+
                   |                 Laravel Application               |
                   |                                                   |
                   |  [Http Routing & Global Security Middleware]     |
                   |  [Tenant Identification & Context Scoping]       |
                   |  [Authentication & RBAC Permission Guards]        |
                   |                                                   |
                   |  +---------------------------------------------+  |
                   |  |          MODULAR DOMAIN BOUNDARIES          |  |
                   |  |                                             |  |
                   |  |  [Core]        [Tenant]     [CMS]           |  |
                   |  |  [User/RBAC]   [Org]        [Employee]      |  |
                   |  |  [Attendance]  [Leave]      [Payroll]       |  |
                   |  |  [Compliance]  [Workflow]   [Subscription]  |  |
                   |  +---------------------------------------------+  |
                   |                                                   |
                   |  [Service Layer: PPh21, BPJS, Overtime, Tenant]   |
                   |  [Eloquent Models with Global Tenant Scoping]    |
                   +-------------+-----------------------+-------------+
                                 |                       |
                       SQL / TCP:5432              TCP:6379
                                 |                       |
                     +-----------v----------+  +---------v----------+
                     |  PostgreSQL Database |  |    Redis Cache     |
                     |  (Shared DB / Tenant)|  |   & Queue Broker   |
                     +----------------------+  +--------------------+
                                                         ^
                                                         | Async Jobs
                                               +---------+----------+
                                               | Queue & Scheduler  |
                                               | (Payroll, Notif)   |
                                               +--------------------+
```

---

## 2. STRUKTUR DIREKTORI PROYEK (PROJECT DIRECTORY BLUEPRINT)

Aplikasi diorganisir secara modular di dalam `app/` dan `resources/`:

```
hris-php/
├── app/
│   ├── Http/
│   │   ├── Controllers/             # General routing controllers (Auth, Landing, SwitchTenant)
│   │   ├── Middleware/              # TenantIdentification, EnforceTenantIsolation, RolePermission
│   │   └── Requests/                # Form validation requests
│   ├── Models/                      # Core Eloquent models (Tenant, Company, User, Role, Permission)
│   ├── Modules/                     # Domain modules (Clean Domain boundaries)
│   │   ├── Attendance/
│   │   │   ├── Controllers/
│   │   │   ├── Models/              # AttendanceRecord, Shift, Holiday, OvertimeRequest
│   │   │   ├── Services/            # AttendanceCalculationService, GeofenceService
│   │   │   └── Requests/
│   │   ├── CMS/
│   │   │   ├── Controllers/         # PublicLandingController, AdminCmsController
│   │   │   ├── Models/              # CmsSlider, CmsFeature, CmsModule, CmsPricingPlan, CmsFaq
│   │   │   └── Services/            # CmsMediaService
│   │   ├── Compliance/
│   │   │   ├── Services/            # Pph21TerCalculator, BpjsCalculator, SeveranceCalculator
│   │   │   └── Models/              # TaxProfile, BpjsProfile, ContractCompliance
│   │   ├── Employee/
│   │   │   ├── Controllers/
│   │   │   ├── Models/              # Employee, EmployeeProfile, EmployeeDocument
│   │   │   └── Services/            # EmployeeLifecycleService
│   │   ├── Leave/
│   │   │   ├── Controllers/
│   │   │   ├── Models/              # LeaveType, LeaveRequest, LeaveBalance
│   │   │   └── Services/            # LeaveAccrualService, LeaveApprovalService
│   │   ├── Organization/
│   │   │   ├── Controllers/
│   │   │   └── Models/              # Branch, Department, Division, Position, JobLevel
│   │   ├── Payroll/
│   │   │   ├── Controllers/
│   │   │   ├── Models/              # PayrollPeriod, PayrollRecord, SalaryComponent, Payslip
│   │   │   └── Services/            # PayrollProcessorService, PayslipPdfService
│   │   ├── Subscription/
│   │   │   ├── Controllers/
│   │   │   ├── Models/              # SubscriptionPlan, Subscription, SubscriptionInvoice
│   │   │   └── Services/            # QuotaCheckService, BillingService
│   │   └── Workflow/
│   │       ├── Models/              # WorkflowDefinition, ApprovalRequest, ApprovalHistory
│   │       └── Services/            # WorkflowEngineService
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── TenantServiceProvider.php
│   │   └── RbacServiceProvider.php
│   ├── Services/                    # Shared enterprise services (AuditTrail, Storage)
│   ├── Support/                     # Helper traits, DTOs, Enums, Macro helpers
│   │   ├── Traits/
│   │   │   └── BelongsToTenant.php  # Auto-tenant query scoping & auto-tenant assignment
│   │   └── Scopes/
│   │       └── TenantScope.php      # Global scope enforcing tenant_id = active_tenant_id
│   └── Tenancy/
│       ├── TenantManager.php        # Context holder for current tenant instance
│       └── TenantContext.php
├── config/                          # Standard and modular configurations
├── database/
│   ├── factories/
│   ├── migrations/                  # Sequenced migrations by domain
│   └── seeders/                     # Master seeders (Roles, Plans, CMS, SuperAdmin, DemoCompany)
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   └── local.ini
│   └── postgres/
│       └── init.sql
├── resources/
│   ├── css/
│   │   └── app.css                  # Tailwind directives and custom utility styles
│   ├── js/
│   │   ├── app.js                   # Alpine.js init, Chart.js components, Lucide icons
│   │   └── components/
│   └── views/
│       ├── auth/                    # Login, Register, Forgot Password
│       ├── components/              # Reusable Blade UI components (button, input, modal, card)
│       ├── layouts/                 # App, Landing, Auth, SuperAdmin, ESS layouts
│       ├── public/                  # Landing page with dynamic CMS sections
│       ├── super-admin/             # Companies, Subscriptions, CMS, Audit Logs
│       ├── company/                 # Master data, Organization, Policy configs
│       ├── hr/                      # Employee CRUD, Attendance recap, Payroll runs, Contracts
│       └── employee/                # ESS portal, Clock-in, Leave requests, Payslips
├── routes/
│   ├── web.php                      # Public, Auth, and Dashboard routes
│   ├── console.php                  # Artisan commands and scheduled cron jobs
│   └── api.php                      # REST API endpoints
├── tests/
│   ├── Feature/                     # Tenant isolation, Auth, RBAC, Payroll tests
│   └── Unit/                        # PPh21 TER, BPJS calculation unit tests
├── docker-compose.yml
├── docker-compose.prod.yml
├── Dockerfile
└── vite.config.js
```

---

## 3. PEMISAHAN TANGGUNG JAWAB (SEPARATION OF CONCERNS)

Untuk memastikan sistem tidak menumpuk logika pada controller (Fat Controller anti-pattern), diterapkan aturan desain berikut:

1. **Controller Layer (Tipis)**:
   - Hanya menerima HTTP Request, mendelegasikan validasi ke Form Request, memanggil Service yang relevan, dan mengembalikan HTTP Response (Blade view atau Json response).
   - Dilarang keras melakukan kalkulasi pajak, manipulasi saldo cuti, atau komputasi gaji langsung di Controller.
2. **Form Request Layer**:
   - Memvalidasi tipe data, format, batasan file, keunikan per tenant (`Rule::unique('employees')->where('tenant_id', $tenantId)`).
   - Memeriksa hak otorisasi dasar pengguna untuk mengeksekusi request tersebut.
3. **Service Layer**:
   - Berisi seluruh logika bisnis murni, kalkulasi matematika, pemanggilan repository, dan pengelolaan transaksi database atomik (`DB::transaction`).
   - Contoh: `Pph21TerCalculator` menerima bruto dan PTKP lalu mengembalikan nominal potongan pajak berdasarkan PMK 168/2023.
4. **Eloquent Model & Scope Layer**:
   - Merepresentasikan entitas database, mendefinisikan relasi (1-to-N, N-to-N), casting atribut, accessor/mutator, dan memasang `TenantScope` secara otomatis melalui trait `BelongsToTenant`.
5. **Policy & Gate Layer**:
   - Menegakkan otorisasi granular peran (RBAC) dan kepemilikan tenant.
   - Contoh: `EmployeePolicy` memeriksa apakah pengguna berhak mengubah data karyawan berdasarkan permission `employee.update` DAN kesamaan `tenant_id`.

---

## 4. ASYNCHRONOUS JOBS & SCHEDULER

Operasi intensif komputasi atau komunikasi eksternal diproses secara asinkron menggunakan Laravel Queue yang didukung Redis:

1. **Bulk Payroll Calculation Job**: Menghitung ratusan/ribuan slip gaji dalam background worker per batch.
2. **Payslip PDF Generator**: Merender dokumen PDF slip gaji dan menyimpannya di storage terisolasi.
3. **Notification Mailers**: Mengirim email verifikasi, peringatan jatuh tempo kontrak, notifikasi persetujuan cuti.
4. **Scheduled Cron Commands (`routes/console.php`)**:
   - Harian pukul 01:00: Pengecekan otomatis kontrak karyawan yang akan berakhir dalam 60, 30, dan 7 hari ke depan.
   - Harian pukul 02:00: Penguncian rekap absensi harian (penandaan status Tidak Hadir/Alpha jika tidak ada clock in).
   - Bulanan hari ke-1: Perhitungan accrual hak cuti tahunan karyawan yang berhak.
   - Harian pukul 23:59: Pemeriksaan status masa aktif langganan tenant (Trial -> Active -> Expired).

---
*Dokumen Arsitektur Sistem ini menjadi landasan implementasi modul backend dan integrasi sistem.*

