<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'key',
        'title',
        'audience',
        'version',
        'is_active',
        'content_html',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
    ];
}

