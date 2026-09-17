<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ooh_inventories') && ! Schema::hasColumn('ooh_inventories', 'geo_fingerprint')) {
            Schema::table('ooh_inventories', function (Blueprint $table) {
                $table->string('geo_fingerprint', 96)->nullable()->after('permit_no');
                $table->index('geo_fingerprint');
            });
        }

        if (Schema::hasTable('ooh_inventories')) {
            $rows = DB::table('ooh_inventories')->select('id', 'permit_no', 'lat', 'lng')->get();
            foreach ($rows as $row) {
                $permit = mb_strtoupper(preg_replace('/\s+/', '', (string) ($row->permit_no ?? '')));
                $fp = $permit.'|'.round((float) $row->lat, 4).'|'.round((float) $row->lng, 4);
                DB::table('ooh_inventories')->where('id', $row->id)->update(['geo_fingerprint' => $fp]);
            }
        }
    }

    public function down(): void
    {
        // Column is part of the OOH inventories schema (also created in 220000).
    }
};
