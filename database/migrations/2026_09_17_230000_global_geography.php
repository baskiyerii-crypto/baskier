<?php

use App\Support\IsoCountries;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->char('code', 2)->primary();
                $table->string('name_en', 120);
                $table->string('name_tr', 120);
            });
        }

        foreach (IsoCountries::all() as $code => [$en, $tr]) {
            DB::table('countries')->updateOrInsert(
                ['code' => $code],
                ['name_en' => $en, 'name_tr' => $tr]
            );
        }

        if (! Schema::hasTable('world_places')) {
            Schema::create('world_places', function (Blueprint $table) {
                $table->id();
                $table->char('country_code', 2);
                $table->string('city', 120);
                $table->string('district', 120)->default('');
                $table->timestamps();
                $table->unique(['country_code', 'city', 'district']);
                $table->index(['country_code', 'city']);
            });
        }

        if (Schema::hasTable('vendors') && ! Schema::hasColumn('vendors', 'country_code')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->char('country_code', 2)->default('TR')->after('phone');
                $table->index('country_code');
            });
        }

        if (Schema::hasTable('ooh_inventories') && ! Schema::hasColumn('ooh_inventories', 'country_code')) {
            Schema::table('ooh_inventories', function (Blueprint $table) {
                $table->char('country_code', 2)->default('TR')->after('address');
                $table->index(['status', 'country_code']);
            });
        }

        $this->backfillPlacesFrom('ooh_inventories');
        $this->backfillPlacesFrom('vendors');
    }

    private function backfillPlacesFrom(string $table): void
    {
        if (! Schema::hasTable('world_places') || ! Schema::hasTable($table) || ! Schema::hasColumn($table, 'city')) {
            return;
        }

        $hasCountry = Schema::hasColumn($table, 'country_code');
        $query = DB::table($table)->whereNotNull('city')->where('city', '!=', '');
        $columns = $hasCountry ? ['city', 'district', 'country_code'] : ['city', 'district'];

        foreach ($query->get($columns) as $row) {
            $code = strtoupper((string) ($hasCountry ? ($row->country_code ?? 'TR') : 'TR'));
            if (! IsoCountries::isValid($code)) {
                $code = 'TR';
            }
            $city = mb_substr(trim((string) $row->city), 0, 120);
            if ($city === '') {
                continue;
            }
            DB::table('world_places')->updateOrInsert(
                [
                    'country_code' => $code,
                    'city' => $city,
                    'district' => mb_substr(trim((string) ($row->district ?? '')), 0, 120),
                ],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ooh_inventories') && Schema::hasColumn('ooh_inventories', 'country_code')) {
            Schema::table('ooh_inventories', function (Blueprint $table) {
                $table->dropIndex(['status', 'country_code']);
                $table->dropColumn('country_code');
            });
        }
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'country_code')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropIndex(['country_code']);
                $table->dropColumn('country_code');
            });
        }
        Schema::dropIfExists('world_places');
        Schema::dropIfExists('countries');
    }
};
