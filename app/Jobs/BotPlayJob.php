<?php

namespace App\Jobs;

use App\Events\ScoreUpdated;
use App\Events\WordSubmitted;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;
use App\Services\BotPlayerService;
use App\Services\ScoringEngine;
use App\Services\WordValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BotPlayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const MAX_WORDS_PER_ROUND = 12;
    private const MIN_DELAY_SECONDS = 3;
    private const MAX_DELAY_SECONDS = 8;

    public function __construct(
        private int $roundId,
        private int $botPlayerId,
        private int $wordsSubmitted = 0,
    ) {}

    public function handle(
        BotPlayerService $botService,
        WordValidator $wordValidator,
        ScoringEngine $scoringEngine,
    ): void {
        $round = GameRound::find($this->roundId);

        if (!$round || $round->status !== 'playing' || $this->wordsSubmitted >= self::MAX_WORDS_PER_ROUND) {
            return;
        }

        $bot = GamePlayer::find($this->botPlayerId);

        if (!$bot) {
            return;
        }

        $word = $botService->selectNextWord($round, $bot);

        if (!$word) {
            return;
        }

        // Validate through the same pipeline as human players (Req 11.6)
        $validationResult = $wordValidator->validate($round, $bot, $word);

        if (!$validationResult->isValid) {
            // Word was invalid (unlikely since we pre-filter, but respects the same rules)
            SubmittedWord::create([
                'game_round_id' => $round->id,
                'game_player_id' => $bot->id,
                'word' => $word,
                'is_valid' => false,
                'rejection_reason' => $validationResult->rejectionReason,
                'points' => 0,
                'combo_multiplier' => 1,
                'total_points' => 0,
                'is_perfect_word' => false,
                'is_long_word' => false,
                'submitted_at' => now(),
            ]);

            // Still dispatch next job to try again
            self::dispatch($this->roundId, $this->botPlayerId, $this->wordsSubmitted + 1)
                ->delay(now()->addSeconds(rand(self::MIN_DELAY_SECONDS, self::MAX_DELAY_SECONDS)));

            return;
        }

        // Calculate score using same ScoringEngine as humans (Req 11.5)
        $currentCombo = $scoringEngine->getComboForPlayer($round, $bot);
        $scoreResult = $scoringEngine->calculatePoints($word, $round->letters, $currentCombo);

        // Save valid submission
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $bot->id,
            'word' => $word,
            'is_valid' => true,
            'rejection_reason' => null,
            'points' => $scoreResult->points,
            'combo_multiplier' => $scoreResult->comboMultiplier,
            'total_points' => $scoreResult->totalPoints,
            'is_perfect_word' => $scoreResult->isPerfectWord,
            'is_long_word' => $scoreResult->isLongWord,
            'submitted_at' => now(),
        ]);

        // Update bot's total score atomically
        $scoringEngine->updatePlayerScore($bot, $scoreResult->totalPoints);
        $bot->refresh();

        // Broadcast events — identical to human submissions
        $game = $round->game;

        event(new WordSubmitted(
            game: $game,
            playerNickname: $bot->nickname,
            word: $word,
            points: $scoreResult->totalPoints,
            isValid: true,
        ));

        // Build scoreboard and broadcast ScoreUpdated
        $scoreboard = $this->buildScoreboard($game, $round);
        event(new ScoreUpdated(
            game: $game,
            scoreboard: $scoreboard,
        ));

        // Dispatch next BotPlayJob with random 3-8 second delay (Req 11.2)
        self::dispatch($this->roundId, $this->botPlayerId, $this->wordsSubmitted + 1)
            ->delay(now()->addSeconds(rand(self::MIN_DELAY_SECONDS, self::MAX_DELAY_SECONDS)));
    }

    /**
     * Build a scoreboard array of all players sorted by score.
     */
    private function buildScoreboard(Game $game, GameRound $round): array
    {
        $players = $game->players()->orderByDesc('total_score')->get();
        $position = 1;

        return $players->map(function ($player) use ($round, &$position) {
            $lastSubmission = SubmittedWord::where('game_round_id', $round->id)
                ->where('game_player_id', $player->id)
                ->where('is_valid', true)
                ->orderByDesc('submitted_at')
                ->first();

            return [
                'nickname' => $player->nickname,
                'score' => $player->total_score,
                'position' => $position++,
                'last_word' => $lastSubmission?->word,
            ];
        })->toArray();
    }
}
