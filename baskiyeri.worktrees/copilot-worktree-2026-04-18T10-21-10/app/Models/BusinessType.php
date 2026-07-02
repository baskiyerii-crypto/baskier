<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BusinessType extends Model
{
    protected $fillable = ['slug', 'name', 'sort_order'];

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class)->withTimestamps();
    }
}
