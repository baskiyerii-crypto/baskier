<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 32)->default('active')->after('role');
            }
        });

        if (! Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('phone', 32)->nullable();
                $table->string('avatar')->nullable();
                $table->text('bio')->nullable();
                $table->json('preferences')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('logo');
            }
            if (! Schema::hasColumn('vendors', 'service_regions')) {
                $table->json('service_regions')->nullable()->after('description');
            }
            if (! Schema::hasColumn('vendors', 'verification_status')) {
                $table->string('verification_status', 32)->default('pending')->after('is_active');
            }
            if (! Schema::hasColumn('vendors', 'commission_rate_override')) {
                $table->decimal('commission_rate_override', 5, 2)->nullable()->after('balance');
            }
            if (! Schema::hasColumn('vendors', 'quotes_answered')) {
                $table->unsignedInteger('quotes_answered')->default(0)->after('reviews_count');
            }
            if (! Schema::hasColumn('vendors', 'quotes_won')) {
                $table->unsignedInteger('quotes_won')->default(0)->after('quotes_answered');
            }
        });

        if (! Schema::hasTable('vendor_documents')) {
            Schema::create('vendor_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('document_type', 64)->default('registration');
                $table->string('path');
                $table->string('status', 32)->default('pending');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('category_attributes')) {
            Schema::create('category_attributes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->string('input_type', 32)->default('text');
                $table->json('options')->nullable();
                $table->boolean('is_required')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['category_id', 'slug']);
            });
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'catalog_type')) {
                $table->string('catalog_type', 32)->default('standard')->after('product_type');
            }
            if (! Schema::hasColumn('products', 'pricing_type')) {
                $table->string('pricing_type', 32)->default('fixed')->after('catalog_type');
            }
            if (! Schema::hasColumn('products', 'listing_status')) {
                $table->string('listing_status', 32)->default('published')->after('is_active');
            }
            if (! Schema::hasColumn('products', 'price_min')) {
                $table->decimal('price_min', 10, 2)->nullable()->after('price');
            }
            if (! Schema::hasColumn('products', 'price_max')) {
                $table->decimal('price_max', 10, 2)->nullable()->after('price_min');
            }
        });

        if (! Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('path');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('sku')->nullable();
                $table->decimal('price_adjustment', 10, 2)->default(0);
                $table->unsignedInteger('stock')->default(0);
                $table->json('attributes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_pricing_rules')) {
            Schema::create('product_pricing_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->json('rule');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shop_favorites')) {
            Schema::create('shop_favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'vendor_id']);
            });
        }

        Schema::table('quote_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('quote_requests', 'quantity')) {
                $table->unsignedInteger('quantity')->nullable()->after('description');
            }
            if (! Schema::hasColumn('quote_requests', 'width_cm')) {
                $table->decimal('width_cm', 10, 2)->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('quote_requests', 'height_cm')) {
                $table->decimal('height_cm', 10, 2)->nullable()->after('width_cm');
            }
            if (! Schema::hasColumn('quote_requests', 'depth_cm')) {
                $table->decimal('depth_cm', 10, 2)->nullable()->after('height_cm');
            }
            if (! Schema::hasColumn('quote_requests', 'materials')) {
                $table->text('materials')->nullable()->after('depth_cm');
            }
            if (! Schema::hasColumn('quote_requests', 'budget_min')) {
                $table->decimal('budget_min', 12, 2)->nullable()->after('materials');
            }
            if (! Schema::hasColumn('quote_requests', 'budget_max')) {
                $table->decimal('budget_max', 12, 2)->nullable()->after('budget_min');
            }
            if (! Schema::hasColumn('quote_requests', 'deadline_at')) {
                $table->timestamp('deadline_at')->nullable()->after('budget_max');
            }
            if (! Schema::hasColumn('quote_requests', 'needs_shipping')) {
                $table->boolean('needs_shipping')->default(false)->after('deadline_at');
            }
            if (! Schema::hasColumn('quote_requests', 'needs_installation')) {
                $table->boolean('needs_installation')->default(false)->after('needs_shipping');
            }
        });

        if (! Schema::hasTable('quote_request_attributes')) {
            Schema::create('quote_request_attributes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
                $table->string('attribute_key');
                $table->text('attribute_value')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quote_request_files')) {
            Schema::create('quote_request_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('quotes', 'included_notes')) {
                $table->text('included_notes')->nullable()->after('note');
            }
            if (! Schema::hasColumn('quotes', 'excluded_notes')) {
                $table->text('excluded_notes')->nullable()->after('included_notes');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'source_type')) {
                $table->string('source_type', 32)->nullable()->after('type');
            }
            if (! Schema::hasColumn('orders', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (! Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status', 32)->default('paid')->after('status');
            }
            if (! Schema::hasColumn('orders', 'billing_address_id')) {
                $table->foreignId('billing_address_id')->nullable()->after('shipping_address')->constrained('addresses')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->foreignId('shipping_address_id')->nullable()->after('billing_address_id')->constrained('addresses')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('order_files')) {
            Schema::create('order_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('purpose', 64)->default('customer_upload');
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('design_approvals')) {
            Schema::create('design_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('round')->default(1);
                $table->string('status', 32)->default('pending');
                $table->string('design_file_path')->nullable();
                $table->text('vendor_note')->nullable();
                $table->text('customer_feedback')->nullable();
                $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('carrier')->nullable();
                $table->string('tracking_number')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('provider', 64)->default('demo');
                $table->string('reference')->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('status', 32)->default('completed');
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('commissions')) {
            Schema::create('commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->decimal('rate', 5, 2);
                $table->decimal('amount', 12, 2);
                $table->decimal('vendor_net', 12, 2);
                $table->string('payout_status', 32)->default('pending');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payout_requests')) {
            Schema::create('payout_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('status', 32)->default('pending');
                $table->text('admin_note')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('subject');
                $table->string('status', 32)->default('open');
                $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('support_ticket_messages')) {
            Schema::create('support_ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_logs')) {
            Schema::create('admin_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('price_snapshots')) {
            Schema::create('price_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('quote_request_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('estimated_min', 12, 2);
                $table->decimal('estimated_max', 12, 2);
                $table->string('confidence', 32)->default('medium');
                $table->timestamp('computed_at')->useCurrent();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quote_request_vendor_matches')) {
            Schema::create('quote_request_vendor_matches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->decimal('score', 8, 4)->default(0);
                $table->timestamps();
                $table->unique(['quote_request_id', 'vendor_id']);
            });
        }

        Schema::table('conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('conversations', 'quote_request_id')) {
                $table->foreignId('quote_request_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('body');
            }
            if (! Schema::hasColumn('messages', 'display_body')) {
                $table->text('display_body')->nullable()->after('attachment_path');
            }
            if (! Schema::hasColumn('messages', 'moderation_flags')) {
                $table->json('moderation_flags')->nullable()->after('blocked');
            }
        });

        $this->migrateOrderStatuses();

        if (Schema::hasColumn('orders', 'source_type')) {
            DB::statement("UPDATE orders SET source_type = CASE type WHEN 'quote' THEN 'quote' WHEN 'product' THEN 'product' ELSE type END WHERE source_type IS NULL");
        }
    }

    private function migrateOrderStatuses(): void
    {
        $map = [
            'paid' => 'confirmed',
            'in_progress' => 'in_production',
        ];
        foreach ($map as $from => $to) {
            DB::table('orders')->where('status', $from)->update(['status' => $to]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_vendor_matches');
        Schema::dropIfExists('price_snapshots');
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('design_approvals');
        Schema::dropIfExists('order_files');
        Schema::dropIfExists('quote_request_files');
        Schema::dropIfExists('quote_request_attributes');
        Schema::dropIfExists('shop_favorites');
        Schema::dropIfExists('product_pricing_rules');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('category_attributes');
        Schema::dropIfExists('vendor_documents');
        Schema::dropIfExists('user_profiles');

        Schema::table('messages', function (Blueprint $table) {
            $cols = ['attachment_path', 'display_body', 'moderation_flags'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('messages', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'quote_request_id')) {
                $table->dropForeign(['quote_request_id']);
                $table->dropColumn('quote_request_id');
            }
        });

        Schema::table('quotes', function (Blueprint $table) {
            foreach (['included_notes', 'excluded_notes'] as $c) {
                if (Schema::hasColumn('quotes', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            foreach ([
                'quantity', 'width_cm', 'height_cm', 'depth_cm', 'materials',
                'budget_min', 'budget_max', 'deadline_at', 'needs_shipping', 'needs_installation',
            ] as $c) {
                if (Schema::hasColumn('quote_requests', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('products', function (Blueprint $table) {
            foreach (['catalog_type', 'pricing_type', 'listing_status', 'price_min', 'price_max'] as $c) {
                if (Schema::hasColumn('products', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            foreach ([
                'cover_image', 'service_regions', 'verification_status', 'commission_rate_override',
                'quotes_answered', 'quotes_won',
            ] as $c) {
                if (Schema::hasColumn('vendors', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'billing_address_id')) {
                $table->dropForeign(['billing_address_id']);
                $table->dropColumn('billing_address_id');
            }
            if (Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->dropForeign(['shipping_address_id']);
                $table->dropColumn('shipping_address_id');
            }
            foreach (['source_type', 'source_id', 'payment_status'] as $c) {
                if (Schema::hasColumn('orders', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        if (Schema::hasTable('notifications')) {
            Schema::dropIfExists('notifications');
        }
    }
};
