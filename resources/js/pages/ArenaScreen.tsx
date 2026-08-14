import { useEffect, useState, useCallback } from 'react';
import { useParams } from 'react-router-dom';
import { useGame } from '../hooks/useGame';
import { useWebSocket } from '../hooks/useWebSocket';
import { useTimer } from '../hooks/useTimer';
import { gameApi } from '../api/gameApi';
import WaitingRoom from '../components/arena/WaitingRoom';
import GameBoard from '../components/arena/GameBoard';
import EndScreen from '../components/arena/EndScreen';

export default function ArenaScreen() {
  const { code } = useParams<{ code: string }>();
  const { state, dispatch } = useGame();
  const [error, setError] = useState<string | null>(null);

  // Connect WebSocket
  useWebSocket(code, dispatch);

  // Timer hook
  const timeRemaining = useTimer(
    state.round?.started_at ?? null,
    state.round?.duration_seconds ?? 60
  );

  // Update time in state
  useEffect(() => {
    if (state.status === 'playing') {
      dispatch({ type: 'SET_TIME', payload: timeRemaining });
    }
  }, [timeRemaining, state.status, dispatch]);

  // Fetch initial game state on mount
  useEffect(() => {
    if (!code) return;

    const fetchState = async () => {
      try {
        const response = await gameApi.getGameState(code);
        dispatch({
          type: 'SET_GAME_STATE',
          payload: {
            game: {
              code: response.code,
              status: response.status as 'waiting' | 'playing' | 'finished',
              mode: response.mode as 'arena' | 'vs_computer',
              max_players: response.max_players,
              qr_url: response.qr_url,
            },
            players: response.players,
            round: response.current_round,
          },
        });
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Erro ao carregar jogo.';
        setError(message);
      }
    };

    fetchState();
  }, [code, dispatch]);

  // Get the player token from localStorage (host token)
  const getPlayerToken = useCallback((): string | null => {
    if (!code) return null;
    return localStorage.getItem(`player_token_${code}`);
  }, [code]);

  // Check if current viewer is host
  const isHost = (() => {
    const token = getPlayerToken();
    // If we have a token stored, we're the host (for arena screen)
    return token !== null;
  })();

  // Start round handler
  const handleStartRound = useCallback(async () => {
    if (!code) return;
    const token = getPlayerToken();
    if (!token) {
      setError('Token de jogador não encontrado. Você é o host?');
      return;
    }

    try {
      await gameApi.startRound(code, token);
      // The WebSocket RoundStarted event will update the state
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao iniciar rodada.';
      setError(message);
    }
  }, [code, getPlayerToken]);

  // Play again handler
  const handlePlayAgain = useCallback(async () => {
    if (!code) return;
    const token = getPlayerToken();
    if (!token) {
      setError('Token de jogador não encontrado.');
      return;
    }

    try {
      const result = await gameApi.playAgain(code, token);
      // Transition back to waiting state with new round info
      dispatch({
        type: 'SET_GAME_STATE',
        payload: {
          game: {
            code: state.game?.code ?? code,
            status: 'waiting',
            mode: state.game?.mode ?? 'arena',
            max_players: state.game?.max_players ?? 10,
            qr_url: state.game?.qr_url ?? `${window.location.origin}/play/${code}`,
          },
          players: state.players,
          round: {
            round_number: result.round_number,
            letters: result.letters,
            started_at: result.started_at,
            duration_seconds: result.duration_seconds,
            status: 'waiting',
          },
        },
      });
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao criar nova rodada.';
      setError(message);
    }
  }, [code, getPlayerToken, state.game, state.players, dispatch]);

  // Loading state
  if (state.status === 'loading') {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-900 via-indigo-950 to-gray-900">
        <div className="text-center">
          <div className="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-indigo-400 border-t-transparent" />
          <p className="mt-4 text-xl text-indigo-200">Carregando jogo...</p>
        </div>
      </div>
    );
  }

  // Error state
  if (error) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-900 via-indigo-950 to-gray-900">
        <div className="text-center">
          <p className="text-xl text-red-400">{error}</p>
          <button
            onClick={() => setError(null)}
            className="mt-4 rounded-xl bg-indigo-500 px-6 py-2 text-white hover:bg-indigo-400"
          >
            Tentar novamente
          </button>
        </div>
      </div>
    );
  }

  // Render based on game status
  switch (state.status) {
    case 'waiting':
      return (
        <WaitingRoom
          code={code ?? ''}
          players={state.players}
          qrUrl={state.game?.qr_url ?? `${window.location.origin}/play/${code}`}
          isHost={isHost}
          onStartRound={handleStartRound}
        />
      );

    case 'playing':
      return (
        <GameBoard
          letters={state.round?.letters ?? ''}
          timeRemaining={timeRemaining}
          scoreboard={state.scoreboard}
          recentWords={state.recentWords}
          level={state.round?.level}
        />
      );

    case 'finished':
      return (
        <EndScreen
          scoreboard={state.scoreboard}
          players={state.players}
          highlights={state.highlights}
          onPlayAgain={handlePlayAgain}
          isHost={isHost}
          baseWord={state.baseWord}
        />
      );

    default:
      return null;
  }
}
