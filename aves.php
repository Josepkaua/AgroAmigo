<?php
declare(strict_types=1);
require_once 'includes/auth.php';
session_init(); // conteúdo público — não exige login

$pagina        = 'aves';
$titulo_pagina = 'Aves';

$animal = [
    'nome'     => 'Aves',
    'emoji'    => '🐔',
    'imagem'   => 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=1400&q=80&auto=format&fit=crop',
    'descricao'=> 'Orientações para criação de galinhas caipiras, poedeiras e outras aves de quintal no Maranhão, sistema muito presente na agricultura familiar da região.',
    'racas' => [
        ['emoji'=>'🐔','nome'=>'Galinha Caipira (SRD)','tipo'=>'Corte/Ovos','imagem'=>'https://images.unsplash.com/photo-1588597989061-b60ad0eefdbf?w=600&q=80&auto=format&fit=crop','desc'=>'Aves mestiças sem padrão racial, adaptadas ao manejo extensivo do Maranhão. Produção: 130–180 ovos/ano com comportamento forrageador ativo — reduz custo de ração em 20–30% em piquetes. Peso vivo ao abate: 1,8–2,5 kg entre 90–120 dias. Alta resistência a ectoparasitas (piolhos, carrapatos). Valorização crescente no mercado de ovos e carne caipira.'],
        ['emoji'=>'🥚','nome'=>'ISA Brown',             'tipo'=>'Poedeira',  'imagem'=>'assets/img/racas/aves/isa-brown.jpg','desc'=>'Linhagem comercial híbrida poedeira de plumagem marrom-avermelhada. Produção: 300–320 ovos/ano, conversão 2,0–2,2 kg de ração/dúzia de ovos. Pico de postura entre 24 e 36 semanas, período produtivo de 70–80 semanas. Em sistema semi-intensivo com piquete, reduz custo de ração em 15–20% com pequena redução de postura. Principal linhagem de postura do Brasil.'],
        ['emoji'=>'🐓','nome'=>'Pescoço Pelado',        'tipo'=>'Corte/Ovos','imagem'=>'assets/img/racas/aves/pescoco-pelado.jpg','desc'=>'Variedade com gene Na (naked neck), que elimina 40% das penas do pescoço e peito, reduzindo a produção de calor corporal em ~30% — melhor adaptação fisiológica ao calor do Maranhão. Menor dispêndio energético com termorregulação resulta em mais energia para postura e crescimento. Produção: 160–200 ovos/ano, peso ao abate 2,0–2,8 kg. Ideal para regiões com temperatura acima de 30°C.'],
    ],
    'topicos' => [

        [
            'icone'  => '💉',
            'titulo' => 'Sanidade e Vacinação',
            'porque' => 'Doença de ave anda rápido: o que começa em uma galinha pode pegar o '
                      . 'galinheiro inteiro em uma semana. Newcastle e Bouba não têm tratamento '
                      . '— ou você vacina antes, ou perde o lote.',
            'passos' => [
                ['acao'    => 'Marek: no primeiro dia de vida, no incubatório',
                 'detalhe' => 'Não dá para fazer em casa. Ao comprar pintainho, exija a nota '
                            . 'com a garantia de que veio vacinado contra Marek. Depois que o '
                            . 'pinto sai do incubatório, não adianta mais.'],
                ['acao'    => 'Newcastle: 1ª dose aos 7 dias, reforço aos 28',
                 'detalhe' => 'Aplicada em gota no olho ou na narina. Cuidado: a vacina viva é '
                            . 'delicada — nada de água clorada nem sol na hora de aplicar. '
                            . 'Faça de manhã cedo ou no fim da tarde.'],
                ['acao'    => 'Bouba aviária: a partir dos 21 dias, onde já teve a doença',
                 'detalhe' => 'Aplicada com agulha dupla na membrana da asa. 7 a 10 dias depois '
                            . 'confira se apareceu a "pega" (crostinha no local) — se não '
                            . 'apareceu, a vacina não funcionou e precisa repetir.'],
                ['acao'    => 'Uma seringa e uma agulha limpas por lote',
                 'detalhe' => 'Material sujo espalha doença em vez de prevenir. E vacina aberta '
                            . 'só vale para o dia: o que sobrou, descarte.'],
                ['acao'    => 'Anote data, lote e fabricante',
                 'detalhe' => 'Sem registro você não sabe quando dar o reforço nem o que falhou '
                            . 'se o lote adoecer.'],
            ],
            'numeros' => [
                ['Marek',                 '1º dia de vida, no incubatório'],
                ['Newcastle — 1ª dose',   '7 dias de vida (gota no olho/narina)'],
                ['Newcastle — reforço',   'aos 28 dias'],
                ['Bouba aviária',         'a partir de 21 dias (membrana da asa)'],
                ['Conferir a "pega"',     '7 a 10 dias após a vacina de bouba'],
                ['Conservação',           'entre 2°C e 8°C, sem congelar'],
            ],
            'alerta' => [
                'Muitas aves morrendo em poucos dias — em caso de suspeita de Newcastle ou influenza aviária, comunicar à AGED-MA é obrigatório por lei',
                'Aves com pescoço torto, andando em círculo ou com paralisia',
                'Queda súbita e forte na postura do lote inteiro',
                'Aves com dificuldade de respirar, chiado ou bico aberto',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
                ['texto' => 'AGED-MA', 'url' => 'https://www.aged.ma.gov.br/'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🌿',
            'titulo' => 'Nutrição e Água',
            'porque' => 'Ração é o maior custo do galinheiro, e a galinha caipira tem uma '
                      . 'vantagem: ela cata parte do próprio sustento no piquete. Quem usa isso '
                      . 'direito corta bem o gasto sem perder produção. E a água pesa mais que '
                      . 'a ração: um dia sem água derruba a postura por semanas.',
            'passos' => [
                ['acao'    => 'Ajuste a ração à idade da ave',
                 'detalhe' => 'Pintainho precisa de mais proteína; poedeira precisa de mais '
                            . 'cálcio. Dar ração de crescimento para poedeira dá ovo de casca '
                            . 'fina, que quebra na coleta.'],
                ['acao'    => 'Dê cálcio separado para as poedeiras',
                 'detalhe' => 'Calcário calcítico grosso ou casca de ostra, cerca de 5 g por '
                            . 'ave por dia, num cocho à parte. A galinha pega quando precisa — '
                            . 'principalmente no fim da tarde, que é quando forma a casca.'],
                ['acao'    => 'Use o piquete como parte da comida',
                 'detalhe' => 'Capim tenro, leucena, folha de mandioca murcha e insetos '
                            . 'substituem parte da ração e deixam a gema mais alaranjada. '
                            . 'Rode os piquetes para o capim se recuperar.'],
                ['acao'    => 'Água limpa e fresca o tempo todo',
                 'detalhe' => 'Lave o bebedouro todo dia — biofilme verde no fundo é fonte de '
                            . 'doença. No calor, ponha o bebedouro na sombra: ave não bebe '
                            . 'água quente e para de comer.'],
                ['acao'    => 'Guarde a ração no seco e no alto',
                 'detalhe' => 'Saco no chão úmido cria fungo, e fungo produz micotoxina, que '
                            . 'derruba a postura sem a ave parecer doente. Nunca use ração '
                            . 'embolorada.'],
            ],
            'numeros' => [
                ['Poedeira — ração',      '80 a 100 g por ave por dia'],
                ['Frango caipira — ração','cerca de 120 g por ave por dia'],
                ['Inicial (0–28 dias)',   '22% de proteína bruta'],
                ['Crescimento (29–70 d)', '18% a 20% de proteína bruta'],
                ['Cálcio para poedeira',  'cerca de 5 g/ave/dia, em cocho separado'],
                ['Água',                  'limpa, à vontade, bebedouro lavado todo dia'],
            ],
            'alerta' => [
                'Queda de postura sem outra explicação (suspeita de micotoxina ou falta de água)',
                'Ovos com casca fina, mole ou sem casca',
                'Aves bicando penas umas das outras (canibalismo — falta de espaço, proteína ou excesso de luz)',
                'Ração com cheiro de mofo ou empedrada',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🏠',
            'titulo' => 'Galinheiro e Ambiência',
            'porque' => 'No calor do Maranhão a ave não sua: ela perde calor pelo bico aberto e '
                      . 'ofegando. Se o galinheiro é abafado, ela para de comer para não '
                      . 'esquentar mais — e aí não bota e não engorda. Ventilação e sombra são '
                      . 'produção, não luxo.',
            'passos' => [
                ['acao'    => 'Ventilação cruzada e telhado com beiral',
                 'detalhe' => 'Laterais abertas com tela nos dois lados, pé-direito alto e '
                            . 'beiral largo. O galpão comprido deve ficar no sentido '
                            . 'leste-oeste, para o sol não bater dentro nas horas quentes.'],
                ['acao'    => 'Não aperte as aves',
                 'detalhe' => '3 a 4 galinhas por m² no galpão. Superlotação faz calor, briga, '
                            . 'bicagem e queda de postura.'],
                ['acao'    => 'Cama seca e solta',
                 'detalhe' => '8 a 10 cm de maravalha ou palha de arroz. Cama molhada perto do '
                            . 'bebedouro é onde começa coccidiose e problema de pata — revolva '
                            . 'e reponha nos pontos úmidos.'],
                ['acao'    => 'Poleiro e ninho na medida certa',
                 'detalhe' => 'Cerca de 25 cm de poleiro por ave, e 1 ninho para cada 5 '
                            . 'poedeiras, em lugar escuro e calmo. Ninho de menos é ovo no '
                            . 'chão, sujo e quebrado.'],
                ['acao'    => 'Feche contra predador',
                 'detalhe' => 'Tela enterrada 20 cm no contorno e galpão fechado à noite: '
                            . 'raposa, gambá e cobra tiram mais ave que doença em muita '
                            . 'propriedade.'],
            ],
            'numeros' => [
                ['Densidade',        '3 a 4 aves por m²'],
                ['Poleiro',          'cerca de 25 cm por ave'],
                ['Ninho',            '1 para cada 5 poedeiras'],
                ['Cama',             '8 a 10 cm de maravalha ou palha de arroz'],
                ['Orientação',       'galpão no sentido leste-oeste'],
                ['Fechar o galpão',  'todas as noites, contra predadores'],
            ],
            'alerta' => [
                'Aves ofegantes, com asas abertas e amontoadas na parede (estresse por calor)',
                'Cama encharcada ou com cheiro forte de amônia',
                'Aves com diarreia com sangue (suspeita de coccidiose)',
                'Perdas noturnas frequentes (predador entrando)',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🥚',
            'titulo' => 'Manejo do Lote e Postura',
            'porque' => 'Galinha é bicho de rotina: come, bota e dorme na hora certa. Quem '
                      . 'mantém horário e anota a produção percebe o problema na primeira '
                      . 'semana — e não quando o prejuízo já está feito.',
            'passos' => [
                ['acao'    => 'Colete os ovos 2 a 3 vezes por dia',
                 'detalhe' => 'Ovo parado no ninho quebra, suja e estimula a galinha a chocar. '
                            . 'Não lave o ovo sujo com água: ela empurra a sujeira para dentro '
                            . 'pelos poros da casca — limpe a seco com pano ou lixa fina.'],
                ['acao'    => 'Não misture idades',
                 'detalhe' => 'Pintainho, franga e adulta em lotes separados. A ave velha '
                            . 'carrega doença que o pintainho ainda não aguenta.'],
                ['acao'    => 'Anote a postura todo dia',
                 'detalhe' => 'Número de ovos por dia dividido pelo número de galinhas é o '
                            . 'termômetro do galinheiro. Queda de mais de 10% em poucos dias '
                            . 'é sinal de problema — água, calor, doença ou ração.'],
                ['acao'    => 'Descarte na hora certa',
                 'detalhe' => 'Poedeira comercial acima de 18 meses ou lote abaixo de 60% de '
                            . 'postura em geral já não paga a ração. A ave descartada ainda '
                            . 'vale como galinha caipira de corte.'],
                ['acao'    => 'Frango caipira: abata no ponto',
                 'detalhe' => 'Entre 90 e 120 dias, com 2 kg ou mais. Passar muito disso é '
                            . 'gastar ração para converter cada vez menos.'],
            ],
            'numeros' => [
                ['Coleta de ovos',        '2 a 3 vezes por dia'],
                ['Poedeira comercial',    '300 a 320 ovos por ano'],
                ['Caipira (SRD)',         '130 a 180 ovos por ano'],
                ['Início de postura',     'cerca de 20 semanas de idade'],
                ['Descarte da poedeira',  'acima de 18 meses ou postura abaixo de 60%'],
                ['Abate do frango caipira','90 a 120 dias, com 2 kg ou mais'],
            ],
            'alerta' => [
                'Queda de mais de 10% na postura em poucos dias',
                'Muitos ovos deformados, pequenos ou com casca manchada',
                'Aves paradas, com crista pálida ou penas arrepiadas',
                'Aumento de ovos no chão (falta de ninho ou lote assustado)',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Suínos e Aves',
                 'url'   => 'https://www.embrapa.br/suinos-e-aves'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🛡️',
            'titulo' => 'Biosseguridade',
            'porque' => 'Doença de ave quase nunca nasce dentro do galinheiro: ela entra. Entra '
                      . 'na bota de quem visitou outro criador, no rato, no pássaro silvestre '
                      . 'que come no cocho. Fechar essas portas é mais barato que qualquer '
                      . 'remédio.',
            'passos' => [
                ['acao'    => 'Vazio sanitário entre lotes',
                 'detalhe' => 'Tire tudo, lave, desinfete e deixe o galpão vazio por 15 dias. '
                            . 'Sem esse intervalo, o lote novo herda a doença do lote velho.'],
                ['acao'    => 'Tela contra pássaro silvestre',
                 'detalhe' => 'Pássaro de fora é o principal transmissor de Newcastle e '
                            . 'influenza aviária. Ele não pode comer no seu cocho nem beber '
                            . 'no seu bebedouro.'],
                ['acao'    => 'Controle rato',
                 'detalhe' => 'Rato transmite salmonela e leptospirose e ainda come ração. '
                            . 'Mantenha o entorno roçado, a ração em vasilha fechada e use '
                            . 'iscas em porta-iscas fechado, longe das aves.'],
                ['acao'    => 'Bota e roupa só do galinheiro',
                 'detalhe' => 'Quem visitou outra criação não entra no mesmo dia. Um pedilúvio '
                            . 'com desinfetante na porta já ajuda muito.'],
                ['acao'    => 'Ave morta sai do galpão na hora',
                 'detalhe' => 'Enterre com cal ou faça compostagem. Carcaça no chão vira '
                            . 'fonte de doença e atrai urubu e rato.'],
            ],
            'numeros' => [
                ['Vazio sanitário',   '15 dias entre lotes'],
                ['Quarentena',        'ave nova, 21 dias separada'],
                ['Pedilúvio',         'na entrada, com desinfetante trocado'],
                ['Ave doente',        'isolar imediatamente'],
                ['Ave morta',         'retirar no mesmo dia e enterrar/compostar'],
            ],
            'alerta' => [
                'Mortalidade alta e repentina no lote — comunique à AGED-MA (Newcastle e influenza aviária são de notificação obrigatória)',
                'Aves silvestres mortas perto da criação',
                'Diarreia espalhando pelo lote',
                'Aves novas adoecendo logo após entrarem no plantel',
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
