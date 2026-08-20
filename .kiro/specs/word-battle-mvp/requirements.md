# Requirements Document

## Introduction

Batalha de Palavras é um jogo de associação semântica de palavras em tempo real. O jogo lança uma palavra-tema e os jogadores digitam palavras relacionadas ao tema para ganhar pontos. A pontuação é baseada na similaridade semântica calculada via embeddings de IA. O jogo oferece modo solo e multiplayer com sistema de ranking semanal.

Stack técnica: Laravel 12 + PHP 8.3, PostgreSQL/MySQL, React + TypeScript + Vite, Laravel Reverb (WebSockets), OpenAI Embeddings API.

## Glossary

- **Palavra-Tema**: Palavra comum do dicionário pt-BR exibida como tema da rodada. Os jogadores devem digitar palavras relacionadas a ela.
- **Similaridade Semântica**: Grau de relação de significado entre duas palavras, calculado via embeddings vetoriais (cosine similarity).
- **Arena_Screen**: Interface exibida na TV/projetor para modo multiplayer com placar ao vivo.
- **Player_Screen**: Interface mobile onde o jogador digita palavras e vê feedback.
- **Ranking Semanal**: Top 10 jogadores da semana baseado na melhor pontuação individual de uma rodada.
- **Time Bonus**: +5 segundos adicionados ao timer a cada palavra válida aceita.

## Requirements

### Requirement 1: Game Modes

**User Story:** Como jogador, quero escolher entre jogar sozinho ou com amigos para ter flexibilidade.

#### Acceptance Criteria

1. WHEN o jogador acessa a tela inicial, THE sistema SHALL exibir opções de "Jogar Sozinho" e "Jogar com Amigos"
2. WHEN o jogador escolhe "Jogar Sozinho", THE sistema SHALL criar o jogo, iniciar a rodada automaticamente e redirecionar para a tela de jogo
3. WHEN o jogador escolhe "Jogar com Amigos", THE sistema SHALL criar uma sala com código e QR Code para outros jogadores entrarem
4. BEFORE de jogar, THE sistema SHALL solicitar um apelido (nickname) com 2-20 caracteres
5. THE sistema SHALL persistir o nickname no localStorage para reutilização entre sessões

### Requirement 2: Round Mechanics

**User Story:** Como jogador, quero que cada rodada tenha uma palavra-tema e tempo limitado para manter o desafio.

#### Acceptance Criteria

1. WHEN uma rodada inicia, THE sistema SHALL selecionar uma palavra-tema aleatória do dicionário (4-7 caracteres, preferencialmente palavras com categoria definida)
2. WHEN uma rodada inicia, THE timer SHALL começar em 30 segundos
3. WHEN o jogador acerta uma palavra (pontuação > 0), THE timer SHALL incrementar +5 segundos
4. WHEN o timer chega a 0, THE rodada SHALL finalizar e exibir a tela de resultados
5. THE palavra-tema SHALL ser exibida de forma proeminente na tela do jogador
6. THE jogador NÃO PODE submeter a própria palavra-tema como resposta

### Requirement 3: Word Validation

**User Story:** Como jogador, quero que minhas palavras sejam validadas de forma justa.

#### Acceptance Criteria

1. WHEN uma palavra é submetida, THE sistema SHALL verificar que a palavra existe no dicionário pt-BR (tabela dictionary_words com is_valid = true)
2. WHEN uma palavra é submetida, THE sistema SHALL verificar que não é a mesma palavra-tema
3. WHEN uma palavra é submetida, THE sistema SHALL verificar que o jogador não submeteu a mesma palavra nesta rodada
4. WHEN uma palavra é submetida, THE sistema SHALL verificar que a rodada ainda está ativa (timer > 0)
5. THE sistema SHALL aceitar palavras com mínimo de 2 caracteres
6. THE sistema SHALL rejeitar palavras que não existem no dicionário com feedback "Palavra não encontrada no dicionário"

### Requirement 4: Semantic Scoring

**User Story:** Como jogador, quero ser pontuado pela qualidade da associação semântica.

