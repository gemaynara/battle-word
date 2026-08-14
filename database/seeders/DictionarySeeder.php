<?php

namespace Database\Seeders;

use App\Models\DictionaryWord;
use Illuminate\Database\Seeder;

class DictionarySeeder extends Seeder
{
    /**
     * Seed the dictionary_words table with ~500 common Portuguese words.
     * Words are varied in length (2-12+ chars) to support game mechanics.
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
     * Return ~500 common Portuguese words across varied lengths.
     *
     * @return string[]
     */
    private function getWords(): array
    {
        return [
            // 2-letter words
            'AR', 'AO', 'DE', 'DO', 'EM', 'EU', 'IR', 'JA', 'LA', 'ME',
            'NO', 'OU', 'SE', 'UM', 'VA',

            // 3-letter words
            'AGO', 'ANO', 'ATE', 'BEM', 'BOA', 'CEM', 'CHA', 'COR', 'DAR', 'DIA',
            'DOM', 'ELA', 'ERA', 'FAZ', 'FIM', 'FOI', 'GIZ', 'IRA', 'LAR', 'LEI',
            'LER', 'LUA', 'MAE', 'MAR', 'MAS', 'MEU', 'MIL', 'NEM', 'NOS', 'OCA',
            'PAI', 'PAR', 'POR', 'RUA', 'SAL', 'SER', 'SOL', 'TER', 'UMA', 'VER',
            'VEZ', 'VOZ', 'CRU', 'RIM', 'CAS',

            // 4-letter words
            'AGUA', 'ALMA', 'ALTO', 'AMOR', 'ARCO', 'ASAS', 'BALA', 'BELO', 'BOCA',
            'BOLA', 'CAFE', 'CAMA', 'CAPA', 'CARA', 'CASA', 'CEDO', 'CENA', 'COMO',
            'COPA', 'DADO', 'DATA', 'DEDO', 'DOCE', 'DOIS', 'DONO', 'FACA', 'FAME',
            'FASE', 'FATO', 'FILA', 'FINO', 'FOGO', 'FOME', 'GATO', 'GELO', 'GUIA',
            'HORA', 'ILHA', 'JOGO', 'LADO', 'LAGO', 'LATA', 'LEVE', 'LIMA', 'LINHA',
            'LOBO', 'LOJA', 'MALA', 'MAPA', 'MESA', 'META', 'MEDO', 'MEIA', 'MODA',
            'MOLA', 'MURO', 'NAVE', 'NEVE', 'NADA', 'NOME', 'NOTA', 'NOVO', 'OBRA',
            'OITO', 'OLHO', 'ONDE', 'OURO', 'ONDA', 'PAGO', 'PANO', 'PARA', 'PATA',
            'PELE', 'PESO', 'PICO', 'PISO', 'POTE', 'POUCO', 'RAMO', 'RATO', 'REDE',
            'REIS', 'RICO', 'RISO', 'RODA', 'ROSA', 'SALA', 'SECO', 'SEDE', 'SEIS',
            'SINO', 'SOPA', 'TELA', 'TEMA', 'TIPO', 'TODA', 'TOPO', 'TRIO', 'TUBO',
            'URSO', 'VACA', 'VALE', 'VASO', 'VELA', 'VIDA', 'VILA', 'VOTO', 'ZONA',

            // 5-letter words
            'ABRIR', 'ABRIL', 'AINDA', 'AMIGO', 'ANDAR', 'ANTES', 'APOIO', 'AREIA',
            'BARCO', 'BANCO', 'BAIXO', 'BICHO', 'BRACO', 'BREVE', 'BURRO', 'CAMPO',
            'CAIXA', 'CALOR', 'CARTA', 'CAUSA', 'CERCA', 'CHAVE', 'CLARO', 'COBRA',
            'COMER', 'CONTA', 'CORDA', 'CORPO', 'CRISE', 'CRUDE', 'DANCA', 'DESDE',
            'DIZER', 'DORSO', 'DUVIR', 'ENTRE', 'EXATO', 'FAROL', 'FESTA', 'FIBRA',
            'FINAL', 'FIRME', 'FORMA', 'FORTE', 'FRASE', 'FRUTA', 'GERAL', 'GOLPE',
            'GRADE', 'GRUPO', 'IDADE', 'IGUAL', 'JOVEM', 'JUSTO', 'LAPIS', 'LARGO',
            'LEITE', 'LIMPO', 'LISTA', 'LIVRO', 'LUGAR', 'MAGRO', 'MAIOR', 'MANGA',
            'MARCO', 'MATAR', 'MEDIA', 'MENOR', 'MESES', 'METRO', 'MILHO', 'MINHA',
            'MOEDA', 'MONTE', 'MORAL', 'MOTOR', 'MUITO', 'MUNDO', 'MUSEU', 'NAVIO',
            'NOITE', 'NOSSO', 'NUVEM', 'NUNCA', 'OBVIO', 'OLHAR', 'ORDEM', 'OUVIR',
            'PADRE', 'PALCO', 'PAPEL', 'PARCO', 'PASSO', 'PASTA', 'PATIO', 'PEDRA',
            'PEITO', 'PERDA', 'PESAR', 'PISTA', 'PLANO', 'PLUMA', 'PODER', 'PONTO',
            'PORTA', 'POSSE', 'PRAZO', 'PRECO', 'PRIMO', 'PROVA', 'PULSO', 'QUEDA',
            'RAIVA', 'RAPAZ', 'RELVA', 'RITMO', 'ROCHA', 'ROLAR', 'ROUPA', 'SABOR',
            'SALTO', 'SANTO', 'SINAL', 'SOBRE', 'SONHO', 'SORTE', 'SUBIR', 'SUAVE',
            'SURDO', 'TANTO', 'TARDE', 'TERRA', 'TEXTO', 'TIGRE', 'TOTAL', 'TRAGO',
            'TRAPO', 'TURMA', 'UNICO', 'USADO', 'VALOR', 'VELHO', 'VERDE', 'VIGOR',
            'VINTE', 'VIRAR', 'VITAL', 'VOLTA', 'VOTAR',

            // 6-letter words
            'ABERTO', 'ACORDO', 'ALEGRE', 'ALIADO', 'AMARGO', 'AMAVEL', 'ANIMAL',
            'ANTIGO', 'ARVORE', 'ATAQUE', 'BAIRRO', 'BALEIA', 'BONITO', 'BORDAR',
            'BRANCO', 'BRUTAR', 'CABECA', 'CADEIA', 'CAMELO', 'CANETA', 'CENTRO',
            'CHEGAR', 'CIDADE', 'COBRIR', 'COELHO', 'COLHER', 'COMPRA', 'CONTAR',
            'COZIDO', 'DENTRO', 'DEPOIS', 'DIARIO', 'DORMIR', 'EFEITO', 'ESTADO',
            'ESTUDO', 'FERVER', 'FILMAR', 'FRANGO', 'FUTURO', 'GARRAFA', 'GLOBAL',
            'GRITAR', 'GUERRA', 'HUMANO', 'IGREJA', 'JANELA', 'JARDIM', 'JORNAL',
            'LATERAL', 'LIGADO', 'LIMPAR', 'MANDAR', 'MENINA', 'MINUTO', 'MODULO',
            'MORADA', 'MOTIVO', 'MUSICA', 'NAMORO', 'NATURA', 'NUMERO', 'OBJETO',
            'OCEANO', 'OFERTA', 'PADRAO', 'PALITO', 'PASSADO', 'PENSAR', 'PESSOA',
            'PINTAR', 'PLANTA', 'PREDIO', 'RAPIDO', 'RECIBO', 'RESUME', 'SABADO',
            'SALADA', 'SEGURO', 'SEMPRE', 'SENHOR', 'SESSAO', 'SINAIS', 'SISTEMA',
            'TOMATE', 'TREINO', 'TRISTE', 'UNIDADE', 'VENDAS', 'VIAJAR', 'VIRAGO',
            'VOLUME',

            // 7-letter words
            'ABACATE', 'ABRINDO', 'ACREDITO', 'ALERGIA', 'ALIMENTO', 'AMARELO',
            'ANALISE', 'APLICAR', 'AQUELAS', 'ASSUNTO', 'AVANCAR', 'BAIXADA',
            'BALANCE', 'BARULHO', 'BASEADO', 'BATALHA', 'CABRITO', 'CAMINHO',
            'CAPITAL', 'CARINHO', 'CERVEJA', 'CIENCIA', 'CIMENTO', 'CLIENTE',
            'COLUNA', 'COMBATE', 'COMEDIA', 'COMENDO', 'CONSELHO', 'CORRETO',
            'CRIANCA', 'CULTURA', 'DEFEITO', 'DEMANDA', 'DESTINO', 'DIREITO',
            'DIVERSO', 'DOMINGO', 'DURANTE', 'EDITORA', 'EFEITOS', 'ELEVADO',
            'EMPRESA', 'ENERGIA', 'EQUIPAR', 'ESFORCO', 'ESPELHO', 'ESTRELA',
            'EXEMPLO', 'FAMILIA', 'FAZENDA', 'FICANDO', 'GOVERNO', 'GRANDAO',
            'HARMONIA', 'IMAGEM', 'IMPACTO', 'JANTAR', 'LARANJA', 'LEITURA',
            'LIBERAL', 'LIGACAO', 'MADEIRA', 'MAQUINA', 'MENTIRA', 'MERCADO',
            'MILENIA', 'MODERNA', 'MOMENTO', 'NENHUMA', 'NOVENTA', 'OUTUBRO',
            'PALAVRA', 'PALMITO', 'PARCELA', 'PARTIDA', 'PEIXOTO', 'PEQUENO',
            'PIEDADE', 'PINTURA', 'PLANETA', 'POPULAR', 'PRATICA', 'PRESENTE',
            'PRINCESA', 'PROBLEMA', 'PRODUTO', 'PROJETO', 'PUBLICO', 'QUANTIA',
            'QUERIDO', 'REGULAR', 'RESGATE', 'ROTEIRO', 'SAUDADE', 'SEGUNDA',
            'SERVICO', 'SIMPLES', 'SOLIDAO', 'SUCESSO', 'SUPREMO', 'TAMANHO',
            'TERCEIRO', 'TRABALHO', 'TURISMO', 'UNIDADE', 'VALENTE', 'VARIADO',
            'VERDADE', 'VIBRANT', 'VITORIA', 'VIZINHO', 'XADREZ',

            // 8-letter words
            'ABSOLUTO', 'ACADEMIA', 'ADEQUADO', 'AJUDANTE', 'AMBIENTE', 'APARELHO',
            'ATIVIDADE', 'AVENTURA', 'BASTANTE', 'BICICLO', 'BONDADE', 'CAMAROTE',
            'CAMPANHA', 'CAPRICHO', 'CARACTER', 'CELEBRAR', 'COMPARAR', 'COMPLEXO',
            'COMUNICA', 'CONFIAR', 'CONJUNTO', 'CONTEUDO', 'CONTROLE', 'CONVERSA',
            'CORRENTE', 'COSTELAS', 'CRIMINAL', 'DELICADO', 'DESCONTO', 'DETALHES',
            'DINHEIRO', 'DINAMICA', 'DIPLOMATA', 'DISTANTE', 'DIVIDIDO', 'DOMESTICO',
            'ENCAIXAR', 'ENTENDER', 'ENTREGAR', 'ESCREVER', 'ESPECIAL', 'ESPERADO',
            'ESTRANHO', 'EVENTUAL', 'EXAMINAR', 'EXERCITO', 'FAMILIAR', 'FEMININO',
            'FESTEIRO', 'FLORESTA', 'FORMACAO', 'FUNCIONAL', 'GARANTIA', 'GENEROSO',
            'GOVERNAR', 'GRANDEZA', 'IMPORTAR', 'INCENDIO', 'INFORMAL', 'INCRIVEL',
            'INTERIOR', 'JURAMENT', 'LABORAR', 'LIDERANCA', 'MATERIAL', 'MEMORIAS',
            'MERCADOR', 'MODERNIZAR', 'MONTANHA', 'NATUREZA', 'NEGOCIAR', 'OPERACAO',
            'ORGANIZAR', 'PAISAGEM', 'PARCEIRO', 'PASSAGEM', 'PASSEIO', 'PEQUENAS',
            'PERSISTIR', 'PETROLEIRO', 'POLICIAL', 'POPULACAO', 'POTENCIA', 'PRECIOSO',
            'PREJUIZO', 'PRESENTE', 'PRINCIPIO', 'PROPOSTA', 'PROTETOR', 'QUALIDADE',
            'QUEREMOS', 'REALISTA', 'REGISTRO', 'RENOVAR', 'RESOLVER', 'RESPEITO',
            'SABEDORIA', 'SAUDAVEL', 'SEGURANCA', 'SENTINDO', 'SERVICOS', 'SILVESTRE',
            'SIMBOLO', 'SITUACAO', 'SUPERIOR', 'SURPRESA', 'TELEFONE', 'TEMPORAL',
            'TESOURA', 'TOLERADO', 'TRABALHAR', 'UNIVERSO', 'UTILIZANDO', 'VENDEDOR',
            'VERDADEIRO', 'VIGILANTE', 'VIOLENTO', 'VISITANTE',

            // 9+ letter words (longer words for bonus scoring)
            'ABANDONAR', 'ABELHINHA', 'ABORDAGEM', 'ACADEMIA', 'ADAPTACAO',
            'ADMIRADOR', 'AEROPORTO', 'ALGORITMO', 'AMPLITUDE', 'ANIVERSARIO',
            'APLICACAO', 'APROVACAO', 'ARGENTINA', 'ARTESANAL', 'ASSEGURAR',
            'ATENDENTE', 'AUTOMACAO', 'AVENTURAS', 'BRASILEIRA', 'CALCULADO',
            'CAPACIDADE', 'CARDAPIO', 'CARPINTEIRO', 'CATASTROFE', 'CHOCOLATE',
            'COMBUSTIVEL', 'COMPRIMENTO', 'COMUNICACAO', 'CONFERENCIA', 'CONSIDERAR',
            'CONSTRUCAO', 'CONSULTORIO', 'CONTROLAR', 'COORDENACAO', 'CORRESPONDENTE',
            'CURIOSIDADE', 'DECORACAO', 'DEMOCRACIA', 'DEPARTAMENTO', 'DESCONHECIDO',
            'DESENVOLVIMENTO', 'DESPERTADOR', 'DIPLOMACIA', 'DESCOBERTA', 'DISCRIMINACAO',
            'ELETRICIDADE', 'EMPRESTIMO', 'ENGARRAFAMENTO', 'ENGENHARIA', 'ENTREVISTA',
            'EQUILIBRIO', 'ESCRITORIO', 'ESPECIALISTA', 'EXPERIENCIA', 'FERRAMENTA',
            'FOTOGRAFIA', 'FUNDAMENTAL', 'GASTRONOMIA', 'GELADEIRA', 'GOVERNAMENTAL',
            'HISTORICAMENTE', 'ILUMINACAO', 'INDEPENDENTE', 'INFRAESTRUTURA', 'INSTRUMENTO',
            'INTELIGENCIA', 'INTERESSANTE', 'INVESTIMENTO', 'LABORATORIO', 'LITERATURA',
            'MANIFESTACAO', 'MATEMATICA', 'MEDICAMENTO', 'MELODIA', 'MINISTERIO',
            'MULTINACIONAL', 'NATURALIDADE', 'NECESSIDADE', 'OPORTUNIDADE', 'ORGANIZACAO',
            'PARTICIPACAO', 'PENSAMENTO', 'PERSONALIDADE', 'PLATAFORMA', 'PRESERVACAO',
            'PROCESSAMENTO', 'PROFISSIONAL', 'PROGRAMACAO', 'PROPRIEDADE', 'QUESTIONARIO',
            'RECONHECIMENTO', 'RELACIONAMENTO', 'RESPONSAVEL', 'RESTAURANTE', 'SUSTENTAVEL',
            'TECNOLOGIA', 'TEMPERATURA', 'TRABALHADOR', 'TRANSPORTE', 'TREINAMENTO',
            'UNIVERSIDADE', 'VEGETARIANO', 'VELOCIDADE', 'VOLUNTARIO',
        ];
    }
}
