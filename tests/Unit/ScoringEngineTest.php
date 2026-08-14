<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;
use App\Services\ScoreResult;
use App\Services\ScoringEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoringEngineTest extends TestCase
{
    use RefreshDatabase;

    private ScoringEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new ScoringEngine();
    }

    // ─── Point Calculation by Word Length ───

    public function test_calculate_points_2_letter_word_returns_1_point(): void
    {
        $result = $this->engine->calculatePoints('MA', 'MARTES', 1);

        $this->assertInstanceOf(ScoreResult::class, $result);
        $this->assertEquals(1, $result->points);
    }

    public function test_calculate_points_3_letter_word_returns_3_points(): void
    {
        $result = $this->engine->calculatePoints('MAR', 'MARTES', 1);

        $this->assertEquals(3, $result->points);
    }

    public function test_calculate_points_4_letter_word_returns_5_points(): void
    {
        $result = $this->engine->calculatePoints('MARE', 'MARTES', 1);

        $this->assertEquals(5, $result->points);
    }

    public function test_calculate_points_5_letter_word_returns_8_points(): void
    {
        $result = $this->engine->calculatePoints('MARTE', 'MARTES', 1);

        $this->assertEquals(8, $result->points);
    }

    public function test_calculate_points_6_letter_word_returns_12_points(): void
    {
        $result = $this->engine->calculatePoints('MARTES', 'MARTESA', 1);

        $this->assertEquals(12, $result->points);
    }

    public function test_calculate_points_7_letter_word_returns_17_points(): void
    {
        $result = $this->engine->calculatePoints('MARTESA', 'MARTESAB', 1);

        $this->assertEquals(17, $result->points);
    }

    public function test_calculate_points_8_or_more_letter_word_returns_25_points(): void
    {
        $result = $this->engine->calculatePoints('MARTESAB', 'MARTESABC', 1);

        $this->assertEquals(25, $result->points);
    }

    public function test_calculate_points_9_letter_word_returns_25_points(): void
    {
        $result = $this->engine->calculatePoints('MARTESABC', 'MARTESABCD', 1);

        $this->assertEquals(25, $result->points);
    }

    // ─── Combo Multiplier ───

    public function test_combo_multiplier_1_does_not_multiply(): void
    {
        $result = $this->engine->calculatePoints('MAR', 'MARTES', 1);

        $this->assertEquals(1, $result->comboMultiplier);
        $this->assertEquals(3, $result->totalPoints); // 3 * 1
    }

    public function test_combo_multiplier_2_doubles_points(): void
    {
        $result = $this->engine->calculatePoints('MAR', 'MARTES', 2);

        $this->assertEquals(2, $result->comboMultiplier);
        $this->assertEquals(6, $result->totalPoints); // 3 * 2
    }

    public function test_combo_multiplier_capped_at_5(): void
    {
        $result = $this->engine->calculatePoints('MAR', 'MARTES', 7);

        $this->assertEquals(5, $result->comboMultiplier);
        $this->assertEquals(15, $result->totalPoints); // 3 * 5
    }

    public function test_combo_multiplier_exactly_5_is_allowed(): void
    {
        $result = $this->engine->calculatePoints('MARTE', 'MARTES', 5);

        $this->assertEquals(5, $result->comboMultiplier);
        $this->assertEquals(40, $result->totalPoints); // 8 * 5
    }

    // ─── Perfect Word Detection ───

    public function test_perfect_word_detected_when_all_letters_used(): void
    {
        $result = $this->engine->calculatePoints('MARTES', 'MARTES', 1);

        $this->assertTrue($result->isPerfectWord);
    }

    public function test_perfect_word_awards_10_bonus_before_combo(): void
    {
        // MARTES = 6 letters = 12 points + 10 bonus = 22
        $result = $this->engine->calculatePoints('MARTES', 'MARTES', 1);

        $this->assertEquals(12, $result->points);
        $this->assertEquals(22, $result->totalPoints); // (12 + 10) * 1
    }

    public function test_perfect_word_bonus_multiplied_by_combo(): void
    {
        // MARTES = 12 base + 10 bonus = 22, × 3 combo = 66
        $result = $this->engine->calculatePoints('MARTES', 'MARTES', 3);

        $this->assertEquals(66, $result->totalPoints); // (12 + 10) * 3
    }

    public function test_not_perfect_word_when_not_all_letters_used(): void
    {
        $result = $this->engine->calculatePoints('MARTE', 'MARTES', 1);

        $this->assertFalse($result->isPerfectWord);
    }

    public function test_perfect_word_respects_letter_order_independence(): void
    {
        // "STREAM" uses same letters as "MARTES" in different order
        $result = $this->engine->calculatePoints('STREAM', 'MARTES', 1);

        $this->assertTrue($result->isPerfectWord);
    }

    public function test_perfect_word_handles_duplicate_letters(): void
    {
        // Letter set "AABBC" - word "CABBA" uses all including duplicates
        $result = $this->engine->calculatePoints('CABBA', 'AABBC', 1);

        $this->assertTrue($result->isPerfectWord);
    }

    public function test_not_perfect_word_when_word_shorter_than_letter_set(): void
    {
        $result = $this->engine->calculatePoints('MAR', 'MARTES', 1);

        $this->assertFalse($result->isPerfectWord);
    }

    // ─── Long Word Detection ───

    public function test_long_word_true_for_6_letters(): void
    {
        $result = $this->engine->calculatePoints('MARTES', 'MARTESA', 1);

        $this->assertTrue($result->isLongWord);
    }

    public function test_long_word_true_for_7_letters(): void
    {
        $result = $this->engine->calculatePoints('MARTESA', 'MARTESAB', 1);

        $this->assertTrue($result->isLongWord);
    }

    public function test_long_word_false_for_5_letters(): void
    {
        $result = $this->engine->calculatePoints('MARTE', 'MARTES', 1);

        $this->assertFalse($result->isLongWord);
    }

    // ─── Total Points Calculation ───

    public function test_total_points_formula_base_times_combo(): void
    {
        // 5 letters = 8 points, combo 3 → 8 * 3 = 24
        $result = $this->engine->calculatePoints('MARTE', 'MARTES', 3);

        $this->assertEquals(24, $result->totalPoints);
    }

    // ─── getComboForPlayer ───

    public function test_get_combo_returns_1_for_no_submissions(): void
    {
        $game = Game::create([
            'code' => 'ABC123',
            'status' => 'playing',
            'mode' => 'arena',
            'max_players' => 10,
            'round_duration_seconds' => 60,
        ]);

        $player = GamePlayer::create([
            'game_id' => $game->id,
            'nickname' => 'Player1',
            'is_host' => true,
            'is_bot' => false,
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
            'letters' => 'MARTES',
            'base_word' => 'MARTES',
            'duration_seconds' => 60,
            'status' => 'playing',
            'total_valid_words' => 0,
            'started_at' => now(),
        ]);

        $combo = $this->engine->getComboForPlayer($round, $player);

        $this->assertEquals(1, $combo);
    }

    public function test_get_combo_increments_for_consecutive_valid_words(): void
    {
        [$round, $player] = $this->createRoundAndPlayer();

        // 3 consecutive valid words
        $this->createSubmission($round, $player, 'MAR', true, '2024-01-01 12:00:01');
        $this->createSubmission($round, $player, 'ARTE', true, '2024-01-01 12:00:02');
        $this->createSubmission($round, $player, 'MARTE', true, '2024-01-01 12:00:03');

        $combo = $this->engine->getComboForPlayer($round, $player);

        // Next combo should be 4 (3 consecutive + 1)
        $this->assertEquals(4, $combo);
    }

    public function test_get_combo_resets_after_invalid_word(): void
    {
        [$round, $player] = $this->createRoundAndPlayer();

        // Valid, valid, invalid, valid
        $this->createSubmission($round, $player, 'MAR', true, '2024-01-01 12:00:01');
        $this->createSubmission($round, $player, 'ARTE', true, '2024-01-01 12:00:02');
        $this->createSubmission($round, $player, 'XYZ', false, '2024-01-01 12:00:03');
        $this->createSubmission($round, $player, 'TE', true, '2024-01-01 12:00:04');

        $combo = $this->engine->getComboForPlayer($round, $player);

        // Only the last valid word counts (1 consecutive), so next combo = 2
        $this->assertEquals(2, $combo);
    }

    public function test_get_combo_capped_at_5(): void
    {
        [$round, $player] = $this->createRoundAndPlayer();

        // 6 consecutive valid words
        for ($i = 1; $i <= 6; $i++) {
            $this->createSubmission($round, $player, "W{$i}", true, "2024-01-01 12:00:0{$i}");
        }

        $combo = $this->engine->getComboForPlayer($round, $player);

        $this->assertEquals(5, $combo);
    }

    public function test_get_combo_returns_1_when_last_submission_is_invalid(): void
    {
        [$round, $player] = $this->createRoundAndPlayer();

        // Valid then invalid
        $this->createSubmission($round, $player, 'MAR', true, '2024-01-01 12:00:01');
        $this->createSubmission($round, $player, 'XYZ', false, '2024-01-01 12:00:02');

        $combo = $this->engine->getComboForPlayer($round, $player);

        // After invalid, combo resets → next combo = 1
        $this->assertEquals(1, $combo);
    }

    // ─── Atomic Score Update ───

    public function test_update_player_score_increments_atomically(): void
    {
        $game = Game::create([
            'code' => 'TST123',
            'status' => 'playing',
            'mode' => 'arena',
            'max_players' => 10,
            'round_duration_seconds' => 60,
        ]);

        $player = GamePlayer::create([
            'game_id' => $game->id,
            'nickname' => 'Scorer',
            'is_host' => false,
            'is_bot' => false,
            'is_connected' => true,
            'total_score' => 10,
            'total_words' => 0,
            'best_combo' => 0,
            'longest_word_length' => 0,
            'joined_at' => now(),
        ]);

        $this->engine->updatePlayerScore($player, 15);

        $player->refresh();
        $this->assertEquals(25, $player->total_score);
    }

    // ─── Helpers ───

    private function createRoundAndPlayer(): array
    {
        $game = Game::create([
            'code' => 'GME' . rand(100, 999),
            'status' => 'playing',
            'mode' => 'arena',
            'max_players' => 10,
            'round_duration_seconds' => 60,
        ]);

        $player = GamePlayer::create([
            'game_id' => $game->id,
            'nickname' => 'TestPlayer',
            'is_host' => true,
            'is_bot' => false,
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
            'letters' => 'MARTES',
            'base_word' => 'MARTES',
            'duration_seconds' => 60,
            'status' => 'playing',
            'total_valid_words' => 0,
            'started_at' => now(),
        ]);

        return [$round, $player];
    }

    private function createSubmission(
        GameRound $round,
        GamePlayer $player,
        string $word,
        bool $isValid,
        string $submittedAt
    ): SubmittedWord {
        return SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player->id,
            'word' => strtoupper($word),
            'is_valid' => $isValid,
            'rejection_reason' => $isValid ? null : 'not_in_dictionary',
            'points' => $isValid ? 3 : 0,
            'combo_multiplier' => 1,
            'total_points' => $isValid ? 3 : 0,
            'is_perfect_word' => false,
            'is_rare_word' => false,
            'is_long_word' => false,
            'submitted_at' => $submittedAt,
        ]);
    }
}
