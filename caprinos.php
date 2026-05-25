<?php
declare(strict_types=1);
require_once 'includes/auth.php';
require_login('index.php');

$pagina        = 'caprinos';
$titulo_pagina = 'Caprinos';

$animal = [
    'nome'     => 'Caprinos',
    'emoji'    => '🐐',
    'imagem'   => 'https://images.unsplash.com/photo-1560819400-434c188f63ef?w=1400&q=80&auto=format&fit=crop',
    'descricao'=> 'A caprinocultura é uma das atividades mais importantes da agricultura familiar maranhense. Cabras são animais rústicos, adaptados ao clima semiárido e muito importantes para a geração de renda.',
    'racas' => [
        ['emoji'=>'🐐','nome'=>'Anglo-nubiano', 'tipo'=>'Leite/Misto','imagem'=>'https://images.unsplash.com/photo-1593750187970-84858a2aaf5e?w=600&q=80&auto=format&fit=crop','desc'=>'Raça de orelhas longas e caídas, boa produção de leite com alto teor de gordura. Muito docil e popular em sistemas familiares no Nordeste.'],
        ['emoji'=>'🐑','nome'=>'Boer',          'tipo'=>'Corte',      'imagem'=>'https://images.unsplash.com/photo-1560819400-434c188f63ef?w=600&q=80&auto=format&fit=crop','desc'=>'Raça sul-africana de grande porte e rápido crescimento. Excelente rendimento de carcaça. Usado em cruzamento industrial para melhorar raças locais.'],
        ['emoji'=>'🐐','nome'=>'SRD (Comum)',   'tipo'=>'Misto',      'imagem'=>'https://images.unsplash.com/photo-1593750187970-84858a2aaf5e?w=600&q=80&auto=format&fit=crop&sat=-50','desc'=>'Sem Raça Definida, os caprinos locais do Maranhão são extremamente rústicos e adaptados. Menor produção, mas altíssima resiliência e baixo custo de manutenção.'],
    ],
    'topicos' => [
        [
            'titulo' => 'Ambiência',
            'icone'  => '🏠',
            'intro'  => "O aprisco (instalação para caprinos) deve ser elevado do chão (0,7 a 1 metro) para melhorar a ventilação, evitar umidade e facilitar a limpeza. Caprinos são muito sensíveis à umidade e doenças respiratórias.\n\nNo Maranhão, a orientação ideal do aprisco é nordeste-sudoeste para maximizar a ventilação natural e reduzir a incidência direta do sol.",
            'dicas'  => [
                'Área por animal: 1,0 m² para cabritos, 1,5 m² para adultos em aprisco coberto',
                'Piso ripado ou tablado de madeira: permite queda dos dejetos, mantendo o piso seco',
                'Forneça de 3 a 5 litros de água limpa por animal adulto por dia (mais em lactação)',
                'Instale comedouros laterais com grade para evitar desperdício de volumoso',
                'Sombreamento externo para o período de pastejo — árvores nativas são ideais',
                'Evite locais úmidos ou com má ventilação — umidade favorece verminoses e pneumonias',
            ],
        ],
        [
            'titulo' => 'Vacinação',
            'icone'  => '💉',
            'intro'  => "Os caprinos são suscetíveis a algumas doenças clostridiais que causam morte súbita. A vacinação é a medida mais econômica de prevenção. Associe sempre com boas práticas de manejo e nutrição.\n\nO calendário vacinal deve ser discutido com médico-veterinário, pois varia conforme a região e o histórico de doenças locais.",
            'dicas'  => [
                'Clostridioses (Gangrena gasosa, Enterotoxemia): anualmente, 30 dias antes do período chuvoso',
                'Ectima Contagioso (boqueira): vacinar se houver histórico na região — doença viral de fácil transmissão',
                'Linfadenite Caseosa (mal do caroço): não há vacina comercial no Brasil — controle por descarte',
                'Raiva: vacinar em regiões com morcegos hematófagos — dose anual',
                'Priorize fêmeas prenhes: vacinação 30 dias antes do parto protege os cabritos pelo colostro',
                'Nunca compartilhe seringas ou agulhas entre animais — risco de transmissão de doenças',
            ],
        ],
        [
            'titulo' => 'Nutrição',
            'icone'  => '🌿',
            'intro'  => "Caprinos são ruminantes que aproveitam muito bem forragens de baixa qualidade, sendo ideais para regiões com vegetação de caatinga e cerrado. Porém, para boa produção de leite ou ganho de peso, precisam de suplementação adequada.\n\nO sal mineral para caprinos é diferente do bovino — certifique-se de usar o produto correto para a espécie.",
            'dicas'  => [
                'Ofereça de 3 a 5% do peso vivo em forragem fresca (capim, feno, folhagens) por dia',
                'Suplementação proteica na seca: farelo de soja, palma forrageira, feno de leucena',
                'Sal mineral específico para caprinos: disponível 24h em cocho coberto',
                'Fêmeas em lactação: acrescentar 200 a 300g de concentrado por litro de leite produzido',
                'Não forneça uréia a caprinos sem orientação técnica — risco de intoxicação',
                'Plantas tóxicas comuns no Maranhão: timbo, jurema preta (em excesso) — identifique e elimine do pasto',
            ],
        ],
        [
            'titulo' => 'Manejo',
            'icone'  => '🤝',
            'intro'  => "A reprodução em caprinos é estacional no Nordeste, com maior atividade reprodutiva entre junho e novembro (dias curtos). A gestação dura em média 150 dias (5 meses), e as cabras podem ter de 1 a 3 crias por parto.\n\nO manejo da ordenha e das crias é fundamental para quem produz leite caprino para consumo ou processamento.",
            'dicas'  => [
                'Desmame: 60 a 90 dias para cabritos de corte; 30 dias se for para produção intensiva de leite',
                'Ordenha: 2 vezes ao dia em horários fixos (manhã e tarde) para manter a produção',
                'Antes da ordenha: limpe o úbere com pano umedecido e descarte os primeiros jatos (pré-dipping)',
                'Identifique e trate mastite imediatamente — use o Teste CMT mensalmente',
                'Casqueamento: realize a cada 4-6 meses para evitar problemas locomotores',
                'Bode reprodutor: 1 bode para cada 30 a 40 fêmeas no sistema de monta natural',
            ],
        ],
        [
            'titulo' => 'Biosseguridade',
            'icone'  => '🛡️',
            'intro'  => "A verminose é a principal causa de mortalidade em caprinos no Nordeste, incluindo o Maranhão. O controle parasitário inteligente, usando a técnica FAMACHA, evita o uso excessivo de vermífugos e a resistência dos parasitas.\n\nA higiene do aprisco, especialmente em época chuvosa, é fundamental para reduzir a contaminação do ambiente por larvas de vermes.",
            'dicas'  => [
                'FAMACHA: avalie a mucosa ocular mensalmente — vermifugue apenas animais com escore 3, 4 ou 5',
                'Rotação de princípios ativos de vermífugo (albendazol, ivermectina, closantel) para evitar resistência',
                'Pedilúvio com sulfato de zinco a 10%: passagem semanal para prevenir foot-rot (podridão dos cascos)',
                'Quarentena de 21 dias para animais novos: vermifugue e observe antes de integrar ao rebanho',
                'Limpeza do aprisco: remova fezes diariamente na época chuvosa — larvas de vermes se desenvolvem no ambiente úmido',
                'Descarte animais cronicamente doentes ou com más conformações — não reproduza problemas genéticos',
            ],
        ],
    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
