import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { gameApi } from '../api/gameApi';

export default function HomePage() {
  const navigate = useNavigate();
  const [joinCode, setJoinCode] = useState('');
  const [joinError, setJoinError] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState('aleatorio');
  const [view, setView] = useState<'main' | 'friends'>('main');

  const categories = [
    { value: 'aleatorio', label: '🎲 Aleatório' },
    { value: 'animais', label: '🐾 Animais' },
    { value: 'alimentos', label: '🍎 Alimentos' },
    { value: 'natureza', label: '🌿 Natureza' },
    { value: 'profissoes', label: '👷 Profissões' },
    { value: 'objetos', label: '🔧 Objetos' },
  ];

  // Solo mode: create game, auto-join as player, start round, go to player screen
  const handlePlaySolo = async () => {
    setError('');
    setIsLoading(true);
    try {
      const response = await gameApi.createGame('vs_computer', selectedCategory);
      const code = response.code;
      const token = response.player_token;

      // Store token
      localStorage.setItem(`player_token_${code}`, token);
      localStorage.setItem(`player_nickname_${code}`, 'Jogador');

      // Start the round immediately
      await gameApi.startRound(code, token);

      // Navigate to player screen
      navigate(`/play/${code}`);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao criar jogo. Tente novamente.';
      setError(message);
    } finally {
      setIsLoading(false);
    }
  };

  // Friends mode: create game and go to arena (waiting room with QR)
  const handleCreateFriendsGame = async () => {
    setError('');
    setIsLoading(true);
    try {
      const response = await gameApi.createGame('arena', selectedCategory);
      localStorage.setItem(`player_token_${response.code}`, response.player_token);
      navigate(`/arena/${response.code}`);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao criar jogo. Tente novamente.';
      setError(message);
    } finally {
      setIsLoading(false);
    }
  };

  const handleJoinCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6);
    setJoinCode(value);
    setJoinError('');
  };

  const handleJoinGame = (e: React.FormEvent) => {
    e.preventDefault();
    setJoinError('');
    if (joinCode.length !== 6) {
      setJoinError('O código deve ter 6 caracteres.');
      return;
    }
    navigate(`/play/${joinCode}`);
  };

  // Main view: choose solo or friends
  if (view === 'main') {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 px-4">
        <div className="w-full max-w-md space-y-6 text-center">
          {/* Title */}
          <div>
            <h1 className="text-4xl font-bold text-white tracking-tight sm:text-5xl">
              Batalha de Palavras
            </h1>
            <p className="mt-2 text-base text-purple-200 sm:text-lg">
              Descubra palavras relacionadas ao tema e ganhe pontos!
            </p>
          </div>

          {/* Category Selector */}
          <div className="rounded-2xl bg-white/10 backdrop-blur-sm p-4 space-y-3">
            <label className="block text-sm font-medium text-purple-200">Tema das palavras</label>
            <div className="flex flex-wrap justify-center gap-2">
              {categories.map((cat) => (
                <button
                  key={cat.value}
                  onClick={() => setSelectedCategory(cat.value)}
                  className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                    selectedCategory === cat.value
                      ? 'bg-indigo-500 text-white'
                      : 'bg-white/10 text-purple-200 hover:bg-white/20'
                  }`}
                >
                  {cat.label}
                </button>
              ))}
            </div>
          </div>

          {/* Play Options */}
          <div className="space-y-3">
            <button
              onClick={handlePlaySolo}
              disabled={isLoading}
              className="w-full rounded-2xl bg-indigo-500 px-6 py-4 text-lg font-semibold text-white transition hover:bg-indigo-400 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isLoading ? 'Preparando...' : '🎯 Jogar Sozinho'}
            </button>

            <button
              onClick={() => setView('friends')}
              disabled={isLoading}
              className="w-full rounded-2xl bg-pink-500 px-6 py-4 text-lg font-semibold text-white transition hover:bg-pink-400 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              👥 Jogar com Amigos
            </button>
          </div>

          {error && <p className="text-sm text-red-300">{error}</p>}

          {/* Join existing game */}
          <div className="rounded-2xl bg-white/10 backdrop-blur-sm p-4 space-y-3">
            <h2 className="text-base font-semibold text-white">Entrar em um Jogo</h2>
            <form onSubmit={handleJoinGame} className="flex gap-2">
              <input
                type="text"
                value={joinCode}
                onChange={handleJoinCodeChange}
                placeholder="CÓDIGO"
                maxLength={6}
                className="flex-1 rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-center text-lg font-mono font-bold tracking-widest text-white placeholder:text-white/40 placeholder:text-sm placeholder:tracking-normal focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/50"
              />
              <button
                type="submit"
                className="rounded-xl bg-green-500 px-5 py-3 text-base font-semibold text-white transition hover:bg-green-400"
              >
                Entrar
              </button>
            </form>
            {joinError && <p className="text-sm text-red-300">{joinError}</p>}
          </div>
        </div>
      </div>
    );
  }

  // Friends view: create game or go back
  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 px-4">
      <div className="w-full max-w-md space-y-6 text-center">
        <div>
          <h1 className="text-3xl font-bold text-white tracking-tight sm:text-4xl">
            Jogar com Amigos
          </h1>
          <p className="mt-2 text-base text-purple-200">
            Crie uma sala e compartilhe o código ou QR Code
          </p>
        </div>

        {/* Category Selector */}
        <div className="rounded-2xl bg-white/10 backdrop-blur-sm p-4 space-y-3">
          <label className="block text-sm font-medium text-purple-200">Tema das palavras</label>
          <div className="flex flex-wrap justify-center gap-2">
            {categories.map((cat) => (
              <button
                key={cat.value}
                onClick={() => setSelectedCategory(cat.value)}
                className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                  selectedCategory === cat.value
                    ? 'bg-indigo-500 text-white'
                    : 'bg-white/10 text-purple-200 hover:bg-white/20'
                }`}
              >
                {cat.label}
              </button>
            ))}
          </div>
        </div>

        <button
          onClick={handleCreateFriendsGame}
          disabled={isLoading}
          className="w-full rounded-2xl bg-indigo-500 px-6 py-4 text-lg font-semibold text-white transition hover:bg-indigo-400 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {isLoading ? 'Criando sala...' : '🎮 Criar Sala'}
        </button>

        {error && <p className="text-sm text-red-300">{error}</p>}

        <button
          onClick={() => setView('main')}
          className="text-purple-300 hover:text-white transition text-sm"
        >
          ← Voltar
        </button>
      </div>
    </div>
  );
}
