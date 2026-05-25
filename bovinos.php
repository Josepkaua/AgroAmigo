<?php
declare(strict_types=1);
require_once 'includes/auth.php';
require_login('index.php');

$pagina        = 'bovinos';
$titulo_pagina = 'Bovinos';

$animal = [
    'nome'     => 'Bovinos',
    'emoji'    => '🐄',
    'imagem'   => 'https://images.unsplash.com/photo-1588152850700-c82ecb8ba9b1?w=1400&q=80&auto=format&fit=crop',
    'descricao'=> 'Orientações técnicas para criação de bovinos de corte e leite no Maranhão, com foco nas raças mais adaptadas ao clima quente e úmido da região.',
    'racas' => [
        ['emoji'=>'🐂','nome'=>'Nelore',    'tipo'=>'Corte',      'imagem'=>'https://images.unsplash.com/photo-1566040924976-f837330d1a5b?w=600&q=80&auto=format&fit=crop', 'desc'=>'Zebuíno de origem indiana (raça Ongole), pelagem branco-acinzentada, pele escura e corcova proeminente. Resistência natural ao carrapato (Rhipicephalus microplus) e à tristeza parasitária. Ganho a pasto: 400–600 g/dia. Abate com 450–520 kg (rendimento de carcaça 52–54%). Padrão dominante de corte no Maranhão e Tocantins. Abate médio: 30–36 meses a pasto.'],
        ['emoji'=>'🐄','nome'=>'Girolando', 'tipo'=>'Leite/Misto','imagem'=>'https://images.unsplash.com/photo-1498191923457-88552caeccb3?w=600&q=80&auto=format&fit=crop', 'desc'=>'Síntese brasileira entre Gir (37,5–87,5%) e Holandês, desenvolvida pela Embrapa. Combina rusticidade tropical com produção leiteira: 10–20 litros/dia em sistema semi-intensivo no Maranhão. Gordura 3,5–4,2%. Resiste ao carrapato e ao calor (ITGU conforto até 79). Raça mais criada na bacia leiteira maranhense. Padrão recomendado: 5/8 Holandês × 3/8 Gir.'],
        ['emoji'=>'🐃','nome'=>'Gir',       'tipo'=>'Leite',      'imagem'=>'https://images.unsplash.com/photo-1583364428520-fa6c5013c0c3?w=600&q=80&auto=format&fit=crop', 'desc'=>'Zebuíno indiano (origem Gujarat/Rajastão), coloração variável de amarelo-claro a vermelho-escuro, orelhas longas tubuladas e chanfro convexo (perfil de "papagaio"). Machos 600–750 kg, fêmeas 350–450 kg. Produção de leite: 8–14 litros/dia. Transpira mais que taurinos — melhor adaptação ao calor. Base genética do Girolando e do Guzerá leiteiro. Alta resistência à mosca-dos-chifres e ao berne.'],
    ],
    'topicos' => [
        [
            'titulo' => 'Ambiência',
            'icone'  => '🏠',
            'intro'  => "A ambiência adequada garante conforto, reduz estresse e melhora o desempenho produtivo. No Maranhão, o calor intenso exige atenção especial ao sombreamento e ao fornecimento de água.\n\nO sombreamento natural (árvores) ou artificial (telado) é fundamental. Cada bovino adulto precisa de pelo menos 4 m² de sombra. A temperatura de conforto térmico para bovinos é entre 10°C e 27°C.",
            'dicas'  => [
                'Ofereça no mínimo 50 a 100 litros de água limpa por animal por dia (mais em épocas quentes)',
                'Instale bebedouros em locais sombreados para incentivar o consumo de água',
                'Carga animal recomendada: 1 a 2 UA/hectare em pastagens de braquiária',
                'Evite superlotar o pasto — isso compromete a recuperação das forrageiras',
                'Cochos de sal mineral devem estar protegidos da chuva e de fácil acesso',
                'Curral de manejo deve ter piso antiderrapante e brete para contenção segura',
            ],
        ],
        [
            'titulo' => 'Vacinação',
            'icone'  => '💉',
            'intro'  => "A vacinação é a forma mais eficiente e econômica de prevenir doenças no rebanho bovino. No Maranhão, algumas vacinações são obrigatórias por lei estadual e federal.\n\nMantenha um calendário vacinal atualizado e registre todas as aplicações com data, lote e fabricante da vacina.",
            'dicas'  => [
                'Febre Aftosa: vacinação obrigatória em Abril e Outubro para todos os bovinos',
                'Brucelose: vacinar fêmeas entre 3 e 8 meses de idade (apenas uma vez na vida)',
                'Raiva bovina: vacinar anualmente em regiões com morcegos hematófagos',
                'Carbúnculo sintomático: vacinar bezerros entre 4 e 8 meses, reforço anual',
                'Botulismo: recomendado em regiões com histórico da doença, anualmente',
                'Guarde as vacinas em geladeira entre 2°C e 8°C e aplique com agulha limpa',
            ],
        ],
        [
            'titulo' => 'Nutrição',
            'icone'  => '🌿',
            'intro'  => "A pastagem é a base da alimentação dos bovinos no Maranhão. No entanto, durante a estação seca (junho a setembro), a qualidade e quantidade da forragem caem muito, exigindo suplementação.\n\nA suplementação mineral é indispensável o ano todo para garantir crescimento, reprodução e saúde do rebanho.",
            'dicas'  => [
                'Ofereça sal mineral específico para bovinos de corte ou leite — nunca sal comum de cozinha',
                'Na seca, use suplemento proteico-energético (ureia + milho + farelo) para manter o ganho de peso',
                'Bezerros em aleitamento: garanta acesso ao leite da mãe e introduza ração a partir dos 15 dias',
                'Vacas em lactação precisam de 15 a 25% a mais de energia e proteína na dieta',
                'Evite pastagem com excesso de oxalato (capim-napier jovem) — pode causar hipocalcemia',
                'Agua fresca e limpa é o nutriente mais importante: troque diariamente nos cochos',
            ],
        ],
        [
            'titulo' => 'Manejo',
            'icone'  => '🤝',
            'intro'  => "O manejo correto aumenta a produtividade, reduz perdas e garante o bem-estar animal. Estabeleça rotinas fixas para o rebanho adaptar-se e estresar menos.\n\nO manejo reprodutivo é especialmente importante para aumentar a taxa de natalidade e a rentabilidade da propriedade.",
            'dicas'  => [
                'Pese os animais mensalmente para acompanhar o ganho de peso — meta: 500g/dia em bezerros',
                'Desmame: realize entre 7 e 9 meses de idade (desmame precoce: 60-90 dias em casos específicos)',
                'IATF (Inseminação Artificial em Tempo Fixo): melhora a genética e concentra parições',
                'Identifique cada animal com brinco ou tatuagem para facilitar o controle individual',
                'Casqueamento: faça anualmente ou quando notar coxeamento no rebanho',
                'Vermifugue estrategicamente — use exame de contagem de ovos (OPG) antes de tratar',
            ],
        ],
        [
            'titulo' => 'Biosseguridade',
            'icone'  => '🛡️',
            'intro'  => "Biosseguridade é o conjunto de medidas para prevenir a entrada e a disseminação de doenças na propriedade. É mais barato prevenir do que tratar.\n\nEstabeleça protocolos claros e treine toda a equipe da propriedade para segui-los.",
            'dicas'  => [
                'Quarentena: isole animais novos por no mínimo 30 dias antes de juntar ao rebanho',
                'Controle de carrapatos (Rhipicephalus microplus): banhos estratégicos com acaricidas rotativos',
                'Descarte animais com brucelose ou tuberculose confirmadas — são zoonoses',
                'Limpe e desinfete o curral após entrada de animais externos',
                'Evite visitas desnecessárias de animais ou pessoas de outras propriedades',
                'Notifique imediatamente ao serviço veterinário estadual casos suspeitos de febre aftosa ou raiva',
            ],
        ],
    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
