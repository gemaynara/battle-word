<?php

namespace Tests\Unit;

use App\Models\DictionaryWord;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\SubmittedWord;
use App\Services\ValidationResult;
use App\Services\WordValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordValidatorTest extends TestCase
{
    use RefreshDatabase;

    private WordValidator $validator;
    private Game $game;
    private GameRound $round;
    private GamePlayer $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new WordValidator();

        $this->game = Game::create([
            'code' => 'TEST01',
            'status' => 'playing',
            'mode' => 'arena',
            'max_players' => 10,
            'round_duration_seconds' => 60,
        ]);

        $this->round = GameRound::create([
            'game_id' => $this->game->id,
            'round_number' => 1,
            'letters' => 'MARTES',
            'base_word' => 'MARTES',
            'duration_seconds' => 60,
            'status' => 'playing',
            'started_at' => now(),
        ]);

        $this->player = GamePlayer::create([
            'game_id' => $this->game->id,
            'nickname' => 'TestPlayer',
            'is_host' => false,
            'is_bot' => false,
            'is_connected' => true,
            'total_score' => 0,
            'total_words' => 0,
            'joined_at' => now(),
        ]);

        // Seed a valid dictionary word
        DictionaryWord::create([
            'word' => 'MESA',
            'length' => 4,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        DictionaryWord::create([
            'word' => 'MATES',
            'length' => 5,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);
    }

    public function test_valid_word_passes_all_checks(): void
    {
        $result = $this->validator->validate($this->round, $this->player, 'MESA');

        $this->assertTrue($result->isValid);
        $this->assertNull($result->rejectionReason);
    }

    public function test_normalizes_word_to_uppercase(): void
    {
        $result = $this->validator->validate($this->round, $this->player, 'mesa');

        $this->assertTrue($result->isValid);
    }

    // --- 1. Time/round status checks ---

    public function test_rejects_when_round_is_not_playing(): void
    {
        $this->round->update(['status' => 'finished']);

        $result = $this->validator->validate($this->round->fresh(), $this->player, 'MESA');

        $this->assertFalse($result->isValid);
        $this->assertEquals('time_expired', $result->rejectionReason);
    }

    public function test_rejects_when_round_time_has_expired(): void
    {
        $this->round->update(['started_at' => now()->subSeconds(120)]);

        $result = $this->validator->validate($this->round->fresh(), $this->player, 'MESA');

        $this->assertFalse($result->isValid);
        $this->assertEquals('time_expired', $result->rejectionReason);
    }

    // --- 2. Player participation checks ---

    public function test_rejects_when_player_is_disconnected(): void
    {
        $this->player->update(['is_connected' => false]);

        $result = $this->validator->validate($this->round, $this->player->fresh(), 'MESA');

        $this->assertFalse($result->isValid);
        $this->assertEquals('invalid_letters', $result->rejectionReason);
    }

    // --- 3. Min/max length checks ---

    public function test_rejects_word_shorter_than_2_characters(): void
    {
        DictionaryWord::create([
            'word' => 'A',
            'length' => 1,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'A');

        $this->assertFalse($result->isValid);
        $this->assertEquals('invalid_letters', $result->rejectionReason);
    }

    public function test_rejects_word_longer_than_letter_set(): void
    {
        // Letter set is 'MARTES' (6 chars), so a 7-char word should fail
        DictionaryWord::create([
            'word' => 'MARTESS',
            'length' => 7,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'MARTESS');

        $this->assertFalse($result->isValid);
        $this->assertEquals('invalid_letters', $result->rejectionReason);
    }

    // --- 4. Letter availability checks ---

    public function test_rejects_word_with_unavailable_letters(): void
    {
        DictionaryWord::create([
            'word' => 'ZONA',
            'length' => 4,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'ZONA');

        $this->assertFalse($result->isValid);
        $this->assertEquals('invalid_letters', $result->rejectionReason);
    }

    public function test_rejects_word_exceeding_letter_quantity(): void
    {
        // 'MARTES' has only one 'M', so 'MAMA' would need 2 M's and 2 A's
        DictionaryWord::create([
            'word' => 'MAMA',
            'length' => 4,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'MAMA');

        $this->assertFalse($result->isValid);
        $this->assertEquals('invalid_letters', $result->rejectionReason);
    }

    // --- 5. Dictionary lookup checks ---

    public function test_rejects_word_not_in_dictionary(): void
    {
        $result = $this->validator->validate($this->round, $this->player, 'STER');

        $this->assertFalse($result->isValid);
        $this->assertEquals('not_in_dictionary', $result->rejectionReason);
    }

    public function test_rejects_invalid_dictionary_word(): void
    {
        DictionaryWord::create([
            'word' => 'MARTE',
            'length' => 5,
            'is_valid' => false,
            'is_inappropriate' => false,
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'MARTE');

        $this->assertFalse($result->isValid);
        $this->assertEquals('not_in_dictionary', $result->rejectionReason);
    }

    public function test_rejects_inappropriate_dictionary_word(): void
    {
        DictionaryWord::create([
            'word' => 'RASTE',
            'length' => 5,
            'is_valid' => true,
            'is_inappropriate' => true,
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'RASTE');

        $this->assertFalse($result->isValid);
        $this->assertEquals('not_in_dictionary', $result->rejectionReason);
    }

    // --- 6. Duplicate checks ---

    public function test_rejects_duplicate_word_from_same_player_in_same_round(): void
    {
        SubmittedWord::create([
            'game_round_id' => $this->round->id,
            'game_player_id' => $this->player->id,
            'word' => 'MESA',
            'is_valid' => true,
            'points' => 5,
            'combo_multiplier' => 1,
            'total_points' => 5,
            'submitted_at' => now(),
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'MESA');

        $this->assertFalse($result->isValid);
        $this->assertEquals('duplicate', $result->rejectionReason);
    }

    public function test_allows_same_word_from_different_player(): void
    {
        $otherPlayer = GamePlayer::create([
            'game_id' => $this->game->id,
            'nickname' => 'OtherPlayer',
            'is_host' => false,
            'is_bot' => false,
            'is_connected' => true,
            'total_score' => 0,
            'total_words' => 0,
            'joined_at' => now(),
        ]);

        SubmittedWord::create([
            'game_round_id' => $this->round->id,
            'game_player_id' => $otherPlayer->id,
            'word' => 'MESA',
            'is_valid' => true,
            'points' => 5,
            'combo_multiplier' => 1,
            'total_points' => 5,
            'submitted_at' => now(),
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'MESA');

        $this->assertTrue($result->isValid);
    }

    // --- Priority order tests ---

    public function test_time_expired_takes_priority_over_invalid_letters(): void
    {
        $this->round->update(['status' => 'finished']);

        // 'ZZZ' would fail both length and letter checks, but time_expired should be first
        $result = $this->validator->validate($this->round->fresh(), $this->player, 'ZZZ');

        $this->assertFalse($result->isValid);
        $this->assertEquals('time_expired', $result->rejectionReason);
    }

    public function test_player_disconnected_takes_priority_over_invalid_word(): void
    {
        $this->player->update(['is_connected' => false]);

        $result = $this->validator->validate($this->round, $this->player->fresh(), 'ZZZ');

        $this->assertFalse($result->isValid);
        $this->assertEquals('invalid_letters', $result->rejectionReason);
    }

    public function test_length_check_takes_priority_over_dictionary_lookup(): void
    {
        // Single char word fails length before dictionary
        $result = $this->validator->validate($this->round, $this->player, 'M');

        $this->assertFalse($result->isValid);
        $this->assertEquals('invalid_letters', $result->rejectionReason);
    }

    public function test_letter_availability_takes_priority_over_dictionary_lookup(): void
    {
        // 'ZO' uses letters not in 'MARTES', fails letter check before dictionary
        $result = $this->validator->validate($this->round, $this->player, 'ZO');

        $this->assertFalse($result->isValid);
        $this->assertEquals('invalid_letters', $result->rejectionReason);
    }

    public function test_dictionary_check_takes_priority_over_duplicate(): void
    {
        // Submit a non-dictionary word that's already been submitted
        SubmittedWord::create([
            'game_round_id' => $this->round->id,
            'game_player_id' => $this->player->id,
            'word' => 'STER',
            'is_valid' => false,
            'rejection_reason' => 'not_in_dictionary',
            'points' => 0,
            'combo_multiplier' => 1,
            'total_points' => 0,
            'submitted_at' => now(),
        ]);

        $result = $this->validator->validate($this->round, $this->player, 'STER');

        $this->assertFalse($result->isValid);
        $this->assertEquals('not_in_dictionary', $result->rejectionReason);
    }
}
