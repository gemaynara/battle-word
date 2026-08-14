# Batalha de Palavras

## Briefing completo do produto

> 🎮 Jogo de palavras competitivo • 📺 Modo Arena • 📱 Celular como controle • ⚡ Tempo real

**Versão:** 1.0

---

## 1. Visão do produto

**Batalha de Palavras** é um jogo competitivo de partidas rápidas baseado em vocabulário, velocidade e estratégia.

O grande diferencial é o **Modo Arena**: uma TV, notebook ou projetor funciona como tela principal e vários jogadores participam usando seus celulares como controles.

O produto deve começar simples o suficiente para permitir um MVP rápido, mas com arquitetura preparada para evoluir para multiplayer em tempo real, rankings, temporadas, poderes, desafios e eventos.

### Proposta de valor

> **“Você consegue pensar mais rápido que seu adversário?”**

---

## 2. Público-alvo e contexto de uso

- Jogadores casuais que gostam de desafios rápidos.
- Pessoas que gostam de palavras, vocabulário e competição.
- Amigos e famílias jogando juntos na mesma sala.
- Escolas, professores e atividades educativas.
- Eventos, festas, bares e ambientes de entretenimento.
- Empresas em dinâmicas e ações de integração.
- Jogadores online que preferem partidas curtas.

---

## 3. O grande diferencial: Modo Arena

No **Modo Arena**, não é necessário instalar um aplicativo.

A tela principal cria uma partida e apresenta um código ou QR Code. Cada participante acessa a página pelo celular, informa o código e escolhe um apelido.

### Tela principal

- Exibe letras ou palavra da rodada.
- Exibe cronômetro.
- Exibe palavras enviadas.
- Exibe ranking ao vivo.
- Exibe animações e efeitos.
- Exibe resultado da rodada.

### Celular do jogador

- Campo para digitar palavras.
- Botão para enviar.
- Pontuação individual.
- Combo atual.
- Feedback de palavra válida/inválida.
- Histórico das próprias palavras.

> **A TV é o palco; o celular é o controle.**

---

## 4. Fluxo completo da partida

1. Criador abre o jogo e seleciona **Criar partida**.
2. O sistema gera um código curto e um QR Code.
3. Jogadores escaneiam o QR Code ou acessam a página e digitam o código.
4. Cada jogador escolhe um nickname.
5. A tela principal mostra os participantes conectados.
6. O anfitrião inicia a rodada.
7. O sistema apresenta o conjunto de letras ou palavra da rodada.
8. O cronômetro começa.
9. Jogadores digitam palavras nos celulares.
10. O backend valida cada palavra e calcula os pontos.
11. A tela principal recebe o evento em tempo real e exibe a palavra e a pontuação.
12. Ao terminar o tempo, a rodada é encerrada.
13. O placar é atualizado.
14. Novas rodadas podem começar.
15. Ao final, é apresentado o campeão e os destaques da partida.

---

## 5. Mecânica principal

### 5.1 Conjunto de letras

Os jogadores recebem exatamente o mesmo conjunto de letras.

Cada letra só pode ser utilizada na quantidade disponível para formar uma palavra.

**Exemplo:**

`M A R T E S`

Possíveis palavras:

- MAR
- META
- TERMO
- MORTE

### 5.2 Duração

Para o MVP: **60 segundos por rodada**.

### 5.3 Palavras repetidas

Uma mesma palavra só pode pontuar uma vez por jogador durante a rodada.

### 5.4 Validação

O sistema deve verificar:

- A palavra existe no dicionário?
- As letras utilizadas estão disponíveis?
- A palavra já foi enviada pelo mesmo jogador?
- A partida ainda está ativa?
- O jogador está autorizado a participar daquela partida?

---

## 6. Sistema de pontuação

| Tamanho | Pontuação |
|---|---:|
| 2 letras | 1 |
| 3 letras | 3 |
| 4 letras | 5 |
| 5 letras | 8 |
| 6 letras | 12 |
| 7 letras | 17 |
| 8+ letras | 25+ |

A pontuação deve ser calculada no backend. O frontend nunca deve ser a autoridade sobre a pontuação.

---

## 7. Combos e eventos especiais

- **Combo:** sequência de palavras válidas aumenta o multiplicador.
- **Palavra longa:** destaque para palavras com 7 ou mais letras.
- **Palavra rara:** evento especial para palavras pouco comuns.
- **Palavra perfeita:** utiliza todas as letras disponíveis.
- **Últimos 10 segundos:** mudança visual e sonora para aumentar a tensão.
- **Virada:** destaque quando um jogador ultrapassa outro no placar.

---

## 8. Estrutura da rodada

| Rodada | Exemplo | Duração |
|---|---|---:|
| 1 | MARTE | 60 s |
| 2 | COMPUTADOR | 60 s |
| 3 | ABACAXI | 60 s |
| Final | Resultado | — |

O MVP pode começar com uma única rodada de 60 segundos e depois evoluir para 3 ou 5 rodadas.

---

## 9. Experiência da tela principal

A tela principal deve ser visual, legível de longe e com poucos elementos.

