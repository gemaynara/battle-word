import { useState, FormEvent } from 'react';
import { gameApi } from '../../api/gameApi';

interface JoinFormProps {
  code: string;
  onJoined: (token: string, nickname: string) => void;
}

const styles = {
  container: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: '100dvh',
    padding: '24px 16px',
    backgroundColor: '#0f172a',
    color: '#f8fafc',
  },
  title: {
    fontSize: '24px',
    fontWeight: '700' as const,
    marginBottom: '8px',
    textAlign: 'center' as const,
  },
  subtitle: {
    fontSize: '14px',
    color: '#94a3b8',
    marginBottom: '32px',
    textAlign: 'center' as const,
  },
  form: {
    width: '100%',
    maxWidth: '320px',
    display: 'flex',
    flexDirection: 'column' as const,
    gap: '16px',
  },
  label: {
    fontSize: '14px',
    fontWeight: '500' as const,
    color: '#cbd5e1',
    marginBottom: '4px',
    display: 'block',
  },
  input: {
    width: '100%',
    padding: '14px 16px',
    fontSize: '16px',
    borderRadius: '8px',
    border: '2px solid #334155',
    backgroundColor: '#1e293b',
    color: '#f8fafc',
    outline: 'none',
    boxSizing: 'border-box' as const,
  },
  inputFocus: {
    borderColor: '#6366f1',
  },
  button: {
    width: '100%',
    padding: '14px',
    fontSize: '16px',
    fontWeight: '600' as const,
    borderRadius: '8px',
    border: 'none',
    backgroundColor: '#6366f1',
    color: '#ffffff',
    cursor: 'pointer',
    minHeight: '48px',
    marginTop: '8px',
  },
  buttonDisabled: {
    opacity: 0.6,
    cursor: 'not-allowed' as const,
  },
  error: {
    fontSize: '13px',
    color: '#f87171',
    marginTop: '4px',
  },
  hint: {
    fontSize: '12px',
    color: '#64748b',
    marginTop: '4px',
  },
  gameCode: {
    fontSize: '14px',
    color: '#a5b4fc',
    fontWeight: '500' as const,
    marginBottom: '4px',
  },
};

const NICKNAME_REGEX = /^[A-Za-z0-9 _]+$/;

export default function JoinForm({ code, onJoined }: JoinFormProps) {
  const [nickname, setNickname] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const validate = (value: string): string | null => {
    if (value.length < 2) {
      return 'O apelido deve ter pelo menos 2 caracteres.';
    }
    if (value.length > 30) {
      return 'O apelido deve ter no máximo 30 caracteres.';
    }
    if (!NICKNAME_REGEX.test(value)) {
      return 'Use apenas letras, números, espaços e underscores.';
    }
    return null;
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    const trimmed = nickname.trim();
    const validationError = validate(trimmed);
    if (validationError) {
      setError(validationError);
      return;
    }
    setError('');
    setLoading(true);
    try {
      const response = await gameApi.joinGame(code, trimmed);
      localStorage.setItem(`player_token_${code}`, response.player_token);
      localStorage.setItem(`player_nickname_${code}`, response.nickname);
      onJoined(response.player_token, response.nickname);
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Erro ao entrar no jogo.';
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={styles.container}>
      <p style={styles.gameCode}>Jogo: {code}</p>
      <h1 style={styles.title}>Entrar na Partida</h1>
      <p style={styles.subtitle}>Escolha seu apelido para jogar</p>
      <form style={styles.form} onSubmit={handleSubmit}>
        <div>
          <label style={styles.label} htmlFor="nickname">
            Apelido
          </label>
          <input
            id="nickname"
            type="text"
            style={styles.input}
            value={nickname}
            onChange={(e) => {
              setNickname(e.target.value);
              if (error) setError('');
            }}
            maxLength={30}
            placeholder="Seu apelido..."
            autoComplete="off"
            autoFocus
          />
          {error && <p style={styles.error}>{error}</p>}
          <p style={styles.hint}>2-30 caracteres (letras, números, espaços, _)</p>
        </div>
        <button
          type="submit"
          style={{
            ...styles.button,
            ...(loading ? styles.buttonDisabled : {}),
          }}
          disabled={loading}
        >
          {loading ? 'Entrando...' : 'Entrar'}
        </button>
      </form>
    </div>
  );
}
