<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameScore extends Model
{
    protected $fillable = [
        'game_id',
        'game_round_id',
        'game_player_id',
        'round_score',
        'words_submitted',
        'valid_words',
        'invalid_words',
        'best_combo',
        'longest_word_length',
        'longest_word',
        'had_perfect_word',
        'position_in_round',
    ];

    protected $casts = [
        'had_perfect_word' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }
}
