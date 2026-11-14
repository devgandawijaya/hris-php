<?php

namespace App\Services\Integration;

class OpenApiService
{
    /**
     * Generate the complete OpenAPI 3.1.0 specification array.
     */
    public function getSpec(): array
    {
        $baseUrl = url('/api/v1');

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'RiHRIS Enterprise Open API',
                'version' => '1.0.0',
                'description' => 'OpenAPI Specification resmi platform RiHRIS Enterprise untuk integrasi dua arah antara sistem pihak ketiga (ERP SAP/Odoo, Mesin Fingerprint Biometrik, Core HR, Portals) dengan sistem internal RiHRIS.',
                'contact' => [
                    'name' => 'RiHRIS Developer Relations & API Integration Support',
                    'email' => 'hallo@phoebehub.com',
                    'url' => 'https://wa.me/6282218866002',
                ],
                'license' => [
                    'name' => 'Proprietary Enterprise License',
                    'url' => 'https://rihris.id/terms',
                ],
            ],
            'servers' => [
                [
                    'url' => $baseUrl,
                    'description' => 'RiHRIS API v1 Production / Sandbox Tenant Gateway',
                ],
            ],
            'tags' => [
                [
                    'name' => 'Employees',
                    'description' => 'Manajemen dan sinkronisasi master data karyawan dengan ERP dan HRMS eksternal.',
                ],
                [
                    'name' => 'Attendance',
                    'description' => 'Integrasi mesin biometrik (Fingerprint, Face Recognition) dan push log presensi GPS eksternal.',
                ],
                [
                    'name' => 'Departments',
                    'description' => 'Sinkronisasi struktur organisasi dan departemen.',
                ],
                [
                    'name' => 'Leave',
                    'description' => 'Cek hak cuti, saldo kuota, dan pengajuan cuti karyawan dari portal eksternal.',
                ],
                [
                    'name' => 'Payroll',
                    'description' => 'Ekspor ringkasan penggajian dan beban biaya tenaga kerja untuk penjurnalan ERP / Akuntansi.',
                ],
                [
                    'name' => 'Webhooks',
                    'description' => 'Katalog event-driven webhook outbound untuk notifikasi real-time.',
                ],
            ],
            'paths' => [
                '/employees' => [
                    'get' => [
                        'tags' => ['Employees'],
                        'summary' => 'Daftar Karyawan (Paginated)',
                        'description' => 'Mengambil daftar seluruh karyawan aktif/non-aktif pada tenant dengan filter departemen, status, dan pencarian nama.',
                        'operationId' => 'listEmployees',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'per_page',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'integer', 'default' => 25],
                                'description' => 'Jumlah baris per halaman (maksimal 100).',
                            ],
                            [
                                'name' => 'page',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'integer', 'default' => 1],
                                'description' => 'Nomor halaman.',
                            ],
                            [
                                'name' => 'status',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string', 'enum' => ['active', 'probation', 'resigned', 'terminated']],
                                'description' => 'Filter berdasarkan status karyawan.',
                            ],
                            [
                                'name' => 'search',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                                'description' => 'Pencarian nama atau NIK karyawan.',
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Daftar karyawan berhasil diambil.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/EmployeeListResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => ['$ref' => '#/components/responses/UnauthorizedError'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Employees'],
                        'summary' => 'Daftarkan Karyawan Baru (Inbound Sync)',
                        'description' => 'Menerima data karyawan baru yang dibuat dari sistem pihak ketiga (misalnya ATS Rekrutmen atau ERP onboarding).',
                        'operationId' => 'createEmployee',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/EmployeeInput',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Karyawan berhasil didaftarkan.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/EmployeeDetailResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => ['$ref' => '#/components/responses/UnauthorizedError'],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
                '/employees/{id}' => [
                    'get' => [
                        'tags' => ['Employees'],
                        'summary' => 'Detail Karyawan',
                        'description' => 'Mengambil profil lengkap satu karyawan berdasarkan ID internal database atau Nomor Induk Karyawan (NIK).',
                        'operationId' => 'getEmployeeDetail',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                                'description' => 'ID numerik atau employee_number (NIK).',
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Detail karyawan ditemukan.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/EmployeeDetailResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => ['$ref' => '#/components/responses/NotFoundError'],
                            '401' => ['$ref' => '#/components/responses/UnauthorizedError'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Employees'],
                        'summary' => 'Perbarui Data Karyawan (Update Master)',
                        'description' => 'Memperbarui informasi profil karyawan yang disinkronkan dari ERP atau sistem eksternal.',
                        'operationId' => 'updateEmployee',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                                'description' => 'ID numerik atau employee_number (NIK).',
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/EmployeeUpdateInput',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Data karyawan berhasil diperbarui.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/EmployeeDetailResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => ['$ref' => '#/components/responses/NotFoundError'],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
                '/attendance' => [
                    'get' => [
                        'tags' => ['Attendance'],
                        'summary' => 'Daftar Log Presensi Harian',
                        'description' => 'Mengambil catatan kehadiran seluruh karyawan pada tanggal tertentu.',
                        'operationId' => 'listAttendance',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'date',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string', 'format' => 'date'],
                                'description' => 'Format YYYY-MM-DD (default hari ini).',
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Data presensi berhasil diambil.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/AttendanceListResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => ['$ref' => '#/components/responses/UnauthorizedError'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Attendance'],
                        'summary' => 'Catat Presensi Tunggal (Single Clock In/Out)',
                        'description' => 'Menerima 1 event presensi (clock_in atau clock_out) dari mesin fingerprint atau gerbang biometrik.',
                        'operationId' => 'recordAttendance',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/AttendanceSingleInput',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Presensi berhasil dicatat.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/AttendanceDetailResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => ['$ref' => '#/components/responses/NotFoundError'],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
                '/attendance/bulk' => [
                    'post' => [
                        'tags' => ['Attendance'],
                        'summary' => 'Sinkronisasi Presensi Massal (Bulk Biometric Logs Push)',
                        'description' => 'Menerima batch log presensi dari mesin fingerprint/biometrik saat sinkronisasi periodik (misal setiap 15 menit atau upload offline logs).',
                        'operationId' => 'recordBulkAttendance',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/AttendanceBulkInput',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Batch presensi selesai diproses.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/AttendanceBulkResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
                '/departments' => [
                    'get' => [
                        'tags' => ['Departments'],
                        'summary' => 'Daftar Departemen / Divisi',
                        'description' => 'Mengambil daftar struktur departemen perusahaan beserta jumlah karyawan aktif.',
                        'operationId' => 'listDepartments',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Daftar departemen berhasil diambil.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DepartmentListResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Departments'],
                        'summary' => 'Tambah / Sinkronisasi Departemen dari ERP',
                        'description' => 'Membuat departemen baru yang didefinisikan dari sistem organisasi luar.',
                        'operationId' => 'createDepartment',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['name'],
                                        'properties' => [
                                            'name' => ['type' => 'string', 'example' => 'Logistik & Rantai Pasok'],
                                            'description' => ['type' => 'string', 'example' => 'Divisi logistik terpadu pergudangan.'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Departemen berhasil dibuat.',
                            ],
                        ],
                    ],
                ],
                '/leave/types' => [
                    'get' => [
                        'tags' => ['Leave'],
                        'summary' => 'Daftar Jenis Cuti Resmi',
                        'description' => 'Mengambil daftar jenis cuti (Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, Cuti Menikah, dll.) yang berlaku di perusahaan.',
                        'operationId' => 'listLeaveTypes',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Daftar jenis cuti berhasil diambil.',
                            ],
                        ],
                    ],
                ],
                '/leave/requests' => [
                    'get' => [
                        'tags' => ['Leave'],
                        'summary' => 'Daftar Permohonan Cuti',
                        'description' => 'Mengambil data permohonan cuti dengan filter status (pending, approved, rejected).',
                        'operationId' => 'listLeaveRequests',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'status',
                                'in' => 'query',
                                'schema' => ['type' => 'string', 'enum' => ['pending', 'approved', 'rejected']],
                                'description' => 'Filter status persetujuan.',
                            ],
                            [
                                'name' => 'employee_number',
                                'in' => 'query',
                                'schema' => ['type' => 'string'],
                                'description' => 'Filter NIK karyawan.',
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Daftar permohonan cuti.',
                            ],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Leave'],
                        'summary' => 'Ajukan Permohonan Cuti dari Aplikasi Pihak Ketiga',
                        'description' => 'Membuat permohonan cuti karyawan yang diajukan melalui portal mandiri eksternal atau aplikasi superapp perusahaan.',
                        'operationId' => 'submitLeaveRequest',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/LeaveRequestInput',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Permohonan cuti berhasil dibuat.',
                            ],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
                '/payroll/summary' => [
                    'get' => [
                        'tags' => ['Payroll'],
                        'summary' => 'Ringkasan Payroll & Jurnal ERP',
                        'description' => 'Mengambil ringkasan pengeluaran payroll, total gaji bruto, potongan PPh 21 TER, iuran BPJS, dan alokasi cost center per departemen untuk integrasi General Ledger sistem akuntansi / ERP.',
                        'operationId' => 'getPayrollSummary',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'period_id',
                                'in' => 'query',
                                'schema' => ['type' => 'integer'],
                                'description' => 'ID Periode Payroll (opsional, default periode terakhir).',
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Ringkasan payroll berhasil diambil.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/PayrollSummaryResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/webhooks/events' => [
                    'get' => [
                        'tags' => ['Webhooks'],
                        'summary' => 'Katalog Event Webhook Outbound',
                        'description' => 'Menampilkan daftar seluruh event yang dapat dilanggan oleh sistem pihak ketiga lengkap dengan contoh payload.',
                        'operationId' => 'listWebhookEvents',
                        'security' => [
                            ['ApiKeyAuth' => []],
                            ['BearerAuth' => []],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Daftar katalog event webhook.',
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-KEY',
                        'description' => 'Token API rahasia yang digenerate dari menu Integrasi Enterprise (Contoh format: ri_live_xxxxxxxxxxxxxxxxxxxx).',
                    ],
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'API-Key',
                        'description' => 'Alternatif otentikasi melalui header Authorization: Bearer <plain_api_key>',
                    ],
                ],
                'schemas' => [
                    'EmployeeInput' => [
                        'type' => 'object',
                        'required' => ['employee_number', 'first_name', 'email', 'join_date'],
                        'properties' => [
                            'employee_number' => ['type' => 'string', 'example' => 'EMP-2026-088'],
                            'first_name' => ['type' => 'string', 'example' => 'Budi'],
                            'last_name' => ['type' => 'string', 'example' => 'Santoso'],
                            'email' => ['type' => 'string', 'format' => 'email', 'example' => 'budi.santoso@perusahaan.com'],
                            'phone' => ['type' => 'string', 'example' => '081234567890'],
                            'gender' => ['type' => 'string', 'enum' => ['male', 'female'], 'example' => 'male'],
                            'employment_status' => ['type' => 'string', 'enum' => ['pkwtt', 'pkwt', 'probation', 'internship'], 'example' => 'pkwt'],
                            'join_date' => ['type' => 'string', 'format' => 'date', 'example' => '2026-06-01'],
                            'end_date' => ['type' => 'string', 'format' => 'date', 'nullable' => true, 'example' => '2027-05-31'],
                            'department_id' => ['type' => 'integer', 'nullable' => true, 'example' => 1],
                            'basic_salary' => ['type' => 'number', 'format' => 'float', 'example' => 8500000.00],
                        ],
                    ],
                    'EmployeeUpdateInput' => [
                        'type' => 'object',
                        'properties' => [
                            'first_name' => ['type' => 'string'],
                            'last_name' => ['type' => 'string'],
                            'phone' => ['type' => 'string'],
                            'department_id' => ['type' => 'integer'],
                            'position_id' => ['type' => 'integer'],
                            'basic_salary' => ['type' => 'number'],
                            'status' => ['type' => 'string', 'enum' => ['active', 'suspended', 'resigned', 'terminated']],
                        ],
                    ],
                    'AttendanceSingleInput' => [
                        'type' => 'object',
                        'required' => ['employee_number', 'type', 'timestamp'],
                        'properties' => [
                            'employee_number' => ['type' => 'string', 'example' => 'EMP-2026-001'],
                            'type' => ['type' => 'string', 'enum' => ['clock_in', 'clock_out'], 'example' => 'clock_in'],
                            'timestamp' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-11-14 08:02:15'],
                            'device_id' => ['type' => 'string', 'example' => 'ZKTECO-GATE-LOBBY-01'],
                        ],
                    ],
                    'AttendanceBulkInput' => [
                        'type' => 'object',
                        'required' => ['logs'],
                        'properties' => [
                            'device_id' => ['type' => 'string', 'example' => 'ZKTECO-FINGERPRINT-GATE-01'],
                            'logs' => [
                                'type' => 'array',
                                'items' => [
                                    '$ref' => '#/components/schemas/AttendanceSingleInput',
                                ],
                            ],
                        ],
                    ],
                    'AttendanceBulkResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'message' => ['type' => 'string', 'example' => 'Batch presensi 10 log selesai diproses.'],
                            'summary' => [
                                'type' => 'object',
                                'properties' => [
                                    'total' => ['type' => 'integer', 'example' => 10],
                                    'successful' => ['type' => 'integer', 'example' => 10],
                                    'failed' => ['type' => 'integer', 'example' => 0],
                                ],
                            ],
                            'errors' => ['type' => 'array', 'items' => ['type' => 'string']],
                        ],
                    ],
                    'LeaveRequestInput' => [
                        'type' => 'object',
                        'required' => ['employee_number', 'leave_type_id', 'start_date', 'end_date', 'reason'],
                        'properties' => [
                            'employee_number' => ['type' => 'string', 'example' => 'EMP-2026-001'],
                            'leave_type_id' => ['type' => 'integer', 'example' => 1],
                            'start_date' => ['type' => 'string', 'format' => 'date', 'example' => '2026-12-01'],
                            'end_date' => ['type' => 'string', 'format' => 'date', 'example' => '2026-12-03'],
                            'reason' => ['type' => 'string', 'example' => 'Keperluan keluarga di luar kota.'],
                        ],
                    ],
                    'EmployeeListResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'data' => [
                                'type' => 'array',
                                'items' => ['type' => 'object'],
                            ],
                            'meta' => [
                                'type' => 'object',
                                'properties' => [
                                    'current_page' => ['type' => 'integer', 'example' => 1],
                                    'total' => ['type' => 'integer', 'example' => 45],
                                    'per_page' => ['type' => 'integer', 'example' => 25],
                                ],
                            ],
                        ],
                    ],
                    'EmployeeDetailResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'data' => ['type' => 'object'],
                        ],
                    ],
                    'AttendanceListResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'date' => ['type' => 'string', 'example' => '2026-11-14'],
                            'count' => ['type' => 'integer', 'example' => 42],
                            'data' => ['type' => 'array', 'items' => ['type' => 'object']],
                        ],
                    ],
                    'AttendanceDetailResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'message' => ['type' => 'string'],
                            'data' => ['type' => 'object'],
                        ],
                    ],
                    'DepartmentListResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'data' => ['type' => 'array', 'items' => ['type' => 'object']],
                        ],
                    ],
                    'PayrollSummaryResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'period' => ['type' => 'object'],
                            'totals' => [
                                'type' => 'object',
                                'properties' => [
                                    'gross_salary' => ['type' => 'number', 'example' => 185000000.00],
                                    'take_home_pay' => ['type' => 'number', 'example' => 162400000.00],
                                    'pph21_ter_tax' => ['type' => 'number', 'example' => 9250000.00],
                                    'bpjs_employer_cost' => ['type' => 'number', 'example' => 13350000.00],
                                ],
                            ],
                            'department_allocations' => ['type' => 'array', 'items' => ['type' => 'object']],
                        ],
                    ],
                ],
                'responses' => [
                    'UnauthorizedError' => [
                        'description' => 'Otentikasi gagal: X-API-KEY tidak valid atau kadaluarsa.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => false],
                                        'message' => ['type' => 'string', 'example' => 'Invalid or expired API Key.'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'NotFoundError' => [
                        'description' => 'Entitas yang diminta tidak ditemukan.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => false],
                                        'message' => ['type' => 'string', 'example' => 'Resource not found.'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'ValidationError' => [
                        'description' => 'Validasi data masukan gagal (422 Unprocessable Entity).',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => false],
                                        'message' => ['type' => 'string', 'example' => 'The given data was invalid.'],
                                        'errors' => ['type' => 'object'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
