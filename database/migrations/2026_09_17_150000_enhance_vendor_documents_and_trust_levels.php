<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('vendor_documents')) {
            Schema::table('vendor_documents', function (Blueprint $table) {
                if (! Schema::hasColumn('vendor_documents', 'issuing_institution')) {
                    $table->string('issuing_institution')->nullable()->after('document_type');
                }
                if (! Schema::hasColumn('vendor_documents', 'document_number')) {
                    $table->string('document_number')->nullable()->after('issuing_institution');
                }
                if (! Schema::hasColumn('vendor_documents', 'issued_at')) {
                    $table->date('issued_at')->nullable()->after('document_number');
                }
                if (! Schema::hasColumn('vendor_documents', 'expires_at')) {
                    $table->date('expires_at')->nullable()->after('issued_at');
                }
                if (! Schema::hasColumn('vendor_documents', 'disk')) {
                    $table->string('disk', 32)->default('private')->after('expires_at');
                }
                if (! Schema::hasColumn('vendor_documents', 'file_path')) {
                    $table->string('file_path')->nullable()->after('disk');
                }
                if (! Schema::hasColumn('vendor_documents', 'original_filename')) {
                    $table->string('original_filename')->nullable()->after('file_path');
                }
                if (! Schema::hasColumn('vendor_documents', 'mime_type')) {
                    $table->string('mime_type', 128)->nullable()->after('original_filename');
                }
                if (! Schema::hasColumn('vendor_documents', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
                }
                if (! Schema::hasColumn('vendor_documents', 'quarantine_status')) {
                    $table->string('quarantine_status', 32)->default('clean')->after('file_size');
                }
                if (! Schema::hasColumn('vendor_documents', 'reviewed_by')) {
                    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
                }
                if (! Schema::hasColumn('vendor_documents', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                }
                if (! Schema::hasColumn('vendor_documents', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('reviewed_at');
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (! Schema::hasColumn('vendors', 'trust_level')) {
                    $table->unsignedTinyInteger('trust_level')->default(0)->after('verification_status');
                }
                if (! Schema::hasColumn('vendors', 'is_suspended')) {
                    $table->boolean('is_suspended')->default(false)->after('trust_level');
                }
                if (! Schema::hasColumn('vendors', 'suspended_at')) {
                    $table->timestamp('suspended_at')->nullable()->after('is_suspended');
                }
                if (! Schema::hasColumn('vendors', 'suspended_by')) {
                    $table->foreignId('suspended_by')->nullable()->constrained('users')->nullOnDelete()->after('suspended_at');
                }
                if (! Schema::hasColumn('vendors', 'suspension_reason')) {
                    $table->text('suspension_reason')->nullable()->after('suspended_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vendor_documents')) {
            Schema::table('vendor_documents', function (Blueprint $table) {
                $cols = [
                    'issuing_institution', 'document_number', 'issued_at', 'expires_at',
                    'disk', 'file_path', 'original_filename', 'mime_type', 'file_size',
                    'quarantine_status', 'reviewed_by', 'reviewed_at', 'rejection_reason'
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('vendor_documents', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                $cols = ['trust_level', 'is_suspended', 'suspended_at', 'suspended_by', 'suspension_reason'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('vendors', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