#### Acceptance Criteria

1. WHEN uma palavra válida é aceita, THE sistema SHALL calcular a similaridade semântica entre a palavra submetida e a palavra-tema usando OpenAI Embeddings (text-embedding-3-small)
2. THE pontuação SHALL ser calculada como: similaridade normalizada de 0-100 pontos (threshold mínimo de 30% de similaridade)
3. IF a similaridade é menor que 30%, THEN a pontuação SHALL ser 0 e a palavra é marcada como "pouco relacionada"
4. THE sistema SHALL cachear embeddings por 7 dias para reduzir chamadas à API
5. WHEN uma palavra pontua 50+, THE sistema SHALL tocar um som especial e exibir "Excelente!"

### Requirement 5: Ranking System

**User Story:** Como jogador, quero ver um ranking semanal para competir com outros.

#### Acceptance Criteria

1. THE sistema SHALL manter um ranking semanal com a melhor pontuação de cada jogador (por nickname)
2. THE ranking SHALL exibir os top 10 jogadores na tela inicial
3. WHEN um jogador pontua, THE sistema SHALL atualizar o ranking se a pontuação for maior que o melhor score anterior na semana
4. THE ranking SHALL resetar automaticamente toda semana (baseado em week_key formato YYYY-Wnn)
5. THE sistema SHALL exibir posição, nickname e pontuação no ranking

### Requirement 6: Game UX

**User Story:** Como jogador, quero uma experiência fluida e gamificada.

#### Acceptance Criteria

1. WHEN uma palavra é aceita, THE sistema SHALL exibir uma animação de pontos flutuantes subindo na tela
2. WHEN a rodada finaliza com pontos, THE sistema SHALL exibir confetti e título "Parabéns!"
3. WHEN a rodada finaliza sem pontos, THE sistema SHALL exibir mensagem triste com título "Tempo esgotado!" e emoji 😅
4. THE sistema SHALL tocar sons sintetizados: acerto (arpejo ascendente), erro (buzz), pontuação alta (fanfarra), fim com pontos (celebração), fim sem pontos (trombone triste)
5. THE Player_Screen SHALL ser responsivo e funcionar em telas de 320px a 428px de largura
6. THE input de palavra SHALL ter font-size de 16px para evitar zoom no iOS
7. THE jogador SHALL ter um botão de "Abandonar" para voltar à tela inicial durante o jogo

### Requirement 7: Dictionary

**User Story:** Como jogador, quero que o dicionário contenha palavras que eu conheço.

#### Acceptance Criteria

1. THE dicionário SHALL conter ~26.000 palavras comuns do português brasileiro filtradas por frequência (ICF ≤ 14 do repositório fserb/pt-br)
2. THE dicionário SHALL incluir palavras do cotidiano (eletrodomésticos, roupas, alimentos, tecnologia, etc.) adicionadas manualmente
3. THE dicionário SHALL ter palavras categorizadas (animais, alimentos, natureza, objetos, verbos, adjetivos, profissões) para seleção de temas
4. THE sistema SHALL normalizar palavras removendo acentos e convertendo para maiúscula antes de armazenar e validar
5. AS palavras-tema SHALL ser selecionadas preferencialmente de palavras com categoria definida ou de uma lista fixa de palavras simples do cotidiano

### Requirement 8: Multiplayer (Arena Mode)

**User Story:** Como host, quero projetar o jogo na TV para todos acompanharem.

#### Acceptance Criteria

1. THE Arena_Screen SHALL exibir código do jogo e QR Code na sala de espera
2. THE Arena_Screen SHALL exibir a palavra-tema, timer, placar e últimas palavras durante o jogo
3. THE sistema SHALL suportar até 10 jogadores simultâneos por sala
4. THE WebSocket SHALL transmitir eventos em tempo real (jogador entrou, palavra submetida, placar atualizado, rodada finalizada)
5. THE host SHALL ter botão "Iniciar Rodada" e "Encerrar Sala"
6. THE sala de espera SHALL exibir as regras do jogo de forma clara
