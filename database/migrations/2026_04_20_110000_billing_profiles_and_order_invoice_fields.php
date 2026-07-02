<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('billing_profiles')) {
            Schema::create('billing_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('invoice_type', 16)->default('individual');
                $table->string('full_name')->nullable();
                $table->string('company_name')->nullable();
                $table->string('tax_number', 16)->nullable();
                $table->string('identity_number', 16)->nullable();
                $table->string('tax_office', 120)->nullable();
                $table->string('email', 190)->nullable();
                $table->string('phone', 32)->nullable();
                $table->timestamps();
                $table->index(['user_id', 'invoice_type']);
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'invoice_type')) {
                $table->string('invoice_type', 16)->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('orders', 'invoice_full_name')) {
                $table->string('invoice_full_name')->nullable()->after('invoice_type');
            }
            if (! Schema::hasColumn('orders', 'invoice_company_name')) {
                $table->string('invoice_company_name')->nullable()->after('invoice_full_name');
            }
            if (! Schema::hasColumn('orders', 'invoice_tax_number')) {
                $table->string('invoice_tax_number', 16)->nullable()->after('invoice_company_name');
            }
            if (! Schema::hasColumn('orders', 'invoice_identity_number')) {
                $table->string('invoice_identity_number', 16)->nullable()->after('invoice_tax_number');
            }
            if (! Schema::hasColumn('orders', 'invoice_tax_office')) {
                $table->string('invoice_tax_office', 120)->nullable()->after('invoice_identity_number');
            }
            if (! Schema::hasColumn('orders', 'invoice_email')) {
                $table->string('invoice_email', 190)->nullable()->after('invoice_tax_office');
            }
            if (! Schema::hasColumn('orders', 'invoice_phone')) {
                $table->string('invoice_phone', 32)->nullable()->after('invoice_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'invoice_type',
                'invoice_full_name',
                'invoice_company_name',
                'invoice_tax_number',
                'invoice_identity_number',
                'invoice_tax_office',
                'invoice_email',
                'invoice_phone',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('billing_profiles');
    }
};
