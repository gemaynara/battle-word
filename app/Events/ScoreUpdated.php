<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Game $game,
        public array $scoreboard,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->game->code)];
    }

    public function broadcastAs(): string
    {
        return 'ScoreUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'scoreboard' => $this->scoreboard,
        ];
    }
}
