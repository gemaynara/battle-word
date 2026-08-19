import type { WordSubmission } from '../../api/types';

interface WordHistoryProps {
  words: WordSubmission[];
}

const rejectionReasons: Record<string, string> = {
  'Tempo esgotado': 'Tempo esgotado',
  'Não pode usar a palavra-tema': 'É a palavra-tema!',
  'Palavra não encontrada no dicionário': 'Não está no dicionário',
  'Palavra já enviada': 'Já enviada',
  'Palavra muito curta': 'Muito curta',
  'Palavra pouco relacionada': 'Pouco relacionada',
};

const styles = {
  container: {
    flex: 1,
    overflowY: 'auto' as const,
    padding: '8px 12px',
  },
  empty: {
    textAlign: 'center' as const,
    color: '#64748b',
    fontSize: '14px',
    padding: '32px 0',
  },
  item: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: '10px 12px',
    borderRadius: '8px',
    marginBottom: '6px',
    backgroundColor: '#1e293b',
  },
  wordSection: {
    display: 'flex',
    alignItems: 'center',
    gap: '8px',
  },
  word: {
    fontSize: '15px',
    fontWeight: '600' as const,
    color: '#f8fafc',
    letterSpacing: '0.5px',
  },
  validIcon: {
    color: '#4ade80',
    fontSize: '16px',
  },
  invalidIcon: {
    color: '#f87171',
    fontSize: '16px',
  },
  rightSection: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'flex-end',
    gap: '2px',
  },
  points: {
    fontSize: '14px',
    fontWeight: '700' as const,
    color: '#4ade80',
  },
  pointsHigh: {
    fontSize: '14px',
    fontWeight: '700' as const,
    color: '#fbbf24',
  },
  reason: {
    fontSize: '12px',
    color: '#f87171',
  },
};

export default function WordHistory({ words }: WordHistoryProps) {
  if (words.length === 0) {
    return (
      <div style={styles.container}>
        <p style={styles.empty}>
          Pense em palavras relacionadas ao tema!
        </p>
      </div>
    );
  }

  return (
    <div style={styles.container}>
      {words.map((submission, index) => (
        <div key={`${submission.word}-${index}`} style={styles.item}>
          <div style={styles.wordSection}>
            <span style={submission.is_valid ? styles.validIcon : styles.invalidIcon}>
              {submission.is_valid ? '✓' : '✗'}
            </span>
            <span style={styles.word}>{submission.word}</span>
          </div>
          <div style={styles.rightSection}>
            {submission.is_valid ? (
              <span style={submission.points >= 50 ? styles.pointsHigh : styles.points}>
                +{submission.points} pts
              </span>
            ) : (
              <span style={styles.reason}>
                {rejectionReasons[submission.rejection_reason ?? ''] || submission.rejection_reason || 'Inválida'}
              </span>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
