# Implementation Plan: Word Battle MVP

## Overview

This plan implements the Batalha de Palavras MVP — a real-time competitive word game with Arena mode. Tasks are ordered by dependency: infrastructure first, then core services, then API layer, then frontend, then integration testing.

## Tasks

- [x] 1. Install backend dependencies (Laravel Reverb, simplesoftwareio/simple-qrcode, Laravel Sanctum), configure broadcasting driver as reverb, publish Reverb config, update .env with Reverb keys. Verify `php artisan reverb:start` works.
  - **Requirements:** 8.5
  - **Files:** `composer.json`, `config/broadcasting.php`, `.env`, `.env.example`, `config/reverb.php`

- [x] 2. Install frontend dependencies (React, React Router, Laravel Echo, pusher-js, qrcode.react, @vitejs/plugin-react), configure Vite with React plugin, add TypeScript support (tsconfig.json), create app.tsx entry point and echo.ts client setup.
  - **Requirements:** 13, 14
  - **Files:** `package.json`, `vite.config.ts`, `tsconfig.json`, `resources/js/app.tsx`, `resources/js/echo.ts`

- [x] 3. Create artisan command `dictionary:import` that reads a word list file (one word per line), normalizes to uppercase, calculates length, and upserts into dictionary_words table. Include a dev seeder with ~500 common Portuguese words.
  - **Requirements:** 6.1
  - **Files:** `app/Console/Commands/ImportDictionary.php`, `database/seeders/DictionarySeeder.php`

- [x] 4. Implement LetterSetGenerator service: select random base word (5-12 chars, valid, not inappropriate), extract letters, count valid formable words, retry up to 10 times if count < 10, return LetterSetResult value object.
  - **Requirements:** 3.1, 3.2, 3.3, 3.4, 3.5
  - **Files:** `app/Services/LetterSetGenerator.php`, `app/Services/LetterSetResult.php`

- [x] 5. Implement WordValidator service with validation pipeline in priority order: time/round status → player participation → min/max length → letter availability → dictionary lookup → duplicate check. Return ValidationResult value object with first failing reason.
  - **Requirements:** 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8
  - **Files:** `app/Services/WordValidator.php`, `app/Services/ValidationResult.php`

- [x] 6. Implement ScoringEngine service: point calculation by word length (2=1, 3=3, 4=5, 5=8, 6=12, 7=17, 8+=25), combo tracking (max 5x, resets on invalid), perfect word detection (+10 bonus), atomic score update via increment.
  - **Requirements:** 7.1, 7.2, 7.4, 7.5, 7.6
  - **Files:** `app/Services/ScoringEngine.php`, `app/Services/ScoreResult.php`

- [x] 7. Implement RoundManager service (createRound with LetterSetGenerator, startRound sets playing + dispatches EndRoundJob, endRound finalizes positions + creates GameScore records) and EndRoundJob (calls endRound if round still playing after 60s).
  - **Requirements:** 4.1, 4.3, 4.4, 4.6
  - **Files:** `app/Services/RoundManager.php`, `app/Jobs/EndRoundJob.php`

- [x] 8. Implement GameService: game creation (6-char code generation with retry, QR URL, host registration), player join (nickname validation, uniqueness, game joinable check), getGameState, and playAgain logic.
  - **Requirements:** 1.1, 1.2, 1.3, 1.4, 1.5, 2.2, 2.3, 2.4, 2.5
  - **Files:** `app/Services/GameService.php`

- [x] 9. Create broadcast events (PlayerJoined, PlayerDisconnected, RoundStarted, WordSubmitted, ScoreUpdated, RoundEnded) implementing ShouldBroadcast on channel `game.{code}`. Configure channel authorization in routes/channels.php.
  - **Requirements:** 8.1, 8.2, 8.3, 8.4, 8.5
  - **Files:** `app/Events/PlayerJoined.php`, `app/Events/PlayerDisconnected.php`, `app/Events/RoundStarted.php`, `app/Events/WordSubmitted.php`, `app/Events/ScoreUpdated.php`, `app/Events/RoundEnded.php`, `routes/channels.php`

- [x] 10. Add player_token (UUID) column to game_players via migration, generate token on join, create middleware to resolve GamePlayer from token. Support reconnection within 30 seconds.
  - **Requirements:** 5.6, 8.6
  - **Files:** `database/migrations/2024_01_01_000010_add_player_token_to_game_players.php`, `app/Http/Middleware/ResolveGamePlayer.php`, `app/Models/GamePlayer.php`

- [x] 11. Create API controllers (GameController, RoundController, WordSubmissionController, GameHistoryController) and register routes in routes/api.php. Implement rate limiting (1 word/second per player_token). Wire controllers to services.
  - **Requirements:** 1, 2, 4, 5, 12
  - **Files:** `app/Http/Controllers/GameController.php`, `app/Http/Controllers/RoundController.php`, `app/Http/Controllers/WordSubmissionController.php`, `app/Http/Controllers/GameHistoryController.php`, `routes/api.php`, `app/Providers/AppServiceProvider.php`

