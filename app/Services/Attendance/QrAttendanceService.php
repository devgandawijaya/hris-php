<?php

namespace App\Services\Attendance;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\BranchQrKiosk;
use App\Models\Employee;
use Illuminate\Support\Str;

class QrAttendanceService
{
    /**
     * Create or refresh QR code for a branch kiosk.
     */
    public function generateOrRefreshKiosk(Branch $branch, string $kioskName = 'Kios Utama'): BranchQrKiosk
    {
        $token = Str::random(32);
        $qrPayload = 'RIHRIS-KIOSK:' . $branch->id . ':' . $token . ':' . time();

        return BranchQrKiosk::updateOrCreate(
            [
                'tenant_id' => $branch->tenant_id,
                'branch_id' => $branch->id,
                'kiosk_name' => $kioskName,
            ],
            [
                'secret_token' => $token,
                'current_qr_code' => $qrPayload,
                'qr_refreshed_at' => now(),
                'is_active' => true,
            ]
        );
    }

    /**
     * Verify QR scan and record employee touchless attendance.
     */
    public function recordQrAttendance(
        Employee $employee,
        string $qrCode,
        float $latitude,
        float $longitude
    ): array {
        // Parse payload format: RIHRIS-KIOSK:{branch_id}:{token}:{timestamp}
        $parts = explode(':', $qrCode);
        if (count($parts) < 4 || $parts[0] !== 'RIHRIS-KIOSK') {
            return [
                'success' => false,
                'message' => 'Format QR Code tidak valid atau bukan QR Kios resmi RiHRIS.',
            ];
        }

        $branchId = (int) $parts[1];
        $token = $parts[2];

        $kiosk = BranchQrKiosk::with('branch')
            ->where('tenant_id', $employee->tenant_id)
            ->where('branch_id', $branchId)
            ->where('secret_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$kiosk) {
            return [
                'success' => false,
                'message' => 'QR Code Kios telah kedaluwarsa atau tidak terdaftar pada cabang ini.',
            ];
        }

        $branch = $kiosk->branch;

        // Verify distance against branch GPS geofence
        $distanceMeters = $this->calculateDistance(
            $latitude,
            $longitude,
            (float) $branch->latitude,
            (float) $branch->longitude
        );

        $allowedRadius = $branch->radius_meters ?: 100;
        if ($distanceMeters > ($allowedRadius + 50)) { // 50m tolerance for GPS jitter
            return [
                'success' => false,
                'message' => "Posisi Anda berada di luar radius kantor ({$distanceMeters}m dari lokasi cabang, batas: {$allowedRadius}m).",
            ];
        }

        $today = now()->toDateString();
        $record = AttendanceRecord::firstOrNew([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        $action = 'clock_in';
        if ($record->exists && $record->clock_in && !$record->clock_out) {
            // Clock Out
            $record->clock_out = now()->toTimeString();
            $record->clock_out_latitude = $latitude;
            $record->clock_out_longitude = $longitude;
            $record->save();
            $action = 'clock_out';
        } else {
            // Clock In
            $record->clock_in = now()->toTimeString();
            $record->clock_in_latitude = $latitude;
            $record->clock_in_longitude = $longitude;
            $record->status = 'present';
            $record->save();
            $action = 'clock_in';
        }

        return [
            'success' => true,
            'action' => $action,
            'message' => $action === 'clock_in' ? 'Presensi Masuk via QR Kios berhasil dicatat!' : 'Presensi Pulang via QR Kios berhasil dicatat!',
            'time' => now()->format('H:i:s'),
            'branch' => $branch->name,
            'record' => $record,
        ];
    }

    /**
     * Haversine distance in meters.
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }
}
