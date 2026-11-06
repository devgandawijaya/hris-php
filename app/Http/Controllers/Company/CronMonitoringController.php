<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CronExecutionLog;
use App\Services\Automation\CronAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CronMonitoringController extends Controller
{
    public function __construct(
        protected CronAutomationService $cronAutomationService
    ) {}

    public function index(): View
    {
        $logs = CronExecutionLog::latest('executed_at')->paginate(20);

        $jobs = [
            [
                'command' => 'hris:check-contract-expirations',
                'name' => 'Monitoring Batas Berakhir PKWT (PP 35/2021)',
                'schedule' => 'Setiap Hari pukul 01:00 WIB',
                'description' => 'Memeriksa kontrak PKWT yang akan berakhir dalam 30 hari dan mengirimkan peringatan HR otomatis untuk uang kompensasi atau adendum.',
                'last_log' => CronExecutionLog::where('command_name', 'hris:check-contract-expirations')->latest('executed_at')->first(),
            ],
            [
                'command' => 'hris:expire-warning-letters',
                'name' => 'Kadaluarsa Otomatis Surat Peringatan (UU 13/2003)',
                'schedule' => 'Setiap Hari pukul 02:00 WIB',
                'description' => 'Menghitung masa berlaku 6 bulan Surat Peringatan (SP1/SP2/SP3) sesuai Pasal 161 UU Ketenagakerjaan dan otomatis mengeset status expired.',
                'last_log' => CronExecutionLog::where('command_name', 'hris:expire-warning-letters')->latest('executed_at')->first(),
            ],
            [
                'command' => 'hris:check-probation-expirations',
                'name' => 'Monitoring Masa Percobaan PKWTT (Pasal 60 UU Ketenagakerjaan)',
                'schedule' => 'Setiap Hari pukul 03:00 WIB',
                'description' => 'Mendeteksi karyawan baru PKWTT yang mendekati batas maksimum masa percobaan 3 bulan untuk evaluasi pengangkatan tetap.',
                'last_log' => CronExecutionLog::where('command_name', 'hris:check-probation-expirations')->latest('executed_at')->first(),
            ],
            [
                'command' => 'hris:process-loan-installments',
                'name' => 'Sinkronisasi Angsuran Kasbon Gaji Jatuh Tempo (PP 36/2021)',
                'schedule' => 'Setiap Hari pukul 04:00 WIB',
                'description' => 'Menyiapkan pemotongan cicilan pinjaman/kasbon yang jatuh tempo untuk digabungkan ke kalkulasi payroll bulanan.',
                'last_log' => CronExecutionLog::where('command_name', 'hris:process-loan-installments')->latest('executed_at')->first(),
            ],
        ];

        return view('company.cron.index', compact('logs', 'jobs'));
    }

    public function runCommand(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'command' => 'required|in:hris:check-contract-expirations,hris:expire-warning-letters,hris:check-probation-expirations,hris:process-loan-installments',
        ]);

        $command = $validated['command'];

        $result = match ($command) {
            'hris:check-contract-expirations' => $this->cronAutomationService->checkContractExpirations(),
            'hris:expire-warning-letters' => $this->cronAutomationService->expireWarningLetters(),
            'hris:check-probation-expirations' => $this->cronAutomationService->checkProbationExpirations(),
            'hris:process-loan-installments' => $this->cronAutomationService->processLoanInstallments(),
        };

        return redirect()->route('company.cron.index')
            ->with('success', "Perintah [{$command}] berhasil dijalankan secara manual. {$result['summary']}");
    }
}
