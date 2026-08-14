# Technical Design Document

## Overview

This document describes the technical architecture for the Batalha de Palavras MVP — a real-time competitive word game with Arena mode (TV as display, phones as controllers). The system is built on Laravel 12 + PHP 8.2, PostgreSQL, React + Vite (frontend), and Laravel Reverb (WebSockets).

The existing project has the complete data layer (models, migrations, seeders). This design covers the application layer: Services, Controllers, Events, Broadcasting, Bot logic, and the React frontend.

## Architecture

### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND (React + Vite)                   │
├──────────────────────────┬──────────────────────────────────────┤
│     Arena Screen         │         Player Screen                 │
│  (TV/Projector)          │        (Mobile Phone)                 │
│  - WebSocket listener    │  - WebSocket listener                 │
│  - QR Code display       │  - HTTP POST word submissions         │
│  - Live scoreboard       │  - Word history                       │
│  - Timer                 │  - Score display                      │
└────────────┬─────────────┴──────────────┬───────────────────────┘
             │ WebSocket (Echo)            │ REST API + WebSocket
             │                            │
┌────────────▼────────────────────────────▼───────────────────────┐
│                     BACKEND (Laravel 12)                          │
├─────────────────────────────────────────────────────────────────┤
│  Controllers                                                     │
│  ├── GameController          (create, join, state)               │
│  ├── RoundController         (start, status)                     │
│  ├── WordSubmissionController (submit word)                      │
│  └── GameHistoryController   (list past games)                   │
├─────────────────────────────────────────────────────────────────┤
│  Services                                                        │
│  ├── GameService             (game lifecycle)                    │
│  ├── LetterSetGenerator      (base word → letter set)            │
│  ├── WordValidator           (validation pipeline)               │
│  ├── ScoringEngine           (points + combos)                   │
│  ├── RoundManager            (start, end, timer)                 │
│  └── BotPlayerService        (AI opponent logic)                 │
├─────────────────────────────────────────────────────────────────┤
│  Events (Broadcasting via Laravel Reverb)                        │
│  ├── PlayerJoined                                                │
│  ├── RoundStarted                                                │
│  ├── WordSubmitted                                               │
│  ├── ScoreUpdated                                                │
│  ├── RoundEnded                                                  │
│  └── PlayerDisconnected                                          │
├─────────────────────────────────────────────────────────────────┤
│  Jobs                                                            │
│  ├── EndRoundJob             (scheduled timer expiration)        │
│  └── BotPlayJob              (bot word submissions)              │
└─────────────────────────────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────────────────┐
│                     PostgreSQL Database                           │
│  (games, game_players, game_rounds, submitted_words,             │
│   game_scores, dictionary_words, rankings, achievements)         │
└─────────────────────────────────────────────────────────────────┘
```

### Component Interaction Flow (Word Submission)

```
Player Phone                  Laravel Backend                    All Clients
     │                              │                               │
     │  POST /api/games/{code}/     │                               │
     │       submit-word            │                               │
     │─────────────────────────────>│                               │
     │                              │ WordValidator                  │
     │                              │  ├── Check round active        │
     │                              │  ├── Check player connected    │
     │                              │  ├── Check word length         │
     │                              │  ├── Check letters available   │
     │                              │  ├── Check dictionary          │
     │                              │  └── Check duplicates          │
     │                              │                               │
     │                              │ ScoringEngine                  │
     │                              │  ├── Calculate base points     │
     │                              │  ├── Apply combo multiplier    │
     │                              │  ├── Check perfect word        │
     │                              │  └── Update total_score        │
     │                              │                               │
     │  200 OK {points, combo,      │                               │
     │          is_valid, ...}      │                               │
     │<─────────────────────────────│                               │
     │                              │ Broadcast WordSubmitted        │
     │                              │──────────────────────────────>│
     │                              │ Broadcast ScoreUpdated         │
     │                              │──────────────────────────────>│
     │                              │                               │
