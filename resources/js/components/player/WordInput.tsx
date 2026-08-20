import { useState, useRef, FormEvent, KeyboardEvent } from 'react';

interface WordInputProps {
  onSubmit: (word: string) => void;
  disabled?: boolean;
}

const styles = {
  container: {
    display: 'flex',
    flexDirection: 'row' as const,
    gap: '8px',
    padding: '8px 12px',
    backgroundColor: '#1e293b',
    borderBottom: '1px solid #334155',
    flexShrink: 0,
  },
  input: {
    flex: 1,
    padding: '10px 12px',
    fontSize: '16px',
    borderRadius: '8px',
    border: '2px solid #334155',
    backgroundColor: '#0f172a',
    color: '#f8fafc',
    outline: 'none',
    minHeight: '44px',
    boxSizing: 'border-box' as const,
  },
  button: {
    minWidth: '44px',
    minHeight: '44px',
    padding: '10px 14px',
    fontSize: '14px',
    fontWeight: '600' as const,
    borderRadius: '8px',
    border: 'none',
    backgroundColor: '#6366f1',
    color: '#ffffff',
    cursor: 'pointer',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    whiteSpace: 'nowrap' as const,
    flexShrink: 0,
  },
  buttonDisabled: {
    backgroundColor: '#475569',
    cursor: 'not-allowed' as const,
    opacity: 0.6,
  },
};

export default function WordInput({ onSubmit, disabled = false }: WordInputProps) {
  const [value, setValue] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  const handleSubmit = () => {
    const trimmed = value.trim();
    if (!trimmed || disabled) return;
    onSubmit(trimmed.toUpperCase());
    setValue('');
    inputRef.current?.focus();
  };

  const handleFormSubmit = (e: FormEvent) => {
    e.preventDefault();
    handleSubmit();
  };

  const handleKeyDown = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleSubmit();
    }
  };

  return (
    <form style={styles.container} onSubmit={handleFormSubmit}>
      <input
        ref={inputRef}
        type="text"
        style={styles.input}
        value={value}
        onChange={(e) => setValue(e.target.value.toUpperCase())}
        onKeyDown={handleKeyDown}
        maxLength={30}
        placeholder="Palavra relacionada..."
        disabled={disabled}
        autoComplete="off"
        autoCorrect="off"
        autoCapitalize="characters"
        spellCheck={false}
        autoFocus
      />
      <button
        type="submit"
        style={{
          ...styles.button,
          ...(disabled ? styles.buttonDisabled : {}),
        }}
        disabled={disabled}
        aria-label="Enviar palavra"
      >
        Enviar
      </button>
    </form>
  );
}
