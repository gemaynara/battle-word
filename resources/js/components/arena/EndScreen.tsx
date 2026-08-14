import type { ScoreEntry, Player } from '../../api/types';
import type { Highlights } from '../../hooks/useGame';

interface EndScreenProps {
  scoreboard: ScoreEntry[];
  players: Player[];
  highlights: Highlights | null;
  onPlayAgain: () => void;
  isHost: boolean;
  baseWord?: string | null;
}

export default function EndScreen({ scoreboard, players, highlights, onPlayAgain, isHost, baseWord }: EndScreenProps) {
  // Req 10.1: Use position field from server (respects tiebreaker logic) rather than client-side re-sort
  const sortedScoreboard = [...scoreboard].sort((a, b) => a.position - b.position);
  const winner = sortedScoreboard[0];

  return (
    <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-gray-900 via-indigo-950 to-gray-900 p-8">
      {/* Base Word Reveal */}
      {baseWord && (
        <div className="mb-6 text-center">
          <p className="text-lg font-medium text-indigo-300 uppercase tracking-wider">A palavra era</p>
          <p className="mt-1 text-5xl font-bold text-white tracking-widest">{baseWord}</p>
        </div>
      )}

      {/* Winner Announcement */}
      {winner && (
        <div className="mb-8 text-center">
          <p className="text-2xl font-medium text-yellow-300 uppercase tracking-wider">🏆 Vencedor 🏆</p>
          <p className="mt-2 text-6xl font-bold text-white">{winner.nickname}</p>
          <p className="mt-2 text-4xl font-bold text-yellow-400">{winner.score} pontos</p>
        </div>
      )}

      {/* Final Standings (Req 9.4, 10.1) */}
      <div className="mb-8 w-full max-w-lg rounded-2xl bg-white/10 backdrop-blur-sm p-6">
        <h2 className="mb-4 text-center text-2xl font-semibold text-white">
          Classificação Final ({players.length} jogadores)
        </h2>
        <div className="space-y-2">
          {sortedScoreboard.map((entry) => (
            <div
              key={entry.nickname}
              className={`flex items-center gap-3 rounded-lg px-4 py-3 ${
                entry.position === 1
                  ? 'bg-yellow-500/20 border border-yellow-500/40'
                  : entry.position === 2
                    ? 'bg-gray-400/10 border border-gray-400/30'
                    : entry.position === 3
                      ? 'bg-orange-500/10 border border-orange-500/30'
                      : 'bg-white/5'
              }`}
            >
              <span className="w-10 text-xl font-bold text-indigo-300">
                {entry.position}º
              </span>
              <span className="flex-1 text-xl font-medium text-white">{entry.nickname}</span>
              <span className="text-xl font-bold text-yellow-300">{entry.score}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Highlights (Req 10.2) */}
      {highlights && (
        <div className="mb-8 w-full max-w-lg rounded-2xl bg-white/10 backdrop-blur-sm p-6">
          <h2 className="mb-4 text-center text-2xl font-semibold text-white">Destaques</h2>
          <div className="grid gap-3 sm:grid-cols-3">
            {highlights.best_combo && (
              <div className="rounded-xl bg-purple-500/20 p-4 text-center">
                <p className="text-sm font-medium text-purple-300 uppercase">Melhor Combo</p>
                <p className="mt-1 text-lg font-bold text-white">{highlights.best_combo.nickname}</p>
                <p className="text-2xl font-bold text-purple-400">{highlights.best_combo.combo}x</p>
              </div>
            )}
            {highlights.longest_word && (
              <div className="rounded-xl bg-blue-500/20 p-4 text-center">
                <p className="text-sm font-medium text-blue-300 uppercase">Palavra Mais Longa</p>
                <p className="mt-1 text-lg font-bold text-white">{highlights.longest_word.nickname}</p>
                <p className="text-2xl font-bold text-blue-400">{highlights.longest_word.word}</p>
              </div>
            )}
            {highlights.most_words && (
              <div className="rounded-xl bg-green-500/20 p-4 text-center">
                <p className="text-sm font-medium text-green-300 uppercase">Mais Palavras Válidas</p>
                <p className="mt-1 text-lg font-bold text-white">{highlights.most_words.nickname}</p>
                <p className="text-2xl font-bold text-green-400">{highlights.most_words.count}</p>
              </div>
            )}
          </div>

          {/* Req 10.2: Count of valid words per player */}
          {highlights.words_per_player && highlights.words_per_player.length > 0 && (
            <div className="mt-4">
              <p className="mb-2 text-center text-sm font-medium text-indigo-300 uppercase">Palavras Válidas por Jogador</p>
              <div className="flex flex-wrap justify-center gap-3">
                {highlights.words_per_player.map((entry) => (
                  <div key={entry.nickname} className="rounded-lg bg-white/5 px-3 py-2 text-center">
                    <p className="text-sm text-white">{entry.nickname}</p>
                    <p className="text-lg font-bold text-indigo-300">{entry.count}</p>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      {/* Play Again Button (host only) - Req 10.4 */}
      {isHost && (
        <button
          onClick={onPlayAgain}
          className="rounded-xl bg-green-500 px-10 py-4 text-2xl font-bold text-white transition hover:bg-green-400 active:scale-95"
        >
          Jogar Novamente
        </button>
      )}
    </div>
  );
}
