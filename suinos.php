<?php
declare(strict_types=1);
require_once 'includes/auth.php';
session_init(); // conteúdo público — não exige login

$pagina        = 'suinos';
$titulo_pagina = 'Suínos';

$animal = [
    'nome'     => 'Suínos',
    'emoji'    => '🐷',
    'imagem'   => 'https://images.unsplash.com/photo-1587213128862-80345e23a71a?w=1400&q=80&auto=format&fit=crop',
    'descricao'=> 'Criação de suínos no Maranhão: orientações adaptadas ao clima quente do estado para raças comerciais e locais, muito presentes na agricultura familiar.',
    'racas' => [
        ['emoji'=>'🐷','nome'=>'Large White', 'tipo'=>'Corte', 'imagem'=>'https://images.unsplash.com/photo-1616109259043-fd30a7663a5d?w=600&q=80&auto=format&fit=crop','desc'=>'Raça britânica (Yorkshire), orelhas eretas, corpo longo e pelagem branca uniforme com capa de toucinho fina. Ganho médio de 850–950 g/dia, conversão alimentar 2,2–2,5:1. Abate com 100–120 kg aos 150–170 dias. Prolificidade: 12–14 leitões/parto. Exige nebulização ou aspersão no telhado acima de 28°C no clima maranhense.'],
        ['emoji'=>'🐖','nome'=>'Landrace',    'tipo'=>'Corte', 'imagem'=>'assets/img/racas/suinos/landrace.jpg','desc'=>'Raça escandinava com carcaça extra-longa (76–81 cm) e orelhas grandes totalmente caídas para a frente — distinção visual principal. Base do presunto e lombo industriais. Conversão 2,3–2,6:1, abate com 95–115 kg. Altamente sensível ao calor (zona de conforto: 16–22°C) — exige sombra, aspersão e ventilação cruzada obrigatórias no Maranhão.'],
        ['emoji'=>'🐗','nome'=>'Piau',        'tipo'=>'Misto', 'imagem'=>'assets/img/racas/suinos/piau.jpg','desc'=>'Raça nacional de origem ibérica (nome tupi: "malhado"), pelagem creme com manchas pretas irregulares. Ganho diário de 469 g e carcaça resfriada de 71 kg (Embrapa). Alta rusticidade ao calor e à alimentação alternativa (resíduos de mandioca, milho, farelo de coco). Risco de extinção — menos de 1.000 animais puros registrados no PBB. Ideal para sistemas extensivos do Nordeste.'],
    ],
    'topicos' => [

        [
            'icone'  => '🌿',
            'titulo' => 'Nutrição e Ração',
            'porque' => 'Na suinocultura a ração é 70% a 80% do custo. Isso quer dizer que o '
                      . 'lucro não está no preço do porco — está no quanto de ração você gasta '
                      . 'para fazer cada quilo. Ração errada para a fase é dinheiro jogado no '
                      . 'esterco.',
            'passos' => [
                ['acao'    => 'Troque a ração conforme a fase',
                 'detalhe' => 'Leitão precisa de muita proteína; porco em terminação precisa '
                            . 'de energia. Dar ração de terminação para leitão atrasa o '
                            . 'crescimento; dar ração de creche para porco grande é queimar '
                            . 'dinheiro.'],
                ['acao'    => 'Água à vontade, sempre',
                 'detalhe' => 'O suíno bebe 2 a 5 litros para cada quilo de ração. Bebedouro '
                            . 'entupido ou com pouca pressão derruba o consumo de ração no '
                            . 'mesmo dia. Confira a vazão dos chupetas toda semana.'],
                ['acao'    => 'Aproveite o que a região dá — com critério',
                 'detalhe' => 'Mandioca, milho, farelo de coco e babaçu podem entrar na dieta '
                            . 'e baratear muito, mas a mistura precisa ser balanceada por um '
                            . 'técnico. Mandioca crua em excesso intoxica.'],
                ['acao'    => 'Nunca dê lavagem com restos de carne',
                 'detalhe' => 'Alimentar suíno com resto de comida que contenha carne é '
                            . 'proibido no Brasil e é a porta de entrada da peste suína '
                            . 'clássica. Isso não é opinião: é lei.'],
                ['acao'    => 'Guarde a ração no seco',
                 'detalhe' => 'Saco no chão úmido dá fungo, e a micotoxina do fungo causa '
                            . 'aborto na matriz e queda de crescimento sem sinal claro de '
                            . 'doença.'],
            ],
            'numeros' => [
                ['Creche (28–63 dias)',       'ração 20%–22% PB, 500 a 900 g/dia'],
                ['Crescimento (63–100 dias)', 'ração 16%–18% PB, 1,2 a 2,0 kg/dia'],
                ['Terminação (100–150 dias)', 'ração 14%–16% PB, 2,0 a 3,0 kg/dia'],
                ['Matriz gestante',           '2,0 a 2,5 kg/dia (não deixar engordar)'],
                ['Água',                      '2 a 5 litros por kg de ração'],
                ['Lavagem com carne',         'proibida por lei — risco de peste suína'],
            ],
            'alerta' => [
                'Lote parando de comer de uma hora para outra',
                'Abortos em mais de uma matriz (suspeita de micotoxina ou doença reprodutiva)',
                'Ração com mofo, cheiro estranho ou empedrada',
                'Animais com diarreia e perda rápida de peso',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🏠',
            'titulo' => 'Ambiência e Calor',
            'porque' => 'Porco não sua. Ele perde calor pela respiração e pelo contato com '
                      . 'superfície fria — só isso. Num dia de 35°C no Maranhão, ele come '
                      . 'menos para não esquentar mais, e o ganho de peso despenca. Sombra, '
                      . 'vento e água fresca valem quilo.',
            'passos' => [
                ['acao'    => 'Molhe o animal, não a ração',
                 'detalhe' => 'Aspersão em gotas grossas no lombo, ligada nas horas quentes '
                            . '(10h às 16h), em ciclos: molha e deixa secar. Nebulização fina '
                            . 'em galpão fechado só aumenta a umidade e piora.'],
                ['acao'    => 'Sombra e telhado que não irradia calor',
                 'detalhe' => 'Beiral largo, pé-direito alto e, se possível, forração ou '
                            . 'pintura clara no telhado. Em sistema extensivo, o chafurdo '
                            . '(área de lama) é o ar-condicionado do porco.'],
                ['acao'    => 'Cuidado inverso com o leitão',
                 'detalhe' => 'O recém-nascido sente frio: precisa de 30°C a 32°C no escamoteador, '
                            . 'com lâmpada ou campânula, enquanto a mãe precisa de ambiente '
                            . 'fresco. São duas necessidades opostas na mesma baia.'],
                ['acao'    => 'Respeite a área por animal',
                 'detalhe' => 'Baia lotada é mais calor, mais briga, mais rabo mordido e menos '
                            . 'ganho. É o erro que mais aparece em criação pequena.'],
                ['acao'    => 'Mantenha o piso seco',
                 'detalhe' => 'Piso parcialmente ripado ou com boa caída para o esgoto. Piso '
                            . 'molhado e escorregadio causa lesão de pata e mancada em matriz.'],
            ],
            'numeros' => [
                ['Conforto — crescimento',  'entre 18°C e 22°C'],
                ['Acima de 28°C',           'consumo de ração e ganho de peso caem'],
                ['Escamoteador do leitão',  'entre 30°C e 32°C'],
                ['Área — leitão',           'cerca de 1,5 m² por animal'],
                ['Área — crescimento',      'cerca de 2,0 m² por animal'],
                ['Área — terminação',       'cerca de 2,5 m² por animal'],
            ],
            'alerta' => [
                'Animais ofegantes, deitados e amontoados no ponto mais frio da baia',
                'Consumo de ração caindo nos dias mais quentes',
                'Leitões amontoados e gritando (frio no escamoteador)',
                'Aumento de brigas e mordedura de cauda (superlotação ou estresse)',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '💉',
            'titulo' => 'Sanidade e Vacinação',
            'porque' => 'O protocolo de vacina do suíno não é igual em toda propriedade: depende '
                      . 'do que já circulou ali. Mas duas coisas são iguais em qualquer lugar — '
                      . 'proteger a matriz antes da cobrição e proteger o leitão na creche.',
            'passos' => [
                ['acao'    => 'Matriz: parvovirose + leptospirose antes da cobrição',
                 'detalhe' => 'Cerca de 15 dias antes de cobrir, com reforço conforme a bula. '
                            . 'São as duas causas mais comuns de leitegada pequena, mumificada '
                            . 'ou abortada.'],
                ['acao'    => 'Leitão: circovirose (PCV2) a partir de 3 semanas',
                 'detalhe' => 'Onde a doença já apareceu, é a vacina que mais devolve dinheiro: '
                            . 'evita o refugo — aquele animal que come e não cresce.'],
                ['acao'    => 'Erisipela nas matrizes e reprodutores',
                 'detalhe' => 'Duas vezes ao ano nas matrizes, anual nos machos. A bactéria '
                            . 'vive no solo e é zoonose: pega em gente pela pele ferida.'],
                ['acao'    => 'Peste suína clássica: notificação obrigatória',
                 'detalhe' => 'O Maranhão está em zona livre de peste suína clássica. Por isso '
                            . 'não se vacina — e por isso qualquer suspeita tem que ser '
                            . 'comunicada imediatamente à AGED-MA. Confirme a situação atual '
                            . 'com o serviço veterinário antes de qualquer decisão.'],
                ['acao'    => 'Registre tudo',
                 'detalhe' => 'Data, produto, lote e quem aplicou. Sem isso não há '
                            . 'rastreabilidade nem defesa na fiscalização.'],
            ],
            'numeros' => [
                ['Parvo + lepto (matriz)',  'cerca de 15 dias antes da cobrição'],
                ['Circovirose (leitão)',    'a partir de 3 semanas de vida'],
                ['Erisipela (matriz)',      '2 vezes por ano'],
                ['Erisipela (reprodutor)',  '1 vez por ano'],
                ['Conservação',             'entre 2°C e 8°C, sem congelar'],
                ['Agulha',                  'trocada entre animais'],
            ],
            'alerta' => [
                'Vários animais com manchas avermelhadas na pele, febre e apatia',
                'Abortos, natimortos ou leitões mumificados repetidos',
                'Mortalidade alta e súbita — comunique à AGED-MA imediatamente',
                'Animais com tosse seca persistente no lote',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
                ['texto' => 'AGED-MA', 'url' => 'https://www.aged.ma.gov.br/'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🤝',
            'titulo' => 'Manejo e Reprodução',
            'porque' => 'A matriz é a máquina da granja: o que decide o resultado do ano é '
                      . 'quantos leitões ela desmama. E a maior parte das perdas acontece nas '
                      . 'primeiras 72 horas de vida do leitão — esmagamento, frio e falta de '
                      . 'colostro.',
            'passos' => [
                ['acao'    => 'Cubra a leitoa pelo peso e no 2º ou 3º cio',
                 'detalhe' => 'Em torno de 7 a 8 meses e 120 a 130 kg. Cobrir no primeiro cio '
                            . 'dá leitegada pequena e encurta a vida útil da matriz.'],
                ['acao'    => 'Prepare o parto',
                 'detalhe' => 'Lave a matriz antes de entrar na maternidade e deixe o '
                            . 'escamoteador aquecido. Assista ao parto: leitão preso ou matriz '
                            . 'parada há mais de 30 minutos entre leitões precisa de ajuda.'],
                ['acao'    => 'Colostro nas primeiras 6 horas',
                 'detalhe' => 'O leitão nasce sem defesa nenhuma — tudo vem do colostro, e o '
                            . 'intestino só absorve nas primeiras horas. Garanta que o menor '
                            . 'mame no teto da frente, que dá mais leite.'],
                ['acao'    => 'Uniformize as leitegadas em 24 horas',
                 'detalhe' => 'Passe leitões da mãe com muitos para a mãe com tetos sobrando, '
                            . 'só depois de todos terem mamado o colostro da própria mãe.'],
                ['acao'    => 'Umbigo, ferro e desmame na hora certa',
                 'detalhe' => 'Iodo a 10% no umbigo ao nascer; ferro injetável até o 3º dia '
                            . '(o leite da porca não tem ferro suficiente); desmame entre 21 '
                            . 'e 28 dias.'],
            ],
            'numeros' => [
                ['Gestação',            '114 dias (3 meses, 3 semanas e 3 dias)'],
                ['Cio',                 'a cada 21 dias'],
                ['1ª cobertura',        '7 a 8 meses e 120 a 130 kg, no 2º ou 3º cio'],
                ['Ferro no leitão',     'até o 3º dia de vida'],
                ['Desmame',             '21 a 28 dias'],
                ['Descarte da matriz',  'acima de 8 partos ou baixo desempenho'],
            ],
            'alerta' => [
                'Matriz mais de 30 minutos entre leitões, ou parto parado',
                'Matriz sem leite após o parto, com febre e úbere duro (mamite)',
                'Muitos leitões esmagados ou mortos nas primeiras 72 horas',
                'Leitões pálidos e sem força (falta de ferro)',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🛡️',
            'titulo' => 'Biosseguridade e Dejetos',
            'porque' => 'Quase toda doença cara da suinocultura entra pela porteira: no animal '
                      . 'comprado, na bota do visitante, no caminhão. E o dejeto, se mal '
                      . 'manejado, contamina o poço da própria família. Aqui saúde do rebanho '
                      . 'e saúde de quem mora na propriedade andam juntas.',
            'passos' => [
                ['acao'    => 'Tudo dentro, tudo fora',
                 'detalhe' => 'O lote entra junto e sai junto. Entre um lote e outro: lava, '
                            . 'desinfeta e deixa vazio pelo menos 15 dias. Misturar idades na '
                            . 'mesma baia é manter a doença circulando para sempre.'],
                ['acao'    => 'Quarentena de 21 dias para animal comprado',
                 'detalhe' => 'Longe do plantel, observado e com exame quando indicado. Compre '
                            . 'de granja com procedência conhecida e exija a GTA.'],
                ['acao'    => 'Controle a entrada de gente e de veículo',
                 'detalhe' => 'Bota e macacão da granja, pedilúvio na porta e nada de visitar '
                            . 'outra criação e voltar no mesmo dia. Caminhão não encosta na '
                            . 'baia.'],
                ['acao'    => 'Trate o dejeto — biodigestor ou esterqueira',
                 'detalhe' => 'Dejeto cru na água contamina poço e rio e é infração ambiental. '
                            . 'Depois de tratado, vira adubo bom para a lavoura e o '
                            . 'biodigestor ainda dá gás para a cozinha.'],
                ['acao'    => 'Proteja quem maneja',
                 'detalhe' => 'Leptospirose, salmonela e erisipela pegam em gente. Luva, bota '
                            . 'e lavar as mãos antes de comer não é frescura.'],
            ],
            'numeros' => [
                ['Vazio sanitário',    'mínimo 15 dias entre lotes'],
                ['Quarentena',         '21 dias'],
                ['Pedilúvio',          'na entrada, desinfetante trocado'],
                ['Dejeto',             'biodigestor ou esterqueira, nunca direto na água'],
                ['Documento',          'GTA obrigatória para transportar animal'],
            ],
            'alerta' => [
                'Doença aparecendo logo depois de entrar animal novo',
                'Cheiro forte ou dejeto escorrendo para fora da propriedade',
                'Mortalidade alta e súbita — comunique à AGED-MA',
                'Pessoa da propriedade com febre alta após contato com os animais (procure atendimento e informe o contato)',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
                ['texto' => 'AGED-MA', 'url' => 'https://www.aged.ma.gov.br/'],
            ],
            'revisado' => 'agosto/2026',
        ],

    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
