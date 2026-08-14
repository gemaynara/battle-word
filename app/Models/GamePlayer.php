<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GamePlayer extends Model
{
    protected $fillable = [
        'game_id',
        'user_id',
        'nickname',
        'avatar',
        'is_host',
        'is_bot',
        'is_connected',
        'total_score',
        'total_words',
        'best_combo',
        'longest_word_length',
        'position',
        'player_token',
        'joined_at',
        'disconnected_at',
    ];

    protected $casts = [
        'is_host' => 'boolean',
        'is_bot' => 'boolean',
        'is_connected' => 'boolean',
        'joined_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (GamePlayer $player): void {
            if (empty($player->player_token)) {
                $player->player_token = (string) Str::uuid();
            }
        });
    }

    public static function findByToken(string $token): ?GamePlayer
    {
        return static::where('player_token', $token)->first();
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submittedWords(): HasMany
    {
        return $this->hasMany(SubmittedWord::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    public function scopeConnected($query)
    {
        return $query->where('is_connected', true);
    }

    public function scopeBots($query)
    {
        return $query->where('is_bot', true);
    }
}
