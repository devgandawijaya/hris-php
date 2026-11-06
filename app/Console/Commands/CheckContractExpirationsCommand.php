<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Automation\CronAutomationService;

class CheckContractExpirationsCommand extends Command
{
    protected $signature = 'hris:check-contract-expirations';
    protected $description = 'Periksa kontrak kerja PKWT yang akan berakhir dalam 30 hari ke depan dan kirim notifikasi otomatis (PP 35/2021).';

    public function handle(CronAutomationService $automationService): int
    {
        $this->info('Memulai pengecekan masa berlaku kontrak PKWT...');
        $result = $automationService->checkContractExpirations();

        $this->info("Pengecekan selesai. {$result['summary']}");
        return Command::SUCCESS;
    }
}

