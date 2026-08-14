<?php

namespace Database\Seeders;

use App\Models\DictionaryWord;
use Illuminate\Database\Seeder;

class DictionarySeeder extends Seeder
{
    /**
     * Seed the dictionary_words table with valid Portuguese (pt-BR) words.
     * All words exist in the official Portuguese dictionary.
     * Organized by length for game balance.
     */
    public function run(): void
    {
        $words = $this->getWords();

        $chunks = array_chunk($words, 100);

        foreach ($chunks as $chunk) {
            $records = array_map(function (string $word) {
                $normalized = mb_strtoupper(trim($word));
                return [
                    'word' => $normalized,
                    'length' => mb_strlen($normalized),
                    'is_valid' => true,
                    'is_inappropriate' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $chunk);

            DictionaryWord::upsert($records, ['word'], ['length', 'updated_at']);
        }

        $this->command->info('Seeded ' . count($words) . ' Portuguese words.');
    }

    /**
     * Valid Portuguese (pt-BR) dictionary words organized by length.
     *
     * @return string[]
     */
    private function getWords(): array
    {
        return array_merge(
            $this->get2Letters(),
            $this->get3Letters(),
            $this->get4Letters(),
            $this->get5Letters(),
            $this->get6Letters(),
            $this->get7Letters(),
            $this->get8Letters(),
            $this->get9PlusLetters(),
        );
    }

    private function get2Letters(): array
    {
        return [
            'AR', 'AI', 'AO', 'AS', 'AZ', 'CA', 'DA', 'DE', 'DO', 'EM',
            'EU', 'IR', 'JA', 'LA', 'LI', 'MA', 'ME', 'NA', 'NO', 'NU',
            'OS', 'OU', 'PA', 'PE', 'PI', 'RE', 'SE', 'SI', 'TA', 'TU',
            'UM', 'VA', 'VI',
        ];
    }

    private function get3Letters(): array
    {
        return [
            'ACO', 'ALA', 'ALI', 'AMO', 'ANO', 'APE', 'ARA', 'ASA', 'ATA',
            'ATE', 'ATO', 'AVE', 'AVO', 'BAR', 'BEM', 'BOA', 'BOI', 'BOM',
            'CAL', 'CAS', 'CEU', 'CHA', 'COR', 'CRU', 'CUZ', 'DAR', 'DEU',
            'DIA', 'DOM', 'DOR', 'ELA', 'ELE', 'ELO', 'ERA', 'FAZ', 'FEL',
            'FIA', 'FIM', 'FIO', 'FOI', 'GAS', 'GIZ', 'GOL', 'IDA', 'IRA',
            'JUS', 'LAR', 'LEI', 'LER', 'LUA', 'LUZ', 'MAE', 'MAL', 'MAR',
            'MAS', 'MAU', 'MEL', 'MES', 'MEU', 'MIL', 'MIM', 'NAO', 'NEM',
            'NOZ', 'NUA', 'OCA', 'ORA', 'OSO', 'OVO', 'PAI', 'PAR', 'PAS',
            'PAZ', 'PIS', 'POR', 'POS', 'PUS', 'REI', 'RES', 'RIM', 'RIO',
            'RIR', 'RUA', 'RUM', 'SAL', 'SER', 'SIM', 'SOB', 'SOL', 'SOM',
            'SUA', 'TAL', 'TEM', 'TER', 'TIA', 'TIO', 'TOM', 'TUA', 'UMA',
            'UNS', 'URO', 'UVA', 'VAI', 'VAN', 'VEM', 'VER', 'VEZ', 'VIA',
            'VIR', 'VOO', 'VOZ',
        ];
    }

    private function get4Letters(): array
    {
        return [
            'ABRE', 'ACAO', 'ACNE', 'AFIM', 'AGIL', 'AGUA', 'ALEM', 'ALGO',
            'ALHO', 'ALMA', 'ALTO', 'AMOR', 'ANEL', 'ANJO', 'ANTE', 'ARCO',
            'ARMA', 'ASAS', 'ASNO', 'ATUM', 'AUTO', 'AVES', 'AVOS', 'AZUL',
            'BALA', 'BAFO', 'BAGO', 'BAIA', 'BECO', 'BELO', 'BENS', 'BICHO',
            'BIFE', 'BOCA', 'BODE', 'BOLA', 'BOLO', 'BOTE', 'CABO', 'CADA',
            'CAFE', 'CAIR', 'CAMA', 'CANA', 'CANO', 'CAPA', 'CARA', 'CASA',
            'CASO', 'CAUL', 'CEDO', 'CEGO', 'CEIA', 'CELA', 'CENA', 'CERA',
            'CHAO', 'CIMO', 'COCA', 'COCO', 'COLA', 'COMO', 'CONE', 'COPA',
            'COPO', 'CORA', 'COTA', 'COVA', 'CUBO', 'DADO', 'DAMA', 'DANO',
            'DATA', 'DEDO', 'DEMO', 'DEUS', 'DOCE', 'DOCA', 'DOIS', 'DONA',
            'DONO', 'DOSE', 'DOTE', 'DURA', 'DURO', 'ECOA', 'ECOS', 'EIXO',
            'ERVA', 'ESSE', 'ESTE', 'EURO', 'FACA', 'FACE', 'FADA', 'FALA',
            'FAMA', 'FARO', 'FASE', 'FATO', 'FAVA', 'FERA', 'FIAR', 'FIEL',
            'FILA', 'FINO', 'FITA', 'FOCO', 'FOGO', 'FOME', 'FORA', 'FORO',
            'FOTO', 'FRIO', 'FUGA', 'FUMO', 'GADO', 'GALO', 'GAMA', 'GANA',
            'GATO', 'GELO', 'GEMA', 'GOLA', 'GOMA', 'GOTA', 'GRAU', 'GUIA',
            'HINO', 'HORA', 'HOJE', 'Hugo', 'IBIS', 'IDEM', 'ILHA', 'INCA',
            'IODO', 'ISSO', 'ITEM', 'JATO', 'JOIA', 'JOGO', 'JUBA', 'JUIZ',
            'JURA', 'JURO', 'JUTA', 'LADO', 'LAGO', 'LAJE', 'LAMA', 'LAPO',
            'LATA', 'LEAL', 'LEMA', 'LEVE', 'LIGA', 'LIMA', 'LISO', 'LOBO',
            'LODO', 'LOJA', 'LONA', 'LOTE', 'LUAR', 'LUPA', 'LUVA', 'LUXO',
            'MACA', 'MAGO', 'MAIO', 'MALA', 'MAMO', 'MANO', 'MAPA', 'MATO',
            'MEDO', 'MEIA', 'MEIO', 'MESA', 'META', 'MICO', 'MINA', 'MITO',
            'MODA', 'MODO', 'MOLA', 'MOLE', 'MOFO', 'MORA', 'MORRO','MUDO',
            'MULA', 'MURO', 'MUSA', 'NABO', 'NADA', 'NATA', 'NAVE', 'NETA',
            'NETO', 'NEVE', 'NOEL', 'NOME', 'NOTA', 'NOVA', 'NOVO', 'NUCA',
            'OBRA', 'ODIO', 'OGRO', 'OITO', 'OLEO', 'OLHO', 'ONDA', 'ONDE',
            'ONZE', 'ORAL', 'OSSO', 'OURO', 'OVAL', 'PACA', 'PAGO', 'PAIS',
            'PANO', 'PARA', 'PARE', 'PATA', 'PATO', 'PECA', 'PELE', 'PELO',
            'PENA', 'PERA', 'PESO', 'PICA', 'PICO', 'PINO', 'PISO', 'PODE',
            'POIO', 'POLO', 'POMO', 'POSE', 'POSTE','POTE', 'POVO', 'PULO',
            'PUMA', 'RABO', 'RAMO', 'RAIO', 'RARO', 'RATO', 'REAL', 'REDE',
            'REIS', 'REMO', 'RENA', 'RETO', 'RICO', 'RIJO', 'RITO', 'RISO',
            'RODA', 'ROLO', 'ROMA', 'ROSA', 'ROTA', 'RUDE', 'RUMO', 'SACO',
            'SAGA', 'SALA', 'SALDO','SAPO', 'SECO', 'SEDA', 'SEDE', 'SEIS',
            'SELO', 'SERA', 'SETA', 'SETE', 'SIDO', 'SILO', 'SINO', 'SOCO',
            'SOLA', 'SOLO', 'SOMA', 'SONO', 'SOPA', 'SORO', 'SUMO', 'TACO',
            'TALO', 'TAPA', 'TATU', 'TAXA', 'TEIA', 'TELA', 'TEMA', 'TETO',
            'TIDO', 'TIPO', 'TIRO', 'TOCA', 'TODA', 'TODO', 'TOMO', 'TOPO',
            'TORA', 'TRIO', 'TUBO', 'TUDO', 'TUFO', 'URNA', 'URSO', 'USAR',
            'UVAS', 'VACA', 'VAGA', 'VALE', 'VARA', 'VASO', 'VEIA', 'VELA',
            'VENTO','VERA', 'VIDA', 'VIGA', 'VILA', 'VIME', 'VIRA', 'VISA',
            'VIVO', 'VOAR', 'VOTO', 'ZERO', 'ZONA', 'ZUMO',
        ];
    }

    private function get5Letters(): array
    {
        return [
            'ABADE', 'ABANO', 'ABRIR', 'ABRIL', 'ACASO', 'ACESA', 'ACIMA',
            'ACIDO', 'ACRES', 'AGORA', 'AGUDO', 'AINDA', 'AIPIM', 'AJUDA',
            'ALDEIA','ALGUM', 'ALIBI', 'ALMAS', 'ALUNO', 'AMADO', 'AMBAR',
            'AMIGO', 'AMPLO', 'ANDAR', 'ANIMO', 'ANTES', 'ANZOL', 'APOIO',
            'ARADO', 'AREIA', 'ARGOL', 'AROMA', 'ARROZ', 'ASILO', 'ATLAS',
            'AVIAO', 'AVISO', 'BAIXO', 'BALDE', 'BALSA', 'BANCO', 'BANDA',
            'BANHO', 'BARBA', 'BARCO', 'BARRA', 'BARRO', 'BASTA', 'BATER',
            'BEIRA', 'BEIJO', 'BELAS', 'BICHO', 'BLOCO', 'BLUSA', 'BOLHA',
            'BOLSA', 'BOMBA', 'BONDE', 'BRAÇO', 'BRASA', 'BRAVO', 'BREVE',
            'BRIGA', 'BRISA', 'BRUXA', 'BURRO', 'BUSCA', 'CABRA', 'CACHO',
            'CAFUA', 'CAIXA', 'CALDA', 'CALMA', 'CALOR', 'CALDO', 'CAMPO',
            'CANAL', 'CANTO', 'CAPIM', 'CARGA', 'CARNE', 'CARRO', 'CARTA',
            'CASAL', 'CAUSA', 'CAVAR', 'CEBOLA','CEDER', 'CERCA', 'CERNE',
            'CERVO', 'CHAMA', 'CHAVE', 'CHEFE', 'CHEIA', 'CHORO', 'CINCO',
            'CISNE', 'CLARO', 'CLIMA', 'COBRA', 'COFRE', 'COICE', 'COLAR',
            'COLMO', 'COMER', 'COMUM', 'CONDE', 'CONTO', 'CONTA', 'COPIA',
            'CORAL', 'CORDA', 'CORPO', 'CORRE', 'CORTE', 'CORVO', 'COSTA',
            'COURO', 'COUVE', 'COZER', 'CREME', 'CRIAR', 'CRIME', 'CRISE',
            'CRUEL', 'DANCA', 'DARDO', 'DEBIL', 'DEDAL', 'DEFEITO','DESDE',
            'DEVER', 'DIETA', 'DIGNO', 'DISCO', 'DITAR', 'DIZER', 'DOIDO',
            'DORSO', 'DOTAR', 'DRAMA', 'DUPLO', 'DUQUE', 'DURMO', 'EIXOS',
            'ELITE', 'ENFIM', 'ENTRE', 'ENVIO', 'ERETO', 'ERGUE', 'ERRAR',
            'ESCOL', 'EXATO', 'EXIGE', 'EXTRA', 'FABIO', 'FACIL', 'FALHA',
            'FALSO', 'FARPA', 'FAROL', 'FARTO', 'FATAL', 'FATOR', 'FAUNA',
            'FAVOR', 'FEBRE', 'FELIZ', 'FEMEA', 'FENDA', 'FERRO', 'FESTA',
            'FIBRA', 'FICAR', 'FILHA', 'FILHO', 'FILME', 'FINAL', 'FINCA',
            'FIRME', 'FLORA', 'FLUIR', 'FOBIA', 'FOFAS', 'FOICE', 'FOLHA',
            'FOLIA', 'FORCA', 'FORMA', 'FORTE', 'FOSSO', 'FRASE', 'FREAR',
            'FREVO', 'FROTA', 'FRUTA', 'FUGIR', 'FUMAR', 'FUNDO', 'FUNIL',
            'GAFES', 'GALHO', 'GANHA', 'GARFO', 'GARRA', 'GASTO', 'GEMEO',
            'GENIO', 'GENTE', 'GERAL', 'GESSO', 'GESTO', 'GLOBO', 'GOLFO',
            'GOLPE', 'GRADE', 'GRAFE', 'GRAMO', 'GRAVE', 'GREVE', 'GRILO',
            'GRIPE', 'GRITO', 'GROSSO','GRUPO', 'GUETO', 'HAVER', 'HEROI',
            'HONRA', 'HUMOR', 'IAQUE', 'IDEIA', 'IDADE', 'IDOLO', 'IGUAL',
            'IMPAR', 'IMPOR', 'INDIO', 'INFER', 'INTIL', 'ISCAR', 'ISOLA',
            'ITERA', 'JANTA', 'JOGAR', 'JORNAL','JOVEM', 'JUDIO', 'JUIZO',
            'JUNTA', 'JURAR', 'JUSTO', 'LABOR', 'LAMPO', 'LAPIS', 'LARGO',
            'LARVA', 'LAVAR', 'LAZER', 'LEGAL', 'LEITE', 'LENHA', 'LENTO',
            'LESMA', 'LEVAR', 'LIÇÃO', 'LIDAR', 'LIGAR', 'LIMAO', 'LIMPO',
            'LINDA', 'LINDO', 'LINHA', 'LISTA', 'LITRO', 'LIVRO', 'LOIRO',
            'LOMBO', 'LONGE', 'LOTAR', 'LOUCA', 'LUGAR', 'LUTAR', 'MACHO',
            'MACRO', 'MAGRO', 'MAIOR', 'MANCO', 'MANGA', 'MANHA', 'MANTO',
            'MARCA', 'MARCO', 'MASSA', 'MATAR', 'MEDIR', 'MEIGO', 'MENOR',
            'MENTA', 'MESES', 'METAL', 'METER', 'METRO', 'MILHO', 'MINHA',
            'MIOLO', 'MIRRA', 'MISSA', 'MISTO', 'MOCHO', 'MOEDA', 'MOLDE',
            'MONTE', 'MORAL', 'MORAR', 'MORDO', 'MORNO', 'MOTOR', 'MUDAR',
            'MUITO', 'MUNDO', 'MUSEU', 'NADAR', 'NARIZ', 'NAVIO', 'NERVO',
            'NINHO', 'NIVEL', 'NOBRE', 'NOITE', 'NOIVA', 'NOSSO', 'NUVEM',
            'NUNCA', 'OBESO', 'OBVIO', 'OLHAR', 'ONTEM', 'OPACO', 'OPERA',
            'ORDEM', 'ORGAO', 'OTIMO', 'OUVIR', 'PADRE', 'PALCO', 'PALHA',
            'PALMO', 'PANELA','PAPEL', 'PARDO', 'PARIR', 'PASSO', 'PASTA',
            'PATIO', 'PEDRA', 'PEITO', 'PEIXE', 'PENHA', 'PERDA', 'PERNA',
            'PERTO', 'PESAR', 'PESCA', 'PIANO', 'PILAR', 'PILHA', 'PINGO',
            'PINTA', 'PIRES', 'PISTA', 'PLACA', 'PLANO', 'PLUMA', 'PODER',
            'POEMA', 'POLIR', 'POLPA', 'POMAR', 'POMBA', 'PONTO', 'PORCO',
            'PORTA', 'POSSE', 'POSTE', 'POUCA', 'PRAÇA', 'PRADO', 'PRAGA',
            'PRATO', 'PRAZO', 'PRECE', 'PREGO', 'PRELO', 'PRESA', 'PRIMA',
            'PRIMO', 'PROVA', 'PUDIM', 'PULGA', 'PULSO', 'PUNHO', 'QUASE',
            'QUEDA', 'QUEIJO','RACHA', 'RAIVA', 'RAMPA', 'RAPAZ', 'RASTO',
            'RAZAO', 'REDOR', 'REGER', 'REGRA', 'RELER', 'RENDA', 'REZAR',
            'RIFLE', 'RIGOR', 'RISCO', 'RITMO', 'RIVAL', 'ROCHA', 'RODAS',
            'ROLHA', 'RONCO', 'ROUPA', 'RUIDO', 'SABAO', 'SABER', 'SABOR',
            'SAFRA', 'SAIDA', 'SALDO', 'SALMO', 'SALSA', 'SALTO', 'SALVA',
            'SANTO', 'SAUDE', 'SECAR', 'SELVA', 'SENSO', 'SERIE', 'SERRA',
            'SERVO', 'SINAL', 'SIRVA', 'SOBRE', 'SOGRA', 'SOLAR', 'SONHO',
            'SOPRO', 'SORTE', 'SUBIR', 'SUAVE', 'SULCO', 'SURDO', 'SURGE',
            'SUSTO', 'TANTO', 'TAQUE', 'TARDE', 'TARJA', 'TENDA', 'TENSO',
            'TERNO', 'TERRA', 'TESTA', 'TEXTO', 'TIGRE', 'TIMAO', 'TINTA',
            'TOLDO', 'TOMAR', 'TOQUE', 'TORCE', 'TOTAL', 'TOUCA', 'TRAÇO',
            'TRAJE', 'TRAGO', 'TRAJE', 'TRAPO', 'TRAVO', 'TRECO', 'TREVO',
            'TRIBO', 'TRILHO','TROCA', 'TRONO', 'TROPA', 'TURMA', 'TURNO',
            'TUTELA','UIVAR', 'ULTRA', 'UMBRA', 'UNICO', 'UNIAO', 'UNTAR',
            'URGIR', 'URNA', 'USADO', 'USUAL', 'VAGAR', 'VALER', 'VALOR',
            'VALSA', 'VAPOR', 'VARIA', 'VAZIO', 'VEADO', 'VELHO', 'VELOZ',
            'VENDA', 'VENTO', 'VERDE', 'VERME', 'VERSO', 'VETOR', 'VIGOR',
            'VINDA', 'VINTE', 'VIOLA', 'VIRAR', 'VIRIL', 'VISAR', 'VISTA',
            'VITAL', 'VIUVA', 'VIVER', 'VOLTA', 'VOTAR', 'VULTO', 'ZEBRA',
            'ZOMBI', 'ZURRO',
        ];
    }

    private function get6Letters(): array
    {
        return [
            'ABACAX', 'ABAIXO', 'ABELHA', 'ABERTO', 'ABRIGO', 'ABSORTO',
            'ACEITE', 'ACESSO', 'ACORDO', 'ACUCAR', 'ADAPTA', 'ADEGAR',
            'ADULTO', 'AFAGAR', 'AGENDA', 'AGENTE', 'AGRAFO', 'AGRIAO',
            'ALARME', 'ALEGRE', 'ALIADO', 'ALMOÇO', 'ALTURA', 'AMARGO',
            'AMAVEL', 'AMEACA', 'AMEIXA', 'AMIZADE','ANIMAL', 'ANTIGO',
            'APENAS', 'APERTO', 'APOIAR', 'APROVA', 'ARANHA', 'ARDIDO',
            'ARMADO', 'ARRANJO','ARREIA', 'ARVORE', 'ASSADO', 'ATIRAR',
            'ATAQUE', 'ATRAIR', 'ATRITO', 'AUXILIO','AVANCA', 'AVESSO',
            'BABADO', 'BACANA', 'BAIRRO', 'BAINHA', 'BALADA', 'BALCAO',
            'BANANA', 'BARRIL', 'BASICO', 'BATATA', 'BAZUCA', 'BELEZA',
            'BERCOS', 'BILHAR', 'BILHAO', 'BISAVO', 'BONECA', 'BONITO',
            'BRANCO', 'BRINDE', 'BRONZE', 'BRUTAS', 'BURACO', 'BUZINA',
            'CABANA', 'CABECA', 'CACADA', 'CADEIA', 'CALCAR', 'CALHAR',
            'CAMARÀ', 'CAMELO', 'CAMISA', 'CANETA', 'CANINO', 'CANTOR',
            'CAPOTE', 'CARECA', 'CARICA', 'CARNES', 'CASACO', 'CASTIO',
            'CAUSAR', 'CAVALO', 'CELULA', 'CENORA', 'CENTRO', 'CEREJA',
            'CERTOS', 'CESSAR', 'CHEGAR', 'CIDADE', 'CINEMA', 'CIPRES',
            'COBRAR', 'COBRIR', 'COELHO', 'COGITO', 'COLETA', 'COLHER',
            'COLINA', 'COMECO', 'COMPOR', 'COMPRA', 'CONCHA', 'CONFIA',
            'CONTAR', 'CONTEI', 'COPIAR', 'CORACA', 'CORDAS', 'CORREIA',
            'CORTAM', 'COSTAS', 'COZER', 'COZIDO', 'CRAVAR', 'CUIDAR',
            'DEGRAU', 'DENTRO', 'DEPOIS', 'DERIVA', 'DIANTE', 'DIARIO',
            'DIESEL', 'DIGITO', 'DILEMA', 'DIRETO', 'DISCOS', 'DIVIDA',
            'DOBRAR', 'DOMADO', 'DORMIR', 'DOURAR', 'DUREZA', 'EFEITO',
            'ELEITO', 'EMPATE', 'ENFIAR', 'ENGANO', 'ENSAIO', 'EQUIPE',
            'ESCAPE', 'ESPADA', 'ESPAÇO', 'ESTADO', 'ESTILO', 'ESTUDO',
            'ETERNO', 'EVITAR', 'EXIBIR', 'FACADA', 'FARELO', 'FAXINA',
            'FECUND', 'FECHAR', 'FERVER', 'FIGURÀ', 'FILMAR', 'FILTRO',
            'FLEUMA', 'FOFOCA', 'FOLEGO', 'FORÇAR', 'FRANGO', 'FREADA',
            'FRENTE', 'FUNCAO', 'FURADO', 'FUTURO', 'GANCHO', 'GAROTA',
            'GÊNERO', 'GENTIL', 'GILETE', 'GLOBAL', 'GOLEIRO','GORILA',
            'GRANJA', 'GRATIS', 'GRITAR', 'GUERRA', 'HABITO', 'HERDAR',
            'HOMEM', 'HUMANO', 'IGREJA', 'INCHAR', 'INDICE', 'INFELIZ',
            'INTIMO', 'INVEJA', 'ISENTO', 'JANELA', 'JARDIM', 'JOGADA',
            'JORNAL', 'JUNTAR', 'LEILOÀ', 'LIGADO', 'LIMPAR', 'LOMBAR',
            'LUCRAR', 'MACACO', 'MANDAR', 'MANUAL', 'MENINA', 'MENINO',
            'MINUTO', 'MORADA', 'MOTIVO', 'MULHER', 'MUSICA', 'NEUTRO',
            'NINHOL', 'NORMAL', 'NOTADO', 'NUMERO', 'OBJETO', 'OCEANO',
            'OFENSA', 'OFERTA', 'OLHADO', 'OPINAR', 'ORGULHO','PADRAO',
            'PALITO', 'PASSAR', 'PECADO', 'PEDIDO', 'PENSAR', 'PERIGO',
            'PESSOA', 'PILOTO', 'PINTAR', 'PISTOL', 'PLANTA', 'PLACAR',
            'PLOCAR', 'POLIDO', 'POUSAR', 'PRAZER', 'PREDIO', 'PREGAR',
            'PRESSA', 'PROVAR', 'PUNHAL', 'QUARTO', 'QUEBRA', 'QUIETO',
            'RAPIDO', 'RECADO', 'RECIFE', 'REINAR', 'RELATO', 'REMOTO',
            'RENDER', 'RESUMO', 'RETIRO', 'RIGIDO', 'RIQUEZA','ROTINA',
            'SABADO', 'SALADA', 'SEGURO', 'SEMPRE', 'SENHOR', 'SERENO',
            'SEVERO', 'SOCORR', 'SOLIDO', 'SOLTAR', 'SOMBRA', 'SONORO',
            'SOPRAR', 'SORRIR', 'SUBIDA', 'SUJEITO','TEMPLO', 'TENDER',
            'TERMOS', 'TOALHA', 'TOMATE', 'TORNAR', 'TREINO', 'TRISTE',
            'UNIDIR', 'VALIDO', 'VENENO', 'VERDOR', 'VIAJAR', 'VOLUME',
            'XAROPE', 'ZELOSO',
        ];
    }

    private function get7Letters(): array
    {
        return [
            'ABACATE', 'ABORDAR', 'ABSORVE', 'ABRANDA', 'ACALMAR', 'ACERTAR',
            'ADORNAR', 'AFASTAR', 'AGITADO', 'AGRADAR', 'AJUSTAR', 'ALCANCE',
            'ALEGRIA', 'ALERGIA', 'ALFINETE','ALGODAO', 'ALIANÇA', 'ALMEJAR',
            'ALTERAR', 'AMARELO', 'AMIGAVEL','AMPLIAR', 'ANALISE', 'ANIMADO',
            'ANTEVER', 'APARATO', 'APLICAR', 'APROVAR', 'AQUARIO', 'ARMAZEM',
            'ARRANJO', 'ASSALTO', 'ASSUMIR', 'ASSUNTO', 'ATENÇÃO', 'AUXILIAR',
            'AVANCAR', 'AVENTAL', 'BAGAGEM', 'BAILADO', 'BALANCO', 'BARULHO',
            'BATALHA', 'BIZARRO', 'BLINDAR', 'BROCHURA','CABRITO', 'CADERNO',
            'CALIBRE', 'CAMINHO', 'CAMPAÑA', 'CANCELA', 'CANTICO', 'CAPITAL',
            'CARINHO', 'CARPETE', 'CASEIRO', 'CAUTELA', 'CENTENA', 'CEREBRO',
            'CERTEZA', 'CERVEJA', 'CHAMINE', 'CHAPADA', 'CHUVOSO', 'CIÊNCIA',
            'CIMENTO', 'CIRCULO', 'CLIENTE', 'CLINICA', 'COBERTU', 'COGITAR',
            'COLAPSO', 'COLCHAO', 'COLONIA', 'COLOQUE', 'COMBATE', 'COMEDIA',
            'COMEMORAR','COMECAR', 'COMPRAS', 'CONFORTO','CONJUNTO','CONSIGO',
            'CONSUMO', 'CONTATO', 'CONTUDO', 'CONVITE', 'CORRETO', 'CORTINA',
            'CRIANÇA', 'CULTURA', 'CUIDADO', 'CURIOSA', 'DECISAO', 'DEFEITO',
            'DEMANDA', 'DEPUTADO','DESAFIO', 'DESCIDA', 'DESERTO', 'DESTINO',
            'DETENTO', 'DIÁLOGO', 'DIREITO', 'DISPUTA', 'DIVERSA', 'DIVIDIR',
            'DOMINGO', 'DURANTE', 'EDITORA', 'EDUCAR', 'ELEVADO', 'EMBLEMA',
            'EMPATAR', 'EMPRESA', 'ENCAIXE', 'ENERGIA', 'ENFERMO', 'ENGENHO',
            'ENIGMAS', 'ENRAIZAR','ENTREGA', 'EQUIPAR', 'ERGUIDO', 'ESFORÇO',
            'ESPELHO', 'ESTACAO', 'ESTRAGO', 'ESTRELA', 'ETERNOS', 'EVOLUIR',
            'EXEMPLO', 'EXIBIDO', 'FABRICA', 'FAMILIA', 'FARINHA', 'FAZENDA',
            'FEIJOAO', 'FERIADO', 'FERMENT', 'FICANDO', 'FIGURAS', 'FILMADO',
            'FINANÇA', 'FORMULA', 'FRAGATA', 'FUNDICAO','GALERIA', 'GENUINO',
            'GERACAO', 'GIGANTE', 'GOVERNO', 'GUARDAR', 'HARMONIA','HERANCA',
            'HIGIENE', 'HISTORIA','HUMILDE', 'IMAGINAR','IMEDIATO','IMPACTO',
            'IMPULSO', 'INCLUSO', 'INDULTO', 'INFERNO', 'INJUSTO', 'INOVAR',
            'INTENSO', 'JANTAR', 'JUBILEU', 'JUVENIL', 'LAMPADA', 'LARANJA',
            'LATERAL', 'LEITURA', 'LIBERAL', 'LICENÇA', 'LIGACAO', 'LIMPEZA',
            'LOTERIA', 'MADEIRA', 'MAQUINA', 'MARFIM', 'MENTIRA', 'MERCADO',
            'MILAGRE', 'MINERAL', 'MODERNA', 'MOMENTO', 'MONITOR', 'MORADOR',
            'MORANGA', 'NASCIDO', 'NENHUMA', 'NOTICIA', 'OUTUBRO', 'PALAVRA',
            'PALMITO', 'PARCELA', 'PARTIDA', 'PASSADO', 'PEQUENO', 'PERSEGU',
            'PINTURA', 'PLANETA', 'POPULAR', 'PRATICA', 'PREPARO', 'PROXIMO',
            'PUBLICO', 'QUANTIA', 'QUERIDO', 'QUIMICO', 'RECEITA', 'RECURSO',
            'REFLEXO', 'REGULAR', 'RELACAO', 'RESGATE', 'REUNIAO', 'ROTEIRO',
            'SAUDADE', 'SEGUNDA', 'SENADOR', 'SERVICO', 'SIMPLES', 'SISTEMA',
            'SOCORRO', 'SOLIDAO', 'SUCESSO', 'SUPREMO', 'TAMANHO', 'TERCEIRO',
            'TERRENO', 'TRABALHO','TREMULO', 'TROPICAL','TURISMO', 'UNIDADE',
            'URGENTE', 'UTILIZAR','VALENTE', 'VARIADO', 'VERDADE', 'VIBRADO',
            'VITORIA', 'VIZINHO', 'XADREZ', 'ZANGADO',
        ];
    }

    private function get8Letters(): array
    {
        return [
            'ABANDONO', 'ABSOLUTO', 'ACADEMIA', 'ACUMULAR', 'ADEQUADO', 'ADMIRADO',
            'ADOTIVOS', 'AGRADECE', 'AJUDANTE', 'ALCANCAR', 'ALEGORIA', 'ALIMENTA',
            'ALIVIAR', 'ALTERNAR', 'AMBIENTE', 'AMIZADES', 'ANALISAR', 'ANIVERSA',
            'APARELHO', 'APOSTILA', 'APRENDER', 'APROVADO', 'AQUECIDO', 'ARMAZENAR',
            'ARQUITET', 'ASPIRINA', 'ASSUMIDO', 'ATENÇÃO', 'ATLÉTICO', 'AUMENTAR',
            'AVENTURA', 'BANCÁRIO', 'BARREIRA', 'BASTANTE', 'BATALHAR', 'BENEFICI',
            'BILHETES', 'BONDADE', 'BORRACHA', 'BRILHANT', 'CAÇAROLA', 'CALCULAM',
            'CAMAROTE', 'CAMPANHA', 'CANDIDAT', 'CAPRICHO', 'CARREGAR', 'CASTANHA',
            'CAVALGAR', 'CELEBRAR', 'CENARIOS', 'CHOVENDO', 'CIRCULAR', 'COBRADOR',
            'COLETIVO', 'COMBINAR', 'COMEÇADO', 'COMENTAR', 'COMPARAR', 'COMPLETO',
            'COMUNICA', 'CONCEDER', 'CONCURSO', 'CONFERIR', 'CONFIAR', 'CONFLITO',
            'CONJUNTO', 'CONQUISTA','CONSELHO', 'CONSERVA', 'CONTAVEL', 'CONTEUDO',
            'CONTROLE', 'CONVERSA', 'CONVIVER', 'COOPERAR', 'CORRENTE', 'CRIMINAL',
            'CRITICAR', 'CRUZEIRO', 'CULTIVAR', 'CURATIVO', 'DEBATIDO', 'DEDICADO',
            'DEFENDER', 'DEFINIDO', 'DELICADO', 'DEPOSITO', 'DESCANSA', 'DESCONTO',
            'DESCULPA', 'DESEJADO', 'DESTAQUE', 'DETALHES', 'DEVOLVER', 'DIALOGAR',
            'DINHEIRO', 'DINAMICA', 'DIMINUIR', 'DIPLOMAM', 'DIRIGIDO', 'DISPOSTO',
            'DISTANTE', 'DIVIDIDO', 'DOCENTES', 'DOCUMENT', 'DOMINADO', 'DOUTORADO',
            'ECONOMÍA', 'EFICIENTE','ELEGANTE', 'ELETRICA', 'EMBARCAR', 'EMOCIONÁ',
            'EMPILHAR', 'EMPREGAR', 'EMPURRAR', 'ENCANTAR', 'ENCONTRO', 'ENDERECO',
            'ENFRENTAR','ENGRAÇAD', 'ENORMES', 'ENSAIAR', 'ENTENDER', 'ENTREGAR',
            'EQUILIBR', 'ESCREVER', 'ESPANTAR', 'ESPECIAL', 'ESPERADO', 'ESTRANHO',
            'EVENTUAL', 'EXAMINAR', 'EXERCITO', 'EXPLORAR', 'FAMILIAR', 'FANTASIA',
            'FAVORITO', 'FEMININO', 'FESTIVAL', 'FLORESTA', 'FORMAÇÃO', 'FORNECER',
            'FREQUENTE','FRONTEIRA','FUNCIONAL','GARANTIA', 'GENEROSO', 'GEOMETRI',
            'GOVERNAR', 'GRADUADO', 'GRANDEZA', 'HABITUAR', 'HEROÍSMO', 'HORIZONT',
            'HOSPITAL', 'HUMANIDO', 'IDÊNTICO', 'IMAGINAR', 'IMEDIATO', 'IMPORTAR',
            'INCÊNDIO', 'INCLUIDO', 'INCRÍVEL', 'INDICADO', 'INFANTIL', 'INFORMAR',
            'INOCENTE', 'INSPIRAR', 'INTERIOR', 'INVENTAR', 'ISOLADO', 'JULGADOR',
            'LABORADO', 'LANÇADOS', 'LATITUDE', 'LEGISLAR', 'LEVANTAR', 'LIBERDAD',
            'LIMITADO', 'LINGUICA', 'LONGEVID', 'MATERIAL', 'MEDIEVAL', 'MEMORIAS',
            'MENSAGEM', 'MERCADOR', 'MISTERIO', 'MODERADO', 'MONTANHA', 'MORANGOS',
            'MOSTRADO', 'MOTIVADO', 'MUDANÇAS', 'MUNICIPIO','NACIONAL', 'NATUREZA',
            'NEGOCIAR', 'NOTURNAL', 'OBJETIVO', 'OBRIGADO', 'OBSERVAR', 'OCUPAÇÃO',
            'OFERECER', 'OPERAÇÃO', 'ORGANIZA', 'ORIGINAL', 'OTIMISMO', 'PACIENTE',
            'PAISAGEM', 'PALAVRAS', 'PARCEIRO', 'PASSAGEM', 'PASTILHA', 'PATERNAL',
            'PECULIAR', 'PENDENTE', 'PENSADOR', 'PERFUMAR', 'PERMITIR', 'PESQUISA',
            'POLICIAL', 'POLÍTICO', 'POPULOSO', 'POTÊNCIA', 'PRECIOSO', 'PREJUÍZO',
            'PREMISSA', 'PREPARAR', 'PRESENTE', 'PREVENIR', 'PRIMEIRO', 'PRINCÍPI',
            'PRODUCAO', 'PROFUNDO', 'PROIBIDO', 'PROMESSA', 'PROPOSTA', 'PROTETOR',
            'PROVOCAR', 'PRUDENTE', 'PUBLICAR', 'QUALIDAD', 'QUANTOS', 'QUEREMOS',
            'RACIOCIN', 'REALISTA', 'RECEBIDO', 'RECONHEC', 'RECUPERA', 'REFLEXÃO',
            'REGISTRO', 'RELATIVO', 'RENOVADO', 'RESOLVER', 'RESPEITO', 'RESULTAD',
            'SAUDAVEL', 'SEGMENTO', 'SEGURANC', 'SEMESTRE', 'SENSÍVEL', 'SERENATA',
            'SERVIDOS', 'SILVESTRE','SIMPLIFI', 'SINALIZÀ', 'SITUACAO', 'SOCIALIS',
            'SOLIDARI', 'SOLUCION', 'SUBSTITU', 'SUPERIOR', 'SUPREMOS', 'SURPRESA',
            'SUSPENSE', 'SUTIL', 'TELEFONE', 'TEMPORAL', 'TENTANDO', 'TESOURA',
            'TOLERANT', 'TRABALHA', 'TRANSFOR', 'TRIBUNAL', 'TROPICAL', 'UNIFORME',
            'UNIVERSO', 'UTILIZAR', 'VALIDADE', 'VALORIZA', 'VENDEDOR', 'VERGONHA',
            'VIGILANT', 'VIOLENTO', 'VISITANT', 'VITAMINA', 'VOCABULO', 'VOLUNTAR',
        ];
    }

    private function get9PlusLetters(): array
    {
        return [
            'ABANDONAR', 'ABORDAGEM', 'ACARICIAR', 'ADAPTACAO', 'ADMIRACAO',
            'AEROPORTO', 'AGRADECER', 'ALGODEIRO', 'ALIMENTAR', 'AMANHECER',
            'AMBICIOSO', 'AMPLITUDE', 'ANDAMENTO', 'ANIVERSARIO', 'APLICAÇÃO',
            'APRENDIZADO', 'APROVACAO', 'ARTESANAL', 'ASSEGURAR', 'ATIVIDADE',
            'ATENDENTE', 'AUTOMACAO', 'AUTONOMIA', 'AVALANCHE', 'AVENTURAS',
            'BRILHANTE', 'CABIMENTO', 'CALCULADO', 'CAPACIDADE', 'CARPINTEIRO',
            'CATASTROFE', 'CELEBRADO', 'CHOCOLATE', 'CIDADANIA', 'CLASSIFICAR',
            'COMBUSTÃO', 'COMPANHIA', 'COMPETIÇÃO', 'COMPLEXIDADE', 'COMPRIMENTO',
            'COMUNICAÇÃO', 'COMUNIDADE', 'CONDICIONAR', 'CONFERÊNCIA', 'CONFIANÇA',
            'CONSIDERAR', 'CONSTRUÇÃO', 'CONSULTÓRIO', 'CONTABILIZAR', 'CONTEMPORÂNEO',
            'CONTROLAR', 'CONVENCIDO', 'COORDENAR', 'CURIOSIDADE', 'DECORAÇÃO',
            'DEMOCRACIA', 'DEPARTAMENTO', 'DESAFIANTE', 'DESCONHECIDO', 'DESCOBERTA',
            'DESEMPENHO', 'DESPERTAR', 'DESTRUIÇÃO', 'DIFICULDADE', 'DIPLOMACIA',
            'DISCIPLINA', 'DISTRIBUIR', 'DIVERSIDADE', 'DOCUMENTAR', 'ECONOMIA',
            'EDUCACIONAL', 'EFICIÊNCIA', 'ELETRICIDADE', 'EMPREENDER', 'EMPRÉSTIMO',
            'ENCONTRADO', 'ENGENHARIA', 'ENTREVISTA', 'EQUILÍBRIO', 'ESCRITÓRIO',
            'ESPECIALISTA', 'ESPERANÇA', 'ESTRATÉGIA', 'EXPERIÊNCIA', 'EXPLORAÇÃO',
            'FELICIDADE', 'FERRAMENTA', 'FINALIDADE', 'FOTOGRAFIA', 'FREQUÊNCIA',
            'FUNDAMENTAL', 'GASTRONOMIA', 'GELADEIRA', 'GENEROSIDADE', 'GOVERNAMENTAL',
            'GRATIFICAÇÃO', 'HABILIDADE', 'HARMÔNICO', 'HEREDITÁRIO', 'ILUMINAÇÃO',
            'IMAGINAÇÃO', 'IMPORTÂNCIA', 'IMPRESSÃO', 'INCALCULÁVEL', 'INDEPENDENTE',
            'INFORMAÇÃO', 'INGREDIENTE', 'INSTRUMENTO', 'INTELIGÊNCIA', 'INTERESSANTE',
            'INVESTIMENTO', 'IRRESPONSÁVEL', 'LABORATÓRIO', 'LEGITIMIDADE', 'LITERATURA',
            'MADEIREIRA', 'MANIFESTAÇÃO', 'MATEMATICA', 'MEDICAMENTO', 'MEMORÁVEL',
            'MENTALIDADE', 'MINISTERIAL', 'MODERNIDADE', 'NATURALIDADE', 'NECESSIDADE',
            'OPORTUNIDADE', 'ORGANIZAÇÃO', 'ORIGINALIDADE', 'PARTICIPAÇÃO', 'PENSAMENTO',
            'PERCEPÇÃO', 'PERMANENTE', 'PERSONALIDADE', 'PLANEJAMENTO', 'PLATAFORMA',
            'POPULAÇÃO', 'POSSIBILIDADE', 'PREFERÊNCIA', 'PRESERVAÇÃO', 'PRIORIDADE',
            'PROCEDIMENTO', 'PROCESSAMENTO', 'PRODUTIVIDADE', 'PROFISSIONAL', 'PROGRAMAÇÃO',
            'PROPRIEDADE', 'PROSPERIDADE', 'PUBLICIDADE', 'QUESTIONÁRIO', 'RECOMPENSA',
            'RECONHECIMENTO', 'REFRIGERADOR', 'RELACIONAMENTO', 'REPRESENTANTE',
            'RESPONSÁVEL', 'RESTAURANTE', 'SEGURIDADE', 'SIGNIFICADO', 'SOLIDARIEDADE',
            'SUSTENTÁVEL', 'TECNOLOGIA', 'TEMPERATURA', 'TRABALHADOR', 'TRANSPARÊNCIA',
            'TRANSPORTE', 'TREINAMENTO', 'UNIVERSIDADE', 'VALORIZAÇÃO', 'VEGETARIANO',
            'VELOCIDADE', 'VULNERÁVEL', 'VOLUNTARIADO',
        ];
    }
}
