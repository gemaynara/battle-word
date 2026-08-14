<?php

namespace Tests\Unit;

use App\Events\ScoreUpdated;
use App\Events\WordSubmitted;
use App\Jobs\BotPlayJob;
use App\Models\DictionaryWord;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BotPlayJobTest extends TestCase
{
    use RefreshDatabase;

    // ─── Job Execution ───

    public function test_job_submits_word_and_saves_record(): void
    {
        Event::fake();
        Queue::fake();

        [$round, $bot] = $this->createRoundAndBot();
        $this->seedDictionary();

        $job = new BotPlayJob($round->id, $bot->id, 0);
        $job->handle(
            app(\App\Services\BotPlayerService::class),
            app(\App\Services\WordValidator::class),
            app(\App\Services\ScoringEngine::class),
        );

        // Verify a word was submitted
        $this->assertDatabaseHas('submitted_words', [
            'game_round_id' => $round->id,
            'game_player_id' => $bot->id,
            'is_valid' => true,
        ]);
    }

    public function test_job_broadcasts_events_on_valid_submission(): void
    {
        Event::fake([WordSubmitted::class, ScoreUpdated::class]);
        Queue::fake();

        [$round, $bot] = $this->createRoundAndBot();
        $this->seedDictionary();

        $job = new BotPlayJob($round->id, $bot->id, 0);
        $job->handle(
            app(\App\Services\BotPlayerService::class),
            app(\App\Services\WordValidator::class),
            app(\App\Services\ScoringEngine::class),
        );

        Event::assertDispatched(WordSubmitted::class);
        Event::assertDispatched(ScoreUpdated::class);
    }

    public function test_job_dispatches_next_job_after_submission(): void
    {
        Event::fake();
        Queue::fake();

        [$round, $bot] = $this->createRoundAndBot();
        $this->seedDictionary();

        $job = new BotPlayJob($round->id, $bot->id, 0);
        $job->handle(
            app(\App\Services\BotPlayerService::class),
            app(\App\Services\WordValidator::class),
            app(\App\Services\ScoringEngine::class),
        );

        Queue::assertPushed(BotPlayJob::class);
    }

    public function test_job_stops_when_round_is_not_playing(): void
    {
        Event::fake();
        Queue::fake();

        [$round, $bot] = $this->createRoundAndBot();
        $this->seedDictionary();

        // Set round to finished
        $round->update(['status' => 'finished']);

        $job = new BotPlayJob($round->id, $bot->id, 0);
        $job->handle(
            app(\App\Services\BotPlayerService::class),
            app(\App\Services\WordValidator::class),
            app(\App\Services\ScoringEngine::class),
        );

        // No word submitted, no events
        $this->assertDatabaseMissing('submitted_words', [
            'game_round_id' => $round->id,
            'game_player_id' => $bot->id,
        ]);
        Event::assertNotDispatched(WordSubmitted::class);
        Queue::assertNotPushed(BotPlayJob::class);
    }

    public function test_job_stops_when_max_words_reached(): void
    {
        Event::fake();
        Queue::fake();

        [$round, $bot] = $this->createRoundAndBot();
        $this->seedDictionary();

        // wordsSubmitted = 12 (max reached)
        $job = new BotPlayJob($round->id, $bot->id, 12);
        $job->handle(
            app(\App\Services\BotPlayerService::class),
            app(\App\Services\WordValidator::class),
            app(\App\Services\ScoringEngine::class),
        );

        $this->assertDatabaseMissing('submitted_words', [
            'game_round_id' => $round->id,
            'game_player_id' => $bot->id,
        ]);
        Queue::assertNotPushed(BotPlayJob::class);
    }

    public function test_job_stops_when_no_words_available(): void
    {
        Event::fake();
        Queue::fake();

        [$round, $bot] = $this->createRoundAndBot('XZQ');
        // No words can be formed from XZQ

        $job = new BotPlayJob($round->id, $bot->id, 0);
        $job->handle(
            app(\App\Services\BotPlayerService::class),
            app(\App\Services\WordValidator::class),
            app(\App\Services\ScoringEngine::class),
        );

        $this->assertDatabaseMissing('submitted_words', [
            'game_round_id' => $round->id,
            'game_player_id' => $bot->id,
        ]);
        Queue::assertNotPushed(BotPlayJob::class);
    }

    public function test_job_updates_bot_total_score(): void
    {
        Event::fake();
        Queue::fake();

        [$round, $bot] = $this->createRoundAndBot();
        $this->seedDictionary();

        $this->assertEquals(0, $bot->total_score);

        $job = new BotPlayJob($round->id, $bot->id, 0);
        $job->handle(
            app(\App\Services\BotPlayerService::class),
            app(\App\Services\WordValidator::class),
            app(\App\Services\ScoringEngine::class),
        );

        $bot->refresh();
        $this->assertGreaterThan(0, $bot->total_score);
    }

    public function test_job_stops_when_round_not_found(): void
    {
        Event::fake();
        Queue::fake();

        [, $bot] = $this->createRoundAndBot();

        $job = new BotPlayJob(99999, $bot->id, 0);
        $job->handle(
            app(\App\Services\BotPlayerService::class),
            app(\App\Services\WordValidator::class),
            app(\App\Services\ScoringEngine::class),
        );

        Event::assertNotDispatched(WordSubmitted::class);
        Queue::assertNotPushed(BotPlayJob::class);
    }

    // ─── Helpers ───

    private function createRoundAndBot(string $letters = 'MARTES'): array
    {
        $game = Game::create([
            'code' => 'BOT' . rand(100, 999),
            'status' => 'playing',
            'mode' => 'vs_computer',
            'max_players' => 2,
            'round_duration_seconds' => 60,
        ]);

        $bot = GamePlayer::create([
            'game_id' => $game->id,
            'nickname' => 'Bot 🤖',
            'is_host' => false,
            'is_bot' => true,
            'is_connected' => true,
            'total_score' => 0,
            'total_words' => 0,
            'best_combo' => 0,
            'longest_word_length' => 0,
            'joined_at' => now(),
        ]);

        $round = GameRound::create([
            'game_id' => $game->id,
            'round_number' => 1,
            'letters' => $letters,
            'base_word' => strtolower($letters),
            'duration_seconds' => 60,
            'status' => 'playing',
            'total_valid_words' => 0,
            'started_at' => now(),
        ]);

        return [$round, $bot];
    }

    private function seedDictionary(): void
    {
        $words = [
            ['word' => 'MAR', 'length' => 3],
            ['word' => 'TE', 'length' => 2],
            ['word' => 'MARTE', 'length' => 5],
            ['word' => 'ARTE', 'length' => 4],
            ['word' => 'MARTES', 'length' => 6],
            ['word' => 'REST', 'length' => 4],
            ['word' => 'MET', 'length' => 3],
        ];

        foreach ($words as $wordData) {
            DictionaryWord::create([
                'word' => $wordData['word'],
                'length' => $wordData['length'],
                'is_valid' => true,
                'is_inappropriate' => false,
            ]);
        }
    }
}