```text
              M A R T E

               ⏱ 00:18

        GEANNE — MORTE +8
        JOÃO   — META +5
        ANA    — TERMO +8

        PLACAR

        GEANNE  87
        JOÃO    72
        ANA     65
```

A TV é a experiência principal; o celular funciona como controle.

---

## 10. Experiência do celular

O celular deve funcionar como um controle simples, rápido e confortável.

### Elementos

- Nickname e identificação.
- Cronômetro opcional.
- Campo de texto.
- Botão grande de enviar.
- Feedback imediato.
- Pontuação atual.
- Combo atual.
- Lista das palavras já enviadas.

```text
┌─────────────────────────┐
│       BATALHA           │
│                         │
│       ⏱️ 47             │
│                         │
│ [Digite uma palavra]    │
│                         │
│       [ ENVIAR ]        │
│                         │
│ Minhas palavras:        │
│                         │
│ ✓ MAR                   │
│ ✓ META                  │
│ ✓ AR                    │
└─────────────────────────┘
```

---

## 11. Modos de jogo

| Modo | Descrição | Prioridade |
|---|---|---|
| Arena | TV + vários celulares | MVP principal |
| Contra computador | Jogador contra bot | MVP |
| Desafio diário | Mesmo desafio para todos | 2.0 |
| Online 1x1 | Jogadores de locais diferentes | 2.0 |
| Sala privada | Código compartilhado com amigos | 2.0 |
| Game Show | Anfitrião controla rodadas | Futuro |

---

## 12. Arquitetura técnica

### Stack sugerida

- **Backend:** Laravel + PHP
- **Banco:** PostgreSQL
- **Frontend:** React + Vite
- **Tempo real:** Laravel Reverb + WebSockets
- **Infra:** aplicação web responsiva
- **Aplicativo nativo:** não é necessário inicialmente

### Fluxo

```text
📱 Celular
    ↓
API / WebSocket
    ↓
Laravel
    ↓
Validação
    ↓
Evento WordSubmitted
    ↓
WebSocket
    ↓
📺 Tela principal
```

---

## 13. Modelo de dados inicial

### `users`
Usuários cadastrados.

### `games`
Partidas e configurações.

### `game_players`
Participantes da partida.

### `game_rounds`
Rodadas, letras, duração e estado.

### `submitted_words`
Palavras enviadas pelos jogadores.

### `game_scores`
Pontuação e estatísticas da partida.

### `dictionary_words`
Dicionário e metadados das palavras.

### `daily_challenges`
Desafios diários.

### `rankings`
Classificações.

### `achievements`
Conquistas.

---

## 14. Fluxo técnico em tempo real

Quando um jogador envia uma palavra, a validação deve acontecer no servidor.

```text
Celular envia “MORTE”
        ↓
Laravel recebe
        ↓
Verifica partida
        ↓
Verifica tempo
        ↓
Verifica dicionário
        ↓
Verifica letras
        ↓
Verifica duplicidade
        ↓
Calcula pontos
        ↓
Grava resultado
        ↓
Dispara evento
        ↓
Todos os clientes recebem atualização
```

Essa abordagem evita que um jogador manipule o frontend para alterar sua pontuação.

---

## 15. Algoritmo de geração das letras

Um sorteio completamente aleatório pode produzir conjuntos ruins, como:

`Q W X Z K J`

### Estratégia recomendada

1. Selecionar uma palavra-base existente no dicionário.
2. Extrair suas letras.
3. Adicionar algumas letras complementares.
4. Embaralhar o conjunto.
5. Verificar se o conjunto permite uma quantidade mínima de palavras válidas.
6. Rejeitar conjuntos com baixa jogabilidade.
7. Salvar o conjunto.
8. Utilizar o mesmo conjunto para todos os participantes.

Isso permite controlar a dificuldade e equilibrar as partidas.

---

## 16. Dicionário e regras linguísticas

O jogo precisa de uma fonte de palavras em português e de uma camada própria de regras.

### Decisões necessárias

- Palavras válidas em português.
- Flexões de gênero e número.
- Conjugações verbais.
- Nomes próprios.
- Siglas.
- Abreviações.
- Estrangeirismos.
- Lista de exceções.
- Palavras inadequadas.
- Classificação por tamanho.
- Classificação opcional por frequência ou raridade.

---

## 17. Ranking, XP e progressão

### Ranking global
Melhores jogadores.

### Ranking semanal
Reinício periódico para manter competitividade.

### Ranking mensal
Classificação de temporada.

### XP
Recompensa por jogar e vencer.

### Níveis
Progressão do jogador.

### Conquistas

Exemplos:

- Formou 100 palavras.
- Venceu 10 partidas.
- Fez uma palavra perfeita.
- Conseguiu um combo de 10.
- Venceu uma partida com mais de 100 pontos.

---

## 18. Poderes futuros

Poderes não devem fazer parte do primeiro MVP, mas a arquitetura pode ser preparada para eles.

