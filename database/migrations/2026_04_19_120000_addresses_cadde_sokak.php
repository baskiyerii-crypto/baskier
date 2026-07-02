<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'cadde')) {
                $table->string('cadde', 120)->nullable()->after('neighborhood');
            }
            if (! Schema::hasColumn('addresses', 'sokak')) {
                $table->string('sokak', 120)->nullable()->after('cadde');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'sokak')) {
                $table->dropColumn('sokak');
            }
            if (Schema::hasColumn('addresses', 'cadde')) {
                $table->dropColumn('cadde');
            }
        });
    }
};
