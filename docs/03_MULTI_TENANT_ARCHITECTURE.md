# MULTI-TENANT ARCHITECTURE DESIGN
## RiHRIS — SaaS Multi-Company Data Isolation & Execution Model

**Version:** 1.0.0  
**Isolation Strategy:** Shared Database with Tenant Column Discriminator (`tenant_id`)  
**Scope Mechanism:** Laravel Eloquent Global Query Scopes & Context Middleware  
**Storage Partition:** Dynamic Directory Namespace (`storage/app/tenants/{tenant_id}/`)

---

## 1. STRATEGI MULTI-TENANCY

RiHRIS mengadopsi model **Shared Database, Shared Schema with Tenant Discriminator**. Model ini dipilih karena:
1. **Efisiensi Sumber Daya**: Sangat hemat penggunaan memory dan koneksi pool PostgreSQL dibandingkan model database-per-tenant.
2. **Kemudahan Migrasi & Skalabilitas**: Eksekusi migrasi database hanya perlu dijalankan satu kali untuk seluruh perusahaan.
3. **Analitik Agregat Platform**: Super Admin dapat menghasilkan statistik multi-company secara instan tanpa perlu federated database query.
4. **Isolasi Data yang Dijamin Kode (Code-Enforced Isolation)**: Isolasi dijaga secara otomatis di level framework melalui Eloquent Global Scope, Model Boot Trait, Form Request Validator, dan Tenant Context Middleware.

---

## 2. MEKANISME RESOLUSI TENANT AKTIF (TENANT RESOLUTION LIFECYCLE)

Alur resolusi tenant dilakukan secara otomatis pada setiap request HTTP:

```
[ Incoming Request ]
        │
        ▼
[ TenantMiddleware / IdentifyTenant ]
        │
        ├─► 1. Periksa Subdomain (misal: "acme.rihris.com" -> cari tenant slug "acme")
        │
        ├─► 2. Jika di domain utama, periksa Session pengguna terautentikasi (Auth::user()->active_tenant_id)
        │
        ├─► 3. Jika API request, periksa header "X-Tenant-ID" yang divalidasi terhadap hak user
        │
        ▼
[ Tenant Ditemukan? ]
   ├── TIDAK ──► Jika route membutuhkan tenant -> Abort 404 / Redirect ke Landing / Tenant Selection
   └── YA    ──► [ TenantManager::setCurrentTenant($tenant) ]
                     │
                     ├─► Daftarkan $tenant di Laravel Service Container (app('currentTenant'))
                     ├─► Periksa Status Langganan (Trial / Active / Suspended / Expired)
                     │     └── Jika Suspended/Expired -> Tampilkan layar peringatan pembayaran/kontak admin
                     └─► Lanjutkan request ke Controller / Actions
```

---

## 3. GLOBAL QUERY SCOPE: `TenantScope` & `BelongsToTenant`

Setiap model data milik perusahaan mengimplementasikan trait `BelongsToTenant`.

### 3.1 Implementasi Konseptual `TenantScope`
```php
namespace App\Support\Scopes;

use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantManager = app(TenantManager::class);
        
        // Jika sedang berjalan dalam context tenant aktif
        if ($tenantManager->hasActiveTenant()) {
            $builder->where($model->getTable() . '.tenant_id', '=', $tenantManager->getActiveTenantId());
        }
    }
}
```

### 3.2 Implementasi Konseptual `BelongsToTenant` Trait
```php
namespace App\Support\Traits;

use App\Models\Tenant;
use App\Support\Scopes\TenantScope;
use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Pasang Global Scope untuk seluruh SELECT, UPDATE, DELETE
        static::addGlobalScope(new TenantScope());

        // Pasang tenant_id otomatis pada event CREATING
        static::creating(function ($model) {
            $tenantManager = app(TenantManager::class);
            if ($tenantManager->hasActiveTenant() && empty($model->tenant_id)) {
                $model->tenant_id = $tenantManager->getActiveTenantId();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
```

---

## 4. AKSES SUPER ADMIN VS TENANT USER

1. **Akses Tenant Regular (User / HR / Admin)**:
   - Terikat langsung dengan `tenant_id` tertentu melalui tabel `tenant_user_access`.
   - Tidak memiliki otoritas untuk memanggil `withoutGlobalScope(TenantScope::class)`.
   - Usaha memanggil data dengan ID perusahaan lain akan menghasilkan HTTP 404 Model Not Found.
2. **Akses Multi-Company User**:
   - Seorang pengguna (misal konsultan HR atau Auditor eksternal) dapat memiliki relasi ke beberapa perusahaan dengan role yang berbeda pada masing-masing perusahaan.
   - Disediakan endpoint `POST /switch-tenant/{tenantId}` yang memvalidasi otorisasi sebelum mengalihkan konteks aktif pada sesi pengguna.
3. **Akses Super Admin Platform**:
   - Super Admin memiliki peran global (`is_super_admin = true`).
   - Dapat melihat daftar seluruh tenant, mengelola status langganan, dan mengakses data tenant untuk kebutuhan bantuan teknis atas izin audit.

---

## 5. ISOLASI FILE STORAGE & ASSETS

Seluruh file yang diunggah oleh tenant (foto profil karyawan, scan KTP, dokumen kontrak kerja, berkas reimbursement, slip gaji PDF) diisolasi berdasarkan direktori root tenant:

$$\text{Path} = \text{storage/app/tenants/}\{\text{tenant\_id}\}/\{\text{domain\_category}\}/\{\text{filename}\}$$

- Contoh dokumen kontrak: `storage/app/tenants/018f9d0c-.../contracts/pkwt_karyawan_001.pdf`
- Pengunduhan dokumen melalui rute controller terproteksi yang memverifikasi kepemilikan `tenant_id` sebelum melakukan streaming file ke browser (`Storage::download()`). Tidak ada berkas privat karyawan yang dapat diakses melalui public direct URL tanpa otentikasi.

---

## 6. SIKLUS HIDUP LANGGANAN & ENFORCEMENT KUOTA (SUBSCRIPTION ENGINE)

Setiap aksi create data karyawan atau penambahan modul dicek terhadap kuota paket langganan aktif:

```
[ User Menekan Tombol "Tambah Karyawan" ]
                   │
                   ▼
       [ QuotaCheckService::canAddEmployee($tenant) ]
                   │
         ┌─────────┴─────────┐
         │                   │
    [ Kuota Cukup ]     [ Melebihi Batas Paket ]
         │                   │
         ▼                   ▼
    Tampilkan Form      Tampilkan Alert Upgrade Paket
    Input Karyawan      "Batas 50 Karyawan Tercapai (Paket Starter)"
```

- **Free Trial**: 14 hari masa coba, batasan maksimal 20 karyawan, modul Core HR + Absensi + Cuti.
- **Starter**: Bisnis berkembang, hingga 50 karyawan, modul Core HR + Payroll dasar + BPJS.
- **Professional**: Menengah, hingga 250 karyawan, seluruh modul termasuk PPh 21 TER, Lembur, dan Kontrak.
- **Enterprise**: Skala besar, karyawan tanpa batas (unlimited), seluruh fitur termasuk Workflow Engine kustom, Multi-level approvals, dan prioritas support.

---
*Dokumen Arsitektur Multi-Tenancy ini menjamin isolasi data mutlak antar-perusahaan dalam RiHRIS.*