- [x] 12. Implement BotPlayerService (word selection: 70% short 2-5, 30% long 6+, max 50% possible words, max 12/round) and BotPlayJob (submits one word per job, dispatches next with 3-8s delay, uses same validator/scorer as humans).
  - **Requirements:** 11.1, 11.2, 11.3, 11.4, 11.5, 11.6
  - **Files:** `app/Services/BotPlayerService.php`, `app/Jobs/BotPlayJob.php`

- [x] 13. Set up React frontend structure: React Router (/, /arena/{code}, /play/{code}), TypeScript types matching API, gameApi client module, shared hooks (useGame with useReducer, useWebSocket for Echo channel, useTimer for countdown). Create Blade SPA shell and web route catch-all.
  - **Requirements:** 13, 14
  - **Files:** `resources/js/api/gameApi.ts`, `resources/js/api/types.ts`, `resources/js/pages/HomePage.tsx`, `resources/js/pages/ArenaScreen.tsx`, `resources/js/pages/PlayerScreen.tsx`, `resources/js/hooks/useGame.ts`, `resources/js/hooks/useWebSocket.ts`, `resources/js/hooks/useTimer.ts`, `resources/views/app.blade.php`, `routes/web.php`

- [x] 14. Implement HomePage: "Create Game" button (mode selection arena/vs_computer, calls API, redirects to /arena/{code}) and "Join Game" form (6-char code input, redirects to /play/{code}). Error handling for invalid codes.
  - **Requirements:** 1.6, 2.1
  - **Files:** `resources/js/pages/HomePage.tsx`

- [x] 15. Implement Arena Screen with three states: WaitingRoom (Game_Code large text, QR Code, player list via WebSocket), GameBoard (letters 72px, timer, live scoreboard ranked by score, last 5 words), EndScreen (winner, positions, highlights, Play Again button). All real-time via WebSocket.
  - **Requirements:** 13.1, 13.2, 13.3, 13.4, 13.5, 9.1, 9.2, 9.3, 9.4, 10.1, 10.2, 10.4
  - **Files:** `resources/js/pages/ArenaScreen.tsx`, `resources/js/components/arena/WaitingRoom.tsx`, `resources/js/components/arena/GameBoard.tsx`, `resources/js/components/arena/EndScreen.tsx`

- [x] 16. Implement Player Screen: JoinForm (nickname 2-30 chars validation), WaitingView (confirmation + waiting message), WordInput (text field max 15 chars + 44x44px submit button + Enter key + clear/refocus + prevent empty), WordHistory (newest first + status), score display, timer, end state with final stats. Mobile-optimized 320-428px.
  - **Requirements:** 14.1, 14.2, 14.3, 14.4, 14.5, 14.6, 14.7, 5.3, 5.4, 5.5, 2.1, 2.7, 10.3
  - **Files:** `resources/js/pages/PlayerScreen.tsx`, `resources/js/components/player/JoinForm.tsx`, `resources/js/components/player/WordInput.tsx`, `resources/js/components/player/WordHistory.tsx`, `resources/js/components/player/WaitingView.tsx`

- [ ] 17. Write unit tests for WordValidator (all 6 checks), ScoringEngine (scoring table, combos, perfect word), and LetterSetGenerator (threshold logic). Write feature tests for full API lifecycle (create → join → start → submit → end) including error cases and rate limiting.
  - **Requirements:** All
  - **Files:** `tests/Unit/WordValidatorTest.php`, `tests/Unit/ScoringEngineTest.php`, `tests/Unit/LetterSetGeneratorTest.php`, `tests/Feature/GameLifecycleTest.php`, `tests/Feature/WordSubmissionTest.php`

## Task Dependency Graph

```json
{
  "waves": [
    [1, 2, 3],
    [4, 5, 6, 7, 8, 9, 10],
    [11, 12, 13],
    [14, 15, 16],
    [17]
  ]
}
```

**Wave 1 (Infrastructure):** Install dependencies + dictionary import. No interdependencies.

**Wave 2 (Core Services):** All services, events, and player_token. Depend on wave 1 (Reverb for events, dictionary for validators).

**Wave 3 (Integration Layer):** API controllers wire to services. Bot uses services. React structure uses frontend deps.

**Wave 4 (UI Pages):** All frontend pages depend on React structure (wave 3) and API (wave 3).

**Wave 5 (Verification):** Tests depend on all implementation being complete.

## Notes

- The database layer (models, migrations, seeders) already exists. No schema changes needed except Task 10 (player_token).
- Dictionary import (Task 3) is a prerequisite for LetterSetGenerator and WordValidator to function.
- The bot (Task 12) is independent of other services and can be implemented last before tests.
- For local development, run `composer dev` which starts server, queue worker, and Vite in parallel.
