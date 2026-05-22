<?php
$pagina        = 'suinos';
$titulo_pagina = 'Suínos';

$animal = [
    'nome'     => 'Suínos',
    'emoji'    => '🐷',
    'descricao'=> 'Criação de suínos no Maranhão: orientações adaptadas ao clima quente do estado para raças comerciais e locais, muito presentes na agricultura familiar.',
    'racas' => [
        ['emoji'=>'🐷','nome'=>'Large White',   'tipo'=>'Corte',  'desc'=>'Uma das raças mais produtivas do mundo. Corpo comprido, boa musculatura e alta conversão alimentar. Exige instalações com controle de temperatura.'],
        ['emoji'=>'🐖','nome'=>'Landrace',       'tipo'=>'Corte',  'desc'=>'Raça de origem dinamarquesa, excelente para produção de presunto. Muito usada em cruzamentos industriais. Sensível ao calor — exige sombra e ventilação.'],
        ['emoji'=>'🐗','nome'=>'Piau (local)',   'tipo'=>'Misto',  'desc'=>'Raça brasileira rústica, muito resistente ao clima nordestino. Menor ganho de peso, mas excelente adaptação ao manejo extensivo e à alimentação alternativa.'],
    ],
    'topicos' => [
        [
            'titulo' => 'Ambiência',
            'icone'  => '🏠',
            'intro'  => "Suínos são animais muito sensíveis ao calor — não suam e dependem de sombreamento, ventilação e água para regular a temperatura corporal. No Maranhão, onde as temperaturas passam dos 35°C, instalações adequadas são essenciais para um bom desempenho.\n\nA temperatura ideal para suínos em crescimento é entre 18°C e 22°C. Acima de 28°C, o consumo de ração cai e o ganho de peso reduz significativamente.",
            'dicas'  => [
                'Área mínima por animal: 1,5 m² (leitões), 2 m² (crescimento), 2,5 m² (terminação)',
                'Instale nebulizadores ou aspersores no telhado nas horas mais quentes (10h às 16h)',
                'Piso parcialmente ripado facilita a limpeza e reduz umidade na cama',
                'Orientação da baia: aberturas voltadas para o sentido do vento dominante da região',
                'Forneça chafurdo (área com lama) em sistemas extensivos — ajuda os animais a se refrescarem',
                'Evite lotação excessiva: superlotação aumenta temperatura, estresse e briga entre animais',
            ],
        ],
        [
            'titulo' => 'Vacinação',
            'icone'  => '💉',
            'intro'  => "O esquema vacinal em suínos varia conforme a categoria animal (matrizes, leitões, reprodutores) e o histórico sanitário da propriedade. Doenças como circovirose e leptospirose causam grandes prejuízos econômicos e podem ser evitadas com vacinação correta.\n\nConsulte sempre o médico-veterinário para adaptar o protocolo à realidade da sua criação.",
            'dicas'  => [
                'Parvovirose + Leptospirose: matrizes 15 dias antes da cobrição, reforço anual',
                'Circovirose (PCV2): leitões a partir de 3 semanas de vida (dose única ou dupla)',
                'Erisipela: matrizes 2 vezes ao ano e reprodutores anualmente',
                'Micoplasma (pneumonia enzoótica): leitões 1 e 3 semanas em propriedades com histórico',
                'Mal-rubro (Erisipelothrix): inclua nos protocolos de propriedades com acesso a pastagem',
                'Registre todas as vacinações com data, lote e nome do produto para rastreabilidade',
            ],
        ],
        [
            'titulo' => 'Nutrição',
            'icone'  => '🌿',
            'intro'  => "A alimentação representa 70 a 80% do custo de produção suinícola. Ajustar a dieta à fase produtiva do animal é fundamental para rentabilidade. Ração formulada de forma errada gera desperdício, poluição e menor lucro.\n\nA água é fundamental: suínos consomem de 2 a 5 litros de água para cada quilo de ração ingerido.",
            'dicas'  => [
                'Leitões (0-28 dias): leite materno + pré-inicial a partir dos 10 dias de vida',
                'Creche (28-63 dias): ração inicial com 20-22% proteína bruta — 500g a 900g/dia',
                'Crescimento (63-100 dias): ração crescimento 16-18% PB — 1,2 a 2,0 kg/dia',
                'Terminação (100-150 dias): ração terminação 14-16% PB — 2,0 a 3,0 kg/dia',
                'Matrizes gestantes: 2,0 a 2,5 kg/dia de ração gestação — não deixe engordar demais',
                'Aproveite resíduos agrícolas (mandioca, milho, farelo de coco) como suplemento, com orientação técnica',
            ],
        ],
        [
            'titulo' => 'Manejo',
            'icone'  => '🤝',
            'intro'  => "O ciclo reprodutivo da suína é de 21 dias. A gestação dura 114 dias (3 meses, 3 semanas e 3 dias). Com manejo adequado, uma matriz pode ter 2,2 a 2,4 partos por ano, com 10 a 14 leitões por parto.\n\nO bom manejo começa no nascimento dos leitões e vai até o abate ou descarte da matriz.",
            'dicas'  => [
                'Desmame: 21 a 28 dias de vida — abaixo de 21 dias pode prejudicar a saúde dos leitões',
                'Cobrição: leitoas a partir de 7-8 meses e 120-130 kg; cobre no 2° ou 3° cio',
                'Uniformize leitegadas nas primeiras 24h: equalize o número de leitões por teto disponível',
                'Corte e desinfecção do umbigo ao nascer: use iodo a 10%',
                'Castração de machos: realizar entre 7 e 14 dias com bisturi e antisséptico',
                'Descarte matrizes com mais de 8 partos ou baixo desempenho reprodutivo',
            ],
        ],
        [
            'titulo' => 'Biosseguridade',
            'icone'  => '🛡️',
            'intro'  => "Doenças respiratórias e entéricas são os maiores vilões da suinocultura. Um protocolo rigoroso de biosseguridade reduz custos com medicamentos e aumenta o peso médio ao abate.\n\nO conceito de 'tudo dentro, tudo fora' (all-in all-out) é a estratégia mais eficiente: toda a baia entra e sai junto, facilitando a desinfecção completa.",
            'dicas'  => [
                'Vazio sanitário: mínimo de 15 dias entre lotes com limpeza, lavagem e desinfecção da baia',
                'Arco desinfetante na entrada da granja para veículos e visitantes',
                'Controle de moscas e mosquitos: use telas e larvicidas — vetores de muitas doenças',
                'Não misture animais de diferentes origens sem quarentena de 21 dias',
                'Descarte corretamente dejetos: biodigestor ou compostagem — evite contaminação do solo e água',
                'Lembre: suínos são reservatório de zoonoses como Leptospirose e Salmonelose — proteja quem maneja',
            ],
        ],
    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
