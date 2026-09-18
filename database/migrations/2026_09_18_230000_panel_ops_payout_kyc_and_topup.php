<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payout_requests')) {
            Schema::table('payout_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('payout_requests', 'iban')) {
                    $table->string('iban', 32)->nullable()->after('amount');
                }
                if (! Schema::hasColumn('payout_requests', 'account_holder')) {
                    $table->string('account_holder', 150)->nullable()->after('iban');
                }
                if (! Schema::hasColumn('payout_requests', 'requested_at')) {
                    $table->timestamp('requested_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('payout_requests', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('processed_at');
                }
                if (! Schema::hasColumn('payout_requests', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('approved_at');
                }
                if (! Schema::hasColumn('payout_requests', 'source')) {
                    $table->string('source', 16)->default('manual')->after('status');
                }
            });
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'balance_credited_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('balance_credited_at')->nullable()->after('payout_at');
            });
        }

        if (Schema::hasTable('payment_attempts')) {
            Schema::table('payment_attempts', function (Blueprint $table) {
                if (! Schema::hasColumn('payment_attempts', 'purpose')) {
                    $table->string('purpose', 32)->default('order')->after('provider');
                }
                if (! Schema::hasColumn('payment_attempts', 'vendor_id')) {
                    $table->foreignId('vendor_id')->nullable()->after('user_id')->constrained('vendors')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (! Schema::hasColumn('vendors', 'payout_iban')) {
                    $table->string('payout_iban', 32)->nullable();
                }
                if (! Schema::hasColumn('vendors', 'payout_account_holder')) {
                    $table->string('payout_account_holder', 150)->nullable();
                }
            });
        }

        if (! Schema::hasTable('document_requirement_templates')) {
            Schema::create('document_requirement_templates', function (Blueprint $table) {
                $table->id();
                $table->string('audience', 32);
                $table->string('document_type', 64);
                $table->string('label');
                $table->boolean('required')->default(false);
                $table->boolean('requires_file')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['audience', 'document_type']);
            });
        }

        $this->seedTemplates();
        $this->seedPayoutSettings();
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requirement_templates');

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (Schema::hasColumn('vendors', 'payout_iban')) {
                    $table->dropColumn('payout_iban');
                }
                if (Schema::hasColumn('vendors', 'payout_account_holder')) {
                    $table->dropColumn('payout_account_holder');
                }
            });
        }

        if (Schema::hasTable('payment_attempts')) {
            Schema::table('payment_attempts', function (Blueprint $table) {
                if (Schema::hasColumn('payment_attempts', 'vendor_id')) {
                    $table->dropConstrainedForeignId('vendor_id');
                }
                if (Schema::hasColumn('payment_attempts', 'purpose')) {
                    $table->dropColumn('purpose');
                }
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'balance_credited_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('balance_credited_at');
            });
        }

        if (Schema::hasTable('payout_requests')) {
            Schema::table('payout_requests', function (Blueprint $table) {
                foreach (['iban', 'account_holder', 'requested_at', 'approved_at', 'rejected_at', 'source'] as $col) {
                    if (Schema::hasColumn('payout_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    private function seedTemplates(): void
    {
        if (! Schema::hasTable('document_requirement_templates')) {
            return;
        }
        $rows = [
            ['physical', 'tax_plate', 'Vergi levhası', true, true, 10],
            ['physical', 'company_registration', 'Ticaret sicil / oda kaydı', false, true, 20],
            ['freelancer', 'diploma', 'Diploma', false, true, 10],
            ['freelancer', 'certificate', 'Sertifika', false, true, 20],
            ['freelancer', 'portfolio_accreditation', 'Portföy / akreditasyon', false, true, 30],
            ['outdoor_owner', 'tax_plate', 'Vergi levhası', true, true, 10],
            ['outdoor_owner', 'trade_registry', 'Ticaret sicili', false, true, 20],
            ['outdoor_owner', 'outdoor_permit', 'Açık hava ruhsatı', false, true, 30],
            ['outdoor_agency', 'tax_plate', 'Vergi levhası', true, true, 10],
            ['outdoor_agency', 'trade_registry', 'Ticaret sicili', false, true, 20],
            ['municipality', 'municipality_authority', 'Belediye yetki belgesi', true, true, 10],
            ['municipality', 'outdoor_permit', 'Açık hava ruhsatı', false, true, 20],
        ];
        foreach ($rows as [$audience, $type, $label, $required, $file, $sort]) {
            $exists = DB::table('document_requirement_templates')
                ->where('audience', $audience)
                ->where('document_type', $type)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('document_requirement_templates')->insert([
                'audience' => $audience,
                'document_type' => $type,
                'label' => $label,
                'required' => $required,
                'requires_file' => $file,
                'sort_order' => $sort,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedPayoutSettings(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }
        $defaults = [
            'payout_weekdays' => '1,2,3,4,5',
            'payout_min_amount' => '10',
            'payout_auto_after_days' => '14',
        ];
        foreach ($defaults as $key => $value) {
            $exists = DB::table('settings')->where('key', $key)->exists();
            if (! $exists) {
                $row = ['key' => $key, 'value' => $value];
                if (Schema::hasColumn('settings', 'created_at')) {
                    $row['created_at'] = now();
                    $row['updated_at'] = now();
                }
                DB::table('settings')->insert($row);
            }
        }
    }
};
