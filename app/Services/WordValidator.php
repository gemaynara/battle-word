<?php

namespace App\Services;

use App\Models\DictionaryWord;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;

class WordValidator
{
    /**
     * Validate a word submission through the priority pipeline:
     * 1. Time/round status
     * 2. Player participation
     * 3. Min/max length
     * 4. Letter availability
     * 5. Dictionary lookup
     * 6. Duplicate check
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
            return ValidationResult::invalid('invalid_letters');
        }

        // 3. Min/max length check
        if (!$this->isValidLength($word, $round->letters)) {
            return ValidationResult::invalid('invalid_letters');
        }

        // 4. Letter availability check
        if (!$this->hasValidLetters($word, $round->letters)) {
            return ValidationResult::invalid('invalid_letters');
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
     * Check that the word length is at least 2 and does not exceed the letter set length.
     */
    protected function isValidLength(string $word, string $letterSet): bool
    {
        $wordLength = mb_strlen($word);
        $letterSetLength = mb_strlen($letterSet);

        return $wordLength >= 2 && $wordLength <= $letterSetLength;
    }

    /**
     * Check that each letter in the word is available in the letter set,
     * respecting the exact quantity of each letter.
     */
    protected function hasValidLetters(string $word, string $letterSet): bool
    {
        $available = array_count_values(str_split(strtoupper($letterSet)));
        $needed = array_count_values(str_split(strtoupper($word)));

        foreach ($needed as $letter => $count) {
            if (($available[$letter] ?? 0) < $count) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check that the word exists in the dictionary with is_valid = true and is_inappropriate = false.
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
