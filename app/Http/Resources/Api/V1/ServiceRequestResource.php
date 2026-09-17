<?php

namespace App\Http\Resources\Api\V1;

use App\Support\FreelancerCategories;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\FreelancerJobListing */
class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category' => $this->category,
            'category_label' => FreelancerCategories::label($this->category),
            'title' => $this->title,
            'description' => $this->description,
            'budget_min' => $this->budget_min ? (string) $this->budget_min : null,
            'budget_max' => $this->budget_max ? (string) $this->budget_max : null,
            'status' => $this->status,
            'bids_count' => $this->bids_count ?? $this->whenCounted('bids'),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'public_id' => $this->user->public_id,
            ]),
            'bids' => $this->whenLoaded('bids', fn () => $this->bids->map(fn ($bid) => [
                'id' => $bid->id,
                'freelancer_job_listing_id' => $bid->freelancer_job_listing_id,
                'user_id' => $bid->user_id,
                'amount' => (string) $bid->amount,
                'delivery_days' => $bid->delivery_days,
                'proposal' => $bid->proposal,
                'status' => $bid->status,
                'user' => $bid->relationLoaded('user') && $bid->user ? [
                    'id' => $bid->user->id,
                    'name' => $bid->user->name,
                    'public_id' => $bid->user->public_id,
                ] : null,
                'created_at' => $bid->created_at,
            ])),
            'selected_bid' => $this->whenLoaded('selectedBid'),
            'created_at' => $this->created_at,
            'closed_at' => $this->closed_at,
        ];
    }
}
