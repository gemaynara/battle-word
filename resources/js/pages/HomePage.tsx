import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { gameApi } from '../api/gameApi';

export default function HomePage() {
  const navigate = useNavigate();
  const [joinCode, setJoinCode] = useState('');
  const [joinError, setJoinError] = useState('');
  const [createError, setCreateError] = useState('');
  const [isCreating, setIsCreating] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState('aleatorio');

  const categories = [
    { value: 'aleatorio', label: 'Aleatório' },
    { value: 'substantivos', label: 'Substantivos' },
    { value: 'cidades', label: 'Cidades' },
    { value: 'filmes', label: 'Filmes' },
    { value: 'animais', label: 'Animais' },
    { value: 'comidas', label: 'Comidas' },
    { value: 'profissoes', label: 'Profissões' },
    { value: 'esportes', label: 'Esportes' },
    { value: 'natureza', label: 'Natureza' },
  ];

  const handleCreateGame = async (mode: 'arena' | 'vs_computer') => {
    setCreateError('');
    setIsCreating(true);
    try {
      const response = await gameApi.createGame(mode, selectedCategory);
      // Store host player_token so ArenaScreen can start rounds
      localStorage.setItem(`player_token_${response.code}`, response.player_token);
      navigate(`/arena/${response.code}`);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Erro ao criar jogo. Tente novamente.';
      setCreateError(message);
    } finally {
      setIsCreating(false);
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

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 px-4">
      <div className="w-full max-w-md space-y-8 text-center">
        {/* Title */}
        <div>
          <h1 className="text-5xl font-bold text-white tracking-tight">
            Batalha de Palavras
          </h1>
          <p className="mt-3 text-lg text-purple-200">
            Desafie seus amigos em uma batalha de vocabulário!
          </p>
        </div>

        {/* Create Game Section */}
        <div className="rounded-2xl bg-white/10 backdrop-blur-sm p-6 space-y-4">
          <h2 className="text-xl font-semibold text-white">Criar Jogo</h2>

          {/* Category Selector */}
          <div>
            <label className="block text-sm font-medium text-purple-200 mb-2">Tema das palavras</label>
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

          <div className="flex flex-col gap-3 sm:flex-row sm:gap-4">
            <button
              onClick={() => handleCreateGame('arena')}
              disabled={isCreating}
              className="flex-1 rounded-xl bg-indigo-500 px-6 py-3 text-lg font-semibold text-white transition hover:bg-indigo-400 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isCreating ? 'Criando...' : 'Modo Arena'}
            </button>
            <button
              onClick={() => handleCreateGame('vs_computer')}
              disabled={isCreating}
              className="flex-1 rounded-xl bg-pink-500 px-6 py-3 text-lg font-semibold text-white transition hover:bg-pink-400 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isCreating ? 'Criando...' : 'Jogar contra Bot'}
            </button>
          </div>
          {createError && (
            <p className="text-sm text-red-300">{createError}</p>
          )}
        </div>

        {/* Join Game Section */}
        <div className="rounded-2xl bg-white/10 backdrop-blur-sm p-6 space-y-4">
          <h2 className="text-xl font-semibold text-white">Entrar em Jogo</h2>
          <form onSubmit={handleJoinGame} className="space-y-3">
            <input
              type="text"
              value={joinCode}
              onChange={handleJoinCodeChange}
              placeholder="Código do jogo (6 caracteres)"
              maxLength={6}
              className="w-full rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-center text-2xl font-mono font-bold tracking-widest text-white placeholder:text-white/40 placeholder:text-base placeholder:font-normal placeholder:tracking-normal focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/50"
            />
            <button
              type="submit"
              className="w-full rounded-xl bg-green-500 px-6 py-3 text-lg font-semibold text-white transition hover:bg-green-400"
            >
              Entrar
            </button>
          </form>
          {joinError && (
            <p className="text-sm text-red-300">{joinError}</p>
          )}
        </div>
      </div>
    </div>
  );
}
