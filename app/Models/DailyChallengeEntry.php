<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyChallengeEntry extends Model
{
    protected $fillable = [
        'daily_challenge_id',
        'user_id',
        'score',
        'words_found',
        'best_combo',
        'longest_word_length',
        'words_submitted',
        'completed_at',
    ];

    protected $casts = [
        'words_submitted' => 'array',
        'completed_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(DailyChallenge::class, 'daily_challenge_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
