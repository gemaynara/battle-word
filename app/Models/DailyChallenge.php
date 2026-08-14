<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyChallenge extends Model
{
    protected $fillable = [
        'challenge_date',
        'letters',
        'base_word',
        'duration_seconds',
        'total_valid_words',
        'is_active',
    ];

    protected $casts = [
        'challenge_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(DailyChallengeEntry::class);
    }

    public function scopeToday($query)
    {
        return $query->where('challenge_date', now()->toDateString());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getLettersArrayAttribute(): array
    {
        return str_split(strtoupper($this->letters));
    }
}
