<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderQuestionReply extends Model
{
    protected $fillable = ['order_question_id', 'user_id', 'body', 'is_from_vendor'];

    protected $casts = [
        'is_from_vendor' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(OrderQuestion::class, 'order_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
