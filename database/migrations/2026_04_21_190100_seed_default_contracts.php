<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $defaults = [
            [
                'key' => 'terms',
                'title' => 'Kullanım Koşulları',
                'audience' => 'all',
                'version' => 1,
            ],
            [
                'key' => 'privacy',
                'title' => 'Gizlilik Politikası',
                'audience' => 'all',
                'version' => 1,
            ],
            [
                'key' => 'vendor_agreement',
                'title' => 'Satıcı Sözleşmesi',
                'audience' => 'vendor',
                'version' => 1,
            ],
            [
                'key' => 'distance_sales',
                'title' => 'Mesafeli Satış Sözleşmesi',
                'audience' => 'customer',
                'version' => 1,
            ],
        ];

        foreach ($defaults as $d) {
            DB::table('contracts')->updateOrInsert(
                ['key' => $d['key']],
                [
                    'title' => $d['title'],
                    'audience' => $d['audience'],
                    'version' => $d['version'],
                    'is_active' => true,
                    'content_html' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('contracts')->whereIn('key', ['terms', 'privacy', 'vendor_agreement', 'distance_sales'])->delete();
    }
};

