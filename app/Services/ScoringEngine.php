<?php

namespace App\Services;

use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;

class ScoringEngine
{
    private const POINTS_BY_LENGTH = [
        2 => 1,
        3 => 3,
        4 => 5,
        5 => 8,
        6 => 12,
        7 => 17,
    ];

    private const POINTS_8_PLUS = 25;
    private const PERFECT_WORD_BONUS = 10;
    private const MAX_COMBO = 5;
    private const LONG_WORD_MIN_LENGTH = 6;

    /**
     * Calculate points for a submitted word.
     *
     * Determines base points by word length, detects perfect words,
     * and applies the combo multiplier to compute total points.
     * Returns the new combo value for the next submission.
     */
    public function calculatePoints(string $word, string $letterSet, int $currentCombo): ScoreResult
    {
        $wordLength = mb_strlen($word);
        $basePoints = $this->getBasePoints($wordLength);
        $isPerfectWord = $this->isPerfectWord($word, $letterSet);
        $perfectBonus = $isPerfectWord ? self::PERFECT_WORD_BONUS : 0;
        $isLongWord = $wordLength >= self::LONG_WORD_MIN_LENGTH;

        // Combo is clamped to MAX_COMBO
        $comboMultiplier = min($currentCombo, self::MAX_COMBO);

        // Total points = (base_points + perfect_bonus) × combo_multiplier
        $totalPoints = ($basePoints + $perfectBonus) * $comboMultiplier;

        // New combo = min(currentCombo + 1, MAX_COMBO) for the next valid submission
        $newCombo = min($currentCombo + 1, self::MAX_COMBO);

        return new ScoreResult(
            points: $basePoints,
            comboMultiplier: $comboMultiplier,
            totalPoints: $totalPoints,
            isPerfectWord: $isPerfectWord,
            isLongWord: $isLongWord,
            newCombo: $newCombo,
        );
    }

    /**
     * Determine the current combo multiplier for a player in the current round.
     *
     * Looks at the player's submitted_words for the round in chronological order,
     * counting consecutive valid submissions from the most recent backwards
     * until hitting an invalid one.
     */
    public function getComboForPlayer(GameRound $round, GamePlayer $player): int
    {
        $submissions = SubmittedWord::where('game_round_id', $round->id)
            ->where('game_player_id', $player->id)
            ->orderBy('submitted_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        if ($submissions->isEmpty()) {
            return 1;
        }

        $consecutiveValid = 0;

        foreach ($submissions as $submission) {
            if ($submission->is_valid) {
                $consecutiveValid++;
            } else {
                break;
            }
        }

        // The combo for the NEXT submission is consecutive valid + 1
        // (since the next valid word continues the streak)
        $nextCombo = $consecutiveValid + 1;

        return min($nextCombo, self::MAX_COMBO);
    }

    /**
     * Atomically update the player's total score.
     */
    public function updatePlayerScore(GamePlayer $player, int $totalPoints): void
    {
        $player->increment('total_score', $totalPoints);
    }

    /**
     * Get base points for a given word length.
     */
    private function getBasePoints(int $length): int
    {
        if ($length >= 8) {
            return self::POINTS_8_PLUS;
        }

        return self::POINTS_BY_LENGTH[$length] ?? 0;
    }

    /**
     * Detect if a word is a "perfect word" — uses ALL letters in the Letter_Set.
     *
     * Checks by comparing sorted letters of the word and the letter set.
     */
    private function isPerfectWord(string $word, string $letterSet): bool
    {
        $wordLetters = str_split(strtoupper($word));
        $setLetters = str_split(strtoupper($letterSet));

        if (count($wordLetters) !== count($setLetters)) {
            return false;
        }

        sort($wordLetters);
        sort($setLetters);

        return $wordLetters === $setLetters;
    }
}
