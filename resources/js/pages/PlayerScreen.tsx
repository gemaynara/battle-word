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

const styles = {
  page: {
    display: 'flex',
    flexDirection: 'column' as const,
    height: '100vh',
    height: '100dvh',
    backgroundColor: '#0f172a',
    color: '#f8fafc',
    maxWidth: '428px',
    margin: '0 auto',
    overflow: 'hidden' as const,
  },
  header: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: '8px 12px',
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
    fontSize: '20px',
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
    fontSize: '20px',
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
    gap: '4px',
    padding: '12px 12px',
    backgroundColor: '#1e293b',
    flexShrink: 0,
  },
  themeLabel: {
    fontSize: '10px',
    color: '#64748b',
    textTransform: 'uppercase' as const,
    letterSpacing: '1px',
  },
  themeWord: {
    fontSize: '28px',
    fontWeight: '800' as const,
    color: '#a5b4fc',
    letterSpacing: '2px',
  },
  themeHint: {
    fontSize: '12px',
    color: '#94a3b8',
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
    minHeight: '100vh',
    padding: '24px 16px',
    backgroundColor: '#0f172a',
    color: '#f8fafc',
    textAlign: 'center' as const,
    maxWidth: '428px',
    margin: '0 auto',
    boxSizing: 'border-box' as const,
  },
  endTitle: {
    fontSize: '28px',
    fontWeight: '700' as const,
    marginBottom: '8px',
  },
  endScore: {
    fontSize: '48px',
    fontWeight: '700' as const,
    color: '#6366f1',
    marginBottom: '8px',
  },
  endPosition: {
    fontSize: '18px',
    color: '#94a3b8',
    marginBottom: '32px',
  },
  statsGrid: {
    display: 'grid',
    gridTemplateColumns: '1fr 1fr',
    gap: '16px',
    width: '100%',
    maxWidth: '300px',
    marginBottom: '32px',
  },
  statCard: {
    backgroundColor: '#1e293b',
    borderRadius: '8px',
    padding: '16px 12px',
    textAlign: 'center' as const,
  },
  statValue: {
    fontSize: '24px',
    fontWeight: '700' as const,
    color: '#f8fafc',
    marginBottom: '4px',
  },
  statLabel: {
    fontSize: '12px',
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
  },
  loadingContainer: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: '100vh',
    backgroundColor: '#0f172a',
    color: '#94a3b8',
    fontSize: '16px',
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

    // Wait 2 seconds for the backend to process EndRoundJob, then poll
    const timeout = setTimeout(async () => {
      if (!code) return;
      try {
        const data = await gameApi.getGameState(code);
        if (data.status === 'finished' || (data.current_round && data.current_round.status === 'finished')) {
          dispatch({
            type: 'ROUND_ENDED',
            payload: {
              round_number: data.current_round?.round_number ?? 1,
              final_scores: data.players.map((p, i) => ({
                nickname: p.nickname,
                score: p.total_score ?? 0,
                position: i + 1,
                last_word: null,
              })),
              base_word: state.round?.letters ?? undefined,
            },
          });
        }
      } catch {
        // If fetch fails, just force to finished state
        setView('finished');
      }
    }, 2500);

    return () => clearTimeout(timeout);
  }, [view, timeRemaining, code, dispatch, state.round?.letters]);

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
    } catch (err) {
      // If submission fails (rate limit, etc.), show as rejected
      const submission: WordSubmission = {
        word,
        is_valid: false,
        points: 0,
        rejection_reason: 'Erro ao enviar',
      };
      dispatch({ type: 'SET_MY_WORD', payload: submission });
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
    const longestWord = validWords.reduce(
      (longest, w) => (w.word.length > longest.length ? w.word : longest),
      ''
    );
    // Use the position field from scoreboard entry if available, else fallback to index
    const myEntry = state.scoreboard.find((entry) => entry.nickname === nickname);
    const myPosition = myEntry?.position ?? (
      state.scoreboard.findIndex((entry) => entry.nickname === nickname) + 1
    );

    return (
      <div style={styles.endContainer}>
        <h1 style={styles.endTitle}>Rodada Finalizada!</h1>
        {state.baseWord && (
          <p style={{
            fontSize: '14px',
            color: '#a5b4fc',
            marginBottom: '12px',
          }}>
            A palavra era: <strong style={{ fontSize: '18px', color: '#f8fafc', letterSpacing: '2px' }}>{state.baseWord}</strong>
          </p>
        )}
        <p style={styles.endScore}>{state.myScore}</p>
        {myPosition > 0 && (
          <p style={styles.endPosition}>
            Você ficou em {myPosition}º lugar
          </p>
        )}
        <div style={styles.statsGrid}>
          <div style={styles.statCard}>
            <div style={styles.statValue}>{validWords.length}</div>
            <div style={styles.statLabel}>Palavras válidas</div>
          </div>
          <div style={styles.statCard}>
            <div style={styles.statValue}>{longestWord || '—'}</div>
            <div style={styles.statLabel}>Maior palavra</div>
          </div>
        </div>
        <button
          style={styles.homeButton}
          onClick={() => window.location.href = '/'}
        >
          Voltar ao Início
        </button>
      </div>
    );
  }

  // PLAYING VIEW
  const themeWord = state.round?.letters || '';

  return (
    <div style={styles.page}>
      {/* Header: Score + Timer + Combo */}
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
        <div style={styles.comboBlock}>
          <span style={styles.comboLabel}>Combo</span>
          <span style={styles.comboValue}>{state.myCombo}x</span>
        </div>
      </div>

      {/* Theme Word */}
      <div style={styles.themeSection}>
        <span style={styles.themeLabel}>Palavra-tema</span>
        <span style={styles.themeWord}>{themeWord}</span>
        <span style={styles.themeHint}>Digite palavras relacionadas!</span>
      </div>

      {/* Word History (scrollable middle) */}
      <div style={styles.wordListArea}>
        <WordHistory words={state.myWords} />
      </div>

      {/* Word Input (sticky bottom) */}
      <WordInput
        onSubmit={handleSubmitWord}
        disabled={timeRemaining <= 0}
      />
    </div>
  );
}
