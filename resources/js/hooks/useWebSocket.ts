import { useEffect, useRef } from 'react';
import type { GameAction, Highlights } from './useGame';
import type { Player, ScoreEntry } from '../api/types';
import '../echo';

interface WebSocketEvents {
  PlayerJoined: { player: Player };
  PlayerDisconnected: { player_id: number; nickname: string };
  RoundStarted: { round_number: number; letters: string; started_at: string; duration_seconds: number; level?: number };
  WordSubmitted: { player_nickname: string; word: string; points: number; is_valid: boolean };
  ScoreUpdated: { scoreboard: ScoreEntry[] };
  RoundEnded: { round_number: number; final_scores: ScoreEntry[]; highlights?: Highlights; base_word?: string };
}

/**
 * Subscribe to a game channel using Laravel Echo.
 * Listens for game events and dispatches actions to the game reducer.
 * Cleans up subscription on unmount or code change.
 */
export function useWebSocket(code: string | undefined, dispatch: React.Dispatch<GameAction>) {
  const channelRef = useRef<ReturnType<typeof window.Echo.channel> | null>(null);

  useEffect(() => {
    if (!code) return;

    const channel = window.Echo.channel(`game.${code}`);
    channelRef.current = channel;

    channel.listen('.PlayerJoined', (event: WebSocketEvents['PlayerJoined']) => {
      dispatch({ type: 'PLAYER_JOINED', payload: event.player });
    });

    channel.listen('.PlayerDisconnected', (event: WebSocketEvents['PlayerDisconnected']) => {
      dispatch({ type: 'PLAYER_DISCONNECTED', payload: { player_id: event.player_id } });
    });

    channel.listen('.RoundStarted', (event: WebSocketEvents['RoundStarted']) => {
      dispatch({ type: 'ROUND_STARTED', payload: { ...event, level: event.level } });
    });

    channel.listen('.WordSubmitted', (event: WebSocketEvents['WordSubmitted']) => {
      dispatch({
        type: 'WORD_SUBMITTED',
        payload: {
          word: event.word,
          is_valid: event.is_valid,
          points: event.points,
          player_nickname: event.player_nickname,
        },
      });
    });

    channel.listen('.ScoreUpdated', (event: WebSocketEvents['ScoreUpdated']) => {
      dispatch({ type: 'SCORE_UPDATED', payload: { scoreboard: event.scoreboard } });
    });

    channel.listen('.RoundEnded', (event: WebSocketEvents['RoundEnded']) => {
      dispatch({ type: 'ROUND_ENDED', payload: { round_number: event.round_number, final_scores: event.final_scores, highlights: event.highlights, base_word: event.base_word } });
    });

    return () => {
      window.Echo.leave(`game.${code}`);
      channelRef.current = null;
    };
  }, [code, dispatch]);
}
