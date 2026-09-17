<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_attempts')) {
            Schema::create('payment_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('conversation_id', 100)->unique();
                $table->string('idempotency_key', 100)->index();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('provider', 32)->default('iyzico');
                $table->string('status', 32)->default('pending');
                $table->decimal('amount', 12, 2);
                $table->string('currency', 3)->default('TRY');
                $table->json('order_ids')->nullable();
                $table->json('metadata')->nullable();
                $table->string('provider_reference', 120)->nullable();
                $table->string('error_code', 64)->nullable();
                $table->string('error_message', 255)->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stock_reservations')) {
            Schema::create('stock_reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('checkout_token', 64)->index();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->integer('quantity');
                $table->string('status', 32)->default('reserved');
                $table->timestamp('expires_at')->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'idempotency_key')) {
                    $table->string('idempotency_key', 100)->nullable()->index();
                }
                if (! Schema::hasColumn('orders', 'payment_attempt_id')) {
                    $table->foreignId('payment_attempt_id')->nullable()->constrained('payment_attempts')->nullOnDelete();
                }
                if (! Schema::hasColumn('orders', 'currency')) {
                    $table->string('currency', 3)->default('TRY');
                }
            });
        }

        if (Schema::hasTable('order_contract_acceptances')) {
            Schema::table('order_contract_acceptances', function (Blueprint $table) {
                if (! Schema::hasColumn('order_contract_acceptances', 'version')) {
                    $table->unsignedSmallInteger('version')->default(1);
                }
                if (! Schema::hasColumn('order_contract_acceptances', 'contract_hash')) {
                    $table->string('contract_hash', 64)->nullable();
                }
                if (! Schema::hasColumn('order_contract_acceptances', 'checkout_token')) {
                    $table->string('checkout_token', 64)->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_contract_acceptances')) {
            Schema::table('order_contract_acceptances', function (Blueprint $table) {
                foreach (['checkout_token', 'contract_hash', 'version'] as $col) {
                    if (Schema::hasColumn('order_contract_acceptances', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'payment_attempt_id')) {
                    $table->dropConstrainedForeignId('payment_attempt_id');
                }
                if (Schema::hasColumn('orders', 'idempotency_key')) {
                    $table->dropColumn('idempotency_key');
                }
                if (Schema::hasColumn('orders', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }

        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('payment_attempts');
    }
};
