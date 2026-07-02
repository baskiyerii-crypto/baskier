<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerJobBid extends Model
{
    protected $table = 'freelancer_job_bids';

    protected $fillable = [
        'freelancer_job_listing_id', 'user_id', 'amount', 'delivery_days', 'proposal', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(FreelancerJobListing::class, 'freelancer_job_listing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
