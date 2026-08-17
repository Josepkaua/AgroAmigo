<?php
declare(strict_types=1);
require_once 'includes/auth.php';
session_init(); // conteúdo público — não exige login

$pagina        = 'ovinos';
$titulo_pagina = 'Ovinos';

$animal = [
    'nome'     => 'Ovinos',
    'emoji'    => '🐑',
    'imagem'   => 'https://images.unsplash.com/photo-1494079218307-7fa091ab4df2?w=1400&q=80&auto=format&fit=crop',
    'descricao'=> 'A ovinocultura no Maranhão é dominada pela raça Santa Inês, altamente adaptada ao clima tropical. A criação de ovinos oferece ótima rentabilidade e ciclo curto de produção para a agricultura familiar.',
    'racas' => [
        ['emoji'=>'🐑','nome'=>'Santa Inês',   'tipo'=>'Corte/Misto','imagem'=>'assets/img/racas/ovinos/santa-ines.jpg','desc'=>'Raça deslanada brasileira formada no Nordeste (Morada Nova × Somalis Brasileira × Bergamácia). Machos 80–100 kg, fêmeas 60–70 kg. Cordeiros atingem 28–35 kg ao abate em 90–120 dias. Poliéstrica anual: reproduz em qualquer época do ano. Prolificidade 1,1–1,4 crias/parto. Maior resistência a helmintos gastrointestinais que raças lanadas em clima tropical.'],
        ['emoji'=>'🐏','nome'=>'Dorper',        'tipo'=>'Corte',      'imagem'=>'assets/img/racas/ovinos/dorper.jpg','desc'=>'Raça sul-africana deslanada: corpo branco com cabeça e pescoço negros, pele pigmentada. Maturidade precoce — F1 Dorper × Santa Inês atinge 35–40 kg em 90–100 dias. Rendimento de carcaça 48–55% com excelente proporção músculo:osso. Cruzamento mais eficaz para elevar produtividade sem perda de rusticidade no Nordeste brasileiro.'],
        ['emoji'=>'🐑','nome'=>'SRD (Comum)',   'tipo'=>'Misto',      'imagem'=>'https://images.unsplash.com/photo-1613573057422-ec1f77c60028?w=600&q=80&auto=format&fit=crop','desc'=>'Ovinos mestiços sem padrão racial definido, selecionados empiricamente por gerações de criadores maranhenses. Peso adulto 30–50 kg, prolificidade 1,0–1,3 crias/parto. Elevada rusticidade ao clima tropical úmido e ao cerrado-caatinga maranhense. Aptidão predominantemente para carne e subsistência — baixíssimo custo de manutenção.'],
    ],
    'topicos' => [

        [
            'icone'  => '🛡️',
            'titulo' => 'Verminose — o problema nº 1',
            'porque' => 'No Maranhão o que mais mata ovino não é falta de comida: é o '
                      . 'Haemonchus, verme que suga sangue no estômago do animal. Ele deixa a '
                      . 'ovelha anêmica, com papada embaixo do queixo, e mata sem diarreia '
                      . 'nenhuma. E o pior: vermifugar todo mundo todo mês criou verme que já '
                      . 'não morre com remédio.',
            'passos' => [
                ['acao'    => 'Adote o FAMACHA — trate só quem precisa',
                 'detalhe' => 'Uma vez por mês, olhe a cor da mucosa do olho de cada animal e '
                            . 'compare com o cartão. Escore 1 e 2 não trata; 3 observa; 4 e 5 '
                            . 'trata na hora. Deixar os resistentes sem tratar é de propósito: '
                            . 'são eles que mantêm o verme sensível ao remédio no pasto.'],
                ['acao'    => 'Troque o princípio ativo, não a marca',
                 'detalhe' => 'Olhe a bula, não a embalagem: albendazol, ivermectina, levamisol, '
                            . 'closantel, monepantel. Só troque quando o produto começar a '
                            . 'falhar — e confirme com exame de fezes antes e 10 dias depois.'],
                ['acao'    => 'Rode os piquetes e evite o orvalho',
                 'detalhe' => 'A larva sobe na pontinha do capim de madrugada e some no sol '
                            . 'forte. Solte os animais depois que o orvalho secar e deixe o '
                            . 'piquete descansar 60 a 90 dias entre usos.'],
                ['acao'    => 'Quarentena de 21 dias para animal comprado',
                 'detalhe' => 'Vermifugue com dois princípios diferentes e só solte no rebanho '
                            . 'depois. É assim que entra verme resistente na propriedade.'],
                ['acao'    => 'Descarte o que vive doente',
                 'detalhe' => 'Ovelha que precisa de vermífugo o tempo todo passa isso para as '
                            . 'crias. Descartar é manejo, não crueldade.'],
            ],
            'numeros' => [
                ['FAMACHA',              'todo mês, animal por animal'],
                ['Tratar',               'escore 4 e 5 (3 sob observação)'],
                ['Descanso do piquete',  '60 a 90 dias'],
                ['Quarentena',           '21 dias'],
                ['Conferir se funcionou','exame de fezes 10 dias após o vermífugo'],
            ],
            'alerta' => [
                'Mucosa do olho branca ou papada (edema) embaixo do queixo — anemia grave, é urgência',
                'Animal vermifugado que não melhora em 5 dias (suspeita de resistência)',
                'Mortes seguidas no lote sem causa clara',
                'Cordeiros parando de crescer mesmo com pasto e sal bons',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Caprinos e Ovinos',
                 'url'   => 'https://www.embrapa.br/caprinos-e-ovinos'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '💉',
            'titulo' => 'Sanidade e Vacinação',
            'porque' => 'Clostridiose em ovino não dá tempo de tratar: o produtor encontra o '
                      . 'cordeiro mais bonito do lote morto no pasto. É justamente o animal em '
                      . 'melhor condição que morre. A vacina é barata e é a única defesa.',
            'passos' => [
                ['acao'    => 'Clostridioses: vacine o rebanho todo, uma vez por ano',
                 'detalhe' => 'A polivalente cobre enterotoxemia e gangrena gasosa. Animal que '
                            . 'nunca tomou precisa de duas doses com 30 dias de intervalo — '
                            . 'a primeira sozinha não protege.'],
                ['acao'    => 'Ovelha prenhe: vacine 30 dias antes do parto',
                 'detalhe' => 'O cordeiro nasce protegido pelo colostro e fica coberto até uns '
                            . '60 a 90 dias. É a proteção mais barata que existe.'],
                ['acao'    => 'Cordeiro: primeira dose aos 60 dias, reforço 30 dias depois',
                 'detalhe' => 'Antes disso o anticorpo da mãe ainda atrapalha a resposta da '
                            . 'vacina.'],
                ['acao'    => 'Raiva: anual, onde há morcego hematófago',
                 'detalhe' => 'Região com gruta, casa velha, ponte ou tronco oco, ou animal '
                            . 'aparecendo com mordida no pescoço: vacine. Não tem tratamento e '
                            . 'é zoonose.'],
                ['acao'    => 'Guarde a vacina direito',
                 'detalhe' => 'Entre 2°C e 8°C, em caixa térmica com gelo reciclável na ida ao '
                            . 'campo. Vacina que esquentou não protege — e você só descobre '
                            . 'quando o animal morre.'],
            ],
            'numeros' => [
                ['Clostridiose — 1ª vez',    'duas doses, 30 dias entre elas'],
                ['Clostridiose — depois',    'reforço anual'],
                ['Ovelha prenhe',            '30 dias antes do parto'],
                ['Cordeiro — 1ª dose',       'aos 60 dias de vida'],
                ['Conservação',              'entre 2°C e 8°C, sem congelar'],
            ],
            'alerta' => [
                'Cordeiro em boa condição encontrado morto sem sinal de doença',
                'Vários animais adoecendo na mesma semana',
                'Animal com salivação, dificuldade de engolir ou agressividade fora do normal (suspeita de raiva — não manipule sem proteção)',
                'Ferida na boca, no casco ou entre os dedos com salivação e manqueira — comunique à AGED-MA, é obrigatório por lei',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Caprinos e Ovinos',
                 'url'   => 'https://www.embrapa.br/caprinos-e-ovinos'],
                ['texto' => 'AGED-MA', 'url' => 'https://www.aged.ma.gov.br/'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🌿',
            'titulo' => 'Nutrição e Pastagem',
            'porque' => 'O cordeiro cresce rápido nos primeiros meses e depois desanda — quem '
                      . 'perde essa janela vende animal magro. E tem um detalhe que mata: sal '
                      . 'mineral de bovino tem cobre demais para ovino, e cobre acumulado '
                      . 'mata de uma vez só.',
            'passos' => [
                ['acao'    => 'Use sal mineral específico de ovino',
                 'detalhe' => 'Nunca o de bovino. O ovino não elimina bem o cobre; ele se '
                            . 'acumula no fígado e um dia solta tudo de vez — o animal fica '
                            . 'amarelo e morre em horas. Cocho coberto, disponível o dia todo.'],
                ['acao'    => 'Aproveite a fase de creep-feeding do cordeiro',
                 'detalhe' => 'Cerquinha onde só o cordeiro entra, com concentrado à vontade, '
                            . 'a partir dos 15 dias. É onde o quilo sai mais barato de toda '
                            . 'a criação.'],
                ['acao'    => 'Reforce a ovelha nos últimos 45 dias de gestação',
                 'detalhe' => 'Dois terços do crescimento do cordeiro acontecem aí. Faltando '
                            . 'energia, dá toxemia da prenhez e morrem mãe e cria.'],
                ['acao'    => 'Faça reserva para a seca ainda na chuva',
                 'detalhe' => 'Feno, silagem, cana picada, palma. Rebanho que perde peso na '
                            . 'seca não recupera a estação inteira.'],
                ['acao'    => 'Aumente concentrado devagar',
                 'detalhe' => 'Subir de uma vez causa acidose e enterotoxemia. Ajuste ao longo '
                            . 'de 10 a 14 dias.'],
            ],
            'numeros' => [
                ['Forragem por dia',        '3% a 5% do peso vivo'],
                ['Água — adulto',           '3 a 4 litros por dia'],
                ['Concentrado — terminação','200 a 400 g/dia (milho + farelo de soja)'],
                ['Ganho de peso — meta',    '200 a 250 g por dia na terminação'],
                ['Sal mineral',             'específico de ovino (nunca o de bovino)'],
                ['Troca de dieta',          'gradual, em 10 a 14 dias'],
            ],
            'alerta' => [
                'Animal com mucosa e urina amareladas (suspeita de intoxicação por cobre)',
                'Ovelha no fim da gestação deitada, apática, sem comer (toxemia da prenhez)',
                'Barriga estufada do lado esquerdo com o animal gemendo (timpanismo)',
                'Lote inteiro perdendo peso mesmo com pasto disponível',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Caprinos e Ovinos',
                 'url'   => 'https://www.embrapa.br/caprinos-e-ovinos'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🤝',
            'titulo' => 'Manejo e Reprodução',
            'porque' => 'A vantagem do ovino é o ciclo curto: cinco meses de gestação e cordeiro '
                      . 'pronto em três a quatro meses. Com a Santa Inês, que reproduz o ano '
                      . 'todo, dá para tirar três partos em dois anos — mas só com organização.',
            'passos' => [
                ['acao'    => 'Cubra a borrega pelo peso, não pela idade',
                 'detalhe' => 'Cerca de 70% do peso adulto — em geral 8 a 10 meses. Coberta '
                            . 'cedo demais, ela para de crescer e o parto vem difícil.'],
                ['acao'    => 'Concentre as coberturas em estação de monta',
                 'detalhe' => 'Solte o carneiro por 45 a 60 dias e recolha. Assim os partos '
                            . 'saem juntos, os lotes ficam uniformes e você planeja a venda '
                            . 'em vez de correr atrás dela.'],
                ['acao'    => 'Cuide do parto e do colostro',
                 'detalhe' => 'O cordeiro precisa mamar nas primeiras 6 horas. Desinfete o '
                            . 'umbigo com iodo a 10%. As primeiras 48 horas decidem quase '
                            . 'toda a mortalidade de cordeiro.'],
                ['acao'    => 'Desmame entre 75 e 90 dias e separe por sexo',
                 'detalhe' => 'A ovelha volta ao cio mais cedo e os machos crescem melhor '
                            . 'separados das fêmeas.'],
                ['acao'    => 'Pese todo mês e anote',
                 'detalhe' => 'Sem balança você não sabe se está ganhando ou perdendo dinheiro. '
                            . 'A ficha do AgroAmigo tem os campos de pesagem e parto.'],
            ],
            'numeros' => [
                ['Gestação',            '150 dias (cerca de 5 meses)'],
                ['Cio',                 'a cada 17 dias, dura 24 a 36 horas'],
                ['1ª cobertura',        'cerca de 70% do peso adulto'],
                ['Estação de monta',    '45 a 60 dias com o carneiro no lote'],
                ['Desmame',             '75 a 90 dias'],
                ['Reprodutor',          '1 carneiro para 20 a 30 ovelhas'],
            ],
            'alerta' => [
                'Ovelha em trabalho de parto há mais de 1 hora sem sair a cria',
                'Aborto em mais de uma ovelha na mesma temporada',
                'Cordeiros nascendo fracos ou morrendo nos primeiros dias',
                'Úbere quente, duro ou com leite alterado (mastite)',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Caprinos e Ovinos',
                 'url'   => 'https://www.embrapa.br/caprinos-e-ovinos'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🏠',
            'titulo' => 'Instalações e Ambiência',
            'porque' => 'Instalação de ovino não precisa ser cara — precisa ser seca. Chão '
                      . 'úmido embaixo do animal é fábrica de larva de verme, casco podre e '
                      . 'pneumonia. Um aprisco simples e bem ventilado vale mais que um caro '
                      . 'e abafado.',
            'passos' => [
                ['acao'    => 'Levante o piso',
                 'detalhe' => 'Tablado ripado a 0,5–0,8 m do chão: dejeto cai, animal fica '
                            . 'seco, larva de verme não sobe.'],
                ['acao'    => 'Ventile de verdade',
                 'detalhe' => 'Laterais abertas ou meia-parede e beiral largo. Galpão fechado '
                            . 'no calor do Maranhão é doença respiratória garantida.'],
                ['acao'    => 'Separe por categoria',
                 'detalhe' => 'Ovelha parida, borrega, cordeiro desmamado e carneiro em lotes '
                            . 'diferentes. Cada um come diferente e briga menos.'],
                ['acao'    => 'Sombra e água no piquete',
                 'detalhe' => 'Sem sombra o animal para de pastar nas horas quentes; sem água '
                            . 'perto, ele anda demais e gasta o que comeu.'],
                ['acao'    => 'Limpe antes que acumule',
                 'detalhe' => 'Retire o esterco 2 vezes por semana na seca e mais na chuva.'],
            ],
            'numeros' => [
                ['Área coberta — adulto',  '1,2 m² por animal'],
                ['Área — cordeiro',        '0,5 m² por animal'],
                ['Altura do tablado',      '0,5 a 0,8 m do chão'],
                ['Espaço de cocho',        '30 a 40 cm por animal'],
                ['Limpeza',                '2 vezes por semana (mais na chuva)'],
            ],
            'alerta' => [
                'Vários animais tossindo ou com secreção nasal',
                'Animais mancando, com cheiro forte entre os dedos (podridão do casco)',
                'Lote inquieto, amontoado ou brigando (superlotação ou calor)',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Caprinos e Ovinos',
                 'url'   => 'https://www.embrapa.br/caprinos-e-ovinos'],
            ],
            'revisado' => 'agosto/2026',
        ],

    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
