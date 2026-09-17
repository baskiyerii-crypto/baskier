<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (! Schema::hasColumn('vendors', 'cover_image')) {
                    $table->string('cover_image')->nullable()->after('logo');
                }
                if (! Schema::hasColumn('vendors', 'ozalit_enabled')) {
                    $table->boolean('ozalit_enabled')->default(false);
                }
                if (! Schema::hasColumn('vendors', 'ozalit_expires_at')) {
                    $table->timestamp('ozalit_expires_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (! Schema::hasColumn('messages', 'display_body')) {
                    $table->text('display_body')->nullable()->after('body');
                }
                if (! Schema::hasColumn('messages', 'moderation_flags')) {
                    $table->json('moderation_flags')->nullable()->after('blocked');
                }
            });
        }

        if (! Schema::hasTable('platform_brands')) {
            Schema::create('platform_brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('logo')->nullable();
                $table->string('website_url')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_questions')) {
            Schema::create('product_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->text('question');
                $table->text('answer')->nullable();
                $table->timestamp('answered_at')->nullable();
                $table->string('status', 32)->default('open');
                $table->timestamps();
                $table->index(['vendor_id', 'status']);
                $table->index(['customer_id', 'status']);
            });
        }

        if (! Schema::hasTable('order_questions')) {
            Schema::create('order_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->string('asked_by', 16);
                $table->string('subject')->nullable();
                $table->string('status', 32)->default('open');
                $table->timestamps();
                $table->index(['vendor_id', 'status']);
                $table->index(['customer_id', 'status']);
            });
        }

        if (! Schema::hasTable('order_question_replies')) {
            Schema::create('order_question_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_question_id')->constrained('order_questions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('body');
                $table->boolean('is_from_vendor')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_question_replies');
        Schema::dropIfExists('order_questions');
        Schema::dropIfExists('product_questions');
        Schema::dropIfExists('platform_brands');

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                foreach (['display_body', 'moderation_flags'] as $col) {
                    if (Schema::hasColumn('messages', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                foreach (['cover_image', 'ozalit_enabled', 'ozalit_expires_at'] as $col) {
                    if (Schema::hasColumn('vendors', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
