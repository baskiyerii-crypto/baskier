<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerJobListing extends Model
{
    protected $table = 'freelancer_job_listings';

    protected $fillable = [
        'user_id', 'category', 'title', 'description',
        'budget_min', 'budget_max', 'status', 'closed_at',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(FreelancerJobBid::class, 'freelancer_job_listing_id');
    }

    public function selectedBid(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FreelancerJobBid::class, 'freelancer_job_listing_id')->where('status', 'selected');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
