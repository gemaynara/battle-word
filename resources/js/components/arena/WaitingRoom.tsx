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
  const handleCloseRoom = () => {
    if (confirm('Tem certeza que deseja encerrar a sala?')) {
      window.location.href = '/';
    }
  };

  return (
    <div className="min-h-screen overflow-y-auto bg-gradient-to-br from-gray-900 via-indigo-950 to-gray-900 p-6 pb-12">
      <div className="mx-auto flex max-w-lg flex-col items-center">
        {/* Game Code */}
        <div className="mb-6 text-center">
          <p className="text-lg font-medium text-indigo-300 uppercase tracking-wider">Código do Jogo</p>
          <p className="mt-1 font-mono font-bold text-white tracking-[0.3em]" style={{ fontSize: '56px', lineHeight: 1.2 }}>
            {code}
          </p>
        </div>

        {/* QR Code */}
        <div className="mb-6 rounded-2xl bg-white p-4">
          <QRCodeSVG value={qrUrl} size={180} level="M" />
        </div>

        {/* Instruction */}
        <p className="mb-6 text-center text-base text-indigo-200">
          Escaneie o QR Code ou digite o código no celular
        </p>

        {/* Rules */}
        <div className="mb-6 w-full rounded-2xl bg-indigo-500/20 border border-indigo-400/30 backdrop-blur-sm p-4">
          <h3 className="mb-2 text-center text-base font-semibold text-indigo-200">Como Jogar</h3>
          <ul className="space-y-1.5 text-sm text-indigo-100">
            <li>• Uma <strong>palavra-tema</strong> aparece na tela</li>
            <li>• Digite palavras que tenham <strong>relação com o tema</strong></li>
            <li>• Quanto mais relacionada, mais pontos (até 100)</li>
            <li>• Você começa com <strong>30 segundos</strong></li>
            <li>• Cada acerto dá <strong>+5 segundos</strong> extras</li>
            <li>• A palavra precisa existir no dicionário</li>
          </ul>
        </div>

        {/* Connected Players */}
        <div className="mb-6 w-full rounded-2xl bg-white/10 backdrop-blur-sm p-4">
          <h2 className="mb-3 text-center text-lg font-semibold text-white">
            Jogadores ({players.length})
          </h2>
          {players.length === 0 ? (
            <p className="text-center text-base text-indigo-300">Aguardando jogadores...</p>
          ) : (
            <ul className="space-y-2">
              {players.map((player) => (
                <li
                  key={player.id}
                  className="flex items-center gap-3 rounded-lg bg-white/5 px-4 py-2.5"
                >
                  <span className={`h-3 w-3 rounded-full ${player.is_connected ? 'bg-green-400' : 'bg-gray-500'}`} />
                  <span className="text-base font-medium text-white">{player.nickname}</span>
                  {player.is_host && (
                    <span className="ml-auto rounded-full bg-indigo-500/30 px-2.5 py-0.5 text-xs text-indigo-200">
                      Host
                    </span>
                  )}
                </li>
              ))}
            </ul>
          )}
        </div>

        {/* Buttons */}
        {isHost && (
          <div className="flex w-full flex-col gap-3">
            {players.length >= 1 && (
              <button
                onClick={onStartRound}
                className="w-full rounded-xl bg-green-500 px-8 py-3.5 text-lg font-bold text-white transition hover:bg-green-400 active:scale-95"
              >
                Iniciar Rodada
              </button>
            )}
            <button
              onClick={handleCloseRoom}
              className="w-full rounded-xl bg-red-500/20 border border-red-400/30 px-8 py-3 text-base font-semibold text-red-300 transition hover:bg-red-500/30"
            >
              Encerrar Sala
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
