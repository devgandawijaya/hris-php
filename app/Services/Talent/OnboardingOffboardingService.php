<?php

namespace App\Services\Talent;

use App\Models\CompanyAsset;
use App\Models\Employee;
use App\Models\ExitInterview;
use App\Models\OnboardingOffboardingChecklist;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OnboardingOffboardingService
{
    /**
     * Standard Onboarding Tasks Template.
     */
    protected array $defaultOnboardingTasks = [
        'Pengumpulan & Verifikasi Berkas (KTP, NPWP, Ijazah, Buku Nikah)',
        'Pembuatan Akun Email Perusahaan & Akses Sistem RiHRIS',
        'Penyerahan Kartu Tanda Pengenal (ID Card) & Akses Gedung',
        'Penyerahan Laptop & Perlengkapan Kerja Kantor (Handover Asset)',
        'Sesi Orientasi Kebijakan Perusahaan, Peraturan Kerja & Budaya',
        'Pendaftaran Kepesertaan BPJS Ketenagakerjaan & BPJS Kesehatan',
    ];

    /**
     * Standard Offboarding Tasks Template.
     */
    protected array $defaultOffboardingTasks = [
        'Penyelesaian Serah Terima Tugas & Dokumentasi Pekerjaan (Handover)',
        'Pengembalian Laptop, Charger, dan Perangkat Kantor',
        'Pengembalian ID Card & Akses Pintu/Gedung',
        'Penonaktifan Akun Email Perusahaan & Akses Sistem Internal',
        'Pelaksanaan Sesi Wawancara Keluar (Exit Interview)',
        'Penerbitan Surat Keterangan Kerja (Paklaring) & Penghitungan Hak Akhir',
    ];

    /**
     * Initialize Onboarding checklist for an employee.
     */
    public function initializeOnboarding(Employee $employee, ?User $assignee = null): void
    {
        DB::transaction(function () use ($employee, $assignee) {
            foreach ($this->defaultOnboardingTasks as $taskTitle) {
                OnboardingOffboardingChecklist::firstOrCreate(
                    [
                        'tenant_id' => $employee->tenant_id,
                        'employee_id' => $employee->id,
                        'type' => 'onboarding',
                        'title' => $taskTitle,
                    ],
                    [
                        'assigned_to_user_id' => $assignee?->id,
                        'is_completed' => false,
                    ]
                );
            }
        });
    }

    /**
     * Initialize Offboarding checklist for an employee.
     */
    public function initializeOffboarding(Employee $employee, ?User $assignee = null): void
    {
        DB::transaction(function () use ($employee, $assignee) {
            foreach ($this->defaultOffboardingTasks as $taskTitle) {
                OnboardingOffboardingChecklist::firstOrCreate(
                    [
                        'tenant_id' => $employee->tenant_id,
                        'employee_id' => $employee->id,
                        'type' => 'offboarding',
                        'title' => $taskTitle,
                    ],
                    [
                        'assigned_to_user_id' => $assignee?->id,
                        'is_completed' => false,
                    ]
                );
            }
        });
    }

    /**
     * Complete a checklist task.
     */
    public function completeTask(OnboardingOffboardingChecklist $task): void
    {
        $task->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Assign a company asset to an employee.
     */
    public function assignAsset(CompanyAsset $asset, Employee $employee): CompanyAsset
    {
        $asset->update([
            'assigned_employee_id' => $employee->id,
            'assigned_date' => now()->toDateString(),
            'status' => 'assigned',
        ]);

        return $asset;
    }

    /**
     * Return a company asset (back to available inventory).
     */
    public function returnAsset(CompanyAsset $asset): CompanyAsset
    {
        $asset->update([
            'assigned_employee_id' => null,
            'assigned_date' => null,
            'status' => 'available',
        ]);

        return $asset;
    }

    /**
     * Record exit interview.
     */
    public function recordExitInterview(Employee $employee, array $data, ?User $interviewer = null): ExitInterview
    {
        return ExitInterview::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
            ],
            [
                'interview_date' => $data['interview_date'] ?? now()->toDateString(),
                'reason_for_leaving' => $data['reason_for_leaving'] ?? 'other',
                'feedback' => $data['feedback'] ?? null,
                'willing_to_recommend' => $data['willing_to_recommend'] ?? true,
                'interviewer_user_id' => $interviewer?->id,
            ]
        );
    }
}
