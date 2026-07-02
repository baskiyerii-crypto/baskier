<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('vendors')->where('verification_status', 'pending')->update(['verification_status' => 'approved']);
    }

    public function down(): void
    {
        //
    }
};
