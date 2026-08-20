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
    minHeight: '100dvh',
    padding: '24px 16px',
    backgroundColor: '#0f172a',
    color: '#f8fafc',
    textAlign: 'center' as const,
  },
  checkmark: {
    fontSize: '40px',
    marginBottom: '12px',
  },
  title: {
    fontSize: '18px',
    fontWeight: '700' as const,
    marginBottom: '6px',
  },
  code: {
    color: '#a5b4fc',
    fontWeight: '700' as const,
  },
  nickname: {
    fontSize: '15px',
    color: '#94a3b8',
    marginBottom: '24px',
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
        Você entrou no jogo <span style={styles.code}>{gameCode}</span>
      </h1>
      <p style={styles.nickname}>Jogando como: <strong>{nickname}</strong></p>

      <div style={{
        backgroundColor: 'rgba(99, 102, 241, 0.15)',
        border: '1px solid rgba(99, 102, 241, 0.3)',
        borderRadius: '12px',
        padding: '14px 16px',
        marginBottom: '24px',
        maxWidth: '300px',
        width: '100%',
        textAlign: 'left' as const,
      }}>
        <p style={{ fontSize: '13px', color: '#c7d2fe', marginBottom: '8px', fontWeight: '600', textAlign: 'center' }}>
          Regras
        </p>
        <ul style={{ fontSize: '12px', color: '#a5b4fc', lineHeight: '1.7', margin: 0, paddingLeft: '16px' }}>
          <li>Uma palavra-tema aparece na tela</li>
          <li>Digite palavras relacionadas ao tema</li>
          <li>Quanto mais relacionada, mais pontos</li>
          <li>Cada acerto dá +5 segundos de tempo</li>
          <li>A palavra precisa existir no dicionário</li>
        </ul>
      </div>

      <p style={styles.waiting}>Aguardando o host iniciar...</p>
      <span style={styles.pulse} />
    </div>
  );
}
