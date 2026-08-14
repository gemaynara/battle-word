<?php

namespace App\Http\Controllers;

use App\Models\GamePlayer;
use App\Models\GameScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameHistoryController extends Controller
{
    /**
     * Get authenticated user's game history.
     * GET /api/games/history
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $gamePlayers = GamePlayer::where('user_id', $user->id)
            ->with(['game' => function ($query) {
                $query->whereIn('status', ['finished', 'playing']);
            }])
            ->whereHas('game', function ($query) {
                $query->whereIn('status', ['finished', 'playing']);
            })
            ->orderByDesc('joined_at')
            ->paginate(20);

        $data = $gamePlayers->getCollection()->map(function (GamePlayer $gamePlayer) {
            $game = $gamePlayer->game;
            $totalPlayers = $game->players()->count();

            // Get player's best score record
            $bestScore = GameScore::where('game_player_id', $gamePlayer->id)
                ->where('game_id', $game->id)
                ->orderByDesc('round_score')
                ->first();

            return [
                'code' => $game->code,
                'mode' => $game->mode,
                'played_at' => $game->started_at?->toIso8601String() ?? $gamePlayer->joined_at->toIso8601String(),
                'my_score' => $gamePlayer->total_score,
                'my_position' => $gamePlayer->position ?? $bestScore?->position_in_round,
                'total_players' => $totalPlayers,
                'my_words' => $gamePlayer->total_words,
                'longest_word' => $bestScore?->longest_word,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $gamePlayers->currentPage(),
                'total' => $gamePlayers->total(),
                'per_page' => $gamePlayers->perPage(),
            ],
        ]);
    }
}
