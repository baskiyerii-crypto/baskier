<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequestItemFile extends Model
{
    protected $fillable = [
        'quote_request_item_id',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(QuoteRequestItem::class, 'quote_request_item_id');
    }
}

