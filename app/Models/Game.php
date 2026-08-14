<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'host_user_id',
        'code',
        'status',
        'mode',
        'max_players',
        'total_rounds',
        'round_duration_seconds',
        'settings',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    public function currentRound()
    {
        return $this->rounds()->where('status', 'playing')->first();
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'playing']);
    }

    public function isJoinable(): bool
    {
        return $this->status === 'waiting' && $this->players()->count() < $this->max_players;
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        } while (self::where('code', $code)->whereIn('status', ['waiting', 'playing'])->exists());

        return $code;
    }
}
