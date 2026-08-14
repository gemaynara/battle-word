<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\RoundManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function __construct(
        private RoundManager $roundManager,
    ) {}

    /**
     * Start a new round (host only).
     * POST /api/games/{code}/start-round
     */
    public function start(Request $request, string $code): JsonResponse
    {
        $player = $request->attributes->get('game_player');
        $game = $player->game;

        if (!$player->is_host) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Only the host can start a round.',
            ], 403);
        }

        // Check if there's already an active round
        $currentRound = $game->currentRound();
        if ($currentRound) {
            // Return the active round's data instead of error
            // This handles the case where the WebSocket event was missed
            return response()->json([
                'round_number' => $currentRound->round_number,
                'letters' => $currentRound->letters,
                'started_at' => $currentRound->started_at->toIso8601String(),
                'duration_seconds' => $currentRound->duration_seconds,
                'level' => min($currentRound->round_number, 4),
            ]);
        }

        // Get the latest round in "waiting" status, or create one
        $round = $game->rounds()->where('status', 'waiting')->latest()->first();

        if (!$round) {
            $round = $this->roundManager->createRound($game);
        }

        // Update game status to playing
        $game->update(['status' => 'playing']);

        $this->roundManager->startRound($round);
        $round->refresh();

        return response()->json([
            'round_number' => $round->round_number,
            'letters' => $round->letters,
            'started_at' => $round->started_at->toIso8601String(),
            'duration_seconds' => $round->duration_seconds,
            'level' => min($round->round_number, 4),
        ]);
    }

    /**
     * Get current round state.
     * GET /api/games/{code}/round
     */
    public function show(string $code): JsonResponse
    {
        $game = Game::where('code', strtoupper($code))->firstOrFail();

        $currentRound = $game->rounds()->whereIn('status', ['waiting', 'playing'])->latest()->first();

        if (!$currentRound) {
            // Get the latest finished round
            $currentRound = $game->rounds()->where('status', 'finished')->latest()->first();
        }

        if (!$currentRound) {
            return response()->json([
                'error' => 'no_round',
                'message' => 'No round found for this game.',
            ], 404);
        }

        return response()->json([
            'round_number' => $currentRound->round_number,
            'letters' => $currentRound->letters,
            'started_at' => $currentRound->started_at?->toIso8601String(),
            'duration_seconds' => $currentRound->duration_seconds,
            'status' => $currentRound->status,
            'time_remaining' => $currentRound->time_remaining,
        ]);
    }
}
