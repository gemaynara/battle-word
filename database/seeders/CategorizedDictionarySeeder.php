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
            'animais' => $this->getAnimais(),
            'alimentos' => $this->getAlimentos(),
            'corpo' => $this->getCorpo(),
            'natureza' => $this->getNatureza(),
            'objetos' => $this->getObjetos(),
            'verbos' => $this->getVerbos(),
            'adjetivos' => $this->getAdjetivos(),
            'profissoes' => $this->getProfissoes(),
        ];
    }

    private function getAnimais(): array
    {
        return [
            'ABELHA', 'AGUIA', 'ANTA', 'ARANHA', 'ARARA', 'ASNO', 'ATUM',
            'BALEIA', 'BARATA', 'BEIJA', 'BEZERRA', 'BODE', 'BOI', 'BORBOLETA',
            'BURRO', 'CABRA', 'CACHORRO', 'CAMELO', 'CAPIVARA', 'CARACOL',
            'CARANGUEJO', 'CARNEIRO', 'CAVALO', 'CERVO', 'CISNE', 'COALA',
            'COBRA', 'CODORNA', 'COELHO', 'CORUJA', 'CORVO', 'CROCODILO',
            'ELEFANTE', 'ESQUILO', 'FALCAO', 'FLAMINGO', 'FOCA', 'FORMIGA',
            'GALINHA', 'GALO', 'GAMBÁ', 'GARÇA', 'GATO', 'GIRAFA', 'GOLFINHO',
            'GORILA', 'GRILO', 'HAMSTER', 'HIENA', 'HIPOPOTAMO', 'IGUANA',
            'JABUTI', 'JACARE', 'JAGUATIRICA', 'LAGARTA', 'LAGARTO', 'LEAO',
            'LEBRE', 'LEOPARDO', 'LESMA', 'LOBO', 'LONTRA', 'LULA', 'MACACO',
            'MORCEGO', 'MOSCA', 'MULA', 'ONCA', 'OSTRA', 'OVELHA', 'PACA',
            'PANTERA', 'PAPAGAIO', 'PARDAL', 'PATO', 'PAVAO', 'PEIXE',
            'PELICANO', 'PERDIZ', 'PERIQUITO', 'PERU', 'PINGUIM', 'PIRANHA',
            'POLVO', 'POMBO', 'PORCO', 'PUMA', 'RAPOSA', 'RATO', 'RENA',
            'RINOCERONTE', 'SABIA', 'SALAMANDRA', 'SALMAO', 'SAPO', 'SARDINHA',
            'SERPENTE', 'TARTARUGA', 'TATU', 'TIGRE', 'TUCANO', 'TUBARAO',
            'URUBU', 'URSO', 'VACA', 'VEADO', 'ZEBRA',
        ];
    }

    private function getAlimentos(): array
    {
        return [
            'ABACATE', 'ABACAXI', 'ABOBRINHA', 'ACEROLA', 'AÇUCAR', 'AGRIAO',
            'ALFACE', 'ALHO', 'AMEIXA', 'AMENDOA', 'AMENDOIM', 'AMORA', 'ARROZ',
            'AVEIA', 'AZEITE', 'AZEITONA', 'BANANA', 'BATATA', 'BERINJELA',
            'BETERRABA', 'BISCOITO', 'BOLO', 'BRÓCOLIS', 'CACAU', 'CAFÉ',
            'CAJU', 'CANELA', 'CAQUI', 'CARNE', 'CASTANHA', 'CEBOLA', 'CENOURA',
            'CEREJA', 'CHOCOLATE', 'COCO', 'COUVE', 'CREME', 'CUSCUZ', 'DAMASCO',
            'ERVILHA', 'FARINHA', 'FEIJAO', 'FIGO', 'FRAMBOESA', 'FRANGO',
            'GENGIBRE', 'GOIABA', 'GRANOLA', 'GRÃO', 'INHAME', 'IOGURTE',
            'JABUTICABA', 'JACA', 'LARANJA', 'LEITE', 'LENTILHA', 'LIMAO',
            'LINGUICA', 'MAÇÃ', 'MAMAO', 'MANDIOCA', 'MANGA', 'MANTEIGA',
            'MARACUJA', 'MELANCIA', 'MELAO', 'MEL', 'MILHO', 'MORANGO',
            'MOSTARDA', 'NABO', 'NOZES', 'OVOS', 'PALMITO', 'PAMONHA',
            'PÊSSEGO', 'PIMENTA', 'PIPOCA', 'PITANGA', 'POLENTA', 'PRESUNTO',
            'PUDIM', 'QUEIJO', 'QUIABO', 'RABANETE', 'REPOLHO', 'ROMÃ',
            'SALADA', 'SALMAO', 'SALSA', 'SARDINHA', 'SOJA', 'SORVETE',
            'TAPIOCA', 'TOMATE', 'TORRADA', 'TRIGO', 'UVA', 'VINAGRE',
        ];
    }

    private function getCorpo(): array
    {
        return [
            'BARRIGA', 'BOCA', 'BRACO', 'CABECA', 'CABELO', 'CALCANHAR',
            'CANELA', 'CEREBRO', 'CINTURA', 'CLAVICULA', 'COLUNA', 'CORACAO',
            'COSTAS', 'COSTELA', 'COTOVELO', 'CRANIO', 'DEDO', 'DENTE',
            'ESTOMAGO', 'FIGADO', 'GARGANTA', 'JOELHO', 'LABIO', 'LÍNGUA',
            'MANDIBULA', 'MAO', 'MUSCÚLO', 'NARINA', 'NARIZ', 'NERVO',
            'OLHO', 'OMBRO', 'ORELHA', 'OSSO', 'PALMA', 'PANTURRILHA',
            'PEITO', 'PELE', 'PERNA', 'PESCOCO', 'POLEGAR', 'PULMAO',
            'PUNHO', 'QUADRIL', 'QUEIXO', 'RIM', 'SANGUE', 'SOBRANCELHA',
            'TESTA', 'TORNOZELO', 'UNHA', 'VEIA',
        ];
    }

    private function getNatureza(): array
    {
        return [
            'AGUA', 'AREIA', 'ARVORE', 'AURORA', 'BOSQUE', 'BRISA', 'CACTO',
            'CAMPO', 'CASCATA', 'CAVERNA', 'CERRADO', 'CHUVA', 'COLINA',
            'CORAL', 'CÓRREGO', 'COSTA', 'CRISTAL', 'DELTA', 'DESERTO',
            'DUNA', 'ECLIPSE', 'EROSÃO', 'ESTRELA', 'FAUNA', 'FLORA',
            'FLORESTA', 'FONTE', 'GELO', 'GLACIAR', 'GRUTA', 'ICEBERG',
            'ILHA', 'LAGOA', 'LAGO', 'LAVA', 'LITORAL', 'MANGUE', 'MATA',
            'MONTANHA', 'NASCENTE', 'NEBLINA', 'NEVE', 'NUVEM', 'OCEANO',
            'ONDA', 'ORVALHO', 'PANTANAL', 'PENHASCO', 'PLANICIE', 'PLANALTO',
            'PRAIA', 'RECIFE', 'RELEVO', 'RIACHO', 'RIO', 'ROCHA', 'SAVANA',
            'SELVA', 'SERRA', 'SOL', 'TEMPESTADE', 'TERRA', 'TORNADO',
            'TROVAO', 'TSUNAMI', 'VALE', 'VEGETACAO', 'VENTO', 'VULCAO',
        ];
    }

    private function getObjetos(): array
    {
        return [
            'AGENDA', 'AGULHA', 'ALMOFADA', 'ANEL', 'ARMARIO', 'BALDE',
            'BANDEJA', 'BANHEIRA', 'BICICLETA', 'BOLSA', 'BRINCO', 'CADEIRA',
            'CADERNO', 'CAIXA', 'CALCADO', 'CANETA', 'CANECA', 'CARRO',
            'CARTEIRA', 'CHAVE', 'COBERTOR', 'COLCHAO', 'COLAR', 'COPO',
            'CORTINA', 'COZINHA', 'ESCADA', 'ESCOVA', 'ESPELHO', 'ESTANTE',
            'FACA', 'FOGAO', 'GARFO', 'GARRAFA', 'GELADEIRA', 'GUARDA',
            'JANELA', 'LAMPADA', 'LAPIS', 'LIVRO', 'LUSTRE', 'MALA',
            'MANGUEIRA', 'MARTELO', 'MESA', 'MOEDA', 'OCULOS', 'PANELA',
            'PENTE', 'PIANO', 'PINCEL', 'PORTA', 'PRATO', 'QUADRO',
            'RADIO', 'RELOGIO', 'SABONETE', 'SACOLA', 'SAPATO', 'SOFA',
            'TAPETE', 'TESOURA', 'TOALHA', 'TRAVESSEIRO', 'VASO', 'VASSOURA',
            'VELA', 'VENTILADOR',
        ];
    }

    private function getVerbos(): array
    {
        return [
            'ABRIR', 'ACEITAR', 'ACHAR', 'ACORDAR', 'AGIR', 'AJUDAR', 'AMAR',
            'ANDAR', 'APAGAR', 'APRENDER', 'BATER', 'BEBER', 'BRINCAR',
            'BUSCAR', 'CAIR', 'CANTAR', 'CHAMAR', 'CHEGAR', 'COBRAR', 'COMER',
            'COMPRAR', 'CONHECER', 'CONTAR', 'CORRER', 'CORTAR', 'CRESCER',
            'CUIDAR', 'DANÇAR', 'DAR', 'DEIXAR', 'DEMORAR', 'DESCER',
            'DESEJAR', 'DIRIGIR', 'DIZER', 'DORMIR', 'DUVIDAR', 'EMPURRAR',
            'ENCONTRAR', 'ENSINAR', 'ENTENDER', 'ENTRAR', 'ENVIAR', 'ERGUER',
            'ERRAR', 'ESCOLHER', 'ESCREVER', 'ESCUTAR', 'ESPERAR', 'ESTUDAR',
            'EXISTIR', 'FALAR', 'FAZER', 'FECHAR', 'FICAR', 'FUGIR',
            'GANHAR', 'GASTAR', 'GOSTAR', 'GRITAR', 'GUARDAR', 'IMAGINAR',
            'INDICAR', 'INSISTIR', 'INVENTAR', 'JOGAR', 'JULGAR', 'JUNTAR',
            'LAVAR', 'LEMBRAR', 'LER', 'LEVAR', 'LIGAR', 'LIMPAR', 'LUTAR',
            'MANDAR', 'MARCAR', 'MATAR', 'MENTIR', 'MERECER', 'MEXER',
            'MORAR', 'MORRER', 'MOSTRAR', 'MUDAR', 'NADAR', 'NASCER',
            'NEGAR', 'NOTAR', 'OBEDECER', 'OBSERVAR', 'OBTER', 'ODIAR',
            'OLHAR', 'OUVIR', 'PAGAR', 'PARAR', 'PARECER', 'PARTIR',
            'PASSAR', 'PEDIR', 'PEGAR', 'PENSAR', 'PERDER', 'PERDOAR',
            'PERGUNTAR', 'PERMITIR', 'PESCAR', 'PINTAR', 'PODER', 'PREFERIR',
            'PREPARAR', 'PROIBIR', 'PROMETER', 'PROTEGER', 'PROVAR', 'PUXAR',
            'QUEBRAR', 'QUEIMAR', 'QUERER', 'RECEBER', 'RECLAMAR', 'RECUSAR',
            'REDUZIR', 'REPETIR', 'RESOLVER', 'RESPIRAR', 'REZAR', 'RIR',
            'ROUBAR', 'SABER', 'SAIR', 'SALTAR', 'SALVAR', 'SEGUIR',
            'SENTAR', 'SENTIR', 'SER', 'SERVIR', 'SOFRER', 'SONHAR',
            'SORRIR', 'SUBIR', 'SUMIR', 'SURGIR', 'TENTAR', 'TER', 'TIRAR',
            'TOCAR', 'TOMAR', 'TORCER', 'TRABALHAR', 'TRAZER', 'TROCAR',
            'UNIR', 'USAR', 'VALER', 'VENCER', 'VENDER', 'VER', 'VESTIR',
            'VIAJAR', 'VIVER', 'VOAR', 'VOLTAR', 'VOTAR',
        ];
    }

    private function getAdjetivos(): array
    {
        return [
            'AGIL', 'ALEGRE', 'ALTO', 'AMARGO', 'AMAVEL', 'AMPLO', 'ANTIGO',
            'ATENTO', 'AUDAZ', 'BAIXO', 'BARATO', 'BELO', 'BONITO', 'BRAVO',
            'BREVE', 'CALMO', 'CAPAZ', 'CARO', 'CERTO', 'CLARO', 'COMUM',
            'CORAJOSO', 'CORRETO', 'CRUEL', 'CURTO', 'DENSO', 'DIFICIL',
            'DIGNO', 'DOCE', 'DURO', 'EFICAZ', 'ENORME', 'ESPERTO', 'ESTÁVEL',
            'ETERNO', 'EXATO', 'FACIL', 'FALSO', 'FAMOSO', 'FELIZ', 'FIEL',
            'FINO', 'FIRME', 'FORTE', 'FRACO', 'FRANCO', 'FRIO', 'GENTIL',
            'GORDO', 'GRANDE', 'GRAVE', 'GROSSO', 'HUMILDE', 'IGUAL', 'IMENSO',
            'INCRÍVEL', 'INJUSTO', 'INTENSO', 'JOVEM', 'JUSTO', 'LARGO',
            'LEGAL', 'LENTO', 'LEVE', 'LIMPO', 'LINDO', 'LISO', 'LIVRE',
            'LONGO', 'LOUCO', 'MAGRO', 'MAIOR', 'MANSO', 'MAU', 'MENOR',
            'MOLE', 'MORENO', 'MORTO', 'MUITO', 'NOBRE', 'NOVO', 'OBVIO',
            'OTIMO', 'PERFEITO', 'PESADO', 'PLENO', 'POBRE', 'PODRE',
            'PRONTO', 'PURO', 'QUIETO', 'RAPIDO', 'RARO', 'REAL', 'RICO',
            'RIGIDO', 'RUDE', 'SABIO', 'SAGRADO', 'SANTO', 'SECO', 'SEGURO',
            'SERIO', 'SIMPLES', 'SINCERO', 'SOLIDO', 'SUAVE', 'SURDO',
            'TENSO', 'TIMIDO', 'TOLO', 'TRISTE', 'UNICO', 'UTIL', 'VAGO',
            'VAZIO', 'VELHO', 'VERDE', 'VITAL', 'VIVO',
        ];
    }

    private function getProfissoes(): array
    {
        return [
            'ADVOGADO', 'AGENTE', 'ANALISTA', 'ARBITRO', 'ARQUITETO', 'ARTISTA',
            'ATLETA', 'AUDITOR', 'AUTOR', 'BAILARINA', 'BARBEIRO', 'BIOLOGO',
            'BOMBEIRO', 'CANTOR', 'CAPITAO', 'CARPINTEIRO', 'CARTEIRO', 'CHEFE',
            'CIENTISTA', 'CIRURGIÃO', 'CONTADOR', 'COZINHEIRO', 'DENTISTA',
            'DESIGNER', 'DETETIVE', 'DIRETOR', 'EDITOR', 'ELETRICISTA',
            'ENFERMEIRO', 'ENGENHEIRO', 'ESCRITOR', 'ESCULTOR', 'ESTILISTA',
            'FARMACEUTICO', 'FILOSOFO', 'FISCAL', 'FISICO', 'FLORISTA',
            'FOTOGRAFO', 'GARÇOM', 'GEOLOGO', 'GERENTE', 'JORNALISTA',
            'JUIZ', 'LENHADOR', 'LOCUTOR', 'MAESTRO', 'MARINHEIRO', 'MECANICO',
            'MEDICO', 'MILITAR', 'MINISTRO', 'MOTORISTA', 'MUSICO',
            'PADEIRO', 'PASTOR', 'PEDREIRO', 'PILOTO', 'PINTOR', 'POETA',
            'POLICIAL', 'PORTEIRO', 'PREFEITO', 'PRODUTOR', 'PROFESSOR',
            'PROMOTOR', 'PSICOLOGO', 'QUIMICO', 'RADIALISTA', 'RELATOR',
            'REPORTER', 'REITOR', 'SENADOR', 'SOLDADO', 'TAXISTA', 'TECNICO',
            'TREINADOR', 'VENDEDOR', 'VETERINARIO', 'ZELADOR',
        ];
    }
}
