<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('business_type_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['vendor_id', 'business_type_id']);
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default('Ev');
            $table->string('full_name');
            $table->string('phone', 32);
            $table->string('city', 100);
            $table->string('district', 100)->nullable();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique('order_id');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('rating_average', 3, 2)->nullable()->after('balance');
            $table->unsignedInteger('reviews_count')->default(0)->after('rating_average');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['rating_average', 'reviews_count']);
        });
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('business_type_vendor');
        Schema::dropIfExists('business_types');
    }
};
