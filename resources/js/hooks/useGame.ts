import { useReducer } from 'react';
import type { GameState, Game, Player, Round, ScoreEntry, WordSubmission } from '../api/types';

export interface Highlights {
  best_combo?: { nickname: string; combo: number };
  longest_word?: { nickname: string; word: string };
  most_words?: { nickname: string; count: number };
  words_per_player?: { nickname: string; count: number }[];
}

// Action types
export type GameAction =
  | { type: 'SET_GAME_STATE'; payload: { game: Game; players: Player[]; round: Round | null } }
  | { type: 'PLAYER_JOINED'; payload: Player }
  | { type: 'PLAYER_DISCONNECTED'; payload: { player_id: number } }
  | { type: 'ROUND_STARTED'; payload: { round_number: number; letters: string; started_at: string; duration_seconds: number; level?: number } }
  | { type: 'WORD_SUBMITTED'; payload: WordSubmission }
  | { type: 'SCORE_UPDATED'; payload: { scoreboard: ScoreEntry[] } }
  | { type: 'ROUND_ENDED'; payload: { round_number: number; final_scores: ScoreEntry[]; highlights?: Highlights; base_word?: string } }
  | { type: 'SET_MY_WORD'; payload: WordSubmission }
  | { type: 'SET_TIME'; payload: number };

export interface ExtendedGameState extends GameState {
  highlights: Highlights | null;
  baseWord: string | null;
}

const initialState: ExtendedGameState = {
  game: null,
  round: null,
  players: [],
  scoreboard: [],
  recentWords: [],
  myWords: [],
  myScore: 0,
  myCombo: 0,
  timeRemaining: 0,
  status: 'loading',
  highlights: null,
  baseWord: null,
};

function gameReducer(state: ExtendedGameState, action: GameAction): ExtendedGameState {
  switch (action.type) {
    case 'SET_GAME_STATE': {
      const { game, players, round } = action.payload;
      let status: ExtendedGameState['status'] = 'waiting';
      if (game.status === 'playing' && round?.status === 'playing') {
        status = 'playing';
      } else if (game.status === 'finished') {
        status = 'finished';
      }
      return {
        ...state,
        game,
        players,
        round,
        status,
      };
    }

    case 'PLAYER_JOINED': {
      const exists = state.players.some(p => p.id === action.payload.id);
      const players = exists
        ? state.players.map(p => p.id === action.payload.id ? { ...p, is_connected: true } : p)
        : [...state.players, action.payload];
      return { ...state, players };
    }

    case 'PLAYER_DISCONNECTED': {
      const players = state.players.map(p =>
        p.id === action.payload.player_id ? { ...p, is_connected: false } : p
      );
      return { ...state, players };
    }

    case 'ROUND_STARTED': {
      const round: Round = {
        round_number: action.payload.round_number,
        letters: action.payload.letters,
        started_at: action.payload.started_at,
        duration_seconds: action.payload.duration_seconds,
        status: 'playing',
        level: action.payload.level,
      };
      return {
        ...state,
        round,
        status: 'playing',
        recentWords: [],
        myWords: [],
        myCombo: 0,
        timeRemaining: action.payload.duration_seconds,
        highlights: null,
        baseWord: null,
      };
    }

    case 'WORD_SUBMITTED': {
      const recentWords = [action.payload, ...state.recentWords].slice(0, 5);
      return { ...state, recentWords };
    }

    case 'SCORE_UPDATED': {
      return { ...state, scoreboard: action.payload.scoreboard };
    }

    case 'ROUND_ENDED': {
      const round = state.round ? { ...state.round, status: 'finished' as const } : null;
      return {
        ...state,
        round,
        status: 'finished',
        scoreboard: action.payload.final_scores,
        timeRemaining: 0,
        highlights: action.payload.highlights ?? null,
        baseWord: action.payload.base_word ?? null,
      };
    }

    case 'SET_MY_WORD': {
      const myWords = [action.payload, ...state.myWords];
      const myScore = action.payload.is_valid
        ? state.myScore + action.payload.points
        : state.myScore;
      const myCombo = action.payload.is_valid ? state.myCombo + 1 : 0;
      return { ...state, myWords, myScore, myCombo };
    }

    case 'SET_TIME': {
      return { ...state, timeRemaining: action.payload };
    }

    default:
      return state;
  }
}

export function useGame() {
  const [state, dispatch] = useReducer(gameReducer, initialState);
  return { state, dispatch } as const;
}
