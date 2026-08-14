<?php

namespace App\Jobs;

use App\Models\GameRound;
use App\Services\RoundManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EndRoundJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $roundId,
    ) {}

    /**
     * Execute the job.
     *
     * Ends the round if it is still in "playing" status.
     * This ensures the round ends after 60 seconds even if no client triggers it.
     */
    public function handle(RoundManager $roundManager): void
    {
        $round = GameRound::find($this->roundId);

        if ($round && $round->status === 'playing') {
            $roundManager->endRound($round);
        }
    }
}
