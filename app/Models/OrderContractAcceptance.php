<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderContractAcceptance extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'contract_id',
        'version',
        'contract_hash',
        'checkout_token',
        'ip',
        'scrolled_at',
        'accepted_at',
    ];

    protected $casts = [
        'scrolled_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];
}
