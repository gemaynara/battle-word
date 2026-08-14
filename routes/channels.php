<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Game channel - open for MVP since players use tokens not standard auth.
// In production, validate the player_token against the game.
Broadcast::channel('game.{code}', function ($user, $code) {
    return true;
});
