<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('product')->comment('product|quote|freelancer');
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('freelancer_job_id')->nullable();
            $table->string('status')->default('pending')->comment('pending|paid|in_progress|delivered|completed|cancelled');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('vendor_amount', 12, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('commission_ready_at')->nullable()->comment('15 gün sonra hakedişe hazır');
            $table->boolean('payout_approved')->default(false);
            $table->timestamp('payout_at')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
