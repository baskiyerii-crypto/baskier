<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 32)->nullable()->after('email');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getConnection()->getSchemaBuilder();
            $indexes = $sm->getIndexes('users');
            $hasUnique = false;
            foreach ($indexes as $index) {
                $cols = $index['columns'] ?? [];
                if (($index['unique'] ?? false) && $cols === ['phone']) {
                    $hasUnique = true;
                    break;
                }
            }
            if (! $hasUnique) {
                $table->unique('phone');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'phone')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getConnection()->getSchemaBuilder();
            $indexes = $sm->getIndexes('users');
            foreach ($indexes as $index) {
                $cols = $index['columns'] ?? [];
                if (($index['unique'] ?? false) && $cols === ['phone']) {
                    $table->dropUnique(['phone']);
                    break;
                }
            }
        });
    }
};
