import { useState, useEffect, useCallback } from 'react';
import { useParams } from 'react-router-dom';
import { useGame } from '../hooks/useGame';
import { useWebSocket } from '../hooks/useWebSocket';
import { useTimer } from '../hooks/useTimer';
import { gameApi } from '../api/gameApi';
import type { WordSubmission } from '../api/types';
import JoinForm from '../components/player/JoinForm';
import WaitingView from '../components/player/WaitingView';
import WordInput from '../components/player/WordInput';
import WordHistory from '../components/player/WordHistory';
import FloatingPoints from '../components/player/FloatingPoints';
import Confetti from '../components/player/Confetti';
import { playCorrectSound, playWrongSound, playHighScoreSound, playFinishSound, playFailSound } from '../hooks/useSounds';

const styles = {
  page: {
    display: 'flex',
    flexDirection: 'column' as const,
    height: '100%',
    height: '100dvh',
    backgroundColor: '#0f172a',
    color: '#f8fafc',
    width: '100%',
    maxWidth: '428px',
    margin: '0 auto',
    overflow: 'hidden' as const,
    position: 'fixed' as const,
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  header: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: '10px 16px',
    backgroundColor: '#1e293b',
    borderBottom: '1px solid #334155',
    flexShrink: 0,
  },
  scoreBlock: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'flex-start',
  },
  scoreLabel: {
    fontSize: '10px',
    color: '#64748b',
    textTransform: 'uppercase' as const,
    letterSpacing: '0.5px',
  },
  scoreValue: {
    fontSize: '22px',
    fontWeight: '700' as const,
    color: '#6366f1',
  },
  timerBlock: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'center',
  },
  timerLabel: {
    fontSize: '10px',
    color: '#64748b',
    textTransform: 'uppercase' as const,
    letterSpacing: '0.5px',
  },
  timerValue: {
    fontSize: '22px',
    fontWeight: '700' as const,
    color: '#f8fafc',
  },
  timerWarning: {
    color: '#f87171',
  },
  comboBlock: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'flex-end',
  },
  comboLabel: {
    fontSize: '10px',
    color: '#64748b',
    textTransform: 'uppercase' as const,
    letterSpacing: '0.5px',
  },
  comboValue: {
    fontSize: '20px',
    fontWeight: '700' as const,
    color: '#fbbf24',
  },
  abandonButton: {
    width: '36px',
    height: '36px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: '16px',
    fontWeight: '700' as const,
    backgroundColor: '#ef4444',
    color: '#ffffff',
    border: 'none',
    borderRadius: '8px',
    cursor: 'pointer',
    flexShrink: 0,
  },
  letters: {
    display: 'flex',
    flexWrap: 'wrap' as const,
    justifyContent: 'center',
    gap: '4px',
    padding: '8px 12px',
    backgroundColor: '#0f172a',
    flexShrink: 0,
  },
  letter: {
    width: '36px',
    height: '36px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: '16px',
    fontWeight: '700' as const,
    backgroundColor: '#334155',
    borderRadius: '6px',
    color: '#f8fafc',
  },
  themeSection: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'center',
    gap: '2px',
    padding: '8px 12px',
    backgroundColor: '#1e293b',
    borderBottom: '1px solid #334155',
    flexShrink: 0,
  },
  themeLabel: {
    fontSize: '10px',
    color: '#64748b',
    textTransform: 'uppercase' as const,
    letterSpacing: '1px',
  },
  themeWord: {
    fontSize: 'clamp(20px, 5vw, 26px)',
    fontWeight: '800' as const,
    color: '#a5b4fc',
    letterSpacing: '2px',
    wordBreak: 'break-all' as const,
    textAlign: 'center' as const,
  },
  themeHint: {
    fontSize: '11px',
    color: '#64748b',
  },
  wordListArea: {
    flex: 1,
    overflowY: 'auto' as const,
    display: 'flex',
    flexDirection: 'column' as const,
    minHeight: 0,
  },
  endContainer: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: '100dvh',
    padding: '24px 16px',
    backgroundColor: '#0f172a',
    color: '#f8fafc',
    textAlign: 'center' as const,
    width: '100%',
    maxWidth: '428px',
    margin: '0 auto',
    boxSizing: 'border-box' as const,
  },
  endTitle: {
    fontSize: 'clamp(22px, 6vw, 28px)',
    fontWeight: '700' as const,
    marginBottom: '8px',
  },
  endScore: {
    fontSize: 'clamp(36px, 10vw, 48px)',
    fontWeight: '700' as const,
    color: '#6366f1',
    marginBottom: '8px',
  },
  endPosition: {
    fontSize: '16px',
    color: '#94a3b8',
    marginBottom: '24px',
  },
  statsGrid: {
    display: 'grid',
    gridTemplateColumns: '1fr 1fr',
    gap: '12px',
    width: '100%',
    maxWidth: '300px',
    marginBottom: '24px',
  },
  statCard: {
    backgroundColor: '#1e293b',
    borderRadius: '8px',
    padding: '12px 8px',
    textAlign: 'center' as const,
    overflow: 'hidden' as const,
  },
  statValue: {
    fontSize: 'clamp(16px, 4vw, 22px)',
    fontWeight: '700' as const,
    color: '#f8fafc',
    marginBottom: '2px',
    wordBreak: 'break-all' as const,
  },
  statLabel: {
    fontSize: '11px',
    color: '#64748b',
  },
  homeButton: {
    padding: '14px 32px',
    fontSize: '16px',
    fontWeight: '600' as const,
    borderRadius: '8px',
    border: 'none',
    backgroundColor: '#6366f1',
    color: '#ffffff',
    cursor: 'pointer',
    minHeight: '48px',
    width: '100%',
    maxWidth: '280px',
  },
  loadingContainer: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: '100dvh',
    backgroundColor: '#0f172a',
    color: '#94a3b8',
    fontSize: '16px',
    width: '100%',
    maxWidth: '428px',
    margin: '0 auto',
  },
};

