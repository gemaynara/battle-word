import { useEffect, useRef, useState } from 'react';
import type { ScoreEntry, WordSubmission } from '../../api/types';

interface GameBoardProps {
  letters: string;
  timeRemaining: number;
  scoreboard: ScoreEntry[];
  recentWords: WordSubmission[];
  level?: number;
}

export default function GameBoard({ letters, timeRemaining, scoreboard, recentWords, level }: GameBoardProps) {
  const isUrgent = timeRemaining <= 10;

  // Track previous positions to detect rank changes (Req 9.2)
  const prevPositionsRef = useRef<Map<string, number>>(new Map());
  const [highlightedPlayers, setHighlightedPlayers] = useState<Set<string>>(new Set());

  useEffect(() => {
    const prevPositions = prevPositionsRef.current;
    const newHighlights = new Set<string>();

    scoreboard.forEach((entry) => {
      const prevPos = prevPositions.get(entry.nickname);
      if (prevPos !== undefined && prevPos !== entry.position) {
        newHighlights.add(entry.nickname);
      }
    });

    if (newHighlights.size > 0) {
      setHighlightedPlayers(newHighlights);
      // Clear highlights after animation duration
      const timer = setTimeout(() => setHighlightedPlayers(new Set()), 1500);
      return () => clearTimeout(timer);
    }

    // Update stored positions
    const newPositions = new Map<string, number>();
    scoreboard.forEach((entry) => {
      newPositions.set(entry.nickname, entry.position);
    });
    prevPositionsRef.current = newPositions;
  }, [scoreboard]);

  // Update positions after highlight state is set
  useEffect(() => {
    const newPositions = new Map<string, number>();
    scoreboard.forEach((entry) => {
      newPositions.set(entry.nickname, entry.position);
    });
    prevPositionsRef.current = newPositions;
  }, [scoreboard]);

  // Filter only valid words for arena display (Req 13.2)
  const validRecentWords = recentWords.filter((w) => w.is_valid).slice(0, 5);

  return (
    <div className="flex h-screen flex-col overflow-hidden bg-gradient-to-br from-gray-900 via-indigo-950 to-gray-900 p-4">
      {/* Top section: Timer + Level */}
      <div className="mb-4 flex shrink-0 items-center justify-center gap-6">
        {level && (
          <div className="rounded-xl bg-indigo-500/20 px-5 py-2 text-center">
            <p className="text-sm font-medium text-indigo-300 uppercase">Nível</p>
            <p className="text-3xl font-bold text-indigo-400">{level}</p>
          </div>
        )}
        <div
          className={`rounded-2xl px-8 py-2 text-center font-mono font-bold ${
            isUrgent
              ? 'bg-red-500/20 text-red-400 animate-pulse'
              : 'bg-white/10 text-white'
          }`}
          style={{ fontSize: '48px' }}
        >
          {timeRemaining}s
        </div>
      </div>

      {/* Middle section: Letters + Scoreboard (Req 13.5 - no scrolling) */}
      <div className="flex min-h-0 flex-1 gap-6">
        {/* Left: Letters display */}
        <div className="flex flex-1 flex-col items-center justify-center">
          <p className="mb-4 text-xl font-medium uppercase tracking-wider text-indigo-300">Letras Disponíveis</p>
          <div className="flex flex-wrap items-center justify-center gap-3">
            {letters.split('').map((letter, index) => (
              <div
                key={index}
                className="flex items-center justify-center rounded-xl border-2 border-indigo-400 font-bold text-white"
                style={{
                  width: '90px',
                  height: '90px',
                  fontSize: '72px',
                  lineHeight: 1,
                  // Req 13.1: Solid dark background ensures 7:1+ contrast for white text
                  backgroundColor: '#1e1b4b',
                }}
              >
                {letter}
              </div>
            ))}
          </div>
        </div>

        {/* Right: Scoreboard + Recent Words */}
        <div className="flex w-96 shrink-0 flex-col gap-4 overflow-hidden">
          {/* Live Scoreboard (Req 9.1, 9.2, 9.3) */}
          <div className="flex min-h-0 flex-1 flex-col rounded-2xl bg-white/10 backdrop-blur-sm p-5">
            <h3 className="mb-3 shrink-0 text-lg font-semibold text-indigo-300 uppercase tracking-wider">Placar</h3>
            <div className="min-h-0 flex-1 space-y-2 overflow-hidden">
              {scoreboard.length === 0 ? (
                <p className="text-center text-indigo-400">Aguardando pontuações...</p>
              ) : (
                [...scoreboard]
                  .sort((a, b) => a.position - b.position)
                  .map((entry) => (
                    <div
                      key={entry.nickname}
                      className={`flex items-center gap-3 rounded-lg px-4 py-2 transition-all duration-300 ${
                        entry.position === 1 ? 'bg-yellow-500/20' : 'bg-white/5'
                      } ${
                        highlightedPlayers.has(entry.nickname)
                          ? 'ring-2 ring-yellow-400 bg-yellow-500/30 scale-[1.02]'
                          : ''
                      }`}
                    >
                      <span className="w-8 text-lg font-bold text-indigo-300">
                        {entry.position}º
                      </span>
                      <span className="flex-1 text-lg font-medium text-white truncate">
                        {entry.nickname}
                      </span>
                      <span className="text-lg font-bold text-yellow-300">
                        {entry.score}
                      </span>
                      {/* Req 9.3: Show dash if no last_word */}
                      <span className="ml-2 text-sm text-indigo-400 truncate max-w-[80px]">
                        {entry.last_word ?? '–'}
                      </span>
                    </div>
                  ))
              )}
            </div>
          </div>

          {/* Recent Valid Words (Req 13.2) */}
          <div className="shrink-0 rounded-2xl bg-white/10 backdrop-blur-sm p-5">
            <h3 className="mb-3 text-lg font-semibold text-indigo-300 uppercase tracking-wider">
              Últimas Palavras
            </h3>
            <div className="space-y-2">
              {validRecentWords.length === 0 ? (
                <p className="text-center text-indigo-400">Nenhuma palavra enviada ainda</p>
              ) : (
                validRecentWords.map((submission, index) => (
                  <div
                    key={`${submission.word}-${index}`}
                    className="flex items-center justify-between rounded-lg bg-green-500/10 px-4 py-2"
                  >
                    <div className="flex items-center gap-2">
                      <span className="text-sm font-medium text-green-300">
                        {submission.player_nickname}
                      </span>
                      <span className="text-lg font-bold text-white">{submission.word}</span>
                    </div>
                    <span className="font-bold text-green-400">
                      +{submission.points}
                    </span>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
