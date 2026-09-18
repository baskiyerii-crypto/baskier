<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('outdoor_crews')) {
            Schema::create('outdoor_crews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('name', 80);
                $table->timestamps();
                $table->unique(['vendor_id', 'name']);
            });
        }

        if (Schema::hasTable('vendor_members') && ! Schema::hasColumn('vendor_members', 'crew_id')) {
            Schema::table('vendor_members', function (Blueprint $table) {
                $table->foreignId('crew_id')->nullable()->after('staff_role')->constrained('outdoor_crews')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('ooh_inventory_grants')) {
            Schema::create('ooh_inventory_grants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('crew_id')->nullable()->constrained('outdoor_crews')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->timestamp('starts_at');
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index('vendor_id');
            });
        }

        if (Schema::hasTable('ooh_inventories') && ! Schema::hasColumn('ooh_inventories', 'created_by_user_id')) {
            Schema::table('ooh_inventories', function (Blueprint $table) {
                $table->foreignId('created_by_user_id')->nullable()->after('vendor_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ooh_inventories') && Schema::hasColumn('ooh_inventories', 'created_by_user_id')) {
            Schema::table('ooh_inventories', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by_user_id');
            });
        }

        Schema::dropIfExists('ooh_inventory_grants');

        if (Schema::hasTable('vendor_members') && Schema::hasColumn('vendor_members', 'crew_id')) {
            Schema::table('vendor_members', function (Blueprint $table) {
                $table->dropConstrainedForeignId('crew_id');
            });
        }

        Schema::dropIfExists('outdoor_crews');
    }
};
