<?php

namespace App\Events;

use App\Models\GameRound;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public GameRound $round,
        public array $finalScores,
        public array $highlights,
        public array $winner,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->round->game->code)];
    }

    public function broadcastAs(): string
    {
        return 'RoundEnded';
    }

    public function broadcastWith(): array
    {
        return [
            'round_number' => $this->round->round_number,
            'final_scores' => $this->finalScores,
            'highlights' => $this->highlights,
            'winner' => $this->winner,
            'base_word' => mb_strtoupper($this->round->base_word),
        ];
    }
}
