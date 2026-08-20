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
     * Ends the round if it is still in "playing" status AND the time has actually expired.
     * If the duration was extended (via time bonuses), re-dispatch with remaining time.
     */
    public function handle(RoundManager $roundManager): void
    {
        $round = GameRound::find($this->roundId);

        if (!$round || $round->status !== 'playing') {
            return;
        }

        // Check if time has actually expired (duration may have been extended)
        $elapsed = now()->diffInSeconds($round->started_at, absolute: true);
        $remaining = $round->duration_seconds - $elapsed;

        if ($remaining > 0) {
            // Time was extended, re-dispatch with remaining seconds
            self::dispatch($this->roundId)->delay(now()->addSeconds($remaining));
            return;
        }

        $roundManager->endRound($round);
    }
}
