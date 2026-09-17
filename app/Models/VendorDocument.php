<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorDocument extends Model
{
    protected $fillable = [
        'vendor_id',
        'document_type',
        'issuing_institution',
        'document_number',
        'issued_at',
        'expires_at',
        'disk',
        'file_path',
        'path',
        'original_filename',
        'mime_type',
        'file_size',
        'quarantine_status',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'reviewed_at' => 'datetime',
        'file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (VendorDocument $doc) {
            if (empty($doc->path) && ! empty($doc->file_path)) {
                $doc->path = $doc->file_path;
            }
            if (empty($doc->file_path) && ! empty($doc->path)) {
                $doc->file_path = $doc->path;
            }
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function downloadUrl(int $minutes = 10): string
    {
        return app(\App\Services\DocumentStorageService::class)->generateTemporaryDownloadUrl($this, $minutes);
    }
}

