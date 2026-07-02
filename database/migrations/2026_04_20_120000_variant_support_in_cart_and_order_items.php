<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'variant_id')) {
                $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_user_id_product_id_unique');
            $table->unique(['user_id', 'product_id', 'variant_id'], 'cart_items_user_product_variant_unique');
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'variant_id')) {
                $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }
            if (! Schema::hasColumn('order_items', 'variant_name')) {
                $table->string('variant_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('order_items', 'variant_sku')) {
                $table->string('variant_sku')->nullable()->after('sku');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'variant_id')) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            }
            if (Schema::hasColumn('order_items', 'variant_name')) {
                $table->dropColumn('variant_name');
            }
            if (Schema::hasColumn('order_items', 'variant_sku')) {
                $table->dropColumn('variant_sku');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_user_product_variant_unique');
            $table->unique(['user_id', 'product_id']);
            if (Schema::hasColumn('cart_items', 'variant_id')) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            }
        });
    }
};
