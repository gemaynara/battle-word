<?php

namespace App\Services;

use App\Jobs\EndRoundJob;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\GameScore;
use App\Models\SubmittedWord;
use Illuminate\Support\Facades\DB;

class RoundManager
{
    public function __construct(
        private LetterSetGenerator $letterSetGenerator,
    ) {}

    /**
     * Duration per level in seconds.
     * Level 1 (easy): 60s, Level 2: 50s, Level 3: 45s, Level 4+: 40s
     */
    private const DURATION_BY_LEVEL = [
        1 => 60,
        2 => 50,
        3 => 45,
        4 => 40,
    ];

    /**
     * Create a new round for the given game using LetterSetGenerator.
     * Sets status to "waiting" and calculates the next round_number.
     * Difficulty increases with each round (level = round_number, capped at 4).
     */
    public function createRound(Game $game): GameRound
    {
        $nextRoundNumber = $game->rounds()->count() + 1;
        $level = min($nextRoundNumber, 4);

        // Get category from game settings
        $category = $game->settings['category'] ?? null;

        $letterSet = $this->letterSetGenerator->generate($level, $category);
        $duration = self::DURATION_BY_LEVEL[$level] ?? 40;

        return GameRound::create([
            'game_id' => $game->id,
            'round_number' => $nextRoundNumber,
            'letters' => $letterSet->letters,
            'base_word' => $letterSet->baseWord,
            'duration_seconds' => $duration,
            'status' => 'waiting',
            'total_valid_words' => $letterSet->validWordCount,
        ]);
    }

    /**
     * Start a round: set status to "playing", record started_at,
     * dispatch EndRoundJob delayed by duration_seconds, broadcast RoundStarted.
     *
     * @throws \InvalidArgumentException If round is not in "waiting" status.
     */
    public function startRound(GameRound $round): void
    {
        if ($round->status !== 'waiting') {
            throw new \InvalidArgumentException(
                "Cannot start round #{$round->round_number}: round is not in 'waiting' status (current: '{$round->status}')."
            );
        }

        $round->update([
            'status' => 'playing',
            'started_at' => now(),
        ]);

        // Dispatch EndRoundJob delayed by the round's duration
        EndRoundJob::dispatch($round->id)->delay(now()->addSeconds($round->duration_seconds));

        // Broadcast RoundStarted event (created in Task 9)
        $this->broadcastRoundStarted($round);
    }

    /**
     * End a round: set status to "finished", record finished_at,
     * calculate final positions, create GameScore records, broadcast RoundEnded.
     */
    public function endRound(GameRound $round): void
    {
        if ($round->status !== 'playing') {
            return;
        }

        $round->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        $this->calculateFinalPositions($round);

        // Broadcast RoundEnded event (created in Task 9)
        $this->broadcastRoundEnded($round);
    }

    /**
     * Calculate final positions for all players in the round.
     * Ranking: total score descending.
     * Tiebreaker 1: fewer valid words = higher rank.
     * Tiebreaker 2: earlier joined_at = higher rank.
     *
     * Creates GameScore records for each player.
     */
    public function calculateFinalPositions(GameRound $round): void
    {
        $game = $round->game;
        $players = $game->players()->get();

        // Gather round stats for each player
        $playerStats = $players->map(function (GamePlayer $player) use ($round) {
            $roundWords = SubmittedWord::where('game_round_id', $round->id)
                ->where('game_player_id', $player->id)
                ->get();

            $validWords = $roundWords->where('is_valid', true);
            $invalidWords = $roundWords->where('is_valid', false);

            $roundScore = $validWords->sum('total_points');
            $longestValidWord = $validWords->sortByDesc(fn ($w) => mb_strlen($w->word))->first();
            $bestCombo = $validWords->max('combo_multiplier') ?? 0;
            $hadPerfectWord = $validWords->contains('is_perfect_word', true);

            return [
                'player' => $player,
                'round_score' => $roundScore,
                'words_submitted' => $roundWords->count(),
                'valid_words' => $validWords->count(),
                'invalid_words' => $invalidWords->count(),
                'best_combo' => $bestCombo,
                'longest_word_length' => $longestValidWord ? mb_strlen($longestValidWord->word) : 0,
                'longest_word' => $longestValidWord?->word,
                'had_perfect_word' => $hadPerfectWord,
            ];
        });

        // Sort for position assignment:
        // 1. Higher round_score first
        // 2. Fewer valid_words first (tiebreaker)
        // 3. Earlier joined_at first (tiebreaker)
        $sorted = $playerStats->sort(function ($a, $b) {
            // Primary: score descending
            if ($a['round_score'] !== $b['round_score']) {
                return $b['round_score'] <=> $a['round_score'];
            }

            // Tiebreaker 1: fewer valid words = higher rank
            if ($a['valid_words'] !== $b['valid_words']) {
                return $a['valid_words'] <=> $b['valid_words'];
            }

            // Tiebreaker 2: earlier joined_at = higher rank
            return $a['player']->joined_at <=> $b['player']->joined_at;
        })->values();

        // Create GameScore records with positions and update player position
        $position = 1;
        foreach ($sorted as $stats) {
            GameScore::updateOrCreate(
                [
                    'game_round_id' => $round->id,
                    'game_player_id' => $stats['player']->id,
                ],
                [
                    'game_id' => $game->id,
                    'round_score' => $stats['round_score'],
                    'words_submitted' => $stats['words_submitted'],
                    'valid_words' => $stats['valid_words'],
                    'invalid_words' => $stats['invalid_words'],
                    'best_combo' => $stats['best_combo'],
                    'longest_word_length' => $stats['longest_word_length'],
                    'longest_word' => $stats['longest_word'],
                    'had_perfect_word' => $stats['had_perfect_word'],
                    'position_in_round' => $position,
                ],
            );

            // Update player's position on GamePlayer model
            $stats['player']->update(['position' => $position]);

            $position++;
        }
    }

    /**
     * Broadcast RoundStarted event.
     */
    private function broadcastRoundStarted(GameRound $round): void
    {
        event(new \App\Events\RoundStarted($round));
    }

    /**
     * Broadcast RoundEnded event with final scores, highlights, and winner.
     */
    private function broadcastRoundEnded(GameRound $round): void
    {
        $gameScores = GameScore::where('game_round_id', $round->id)
            ->orderBy('position_in_round')
            ->get();

        $finalScores = $gameScores->map(function (GameScore $score) {
            $player = GamePlayer::find($score->game_player_id);
            return [
                'nickname' => $player?->nickname ?? 'Unknown',
                'score' => $score->round_score,
                'position' => $score->position_in_round,
                'valid_words' => $score->valid_words,
                'best_combo' => $score->best_combo,
            ];
        })->toArray();

        $highlights = [];
        $topScore = $gameScores->first();
        if ($topScore && $topScore->longest_word) {
            $player = GamePlayer::find($topScore->game_player_id);
            $highlights[] = [
                'type' => 'longest_word',
                'nickname' => $player?->nickname ?? 'Unknown',
                'word' => $topScore->longest_word,
            ];
        }

        $winner = [];
        if ($topScore) {
            $winnerPlayer = GamePlayer::find($topScore->game_player_id);
            $winner = [
                'nickname' => $winnerPlayer?->nickname ?? 'Unknown',
                'score' => $topScore->round_score,
            ];
        }

        event(new \App\Events\RoundEnded($round, $finalScores, $highlights, $winner));
    }
}
