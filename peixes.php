<?php
$pagina        = 'peixes';
$titulo_pagina = 'Peixes';

$animal = [
    'nome'     => 'Peixes',
    'emoji'    => '🐟',
    'descricao'=> 'A piscicultura é uma das atividades que mais cresce no Maranhão. Tilápia e Tambaqui são as espécies mais cultivadas, com grande potencial de rentabilidade para pequenos produtores.',
    'racas' => [
        ['emoji'=>'🐟','nome'=>'Tilápia do Nilo',  'tipo'=>'Corte', 'desc'=>'Oreochromis niloticus. Espécie mais criada no Brasil. Crescimento rápido, resistente e aceita bem ração. Adaptada a tanques e viveiros. Ciclo de 6 a 8 meses.'],
        ['emoji'=>'🐠','nome'=>'Tambaqui',          'tipo'=>'Corte', 'desc'=>'Colossoma macropomum. Peixe nativo do Amazonas, muito consumido no Nordeste. Crescimento excelente em viveiros. Resistente e de fácil manejo. Ciclo de 10 a 14 meses.'],
        ['emoji'=>'🐡','nome'=>'Tambacu (híbrido)', 'tipo'=>'Corte', 'desc'=>'Cruzamento entre Tambaqui e Pacu. Combina o crescimento rápido do tambaqui com a resistência do pacu. Popular em pisciculturas familiares do Nordeste.'],
    ],
    'topicos' => [
        [
            'titulo' => 'Ambiência',
            'icone'  => '🏠',
            'intro'  => "O viveiro escavado é o sistema mais utilizado na piscicultura familiar maranhense. A qualidade da água é o fator mais importante para o desempenho dos peixes — mais do que a alimentação ou qualquer outro insumo.\n\nMonitorar parâmetros como pH, oxigênio dissolvido e temperatura deve ser uma rotina semanal na piscicultura.",
            'dicas'  => [
                'Área do viveiro: 0,5 a 2 hectares (menor = mais fácil de manejar para iniciantes)',
                'Profundidade ideal: 1,2 a 1,8 m — muito raso esquenta demais; muito fundo dificulta a pesca',
                'pH ideal: 6,5 a 8,5 — corrija com calcário agrícola se o pH estiver abaixo de 6,5',
                'Oxigênio dissolvido: manter acima de 5 mg/L — use aeradores em viveiros de alta densidade',
                'Temperatura ideal: 26 a 30°C para tilápia; 28 a 32°C para tambaqui',
                'Renovação de água: 5 a 15% por dia no período seco para manter a qualidade',
            ],
        ],
        [
            'titulo' => 'Vacinação',
            'icone'  => '💉',
            'intro'  => "Na piscicultura, vacinações não são comuns como em outras espécies. O foco de biosseguridade é o monitoramento de parasitas e doenças através da observação do comportamento dos peixes e da qualidade da água.\n\nA prevenção por meio do manejo adequado é muito mais eficaz do que o tratamento de doenças já instaladas.",
            'dicas'  => [
                'Monitoramento de parasitas: coleta mensal de amostras de pele e brânquias para análise',
                'Monogenea (parasitas das brânquias): tratamento com sal grosso (2 a 3 kg/1.000 L) por imersão',
                'Argulus (piolho do peixe): visível a olho nu — use organofosforado com orientação veterinária',
                'Problemas bacterianos (feridas, apodrecimento de nadadeiras): use antibióticos com prescrição',
                'Peixes saltando ou boiando na superfície: sinal de falta de oxigênio — aeração emergencial',
                'Nunca use medicamentos sem orientação profissional — resíduos contaminam a água e o peixe',
            ],
        ],
        [
            'titulo' => 'Nutrição',
            'icone'  => '🌿',
            'intro'  => "A ração representa 60 a 70% do custo da piscicultura. Usar a ração certa, na quantidade certa e no momento certo é decisivo para a rentabilidade. Ração molhada que afunda é desperdício e polui a água.\n\nA tilápia é onívora e aceita ração com menos proteína. O tambaqui também aproveita frutos e sementes que caem na água.",
            'dicas'  => [
                'Tamanho da ração: alinhado com a boca do peixe — 1 a 2 mm (alevinos), 3 a 6 mm (adultos)',
                'Proteína bruta: 28 a 32% para crescimento; 20 a 24% para terminação',
                'Fornecimento: 3 a 5% da biomassa por dia, dividido em 2 a 3 refeições',
                'Observe o consumo: forneça apenas o que os peixes consomem em 15 a 20 minutos',
                'Tambaqui em setembro/outubro: aproveite frutos caídos (bacaba, açaí, buriti) — reduz custo da ração',
                'Cal virgem no viveiro (100 kg/ha) antes do povoamento: desinfecção e melhoria do pH do solo',
            ],
        ],
        [
            'titulo' => 'Manejo',
            'icone'  => '🤝',
            'intro'  => "O manejo da piscicultura envolve o controle do estoque (biomassa), a biometria periódica dos peixes e a organização das fases de produção. Um bom registro é fundamental para tomar decisões corretas de despesca e abastecimento.\n\nA densidade de estocagem correta é a decisão mais importante do ciclo produtivo — ela define a qualidade da água e o crescimento dos peixes.",
            'dicas'  => [
                'Densidade: 1 a 2 peixes/m² para tilápia em viveiros extensivos; até 5/m² com aeração',
                'Biometria: pese uma amostra de 50 a 100 peixes mensalmente para calcular a biomassa total',
                'Despesca parcial: retire os maiores a cada 60 dias para uniformizar o lote',
                'Ciclo de engorda: tilápia (6 a 8 meses para 700g a 1kg); tambaqui (10 a 14 meses para 1,5 a 2kg)',
                'Hora da despesca: de madrugada ou ao amanhecer — menor estresse para os peixes e melhor qualidade da carne',
                'Vazio sanitário: após cada ciclo, seque o viveiro por 15 a 30 dias para eliminar patógenos',
            ],
        ],
        [
            'titulo' => 'Biosseguridade',
            'icone'  => '🛡️',
            'intro'  => "Na piscicultura, a biosseguridade começa antes do primeiro peixe entrar no viveiro. A qualidade da água, a procedência dos alevinos e o controle de predadores são os pilares da produção saudável.\n\nÁgua com má qualidade estressará os peixes, deprimirá o sistema imunológico e abrirá caminho para doenças — mesmo em animais vacinados.",
            'dicas'  => [
                'Compre alevinos certificados de pisciculturas registradas — evite alevinos de origem desconhecida',
                'Tela anti-pássaro sobre o viveiro: garças e martins-pescadores podem destruir um lote inteiro',
                'Análise de água: pH, amônia e oxigênio pelo menos 1 vez por semana',
                'Não lance dejetos de animais diretamente no viveiro sem compostagem — risco de proliferação de algas tóxicas',
                'Lote novo: quarentena em tanque separado por 7 a 10 dias antes de juntar ao viveiro principal',
                'Registro da produção: anote o número de peixes, ração consumida, biometrias e despesca mensalmente',
            ],
        ],
    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
