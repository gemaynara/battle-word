<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Events\WordSubmitted;
use App\Models\Game;
use App\Models\SubmittedWord;
use App\Services\SemanticSimilarityService;
use App\Services\WordValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WordSubmissionController extends Controller
{
    public function __construct(
        private WordValidator $wordValidator,
        private SemanticSimilarityService $similarityService,
    ) {}

    /**
     * Submit a word for the current round.
     * The word is scored based on semantic similarity to the theme word.
     * POST /api/games/{code}/submit-word
     */
    public function store(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'word' => 'required|string|max:30',
        ]);

        $player = $request->attributes->get('game_player');
        $game = $player->game;
        $word = mb_strtoupper(trim($request->input('word')));

        // Get current active round
        $round = $game->currentRound();

        if (!$round) {
            return response()->json([
                'error' => 'no_active_round',
                'message' => 'Nenhuma rodada ativa.',
            ], 422);
        }

        // Validate word through the pipeline
        $validationResult = $this->wordValidator->validate($round, $player, $word);

        if (!$validationResult->isValid) {
            // Save invalid submission
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
                'similarity' => 0,
                'is_perfect_word' => false,
                'is_long_word' => false,
                'player_total_score' => $player->total_score,
                'rejection_reason' => $this->translateRejection($validationResult->rejectionReason),
            ]);
        }

        // Calculate semantic similarity between submitted word and theme word
        $themeWord = $round->base_word;
        $similarity = $this->similarityService->calculateSimilarity($themeWord, $word);
        $points = $this->similarityService->similarityToPoints($similarity);

        $isValid = $points > 0;
        $rejectionReason = $isValid ? null : 'low_similarity';

        // Save submission
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player->id,
            'word' => $word,
            'is_valid' => $isValid,
            'rejection_reason' => $rejectionReason,
            'points' => $points,
            'combo_multiplier' => 1,
            'total_points' => $points,
            'is_perfect_word' => $points >= 80,
            'is_long_word' => false,
            'submitted_at' => now(),
        ]);

        // Update player's total score if valid
        if ($isValid) {
            $player->increment('total_score', $points);
            $player->refresh();

            // Add 5 seconds to the round duration
            $round->increment('duration_seconds', 5);

            // Record weekly ranking
            if (!$player->is_bot) {
                $validWordsCount = \App\Models\SubmittedWord::where('game_round_id', $round->id)
                    ->where('game_player_id', $player->id)
                    ->where('is_valid', true)
                    ->count();

                \App\Models\WeeklyRanking::recordScore(
                    $player->nickname,
                    $player->total_score,
                    $game->code,
                    $validWordsCount
                );
            }

            // Broadcast WordSubmitted event
            try {
                event(new WordSubmitted(
                    game: $game,
                    playerNickname: $player->nickname,
                    word: $word,
                    points: $points,
                    isValid: true,
                ));

                // Build scoreboard and broadcast ScoreUpdated
                $scoreboard = $this->buildScoreboard($game);
                event(new ScoreUpdated(
                    game: $game,
                    scoreboard: $scoreboard,
                ));
            } catch (\Exception $e) {
                // Broadcasting failure should not break the API response
                \Illuminate\Support\Facades\Log::warning('Broadcast failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'word' => $word,
            'is_valid' => $isValid,
            'points' => $points,
            'combo_multiplier' => 1,
            'total_points' => $points,
            'similarity' => round($similarity * 100),
            'is_perfect_word' => $points >= 80,
            'is_long_word' => false,
            'player_total_score' => $player->total_score,
            'rejection_reason' => $isValid ? null : 'Palavra pouco relacionada',
            'time_bonus' => $isValid ? 5 : 0,
        ]);
    }

    /**
     * Translate rejection reason to user-friendly message.
     */
    private function translateRejection(?string $reason): string
    {
        return match ($reason) {
            'time_expired' => 'Tempo esgotado',
            'not_connected' => 'Jogador desconectado',
            'too_short' => 'Palavra muito curta',
            'same_as_theme' => 'Não pode usar a palavra-tema',
            'not_in_dictionary' => 'Palavra não encontrada no dicionário',
            'duplicate' => 'Palavra já enviada',
            'low_similarity' => 'Palavra pouco relacionada',
            default => 'Palavra inválida',
        };
    }

    /**
     * Build scoreboard array.
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
