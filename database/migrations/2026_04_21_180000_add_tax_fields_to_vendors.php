<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'company_name')) {
                $table->string('company_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('vendors', 'tax_office')) {
                $table->string('tax_office')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('vendors', 'tax_number')) {
                $table->string('tax_number', 32)->nullable()->after('tax_office');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'tax_number')) $table->dropColumn('tax_number');
            if (Schema::hasColumn('vendors', 'tax_office')) $table->dropColumn('tax_office');
            if (Schema::hasColumn('vendors', 'company_name')) $table->dropColumn('company_name');
        });
    }
};

