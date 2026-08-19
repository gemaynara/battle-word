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

  // Track previous positions to detect rank changes
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
      const timer = setTimeout(() => setHighlightedPlayers(new Set()), 1500);
      return () => clearTimeout(timer);
    }

    const newPositions = new Map<string, number>();
    scoreboard.forEach((entry) => {
      newPositions.set(entry.nickname, entry.position);
    });
    prevPositionsRef.current = newPositions;
  }, [scoreboard]);

  useEffect(() => {
    const newPositions = new Map<string, number>();
    scoreboard.forEach((entry) => {
      newPositions.set(entry.nickname, entry.position);
    });
    prevPositionsRef.current = newPositions;
  }, [scoreboard]);

  const validRecentWords = recentWords.filter((w) => w.is_valid).slice(0, 5);

  // Theme word is stored in the "letters" field
  const themeWord = letters;

  return (
    <div className="flex h-screen flex-col overflow-hidden bg-gradient-to-br from-gray-900 via-indigo-950 to-gray-900 p-4">
      {/* Top section: Timer + Theme Word */}
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

      {/* Middle section: Theme Word + Scoreboard */}
      <div className="flex min-h-0 flex-1 gap-6">
        {/* Left: Theme Word display */}
        <div className="flex flex-1 flex-col items-center justify-center">
          <p className="mb-2 text-lg font-medium uppercase tracking-wider text-indigo-300">Palavra-Tema</p>
          <div
            className="rounded-2xl border-2 border-indigo-400 bg-indigo-950 px-12 py-8 text-center"
          >
            <span className="text-6xl font-bold text-white tracking-widest">{themeWord}</span>
          </div>
          <p className="mt-4 text-base text-indigo-300">
            Jogadores estão enviando palavras relacionadas...
          </p>
        </div>

        {/* Right: Scoreboard + Recent Words */}
        <div className="flex w-96 shrink-0 flex-col gap-4 overflow-hidden">
          {/* Live Scoreboard */}
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
                      <span className="ml-2 text-sm text-indigo-400 truncate max-w-[80px]">
                        {entry.last_word ?? '–'}
                      </span>
                    </div>
                  ))
              )}
            </div>
          </div>

          {/* Recent Valid Words */}
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
                      +{submission.points} pts
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
