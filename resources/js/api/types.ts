export interface Game {
  code: string;
  status: 'waiting' | 'playing' | 'finished';
  mode: 'arena' | 'vs_computer';
  max_players: number;
  qr_url: string;
}

export interface Player {
  id: number;
  nickname: string;
  is_host: boolean;
  is_connected: boolean;
  total_score: number;
  best_combo: number;
}

export interface Round {
  round_number: number;
  letters: string;
  started_at: string | null;
  duration_seconds: number;
  status: 'waiting' | 'playing' | 'finished';
  level?: number;
}

export interface WordSubmissionResult {
  word: string;
  is_valid: boolean;
  points: number;
  combo_multiplier: number;
  total_points: number;
  is_perfect_word: boolean;
  is_long_word: boolean;
  player_total_score: number;
  rejection_reason: string | null;
  time_bonus?: number;
}

export interface ScoreEntry {
  nickname: string;
  score: number;
  position: number;
  last_word: string | null;
}

export interface CreateGameResponse {
  code: string;
  qr_url: string;
  status: string;
  mode: string;
  max_players: number;
  player_token: string;
}

export interface JoinGameResponse {
  player_token: string;
  player_id: number;
  nickname: string;
  game_code: string;
}

export interface GameStateResponse {
  code: string;
  status: string;
  mode: string;
  max_players: number;
  qr_url: string;
  players: Player[];
  current_round: Round | null;
}

export interface GameState {
  game: Game | null;
  round: Round | null;
  players: Player[];
  scoreboard: ScoreEntry[];
  recentWords: WordSubmission[];
  myWords: WordSubmission[];
  myScore: number;
  myCombo: number;
  timeRemaining: number;
  status: 'loading' | 'waiting' | 'playing' | 'finished';
}

export interface WordSubmission {
  word: string;
  is_valid: boolean;
  points: number;
  player_nickname?: string;
  rejection_reason?: string | null;
}
