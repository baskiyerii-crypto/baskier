<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turkiye_mahalleler', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedInteger('district_id');
            $table->unsignedTinyInteger('province_id');
            $table->string('name', 120);
            $table->timestamps();
            $table->foreign('district_id')->references('id')->on('turkiye_ilceler')->cascadeOnDelete();
            $table->foreign('province_id')->references('id')->on('turkiye_iller')->cascadeOnDelete();
            $table->index('district_id');
            $table->index('province_id');
        });

        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'turkiye_neighborhood_id')) {
                $table->unsignedBigInteger('turkiye_neighborhood_id')->nullable()->after('turkiye_district_id');
                $table->string('neighborhood', 120)->nullable()->after('district');
                $table->foreign('turkiye_neighborhood_id')->references('id')->on('turkiye_mahalleler')->nullOnDelete();
                $table->index('turkiye_neighborhood_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'turkiye_neighborhood_id')) {
                $table->dropForeign(['turkiye_neighborhood_id']);
                $table->dropColumn('turkiye_neighborhood_id');
            }
            if (Schema::hasColumn('addresses', 'neighborhood')) {
                $table->dropColumn('neighborhood');
            }
        });
        Schema::dropIfExists('turkiye_mahalleler');
    }
};
