<?php

namespace App\Events;

use App\Models\GameRound;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public GameRound $round,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->round->game->code)];
    }

    public function broadcastAs(): string
    {
        return 'RoundStarted';
    }

    public function broadcastWith(): array
    {
        return [
            'round_number' => $this->round->round_number,
            'letters' => $this->round->letters,
            'started_at' => $this->round->started_at->toIso8601String(),
            'duration_seconds' => $this->round->duration_seconds,
            'level' => min($this->round->round_number, 4),
        ];
    }
}
