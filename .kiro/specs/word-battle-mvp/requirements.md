# Requirements Document

## Introduction

Batalha de Palavras (Word Battle) é um jogo competitivo de palavras em tempo real com Modo Arena, onde uma TV/projetor exibe a partida e jogadores participam usando seus celulares como controles via QR Code. O MVP foca em provar que a experiência TV + celulares é divertida, implementando uma partida funcional com uma rodada de 60 segundos, validação de palavras, pontuação e atualização em tempo real via WebSocket.

Stack técnica: Laravel + PHP, PostgreSQL, React + Vite, Laravel Reverb (WebSockets).

## Glossary

- **Game_Engine**: O sistema backend responsável por gerenciar partidas, validar palavras e calcular pontuação
- **Arena_Screen**: A interface exibida na TV/projetor que mostra o estado da partida para todos os participantes
- **Player_Screen**: A interface exibida no celular do jogador para submissão de palavras e feedback individual
- **Game_Code**: Código alfanumérico curto de 6 caracteres gerado para identificar uma partida ativa
- **Letter_Set**: Conjunto de letras derivado de uma palavra-base do dicionário, compartilhado por todos os jogadores na rodada
- **Base_Word**: Palavra existente no dicionário usada como fonte para gerar o Letter_Set da rodada
- **Word_Validator**: Componente do sistema que verifica se uma palavra submetida é válida conforme as regras do jogo
- **Scoring_Engine**: Componente responsável pelo cálculo de pontos baseado no tamanho da palavra
- **WebSocket_Server**: Laravel Reverb, responsável por transmitir eventos em tempo real para todos os clientes conectados
- **Bot_Player**: Jogador controlado por computador que simula um adversário humano
- **Host**: O jogador que cria e controla a partida (inicia rodadas, gerencia participantes)
- **Round**: Período de 60 segundos durante o qual jogadores submetem palavras

## Requirements

### Requirement 1: Game Creation

**User Story:** As a Host, I want to create a new game session, so that other players can join and compete.

#### Acceptance Criteria

1. WHEN the Host requests game creation, THE Game_Engine SHALL generate a unique 6-character alphanumeric Game_Code using only the characters A-Z (excluding O, I, L) and digits 2-9 (excluding 0, 1)
2. WHEN the Host requests game creation, THE Game_Engine SHALL generate a QR Code containing the URL for players to join the game using the Game_Code
3. WHEN a game is created, THE Game_Engine SHALL set the game status to "waiting", set the game mode to "arena" by default, and register the Host as the first player with is_host = true
4. THE Game_Engine SHALL ensure the Game_Code is unique among all active games (status "waiting" or "playing") by retrying generation up to 5 times before failing
5. IF the Game_Engine cannot generate a unique Game_Code after 5 attempts, THEN THE Game_Engine SHALL reject the game creation request with an error message indicating code generation failure
6. WHEN a game is created, THE Arena_Screen SHALL display the Game_Code in large high-contrast characters readable from 3 meters distance and the QR Code for participants to scan or enter the code manually

### Requirement 2: Player Join

**User Story:** As a player, I want to join a game using my phone, so that I can compete against other players.

#### Acceptance Criteria

1. WHEN a player accesses the join URL (via QR Code or manual code entry), THE Player_Screen SHALL prompt for a nickname between 2 and 30 characters, accepting only letters (A-Z, a-z), numbers (0-9), spaces, and underscores
2. WHEN a player submits a valid nickname for a game in "waiting" status, THE Game_Engine SHALL register the player, set the connection status to connected, and record the joined_at timestamp
3. IF a player submits a nickname already in use within the same game (case-insensitive comparison), THEN THE Game_Engine SHALL reject the join attempt with an error message indicating the nickname is already taken
4. IF a player attempts to join a game that is not in "waiting" status, THEN THE Game_Engine SHALL reject the join attempt with an error message indicating the game is no longer accepting players
5. IF a player attempts to join a game that has reached max_players, THEN THE Game_Engine SHALL reject the join attempt with an error message indicating the game is full
6. WHEN a new player joins, THE Arena_Screen SHALL display the updated list of connected participants within 2 seconds via WebSocket
7. WHEN a player successfully joins the game, THE Player_Screen SHALL display a confirmation with the game code and a message indicating the player is waiting for the Host to start the round

### Requirement 3: Letter Set Generation

**User Story:** As a Host, I want the system to generate a fair set of letters, so that all players have equal opportunity to form words.

#### Acceptance Criteria

