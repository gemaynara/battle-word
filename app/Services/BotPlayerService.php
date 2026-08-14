<?php

namespace App\Services;

use App\Jobs\BotPlayJob;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;
use Illuminate\Support\Collection;

class BotPlayerService
{
    private const MAX_WORDS_PER_ROUND = 12;
    private const SHORT_WORD_PROBABILITY = 70;
    private const SHORT_WORD_MIN_LENGTH = 2;
    private const SHORT_WORD_MAX_LENGTH = 5;
    private const LONG_WORD_MIN_LENGTH = 6;

    public function __construct(
        private readonly LetterSetGenerator $letterSetGenerator,
    ) {}

    /**
     * Start the bot for the given round by dispatching the first BotPlayJob
     * with a random delay between 3-8 seconds.
     */
    public function startBot(GameRound $round, GamePlayer $bot): void
    {
        $delay = rand(3, 8);

        BotPlayJob::dispatch($round->id, $bot->id, 0)
            ->delay(now()->addSeconds($delay));
    }

    /**
     * Select the next word for the bot to submit.
     *
     * Applies the distribution: 70% short words (2-5 letters), 30% long words (6+ letters).
     * Ensures the bot never submits more than 50% of all possible valid words.
     * Returns null if no more words are available or limits are reached.
     */
    public function selectNextWord(GameRound $round, GamePlayer $bot): ?string
    {
        $allValidWords = $this->letterSetGenerator->getValidWordsForLetters($round->letters);
        $totalValidCount = $allValidWords->count();

        if ($totalValidCount === 0) {
            return null;
        }

        // Get words already submitted by the bot in this round
        $submittedWords = SubmittedWord::where('game_round_id', $round->id)
            ->where('game_player_id', $bot->id)
            ->pluck('word')
            ->map(fn (string $word) => strtoupper($word))
            ->toArray();

        // Check 50% limit of total valid words
        $maxAllowed = (int) floor($totalValidCount * 0.5);
        if (count($submittedWords) >= $maxAllowed) {
            return null;
        }

        // Check max 12 words per round
        if (count($submittedWords) >= self::MAX_WORDS_PER_ROUND) {
            return null;
        }

        // Filter out already submitted words
        $availableWords = $allValidWords->filter(function ($dictionaryWord) use ($submittedWords) {
            return !in_array(strtoupper($dictionaryWord->word), $submittedWords);
        });

        if ($availableWords->isEmpty()) {
            return null;
        }

        // Split into short (2-5) and long (6+) pools
        $shortWords = $availableWords->filter(fn ($w) => $w->length >= self::SHORT_WORD_MIN_LENGTH && $w->length <= self::SHORT_WORD_MAX_LENGTH);
        $longWords = $availableWords->filter(fn ($w) => $w->length >= self::LONG_WORD_MIN_LENGTH);

        // Apply distribution: 70% short, 30% long
        $roll = rand(1, 100);
        if ($roll <= self::SHORT_WORD_PROBABILITY) {
            // Try short words first, fall back to long
            $pool = $shortWords->isNotEmpty() ? $shortWords : $longWords;
        } else {
            // Try long words first, fall back to short
            $pool = $longWords->isNotEmpty() ? $longWords : $shortWords;
        }

        if ($pool->isEmpty()) {
            return null;
        }

        // Pick a random word from the selected pool
        $selected = $pool->random();

        return strtoupper($selected->word);
    }
}
