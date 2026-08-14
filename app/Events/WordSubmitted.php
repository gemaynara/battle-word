<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WordSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Game $game,
        public string $playerNickname,
        public string $word,
        public int $points,
        public bool $isValid,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->game->code)];
    }

    public function broadcastAs(): string
    {
        return 'WordSubmitted';
    }

    public function broadcastWith(): array
    {
        return [
            'player_nickname' => $this->playerNickname,
            'word' => $this->word,
            'points' => $this->points,
            'is_valid' => $this->isValid,
        ];
    }
}
