<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQuestion extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_ANSWERED = 'answered';

    protected $fillable = [
        'product_id', 'vendor_id', 'customer_id', 'question', 'answer', 'answered_at', 'status',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
