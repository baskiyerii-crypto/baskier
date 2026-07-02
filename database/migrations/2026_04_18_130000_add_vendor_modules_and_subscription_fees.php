<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('freelancer_enabled')->default(false)->after('is_active');
            $table->timestamp('freelancer_expires_at')->nullable()->after('freelancer_enabled');
            $table->boolean('quotes_enabled')->default(false)->after('freelancer_expires_at');
            $table->timestamp('quotes_expires_at')->nullable()->after('quotes_enabled');
        });

        DB::table('settings')->updateOrInsert(
            ['key' => 'freelancer_monthly_fee'],
            ['value' => '299', 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['key' => 'quotes_monthly_fee'],
            ['value' => '199', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'freelancer_monthly_fee',
            'quotes_monthly_fee',
        ])->delete();

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'freelancer_enabled',
                'freelancer_expires_at',
                'quotes_enabled',
                'quotes_expires_at',
            ]);
        });
    }
};
