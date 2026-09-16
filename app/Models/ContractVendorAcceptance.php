<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractVendorAcceptance extends Model
{
    protected $fillable = [
        'vendor_id', 'contract_id', 'version', 'status', 'due_at', 'accepted_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
