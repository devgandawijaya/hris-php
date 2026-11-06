<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Automation\CronAutomationService;

class CheckProbationExpirationsCommand extends Command
{
    protected $signature = 'hris:check-probation-expirations';
    protected $description = 'Periksa karyawan masa percobaan PKWTT yang mendekati batas maksimal 3 bulan (Pasal 60 UU Ketenagakerjaan).';

    public function handle(CronAutomationService $automationService): int
    {
        $this->info('Memulai pengecekan masa percobaan (probasi) karyawan PKWTT...');
        $result = $automationService->checkProbationExpirations();

        $this->info("Pengecekan selesai. {$result['summary']}");
        return Command::SUCCESS;
    }
}
