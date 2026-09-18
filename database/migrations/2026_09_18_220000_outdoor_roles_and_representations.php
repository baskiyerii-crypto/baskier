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
                if (! Schema::hasColumn('vendors', 'outdoor_role')) {
                    $table->string('outdoor_role', 16)->nullable()->after('outdoor_expires_at');
                    $table->index('outdoor_role');
                }
                if (! Schema::hasColumn('vendors', 'owner_kind')) {
                    $table->string('owner_kind', 24)->nullable()->after('outdoor_role');
                }
            });
        }

        if (! Schema::hasTable('ooh_representations')) {
            Schema::create('ooh_representations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('agency_vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('status', 16)->default('pending');
                $table->boolean('exclusive')->default(false);
                $table->decimal('commission_rate', 5, 2)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('invited_by_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
                $table->unique(['owner_vendor_id', 'agency_vendor_id']);
                $table->index(['agency_vendor_id', 'status']);
                $table->index(['owner_vendor_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ooh_representations');

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                foreach (['owner_kind', 'outdoor_role'] as $col) {
                    if (Schema::hasColumn('vendors', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
