<?php

namespace App\Services\Notification;

use App\Models\Employee;
use App\Models\WhatsappNotificationLog;

class WhatsAppNotificationService
{
    /**
     * Dispatch WhatsApp notification for published payslip.
     */
    public function sendPayslipReady(Employee $employee, string $periodName, float $thp): WhatsappNotificationLog
    {
        $message = "Halo {$employee->first_name},\n\n"
            . "Slip gaji Anda untuk periode *{$periodName}* telah diterbitkan di portal RiHRIS.\n"
            . "Total Take Home Pay: *Rp " . number_format($thp, 0, ',', '.') . "*\n\n"
            . "Silakan login ke portal Employee Self-Service (ESS) untuk mengunduh rincian bukti potong & slip gaji digital Anda.\n\n"
            . "Terima kasih,\nTim Human Capital";

        return $this->dispatch($employee->tenant_id, $employee->phone ?? '+628000000000', 'payslip_ready', $message);
    }

    /**
     * Dispatch WhatsApp notification for leave approval or rejection.
     */
    public function sendLeaveStatusUpdate(Employee $employee, string $leaveType, string $status, ?string $reason = null): WhatsappNotificationLog
    {
        $statusIndo = $status === 'approved' ? 'DISETUJUI ✅' : 'DITOLAK ❌';
        $message = "Halo {$employee->first_name},\n\n"
            . "Pengajuan cuti *{$leaveType}* Anda telah {$statusIndo}.\n";

        if ($reason) {
            $message .= "Catatan/Alasan: {$reason}\n\n";
        }

        $message .= "Terima kasih,\nTim Human Capital";

        return $this->dispatch($employee->tenant_id, $employee->phone ?? '+628000000000', 'leave_status', $message);
    }

    /**
     * Dispatch WhatsApp notification for upcoming contract expiration.
     */
    public function sendContractRenewalNotice(Employee $employee, string $contractNo, string $endDate): WhatsappNotificationLog
    {
        $message = "Halo {$employee->first_name},\n\n"
            . "Pemberitahuan bahwa perjanjian kerja nomor *{$contractNo}* akan berakhir pada *{$endDate}*.\n"
            . "Tim HR akan segera mengoordinasikan evaluasi masa kerja dan kompensasi sesuai PP No. 35/2021.\n\n"
            . "Terima kasih,\nTim Human Capital";

        return $this->dispatch($employee->tenant_id, $employee->phone ?? '+628000000000', 'contract_renewal', $message);
    }

    /**
     * Base dispatch mechanism recording to logs table.
     */
    protected function dispatch(int $tenantId, string $phone, string $template, string $message): WhatsappNotificationLog
    {
        // Normalizing phone format
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        return WhatsappNotificationLog::create([
            'tenant_id' => $tenantId,
            'phone_number' => $cleanPhone,
            'template_name' => $template,
            'message_content' => $message,
            'status' => 'sent',
            'error_message' => null,
        ]);
    }
}
