<?php

namespace App\Http\Middleware;

use App\Models\GamePlayer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveGamePlayer
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->input('player_token') ?? $request->header('X-Player-Token');

        if (!$token) {
            return response()->json(['error' => 'Player token required.'], 401);
        }

        $player = GamePlayer::findByToken($token);

        if (!$player) {
            return response()->json(['error' => 'Invalid player token.'], 401);
        }

        // Support reconnection: if player disconnected less than 30 seconds ago, reconnect them
        if (!$player->is_connected && $player->disconnected_at) {
            $disconnectedSeconds = now()->diffInSeconds($player->disconnected_at);
            if ($disconnectedSeconds <= 30) {
                $player->update([
                    'is_connected' => true,
                    'disconnected_at' => null,
                ]);
            } else {
                return response()->json(['error' => 'Session expired. Please rejoin the game.'], 401);
            }
        }

        // Store player on request for controllers to use
        $request->merge(['game_player' => $player]);
        $request->attributes->set('game_player', $player);

        return $next($request);
    }
}
