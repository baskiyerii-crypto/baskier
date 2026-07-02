<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turkiye_iller', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name', 64);
        });

        Schema::create('turkiye_ilceler', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedTinyInteger('province_id');
            $table->string('name', 80);
            $table->char('postal_code', 5);
            $table->timestamps();
            $table->foreign('province_id')->references('id')->on('turkiye_iller')->cascadeOnDelete();
            $table->index('province_id');
            $table->index('postal_code');
        });

        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'turkiye_district_id')) {
                $table->unsignedInteger('turkiye_district_id')->nullable()->after('user_id');
                $table->foreign('turkiye_district_id')->references('id')->on('turkiye_ilceler')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'turkiye_district_id')) {
                $table->dropForeign(['turkiye_district_id']);
                $table->dropColumn('turkiye_district_id');
            }
        });
        Schema::dropIfExists('turkiye_ilceler');
        Schema::dropIfExists('turkiye_iller');
    }
};