1. WHEN a round starts, THE Game_Engine SHALL randomly select a Base_Word from the dictionary that has a length between 5 and 12 characters, with is_valid = true and is_inappropriate = false
2. WHEN a round starts, THE Game_Engine SHALL extract the letters from the Base_Word to form the Letter_Set
3. WHEN a Letter_Set is generated, THE Game_Engine SHALL verify that the Letter_Set allows a minimum of 10 valid words formable from the available letters
4. IF a generated Letter_Set does not meet the minimum valid word threshold, THEN THE Game_Engine SHALL discard the set and generate a new one, up to a maximum of 10 attempts
5. IF the Game_Engine fails to generate a valid Letter_Set after 10 attempts, THEN THE Game_Engine SHALL select the last generated Letter_Set and proceed with the round
6. WHEN a valid Letter_Set is confirmed, THE Game_Engine SHALL provide the identical Letter_Set to all players in the same round via WebSocket

### Requirement 4: Round Lifecycle

**User Story:** As a Host, I want to start a round and have it run for a fixed duration, so that the game has clear timing.

#### Acceptance Criteria

1. WHEN the Host initiates round start for a round in "waiting" status, THE Game_Engine SHALL set the round status to "playing", record the start timestamp (started_at), and begin the server-authoritative 60-second countdown
2. WHILE a round is in "playing" status, THE Arena_Screen SHALL display the countdown timer updated every second, synchronized with the server start timestamp
3. WHEN the server-calculated elapsed time since started_at reaches 60 seconds, THE Game_Engine SHALL set the round status to "finished", record the finished_at timestamp, and reject any word submissions received after the status change
4. WHEN the round status changes to "finished", THE Game_Engine SHALL broadcast the round-ended event containing the round_number and final status to all connected clients via WebSocket
5. WHILE a round is in "playing" status, THE Player_Screen SHALL display the remaining time to the player, derived from the server-provided start timestamp
6. IF the Host attempts to start a round that is not in "waiting" status, THEN THE Game_Engine SHALL reject the request with an error message indicating the round cannot be started

### Requirement 5: Word Submission

**User Story:** As a player, I want to submit words from my phone during the round, so that I can earn points.

#### Acceptance Criteria

1. WHILE a round is active, WHEN a player submits a word, THE Game_Engine SHALL receive the submission and process validation within 500 milliseconds, accepting words between 2 and 50 characters in length
2. WHEN a word is submitted, THE Game_Engine SHALL normalize the word to uppercase and trim whitespace before validation; IF the resulting word is empty or shorter than 2 characters, THEN THE Game_Engine SHALL reject the submission with an indication that the word is too short
3. WHEN a valid word is accepted, THE Player_Screen SHALL display a checkmark icon and the points earned next to the submitted word within 1 second of server response
4. WHEN a word is rejected, THE Player_Screen SHALL display the rejection reason (not in dictionary, invalid letters, duplicate, too short, or time expired) within 1 second of server response
5. WHEN a word is submitted, THE Player_Screen SHALL display the word in the player's submitted words history with its validation status, showing the most recent submissions first
6. WHILE a round is active, THE Game_Engine SHALL accept a maximum of 1 word submission per player per second; IF a player exceeds this rate, THEN THE Game_Engine SHALL reject the excess submissions with an indication of rate limiting

### Requirement 6: Word Validation

**User Story:** As a player, I want fair word validation, so that only legitimate words earn points.

#### Acceptance Criteria

1. WHEN a word is submitted, THE Word_Validator SHALL check that the word exists in the dictionary_words table with is_valid = true and is_inappropriate = false
2. WHEN a word is submitted, THE Word_Validator SHALL verify that each letter in the word is available in the Letter_Set, respecting the exact quantity of each letter (a letter appearing twice in the word requires at least two occurrences in the Letter_Set)
3. WHEN a word is submitted, THE Word_Validator SHALL verify that the same player has not already submitted the same word in the same round
4. WHEN a word is submitted, THE Word_Validator SHALL verify that the current round is still in "playing" status and that the elapsed time since started_at has not exceeded duration_seconds
5. WHEN a word is submitted, THE Word_Validator SHALL verify that the submitting player is a registered participant of the game with is_connected = true
6. IF any validation check fails, THEN THE Word_Validator SHALL record the first failing rejection reason from the following set: "time_expired", "invalid_letters", "not_in_dictionary", "duplicate" in the submitted_words record and return zero points for that submission
7. WHEN a word is submitted, THE Word_Validator SHALL reject the word with rejection reason "invalid_letters" if the word length is less than 2 characters or exceeds the number of letters in the Letter_Set
8. THE Word_Validator SHALL execute validation checks in the following priority order: time/round status, player participation, minimum/maximum length, letter availability, dictionary lookup, duplicate check

### Requirement 7: Scoring

