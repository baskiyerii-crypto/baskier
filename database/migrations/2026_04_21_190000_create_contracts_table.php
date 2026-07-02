<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contracts')) {
            Schema::create('contracts', function (Blueprint $table) {
                $table->id();
                $table->string('key', 64)->unique();
                $table->string('title', 255);
                $table->string('audience', 16)->default('all'); // all|customer|vendor
                $table->unsignedInteger('version')->default(1);
                $table->boolean('is_active')->default(true);
                $table->longText('content_html')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

