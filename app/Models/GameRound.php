<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model
{
    protected $fillable = [
        'game_id',
        'round_number',
        'letters',
        'base_word',
        'duration_seconds',
        'status',
        'total_valid_words',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function submittedWords(): HasMany
    {
        return $this->hasMany(SubmittedWord::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'playing' || !$this->started_at) {
            return false;
        }

        return now()->diffInSeconds($this->started_at) < $this->duration_seconds;
    }

    public function getTimeRemainingAttribute(): int
    {
        if (!$this->started_at || $this->status !== 'playing') {
            return $this->duration_seconds;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        return max(0, $this->duration_seconds - $elapsed);
    }

    public function getLettersArrayAttribute(): array
    {
        return str_split(strtoupper($this->letters));
    }
}
