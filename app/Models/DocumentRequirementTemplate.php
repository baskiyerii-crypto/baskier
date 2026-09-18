<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequirementTemplate extends Model
{
    public const AUDIENCES = [
        'physical',
        'freelancer',
        'outdoor_owner',
        'outdoor_agency',
        'municipality',
    ];

    protected $fillable = [
        'audience', 'document_type', 'label', 'required', 'requires_file', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'required' => 'boolean',
        'requires_file' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
