<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Automation\CronAutomationService;

class ExpireWarningLettersCommand extends Command
{
    protected $signature = 'hris:expire-warning-letters';
    protected $description = 'Otomatis ubah status Surat Peringatan (SP) yang telah melewati 6 bulan menjadi expired (Pasal 161 UU 13/2003).';

    public function handle(CronAutomationService $automationService): int
    {
        $this->info('Memulai pengecekan masa berlaku Surat Peringatan (SP)...');
        $result = $automationService->expireWarningLetters();

        $this->info("Pengecekan selesai. {$result['summary']}");
        return Command::SUCCESS;
    }
}

