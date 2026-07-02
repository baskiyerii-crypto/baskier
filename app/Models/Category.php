<?php

namespace App\Models;

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

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'parent_id',
        'is_active',
        'delivery_days',
        'requires_quote',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_quote' => 'boolean',
    ];

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
