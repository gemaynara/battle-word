<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Events\WordSubmitted;
use App\Models\Game;
use App\Models\SubmittedWord;
use App\Services\ScoringEngine;
use App\Services\WordValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WordSubmissionController extends Controller
{
    public function __construct(
        private WordValidator $wordValidator,
        private ScoringEngine $scoringEngine,
    ) {}

    /**
     * Submit a word for the current round.
     * POST /api/games/{code}/submit-word
     */
    public function store(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'word' => 'required|string|max:15',
        ]);

        $player = $request->attributes->get('game_player');
        $game = $player->game;
        $word = strtoupper(trim($request->input('word')));

        // Get current active round
        $round = $game->currentRound();

        if (!$round) {
            return response()->json([
                'error' => 'no_active_round',
                'message' => 'No active round found.',
            ], 422);
        }

        // Validate word through the pipeline
        $validationResult = $this->wordValidator->validate($round, $player, $word);

        if (!$validationResult->isValid) {
            // Save invalid submission (resets combo)
            SubmittedWord::create([
                'game_round_id' => $round->id,
                'game_player_id' => $player->id,
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

            return response()->json([
                'word' => $word,
                'is_valid' => false,
                'points' => 0,
                'combo_multiplier' => 1,
                'total_points' => 0,
                'is_perfect_word' => false,
                'is_long_word' => false,
                'player_total_score' => $player->total_score,
                'rejection_reason' => $validationResult->rejectionReason,
            ]);
        }

        // Get current combo for this player
        $currentCombo = $this->scoringEngine->getComboForPlayer($round, $player);

        // Calculate points
        $scoreResult = $this->scoringEngine->calculatePoints($word, $round->letters, $currentCombo);

        // Save valid submission
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player->id,
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

        // Update player's total score atomically
        $this->scoringEngine->updatePlayerScore($player, $scoreResult->totalPoints);
        $player->refresh();

        // Broadcast WordSubmitted event
        event(new WordSubmitted(
            game: $game,
            playerNickname: $player->nickname,
            word: $word,
            points: $scoreResult->totalPoints,
            isValid: true,
        ));

        // Build scoreboard and broadcast ScoreUpdated
        $scoreboard = $this->buildScoreboard($game);
        event(new ScoreUpdated(
            game: $game,
            scoreboard: $scoreboard,
        ));

        return response()->json([
            'word' => $word,
            'is_valid' => true,
            'points' => $scoreResult->points,
            'combo_multiplier' => $scoreResult->comboMultiplier,
            'total_points' => $scoreResult->totalPoints,
            'is_perfect_word' => $scoreResult->isPerfectWord,
            'is_long_word' => $scoreResult->isLongWord,
            'player_total_score' => $player->total_score,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Build a scoreboard array of all players sorted by score with their last valid word.
     */
    private function buildScoreboard(Game $game): array
    {
        $players = $game->players()->orderByDesc('total_score')->get();
        $currentRound = $game->currentRound();

        $position = 1;

        return $players->map(function ($player) use ($currentRound, &$position) {
            $lastWord = null;

            if ($currentRound) {
                $lastSubmission = SubmittedWord::where('game_round_id', $currentRound->id)
                    ->where('game_player_id', $player->id)
                    ->where('is_valid', true)
                    ->orderByDesc('submitted_at')
                    ->first();

                $lastWord = $lastSubmission?->word;
            }

            return [
                'nickname' => $player->nickname,
                'score' => $player->total_score,
                'position' => $position++,
                'last_word' => $lastWord,
            ];
        })->toArray();
    }
}