| Poder | Efeito |
|---|---|
| 🧨 Bomba | Remove uma letra do conjunto |
| 🔀 Embaralhar | Altera a disposição das letras |
| ⏱ Tempo Extra | Adiciona alguns segundos |
| 🔍 Dica | Sugere uma possível palavra |
| ❌ Bloqueio | Impede temporariamente o uso de uma letra |

---

## 19. Tela de resultado

```text
🏆 VOCÊ VENCEU!

GEANNE — 124 pontos
JOÃO   — 98 pontos

+100 XP

🔥 Melhor combo: 7
💎 Maior palavra: 8 letras

[ JOGAR NOVAMENTE ]
```

A tela deve incentivar imediatamente uma nova partida.

---

## 20. MVP — versão 1.0

- [ ] Criar partida.
- [ ] Gerar código da partida.
- [ ] Gerar QR Code.
- [ ] Entrada de jogadores pelo celular.
- [ ] Nickname.
- [ ] Tela Arena.
- [ ] Uma rodada de 60 segundos.
- [ ] Conjunto de letras igual para todos.
- [ ] Campo de envio de palavras.
- [ ] Validação de dicionário.
- [ ] Validação de letras.
- [ ] Bloqueio de palavras repetidas.
- [ ] Pontuação.
- [ ] Atualização em tempo real.
- [ ] Placar ao vivo.
- [ ] Tela final.
- [ ] Contra computador.
- [ ] Histórico básico.

### Foco do MVP

> **Provar que a experiência de TV + celulares é divertida antes de adicionar sistemas complexos.**

---

## 21. Roadmap

### Fase 1 — Core

Dicionário, geração de letras, validação, pontuação e rodada.

### Fase 2 — Arena

Sala, QR Code, celulares, TV e WebSockets.

### Fase 3 — Produto

Login, perfil, histórico, ranking e desafio diário.

### Fase 4 — Competitivo

Matchmaking, multiplayer online e ranking ELO.

### Fase 5 — Retenção

XP, níveis, conquistas, missões e temporadas.

### Fase 6 — Monetização

Anúncios e itens cosméticos.

### Fase 7 — Diferenciação

Poderes, torneios, eventos, clãs e modo Game Show.

---

## 22. Monetização

Possibilidades:

- Versão gratuita com partidas e desafios.
- Anúncios não invasivos.
- Plano premium para remover anúncios.
- Temas visuais.
- Avatares e molduras.
- Efeitos de vitória.
- Personalização da Arena.
- Eventos especiais.
- Torneios.

### Princípio

Evitar vender vantagens competitivas diretamente. O ideal é monetizar principalmente por personalização e conveniência.

---

## 23. Oportunidades de expansão

O Modo Arena permite transformar o jogo em uma experiência para:

- Festas e eventos.
- Escolas.
- Salas de aula.
- Empresas.
- Dinâmicas de equipe.
- Bares.
- Torneios presenciais.
- Eventos corporativos.
- Game shows.
- Desafios patrocinados.

Também podem ser criadas categorias temáticas:

- Filmes.
- Música.
- Tecnologia.
- Geografia.
- Esportes.
- História.
- Cultura pop.

---

## 24. Visão do produto final

A visão de longo prazo é transformar Batalha de Palavras em uma plataforma de jogos linguísticos, e não apenas em um jogo isolado.

```text
📺 UMA TELA
      +
📱 VÁRIOS CELULARES
      +
⚡ TEMPO REAL
      +
🧠 PALAVRAS
      +
🏆 COMPETIÇÃO
```

O **Modo Arena** pode ser o principal diferencial porque cria uma experiência presencial coletiva sem exigir instalação de aplicativos.

O jogador simplesmente aponta a câmera para um QR Code, entra na partida e começa a competir.

---

## 25. Primeira versão recomendada

1. Definir e importar o dicionário de palavras.
2. Criar algoritmo de geração de conjuntos de letras jogáveis.
3. Criar motor de validação.
4. Criar cálculo de pontuação.
5. Criar entidade de partida e rodada.
6. Criar tela Arena.
7. Criar tela Player para celular.
8. Implementar Laravel Reverb/WebSockets.
9. Implementar envio de palavras em tempo real.
10. Implementar placar.
11. Implementar encerramento e resultado.
12. Testar uma partida com vários celulares conectados à mesma TV.

Depois desse ponto teremos um **protótipo jogável real** e será possível testar a diversão do conceito antes de investir nas funcionalidades secundárias.

---

# Resumo executivo

**Batalha de Palavras** será um jogo de palavras competitivo com foco em partidas rápidas e interação em tempo real.

Seu principal diferencial será o **Modo Arena**, no qual:

- uma TV ou projetor exibe a partida;
- cada jogador usa o próprio celular;
- os jogadores entram por QR Code;
- as palavras são enviadas pelo celular;
- a validação acontece no backend;
- as palavras aparecem instantaneamente na tela;
- o placar é atualizado em tempo real;
- a partida pode envolver vários jogadores simultaneamente.

A primeira meta deve ser construir uma experiência funcional e divertida com **Laravel + React + PostgreSQL + Laravel Reverb**, mantendo o produto simples no início e preparado para evoluir para multiplayer, ranking, temporadas, poderes, torneios e monetização.
