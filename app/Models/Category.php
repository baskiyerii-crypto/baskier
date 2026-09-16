<?php

namespace App\Models;

use App\Support\HasLocalizedFields;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;
    use HasLocalizedFields;

    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'description',
        'image',
        'parent_id',
        'is_active',
        'channel',
        'termin_days',
        'delivery_days',
        'requires_quote',
    ];

    public const CHANNEL_PHYSICAL_QUOTE = 'physical_quote';

    public const CHANNEL_FREELANCER = 'freelancer';

    public const CHANNEL_TABELA = 'tabela';

    protected $casts = [
        'is_active' => 'boolean',
        'requires_quote' => 'boolean',
    ];

    public function getTerminDaysAttribute($value): ?int
    {
        if ($value !== null) {
            return (int) $value;
        }

        return $this->attributes['delivery_days'] ?? null;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function quoteVendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'category_vendor')->withTimestamps();
    }

    /**
     * @return list<int>
     */
    public static function familyIdsIncludingSelf(int $categoryId): array
    {
        $ids = [$categoryId];
        $queue = [$categoryId];
        while ($queue !== []) {
            $current = array_shift($queue);
            $children = self::query()
                ->where('parent_id', $current)
                ->where('is_active', true)
                ->pluck('id')
                ->all();
            foreach ($children as $childId) {
                if (! in_array($childId, $ids, true)) {
                    $ids[] = $childId;
                    $queue[] = $childId;
                }
            }
        }

        return $ids;
    }

    /**
     * @return list<self>
     */
    public function ancestorChainIncludingSelf(): array
    {
        $chain = [];
        $current = $this;
        while ($current) {
            $chain[] = $current;
            $current = $current->parent;
        }

        return array_reverse($chain);
    }
}
