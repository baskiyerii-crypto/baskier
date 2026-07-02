<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'is_from_vendor', 'body', 'blocked'];

    protected $casts = [
        'blocked' => 'boolean',
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
        $patterns = [
            '/https?:\/\//i',
            '/www\./i',
            '/\d{10,}/', // telefon
            '/whatsapp|wa\.me|telegram|@\w+/i',
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $body)) {
                return true;
            }
        }
        return false;
    }
}
