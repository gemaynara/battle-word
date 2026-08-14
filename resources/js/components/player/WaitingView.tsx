interface WaitingViewProps {
  gameCode: string;
  nickname: string;
}

const styles = {
  container: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: '100vh',
    padding: '24px 16px',
    backgroundColor: '#0f172a',
    color: '#f8fafc',
    textAlign: 'center' as const,
  },
  checkmark: {
    fontSize: '48px',
    marginBottom: '16px',
  },
  title: {
    fontSize: '20px',
    fontWeight: '700' as const,
    marginBottom: '8px',
  },
  code: {
    color: '#a5b4fc',
    fontWeight: '700' as const,
  },
  nickname: {
    fontSize: '16px',
    color: '#94a3b8',
    marginBottom: '32px',
  },
  waiting: {
    fontSize: '14px',
    color: '#64748b',
    marginBottom: '16px',
  },
  pulse: {
    display: 'inline-block',
    width: '12px',
    height: '12px',
    borderRadius: '50%',
    backgroundColor: '#6366f1',
    animation: 'pulse 1.5s ease-in-out infinite',
  },
  keyframes: `
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(1.2); }
    }
  `,
};

export default function WaitingView({ gameCode, nickname }: WaitingViewProps) {
  return (
    <div style={styles.container}>
      <style>{styles.keyframes}</style>
      <div style={styles.checkmark}>✅</div>
      <h1 style={styles.title}>
        Você entrou no jogo <span style={styles.code}>{gameCode}</span>!
      </h1>
      <p style={styles.nickname}>Jogando como: <strong>{nickname}</strong></p>

      {/* Rules Balloon */}
      <div style={{
        backgroundColor: 'rgba(99, 102, 241, 0.15)',
        border: '1px solid rgba(99, 102, 241, 0.3)',
        borderRadius: '12px',
        padding: '16px 20px',
        marginBottom: '24px',
        maxWidth: '320px',
        textAlign: 'center' as const,
      }}>
        <p style={{ fontSize: '14px', color: '#c7d2fe', marginBottom: '8px', fontWeight: '600' }}>
          Como Pontuar
        </p>
        <p style={{ fontSize: '13px', color: '#a5b4fc', lineHeight: '1.5', margin: 0 }}>
          Forme palavras com as letras disponíveis. Quanto maior, mais pontos!
          Acertos seguidos = combo (até 5x). Use todas as letras = +10 bônus!
        </p>
      </div>

      <p style={styles.waiting}>Aguardando o host iniciar a rodada...</p>
      <span style={styles.pulse} />
    </div>
  );
}
