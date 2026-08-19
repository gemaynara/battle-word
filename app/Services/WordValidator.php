<?php

namespace App\Services;

use App\Models\DictionaryWord;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;

class WordValidator
{
    /**
     * Validate a word submission for the semantic similarity game:
     * 1. Time/round status
     * 2. Player participation
     * 3. Min length (at least 2 chars)
     * 4. Not the same as the theme word
     * 5. Dictionary lookup (word must exist in pt-BR)
     * 6. Duplicate check (same player, same round)
     */
    public function validate(GameRound $round, GamePlayer $player, string $word): ValidationResult
    {
        $word = mb_strtoupper(trim($word));

        // 1. Time/round status check
        if (!$this->isRoundActive($round)) {
            return ValidationResult::invalid('time_expired');
        }

        // 2. Player participation check
        if (!$this->isPlayerConnected($player)) {
            return ValidationResult::invalid('not_connected');
        }

        // 3. Min length check
        if (mb_strlen($word) < 2) {
            return ValidationResult::invalid('too_short');
        }

        // 4. Cannot submit the theme word itself
        if ($word === mb_strtoupper($round->base_word)) {
            return ValidationResult::invalid('same_as_theme');
        }

        // 5. Dictionary lookup
        if (!$this->existsInDictionary($word)) {
            return ValidationResult::invalid('not_in_dictionary');
        }

        // 6. Duplicate check
        if ($this->isDuplicate($round, $player, $word)) {
            return ValidationResult::invalid('duplicate');
        }

        return ValidationResult::valid(0, 1, 0);
    }

    /**
     * Check that the round is in "playing" status and time has not expired.
     */
    protected function isRoundActive(GameRound $round): bool
    {
        if ($round->status !== 'playing' || !$round->started_at) {
            return false;
        }

        $elapsed = now()->diffInSeconds($round->started_at, absolute: true);

        return $elapsed < $round->duration_seconds;
    }

    /**
     * Check that the player is registered and connected.
     */
    protected function isPlayerConnected(GamePlayer $player): bool
    {
        return $player->is_connected === true;
    }

    /**
     * Check that the word exists in the dictionary with is_valid = true.
     */
    protected function existsInDictionary(string $word): bool
    {
        return DictionaryWord::where('word', $word)
            ->where('is_valid', true)
            ->where('is_inappropriate', false)
            ->exists();
    }

    /**
     * Check that the player has not already submitted this word in the same round.
     */
    protected function isDuplicate(GameRound $round, GamePlayer $player, string $word): bool
    {
        return SubmittedWord::where('game_round_id', $round->id)
            ->where('game_player_id', $player->id)
            ->where('word', $word)
            ->exists();
    }
}
