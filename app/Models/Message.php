<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'is_from_vendor', 'body', 'display_body', 'blocked', 'moderation_flags'];

    protected $casts = [
        'blocked' => 'boolean',
        'moderation_flags' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Yönlendirme (link, telefon, whatsapp vb.) tespiti */
    public static function containsRedirect(string $body): bool
    {
        return app(\App\Services\ModerationService::class)->isHardBlocked($body);
    }
}