**User Story:** As a player, I want my score calculated fairly based on word length, so that I am rewarded for finding longer words.

#### Acceptance Criteria

1. WHEN a valid word is accepted, THE Scoring_Engine SHALL calculate points based on word length: 2 letters = 1 point, 3 letters = 3 points, 4 letters = 5 points, 5 letters = 8 points, 6 letters = 12 points, 7 letters = 17 points, 8 or more letters = 25 points
2. WHEN a valid word is accepted, THE Scoring_Engine SHALL atomically increment the player's total_score in the game_players record by the total_points value (points multiplied by combo_multiplier) and store the points, combo_multiplier, and total_points in the submitted_words record
3. THE Scoring_Engine SHALL perform all score calculations exclusively on the backend; the frontend SHALL only display score values received from the backend and SHALL NOT compute or alter scores locally
4. WHEN a valid word uses every letter instance in the Letter_Set (including duplicate occurrences of the same letter), THE Scoring_Engine SHALL flag the submission as a perfect word (is_perfect_word = true) and award a bonus of 10 additional points added to the base word-length points before applying the combo multiplier
5. WHEN a player submits consecutive valid words without any rejected submission in between during the same round, THE Scoring_Engine SHALL increment the combo_multiplier by 1 for each consecutive valid word starting at 1 for the first valid word, up to a maximum combo_multiplier of 5
6. IF a player submits an invalid word, THEN THE Scoring_Engine SHALL reset that player's combo_multiplier to 1 for the next submission
7. IF a score update fails due to a persistence error, THEN THE Scoring_Engine SHALL retry the update once and, if the retry also fails, reject the word submission and return an error indication to the player without altering the player's total_score

### Requirement 8: Real-Time Updates via WebSocket

**User Story:** As a spectator or player watching the Arena Screen, I want to see live updates, so that the game feels dynamic and engaging.

#### Acceptance Criteria

1. WHEN a player submits a valid word, THE WebSocket_Server SHALL broadcast the word, player nickname, and points earned to all connected clients in the same game within 1 second
2. WHEN a player's score changes, THE WebSocket_Server SHALL broadcast the updated scoreboard (all players with nicknames, scores, and ranking positions) to all connected clients in the same game within 1 second
3. WHEN a player joins or explicitly leaves, or WHEN a client connection is lost for more than 5 seconds, THE WebSocket_Server SHALL broadcast the updated player list with connection statuses to all connected clients in the same game within 1 second
4. WHEN the round starts or ends, THE WebSocket_Server SHALL broadcast the round state change (new status, Letter_Set on start, final scores on end) to all connected clients in the same game within 1 second
5. THE WebSocket_Server SHALL maintain persistent connections using Laravel Reverb for both Arena_Screen and Player_Screen clients, scoped to a game-specific channel identified by the Game_Code
6. IF a client loses its WebSocket connection, THEN THE WebSocket_Server SHALL allow the client to reconnect and rejoin the game-specific channel within 30 seconds without requiring the player to re-enter the Game_Code or nickname

### Requirement 9: Live Scoreboard

**User Story:** As a player or spectator, I want to see the live scoreboard on the Arena Screen, so that I can follow the competition in real time.

#### Acceptance Criteria

1. WHILE a round is active, THE Arena_Screen SHALL display all players ranked by total_score in descending order, using earliest score update timestamp as tiebreaker when players have equal scores
2. WHEN a player's score is updated, THE Arena_Screen SHALL re-render the scoreboard within 1 second of receiving the WebSocket event, visually highlighting the player whose rank changed
3. WHILE a round is active, THE Arena_Screen SHALL display each player's nickname, total score, and the last valid word submitted by that player; IF a player has not yet submitted a valid word, THEN THE Arena_Screen SHALL display a dash placeholder in the last word column
4. WHEN the round finishes, THE Arena_Screen SHALL display the final standings showing each player's numeric position (1st, 2nd, 3rd, etc.), nickname, and total score

### Requirement 10: End Screen

**User Story:** As a player, I want to see the final results after the round ends, so that I know who won and how I performed.

#### Acceptance Criteria

1. WHEN the round finishes, THE Arena_Screen SHALL display within 3 seconds the winner (player with highest total_score), final positions of all players ranked by total_score descending, and individual scores. IF two or more players have the same total_score, THEN THE Game_Engine SHALL rank the player who submitted fewer valid words higher (greater efficiency); if still tied, the player who joined first SHALL be ranked higher.
2. WHEN the round finishes, THE Arena_Screen SHALL display game highlights: the player with the highest best_combo value, the longest valid word submitted across all players, and the count of valid words per player
3. WHEN the round finishes, THE Player_Screen SHALL display within 3 seconds the individual player's final score, position among all players, and personal statistics (total valid words submitted, longest valid word)
4. WHEN the Host selects the "Play Again" option on the Arena_Screen, THE Game_Engine SHALL create a new round within the same game, reset all players' round scores to zero, generate a new Letter_Set, and transition the game back to "waiting" status for the Host to start the next round
5. IF the Host has disconnected when the round finishes, THEN THE Arena_Screen SHALL display the end screen results without the "Play Again" option until the Host reconnects

