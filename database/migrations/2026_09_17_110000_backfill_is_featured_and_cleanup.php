<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'is_featured')) {
            DB::table('products')->where('is_featured', true)->update(['is_featured' => false]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backward-compatible no-op: is_featured is deprecated and not restored to true.
    }
};
