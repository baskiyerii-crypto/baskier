<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'moderation_status')) {
                $table->string('moderation_status', 32)->default('approved')->after('is_active');
                $table->index('moderation_status');
            }
            if (! Schema::hasColumn('products', 'moderation_note')) {
                $table->string('moderation_note', 500)->nullable()->after('moderation_status');
            }
            if (! Schema::hasColumn('products', 'submitted_for_moderation_at')) {
                $table->timestamp('submitted_for_moderation_at')->nullable()->after('moderation_note');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'submitted_for_moderation_at')) {
                $table->dropColumn('submitted_for_moderation_at');
            }
            if (Schema::hasColumn('products', 'moderation_note')) {
                $table->dropColumn('moderation_note');
            }
            if (Schema::hasColumn('products', 'moderation_status')) {
                $table->dropIndex(['moderation_status']);
                $table->dropColumn('moderation_status');
            }
        });
    }
};
