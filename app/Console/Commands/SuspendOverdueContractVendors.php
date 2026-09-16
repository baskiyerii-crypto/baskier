<?php

namespace App\Console\Commands;

use App\Services\ContractPublishService;
use Illuminate\Console\Command;

class SuspendOverdueContractVendors extends Command
{
    protected $signature = 'contracts:suspend-overdue';

    protected $description = 'Suspend vendors who missed contract acceptance deadline';

    public function handle(ContractPublishService $service): int
    {
        $count = $service->suspendOverdue();
        $this->info("Suspended {$count} vendor(s).");

        return self::SUCCESS;
    }
}
