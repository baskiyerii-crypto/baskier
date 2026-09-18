<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('turkiye_iller')) {
            Schema::table('turkiye_iller', function (Blueprint $table) {
                if (! Schema::hasColumn('turkiye_iller', 'population')) {
                    $table->unsignedInteger('population')->nullable();
                }
                if (! Schema::hasColumn('turkiye_iller', 'population_year')) {
                    $table->unsignedSmallInteger('population_year')->nullable();
                }
            });
        }

        if (Schema::hasTable('turkiye_ilceler')) {
            Schema::table('turkiye_ilceler', function (Blueprint $table) {
                if (! Schema::hasColumn('turkiye_ilceler', 'population')) {
                    $table->unsignedInteger('population')->nullable();
                }
                if (! Schema::hasColumn('turkiye_ilceler', 'population_year')) {
                    $table->unsignedSmallInteger('population_year')->nullable();
                }
            });
        }

        if (! Schema::hasTable('ooh_kgm_traffic')) {
            Schema::create('ooh_kgm_traffic', function (Blueprint $table) {
                $table->id();
                $table->string('road_ref', 16);
                $table->string('name', 120)->nullable();
                $table->unsignedInteger('aadt');
                $table->unsignedSmallInteger('year');
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->timestamps();
                $table->index('road_ref');
            });
        }

        if (Schema::hasTable('ooh_inventories')) {
            Schema::table('ooh_inventories', function (Blueprint $table) {
                if (! Schema::hasColumn('ooh_inventories', 'face_width_m')) {
                    $table->decimal('face_width_m', 6, 2)->nullable();
                }
                if (! Schema::hasColumn('ooh_inventories', 'face_height_m')) {
                    $table->decimal('face_height_m', 6, 2)->nullable();
                }
                if (! Schema::hasColumn('ooh_inventories', 'facing')) {
                    $table->string('facing', 8)->nullable();
                }
                if (! Schema::hasColumn('ooh_inventories', 'illuminated')) {
                    $table->boolean('illuminated')->default(false);
                }
                if (! Schema::hasColumn('ooh_inventories', 'qr_token')) {
                    $table->string('qr_token', 8)->nullable()->unique();
                }
            });
        }

        if (! Schema::hasTable('ooh_location_insights')) {
            Schema::create('ooh_location_insights', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ooh_inventory_id')->constrained('ooh_inventories')->cascadeOnDelete();
                $table->unsignedInteger('population_province')->nullable();
                $table->unsignedInteger('population_district')->nullable();
                $table->unsignedSmallInteger('population_year')->nullable();
                $table->string('road_class', 32)->nullable();
                $table->string('road_name', 120)->nullable();
                $table->string('road_ref', 16)->nullable();
                $table->unsignedSmallInteger('maxspeed')->nullable();
                $table->unsignedTinyInteger('lanes')->nullable();
                $table->unsignedInteger('vehicle_aadt')->nullable();
                $table->unsignedSmallInteger('vehicle_aadt_year')->nullable();
                $table->string('vehicle_source', 16)->nullable();
                $table->string('pedestrian_kind', 24)->nullable();
                $table->string('visibility_band', 8)->nullable();
                $table->boolean('street_view_available')->default(false);
                $table->timestamp('enriched_at')->nullable();
                $table->timestamps();
                $table->unique('ooh_inventory_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ooh_location_insights');

        if (Schema::hasTable('ooh_inventories')) {
            Schema::table('ooh_inventories', function (Blueprint $table) {
                foreach (['face_width_m', 'face_height_m', 'facing', 'illuminated', 'qr_token'] as $col) {
                    if (Schema::hasColumn('ooh_inventories', $col)) {
                        if ($col === 'qr_token') {
                            $table->dropUnique(['qr_token']);
                        }
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('ooh_kgm_traffic');

        if (Schema::hasTable('turkiye_iller')) {
            Schema::table('turkiye_iller', function (Blueprint $table) {
                foreach (['population', 'population_year'] as $col) {
                    if (Schema::hasColumn('turkiye_iller', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('turkiye_ilceler')) {
            Schema::table('turkiye_ilceler', function (Blueprint $table) {
                foreach (['population', 'population_year'] as $col) {
                    if (Schema::hasColumn('turkiye_ilceler', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
