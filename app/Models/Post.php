<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'meta_title',
        'meta_description',
        'body',
        'status',
        'ai_humanized',
        'published_at',
    ];

    protected $casts = [
        'ai_humanized' => 'boolean',
        'published_at' => 'datetime',
    ];
}
