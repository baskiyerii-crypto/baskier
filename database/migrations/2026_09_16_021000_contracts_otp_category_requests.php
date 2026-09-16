<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contract_vendor_acceptances')) {
            Schema::create('contract_vendor_acceptances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->string('status', 32)->default('pending'); // pending|accepted|expired
                $table->timestamp('due_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();
                $table->unique(['vendor_id', 'contract_id', 'version']);
                $table->index(['status', 'due_at']);
            });
        }

        if (Schema::hasTable('vendors') && ! Schema::hasColumn('vendors', 'contract_suspended_at')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->timestamp('contract_suspended_at')->nullable();
            });
        }

        if (! Schema::hasTable('otp_verifications')) {
            Schema::create('otp_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('channel', 16); // email|whatsapp
                $table->string('destination', 120);
                $table->string('code', 10);
                $table->timestamp('expires_at');
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->index(['destination', 'channel']);
            });
        }

        if (! Schema::hasTable('vendor_category_requests')) {
            Schema::create('vendor_category_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->json('category_ids');
                $table->string('channel', 32)->default('physical_quote');
                $table->string('status', 32)->default('pending'); // pending|approved|rejected
                $table->text('admin_note')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'vendor_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_category_requests');
        Schema::dropIfExists('otp_verifications');
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'contract_suspended_at')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropColumn('contract_suspended_at');
            });
        }
        Schema::dropIfExists('contract_vendor_acceptances');
    }
};
