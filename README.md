# Batalha de Palavras

Jogo multiplayer de associação de palavras em tempo real. Uma palavra-tema é lançada e os jogadores precisam digitar palavras semanticamente relacionadas ao tema para ganhar pontos.

## Como Jogar

1. Escolha seu apelido
2. Selecione um tema (Animais, Alimentos, Natureza, etc.) ou deixe aleatório
3. Jogue sozinho ou crie uma sala para jogar com amigos
4. Uma **palavra-tema** aparece na tela
5. Digite palavras que tenham relação com o tema
6. Quanto mais relacionada a palavra, mais pontos (até 100)
7. Cada acerto dá **+5 segundos** de tempo extra
8. A palavra precisa existir no dicionário português

## Modos de Jogo

- **Solo** — Jogue sozinho e tente bater seu recorde
- **Com Amigos** — Crie uma sala, compartilhe o QR Code/código e dispute em tempo real

## Ranking

Ranking semanal com os top 10 jogadores baseado na melhor pontuação individual.

## Stack Técnica

- **Backend**: Laravel 12 + PHP 8.3
- **Frontend**: React + TypeScript + Vite
- **Banco de Dados**: PostgreSQL (produção) / MySQL (local)
- **WebSocket**: Laravel Reverb
- **Similaridade Semântica**: OpenAI Embeddings API (text-embedding-3-small)
- **Dicionário**: ~26.000 palavras comuns do pt-BR (baseado no ICF do repositório fserb/pt-br)
- **Deploy**: Railway (Docker)
- **Áudio**: Web Audio API (sons sintetizados, sem arquivos externos)

## Executar Localmente

```bash
# Subir com Docker (app + MySQL)
docker compose up --build -d

# Acessar
http://localhost:8082
```

Variáveis necessárias no `.env`:
```
OPENAI_API_KEY=sk-...
```

## Deploy (Railway)

O projeto usa Dockerfile multi-stage (Node para build do frontend + PHP/Apache para o backend). Configurações necessárias no Railway:

- Serviço PostgreSQL
- Variáveis: `DB_URL`, `OPENAI_API_KEY`, `APP_KEY`, `REVERB_*`, `VITE_REVERB_*`
- Porta: 8080

## Estrutura do Projeto

```
app/
├── Console/Commands/     ImportDictionary (importa palavras do léxico pt-BR)
├── Events/               WebSocket events (PlayerJoined, RoundStarted, etc.)
├── Http/Controllers/     API controllers (Game, Round, WordSubmission, Ranking)
├── Jobs/                 EndRoundJob, BotPlayJob
├── Models/               Eloquent models
└── Services/             Lógica de negócio
    ├── SemanticSimilarityService   (OpenAI embeddings + cosine similarity)
    ├── WordValidator               (validação de palavras)
    ├── RoundManager                (ciclo de vida da rodada)
    └── GameService                 (criação/gerenciamento de jogos)

resources/js/
├── pages/                Telas (Home, Arena, Player)
├── components/           Componentes React
├── hooks/                Hooks (useGame, useTimer, useWebSocket, useSounds)
└── api/                  Cliente HTTP para a API
```

## Pontuação

A pontuação é calculada pela similaridade semântica entre a palavra do jogador e a palavra-tema usando embeddings da OpenAI:

- Similaridade < 30% → 0 pontos (palavra pouco relacionada)
- Similaridade 30-100% → mapeada para 0-100 pontos
- Exemplos: tema "COZINHA" → "FOGÃO" (~25pts), "FORNO" (~25pts), "PANELA" (~5pts)

## Licença

MIT
