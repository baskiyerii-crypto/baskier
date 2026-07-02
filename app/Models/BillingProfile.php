<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingProfile extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_type',
        'full_name',
        'company_name',
        'company_address',
        'tax_number',
        'identity_number',
        'tax_office',
        'email',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
