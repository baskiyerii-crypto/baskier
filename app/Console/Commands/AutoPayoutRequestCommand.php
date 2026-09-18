<?php

namespace App\Console\Commands;

use App\Services\PayoutService;
use Illuminate\Console\Command;

class AutoPayoutRequestCommand extends Command
{
    protected $signature = 'payouts:auto-request';

    protected $description = 'Unutulan çekilebilir bakiyeler için otomatik para çekme talebi açar.';

    public function handle(PayoutService $payouts): int
    {
        $count = $payouts->autoRequestForgotten();
        $this->info("Otomatik talep: {$count}");

        return self::SUCCESS;
    }
}
