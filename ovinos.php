<?php
declare(strict_types=1);
require_once 'includes/auth.php';
require_login('index.php');

$pagina        = 'ovinos';
$titulo_pagina = 'Ovinos';

$animal = [
    'nome'     => 'Ovinos',
    'emoji'    => '🐑',
    'descricao'=> 'A ovinocultura no Maranhão é dominada pela raça Santa Inês, altamente adaptada ao clima tropical. A criação de ovinos oferece ótima rentabilidade e ciclo curto de produção para a agricultura familiar.',
    'racas' => [
        ['emoji'=>'🐑','nome'=>'Santa Inês',   'tipo'=>'Corte/Misto', 'desc'=>'Raça brasileira mais popular no Nordeste. Sem lã, adaptada ao calor, resistente a parasitas e muito prolífica. Produz carne de qualidade e aceita bem cruzamento.'],
        ['emoji'=>'🐏','nome'=>'Dorper',        'tipo'=>'Corte',       'desc'=>'Raça sul-africana de alto rendimento de carcaça. Quando cruzado com Santa Inês, gera animais mais pesados e com melhor conversão alimentar.'],
        ['emoji'=>'🐑','nome'=>'SRD (Comum)',   'tipo'=>'Misto',       'desc'=>'Ovinos sem padrão racial definido, muito comuns no interior do Maranhão. Alta rusticidade e baixo custo de manutenção, embora com menor produtividade.'],
    ],
    'topicos' => [
        [
            'titulo' => 'Ambiência',
            'icone'  => '🏠',
            'intro'  => "Os ovinos se adaptam bem a diferentes ambientes, mas precisam de abrigo para proteção contra chuva e sol forte. No Maranhão, instalações simples e bem ventiladas são suficientes para bom desempenho.\n\nAprisco elevado, com piso ripado, reduz significativamente a carga parasitária no ambiente e facilita o manejo sanitário.",
            'dicas'  => [
                'Área coberta por animal: 1,2 m² por ovino adulto; 0,5 m² por cordeiro em creche',
                'Aprisco elevado de 0,5 a 0,8 m do chão com tablado ripado: reduz umidade e verminose',
                'Cercas de tela galinheiro ou arame farpado (5 fios): seguras e de baixo custo',
                'Forneça de 3 a 4 litros de água limpa por ovino adulto por dia',
                'Ofereça sombreamento no piquete — árvores nativas são preferíveis',
                'Mantenha o aprisco limpo e seco: remova fezes 2 vezes por semana na estação seca',
            ],
        ],
        [
            'titulo' => 'Vacinação',
            'icone'  => '💉',
            'intro'  => "Os ovinos são suscetíveis a clostridioses (doenças causadas por bactérias do gênero Clostridium) que causam morte súbita sem sinais prévios. A vacinação é a única forma eficaz de prevenção.\n\nO calendário vacinal deve ser adaptado ao histórico da propriedade e às condições regionais do Maranhão.",
            'dicas'  => [
                'Clostridioses (Enterotoxemia, Gangrena gasosa): 1 vez ao ano, 30 dias antes do parto',
                'Raiva: anual em regiões com ocorrência de morcegos hematófagos',
                'Leptospirose: recomendada em regiões com histórico ou próximas a bovinos e suínos',
                'Cordeiros filhos de mães vacinadas: estão protegidos pelo colostro até os 60-90 dias',
                'Primeira vacinação de cordeiros: 60 dias de vida, com reforço 30 dias depois',
                'Associe vacinação com vermifugação estratégica — doenças enfraquecem a imunidade',
            ],
        ],
        [
            'titulo' => 'Nutrição',
            'icone'  => '🌿',
            'intro'  => "Ovinos são ruminantes eficientes que aproveitam bem forragens grosseiras. A pastagem tropical (braquiária, tifton, capim-elefante) associada a suplementação mineral é suficiente para a manutenção do rebanho.\n\nPara crescimento acelerado dos cordeiros ou produção de matrizes de alta eficiência, acrescente concentrado na dieta.",
            'dicas'  => [
                'Pastagem: ofereça de 3 a 5% do peso vivo em forragem fresca por animal por dia',
                'Sal mineral específico para ovinos: fundamental — ovinos têm necessidades diferentes de bovinos',
                'Concentrado para terminação de cordeiros: 200 a 400g/dia de milho + farelo de soja (70:30)',
                'Ovelhas em gestação final (últimos 45 dias): aumentar energia para evitar toxemia da prenhez',
                'Evite acesso a plantas tóxicas — identificar e eliminar do pasto',
                'Na seca: ofereça feno de qualidade ou cana picada como volumoso alternativo',
            ],
        ],
        [
            'titulo' => 'Manejo',
            'icone'  => '🤝',
            'intro'  => "A ovinocultura tem ciclo curto: gestação de 150 dias e cordeiros prontos para abate entre 90 e 120 dias. Com manejo adequado, é possível ter 1,5 a 1,7 partos por ovelha por ano.\n\nA Santa Inês tem vantagem de ser asazonal — reproduz durante todo o ano, diferente de raças de clima temperado.",
            'dicas'  => [
                'FAMACHA: avalie mensalmente a mucosa ocular para controle individualizado de verminose',
                'Desmame: 75 a 90 dias de vida — antecipa o retorno ao cio da ovelha',
                'Tosquia: a Santa Inês dispensa tosquia — não tem lã. Outras raças: 2 vezes ao ano',
                'Carneiro reprodutor: 1 carneiro para cada 20 a 30 ovelhas no sistema de monta natural',
                'Separar cordeiros por sexo após o desmame: machos crescem mais rápido quando separados',
                'Pesagem mensal dos cordeiros: meta de 200 a 250g de ganho de peso por dia na fase de terminação',
            ],
        ],
        [
            'titulo' => 'Biosseguridade',
            'icone'  => '🛡️',
            'intro'  => "A verminose gastrintestinal (Haemonchus contortus) é o maior problema sanitário dos ovinos no Nordeste, causando anemia, fraqueza e morte. O controle deve ser inteligente — não vermifugue todos os animais de forma indiscriminada.\n\nA técnica FAMACHA (avaliação da mucosa ocular) é a ferramenta mais recomendada para controle seletivo de verminose.",
            'dicas'  => [
                'FAMACHA: escore 1 e 2 = não vermifuga; escore 3 = observar; escore 4 e 5 = tratar imediatamente',
                'Rotação de piquetes: 30 dias dentro, 60 a 90 dias de descanso para quebrar o ciclo parasitário',
                'Evite pastejo em horas de orvalho (manhã cedo) — larvas de verme ficam na pontinha da grama',
                'Quarentena de novos animais: 21 dias com vermifugação estratégica antes de integrar ao rebanho',
                'Rotação de vermífugos: alterne princípios ativos (ivermectina, closantel, albendazol) para evitar resistência',
                'Descarte animais que necessitam vermifugação frequente — sinal de susceptibilidade genética',
            ],
        ],
    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
