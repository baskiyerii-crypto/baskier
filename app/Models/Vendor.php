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
        'cover_image',
        'description',
        'is_active',
        'verification_status',
        'trust_level',
        'is_suspended',
        'suspended_at',
        'suspended_by',
        'suspension_reason',
        'freelancer_enabled',
        'freelancer_expires_at',
        'quotes_enabled',
        'quotes_expires_at',
        'tabela_enabled',
        'tabela_expires_at',
        'ozalit_enabled',
        'ozalit_expires_at',
        'outdoor_enabled',
        'outdoor_expires_at',
        'risk_band',
        'risk_score',
        'contract_suspended_at',
        'registration_tracks',
        'freelancer_tier',
        'profile_pending_payload',
        'map_embed_url',
        'map_lat',
        'map_lng',
        'social_links',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_suspended' => 'boolean',
        'trust_level' => 'integer',
        'freelancer_enabled' => 'boolean',
        'quotes_enabled' => 'boolean',
        'tabela_enabled' => 'boolean',
        'ozalit_enabled' => 'boolean',
        'outdoor_enabled' => 'boolean',
        'balance' => 'decimal:2',
        'rating_average' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'contract_suspended_at' => 'datetime',
        'suspended_at' => 'datetime',
        'freelancer_expires_at' => 'datetime',
        'quotes_expires_at' => 'datetime',
        'tabela_expires_at' => 'datetime',
        'ozalit_expires_at' => 'datetime',
        'outdoor_expires_at' => 'datetime',
        'registration_tracks' => 'array',
        'profile_pending_payload' => 'array',
        'social_links' => 'array',
        'map_lat' => 'decimal:7',
        'map_lng' => 'decimal:7',
    ];

    public function hasTrack(string $track): bool
    {
        return in_array($track, $this->registration_tracks ?? [], true);
    }

    public function hasPhysicalTrack(): bool
    {
        $tracks = $this->registration_tracks ?? [];
        if ($tracks === [] || $tracks === null) {
            return true;
        }

        return $this->hasTrack('physical_products') || $this->hasTrack('physical_quote');
    }

    public function hasFreelancerTrack(): bool
    {
        return $this->hasTrack('freelancer');
    }

    public function hasOutdoorTrack(): bool
    {
        return $this->hasTrack('outdoor');
    }

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

    public function hasActiveOzalitModule(): bool
    {
        return (bool) $this->ozalit_enabled
            && ($this->ozalit_expires_at === null || $this->ozalit_expires_at->isFuture());
    }

    public function hasActiveOutdoorModule(): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'outdoor_enabled')) {
            return false;
        }

        return (bool) $this->outdoor_enabled
            && ($this->outdoor_expires_at === null || $this->outdoor_expires_at->isFuture());
    }

    public function hasApprovedTaxPlate(): bool
    {
        if (($this->verification_status ?? null) === 'verified') {
            return true;
        }

        return $this->documents()
            ->whereIn('document_type', ['tax_plate', 'company_registration'])
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->exists();
    }

    public function hasApprovedFreelancerCredential(): bool
    {
        return $this->documents()
            ->whereIn('document_type', ['diploma', 'certificate', 'portfolio_accreditation'])
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->exists();
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        $path = ltrim((string) $this->logo, '/');

        if (str_starts_with($path, 'uploads/') && is_file(public_path($path))) {
            return asset($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if (is_file(public_path('storage/'.$path))) {
            return asset('storage/'.$path);
        }

        if (is_file(storage_path('app/public/'.$path))) {
            return asset('storage/'.$path);
        }

        return asset('storage/'.$path);
    }

    public function coverUrl(): ?string
    {
        return \App\Support\MediaUrl::public($this->cover_image);
    }

    public function recalculateRating(): void
    {
        $avg = $this->reviews()->avg('rating');
        $count = $this->reviews()->count();
        $this->updateQuietly([
            'rating_average' => $avg !== null ? round((float) $avg, 2) : null,
            'reviews_count' => $count,
        ]);
        app(\App\Services\TrustBadgeService::class)->recalculateAndSave($this);
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

    public function members(): HasMany
    {
        return $this->hasMany(VendorMember::class);
    }

    public function oohInventories(): HasMany
    {
        return $this->hasMany(OohInventory::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }
}
