<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    protected $fillable = [
        'user_id',
        'period',
        'period_key',
        'total_score',
        'games_played',
        'games_won',
        'total_words',
        'xp',
        'level',
        'position',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeGlobal($query)
    {
        return $query->where('period', 'global');
    }

    public function scopeWeekly($query)
    {
        return $query->where('period', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('period', 'monthly');
    }

    public function scopeCurrentWeek($query)
    {
        return $query->where('period', 'weekly')
            ->where('period_key', now()->format('Y-\\WW'));
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('period', 'monthly')
            ->where('period_key', now()->format('Y-m'));
    }
}
