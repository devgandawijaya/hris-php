<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Automation\CronAutomationService;

class ProcessLoanInstallmentsCommand extends Command
{
    protected $signature = 'hris:process-loan-installments';
    protected $description = 'Periksa angsuran kasbon/pinjaman yang jatuh tempo dalam 7 hari untuk persiapan pemotongan payroll.';

    public function handle(CronAutomationService $automationService): int
    {
        $this->info('Memulai pengecekan angsuran pinjaman jatuh tempo...');
        $result = $automationService->processLoanInstallments();

        $this->info("Pengecekan selesai. {$result['summary']}");
        return Command::SUCCESS;
    }
}