### Requirement 11: Bot Opponent Mode

**User Story:** As a player, I want to play against a computer-controlled opponent, so that I can practice or play alone.

#### Acceptance Criteria

1. WHEN the Host creates a game in "vs_computer" mode, THE Game_Engine SHALL add a Bot_Player to the game with is_bot = true and a predefined nickname
2. WHILE a round is active, THE Bot_Player SHALL submit valid words from the available Letter_Set at randomized intervals between 3 and 8 seconds, submitting no more than 12 words per round
3. THE Bot_Player SHALL select words from the dictionary that can be formed with the current Letter_Set, with word lengths distributed between 2 and 5 letters for at least 70% of submissions and 6 or more letters for no more than 30% of submissions
4. THE Bot_Player SHALL submit no more than 50% of all possible valid words for the given Letter_Set, and SHALL NOT always select the longest available words first
5. WHEN the round ends, THE Game_Engine SHALL calculate the Bot_Player's score using the same Scoring_Engine rules as human players
6. WHEN the Bot_Player submits a word, THE Game_Engine SHALL validate the submission through the same Word_Validator rules applied to human players, including duplicate checking within the same round

### Requirement 12: Game History

**User Story:** As a player, I want to see my past game results, so that I can track my progress.

#### Acceptance Criteria

1. WHEN a game finishes, THE Game_Engine SHALL persist the final scores, positions, and game metadata (mode, date, duration, Letter_Set used) for each participant
2. WHEN a player requests game history, THE Game_Engine SHALL return the list of completed games the player participated in, ordered by most recent first, paginated in groups of 20 results per page
3. THE Game_Engine SHALL store each game's detail: final score, position, total words submitted, longest word, and opponent information (nicknames, final scores, and positions of all other participants)
4. IF a player requests game history and has no completed games, THEN THE Game_Engine SHALL return an empty list with zero total results
5. WHEN a player requests game history, THE Game_Engine SHALL require the player to be authenticated and SHALL return only games associated with that player's account

### Requirement 13: Arena Screen Display

**User Story:** As a Host projecting the game on a TV, I want the Arena Screen to be visually clear and readable from a distance, so that all participants can follow the game.

#### Acceptance Criteria

1. THE Arena_Screen SHALL display the Letter_Set with a minimum font size of 72px and a minimum contrast ratio of 7:1 against the background, ensuring readability from 3 meters distance
2. WHILE a round is active, THE Arena_Screen SHALL display the last 5 valid word submissions in chronological order (newest first), showing the submitter's nickname and points earned for each
3. WHEN the game status changes between "waiting", "playing", and "finished", THE Arena_Screen SHALL transition to the corresponding display layout within 1 second of receiving the state-change event via WebSocket
4. WHILE in "waiting" status, THE Arena_Screen SHALL display the Game_Code, QR Code, and list of connected players updated in real time via WebSocket
5. WHILE a round is active, THE Arena_Screen SHALL display the Letter_Set, the countdown timer, the live scoreboard, and the recent word submissions simultaneously without requiring scrolling

### Requirement 14: Player Screen Interface

**User Story:** As a player on my phone, I want a simple and responsive interface, so that I can quickly type and submit words.

#### Acceptance Criteria

1. THE Player_Screen SHALL provide a text input field and a submit button with a minimum tap target size of 44x44 pixels, with the input field and button visible without scrolling when the on-screen keyboard is open
2. WHEN a player submits a word, THE Player_Screen SHALL clear the text input field and refocus it for the next submission
3. THE Player_Screen SHALL display the player's current score, a scrollable list of submitted words with validation status (accepted or rejected with reason), and the remaining round time in seconds
4. THE Player_Screen SHALL support word submission via both the submit button and the Enter key on the keyboard
5. THE Player_Screen SHALL render all interactive elements and content without horizontal scrolling on mobile devices with viewport widths from 320px to 428px
6. IF a player attempts to submit an empty or whitespace-only input, THEN THE Player_Screen SHALL prevent the submission and keep focus on the text input field without sending a request to the Game_Engine
7. THE Player_Screen SHALL limit the text input field to a maximum of 15 characters
