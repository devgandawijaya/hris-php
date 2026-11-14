<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WebhookApiController extends Controller
{
    /**
     * List all supported outbound webhook events and sample payloads.
     */
    public function events(): JsonResponse
    {
        $events = [
            [
                'event' => 'employee.created',
                'description' => 'Dipicu ketika karyawan baru terdaftar di RiHRIS (baik via web UI, import, maupun API).',
                'sample_payload' => [
                    'event' => 'employee.created',
                    'timestamp' => '2026-11-14T10:00:00+07:00',
                    'tenant_id' => 1,
                    'data' => [
                        'id' => 101,
                        'employee_number' => 'EMP-2026-001',
                        'name' => 'Budi Santoso',
                        'email' => 'budi.santoso@perusahaan.com',
                        'department' => 'Teknologi Informasi',
                        'join_date' => '2026-11-01',
                    ],
                ],
            ],
            [
                'event' => 'employee.updated',
                'description' => 'Dipicu ketika terdapat pembaruan data profil master karyawan.',
                'sample_payload' => [
                    'event' => 'employee.updated',
                    'timestamp' => '2026-11-14T10:05:00+07:00',
                    'tenant_id' => 1,
                    'data' => [
                        'id' => 101,
                        'employee_number' => 'EMP-2026-001',
                        'changes' => ['department_id' => 2, 'position_id' => 5],
                    ],
                ],
            ],
            [
                'event' => 'attendance.recorded',
                'description' => 'Dipicu secara instan saat karyawan mencatat clock-in atau clock-out.',
                'sample_payload' => [
                    'event' => 'attendance.recorded',
                    'timestamp' => '2026-11-14T08:02:15+07:00',
                    'tenant_id' => 1,
                    'data' => [
                        'employee_number' => 'EMP-2026-001',
                        'date' => '2026-11-14',
                        'type' => 'clock_in',
                        'time' => '08:02:15',
                        'status' => 'present',
                        'device_id' => 'GATE-LOBBY-01',
                    ],
                ],
            ],
            [
                'event' => 'leave.approved',
                'description' => 'Dipicu ketika atasan/HR menyetujui permohonan cuti karyawan.',
                'sample_payload' => [
                    'event' => 'leave.approved',
                    'timestamp' => '2026-11-14T11:30:00+07:00',
                    'tenant_id' => 1,
                    'data' => [
                        'request_id' => 88,
                        'employee_number' => 'EMP-2026-001',
                        'leave_type' => 'Cuti Tahunan',
                        'start_date' => '2026-12-01',
                        'end_date' => '2026-12-03',
                        'total_days' => 3,
                    ],
                ],
            ],
            [
                'event' => 'payroll.finalized',
                'description' => 'Dipicu ketika perhitungan penggajian bulanan telah disetujui (approved/paid) dan siap diposting ke GL ERP.',
                'sample_payload' => [
                    'event' => 'payroll.finalized',
                    'timestamp' => '2026-11-25T17:00:00+07:00',
                    'tenant_id' => 1,
                    'data' => [
                        'period_id' => 12,
                        'period_name' => 'Payroll November 2026',
                        'total_headcount' => 50,
                        'total_gross' => 250000000.0,
                        'total_thp' => 220000000.0,
                    ],
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'count' => count($events),
            'signature_header' => 'X-RiHRIS-Signature (HMAC-SHA256 with webhook secret)',
            'events' => $events,
        ]);
    }
}
