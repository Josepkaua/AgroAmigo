<?php
declare(strict_types=1);
require_once 'includes/auth.php';
require_login('index.php');

$pagina        = 'aves';
$titulo_pagina = 'Aves';

$animal = [
    'nome'     => 'Aves',
    'emoji'    => '🐔',
    'descricao'=> 'Orientações para criação de galinhas caipiras, poedeiras e outras aves de quintal no Maranhão, sistema muito presente na agricultura familiar da região.',
    'racas' => [
        ['emoji'=>'🐔','nome'=>'Galinha Caipira (SRD)','tipo'=>'Corte/Ovos','desc'=>'Sem Raça Definida, altamente adaptada às condições do Nordeste. Rusticamente resistente a parasitas e calor. Produção de 150 a 200 ovos/ano.'],
        ['emoji'=>'🥚','nome'=>'ISA Brown',             'tipo'=>'Poedeira',  'desc'=>'Raça comercial de alta produtividade. Produz até 320 ovos/ano com boa alimentação. Pode ser usada em sistema semi-intensivo com piquete.'],
        ['emoji'=>'🐓','nome'=>'Pescoço Pelado',        'tipo'=>'Corte/Ovos','desc'=>'Variedade caipira com maior tolerância ao calor tropical. Boa para regiões quentes do Maranhão. Produção mista de ovos e carne.'],
    ],
    'topicos' => [
        [
            'titulo' => 'Ambiência',
            'icone'  => '🏠',
            'intro'  => "O galinheiro deve proteger as aves do sol intenso, da chuva e dos predadores (raposas, gaviões, cobras). A ventilação adequada é essencial para o conforto e a saúde respiratória no clima quente do Maranhão.\n\nA densidade de criação influencia diretamente a produtividade e o bem-estar das aves.",
            'dicas'  => [
                'Densidade recomendada: 3 a 4 galinhas por m² no galpão (evite superlotação)',
                'Instale poleiros de 25 cm por ave para descanso noturno e prevenção de parasitas',
                'Orientação do galinheiro: aberturas voltadas para o leste/oeste para ventilação cruzada',
                'Piso: terra batida ou areia grossa (cama de maravalha ou palha de arroz — 8 a 10 cm)',
                'Ninhos: 1 ninho para cada 5 galinhas poedeiras, em local escuro e tranquilo',
                'Bebedouros e comedouros devem ser lavados diariamente para evitar doenças',
            ],
        ],
        [
            'titulo' => 'Vacinação',
            'icone'  => '💉',
            'intro'  => "As aves caipiras são mais resistentes, mas ainda assim suscetíveis a doenças virais que podem devastar o plantel rapidamente. A vacinação preventiva é muito mais barata que o tratamento.\n\nAdapte o calendário à realidade da sua região — consulte o técnico local.",
            'dicas'  => [
                'Newcastle: 1ª dose aos 7 dias de vida (colírio ou gota nasal), reforço aos 28 dias',
                'Bouba Aviária: vacinar a partir dos 21 dias em regiões com histórico da doença',
                'Marek: vacinar pintinhos no primeiro dia de vida (vacina de incubatório)',
                'Gumboro (Bursite): 14 e 28 dias de vida em criações intensivas ou semi-intensivas',
                'Evite vacinar aves doentes, estressadas ou muito jovens sem orientação técnica',
                'Guarde vacinas em geladeira (2 a 8°C) e use imediatamente após abrir o frasco',
            ],
        ],
        [
            'titulo' => 'Nutrição',
            'icone'  => '🌿',
            'intro'  => "A alimentação é o maior custo da avicultura. Combinar ração comercial com acesso a piquetes reduz custos e melhora a qualidade dos ovos e da carne, além de garantir bem-estar animal.\n\nA água é o nutriente mais crítico: falta de água por 24h pode reduzir a produção de ovos por semanas.",
            'dicas'  => [
                'Fornece ração completa: 80 a 100g por ave por dia (poedeiras); 120g para frangos de corte',
                'Ração inicial (0-28 dias): 22% proteína bruta. Crescimento (29-70 dias): 18-20% PB',
                'Complemento a baixo custo: milho quebrado, mandioca, folhas de leucena, capim-elefante',
                'Acesso a piquete com vegetação: reduz custos em 20-30% e melhora a qualidade dos ovos',
                'Calcário calcítico ou casca de ostra: 5g/ave/dia para poedeiras — fortalece a casca do ovo',
                'Água limpa disponível 24 horas — troque e limpe os bebedouros diariamente',
            ],
        ],
        [
            'titulo' => 'Manejo',
            'icone'  => '🤝',
            'intro'  => "O manejo das aves envolve desde a rotina diária até o controle do lote e a tomada de decisões sobre descarte e renovação do plantel. Organização é a chave para a rentabilidade.\n\nEstabeleça horários fixos para arraçoamento — aves são animais de hábitos e produzem mais com rotina.",
            'dicas'  => [
                'Colete ovos 2 vezes ao dia (manhã e tarde) para evitar quebra e reduzir choco',
                'Identifique e separe galinhas "chocas" (que param de botar) — elas consomem ração sem produzir',
                'Separe lotes por faixa etária: pintinhos, jovens e adultos não devem misturar',
                'Descarte galinhas com mais de 18 meses ou produtividade abaixo de 60% — viram galinha caipira de corte',
                'Frangos de corte caipira: abate entre 90 e 120 dias (peso mínimo 2 kg)',
                'Registre a produção de ovos diariamente para identificar queda precoce na postura',
            ],
        ],
        [
            'titulo' => 'Biosseguridade',
            'icone'  => '🛡️',
            'intro'  => "Doenças aviárias se espalham muito rápido dentro de um lote. Uma ave doente pode infectar todo o plantel em poucos dias. A prevenção é a única estratégia eficaz.\n\nO controle de vetores (roedores, insetos, aves silvestres) é fundamental para evitar a entrada de patógenos.",
            'dicas'  => [
                'Instale tela tipo galinheiro para impedir entrada de pássaros silvestres e predadores',
                'Controle roedores com ratoeiras ou iscas — ratos transmitem Salmonella e Leptospirose',
                'Faça vazio sanitário de 15 dias entre lotes — limpe, desinfete e deixe o galpão vazio',
                'Evite visitar outros galinheiros e retornar sem trocar de roupa e calçado',
                'Isole imediatamente aves doentes (letárgicas, sem apetite, com diarreia ou morrendo)',
                'Enterre ou incinere aves mortas — nunca deixe carcaças no galpão ou a céu aberto',
            ],
        ],
    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
