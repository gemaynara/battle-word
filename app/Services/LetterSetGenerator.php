<?php

namespace App\Services;

use App\Models\DictionaryWord;
use Illuminate\Support\Collection;

class LetterSetGenerator
{
    private const MIN_VALID_WORDS = 10;
    private const MAX_ATTEMPTS = 10;
    private const MIN_FORMABLE_WORD_LENGTH = 2;

    /**
     * Difficulty levels define base word length ranges.
     * Higher levels use longer base words → more letters → harder to find long words.
     */
    private const DIFFICULTY_LEVELS = [
        1 => ['min_length' => 5, 'max_length' => 6],
        2 => ['min_length' => 6, 'max_length' => 8],
        3 => ['min_length' => 8, 'max_length' => 10],
        4 => ['min_length' => 9, 'max_length' => 12],
    ];

    /**
     * Generate a letter set by selecting a random base word and verifying
     * that enough valid words can be formed from its letters.
     *
     * @param int $level Difficulty level (1=easy, 2=medium, 3=hard, 4=expert)
     * @param string|null $category Category filter (null = all categories)
     * @param bool $shuffle Whether to shuffle the letters (false = show base word as-is)
     */
    public function generate(int $level = 1, ?string $category = null, bool $shuffle = true): LetterSetResult
    {
        $lastResult = null;
        $config = $this->getDifficultyConfig($level);

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $baseWord = $this->selectRandomBaseWord($config['min_length'], $config['max_length'], $category);

            if ($baseWord === null) {
                // Fallback: try with broader range if no words found for this level/category
                $baseWord = $this->selectRandomBaseWord(5, 12, $category);
                if ($baseWord === null) {
                    // Last fallback: any category
                    $baseWord = $this->selectRandomBaseWord(5, 12, null);
                    if ($baseWord === null) {
                        break;
                    }
                }
            }

            $letters = mb_strtoupper($baseWord->word);
            $validWordCount = $this->countValidWords($letters);

            // Shuffle the letters so players don't see the base word directly
            // Unless shuffle is disabled (for beginner levels)
            $displayLetters = $shuffle ? $this->shuffleLetters($letters) : $letters;

            $lastResult = new LetterSetResult(
                letters: $displayLetters,
                baseWord: $baseWord->word,
                validWordCount: $validWordCount,
            );

            if ($validWordCount >= self::MIN_VALID_WORDS) {
                return $lastResult;
            }
        }

        // Requirement 3.5: If all attempts fail, use the last generated set
        return $lastResult ?? new LetterSetResult(
            letters: '',
            baseWord: '',
            validWordCount: 0,
        );
    }

    /**
     * Get difficulty configuration for the given level.
     */
    private function getDifficultyConfig(int $level): array
    {
        $maxLevel = max(array_keys(self::DIFFICULTY_LEVELS));
        $effectiveLevel = min($level, $maxLevel);

        return self::DIFFICULTY_LEVELS[$effectiveLevel];
    }

    /**
     * Count how many valid dictionary words can be formed from the given letters.
     */
    public function countValidWords(string $letters): int
    {
        return $this->getValidWordsForLetters($letters)->count();
    }

    /**
     * Get all valid dictionary words that can be formed from the given letters.
     */
    public function getValidWordsForLetters(string $letters): Collection
    {
        $letterLength = mb_strlen($letters);

        // Query words that are valid, not inappropriate, and have length <= letter set length
        // Minimum word length for counting is 2 characters
        $candidates = DictionaryWord::valid()
            ->where('length', '>=', self::MIN_FORMABLE_WORD_LENGTH)
            ->where('length', '<=', $letterLength)
            ->get();

        // Filter in PHP using letter availability check
        return $candidates->filter(function (DictionaryWord $word) use ($letters) {
            return $this->hasValidLetters($word->word, $letters);
        });
    }

    /**
     * Check if a word can be formed from the given letter set,
     * respecting the quantity of each letter available.
     */
    private function hasValidLetters(string $word, string $letterSet): bool
    {
        $available = array_count_values(mb_str_split(mb_strtoupper($letterSet)));
        $needed = array_count_values(mb_str_split(mb_strtoupper($word)));

        foreach ($needed as $letter => $count) {
            if (($available[$letter] ?? 0) < $count) {
                return false;
            }
        }

        return true;
    }

    /**
     * Select a random base word from the dictionary that meets the length criteria.
     */
    private function selectRandomBaseWord(int $minLength, int $maxLength, ?string $category = null): ?DictionaryWord
    {
        $query = DictionaryWord::valid()
            ->where('length', '>=', $minLength)
            ->where('length', '<=', $maxLength);

        if ($category !== null && $category !== 'aleatorio') {
            $query->where('category', $category);
        }

        return $query->inRandomOrder()->first();
    }

    /**
     * Shuffle the letters of a string, ensuring the result differs from the original.
     */
    private function shuffleLetters(string $letters): string
    {
        $chars = mb_str_split($letters);
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            shuffle($chars);
            $shuffled = implode('', $chars);
            if ($shuffled !== $letters) {
                return $shuffled;
            }
        }

        // If all attempts produce the same (e.g., all same letter), just return it
        return implode('', $chars);
    }
}
