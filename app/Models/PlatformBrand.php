<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

class PlatformBrand extends Model
{
    protected $fillable = ['name', 'logo', 'website_url', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function logoUrl(): ?string
    {
        return MediaUrl::public($this->logo);
    }
}
