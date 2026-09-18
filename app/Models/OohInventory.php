<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OohInventory extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REJECTED = 'rejected';

    public const UNIT_DAY = 'day';

    public const UNIT_WEEK = 'week';

    public const UNIT_MONTH = 'month';

    protected $fillable = [
        'vendor_id', 'created_by_user_id', 'category_id', 'title', 'slug', 'description',
        'turkiye_il_id', 'turkiye_ilce_id', 'city', 'district', 'address',
        'country_code', 'lat', 'lng', 'permit_no', 'geo_fingerprint', 'list_price', 'price_unit',
        'proof_radius_m', 'status', 'rejection_reason',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'list_price' => 'decimal:2',
        'proof_radius_m' => 'integer',
        'turkiye_il_id' => 'integer',
        'turkiye_ilce_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $inventory): void {
            if (empty($inventory->slug)) {
                $base = Str::slug($inventory->title) ?: 'pano';
                $inventory->slug = $base.'-'.Str::lower(Str::random(6));
            }
        });
        static::saving(function (self $inventory): void {
            $inventory->geo_fingerprint = self::makeFingerprint(
                $inventory->permit_no,
                (float) $inventory->lat,
                (float) $inventory->lng
            );
        });
    }

    public static function makeFingerprint(?string $permit, float $lat, float $lng): string
    {
        $permit = mb_strtoupper((string) preg_replace('/\s+/', '', (string) $permit));

        return $permit.'|'.number_format(round($lat, 4), 4, '.', '').'|'.number_format(round($lng, 4), 4, '.', '');
    }

    /**
     * @return \Illuminate\Support\Collection<int, self>
     */
    public function similarListings()
    {
        $fp = $this->geo_fingerprint ?: self::makeFingerprint($this->permit_no, (float) $this->lat, (float) $this->lng);

        return static::query()
            ->where('geo_fingerprint', $fp)
            ->whereKeyNot($this->id ?: 0)
            ->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_PENDING_REVIEW])
            ->get();
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(TurkiyeIl::class, 'turkiye_il_id');
    }

    public function districtRel(): BelongsTo
    {
        return $this->belongsTo(TurkiyeIlce::class, 'turkiye_ilce_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function images(): HasMany
    {
        return $this->hasMany(OohInventoryImage::class)->orderBy('sort_order');
    }

    public function occupancies(): HasMany
    {
        return $this->hasMany(OohOccupancy::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(OohInventoryClaim::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function coverPath(): ?string
    {
        return $this->images->first()?->path;
    }

    public function locationLabel(): string
    {
        $parts = array_filter([
            $this->relationLoaded('country') ? $this->country?->localizedName() : ($this->country_code ?? null),
            $this->city ?: $this->province?->name,
            $this->district ?: $this->districtRel?->name,
        ]);

        return implode(' / ', $parts);
    }
}