```

## Components and Interfaces

### 1. Backend Services

#### 1.1 GameService (`App\Services\GameService`)

Responsibilities: Game creation, player join, game state management.

```php
class GameService
{
    public function createGame(string $mode = 'arena', ?int $hostUserId = null): Game
    public function joinGame(string $code, string $nickname, ?int $userId = null): GamePlayer
    public function getGameState(string $code): array
    public function playAgain(Game $game): GameRound
}
```

Key behaviors:
- `createGame`: Generates 6-char code (A-Z excl. O/I/L, 2-9), registers host, returns game with QR URL
- `joinGame`: Validates nickname (2-30 chars, alphanumeric + space/underscore), checks game joinable, broadcasts PlayerJoined event
- `playAgain`: Creates new round, resets player round scores, generates new letter set

#### 1.2 LetterSetGenerator (`App\Services\LetterSetGenerator`)

Responsibilities: Selecting base word, generating playable letter sets.

```php
class LetterSetGenerator
{
    public function generate(): LetterSetResult
    public function countValidWords(string $letters): int
    public function getValidWordsForLetters(string $letters): Collection
}
```

Algorithm:
1. Query `dictionary_words` WHERE `is_valid = true AND is_inappropriate = false AND length BETWEEN 5 AND 12`
2. Select random base word
3. Extract letters (uppercase string, e.g., "MARTES")
4. Count all valid words formable from those letters (query dictionary, check letter availability in PHP)
5. If count < 10, retry (up to 10 attempts)
6. Return `LetterSetResult` with letters, base_word, and valid_word_count

Performance consideration: Cache a precomputed mapping of base words → valid word counts to avoid expensive queries on every round start.

#### 1.3 WordValidator (`App\Services\WordValidator`)

Responsibilities: Full validation pipeline for submitted words.

```php
class WordValidator
{
    public function validate(GameRound $round, GamePlayer $player, string $word): ValidationResult
}
```

Validation order (per Requirement 6, AC8):
1. **Time/round status**: Round must be "playing" and elapsed < duration_seconds
2. **Player participation**: Player must be registered and `is_connected = true`
3. **Min/max length**: Word length ≥ 2 and ≤ letter_set length
4. **Letter availability**: Each letter used must exist in Letter_Set respecting quantities
5. **Dictionary lookup**: Word exists in `dictionary_words` with `is_valid = true` and `is_inappropriate = false`
6. **Duplicate check**: Player hasn't submitted same word in same round

Returns `ValidationResult`:
```php
class ValidationResult
{
    public bool $isValid;
    public ?string $rejectionReason; // time_expired, invalid_letters, not_in_dictionary, duplicate
    public int $points;
    public int $comboMultiplier;
    public int $totalPoints;
    public bool $isPerfectWord;
    public bool $isLongWord;
}
```

Letter availability check algorithm:
```php
function hasValidLetters(string $word, string $letterSet): bool
{
    $available = array_count_values(str_split(strtoupper($letterSet)));
    $needed = array_count_values(str_split(strtoupper($word)));
    foreach ($needed as $letter => $count) {
        if (($available[$letter] ?? 0) < $count) return false;
    }
    return true;
}
```

#### 1.4 ScoringEngine (`App\Services\ScoringEngine`)

Responsibilities: Point calculation, combo tracking, perfect word detection.

```php
class ScoringEngine
{
    public function calculatePoints(string $word, string $letterSet, int $currentCombo): ScoreResult
    public function getComboForPlayer(GameRound $round, GamePlayer $player): int
}
```

Scoring table:
```php
const POINTS_BY_LENGTH = [
    2 => 1, 3 => 3, 4 => 5, 5 => 8,
    6 => 12, 7 => 17,
];
const POINTS_8_PLUS = 25;
const PERFECT_WORD_BONUS = 10;
const MAX_COMBO = 5;
```

Combo logic:
- Combo starts at 1
- Each consecutive valid word increments combo (max 5)
- Any invalid submission resets combo to 1
- Total points = (base_points + perfect_bonus) × combo_multiplier

#### 1.5 RoundManager (`App\Services\RoundManager`)

Responsibilities: Round lifecycle (create, start, end), timer scheduling.

```php
class RoundManager
{
    public function createRound(Game $game): GameRound
    public function startRound(GameRound $round): void
    public function endRound(GameRound $round): void
    public function calculateFinalPositions(GameRound $round): void
}
```

Key behaviors:
- `startRound`: Sets status to "playing", records `started_at`, dispatches `EndRoundJob` delayed by 60 seconds, broadcasts RoundStarted event
- `endRound`: Sets status to "finished", records `finished_at`, calculates positions, creates GameScore records, broadcasts RoundEnded event
- Timer: Uses Laravel's delayed job dispatch (`EndRoundJob::dispatch($round)->delay(60)`) for server-authoritative timing

#### 1.6 BotPlayerService (`App\Services\BotPlayerService`)

Responsibilities: AI opponent behavior during rounds.

```php
class BotPlayerService
{
    public function startBot(GameRound $round, GamePlayer $bot): void
    public function selectNextWord(GameRound $round, GamePlayer $bot): ?string
}
```

Bot behavior:
- On round start, dispatch `BotPlayJob` with random delay 3-8 seconds
- Each `BotPlayJob` submits one word, then dispatches next `BotPlayJob` (if round still active and < 12 words submitted)
- Word selection: 70% words of length 2-5, 30% words of length 6+
- Never submits more than 50% of possible valid words
- Uses same WordValidator pipeline as human players

### 2. Controllers (API)

#### 2.1 Route Structure

```
POST   /api/games                     → GameController@store       (create game)
GET    /api/games/{code}              → GameController@show        (game state)
POST   /api/games/{code}/join         → GameController@join        (join game)
POST   /api/games/{code}/start-round  → RoundController@start      (host starts round)
GET    /api/games/{code}/round        → RoundController@show       (current round state)
POST   /api/games/{code}/submit-word  → WordSubmissionController@store (submit word)
POST   /api/games/{code}/play-again   → GameController@playAgain   (create new round)
GET    /api/games/history             → GameHistoryController@index (auth'd user history)
```

All game-related endpoints use the Game_Code as identifier (not game_id) for simplicity.

#### 2.2 WordSubmissionController

```php
class WordSubmissionController extends Controller
{
    public function store(Request $request, string $code): JsonResponse
    {
        // Rate limit: 1 submission per second per player
        // Validate request (word required, string, max 15 chars)
        // Find game by code, find player by session/token
        // Get current active round
        // Call WordValidator->validate()
        // If valid: call ScoringEngine, save SubmittedWord, update GamePlayer score
        // Broadcast WordSubmitted + ScoreUpdated events
        // Return result to player
    }
}
```

#### 2.3 Player Identification

For MVP (no auth required for arena players):
- Host can optionally be authenticated (Laravel session)
- Arena players identified by a session token (stored in cookie/localStorage)
- On join, server returns a `player_token` that must be sent with every submission
- Token maps to `game_players.id`

### 3. Broadcasting (Events)

#### 3.1 Channel Strategy

```php
// Game-specific presence channel
Broadcast::channel('game.{code}', function ($user, $code) {
    // Allow any client with valid player_token for this game
    return true;
});
```

Channel name: `game.{code}` (e.g., `game.ABC123`)

#### 3.2 Events

| Event | Channel | Payload |
|-------|---------|---------|
| `PlayerJoined` | `game.{code}` | `{player: {id, nickname, is_host}}` |
| `PlayerDisconnected` | `game.{code}` | `{player_id, nickname}` |
| `RoundStarted` | `game.{code}` | `{round_number, letters, started_at, duration_seconds}` |
| `WordSubmitted` | `game.{code}` | `{player_nickname, word, points, is_valid}` |
| `ScoreUpdated` | `game.{code}` | `{scoreboard: [{nickname, score, position, last_word}]}` |
| `RoundEnded` | `game.{code}` | `{round_number, final_scores, highlights, winner}` |

#### 3.3 Laravel Reverb Setup

Required packages:
- `laravel/reverb` (WebSocket server)
- `laravel-echo` + `pusher-js` (frontend client)

Broadcasting driver: `reverb`

### 4. Frontend Architecture (React)

#### 4.1 App Structure

```
resources/js/
├── app.tsx                  (entry point)
├── echo.ts                  (Laravel Echo setup)
├── api/                     (API client functions)
│   ├── gameApi.ts
│   └── types.ts
├── pages/
│   ├── HomePage.tsx         (create/join game)
│   ├── ArenaScreen.tsx      (TV display)
│   └── PlayerScreen.tsx     (mobile controller)
├── components/
│   ├── arena/
│   │   ├── WaitingRoom.tsx  (QR code, player list)
│   │   ├── GameBoard.tsx    (letters, timer, scoreboard)
│   │   └── EndScreen.tsx    (results, play again)
│   └── player/
│       ├── JoinForm.tsx     (nickname input)
│       ├── WordInput.tsx    (word submission)
│       ├── WordHistory.tsx  (submitted words list)
│       └── WaitingView.tsx  (waiting for round start)
└── hooks/
    ├── useGame.ts           (game state management)
    ├── useWebSocket.ts      (Echo channel subscription)
    └── useTimer.ts          (countdown from server timestamp)
```

#### 4.2 Routing

```
/                  → HomePage (create game or enter code)
/arena/{code}      → ArenaScreen (TV display)
/play/{code}       → PlayerScreen (mobile controller)
```

#### 4.3 State Management

Use React Context + useReducer for game state. No external state library needed for MVP.

```typescript
interface GameState {
  game: Game | null;
  round: Round | null;
  players: Player[];
  scoreboard: ScoreEntry[];
  recentWords: WordSubmission[];
  myWords: WordSubmission[];      // player screen only
  myScore: number;                // player screen only
  myCombo: number;                // player screen only
  timeRemaining: number;
  status: 'loading' | 'waiting' | 'playing' | 'finished';
}
```

#### 4.4 Timer Implementation

Client-side timer derived from server `started_at`:
```typescript
function useTimer(startedAt: string | null, durationSeconds: number) {
  // Calculate remaining = duration - (Date.now() - serverStartedAt)
  // Update every second via setInterval
  // Never allows negative values
}
```

### 5. Jobs

#### 5.1 EndRoundJob

```php
class EndRoundJob implements ShouldQueue
{
    public function __construct(private int $roundId) {}

    public function handle(RoundManager $roundManager): void
    {
        $round = GameRound::find($this->roundId);
        if ($round && $round->status === 'playing') {
            $roundManager->endRound($round);
        }
    }
}
```

Dispatched with 60-second delay when round starts. Ensures round ends even if no client triggers it.

#### 5.2 BotPlayJob

```php
class BotPlayJob implements ShouldQueue
{
    public function __construct(
        private int $roundId,
        private int $botPlayerId,
        private int $wordsSubmitted = 0
    ) {}

    public function handle(BotPlayerService $botService): void
    {
        $round = GameRound::find($this->roundId);
        if (!$round || $round->status !== 'playing' || $this->wordsSubmitted >= 12) {
            return;
        }

        $bot = GamePlayer::find($this->botPlayerId);
        $word = $botService->selectNextWord($round, $bot);

        if ($word) {
            // Submit word through same pipeline as human
            // Dispatch next BotPlayJob with random 3-8 second delay
            self::dispatch($this->roundId, $this->botPlayerId, $this->wordsSubmitted + 1)
                ->delay(now()->addSeconds(rand(3, 8)));
        }
    }
}
```

### 6. Rate Limiting

Rate limit word submissions to 1 per player per second:

```php
// In WordSubmissionController
RateLimiter::for('word-submission', function (Request $request) {
    return Limit::perSecond(1)->by($request->input('player_token'));
});
```

### 7. QR Code Generation

Use `simplesoftwareio/simple-qrcode` package to generate QR codes server-side as SVG/PNG.

QR Code URL format: `{APP_URL}/play/{code}`

### 8. Dictionary Import Strategy

Seeder/command to import Portuguese word list:
- Source: Open-source Portuguese word list (e.g., from LibreOffice dictionaries or NILC/USP corpus)
- Import as artisan command: `php artisan dictionary:import {file}`
- Normalize: uppercase, trim, deduplicate
- Set `length` field automatically
- Flag known inappropriate words
- Expected size: 300,000-500,000 words

## Data Models

The data models are already fully implemented in the existing migrations and Eloquent models. No schema changes needed for MVP. Key existing tables:

- `games` — code (6 chars), status, mode, max_players, round_duration_seconds
- `game_players` — nickname, is_host, is_bot, is_connected, total_score, best_combo
- `game_rounds` — letters, base_word, duration_seconds, status, started_at/finished_at
- `submitted_words` — word, is_valid, rejection_reason, points, combo_multiplier, total_points, is_perfect_word
- `game_scores` — round-level aggregated stats per player
- `dictionary_words` — word, length, frequency, is_valid, is_inappropriate

## API Specifications

### POST /api/games
**Request:**
```json
{ "mode": "arena" }
```
**Response (201):**
```json
{
  "code": "ABC123",
  "qr_url": "https://app.url/play/ABC123",
  "status": "waiting",
  "mode": "arena",
  "max_players": 10
}
```

### POST /api/games/{code}/join
**Request:**
```json
{ "nickname": "Geanne" }
```
**Response (200):**
```json
{
  "player_token": "uuid-token",
  "player_id": 1,
  "nickname": "Geanne",
  "game_code": "ABC123"
}
```
**Error (422):**
```json
{ "error": "nickname_taken", "message": "Este apelido já está em uso." }
```

### POST /api/games/{code}/start-round
**Request:** (requires host player_token)
```json
{ "player_token": "host-uuid-token" }
```
**Response (200):**
```json
{
  "round_number": 1,
  "letters": "MARTES",
  "started_at": "2024-01-01T12:00:00Z",
  "duration_seconds": 60
}
```

### POST /api/games/{code}/submit-word
**Request:**
```json
{ "player_token": "uuid-token", "word": "MORTE" }
```
**Response (200):**
```json
{
  "word": "MORTE",
  "is_valid": true,
  "points": 8,
  "combo_multiplier": 2,
  "total_points": 16,
  "is_perfect_word": false,
  "is_long_word": false,
  "player_total_score": 45,
  "rejection_reason": null
}
```

### GET /api/games/{code}
**Response (200):**
```json
{
  "code": "ABC123",
  "status": "playing",
  "mode": "arena",
  "players": [
    { "nickname": "Geanne", "score": 45, "is_host": true, "is_connected": true }
  ],
  "current_round": {
    "round_number": 1,
    "letters": "MARTES",
    "started_at": "2024-01-01T12:00:00Z",
    "duration_seconds": 60,
    "status": "playing"
  }
}
```

### GET /api/games/history
**Response (200):** (requires authentication)
```json
{
  "data": [
    {
      "code": "ABC123",
      "mode": "arena",
      "played_at": "2024-01-01T12:01:00Z",
      "my_score": 87,
      "my_position": 1,
      "total_players": 3,
      "my_words": 12,
      "longest_word": "MARTES"
    }
  ],
  "meta": { "current_page": 1, "total": 15, "per_page": 20 }
}
```

## Security Considerations

1. **Server-authoritative scoring**: All point calculations happen backend-only. Frontend displays but never computes.
2. **Player token**: UUID-based token (stored in `game_players` or cache) authenticates submissions. Prevents spoofing.
3. **Rate limiting**: 1 word/second/player prevents spam and automated tools.
4. **Input sanitization**: Words are uppercased, trimmed, validated against max 15 chars before processing.
5. **Game isolation**: WebSocket channels scoped per game code; API endpoints validate player belongs to game.
6. **Time validation**: Server clock is authoritative; submissions after round end are rejected regardless of client timing.

## Performance Considerations

1. **Dictionary lookups**: Index on `word` column (unique). Single query per validation.
2. **Letter validation**: Done in PHP memory (array_count_values) — no DB query needed.
3. **Duplicate check**: Compound index on `(game_round_id, game_player_id, word)` for fast lookups.
4. **WebSocket broadcast**: Laravel Reverb handles event fan-out efficiently. Each game is a separate channel.
5. **Bot words**: Precompute valid words for the letter set once when round starts; cache in memory for bot selection.
6. **Score updates**: Atomic increment via `$player->increment('total_score', $totalPoints)` to prevent race conditions.

## Testing Strategy

1. **Unit tests**: WordValidator, ScoringEngine, LetterSetGenerator (pure logic, mockable)
2. **Feature tests**: API endpoints with database (create game, join, submit word flow)
3. **Integration tests**: Full round lifecycle with WebSocket assertions
4. **Browser tests**: Arena/Player screen rendering (optional for MVP, manual testing sufficient)

## Dependencies to Install

### Backend (Composer)
```bash
composer require laravel/reverb           # WebSocket server
composer require simplesoftwareio/simple-qrcode  # QR Code generation
composer require laravel/sanctum          # API token auth (for game history)
```

### Frontend (NPM)
```bash
npm install react react-dom @types/react @types/react-dom
npm install react-router-dom
npm install laravel-echo pusher-js
npm install @vitejs/plugin-react
npm install qrcode.react                  # QR Code React component
```

## Correctness Properties

### Property 1: Score Integrity
A player's `total_score` equals the sum of all `total_points` from their valid `submitted_words` in the current game. The backend is the sole authority.

**Validates: Requirements 7.2, 7.3**

### Property 2: Letter Set Fairness
All players in the same round receive identical Letter_Set. The Letter_Set is immutable once the round starts.

**Validates: Requirements 3.5, 3.6**

### Property 3: Timing Authority
The server's `started_at` timestamp and `duration_seconds` are authoritative. No client-side clock can override round boundaries.

**Validates: Requirements 4.1, 4.3**

### Property 4: Combo Consistency
A player's current combo equals the count of their consecutive valid submissions since the last invalid one (or round start). Maximum 5.

**Validates: Requirements 7.5, 7.6**

### Property 5: Duplicate Prevention
A player cannot score points for the same word twice in the same round. The compound index `(game_round_id, game_player_id, word)` enforces this at DB level.

**Validates: Requirements 6.3**

### Property 6: Game Code Uniqueness
No two active games (waiting/playing) share the same code. The unique constraint and retry logic guarantee this.

**Validates: Requirements 1.1, 1.4**

### Property 7: Round Finality
Once a round status is "finished", no further word submissions are accepted. The EndRoundJob ensures the round ends even without client interaction.

**Validates: Requirements 4.3, 6.4**

## Error Handling

1. **Game creation failure (code collision after 5 retries)**: Return 503 Service Unavailable with message explaining temporary failure. Frontend shows retry option.
2. **Letter set generation failure (10 failed attempts)**: Use the last generated set and proceed. Log warning for monitoring. Game continues.
3. **Word submission during inactive round**: Return 422 with `rejection_reason: "time_expired"`. Player sees feedback immediately.
4. **Player token invalid/missing**: Return 401 Unauthorized. Frontend redirects to join screen.
5. **Rate limit exceeded (>1 word/second)**: Return 429 Too Many Requests. Player sees "aguarde" message briefly.
6. **WebSocket disconnection**: Client auto-reconnects within 30 seconds using Echo's reconnection logic. Game state is recoverable via `GET /api/games/{code}`.
7. **Database persistence error on score update**: Retry once. If both attempts fail, return 500 with error. Player's word is not scored. Logged for investigation.
8. **Bot job fails**: Bot stops submitting for remainder of round. Game continues normally for human players. Non-blocking failure.
9. **Dictionary lookup miss (word not found)**: Return valid response with `is_valid: false` and `rejection_reason: "not_in_dictionary"`. Not an application error.
