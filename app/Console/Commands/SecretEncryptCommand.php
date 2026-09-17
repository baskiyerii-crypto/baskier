<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SecretEncryptCommand extends Command
{
    protected $signature = 'secrets:encrypt';

    protected $description = 'Encrypt all plaintext sensitive API credentials in database settings table';

    private const SENSITIVE_KEYS = [
        'iyzico_secret_key',
        'iyzico_webhook_secret',
        'shopify_admin_token',
        'basitkargo_api_key',
        'openai_api_key',
        'evolution_api_key',
    ];

    public function handle(): int
    {
        $this->info('Scanning database for unencrypted secrets...');

        $count = 0;
        foreach (self::SENSITIVE_KEYS as $key) {
            $row = DB::table('settings')->where('key', $key)->first();
            if ($row && ! empty($row->value) && ! str_starts_with($row->value, 'enc:')) {
                Setting::setSecret($key, $row->value);
                $this->line("  ✓ Encrypted secret for key: {$key}");
                $count++;
            }
        }

        $this->info("Encryption complete. {$count} secret(s) safely encrypted.");

        return Command::SUCCESS;
    }
}
