<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        \DB::table('settings')->insert([
            ['key' => 'commission_rate', 'value' => '10', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'payout_day_of_month', 'value' => '5', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'commission_wait_days', 'value' => '15', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'meeting_fee', 'value' => '50', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
