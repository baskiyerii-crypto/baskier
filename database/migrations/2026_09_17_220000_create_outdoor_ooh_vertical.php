<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (! Schema::hasColumn('vendors', 'outdoor_enabled')) {
                    $table->boolean('outdoor_enabled')->default(false);
                }
                if (! Schema::hasColumn('vendors', 'outdoor_expires_at')) {
                    $table->timestamp('outdoor_expires_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'ooh_vendor_request_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('ooh_vendor_request_id')->nullable()->after('quote_id');
                $table->index('ooh_vendor_request_id');
            });
        }

        if (! Schema::hasTable('vendor_members')) {
            Schema::create('vendor_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('staff_role', 16);
                $table->timestamps();
                $table->unique(['vendor_id', 'user_id']);
                $table->index(['vendor_id', 'staff_role']);
            });
        }

        if (! Schema::hasTable('ooh_inventories')) {
            Schema::create('ooh_inventories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('category_id')->constrained()->restrictOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedTinyInteger('turkiye_il_id')->nullable();
                $table->unsignedInteger('turkiye_ilce_id')->nullable();
                $table->string('city', 80)->nullable();
                $table->string('district', 80)->nullable();
                $table->string('address')->nullable();
                $table->decimal('lat', 10, 7);
                $table->decimal('lng', 10, 7);
                $table->string('permit_no', 64)->nullable();
                $table->string('geo_fingerprint', 96)->nullable();
                $table->decimal('list_price', 12, 2)->nullable();
                $table->string('price_unit', 16)->default('month');
                $table->unsignedSmallInteger('proof_radius_m')->default(75);
                $table->string('status', 32)->default('draft');
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['vendor_id', 'status']);
                $table->index(['status', 'turkiye_il_id']);
                $table->index('permit_no');
                $table->index('geo_fingerprint');
            });

            if (Schema::hasTable('turkiye_iller')) {
                try {
                    Schema::table('ooh_inventories', function (Blueprint $table) {
                        $table->foreign('turkiye_il_id')->references('id')->on('turkiye_iller')->nullOnDelete();
                    });
                } catch (\Throwable) {
                }
            }
            if (Schema::hasTable('turkiye_ilceler')) {
                try {
                    Schema::table('ooh_inventories', function (Blueprint $table) {
                        $table->foreign('turkiye_ilce_id')->references('id')->on('turkiye_ilceler')->nullOnDelete();
                    });
                } catch (\Throwable) {
                }
            }
        }

        if (! Schema::hasTable('ooh_inventory_images')) {
            Schema::create('ooh_inventory_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ooh_inventory_id')->constrained('ooh_inventories')->cascadeOnDelete();
                $table->string('path');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ooh_plans')) {
            Schema::create('ooh_plans', function (Blueprint $table) {
                $table->id();
                $table->string('planner_type', 16);
                $table->foreignId('planner_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('planner_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->string('title')->nullable();
                $table->text('note')->nullable();
                $table->string('status', 32)->default('pending_quotes');
                $table->timestamps();
                $table->index(['planner_user_id', 'status']);
                $table->index(['planner_type', 'status']);
            });
        }

        if (! Schema::hasTable('ooh_plan_items')) {
            Schema::create('ooh_plan_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ooh_plan_id')->constrained('ooh_plans')->cascadeOnDelete();
                $table->foreignId('ooh_inventory_id')->constrained('ooh_inventories')->restrictOnDelete();
                $table->foreignId('owner_vendor_id')->constrained('vendors')->restrictOnDelete();
                $table->date('starts_on');
                $table->date('ends_on');
                $table->decimal('list_price_snapshot', 12, 2)->nullable();
                $table->timestamps();
                $table->index(['ooh_plan_id', 'owner_vendor_id']);
            });
        }

        if (! Schema::hasTable('ooh_vendor_requests')) {
            Schema::create('ooh_vendor_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ooh_plan_id')->constrained('ooh_plans')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('status', 32)->default('pending');
                $table->boolean('vendor_consented')->default(false);
                $table->timestamp('quoted_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->unique(['ooh_plan_id', 'vendor_id']);
                $table->index(['vendor_id', 'status']);
            });
        }

        if (! Schema::hasTable('ooh_quotes')) {
            Schema::create('ooh_quotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ooh_vendor_request_id')->constrained('ooh_vendor_requests')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->text('note')->nullable();
                $table->boolean('vendor_consented')->default(false);
                $table->string('status', 32)->default('pending');
                $table->timestamps();
                $table->index(['ooh_vendor_request_id', 'status']);
            });
        }

        if (! Schema::hasTable('ooh_occupancies')) {
            Schema::create('ooh_occupancies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ooh_inventory_id')->constrained('ooh_inventories')->cascadeOnDelete();
                $table->foreignId('ooh_plan_item_id')->nullable()->constrained('ooh_plan_items')->nullOnDelete();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->date('starts_on');
                $table->date('ends_on');
                $table->string('kind', 16);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->index(['ooh_inventory_id', 'starts_on', 'ends_on']);
                $table->index(['kind', 'expires_at']);
            });
        }

        if (! Schema::hasTable('ooh_proofs')) {
            Schema::create('ooh_proofs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ooh_occupancy_id')->constrained('ooh_occupancies')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('photo_path');
                $table->decimal('lat', 10, 7);
                $table->decimal('lng', 10, 7);
                $table->unsignedInteger('distance_m')->nullable();
                $table->boolean('is_valid')->default(false);
                $table->timestamp('captured_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ooh_inventory_claims')) {
            Schema::create('ooh_inventory_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reporter_vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('ooh_inventory_id')->constrained('ooh_inventories')->cascadeOnDelete();
                $table->text('evidence')->nullable();
                $table->string('permit_no', 64)->nullable();
                $table->string('status', 32)->default('pending');
                $table->text('admin_note')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'ooh_vendor_request_id') && Schema::hasTable('ooh_vendor_requests')) {
            try {
                Schema::table('orders', function (Blueprint $table) {
                    $table->foreign('ooh_vendor_request_id')->references('id')->on('ooh_vendor_requests')->nullOnDelete();
                });
            } catch (\Throwable) {
            }
        }

        if (Schema::hasTable('settings')) {
            $exists = DB::table('settings')->where('key', 'outdoor_monthly_fee')->exists();
            if (! $exists) {
                DB::table('settings')->insert([
                    'key' => 'outdoor_monthly_fee',
                    'value' => '249',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'ooh_vendor_request_id')) {
            Schema::table('orders', function (Blueprint $table) {
                try {
                    $table->dropForeign(['ooh_vendor_request_id']);
                } catch (\Throwable) {
                }
                $table->dropColumn('ooh_vendor_request_id');
            });
        }

        Schema::dropIfExists('ooh_inventory_claims');
        Schema::dropIfExists('ooh_proofs');
        Schema::dropIfExists('ooh_occupancies');
        Schema::dropIfExists('ooh_quotes');
        Schema::dropIfExists('ooh_vendor_requests');
        Schema::dropIfExists('ooh_plan_items');
        Schema::dropIfExists('ooh_plans');
        Schema::dropIfExists('ooh_inventory_images');
        Schema::dropIfExists('ooh_inventories');
        Schema::dropIfExists('vendor_members');

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                foreach (['outdoor_enabled', 'outdoor_expires_at'] as $col) {
                    if (Schema::hasColumn('vendors', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'outdoor_monthly_fee')->delete();
        }
    }
};
