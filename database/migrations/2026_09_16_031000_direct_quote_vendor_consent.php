<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('direct_quote_requests') && ! Schema::hasColumn('direct_quote_requests', 'vendor_consented')) {
            Schema::table('direct_quote_requests', function (Blueprint $table) {
                $table->boolean('vendor_consented')->default(false)->after('vendor_note');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('direct_quote_requests') && Schema::hasColumn('direct_quote_requests', 'vendor_consented')) {
            Schema::table('direct_quote_requests', function (Blueprint $table) {
                $table->dropColumn('vendor_consented');
            });
        }
    }
};
