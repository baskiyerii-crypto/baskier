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
                if (! Schema::hasColumn('vendors', 'registration_tracks')) {
                    $table->json('registration_tracks')->nullable();
                }
                if (! Schema::hasColumn('vendors', 'freelancer_tier')) {
                    $table->string('freelancer_tier', 32)->nullable();
                }
                if (! Schema::hasColumn('vendors', 'profile_pending_payload')) {
                    $table->json('profile_pending_payload')->nullable();
                }
                if (! Schema::hasColumn('vendors', 'map_embed_url')) {
                    $table->string('map_embed_url', 500)->nullable();
                }
                if (! Schema::hasColumn('vendors', 'map_lat')) {
                    $table->decimal('map_lat', 10, 7)->nullable();
                }
                if (! Schema::hasColumn('vendors', 'map_lng')) {
                    $table->decimal('map_lng', 10, 7)->nullable();
                }
                if (! Schema::hasColumn('vendors', 'social_links')) {
                    $table->json('social_links')->nullable();
                }
            });
        }

        if (! Schema::hasTable('vendor_profile_change_requests')) {
            Schema::create('vendor_profile_change_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->json('payload');
                $table->string('status', 32)->default('pending');
                $table->text('admin_note')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'vendor_id']);
            });
        }

        if (! Schema::hasTable('contact_shares')) {
            Schema::create('contact_shares', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('context_type', 64);
                $table->unsignedBigInteger('context_id')->nullable();
                $table->boolean('customer_consented')->default(false);
                $table->boolean('vendor_consented')->default(false);
                $table->timestamp('shared_at')->nullable();
                $table->timestamps();
                $table->index(['context_type', 'context_id']);
            });
        }

        if (! Schema::hasTable('direct_quote_requests')) {
            Schema::create('direct_quote_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('body')->nullable();
                $table->string('status', 32)->default('open');
                $table->decimal('offer_amount', 12, 2)->nullable();
                $table->text('vendor_note')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
                $table->index(['vendor_id', 'status']);
                $table->index(['customer_user_id', 'status']);
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('role');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('direct_quote_requests');
        Schema::dropIfExists('contact_shares');
        Schema::dropIfExists('vendor_profile_change_requests');

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                foreach (['registration_tracks', 'freelancer_tier', 'profile_pending_payload', 'map_embed_url', 'map_lat', 'map_lng', 'social_links'] as $col) {
                    if (Schema::hasColumn('vendors', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
