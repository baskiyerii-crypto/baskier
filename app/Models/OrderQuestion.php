<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderQuestion extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_ANSWERED = 'answered';

    protected $fillable = [
        'order_id', 'vendor_id', 'customer_id', 'asked_by', 'subject', 'status',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(OrderQuestionReply::class);
    }
}