export default function PlayerScreen() {
  const { code } = useParams<{ code: string }>();
  const { state, dispatch } = useGame();
  const [playerToken, setPlayerToken] = useState<string | null>(null);
  const [nickname, setNickname] = useState<string | null>(null);
  const [view, setView] = useState<'loading' | 'join' | 'waiting' | 'playing' | 'finished'>('loading');
  const [floatTrigger, setFloatTrigger] = useState<{ points: number; id: number } | null>(null);
  const floatIdRef = { current: 0 };

  useWebSocket(code, dispatch);

  const timeRemaining = useTimer(
    state.round?.started_at ?? null,
    state.round?.duration_seconds ?? 60
  );

  // Check localStorage for existing token on mount
  useEffect(() => {
    if (!code) return;
    const storedToken = localStorage.getItem(`player_token_${code}`);
    const storedNickname = localStorage.getItem(`player_nickname_${code}`);
    if (storedToken && storedNickname) {
      setPlayerToken(storedToken);
      setNickname(storedNickname);
      // Fetch game state to determine current view
      gameApi.getGameState(code).then((data) => {
        const game = {
          code: data.code,
          status: data.status as 'waiting' | 'playing' | 'finished',
          mode: data.mode as 'arena' | 'vs_computer',
          max_players: data.max_players,
          qr_url: data.qr_url,
        };

        // If solo game is finished or has no active round, redirect to home
        const isSolo = data.mode === 'vs_computer';
        const roundFinished = data.current_round?.status === 'finished' || !data.current_round;
        const gameFinished = data.status === 'finished';

        if (isSolo && (gameFinished || roundFinished)) {
          window.location.href = '/';
          return;
        }

        dispatch({
          type: 'SET_GAME_STATE',
          payload: {
            game,
            players: data.players,
            round: data.current_round,
          },
        });
      }).catch(() => {
        // Token might be invalid, show join form
        localStorage.removeItem(`player_token_${code}`);
        localStorage.removeItem(`player_nickname_${code}`);
        setPlayerToken(null);
        setNickname(null);
        setView('join');
      });
    } else {
      setView('join');
    }
  }, [code, dispatch]);

  // Derive view from game state
  useEffect(() => {
    if (!playerToken) return;
    if (state.status === 'loading') {
      setView('loading');
    } else if (state.status === 'waiting') {
      setView('waiting');
    } else if (state.status === 'playing') {
      setView('playing');
    } else if (state.status === 'finished') {
      setView('finished');
    }
  }, [state.status, playerToken]);

  // Fallback: when timer reaches 0, poll game state to detect round end
  useEffect(() => {
    if (view !== 'playing' || timeRemaining > 0) return;

    // Wait 2 seconds for the backend to process EndRoundJob, then transition
    const timeout = setTimeout(() => {
      // Play end sound based on score
      if (state.myScore > 0) {
        playFinishSound();
      } else {
        playFailSound();
      }
      setView('finished');
    }, 2000);

    return () => clearTimeout(timeout);
  }, [view, timeRemaining, state.myScore]);

  const handleJoined = useCallback((token: string, nick: string) => {
    setPlayerToken(token);
    setNickname(nick);
    setView('waiting');
  }, []);

  const handleSubmitWord = useCallback(async (word: string) => {
    if (!code || !playerToken) return;
    try {
      const result = await gameApi.submitWord(code, playerToken, word);
      const submission: WordSubmission = {
        word: result.word,
        is_valid: result.is_valid,
        points: result.total_points,
        rejection_reason: result.rejection_reason,
      };
      dispatch({ type: 'SET_MY_WORD', payload: submission });

      // Add time bonus for valid words
      if (result.is_valid && result.time_bonus) {
        dispatch({ type: 'ADD_TIME_BONUS', payload: result.time_bonus });
      }

      // Trigger floating points animation
      if (result.is_valid && result.total_points > 0) {
        floatIdRef.current += 1;
        setFloatTrigger({ points: result.total_points, id: floatIdRef.current });
        // Play sound
        if (result.total_points >= 50) {
          playHighScoreSound();
        } else {
          playCorrectSound();
        }
      } else if (!result.is_valid) {
        playWrongSound();
      }
    } catch (err) {
      // If submission fails (rate limit, etc.), show as rejected
      const submission: WordSubmission = {
        word,
        is_valid: false,
        points: 0,
        rejection_reason: 'Erro ao enviar',
      };
      dispatch({ type: 'SET_MY_WORD', payload: submission });
      playWrongSound();
    }
  }, [code, playerToken, dispatch]);

  if (!code) {
    return <div style={styles.loadingContainer}>Código do jogo não encontrado.</div>;
  }

  // JOIN VIEW
  if (view === 'join') {
    return <JoinForm code={code} onJoined={handleJoined} />;
  }

  // WAITING VIEW
  if (view === 'waiting') {
    return <WaitingView gameCode={code} nickname={nickname || ''} />;
  }

  // LOADING VIEW
  if (view === 'loading') {
    return <div style={styles.loadingContainer}>Carregando...</div>;
  }

  // END STATE VIEW
  if (view === 'finished') {
    const validWords = state.myWords.filter((w) => w.is_valid);
    const bestWord = validWords.reduce(
      (best, w) => (w.points > best.points ? w : best),
      { word: '', points: 0 } as { word: string; points: number }
    );
    const themeWordDisplay = state.baseWord || state.round?.letters || '';
    const hasPoints = state.myScore > 0;

    return (
      <div style={styles.endContainer}>
        {hasPoints && <Confetti />}

        <h1 style={styles.endTitle}>
          {hasPoints ? 'Parabéns!' : 'Tempo esgotado!'}
        </h1>

        {!hasPoints && (
          <p style={{ fontSize: '48px', marginBottom: '8px' }}>😅</p>
        )}

        {themeWordDisplay && (
          <p style={{
            fontSize: '14px',
            color: '#a5b4fc',
            marginBottom: '12px',
          }}>
            Palavra-tema: <strong style={{ fontSize: '18px', color: '#f8fafc', letterSpacing: '2px' }}>{themeWordDisplay}</strong>
          </p>
        )}

        <p style={styles.endScore}>{state.myScore} pts</p>

        {!hasPoints && (
          <p style={{ fontSize: '14px', color: '#94a3b8', marginBottom: '16px' }}>
            Nenhuma palavra pontuou dessa vez. Tente novamente!
          </p>
        )}

        {hasPoints && (
          <>
            <p style={{ fontSize: '13px', color: '#94a3b8', marginBottom: '16px' }}>
              Tempo sobrevivido: {30 + (validWords.length * 5)}s
            </p>
            <div style={styles.statsGrid}>
              <div style={styles.statCard}>
                <div style={styles.statValue}>{validWords.length}</div>
                <div style={styles.statLabel}>Palavras aceitas</div>
              </div>
              <div style={styles.statCard}>
                <div style={styles.statValue}>{bestWord.word || '—'}</div>
                <div style={styles.statLabel}>Melhor palavra</div>
              </div>
              <div style={styles.statCard}>
                <div style={styles.statValue}>{bestWord.points}</div>
                <div style={styles.statLabel}>Maior pontuação</div>
              </div>
              <div style={styles.statCard}>
                <div style={styles.statValue}>
                  {validWords.length > 0 ? Math.round(state.myScore / validWords.length) : 0}
                </div>
                <div style={styles.statLabel}>Média por palavra</div>
              </div>
            </div>
          </>
        )}

        <button
          style={styles.homeButton}
          onClick={() => window.location.href = '/'}
        >
          {hasPoints ? 'Jogar de novo' : 'Tentar novamente'}
        </button>
      </div>
    );
  }

  // PLAYING VIEW
  const themeWord = state.round?.letters || '';

  return (
    <div style={styles.page}>
      {/* Header: Score + Timer + Abandon */}
      <div style={styles.header}>
        <div style={styles.scoreBlock}>
          <span style={styles.scoreLabel}>Pontos</span>
          <span style={styles.scoreValue}>{state.myScore}</span>
        </div>
        <div style={styles.timerBlock}>
          <span style={styles.timerLabel}>Tempo</span>
          <span
            style={{
              ...styles.timerValue,
              ...(timeRemaining <= 10 ? styles.timerWarning : {}),
            }}
          >
            {timeRemaining}s
          </span>
        </div>
        <button
          style={styles.abandonButton}
          onClick={() => window.location.href = '/'}
          aria-label="Abandonar partida"
        >
          ✕
        </button>
      </div>

      {/* Theme Word - compact */}
      <div style={styles.themeSection}>
        <span style={styles.themeWord}>{themeWord}</span>
        <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
          {state.myCombo > 1 && (
            <span style={{
              fontSize: '11px',
              fontWeight: '700',
              color: '#fbbf24',
              backgroundColor: 'rgba(251, 191, 36, 0.15)',
              padding: '2px 8px',
              borderRadius: '10px',
            }}>
              {state.myCombo}x seguidas
            </span>
          )}
          <span style={styles.themeHint}>+5s por acerto</span>
        </div>
      </div>

      {/* Word Input - right below theme for visibility with keyboard */}
      <WordInput
        onSubmit={handleSubmitWord}
        disabled={timeRemaining <= 0}
      />

      {/* Word History (scrollable, takes remaining space) */}
      <div style={styles.wordListArea}>
        <WordHistory words={state.myWords} />
      </div>

      {/* Floating points animation */}
      <FloatingPoints trigger={floatTrigger} />
    </div>
  );
}
