<?php

namespace Tests\Unit;

use App\Jobs\EndRoundJob;
use App\Models\DictionaryWord;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameRound;
use App\Models\GameScore;
use App\Models\SubmittedWord;
use App\Services\LetterSetGenerator;
use App\Services\LetterSetResult;
use App\Services\RoundManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RoundManagerTest extends TestCase
{
    use RefreshDatabase;

    private RoundManager $roundManager;

    protected function setUp(): void
    {
        parent::setUp();

        $mockGenerator = $this->createMock(LetterSetGenerator::class);
        $mockGenerator->method('generate')->willReturn(
            new LetterSetResult(
                letters: 'MARTES',
                baseWord: 'MARTES',
                validWordCount: 15,
            )
        );

        $this->roundManager = new RoundManager($mockGenerator);
    }

    // --- createRound tests ---

    public function test_create_round_returns_game_round(): void
    {
        $game = $this->createGame();

        $round = $this->roundManager->createRound($game);

        $this->assertInstanceOf(GameRound::class, $round);
        $this->assertEquals($game->id, $round->game_id);
    }

    public function test_create_round_sets_status_to_waiting(): void
    {
        $game = $this->createGame();

        $round = $this->roundManager->createRound($game);

        $this->assertEquals('waiting', $round->status);
    }

    public function test_create_round_stores_letters_and_base_word(): void
    {
        $game = $this->createGame();

        $round = $this->roundManager->createRound($game);

        $this->assertEquals('MARTES', $round->letters);
        $this->assertEquals('MARTES', $round->base_word);
        $this->assertEquals(15, $round->total_valid_words);
    }

    public function test_create_round_calculates_next_round_number(): void
    {
        $game = $this->createGame();

        $round1 = $this->roundManager->createRound($game);
        $round2 = $this->roundManager->createRound($game);

        $this->assertEquals(1, $round1->round_number);
        $this->assertEquals(2, $round2->round_number);
    }

    public function test_create_round_uses_game_duration_seconds(): void
    {
        $game = $this->createGame(['round_duration_seconds' => 90]);

        $round = $this->roundManager->createRound($game);

        $this->assertEquals(90, $round->duration_seconds);
    }

    // --- startRound tests ---

    public function test_start_round_sets_status_to_playing(): void
    {
        Queue::fake();
        $round = $this->createWaitingRound();

        $this->roundManager->startRound($round);

        $round->refresh();
        $this->assertEquals('playing', $round->status);
    }

    public function test_start_round_records_started_at(): void
    {
        Queue::fake();
        $round = $this->createWaitingRound();

        $this->roundManager->startRound($round);

        $round->refresh();
        $this->assertNotNull($round->started_at);
    }

    public function test_start_round_dispatches_end_round_job(): void
    {
        Queue::fake();
        $round = $this->createWaitingRound();

        $this->roundManager->startRound($round);

        Queue::assertPushed(EndRoundJob::class, function ($job) use ($round) {
            // The job should be delayed
            return true;
        });
    }

    public function test_start_round_throws_exception_if_not_waiting(): void
    {
        Queue::fake();
        $round = $this->createWaitingRound();
        $round->update(['status' => 'playing', 'started_at' => now()]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot start round");

        $this->roundManager->startRound($round);
    }

    public function test_start_round_throws_exception_if_finished(): void
    {
        Queue::fake();
        $round = $this->createWaitingRound();
        $round->update(['status' => 'finished', 'finished_at' => now()]);

        $this->expectException(\InvalidArgumentException::class);

        $this->roundManager->startRound($round);
    }

    // --- endRound tests ---

    public function test_end_round_sets_status_to_finished(): void
    {
        $round = $this->createPlayingRound();

        $this->roundManager->endRound($round);

        $round->refresh();
        $this->assertEquals('finished', $round->status);
    }

    public function test_end_round_records_finished_at(): void
    {
        $round = $this->createPlayingRound();

        $this->roundManager->endRound($round);

        $round->refresh();
        $this->assertNotNull($round->finished_at);
    }

    public function test_end_round_does_nothing_if_not_playing(): void
    {
        $round = $this->createWaitingRound();

        $this->roundManager->endRound($round);

        $round->refresh();
        $this->assertEquals('waiting', $round->status);
        $this->assertNull($round->finished_at);
    }

    public function test_end_round_creates_game_score_records(): void
    {
        $game = $this->createGame();
        $player1 = $this->createPlayer($game, 'Player1');
        $player2 = $this->createPlayer($game, 'Player2');
        $round = $this->createPlayingRound($game);

        // Player1 submitted a valid word
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player1->id,
            'word' => 'MAR',
            'is_valid' => true,
            'points' => 3,
            'combo_multiplier' => 1,
            'total_points' => 3,
            'is_perfect_word' => false,
            'submitted_at' => now(),
        ]);

        $this->roundManager->endRound($round);

        $this->assertEquals(2, GameScore::where('game_round_id', $round->id)->count());
    }

    // --- calculateFinalPositions tests ---

    public function test_calculate_positions_ranks_by_score_descending(): void
    {
        $game = $this->createGame();
        $player1 = $this->createPlayer($game, 'Player1');
        $player2 = $this->createPlayer($game, 'Player2');
        $round = $this->createPlayingRound($game);

        // Player1: 3 points
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player1->id,
            'word' => 'MAR',
            'is_valid' => true,
            'points' => 3,
            'combo_multiplier' => 1,
            'total_points' => 3,
            'is_perfect_word' => false,
            'submitted_at' => now(),
        ]);

        // Player2: 8 points
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player2->id,
            'word' => 'MARTE',
            'is_valid' => true,
            'points' => 8,
            'combo_multiplier' => 1,
            'total_points' => 8,
            'is_perfect_word' => false,
            'submitted_at' => now(),
        ]);

        $this->roundManager->calculateFinalPositions($round);

        $score1 = GameScore::where('game_player_id', $player1->id)->first();
        $score2 = GameScore::where('game_player_id', $player2->id)->first();

        $this->assertEquals(2, $score1->position_in_round); // lower score
        $this->assertEquals(1, $score2->position_in_round); // higher score
    }

    public function test_calculate_positions_tiebreaker_fewer_valid_words(): void
    {
        $game = $this->createGame();
        $player1 = $this->createPlayer($game, 'Player1');
        $player2 = $this->createPlayer($game, 'Player2');
        $round = $this->createPlayingRound($game);

        // Player1: 5 points from 1 word (efficient)
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player1->id,
            'word' => 'ARTE',
            'is_valid' => true,
            'points' => 5,
            'combo_multiplier' => 1,
            'total_points' => 5,
            'is_perfect_word' => false,
            'submitted_at' => now(),
        ]);

        // Player2: 5 points from 2 words (less efficient)
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player2->id,
            'word' => 'MA',
            'is_valid' => true,
            'points' => 1,
            'combo_multiplier' => 1,
            'total_points' => 1,
            'is_perfect_word' => false,
            'submitted_at' => now(),
        ]);
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player2->id,
            'word' => 'MARTE',
            'is_valid' => true,
            'points' => 4,
            'combo_multiplier' => 1,
            'total_points' => 4,
            'is_perfect_word' => false,
            'submitted_at' => now(),
        ]);

        $this->roundManager->calculateFinalPositions($round);

        $score1 = GameScore::where('game_player_id', $player1->id)->first();
        $score2 = GameScore::where('game_player_id', $player2->id)->first();

        // Same score (5 each), but player1 used fewer words → higher rank
        $this->assertEquals(1, $score1->position_in_round);
        $this->assertEquals(2, $score2->position_in_round);
    }

    public function test_calculate_positions_tracks_longest_word(): void
    {
        $game = $this->createGame();
        $player = $this->createPlayer($game, 'Player1');
        $round = $this->createPlayingRound($game);

        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player->id,
            'word' => 'MA',
            'is_valid' => true,
            'points' => 1,
            'combo_multiplier' => 1,
            'total_points' => 1,
            'is_perfect_word' => false,
            'submitted_at' => now(),
        ]);
        SubmittedWord::create([
            'game_round_id' => $round->id,
            'game_player_id' => $player->id,
            'word' => 'MARTES',
            'is_valid' => true,
            'points' => 12,
            'combo_multiplier' => 1,
            'total_points' => 12,
            'is_perfect_word' => true,
            'submitted_at' => now(),
        ]);

        $this->roundManager->calculateFinalPositions($round);

        $score = GameScore::where('game_player_id', $player->id)->first();

        $this->assertEquals('MARTES', $score->longest_word);
        $this->assertEquals(6, $score->longest_word_length);
        $this->assertTrue($score->had_perfect_word);
    }

    // --- Helpers ---

    private function createGame(array $attributes = []): Game
    {
        return Game::create(array_merge([
            'code' => 'TST' . rand(100, 999),
            'status' => 'playing',
            'mode' => 'arena',
            'max_players' => 10,
            'total_rounds' => 3,
            'round_duration_seconds' => 60,
        ], $attributes));
    }

    private function createPlayer(Game $game, string $nickname): GamePlayer
    {
        return GamePlayer::create([
            'game_id' => $game->id,
            'nickname' => $nickname,
            'is_host' => false,
            'is_bot' => false,
            'is_connected' => true,
            'total_score' => 0,
            'joined_at' => now(),
        ]);
    }

    private function createWaitingRound(?Game $game = null): GameRound
    {
        $game = $game ?? $this->createGame();

        return GameRound::create([
            'game_id' => $game->id,
            'round_number' => 1,
            'letters' => 'MARTES',
            'base_word' => 'MARTES',
            'duration_seconds' => 60,
            'status' => 'waiting',
            'total_valid_words' => 15,
        ]);
    }

    private function createPlayingRound(?Game $game = null): GameRound
    {
        $game = $game ?? $this->createGame();

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
}
