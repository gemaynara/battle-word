# Technical Design Document

## Overview

Batalha de Palavras é um jogo de associação semântica em tempo real. O sistema lança uma palavra-tema e os jogadores submetem palavras relacionadas. A pontuação é calculada via similaridade de embeddings vetoriais (OpenAI API). O jogo suporta modo solo e multiplayer com ranking semanal.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    FRONTEND (React + TypeScript + Vite)           │
├──────────────────────────┬──────────────────────────────────────┤
│     Arena Screen         │         Player Screen                 │
│  (TV/Projector)          │        (Mobile Phone)                 │
│  - Palavra-tema grande   │  - Input de palavras                  │
│  - Placar ao vivo        │  - Histórico + pontos                 │
│  - Timer                 │  - Timer + score                      │
│  - WebSocket listener    │  - Animações + sons                   │
└────────────┬─────────────┴──────────────┬───────────────────────┘
             │ WebSocket (Echo/Reverb)     │ REST API + WebSocket
             │                            │
┌────────────▼────────────────────────────▼───────────────────────┐
│                     BACKEND (Laravel 12 + PHP 8.3)               │
├─────────────────────────────────────────────────────────────────┤
│  Controllers                                                     │
│  ├── GameController          (create, join, state, play-again)   │
│  ├── RoundController         (start, status)                     │
│  ├── WordSubmissionController (submit + similarity scoring)      │
│  └── RankingController       (weekly top 10)                     │
├─────────────────────────────────────────────────────────────────┤
│  Services                                                        │
│  ├── GameService             (game lifecycle)                    │
│  ├── SemanticSimilarityService (OpenAI embeddings + cosine)     │
│  ├── WordValidator           (dictionary + duplicate checks)     │
│  ├── RoundManager            (round lifecycle, theme selection)  │
│  └── BotPlayerService        (AI opponent - legacy)              │
├─────────────────────────────────────────────────────────────────┤
│  Events (Broadcasting via Laravel Reverb)                        │
│  ├── PlayerJoined / PlayerDisconnected                           │
│  ├── RoundStarted / RoundEnded                                   │
│  ├── WordSubmitted / ScoreUpdated                                │
├─────────────────────────────────────────────────────────────────┤
│  Jobs                                                            │
│  └── EndRoundJob             (finaliza rodada quando timer acaba)│
└─────────────────────────────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────────────────┐
│              PostgreSQL / MySQL + OpenAI API                      │
│  (games, game_players, game_rounds, submitted_words,             │
│   dictionary_words, weekly_rankings)                             │
└─────────────────────────────────────────────────────────────────┘
```

## Core Flow: Word Submission

```
Player                        Backend                         OpenAI API
  │                              │                               │
  │ POST /submit-word {word}     │                               │
  │─────────────────────────────>│                               │
  │                              │ WordValidator                  │
  │                              │  ├── Round active?             │
  │                              │  ├── Not theme word?           │
  │                              │  ├── In dictionary?            │
  │                              │  └── Not duplicate?            │
  │                              │                               │
  │                              │ SemanticSimilarityService      │
  │                              │  ├── Get embedding(theme)  ───>│
  │                              │  ├── Get embedding(word)   ───>│
  │                              │  ├── Cosine similarity         │
  │                              │  └── Map to 0-100 points       │
  │                              │                               │
  │                              │ If valid: +5s to timer         │
  │                              │ Update player score             │
  │                              │ Update weekly ranking           │
  │                              │                               │
  │  {points, similarity,        │                               │
  │   time_bonus, is_valid}      │                               │
  │<─────────────────────────────│                               │
  │                              │ Broadcast events               │
```

## Key Components

### SemanticSimilarityService

```php
class SemanticSimilarityService
{
    // OpenAI text-embedding-3-small
    // Cache embeddings for 7 days
    // Cosine similarity between two word embeddings
    // Threshold: < 0.30 = 0 points
    // Mapping: (similarity - 0.30) / 0.70 * 100
    
