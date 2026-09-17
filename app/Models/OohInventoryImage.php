<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OohInventoryImage extends Model
{
    protected $fillable = ['ooh_inventory_id', 'path', 'sort_order'];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(OohInventory::class, 'ooh_inventory_id');
    }
}
