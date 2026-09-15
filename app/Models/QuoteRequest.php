<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'description',
        'city', 'district', 'address', 'contact_phone', 'status', 'closed_at',
        'request_type', 'show_customer_profile',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'show_customer_profile' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class)->orderBy('sort_order');
    }

    public function meetingCharges(): HasMany
    {
        return $this->hasMany(QuoteMeetingCharge::class);
    }

    public function selectedQuote(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Quote::class)->where('status', 'selected');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
