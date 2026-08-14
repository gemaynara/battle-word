<?php

namespace Database\Seeders;

use App\Models\DictionaryWord;
use Illuminate\Database\Seeder;

class CategorizedDictionarySeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->getCategories();
        $total = 0;

        foreach ($categories as $category => $words) {
            $chunks = array_chunk($words, 100);
            foreach ($chunks as $chunk) {
                $records = array_map(function (string $word) use ($category) {
                    $normalized = mb_strtoupper(trim($word));
                    return [
                        'word' => $normalized,
                        'length' => mb_strlen($normalized),
                        'is_valid' => true,
                        'is_inappropriate' => false,
                        'category' => $category,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $chunk);
                DictionaryWord::upsert($records, ['word'], ['length', 'category', 'updated_at']);
            }
            $total += count($words);
            $this->command->info("  [{$category}] " . count($words) . ' palavras');
        }

        $this->command->info("Total: {$total} palavras em " . count($categories) . ' categorias.');
    }

    private function getCategories(): array
    {
        return [
            'substantivos' => $this->getSubstantivos(),
            'cidades' => $this->getCidades(),
            'filmes' => $this->getFilmes(),
            'animais' => $this->getAnimais(),
            'comidas' => $this->getComidas(),
            'profissoes' => $this->getProfissoes(),
            'esportes' => $this->getEsportes(),
            'natureza' => $this->getNatureza(),
        ];
    }

    private function getSubstantivos(): array
    {
        return [
            // 2-3 letras
            'AR', 'AS', 'AZ', 'CU', 'DO', 'EM', 'EU', 'JA', 'LA', 'MA',
            'NO', 'OS', 'PA', 'PE', 'PI', 'RE', 'RI', 'SI', 'TA', 'TI',
            'TO', 'TU', 'VA', 'VI', 'ABA', 'ACO', 'ALA', 'AMO', 'ANO', 'ARA',
            'ASA', 'ATO', 'AVE', 'AVO', 'BAR', 'BOI', 'CAL', 'CAS', 'CHA', 'COR',
            'CRU', 'DIA', 'DOM', 'ECO', 'EGO', 'ELA', 'ERA', 'FAZ', 'FIM', 'FIO',
            'GOL', 'IDA', 'IRA', 'LAR', 'LEI', 'LER', 'LUA', 'LUZ', 'MAE', 'MAL',
            'MAR', 'MAS', 'MEL', 'MES', 'MIL', 'NAO', 'NEM', 'NOZ', 'OCA', 'OVO',
            'PAI', 'PAR', 'PAZ', 'POR', 'REI', 'RIO', 'RUA', 'RUM', 'SAL', 'SER',
            'SOL', 'SOM', 'TAL', 'TIA', 'TIO', 'TOM', 'UVA', 'VAI', 'VER', 'VEZ',
            'VIA', 'VOZ',
            // 4 letras
            'AGUA', 'ALMA', 'ALTO', 'AMOR', 'ANEL', 'ARCO', 'ARMA', 'ASAS', 'AVES',
            'AZUL', 'BALA', 'BELO', 'BICA', 'BICO', 'BOCA', 'BOLA', 'BOTE', 'CABO',
            'CAFE', 'CAMA', 'CANA', 'CANO', 'CAPA', 'CARA', 'CASA', 'CASO', 'CEDO',
            'CENA', 'CERA', 'CIMO', 'COLA', 'CONE', 'COPA', 'COPO', 'COTA', 'CUBO',
            'DADO', 'DANO', 'DATA', 'DEDO', 'DOCE', 'DOIS', 'DONO', 'DOSE', 'DURA',
            'ERVA', 'FACA', 'FALA', 'FAME', 'FASE', 'FATO', 'FERA', 'FILA', 'FINO',
            'FOGO', 'FOME', 'FORA', 'GALO', 'GATO', 'GELO', 'GEMA', 'GOTA', 'GUIA',
            'HORA', 'ILHA', 'JOGO', 'JOIA', 'JURO', 'LADO', 'LAGO', 'LAMA', 'LATA',
            'LEMA', 'LEVE', 'LIMA', 'LISO', 'LOBO', 'LOJA', 'LOTE', 'LUVA', 'MACA',
            'MAGO', 'MALA', 'MAPA', 'MATO', 'MEDO', 'MEIA', 'MESA', 'META', 'MINA',
            'MODA', 'MOLA', 'MOLE', 'MURO', 'NATA', 'NAVE', 'NETA', 'NETO', 'NEVE',
            'NADA', 'NOME', 'NOTA', 'NOVO', 'NUCA', 'OBRA', 'OITO', 'OLEO', 'OLHO',
            'ONDE', 'ORCA', 'OSSO', 'OURO', 'ONDA', 'PANO', 'PARA', 'PATA', 'PELE',
            'PENA', 'PERA', 'PESO', 'PICO', 'PINO', 'PISO', 'POLO', 'POSE', 'POTE',
            'PULO', 'RAMO', 'RATO', 'REDE', 'REIS', 'REMO', 'RENA', 'RICO', 'RISO',
            'RODA', 'ROLO', 'ROSA', 'ROTA', 'RUMO', 'SALA', 'SAPO', 'SECO', 'SEDE',
            'SEIS', 'SELO', 'SETE', 'SILO', 'SINO', 'SOCO', 'SOPA', 'SORO', 'SUMO',
            'TACO', 'TAPA', 'TEIA', 'TELA', 'TEMA', 'TETO', 'TIPO', 'TIRO', 'TOCA',
            'TODA', 'TOPO', 'TORA', 'TRIO', 'TUBO', 'URNA', 'URSO', 'VACA', 'VAGA',
            'VALE', 'VARA', 'VASO', 'VEIA', 'VELA', 'VIDA', 'VILA', 'VIVO', 'VOTO',
            'ZONA',
            // 5 letras
            'ABRIR', 'ABRIL', 'AINDA', 'ALUNO', 'AMIGO', 'ANDAR', 'ANTES', 'APOIO',
            'AREIA', 'BAIXO', 'BANCO', 'BARCO', 'BARRA', 'BEIRA', 'BICHO', 'BOLSA',
            'BRACO', 'BRASA', 'BREVE', 'BURRO', 'CAIXA', 'CALMA', 'CALOR', 'CAMPO',
            'CANTO', 'CARNE', 'CARTA', 'CAUSA', 'CERCA', 'CERCO', 'CHAMA', 'CHAVE',
            'CLARO', 'COBRA', 'COLAR', 'COMER', 'CONTA', 'CONTO', 'CORDA', 'CORPO',
            'COSTA', 'CRIME', 'CRISE', 'DANCA', 'DESDE', 'DIETA', 'DIZER', 'DRAMA',
            'ENTRE', 'EXATO', 'FARDO', 'FAROL', 'FATOR', 'FEITO', 'FERRO', 'FESTA',
            'FIBRA', 'FINAL', 'FIRME', 'FLORA', 'FOLHA', 'FORMA', 'FORTE', 'FRASE',
            'FRUTA', 'GARRA', 'GERAL', 'GESTO', 'GLOBO', 'GOLPE', 'GRADE', 'GRUPO',
            'IDEIA', 'IDADE', 'IGUAL', 'JOVEM', 'JUIZO', 'JUSTO', 'LAPIS', 'LARGO',
            'LEITE', 'LEVAR', 'LIDAR', 'LIMPO', 'LISTA', 'LIVRO', 'LUCRO', 'LUGAR',
            'MAGRO', 'MAIOR', 'MANGA', 'MARCA', 'MARCO', 'MATAR', 'MEDIA', 'MEDIR',
            'MENOR', 'MESES', 'METRO', 'MILHO', 'MINHA', 'MOEDA', 'MONTE', 'MORAL',
            'MORAR', 'MOTOR', 'MUITO', 'MUNDO', 'MUSEU', 'NADAR', 'NAVIO', 'NOBRE',
            'NOITE', 'NOSSO', 'NUVEM', 'NUNCA', 'OLHAR', 'ORDEM', 'OUVIR', 'PADRE',
            'PALCO', 'PAPEL', 'PARAR', 'PASSO', 'PASTA', 'PATIO', 'PEDRA', 'PEGAR',
            'PEITO', 'PERDA', 'PERNA', 'PESAR', 'PIADA', 'PISTA', 'PLACA', 'PLANO',
            'PLUMA', 'PODER', 'POEMA', 'POMAR', 'PONTO', 'PORTA', 'POSSE', 'PRAZO',
            'PRECO', 'PRIMO', 'PROVA', 'PULSO', 'QUEDA', 'RAIVA', 'RAPAZ', 'RAZAO',
            'REDOR', 'REGAR', 'RENDA', 'RISCO', 'RITMO', 'ROCHA', 'ROLAR', 'ROUPA',
            'RUIDO', 'SABOR', 'SAFRA', 'SALTO', 'SANTO', 'SERIE', 'SINAL', 'SOBRE',
            'SONHO', 'SORTE', 'SUBIR', 'SUAVE', 'SURDO', 'TANTO', 'TARDE', 'TERRA',
            'TEXTO', 'TIGRE', 'TINTA', 'TOCAR', 'TOTAL', 'TRAGO', 'TROCA', 'TURMA',
            'TURNO', 'UNICO', 'UNIAO', 'USADO', 'VAGAR', 'VALOR', 'VELHO', 'VERDE',
            'VETOR', 'VIGOR', 'VINTE', 'VIRAR', 'VITAL', 'VOLTA', 'VOTAR',
            // 6-8 letras
            'ABERTO', 'ACORDO', 'ALEGRE', 'ANIMAL', 'ANTIGO', 'ARVORE', 'ATAQUE',
            'BAIRRO', 'BONITO', 'BRANCO', 'CABECA', 'CADEIA', 'CAMELO', 'CANETA',
            'CENTRO', 'CHEGAR', 'CIDADE', 'COBRIR', 'COELHO', 'COLHER', 'COMPRA',
            'CONTAR', 'DENTRO', 'DEPOIS', 'DIARIO', 'DORMIR', 'EFEITO', 'ESTADO',
            'ESTUDO', 'FRANGO', 'FUTURO', 'GLOBAL', 'GRITAR', 'GUERRA', 'HUMANO',
            'IGREJA', 'JANELA', 'JARDIM', 'JORNAL', 'LIGADO', 'LIMPAR', 'MANDAR',
            'MENINA', 'MINUTO', 'MORADA', 'MOTIVO', 'MUSICA', 'NATURA', 'NUMERO',
            'OBJETO', 'OCEANO', 'OFERTA', 'PADRAO', 'PENSAR', 'PESSOA', 'PINTAR',
            'PLANTA', 'PREDIO', 'RAPIDO', 'SABADO', 'SALADA', 'SEGURO', 'SEMPRE',
            'SENHOR', 'TOMATE', 'TREINO', 'TRISTE', 'VENDAS', 'VIAJAR', 'VOLUME',
            'ABACATE', 'AMARELO', 'ASSUNTO', 'BATALHA', 'CAMINHO', 'CAPITAL',
            'CERVEJA', 'CIENCIA', 'CLIENTE', 'COMBATE', 'COMEDIA', 'CORRETO',
            'CRIANCA', 'CULTURA', 'DEFEITO', 'DESTINO', 'DIREITO', 'DIVERSO',
            'DOMINGO', 'DURANTE', 'EMPRESA', 'ENERGIA', 'ESFORCO', 'ESPELHO',
            'ESTRELA', 'EXEMPLO', 'FAMILIA', 'FAZENDA', 'GOVERNO', 'IMAGEM',
            'IMPACTO', 'LARANJA', 'LEITURA', 'MADEIRA', 'MAQUINA', 'MENTIRA',
            'MERCADO', 'MODERNA', 'MOMENTO', 'NENHUMA', 'OUTUBRO', 'PALAVRA',
            'PARCELA', 'PARTIDA', 'PEQUENO', 'PINTURA', 'PLANETA', 'POPULAR',
            'PRATICA', 'PRESENTE', 'PROBLEMA', 'PRODUTO', 'PROJETO', 'PUBLICO',
            'QUERIDO', 'RESGATE', 'SAUDADE', 'SEGUNDA', 'SERVICO', 'SIMPLES',
            'SUCESSO', 'TAMANHO', 'TRABALHO', 'TURISMO', 'VALENTE', 'VARIADO',
            'VERDADE', 'VITORIA', 'VIZINHO',
        ];
    }

    private function getCidades(): array
    {
        return [
            // Capitais e cidades famosas do Brasil
            'RECIFE', 'MANAUS', 'BELEM', 'CUIABA', 'GOIANIA', 'NATAL',
            'MACEIO', 'ARACAJU', 'PALMAS', 'CURITIBA', 'OSASCO', 'SANTOS',
            'PARATY', 'OLINDA', 'OURO', 'CAMPINAS', 'NITEROI', 'CANELA',
            'GRAMADO', 'ILHEUS', 'BUZIOS', 'BONITO', 'CALDAS', 'MARIANA',
            'TIRADENTES', 'CARUARU', 'CHAPECO', 'MARINGA', 'LONDRINA',
            'CASCAVEL', 'PELOTAS', 'CANOAS', 'CAXIAS', 'BAURU',
            'FRANCA', 'MARABA', 'MACAPA', 'SOBRAL', 'JUAZEIRO',
            'PETROLINA', 'VITORIA', 'SERRA', 'JUNDIAI', 'BETIM',
            'ANAPOLIS', 'DOURADOS', 'SINOP', 'RONDON', 'ITAJAI',
            // Cidades do mundo
            'PARIS', 'ROMA', 'MADRI', 'LISBOA', 'LONDRES', 'BERLIM',
            'VIENA', 'PRAGA', 'ATENAS', 'DUBLIN', 'OSLO', 'CAIRO',
            'TOQUIO', 'SEUL', 'DELHI', 'DUBAI', 'LIMA', 'CUSCO',
            'BOGOTA', 'QUITO', 'HAVANA', 'CANCUN', 'MIAMI', 'BOSTON',
            'DENVER', 'DALLAS', 'MOSCOU', 'MILAO', 'PORTO', 'BRAGA',
            'NAPOLI', 'VENEZA', 'MONACO', 'NICE', 'LYON', 'TURIM',
            'GENOVA', 'MUNIQUE', 'COLONIA',
        ];
    }

    private function getFilmes(): array
    {
        return [
            // Títulos de filmes (palavras únicas que formam títulos)
            'AVATAR', 'BAMBI', 'BLADE', 'BOLERO', 'CASPER', 'COBRA',
            'DJANGO', 'DRIVE', 'DUMBO', 'FARGO', 'FLASH', 'FROZEN',
            'GHOST', 'GREASE', 'HULK', 'JANGO', 'JOKER', 'LOGAN',
            'MATRIX', 'MOANA', 'MULAN', 'NERUDA', 'NEMO', 'OCEAN',
            'PLUTO', 'ROCKY', 'SHREK', 'SPIDER', 'STORM', 'TENET',
            'TITAN', 'TURBO', 'VENOM', 'ROBOCOP',
            // Palavras de títulos em português
            'CENTRAL', 'CIDADE', 'TROPA', 'ELITE', 'CARANDIRU',
            'BACURAU', 'AQUARIUS', 'ORFEU', 'LIMITE', 'PIXOTE',
            'AGOSTO', 'ABRIL', 'OUTUBRO', 'VERAO', 'INVERNO',
            'FLORESTA', 'DESERTO', 'PARAISO', 'INFERNO', 'GUERRA',
            'BATALHA', 'FUGA', 'ENIGMA', 'MISTERO', 'GLADIADOR',
            'PIRATA', 'CAPITAO', 'GIGANTE', 'MONSTRO', 'DRAGAO',
            'ESTRELA', 'PLANETA', 'FUTURO', 'PASSADO', 'DESTINO',
            'LEGADO', 'ALIANCA', 'IMPERIO', 'TEMPLO', 'CASTELO',
            'TORRE', 'COROA', 'ESPADA', 'ESCUDO', 'TRONO',
            'VINGANCA', 'JUSTICA', 'VERDADE', 'MENTIRA', 'SILENCIO',
            'SOMBRA', 'CHAMA', 'TROVAO', 'RELAMPAGO',
        ];
    }

    private function getAnimais(): array
    {
        return [
            // 2-4 letras
            'BOI', 'COR', 'EMU', 'GNU', 'OCA', 'OVO', 'RAT',
            'ANTA', 'ARCO', 'ASNO', 'AVES', 'BODE', 'BOGA', 'BUFO',
            'CABA', 'CUCA', 'FOCA', 'GALO', 'GATA', 'GATO', 'JACA',
            'LEAO', 'LOBO', 'LULA', 'MICO', 'MULA', 'ONCA', 'PACA',
            'PATO', 'PERU', 'PICA', 'PUMA', 'RAIA', 'RATO', 'RENA',
            'SAPO', 'TATU', 'URSO', 'VACA',
            // 5-6 letras
            'ALCE', 'ARARA', 'BALEIA', 'BURRO', 'CABRA', 'CALANGO',
            'CAMELO', 'CAPOTE', 'CAVALO', 'CERVO', 'CISNE', 'COBRA',
            'COALA', 'COELHO', 'CORUJA', 'CORVO', 'CUCO', 'DINGO',
            'DONINHA', 'DROMEDARIO', 'EGUA', 'FALCAO', 'FLAMINGO',
            'FURÃO', 'GALINHA', 'GARÇA', 'GIRAFA', 'GORILA', 'GRILO',
            'GUAXINIM', 'HAMSTER', 'HIENA', 'IGUANA', 'JABUTI',
            'JAGUAR', 'LHAMA', 'LONTRA', 'MACACO', 'MORSA',
            'MOSCA', 'OSTRA', 'OVELHA', 'PANTERA', 'PAPAGAIO',
            'PARDAL', 'PAVAO', 'PELICANO', 'PINGUIM', 'PIRANHA',
            'POLVO', 'POMBO', 'RAPOSA', 'SALMAO', 'SAGUI',
            'SURUBI', 'TARUGA', 'TIGRE', 'TRUTA', 'TUCANO',
            'TUBARAO', 'URUBU', 'VEADO', 'ZEBRA',
        ];
    }

    private function getComidas(): array
    {
        return [
            // 2-4 letras
            'CHA', 'MEL', 'OVO', 'PAO', 'RUM', 'SAL', 'UVA',
            'ACAI', 'ALHO', 'ARMA', 'AVEIA', 'BIFE', 'BOLO', 'CAFE',
            'CAJU', 'COCA', 'COCO', 'DOCE', 'FIGO', 'FUBA', 'JACA',
            'KIWI', 'LEAO', 'LIMA', 'MATE', 'MACA', 'MIGA', 'MINI',
            'MISO', 'NATA', 'NOZES', 'PERA', 'PURE', 'SOJA', 'SOPA',
            'SUCO', 'TOFU',
            // 5-7 letras
            'ABACATE', 'ACEROLA', 'ALFACE', 'AMEIXA', 'AMORA', 'ARROZ',
            'AVEIA', 'BACON', 'BANANA', 'BATATA', 'BERINJELA', 'BISCOITO',
            'BOLINHO', 'BROA', 'BROCOLI', 'CACAU', 'CANELA', 'CAQUI',
            'CARNE', 'CENOURA', 'CEREAIS', 'CEVADA', 'COUVE', 'CREPE',
            'CUSCUZ', 'DAMASCO', 'FEIJAO', 'FRANGO', 'GOIABA', 'IOGURTE',
            'LARANJA', 'LASANHA', 'LEITAO', 'LIMAO', 'LINGUICA', 'MAMAO',
            'MANGA', 'MANTEIGA', 'MELANCIA', 'MELAO', 'MILHO', 'MORANGO',
            'NABO', 'PALMITO', 'PAMONHA', 'PANQUECA', 'PASTEL', 'PEIXE',
            'PICANHA', 'PIZZA', 'POLENTA', 'PUDIM', 'QUEIJO', 'QUIABO',
            'RABANETE', 'RISOTO', 'ROMÃ', 'SALADA', 'SALMAO', 'SORVETE',
            'TAPIOCA', 'TEMPERO', 'TOMATE', 'TORRADA', 'TORTA', 'VATAPA',
        ];
    }

    private function getProfissoes(): array
    {
        return [
            'ATOR', 'CHEF', 'JUIZ',
            'ADVOGADO', 'AGENTE', 'ANALISTA', 'ARBITRO', 'ARTISTA',
            'ATLETA', 'AUDITOR', 'AUTOR', 'BAILARINA', 'BARBEIRO',
            'BIOLOGO', 'BOMBEIRO', 'BORDAR', 'CANTOR', 'CAPITAO',
            'CARPINTEIRO', 'CARTEIRO', 'CHEFE', 'CIENTISTA', 'CIRURGIAO',
            'COACH', 'COMISSARIO', 'CONTADOR', 'COZINHEIRO', 'DENTISTA',
            'DESIGNER', 'DETETIVE', 'DIRETOR', 'EDITOR', 'ELETRICISTA',
            'ENFERMEIRO', 'ENGENHEIRO', 'ESCRITOR', 'ESCULTOR', 'ESTILISTA',
            'FARMACEUTICO', 'FILOSOFO', 'FISCAL', 'FISICO', 'FLORISTA',
            'FOTOGRAFO', 'GARCOM', 'GEOLOGO', 'GERENTE', 'JORNALISTA',
            'LENHADOR', 'LOCUTOR', 'MAESTRO', 'MARINHEIRO', 'MECANICO',
            'MEDICO', 'MILITAR', 'MINISTRO', 'MOTORISTA', 'MUSICO',
            'PADEIRO', 'PASTOR', 'PEDREIRO', 'PILOTO', 'PINTOR',
            'POETA', 'POLICIAL', 'PORTEIRO', 'PREFEITO', 'PRODUTOR',
            'PROFESSOR', 'PROMOTOR', 'PSICOLOGO', 'QUIMICO', 'RADIALISTA',
            'RECEITA', 'RELATOR', 'REPORTER', 'REITOR', 'SENADOR',
            'SOLDADO', 'SURFISTA', 'TAXISTA', 'TECNICO', 'TREINADOR',
            'VENDEDOR', 'VETERINARIO', 'ZELADOR',
        ];
    }

    private function getEsportes(): array
    {
        return [
            'ARCO', 'BOXE', 'GOLF', 'JUDO', 'POLO', 'SURF', 'VELA',
            'BASQUETE', 'BEISEBOL', 'BOLICHE', 'CAPOEIRA', 'CICLISMO',
            'CORRIDA', 'CRICKET', 'DARDO', 'ESGRIMA', 'ESQUI',
            'FUTEBOL', 'FUTSAL', 'GINASTICA', 'HANDEBOL', 'HIPISMO',
            'HOQUEI', 'KARATE', 'LACROSSE', 'LUTA', 'MARATONA',
            'MERGULHO', 'NATACAO', 'PADDLE', 'PATINACAO', 'PELOTA',
            'PETECA', 'RAFTING', 'REMO', 'RUGBY', 'SINUCA',
            'SKATE', 'SQUASH', 'TENIS', 'TRIATLO', 'VOLEI',
            'XADREZ', 'IATISMO', 'ATLETISMO', 'CANOAGEM',
            'ESCALADA', 'SALTO', 'ARREMESSO', 'NADO', 'PENTATLO',
        ];
    }

    private function getNatureza(): array
    {
        return [
            'AR', 'CEU', 'MAR', 'RIO', 'SOL',
            'AGUA', 'AREIA', 'AURORA', 'BAÍA', 'BOSQUE', 'BRISA',
            'CACTO', 'CAMPO', 'CAVERNA', 'CERRADO', 'CHUVA', 'COLINA',
            'CORAL', 'COSTA', 'CRISTAL', 'DELTA', 'DESERTO', 'DUNA',
            'ECLIPSE', 'ESTRELA', 'FAUNA', 'FLORA', 'FLORESTA', 'FONTE',
            'GELO', 'GLACIAR', 'GRUTA', 'ICEBERG', 'ILHA', 'LAGOA',
            'LAGO', 'LAVA', 'LITORAL', 'MANGUE', 'MATA', 'MONTANHA',
            'NEBLINA', 'NEVE', 'NUVEM', 'OCEANO', 'ONDA', 'ORVALHO',
            'PENHASCO', 'PLANICIE', 'PLANALTO', 'PRAIA', 'RECIFE',
            'RELEVO', 'ROCHA', 'SAVANA', 'SELVA', 'SERRA', 'TEMPESTADE',
            'TERRA', 'TORNADO', 'TROVAO', 'TSUNAMI', 'TUNDRA', 'VALE',
            'VENTO', 'VULCAO', 'PANTANAL', 'CAATINGA', 'MANGAL',
            'NASCENTE', 'CASCATA', 'PICO', 'CRATERA', 'PENINSULAA',
            'ATOL', 'ABISMO', 'RAVINA', 'CLAREIRA', 'ESTEPE',
        ];
    }
}
