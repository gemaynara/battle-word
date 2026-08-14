<?php

namespace Tests\Unit;

use App\Jobs\BotPlayJob;
use App\Models\DictionaryWord;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;
use App\Services\BotPlayerService;
use App\Services\LetterSetGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BotPlayerServiceTest extends TestCase
{
    use RefreshDatabase;

    private BotPlayerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BotPlayerService(new LetterSetGenerator());
    }

    // ─── startBot ───

    public function test_start_bot_dispatches_bot_play_job_with_delay(): void
    {
        Queue::fake();

        [$round, $bot] = $this->createRoundAndBot();

        $this->service->startBot($round, $bot);

        Queue::assertPushed(BotPlayJob::class, function ($job) {
            // Verify the job was dispatched with a delay
            return $job->delay !== null;
        });
    }

    // ─── selectNextWord ───

    public function test_select_next_word_returns_valid_word_from_letter_set(): void
    {
        [$round, $bot] = $this->createRoundAndBot('MARTES');
        $this->seedDictionaryForLetters('MARTES');

        $word = $this->service->selectNextWord($round, $bot);

        $this->assertNotNull($word);
        $this->assertIsString($word);
        // Word should be uppercase
        $this->assertEquals(strtoupper($word), $word);
    }

    public function test_select_next_word_returns_null_when_no_valid_words(): void
    {
        [$round, $bot] = $this->createRoundAndBot('XZQ');
        // No dictionary words can be formed from XZQ

        $word = $this->service->selectNextWord($round, $bot);

        $this->assertNull($word);
    }

    public function test_select_next_word_excludes_already_submitted_words(): void
    {
        [$round, $bot] = $this->createRoundAndBot('MARTES');

        // Seed only one valid word
        DictionaryWord::create([
            'word' => 'MAR',
            'length' => 3,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        // Submit that word already
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $bot->id,
            'word' => 'MAR',
            'is_valid' => true,
            'rejection_reason' => null,
            'points' => 3,
            'combo_multiplier' => 1,
            'total_points' => 3,
            'is_perfect_word' => false,
            'is_long_word' => false,
            'submitted_at' => now(),
        ]);

        $word = $this->service->selectNextWord($round, $bot);

        $this->assertNull($word);
    }

    public function test_select_next_word_respects_50_percent_limit(): void
    {
        [$round, $bot] = $this->createRoundAndBot('MARTES');

        // Seed exactly 2 valid words
        DictionaryWord::create(['word' => 'MAR', 'length' => 3, 'is_valid' => true, 'is_inappropriate' => false]);
        DictionaryWord::create(['word' => 'TE', 'length' => 2, 'is_valid' => true, 'is_inappropriate' => false]);

        // Submit 1 word (50% of 2 = 1, so limit reached)
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $bot->id,
            'word' => 'MAR',
            'is_valid' => true,
            'rejection_reason' => null,
            'points' => 3,
            'combo_multiplier' => 1,
            'total_points' => 3,
            'is_perfect_word' => false,
            'is_long_word' => false,
            'submitted_at' => now(),
        ]);

        $word = $this->service->selectNextWord($round, $bot);

        $this->assertNull($word);
    }

    public function test_select_next_word_respects_12_word_max(): void
    {
        [$round, $bot] = $this->createRoundAndBot('MARTES');

        // Seed many valid words
        $this->seedDictionaryForLetters('MARTES');

        // Submit 12 words already
        for ($i = 0; $i < 12; $i++) {
            SubmittedWord::create([
                'game_round_id' => $round->id,
                'game_player_id' => $bot->id,
                'word' => "W{$i}",
                'is_valid' => true,
                'rejection_reason' => null,
                'points' => 1,
                'combo_multiplier' => 1,
                'total_points' => 1,
                'is_perfect_word' => false,
                'is_long_word' => false,
                'submitted_at' => now(),
            ]);
        }

        $word = $this->service->selectNextWord($round, $bot);

        $this->assertNull($word);
    }

    public function test_select_next_word_distribution_picks_from_short_and_long_pools(): void
    {
        [$round, $bot] = $this->createRoundAndBot('MARTES');

        // Seed both short and long words
        DictionaryWord::create(['word' => 'MAR', 'length' => 3, 'is_valid' => true, 'is_inappropriate' => false]);
        DictionaryWord::create(['word' => 'TE', 'length' => 2, 'is_valid' => true, 'is_inappropriate' => false]);
        DictionaryWord::create(['word' => 'MARTE', 'length' => 5, 'is_valid' => true, 'is_inappropriate' => false]);
        DictionaryWord::create(['word' => 'MARTES', 'length' => 6, 'is_valid' => true, 'is_inappropriate' => false]);

        // Run multiple selections to statistically verify distribution covers both pools
        $shortCount = 0;
        $longCount = 0;

        for ($i = 0; $i < 50; $i++) {
            $word = $this->service->selectNextWord($round, $bot);
            if ($word === null) {
                continue;
            }

            $len = mb_strlen($word);
            if ($len >= 2 && $len <= 5) {
                $shortCount++;
            } elseif ($len >= 6) {
                $longCount++;
            }
        }

        // Both pools should be represented (statistical assertion — with 50 tries, both should appear)
        $this->assertGreaterThan(0, $shortCount, 'Short words should be selected');
        $this->assertGreaterThan(0, $longCount, 'Long words should be selected');
        // Short should dominate (70% probability)
        $this->assertGreaterThan($longCount, $shortCount, 'Short words should be selected more often (70% vs 30%)');
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

    private function seedDictionaryForLetters(string $letters): void
    {
        // Add several words that can be formed from the letters
        $words = [
            ['word' => 'MAR', 'length' => 3],
            ['word' => 'TE', 'length' => 2],
            ['word' => 'MARTE', 'length' => 5],
            ['word' => 'ARTE', 'length' => 4],
            ['word' => 'MARTES', 'length' => 6],
            ['word' => 'STER', 'length' => 4],
            ['word' => 'REST', 'length' => 4],
            ['word' => 'TREM', 'length' => 4],
            ['word' => 'MET', 'length' => 3],
            ['word' => 'MESA', 'length' => 4],
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
