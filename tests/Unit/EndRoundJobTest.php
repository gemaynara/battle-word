<?php

namespace Tests\Unit;

use App\Jobs\EndRoundJob;
use App\Models\Game;
use App\Models\GameRound;
use App\Services\LetterSetGenerator;
use App\Services\LetterSetResult;
use App\Services\RoundManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndRoundJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_end_round_job_ends_playing_round(): void
    {
        $round = $this->createPlayingRound();

        $job = new EndRoundJob($round->id);
        $job->handle($this->createRoundManager());

        $round->refresh();
        $this->assertEquals('finished', $round->status);
        $this->assertNotNull($round->finished_at);
    }

    public function test_end_round_job_does_nothing_if_round_already_finished(): void
    {
        $round = $this->createPlayingRound();
        $round->update(['status' => 'finished', 'finished_at' => now()->subSeconds(5)]);
        $originalFinishedAt = $round->finished_at->toIso8601String();

        $job = new EndRoundJob($round->id);
        $job->handle($this->createRoundManager());

        $round->refresh();
        $this->assertEquals('finished', $round->status);
        $this->assertEquals($originalFinishedAt, $round->finished_at->toIso8601String());
    }

    public function test_end_round_job_does_nothing_if_round_not_found(): void
    {
        $job = new EndRoundJob(99999);
        // Should not throw an exception
        $job->handle($this->createRoundManager());

        $this->assertTrue(true); // No exception thrown
    }

    public function test_end_round_job_does_nothing_if_round_is_waiting(): void
    {
        $game = Game::create([
            'code' => 'TST' . rand(100, 999),
            'status' => 'playing',
            'mode' => 'arena',
            'max_players' => 10,
            'total_rounds' => 3,
            'round_duration_seconds' => 60,
        ]);

        $round = GameRound::create([
            'game_id' => $game->id,
            'round_number' => 1,
            'letters' => 'MARTES',
            'base_word' => 'MARTES',
            'duration_seconds' => 60,
            'status' => 'waiting',
            'total_valid_words' => 15,
        ]);

        $job = new EndRoundJob($round->id);
        $job->handle($this->createRoundManager());

        $round->refresh();
        $this->assertEquals('waiting', $round->status);
        $this->assertNull($round->finished_at);
    }

    // --- Helpers ---

    private function createPlayingRound(): GameRound
    {
        $game = Game::create([
            'code' => 'TST' . rand(100, 999),
            'status' => 'playing',
            'mode' => 'arena',
            'max_players' => 10,
            'total_rounds' => 3,
            'round_duration_seconds' => 60,
        ]);

        return GameRound::create([
            'game_id' => $game->id,
            'round_number' => 1,
            'letters' => 'MARTES',
            'base_word' => 'MARTES',
            'duration_seconds' => 60,
            'status' => 'playing',
            'total_valid_words' => 15,
            'started_at' => now(),
        ]);
    }

    private function createRoundManager(): RoundManager
    {
        $mockGenerator = $this->createMock(LetterSetGenerator::class);
        $mockGenerator->method('generate')->willReturn(
            new LetterSetResult(letters: 'MARTES', baseWord: 'MARTES', validWordCount: 15)
        );

        return new RoundManager($mockGenerator);
    }
}
