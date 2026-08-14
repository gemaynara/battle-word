import type { WordSubmission } from '../../api/types';

interface WordHistoryProps {
  words: WordSubmission[];
}

const rejectionReasons: Record<string, string> = {
  time_expired: 'Tempo esgotado',
  invalid_letters: 'Letras inválidas',
  not_in_dictionary: 'Não está no dicionário',
  duplicate: 'Palavra duplicada',
  too_short: 'Palavra muito curta',
};

const styles = {
  container: {
    flex: 1,
    overflowY: 'auto' as const,
    padding: '8px 16px',
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
    borderRadius: '6px',
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
  points: {
    fontSize: '14px',
    fontWeight: '600' as const,
    color: '#4ade80',
  },
  reason: {
    fontSize: '12px',
    color: '#f87171',
  },
  rightSection: {
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'flex-end',
    gap: '2px',
  },
};

export default function WordHistory({ words }: WordHistoryProps) {
  if (words.length === 0) {
    return (
      <div style={styles.container}>
        <p style={styles.empty}>Nenhuma palavra enviada ainda.</p>
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
              <span style={styles.points}>+{submission.points}</span>
            ) : (
              <span style={styles.reason}>
                {rejectionReasons[submission.rejection_reason ?? ''] || 'Inválida'}
              </span>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
