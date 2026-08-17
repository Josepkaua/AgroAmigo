<?php
declare(strict_types=1);
require_once 'includes/auth.php';
session_init(); // conteúdo público — não exige login

$pagina        = 'peixes';
$titulo_pagina = 'Peixes';

$animal = [
    'nome'     => 'Peixes',
    'emoji'    => '🐟',
    'imagem'   => 'https://images.unsplash.com/photo-1628859742240-269783f56d17?w=1400&q=80&auto=format&fit=crop',
    'descricao'=> 'A piscicultura é uma das atividades que mais cresce no Maranhão. Tilápia e Tambaqui são as espécies mais cultivadas, com grande potencial de rentabilidade para pequenos produtores.',
    'racas' => [
        ['emoji'=>'🐟','nome'=>'Tilápia do Nilo',  'tipo'=>'Corte','imagem'=>'assets/img/racas/peixes/tilapia-do-nilo.jpg','desc'=>'Oreochromis niloticus. Espécie mais cultivada no Brasil (mais de 60% da produção aquícola nacional). Atingem 700 g–1 kg em 6–8 meses com ração de 28–32% PB. Temperatura ótima: 27–30°C — toleram até 15°C com redução de crescimento. Suportam baixa oxigenação (mínimo 3 mg/L). Linhagem GIFT e chitralada são as mais indicadas para viveiros no Maranhão.'],
        ['emoji'=>'🐠','nome'=>'Tambaqui',          'tipo'=>'Corte','imagem'=>'assets/img/racas/peixes/tambaqui.jpg','desc'=>'Colossoma macropomum. Peixe nativo do Amazonas, muito consumido no Nordeste. Crescimento excelente em viveiros. Resistente e de fácil manejo. Ciclo de 10 a 14 meses.'],
        ['emoji'=>'🐡','nome'=>'Tambacu (híbrido)', 'tipo'=>'Corte','imagem'=>'assets/img/racas/peixes/tambacu.jpg','desc'=>'Cruzamento entre Tambaqui e Pacu. Combina o crescimento rápido do tambaqui com a resistência do pacu. Popular em pisciculturas familiares do Nordeste.'],
    ],
    'topicos' => [

        [
            'icone'  => '💧',
            'titulo' => 'Qualidade da Água',
            'porque' => 'Na piscicultura a água não é o lugar onde o peixe mora: é o ar que ele '
                      . 'respira e o esgoto onde ele vive. Quase toda doença de viveiro começa '
                      . 'com água ruim. E o oxigênio some justamente de madrugada, quando '
                      . 'ninguém está olhando.',
            'passos' => [
                ['acao'    => 'Meça o oxigênio de madrugada, não de tarde',
                 'detalhe' => 'À tarde, com sol, as algas produzem oxigênio e tudo parece bem. '
                            . 'De madrugada elas consomem — e é entre 4h e 6h da manhã que o '
                            . 'peixe morre. Meça nesse horário pelo menos uma vez por semana.'],
                ['acao'    => 'Fique de olho no pH e corrija com calcário',
                 'detalhe' => 'Faixa boa: 6,5 a 8,5. Água ácida se corrige com calcário '
                            . 'agrícola no viveiro. Aplique aos poucos: mudança brusca de pH '
                            . 'estressa mais que o próprio pH ruim.'],
                ['acao'    => 'Cuidado com a amônia depois da ração',
                 'detalhe' => 'Sobra de ração e fezes viram amônia, que queima a brânquia do '
                            . 'peixe. Quanto mais quente e mais alcalina a água, mais tóxica '
                            . 'ela fica. Alimentar demais é a causa nº 1.'],
                ['acao'    => 'Tenha um plano de emergência de aeração',
                 'detalhe' => 'Aerador reserva, bomba, ou pelo menos entrada de água nova por '
                            . 'gravidade. Quando o peixe está boquejando na superfície, você '
                            . 'tem minutos, não horas.'],
                ['acao'    => 'Renove a água na seca',
                 'detalhe' => 'De 5% a 15% do volume por dia mantém a qualidade quando a '
                            . 'evaporação aperta e a concentração de resíduo sobe.'],
            ],
            'numeros' => [
                ['Oxigênio dissolvido',   'manter acima de 5 mg/L'],
                ['Horário crítico',       'entre 4h e 6h da manhã'],
                ['pH',                    'entre 6,5 e 8,5'],
                ['Temperatura — tilápia', '26°C a 30°C'],
                ['Temperatura — tambaqui','28°C a 32°C'],
                ['Renovação na seca',     '5% a 15% do volume por dia'],
            ],
            'alerta' => [
                'Peixes boquejando na superfície ao amanhecer (falta de oxigênio — é emergência)',
                'Água muito verde-escura que clareia de repente (algas morreram; o oxigênio vai cair)',
                'Mortalidade concentrada de madrugada',
                'Cheiro forte de podre no viveiro',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Pesca e Aquicultura',
                 'url'   => 'https://www.embrapa.br/pesca-e-aquicultura'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🌿',
            'titulo' => 'Ração e Arraçoamento',
            'porque' => 'Ração é 60% a 70% do custo — e boa parte do que se perde vai para o '
                      . 'fundo do viveiro sem peixe nenhum comer. Aí você paga duas vezes: '
                      . 'perde a ração e ainda suja a água que vai adoecer o peixe.',
            'passos' => [
                ['acao'    => 'Ofereça só o que somem em 15 a 20 minutos',
                 'detalhe' => 'Jogue aos poucos e observe. Sobrou boiando depois de 20 minutos, '
                            . 'você passou da conta. Essa observação vale mais que qualquer '
                            . 'tabela.'],
                ['acao'    => 'Acerte o tamanho do pellet para a boca do peixe',
                 'detalhe' => 'Grande demais o peixe não engole; pequeno demais ele gasta mais '
                            . 'energia para comer. 1 a 2 mm para alevino, 3 a 6 mm para adulto.'],
                ['acao'    => 'Divida em 2 a 3 refeições',
                 'detalhe' => 'Melhor aproveitamento e menos sobra. Evite alimentar nas horas '
                            . 'mais quentes e nunca de madrugada, quando o oxigênio está no '
                            . 'fundo do poço.'],
                ['acao'    => 'Ajuste pela biometria, não pelo chute',
                 'detalhe' => 'A quantidade é uma porcentagem da biomassa — e a biomassa muda '
                            . 'todo mês. Sem pesar amostra, ou você subalimenta ou desperdiça.'],
                ['acao'    => 'Guarde a ração no seco e no alto',
                 'detalhe' => 'Saco no chão úmido cria fungo e micotoxina. Ração de piscicultura '
                            . 'estraga rápido no clima quente e úmido — compre para no máximo '
                            . '30 a 45 dias.'],
            ],
            'numeros' => [
                ['Quantidade por dia',    '3% a 5% da biomassa'],
                ['Refeições',             '2 a 3 por dia'],
                ['Tempo de consumo',      'tudo comido em 15 a 20 minutos'],
                ['Pellet — alevino',      '1 a 2 mm'],
                ['Pellet — adulto',       '3 a 6 mm'],
                ['Proteína — crescimento','28% a 32% PB'],
            ],
            'alerta' => [
                'Ração boiando muito tempo depois do trato (excesso — reduza já)',
                'Peixes que param de comer de um dia para o outro',
                'Ração com mofo, empedrada ou fora do prazo',
                'Água ficando turva e escura após os tratos',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Pesca e Aquicultura',
                 'url'   => 'https://www.embrapa.br/pesca-e-aquicultura'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🏠',
            'titulo' => 'Viveiro e Estrutura',
            'porque' => 'Viveiro mal feito você paga o resto da vida: não seca direito, não '
                      . 'esvazia para a despesca, esquenta demais ou vaza. É o tipo de erro '
                      . 'que sai muito mais caro consertar do que fazer certo da primeira vez.',
            'passos' => [
                ['acao'    => 'Comece pequeno',
                 'detalhe' => 'Viveiro menor é mais fácil de manejar, medir e despescar. Quem '
                            . 'começa grande demais perde o controle da água e do lote.'],
                ['acao'    => 'Garanta profundidade e caída para o dreno',
                 'detalhe' => '1,2 a 1,8 m: raso esquenta e dá alga demais; fundo demais '
                            . 'dificulta a pesca. E o fundo tem que ter caída até o dreno, '
                            . 'senão o viveiro nunca esvazia por completo.'],
                ['acao'    => 'Faça a calagem antes de povoar',
                 'detalhe' => 'Cal no fundo seco desinfeta e corrige o solo. É o passo que '
                            . 'mais gente pula e depois paga com doença no ciclo inteiro.'],
                ['acao'    => 'Proteja de pássaro e de enchente',
                 'detalhe' => 'Garça e martim-pescador comem alevino sem parar — use tela ou '
                            . 'fios sobre o viveiro. E o dique precisa estar acima da cota de '
                            . 'cheia: transbordou, o lote vai embora e ainda solta peixe no '
                            . 'ambiente.'],
                ['acao'    => 'Cuide da entrada e da saída de água',
                 'detalhe' => 'Tela fina na entrada impede peixe invasor e predador de entrar; '
                            . 'a saída precisa de registro que permita esvaziar quando quiser.'],
            ],
            'numeros' => [
                ['Tamanho para iniciante', '0,5 a 2 hectares'],
                ['Profundidade',           '1,2 a 1,8 m'],
                ['Calagem antes do ciclo', 'cerca de 100 kg de cal por hectare'],
                ['Secagem entre ciclos',   '15 a 30 dias com o fundo exposto ao sol'],
                ['Entrada de água',        'com tela fina contra peixe invasor'],
            ],
            'alerta' => [
                'Nível caindo sem explicação (vazamento no dique ou no dreno)',
                'Viveiro que não esvazia por completo na despesca',
                'Muita perda de alevino nas primeiras semanas (predação por pássaro)',
                'Risco de transbordo em época de cheia',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Pesca e Aquicultura',
                 'url'   => 'https://www.embrapa.br/pesca-e-aquicultura'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🤝',
            'titulo' => 'Manejo do Lote e Despesca',
            'porque' => 'A decisão que mais pesa no resultado é quantos peixes você põe por '
                      . 'metro quadrado. Densidade alta demais sem aeração é o erro clássico: '
                      . 'o peixe cresce devagar, a água piora e o lote inteiro fica '
                      . 'desuniforme na hora de vender.',
            'passos' => [
                ['acao'    => 'Escolha a densidade pela estrutura que você tem',
                 'detalhe' => 'Sem aeração, 1 a 2 peixes por m² para tilápia. Só suba disso se '
                            . 'tiver aerador e renovação de água garantidos.'],
                ['acao'    => 'Faça biometria todo mês',
                 'detalhe' => 'Pese 50 a 100 peixes, calcule o peso médio e multiplique pelo '
                            . 'número estocado: essa é a biomassa que define a ração do mês. '
                            . 'Sem isso, você está tratando no escuro.'],
                ['acao'    => 'Manuseie de manhã cedo e com cuidado',
                 'detalhe' => 'Peixe estressado no manuseio perde muco e abre porta para '
                            . 'fungo e bactéria. Use puçá de malha macia e não deixe o peixe '
                            . 'fora d\'água mais que o necessário.'],
                ['acao'    => 'Faça despesca parcial',
                 'detalhe' => 'Tirar os maiores a cada 60 dias uniformiza o lote, alivia a '
                            . 'densidade e antecipa a entrada de dinheiro.'],
                ['acao'    => 'Despesque de madrugada ou ao amanhecer',
                 'detalhe' => 'Menos calor, menos estresse e carne de melhor qualidade. '
                            . 'Suspenda a ração 24 horas antes: peixe de estômago vazio '
                            . 'conserva melhor.'],
            ],
            'numeros' => [
                ['Densidade — sem aeração',   '1 a 2 peixes por m²'],
                ['Densidade — com aeração',   'até 5 peixes por m²'],
                ['Biometria',                 'todo mês, amostra de 50 a 100 peixes'],
                ['Ciclo — tilápia',           '6 a 8 meses até 700 g a 1 kg'],
                ['Ciclo — tambaqui',          '10 a 14 meses até 1,5 a 2 kg'],
                ['Jejum antes da despesca',   '24 horas'],
            ],
            'alerta' => [
                'Lote muito desuniforme (canibalismo ou competição por comida)',
                'Peixes com feridas e fungo depois de manuseio',
                'Crescimento parado mesmo com ração em dia',
                'Mortalidade subindo nas semanas seguintes a uma biometria',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Pesca e Aquicultura',
                 'url'   => 'https://www.embrapa.br/pesca-e-aquicultura'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🛡️',
            'titulo' => 'Sanidade e Biosseguridade',
            'porque' => 'Peixe não se vacina na criação familiar — o que existe é prevenção. '
                      . 'Quase toda doença aqui é consequência: água ruim, manuseio brusco ou '
                      . 'alevino de procedência duvidosa. Tratar sem corrigir a causa é jogar '
                      . 'remédio na água.',
            'passos' => [
                ['acao'    => 'Compre alevino de piscicultura registrada',
                 'detalhe' => 'Exija nota e documento sanitário. Alevino barato de origem '
                            . 'desconhecida traz parasita e doença para dentro do seu viveiro '
                            . 'de graça.'],
                ['acao'    => 'Aclimate o alevino na chegada',
                 'detalhe' => 'Boie o saco fechado no viveiro por 15 a 20 minutos para '
                            . 'igualar a temperatura, depois vá misturando a água aos poucos. '
                            . 'Choque térmico mata mais alevino que doença.'],
                ['acao'    => 'Faça quarentena do lote novo',
                 'detalhe' => '7 a 10 dias em tanque separado antes de juntar ao viveiro '
                            . 'principal.'],
                ['acao'    => 'Olhe o comportamento todo dia',
                 'detalhe' => 'Peixe se coçando no fundo, nadando torto, isolado ou parado na '
                            . 'borda é sinal precoce. Doença de peixe começa no comportamento, '
                            . 'antes de aparecer ferida.'],
                ['acao'    => 'Nunca medique por conta própria',
                 'detalhe' => 'Antibiótico sem prescrição contamina a água, gera resistência e '
                            . 'deixa resíduo no peixe que vai para a mesa. Sal e banhos só com '
                            . 'orientação técnica. Retire o peixe morto todo dia.'],
            ],
            'numeros' => [
                ['Aclimatação',        '15 a 20 minutos boiando o saco'],
                ['Quarentena',         '7 a 10 dias em tanque separado'],
                ['Análise de água',    'pelo menos 1 vez por semana'],
                ['Peixe morto',        'retirar todo dia e enterrar'],
                ['Vazio sanitário',    '15 a 30 dias com o fundo seco'],
                ['Medicamento',        'somente com prescrição — respeite a carência'],
            ],
            'alerta' => [
                'Peixes se coçando no fundo ou nas bordas (parasitas)',
                'Feridas na pele, nadadeiras roídas ou manchas esbranquiçadas',
                'Mortalidade aumentando dia após dia',
                'Peixes isolados, escuros e parados perto da margem',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Pesca e Aquicultura',
                 'url'   => 'https://www.embrapa.br/pesca-e-aquicultura'],
            ],
            'revisado' => 'agosto/2026',
        ],

    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
