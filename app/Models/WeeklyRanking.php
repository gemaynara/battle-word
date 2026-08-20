<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyRanking extends Model
{
    protected $fillable = [
        'nickname',
        'week_key',
        'best_score',
        'best_words_count',
        'game_code',
    ];

    /**
     * Record a player's score if it beats their current weekly best.
     */
    public static function recordScore(string $nickname, int $score, string $gameCode, int $wordsCount = 0): void
    {
        if ($score <= 0) {
            return;
        }

        $weekKey = now()->format('Y-\\WW');

        $entry = static::firstOrCreate(
            ['nickname' => $nickname, 'week_key' => $weekKey],
            ['best_score' => $score, 'best_words_count' => $wordsCount, 'game_code' => $gameCode]
        );

        if ($score > $entry->best_score) {
            $entry->update(['best_score' => $score, 'best_words_count' => $wordsCount, 'game_code' => $gameCode]);
        }
    }

    /**
     * Get the top 10 players for the current week.
     */
    public static function currentWeekTop10(): array
    {
        $weekKey = now()->format('Y-\\WW');

        return static::where('week_key', $weekKey)
            ->orderByDesc('best_score')
            ->limit(10)
            ->get()
            ->map(fn ($entry, $index) => [
                'position' => $index + 1,
                'nickname' => $entry->nickname,
                'best_score' => $entry->best_score,
                'words_count' => $entry->best_words_count,
            ])
            ->toArray();
    }
}
