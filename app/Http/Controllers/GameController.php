<?php

namespace App\Http\Controllers;

use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        private GameService $gameService,
    ) {}

    /**
     * Create a new game.
     * POST /api/games
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'mode' => 'sometimes|string|in:arena,vs_computer',
            'category' => 'sometimes|string|in:substantivos,cidades,filmes,animais,comidas,profissoes,esportes,natureza,aleatorio',
        ]);

        $mode = $request->input('mode', 'arena');
        $category = $request->input('category', 'aleatorio');
        $hostUserId = $request->user()?->id;

        $game = $this->gameService->createGame($mode, $hostUserId, $category);

        // Get the host player token
        $hostPlayer = $game->players->firstWhere('is_host', true);

        return response()->json([
            'code' => $game->code,
            'qr_url' => $this->gameService->generateQrUrl($game->code),
            'status' => $game->status,
            'mode' => $game->mode,
            'max_players' => $game->max_players,
            'player_token' => $hostPlayer?->player_token,
            'category' => $category,
        ], 201);
    }

    /**
     * Get game state.
     * GET /api/games/{code}
     */
    public function show(string $code): JsonResponse
    {
        $state = $this->gameService->getGameState($code);

        return response()->json($state);
    }

    /**
     * Join a game.
     * POST /api/games/{code}/join
     */
    public function join(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'nickname' => 'required|string|min:2|max:30',
        ]);

        try {
            $player = $this->gameService->joinGame(
                $code,
                $request->input('nickname'),
                $request->user()?->id,
            );

            return response()->json([
                'player_token' => $player->player_token,
                'player_id' => $player->id,
                'nickname' => $player->nickname,
                'game_code' => strtoupper($code),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = $e->errors();

            // Handle nickname_taken error specifically
            if (isset($messages['nickname'])) {
                return response()->json([
                    'error' => 'nickname_taken',
                    'message' => 'Este apelido já está em uso.',
                ], 422);
            }

            return response()->json([
                'error' => 'validation_error',
                'message' => collect($messages)->flatten()->first(),
            ], 422);
        }
    }

    /**
     * Play again (create new round).
     * POST /api/games/{code}/play-again
     */
    public function playAgain(Request $request, string $code): JsonResponse
    {
        $player = $request->attributes->get('game_player');
        $game = $player->game;

        if (!$player->is_host) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Only the host can start a new round.',
            ], 403);
        }

        $round = $this->gameService->playAgain($game);

        return response()->json([
            'round_number' => $round->round_number,
            'letters' => $round->letters,
            'started_at' => $round->started_at?->toIso8601String(),
            'duration_seconds' => $round->duration_seconds,
        ]);
    }
}
