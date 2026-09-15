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
        'company_name',
        'tax_office',
        'tax_number',
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
        'verification_status',
        'freelancer_enabled',
        'freelancer_expires_at',
        'quotes_enabled',
        'quotes_expires_at',
        'tabela_enabled',
        'tabela_expires_at',
        'risk_band',
        'risk_score',
        'contract_suspended_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'freelancer_enabled' => 'boolean',
        'quotes_enabled' => 'boolean',
        'tabela_enabled' => 'boolean',
        'balance' => 'decimal:2',
        'rating_average' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'contract_suspended_at' => 'datetime',
        'freelancer_expires_at' => 'datetime',
        'quotes_expires_at' => 'datetime',
        'tabela_expires_at' => 'datetime',
    ];

    public function hasActiveFreelancerModule(): bool
    {
        return $this->freelancer_enabled
            && ($this->freelancer_expires_at === null || $this->freelancer_expires_at->isFuture());
    }

    public function hasActiveQuotesModule(): bool
    {
        return $this->quotes_enabled
            && ($this->quotes_expires_at === null || $this->quotes_expires_at->isFuture());
    }

    public function hasActiveTabelaModule(): bool
    {
        return (bool) $this->tabela_enabled
            && ($this->tabela_expires_at === null || $this->tabela_expires_at->isFuture());
    }

    public function hasApprovedTaxPlate(): bool
    {
        if (($this->verification_status ?? null) === 'verified') {
            return true;
        }

        return $this->documents()
            ->where('document_type', 'tax_plate')
            ->where('status', 'approved')
            ->exists();
    }

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

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }
}
