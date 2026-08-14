<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmittedWord extends Model
{
    protected $fillable = [
        'game_round_id',
        'game_player_id',
        'word',
        'is_valid',
        'rejection_reason',
        'points',
        'combo_multiplier',
        'total_points',
        'is_perfect_word',
        'is_rare_word',
        'is_long_word',
        'submitted_at',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'is_perfect_word' => 'boolean',
        'is_rare_word' => 'boolean',
        'is_long_word' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    public function scopeInvalid($query)
    {
        return $query->where('is_valid', false);
    }

    public function scopeForPlayer($query, int $playerId)
    {
        return $query->where('game_player_id', $playerId);
    }
}
