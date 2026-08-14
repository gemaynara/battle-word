import type {
  CreateGameResponse,
  JoinGameResponse,
  GameStateResponse,
  WordSubmissionResult,
} from './types';

const API_BASE = '/api';

async function handleResponse<T>(response: Response): Promise<T> {
  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Request failed' }));
    throw new Error(error.message || error.error || `HTTP ${response.status}`);
  }
  return response.json() as Promise<T>;
}

export const gameApi = {
  createGame: async (mode: string, category: string = 'aleatorio'): Promise<CreateGameResponse> => {
    const response = await fetch(`${API_BASE}/games`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ mode, category }),
    });
    return handleResponse<CreateGameResponse>(response);
  },

  getGameState: async (code: string): Promise<GameStateResponse> => {
    const response = await fetch(`${API_BASE}/games/${code}`, {
      headers: { Accept: 'application/json' },
    });
    return handleResponse<GameStateResponse>(response);
  },

  joinGame: async (code: string, nickname: string): Promise<JoinGameResponse> => {
    const response = await fetch(`${API_BASE}/games/${code}/join`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ nickname }),
    });
    return handleResponse<JoinGameResponse>(response);
  },

  startRound: async (code: string, playerToken: string): Promise<{ round_number: number; letters: string; started_at: string; duration_seconds: number }> => {
    const response = await fetch(`${API_BASE}/games/${code}/start-round`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ player_token: playerToken }),
    });
    return handleResponse(response);
  },

  submitWord: async (code: string, playerToken: string, word: string): Promise<WordSubmissionResult> => {
    const response = await fetch(`${API_BASE}/games/${code}/submit-word`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ player_token: playerToken, word }),
    });
    return handleResponse<WordSubmissionResult>(response);
  },

  playAgain: async (code: string, playerToken: string): Promise<{ round_number: number; letters: string; started_at: string; duration_seconds: number }> => {
    const response = await fetch(`${API_BASE}/games/${code}/play-again`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ player_token: playerToken }),
    });
    return handleResponse(response);
  },
};
