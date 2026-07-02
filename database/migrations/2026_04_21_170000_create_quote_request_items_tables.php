<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quote_request_items')) {
            Schema::create('quote_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_request_id')->constrained('quote_requests')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->unsignedInteger('quantity')->nullable();
                $table->string('unit', 32)->nullable();
                $table->text('spec')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['quote_request_id', 'category_id']);
            });
        }

        if (! Schema::hasTable('quote_request_item_files')) {
            Schema::create('quote_request_item_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_request_item_id')->constrained('quote_request_items')->cascadeOnDelete();
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->string('mime', 128)->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_item_files');
        Schema::dropIfExists('quote_request_items');
    }
};

