<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GameService
{
    private const CODE_CHARACTERS = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    private const CODE_LENGTH = 6;
    private const MAX_CODE_ATTEMPTS = 5;

    public function __construct(
        private RoundManager $roundManager,
    ) {}

    /**
     * Create a new game session with a unique 6-char code.
     *
     * @throws \RuntimeException If unable to generate unique code after max attempts
     */
    public function createGame(string $mode = 'arena', ?int $hostUserId = null, string $category = 'aleatorio'): Game
    {
        $code = $this->generateUniqueCode();

        return DB::transaction(function () use ($code, $mode, $hostUserId, $category) {
            $game = Game::create([
                'code' => $code,
                'status' => 'waiting',
                'mode' => $mode,
                'host_user_id' => $hostUserId,
                'max_players' => 10,
                'round_duration_seconds' => 60,
                'settings' => ['category' => $category],
            ]);

            // Register host as first player
            $hostNickname = $mode === 'vs_computer' ? 'Jogador' : 'Host';
            GamePlayer::create([
                'game_id' => $game->id,
                'user_id' => $hostUserId,
                'nickname' => $hostNickname,
                'is_host' => true,
                'is_connected' => true,
                'joined_at' => now(),
            ]);

            // If mode is vs_computer, create a bot player
            if ($mode === 'vs_computer') {
                GamePlayer::create([
                    'game_id' => $game->id,
                    'nickname' => 'RoBot',
                    'is_bot' => true,
                    'is_connected' => true,
                    'joined_at' => now(),
                ]);
            }

            // Load players relationship before returning
            $game->load('players');

            return $game;
        });
    }

    /**
     * Join a player to an existing game.
     *
     * @throws ValidationException If nickname is invalid, taken, or game not joinable
     */
    public function joinGame(string $code, string $nickname, ?int $userId = null): GamePlayer
    {
        $this->validateNickname($nickname);

        $game = Game::where('code', strtoupper($code))->first();

        if (!$game) {
            throw ValidationException::withMessages([
                'code' => ['Jogo não encontrado.'],
            ]);
        }

        if ($game->status !== 'waiting') {
            throw ValidationException::withMessages([
                'game' => ['Este jogo não está mais aceitando jogadores.'],
            ]);
        }

        if ($game->players()->count() >= $game->max_players) {
            throw ValidationException::withMessages([
                'game' => ['O jogo está lotado.'],
            ]);
        }

        // Case-insensitive nickname uniqueness check within the game
        $nicknameExists = $game->players()
            ->whereRaw('LOWER(nickname) = ?', [strtolower($nickname)])
            ->exists();

        if ($nicknameExists) {
            throw ValidationException::withMessages([
                'nickname' => ['Este apelido já está em uso.'],
            ]);
        }

        $player = GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $userId,
            'nickname' => $nickname,
            'is_connected' => true,
            'joined_at' => now(),
        ]);

        // Broadcast PlayerJoined event
        event(new \App\Events\PlayerJoined($game, $player));

        return $player;
    }

    /**
     * Get the current state of a game.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If game not found
     */
    public function getGameState(string $code): array
    {
        $game = Game::where('code', strtoupper($code))
            ->with(['players' => function ($query) {
                $query->orderByDesc('total_score');
            }])
            ->firstOrFail();

        $currentRound = $game->currentRound();

        $state = [
            'code' => $game->code,
            'status' => $game->status,
            'mode' => $game->mode,
            'max_players' => $game->max_players,
            'qr_url' => $this->generateQrUrl($game->code),
            'players' => $game->players->map(function (GamePlayer $player) {
                return [
                    'id' => $player->id,
                    'nickname' => $player->nickname,
                    'score' => $player->total_score ?? 0,
                    'is_host' => $player->is_host,
                    'is_connected' => $player->is_connected,
                ];
            })->toArray(),
            'current_round' => null,
        ];

        if ($currentRound) {
            $state['current_round'] = [
                'round_number' => $currentRound->round_number,
                'letters' => $currentRound->letters,
                'started_at' => $currentRound->started_at?->toIso8601String(),
                'duration_seconds' => $currentRound->duration_seconds,
                'status' => $currentRound->status,
            ];
        }

        return $state;
    }

    /**
     * Start a new round in the same game (play again).
     * Resets player scores and generates a new letter set.
     */
    public function playAgain(Game $game): GameRound
    {
        return DB::transaction(function () use ($game) {
            // Reset all players' total_score to 0
            $game->players()->update([
                'total_score' => 0,
            ]);

            // Set game back to waiting status
            $game->update(['status' => 'waiting']);

            // Create a new round via RoundManager
            return $this->roundManager->createRound($game);
        });
    }

    /**
     * Generate the QR code URL for a game.
     */
    public function generateQrUrl(string $code): string
    {
        return rtrim(config('app.url'), '/') . '/play/' . $code;
    }

    /**
     * Generate a unique 6-character code for a game.
     *
     * @throws \RuntimeException If unable to generate unique code after max attempts
     */
    private function generateUniqueCode(): string
    {
        $characters = self::CODE_CHARACTERS;
        $charLength = strlen($characters);

        for ($attempt = 1; $attempt <= self::MAX_CODE_ATTEMPTS; $attempt++) {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= $characters[random_int(0, $charLength - 1)];
            }

            // Check uniqueness among active games (waiting or playing)
            $exists = Game::where('code', $code)
                ->whereIn('status', ['waiting', 'playing'])
                ->exists();

            if (!$exists) {
                return $code;
            }
        }

        throw new \RuntimeException(
            'Não foi possível gerar um código único após ' . self::MAX_CODE_ATTEMPTS . ' tentativas.'
        );
    }

    /**
     * Validate nickname format: 2-30 chars, letters (including accented), numbers, spaces, underscores.
     *
     * @throws ValidationException If nickname is invalid
     */
    private function validateNickname(string $nickname): void
    {
        $nickname = trim($nickname);

        if (mb_strlen($nickname) < 2 || mb_strlen($nickname) > 30) {
            throw ValidationException::withMessages([
                'nickname' => ['O apelido deve ter entre 2 e 30 caracteres.'],
            ]);
        }

        // Allow letters (including accented via \p{L}), numbers, spaces, underscores
        if (!preg_match('/^[\p{L}0-9 _]+$/u', $nickname)) {
            throw ValidationException::withMessages([
                'nickname' => ['O apelido só pode conter letras, números, espaços e underscores.'],
            ]);
        }
    }
}
