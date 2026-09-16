<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 32)->nullable()->after('email');
            });
        }
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'phone_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'public_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('public_id', 6)->nullable()->unique()->after('id');
            });
        }

        if (Schema::hasColumn('users', 'public_id')) {
            $users = DB::table('users')->whereNull('public_id')->orderBy('id')->get(['id']);
            $used = DB::table('users')->whereNotNull('public_id')->pluck('public_id')->flip()->all();
            foreach ($users as $user) {
                do {
                    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                } while (isset($used[$code]));
                $used[$code] = true;
                DB::table('users')->where('id', $user->id)->update(['public_id' => $code]);
            }
        }

        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'commission_rate_override')) {
            // Column kept for rollback safety but cleared — single platform rate only.
            DB::table('vendors')->whereNotNull('commission_rate_override')->update(['commission_rate_override' => null]);
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'contract_acceptance_days'],
                ['value' => '15', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'public_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['public_id']);
                $table->dropColumn('public_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone_verified_at');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'contract_acceptance_days')->delete();
        }
    }
};
