# DOKUMENTASI SPESIFIKASI REST API v1 & WEBHOOKS

**Platform:** RiHRIS — Multi-Company SaaS HRIS Indonesia  
**Versi API:** v1.0.0  
**Format:** RESTful JSON  
**Status:** Production Ready  

---

## 1. PENGANTAR & MEKANISME OTENTIKASI

API RiHRIS v1 dirancang untuk memfasilitasi integrasi dua arah antara platform RiHRIS dan sistem pihak ketiga seperti mesin absensi biometrik (ZKTeco, Solution, Fingerspot), sistem ERP korporat (SAP, Oracle, Odoo), serta aplikasi pesan internal (Slack, Microsoft Teams, WhatsApp Gateway).

### Mekanisme Kredensial (API Key)
Setiap permintaan ke endpoint `/api/v1/*` wajib menyertakan kunci API aktif yang dihasilkan melalui menu **Integrasi, API & WA** di dashboard perusahaan.

Gunakan salah satu header HTTP berikut:
```http
X-API-KEY: ri_live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```
atau
```http
Authorization: Bearer ri_live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

> [!IMPORTANT]
> Token API dienkripsi satu arah menggunakan hashing **SHA-256** di basis data PostgreSQL. Kunci mentah (*plain key*) hanya ditampilkan satu kali saat registrasi awal.

---

## 2. STANDARD RESPONSE CODES & FORMAT

Semua response dikembalikan dalam format `application/json` dengan format standar:

```json
{
  "success": true,
  "data": {},
  "meta": {}
}
```

### HTTP Status Codes
| Code | Keterangan |
|---|---|
| `200 OK` | Permintaan berhasil diproses. |
| `201 Created` | Data baru berhasil dibuat (misal: sinkronisasi presensi). |
| `400 Bad Request` | Format parameter tidak valid. |
| `401 Unauthorized` | Kunci API hilang, kadaluarsa, atau tidak valid. |
| `403 Forbidden` | Kunci API tidak memiliki izin (*insufficient permissions*). |
| `404 Not Found` | Data atau entitas yang dicari tidak ditemukan. |
| `422 Unprocessable Content` | Validasi payload gagal (misal: kolom wajib tidak diisi). |
| `500 Server Error` | Terjadi kesalahan internal pada server. |

---

## 3. ENDPOINT SPESIFIKASI REST API v1

### A. List Data Karyawan (Employees)
Mengambil daftar karyawan aktif di dalam tenant terkait.

- **Method:** `GET`
- **Path:** `/api/v1/employees`
- **Query Parameters:**
  - `page` *(integer, optional)*: Nomor halaman paginasi (default: `1`).
  - `per_page` *(integer, optional)*: Jumlah item per halaman (default: `25`, max: `100`).

**Response 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_number": "SDN-2024-001",
      "first_name": "Budi",
      "last_name": "Santoso",
      "email": "budi@solusidigital.id",
      "phone": "+628123456703",
      "gender": "male",
      "employment_status": "pkwtt",
      "join_date": "2023-01-15",
      "basic_salary": "15000000.00",
      "status": "active",
      "department": {
        "id": 1,
        "name": "Technology & Product",
        "code": "TECH-01"
      },
      "position": {
        "id": 1,
        "title": "Lead Software Engineer",
        "job_level": "Manager"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 45,
    "per_page": 25
  }
}
```

---

### B. Detail Karyawan (Employee Detail)
Mengambil profil lengkap karyawan berdasarkan ID atau NIK.

- **Method:** `GET`
- **Path:** `/api/v1/employees/{id_or_nik}`

**Response 200 OK:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee_number": "SDN-2024-001",
    "nik_ktp": "3171012304920005",
    "first_name": "Budi",
    "last_name": "Santoso",
    "email": "budi@solusidigital.id",
    "tax_ptkp_status": "K/1",
    "npwp": "09.876.543.2-011.000",
    "bpjs_tk_number": "18029384756",
    "bpjs_kes_number": "000192837465",
    "department": { "id": 1, "name": "Technology & Product" },
    "position": { "id": 1, "title": "Lead Software Engineer" },
    "contracts": [
      {
        "contract_number": "PKWTT/SDN/2023/001",
        "type": "pkwtt",
        "status": "active",
        "start_date": "2023-01-15"
      }
    ]
  }
}
```

---

### C. Ambil Log Presensi Harian (Attendance Daily Query)
Mengambil data kehadiran seluruh karyawan pada tanggal tertentu.

- **Method:** `GET`
- **Path:** `/api/v1/attendance`
- **Query Parameters:**
  - `date` *(string date, optional)*: Format `YYYY-MM-DD` (default: hari ini).

**Response 200 OK:**
```json
{
  "success": true,
  "date": "2026-11-06",
  "count": 42,
  "data": [
    {
      "id": 128,
      "employee_id": 1,
      "date": "2026-11-06",
      "clock_in": "07:54:12",
      "clock_out": "17:05:40",
      "late_minutes": 0,
      "status": "present",
      "employee": {
        "employee_number": "SDN-2024-001",
        "full_name": "Budi Santoso"
      }
    }
  ]
}
```

---

### D. Sinkronisasi Presensi Biometrik / RFID (Biometric Clock Sync)
Merekam clock-in atau clock-out langsung dari mesin presensi fisik atau sistem pintu akses.

- **Method:** `POST`
- **Path:** `/api/v1/attendance`
- **Headers:** `Content-Type: application/json`
- **Request Body:**
```json
{
  "employee_number": "SDN-2024-001",
  "type": "clock_in",
  "timestamp": "2026-11-06 07:55:00",
  "device_id": "GATE-LOBBY-01"
}
```

**Response 201 Created:**
```json
{
  "success": true,
  "message": "Attendance clock_in recorded successfully.",
  "data": {
    "id": 129,
    "employee_id": 1,
    "date": "2026-11-06",
    "clock_in": "07:55:00",
    "status": "present",
    "notes": "Synced from Biometric Machine ID: GATE-LOBBY-01"
  }
}
```

---

## 4. EVENT-DRIVEN WEBHOOKS SPECIFICATION

RiHRIS mendukung pengiriman event HTTP POST secara *real-time* ke server eksternal saat terjadi transaksi penting di dalam platform.

### A. Format Header Webhook
Setiap payload webhook yang dikirimkan oleh RiHRIS menyertakan signature kriptografi:

```http
POST /your-webhook-handler HTTP/1.1
Host: api.perusahaan.com
Content-Type: application/json
X-RiHRIS-Event: leave.approved
X-RiHRIS-Signature: 5d41402abc4b2a76b9719d911017c592...
```

### B. Cara Verifikasi Signature HMAC-SHA256 (PHP Reference)
```php
$payload = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_X_RIHRIS_SIGNATURE'] ?? '';
$secret = 'whsec_your_registered_secret';

$computedSignature = hash_hmac('sha256', $payload, $secret);

if (!hash_equals($computedSignature, $signatureHeader)) {
    http_response_code(403);
    exit('Invalid Webhook Signature');
}
```

### C. Matriks Event Webhook
| Nama Event | Pemicu (Trigger) |
|---|---|
| `employee.created` | Karyawan baru terdaftar atau pelamar berhasil dikonversi menjadi karyawan. |
| `leave.approved` | Pengajuan cuti karyawan telah disetujui hingga tahap final workflow. |
| `payroll.finalized` | Periode penggajian bulanan telah disahkan dan slip gaji siap diunduh. |
| `ping.test` | Uji coba konektivitas webhook dari antarmuka dashboard perusahaan. |
