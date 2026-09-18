<?php

namespace App\Console\Commands;

use App\Services\EarningsCreditService;
use Illuminate\Console\Command;

class CreditReadyEarningsCommand extends Command
{
    protected $signature = 'earnings:credit-ready';

    protected $description = 'Komisyon bekleme süresi dolan sipariş hakedişini satıcı cüzdanına işler.';

    public function handle(EarningsCreditService $credits): int
    {
        $count = $credits->creditReadyOrders();
        $this->info("Kredilenen sipariş: {$count}");

        return self::SUCCESS;
    }
}
