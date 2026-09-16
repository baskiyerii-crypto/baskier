<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (! Schema::hasColumn('products', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
                if (! Schema::hasColumn('products', 'short_description_en')) {
                    $table->text('short_description_en')->nullable()->after('short_description');
                }
                if (! Schema::hasColumn('products', 'description_en')) {
                    $table->longText('description_en')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                foreach (['name_en', 'short_description_en', 'description_en'] as $col) {
                    if (Schema::hasColumn('products', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'name_en')) {
                    $table->dropColumn('name_en');
                }
            });
        }
    }
};
