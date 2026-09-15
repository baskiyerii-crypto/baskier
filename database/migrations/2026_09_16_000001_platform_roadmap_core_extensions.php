<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'channel')) {
                    $table->string('channel', 32)->default('physical_quote')->after('is_active');
                    $table->index('channel');
                }
                if (Schema::hasColumn('categories', 'delivery_days') && ! Schema::hasColumn('categories', 'termin_days')) {
                    $table->unsignedSmallInteger('termin_days')->nullable()->after('channel');
                } elseif (! Schema::hasColumn('categories', 'termin_days')) {
                    $table->unsignedSmallInteger('termin_days')->nullable()->after('channel');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'termin_due_at')) {
                    $table->timestamp('termin_due_at')->nullable()->after('shipped_at');
                }
                if (! Schema::hasColumn('orders', 'tracking_number')) {
                    $table->string('tracking_number', 120)->nullable()->after('termin_due_at');
                }
                if (! Schema::hasColumn('orders', 'shipping_label_path')) {
                    $table->string('shipping_label_path')->nullable()->after('tracking_number');
                }
                if (! Schema::hasColumn('orders', 'carrier_code')) {
                    $table->string('carrier_code', 64)->nullable()->after('shipping_label_path');
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (! Schema::hasColumn('vendors', 'tabela_enabled')) {
                    $table->boolean('tabela_enabled')->default(false)->after('quotes_expires_at');
                }
                if (! Schema::hasColumn('vendors', 'tabela_expires_at')) {
                    $table->timestamp('tabela_expires_at')->nullable()->after('tabela_enabled');
                }
                if (! Schema::hasColumn('vendors', 'risk_band')) {
                    $table->string('risk_band', 16)->nullable()->after('tabela_expires_at');
                }
                if (! Schema::hasColumn('vendors', 'risk_score')) {
                    $table->decimal('risk_score', 5, 2)->nullable()->after('risk_band');
                }
            });
        }

        if (Schema::hasTable('quote_requests')) {
            Schema::table('quote_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('quote_requests', 'request_type')) {
                    $table->string('request_type', 32)->default('physical_quote')->after('status');
                    $table->index('request_type');
                }
                if (! Schema::hasColumn('quote_requests', 'show_customer_profile')) {
                    $table->boolean('show_customer_profile')->default(false)->after('request_type');
                }
            });
        }

        if (! Schema::hasTable('order_contract_acceptances')) {
            Schema::create('order_contract_acceptances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
                $table->string('ip', 64)->nullable();
                $table->timestamp('scrolled_at')->nullable();
                $table->timestamp('accepted_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('category')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_description', 500)->nullable();
                $table->longText('body');
                $table->string('status', 32)->default('draft');
                $table->boolean('ai_humanized')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('integration_credentials')) {
            Schema::create('integration_credentials', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 64)->unique();
                $table->json('payload')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('endpoint', 500);
                $table->string('public_key')->nullable();
                $table->string('auth_token')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'endpoint']);
            });
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'delivery_days') && Schema::hasColumn('categories', 'termin_days')) {
            DB::statement('UPDATE categories SET termin_days = delivery_days WHERE termin_days IS NULL');
        }

        $settings = [
            ['key' => 'tabela_monthly_fee', 'value' => '149'],
            ['key' => 'tabela_meeting_fee', 'value' => '50'],
            ['key' => 'platform_expenses', 'value' => '0'],
            ['key' => 'iyzico_mode', 'value' => 'sandbox'],
            ['key' => 'iyzico_api_key', 'value' => ''],
            ['key' => 'iyzico_secret_key', 'value' => ''],
            ['key' => 'iyzico_base_url', 'value' => 'https://sandbox-api.iyzipay.com'],
            ['key' => 'basitkargo_api_key', 'value' => ''],
            ['key' => 'basitkargo_base_url', 'value' => ''],
            ['key' => 'openai_api_key', 'value' => ''],
            ['key' => 'openai_model', 'value' => 'gpt-4o-mini'],
        ];

        foreach ($settings as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], ['value' => $row['value'], 'updated_at' => now(), 'created_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('integration_credentials');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('order_contract_acceptances');

        if (Schema::hasTable('quote_requests')) {
            Schema::table('quote_requests', function (Blueprint $table) {
                foreach (['request_type', 'show_customer_profile'] as $col) {
                    if (Schema::hasColumn('quote_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                foreach (['tabela_enabled', 'tabela_expires_at', 'risk_band', 'risk_score'] as $col) {
                    if (Schema::hasColumn('vendors', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                foreach (['termin_due_at', 'tracking_number', 'shipping_label_path', 'carrier_code'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'channel')) {
                    $table->dropColumn('channel');
                }
                // termin_days leave in place for safety; delivery_days retained if present
            });
        }

        DB::table('settings')->whereIn('key', [
            'tabela_monthly_fee', 'tabela_meeting_fee', 'platform_expenses',
            'iyzico_mode', 'iyzico_api_key', 'iyzico_secret_key', 'iyzico_base_url',
            'basitkargo_api_key', 'basitkargo_base_url', 'openai_api_key', 'openai_model',
        ])->delete();
    }
};
