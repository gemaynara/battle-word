import { QRCodeSVG } from 'qrcode.react';
import type { Player } from '../../api/types';

interface WaitingRoomProps {
  code: string;
  players: Player[];
  qrUrl: string;
  isHost: boolean;
  onStartRound: () => void;
}

export default function WaitingRoom({ code, players, qrUrl, isHost, onStartRound }: WaitingRoomProps) {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-gray-900 via-indigo-950 to-gray-900 p-8">
      {/* Game Code - large and readable from distance */}
      <div className="mb-8 text-center">
        <p className="text-2xl font-medium text-indigo-300 uppercase tracking-wider">Código do Jogo</p>
        <p className="mt-2 font-mono font-bold text-white tracking-[0.3em]" style={{ fontSize: '96px', lineHeight: 1.1 }}>
          {code}
        </p>
      </div>

      {/* QR Code */}
      <div className="mb-8 rounded-2xl bg-white p-6">
        <QRCodeSVG value={qrUrl} size={220} level="M" />
      </div>

      {/* Instruction */}
      <p className="mb-8 text-xl text-indigo-200">
        Escaneie o QR Code ou digite o código no celular
      </p>

      {/* Rules Balloon */}
      <div className="mb-8 w-full max-w-lg rounded-2xl bg-indigo-500/20 border border-indigo-400/30 backdrop-blur-sm p-5">
        <h3 className="mb-2 text-center text-lg font-semibold text-indigo-200">Como Jogar</h3>
        <p className="text-center text-indigo-100 leading-relaxed">
          Forme palavras com as letras disponíveis. Quanto maior a palavra, mais pontos!
        </p>
        <div className="mt-3 flex flex-wrap justify-center gap-2 text-sm">
          <span className="rounded-full bg-white/10 px-3 py-1 text-indigo-200">2 letras = 1pt</span>
          <span className="rounded-full bg-white/10 px-3 py-1 text-indigo-200">3 = 3pt</span>
          <span className="rounded-full bg-white/10 px-3 py-1 text-indigo-200">4 = 5pt</span>
          <span className="rounded-full bg-white/10 px-3 py-1 text-indigo-200">5 = 8pt</span>
          <span className="rounded-full bg-white/10 px-3 py-1 text-indigo-200">6 = 12pt</span>
          <span className="rounded-full bg-white/10 px-3 py-1 text-indigo-200">7 = 17pt</span>
          <span className="rounded-full bg-white/10 px-3 py-1 text-yellow-300">8+ = 25pt</span>
        </div>
        <p className="mt-3 text-center text-sm text-indigo-300">
          Acertos consecutivos = combo (até 5x) &nbsp;|&nbsp; Usar todas as letras = +10 bônus
        </p>
      </div>

      {/* Connected Players */}
      <div className="mb-8 w-full max-w-lg rounded-2xl bg-white/10 backdrop-blur-sm p-6">
        <h2 className="mb-4 text-center text-2xl font-semibold text-white">
          Jogadores Conectados ({players.length})
        </h2>
        {players.length === 0 ? (
          <p className="text-center text-lg text-indigo-300">Aguardando jogadores...</p>
        ) : (
          <ul className="space-y-2">
            {players.map((player) => (
              <li
                key={player.id}
                className="flex items-center gap-3 rounded-lg bg-white/5 px-4 py-3"
              >
                <span className={`h-3 w-3 rounded-full ${player.is_connected ? 'bg-green-400' : 'bg-gray-500'}`} />
                <span className="text-lg font-medium text-white">{player.nickname}</span>
                {player.is_host && (
                  <span className="ml-auto rounded-full bg-indigo-500/30 px-3 py-0.5 text-sm text-indigo-200">
                    Host
                  </span>
                )}
              </li>
            ))}
          </ul>
        )}
      </div>

      {/* Start Round Button (host only) */}
      {isHost && players.length >= 1 && (
        <button
          onClick={onStartRound}
          className="rounded-xl bg-green-500 px-10 py-4 text-2xl font-bold text-white transition hover:bg-green-400 active:scale-95"
        >
          Iniciar Rodada
        </button>
      )}
    </div>
  );
}