    public function calculateSimilarity(string $wordA, string $wordB): float
    public function similarityToPoints(float $similarity): int
}
```

### RoundManager - Theme Selection

```php
// Priority:
// 1. Words with category (curated, common words 4-7 chars)
// 2. Fallback: hardcoded list of ~90 everyday words

private function selectThemeWord(?string $category): string
```

### Timer Mechanics

- Initial duration: 30 seconds
- Each valid word (points > 0): +5 seconds added to `duration_seconds`
- EndRoundJob dispatched at initial duration, but re-checks if round still active
- Frontend `useTimer` hook recalculates from `started_at` + `duration_seconds`
- When `duration_seconds` increases, timer auto-extends via React state

### Weekly Ranking

```php
class WeeklyRanking
{
    // Table: weekly_rankings (nickname, week_key, best_score)
    // Records best score per nickname per week
    // API: GET /api/ranking/weekly → top 10 current week
    
    public static function recordScore(string $nickname, int $score, string $gameCode): void
    public static function currentWeekTop10(): array
}
```

## Frontend Architecture

```
resources/js/
├── pages/
│   ├── HomePage.tsx         (nickname, category, play solo/friends, ranking)
│   ├── ArenaScreen.tsx      (TV display for multiplayer)
│   └── PlayerScreen.tsx     (mobile game interface)
├── components/
│   ├── arena/               (WaitingRoom, GameBoard, EndScreen)
│   └── player/              (WordInput, WordHistory, FloatingPoints, Confetti)
├── hooks/
│   ├── useGame.ts           (state reducer)
│   ├── useTimer.ts          (countdown from server timestamp)
│   ├── useWebSocket.ts      (Laravel Echo events)
│   └── useSounds.ts         (Web Audio API synthesized sounds)
└── api/
    ├── gameApi.ts           (HTTP client)
    └── types.ts             (TypeScript interfaces)
```

### Sound Design (Web Audio API)

- **Correct**: Ascending arpeggio C-E-G (sine wave)
- **High Score (50+)**: Fast ascending 5-note scale (sine)
- **Wrong**: Descending square wave buzz
- **Round End (success)**: 4-note triangle wave fanfare
- **Round End (fail)**: Descending sawtooth "sad trombone"

### Animations

- **FloatingPoints**: CSS `@keyframes floatUp` — points rise from center and fade out
- **Confetti**: 40 particles with random colors/sizes falling with rotation

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/games | Create game (mode, category, nickname) |
| GET | /api/games/{code} | Get game state |
| POST | /api/games/{code}/join | Join game (nickname) |
| POST | /api/games/{code}/start-round | Start round (host only) |
| POST | /api/games/{code}/submit-word | Submit word → similarity score |
| POST | /api/games/{code}/play-again | New round same game |
| GET | /api/ranking/weekly | Weekly top 10 |

### Submit Word Response

```json
{
  "word": "MOTOR",
  "is_valid": true,
  "points": 25,
  "similarity": 65,
  "time_bonus": 5,
  "player_total_score": 78,
  "rejection_reason": null
}
```

## Dictionary Strategy

- Source: fserb/pt-br ICF file (419k words with frequency scores)
- Filter: ICF ≤ 14, length 3-12, alpha-only after accent removal
- Result: ~26.000 common Portuguese words
- Supplement: 650+ manually curated everyday words (appliances, tech, food, etc.)
- Categories: 761 words tagged (animais, alimentos, corpo, natureza, objetos, verbos, adjetivos, profissões)
- Accent handling: Removed on import/validation (AÇÚCAR → ACUCAR)

## Deployment

- **Docker**: Multi-stage build (Node for Vite → PHP/Supervisor for app + Reverb)
- **Railway**: PostgreSQL + app container, port 8080
- **Local**: docker-compose with MySQL
- **Entrypoint**: Runs migrations, imports dictionary if empty, seeds categories
