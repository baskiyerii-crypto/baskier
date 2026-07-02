<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    /** @use HasFactory<\Database\Factories\VendorFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'email',
        'phone',
        'city',
        'district',
        'address',
        'balance',
        'rating_average',
        'reviews_count',
        'logo',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
        'rating_average' => 'decimal:2',
    ];

    public function recalculateRating(): void
    {
        $avg = $this->reviews()->avg('rating');
        $count = $this->reviews()->count();
        $this->updateQuietly([
            'rating_average' => $avg !== null ? round((float) $avg, 2) : null,
            'reviews_count' => $count,
        ]);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(VendorBalanceTransaction::class, 'vendor_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function quoteCategories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_vendor')->withTimestamps();
    }

    public function businessTypes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(BusinessType::class)->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
