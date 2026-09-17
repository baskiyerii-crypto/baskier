<?php

namespace App\Models;

use App\Support\HasLocalizedFields;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    use HasLocalizedFields;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'name_en',
        'slug',
        'sku',
        'main_image',
        'price',
        'stock',
        'is_featured',
        'is_active',
        'attributes',
        'short_description',
        'short_description_en',
        'description',
        'description_en',
        'product_type',
        'digital_link',
        'catalog_type',
        'pricing_type',
        'listing_status',
        'price_min',
        'price_max',
        'moderation_status',
        'moderation_note',
        'submitted_for_moderation_at',
    ];

    public const MODERATION_PENDING = 'pending';

    public const MODERATION_APPROVED = 'approved';

    public const MODERATION_REJECTED = 'rejected';

    protected $casts = [
        'attributes' => 'array',
        'is_featured' => 'bool',
        'is_active' => 'bool',
        'price' => 'decimal:2',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'submitted_for_moderation_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        $query->where('is_active', true);

        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'moderation_status')) {
            $query->where(function ($q) {
                $q->where('moderation_status', self::MODERATION_APPROVED)
                    ->orWhereNull('moderation_status');
            });
        }

        return $query;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function displayImagePath(): ?string
    {
        if ($this->main_image) {
            return $this->main_image;
        }

        $first = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        return $first?->path;
    }

    public function displayImageUrl(): ?string
    {
        return MediaUrl::public($this->displayImagePath());
    }

    public function isDigital(): bool
    {
        return $this->product_type === 'digital';
    }

    public function isQuoteBased(): bool
    {
        return $this->pricing_type === 'quote' || $this->catalog_type === 'quote_only';
    }

    public function hasVariants(): bool
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty();
        }

        return $this->variants()->exists();
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    public function attachGallery(array $files): void
    {
        $sort = (int) $this->images()->max('sort_order');
        foreach ($files as $file) {
            $sort++;
            $path = $file->store('products', 'public');
            $this->images()->create(['path' => $path, 'sort_order' => $sort]);
            if (! $this->main_image) {
                $this->update(['main_image' => $path]);
            }
        }
    }
}
