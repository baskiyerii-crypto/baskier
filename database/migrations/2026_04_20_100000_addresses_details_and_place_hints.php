<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'bina_no')) {
                $table->string('bina_no', 60)->nullable()->after('sokak');
            }
            if (! Schema::hasColumn('addresses', 'ic_kapi_no')) {
                $table->string('ic_kapi_no', 60)->nullable()->after('bina_no');
            }
        });

        if (! Schema::hasTable('address_place_hints')) {
            Schema::create('address_place_hints', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('district_id');
                $table->string('cadde', 120)->nullable();
                $table->string('sokak', 120)->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
                $table->foreign('district_id')->references('id')->on('turkiye_ilceler')->cascadeOnDelete();
                $table->unique(['district_id', 'cadde', 'sokak'], 'addr_place_hint_unique');
                $table->index('last_used_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('address_place_hints');

        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'ic_kapi_no')) {
                $table->dropColumn('ic_kapi_no');
            }
            if (Schema::hasColumn('addresses', 'bina_no')) {
                $table->dropColumn('bina_no');
            }
        });
    }
};
