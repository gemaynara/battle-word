<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerDisconnected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Game $game,
        public int $playerId,
        public string $nickname,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->game->code)];
    }

    public function broadcastAs(): string
    {
        return 'PlayerDisconnected';
    }

    public function broadcastWith(): array
    {
        return [
            'player_id' => $this->playerId,
            'nickname' => $this->nickname,
        ];
    }
}
