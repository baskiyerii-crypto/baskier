<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_freelancer')) {
                $table->boolean('is_freelancer')->default(false)->after('vendor_id');
            }
        });

        Schema::create('freelancer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('bio')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->json('skills')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('freelancer_job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category')->comment('logo|wordpress|brochure|digital|other');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('status')->default('open')->comment('open|closed|completed');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('freelancer_job_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_job_listing_id')->constrained('freelancer_job_listings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedSmallInteger('delivery_days')->nullable();
            $table->text('proposal')->nullable();
            $table->string('status')->default('pending')->comment('pending|selected|rejected');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_job_bids');
        Schema::dropIfExists('freelancer_job_listings');
        Schema::dropIfExists('freelancer_profiles');
        if (Schema::hasColumn('users', 'is_freelancer')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_freelancer');
            });
        }
    }
};
