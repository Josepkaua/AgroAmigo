<?php
declare(strict_types=1);
require_once 'includes/auth.php';
session_init(); // conteúdo público — não exige login

$pagina        = 'caprinos';
$titulo_pagina = 'Caprinos';

$animal = [
    'nome'     => 'Caprinos',
    'emoji'    => '🐐',
    'imagem'   => 'https://images.unsplash.com/photo-1560819400-434c188f63ef?w=1400&q=80&auto=format&fit=crop',
    'descricao'=> 'A caprinocultura é uma das atividades mais importantes da agricultura familiar maranhense. Cabras são animais rústicos, adaptados ao clima semiárido e muito importantes para a geração de renda.',
    'racas' => [
        ['emoji'=>'🐐','nome'=>'Anglo-nubiano', 'tipo'=>'Leite/Misto','imagem'=>'assets/img/racas/caprinos/anglo-nubiano.jpg','desc'=>'Raça de dupla aptidão originada no Reino Unido, com orelhas longas e pendentes e perfil nasal convexo (ariano). Produção: 2–4 litros/dia com 4,5–5,0% de gordura — leite superior para queijo artesanal. Machos 70–100 kg, fêmeas 50–70 kg. Prolificidade 1,6–2,0 crias/parto. Bem distribuída no Nordeste e base dos cruzamentos leiteiros do Maranhão.'],
        ['emoji'=>'🐑','nome'=>'Boer',          'tipo'=>'Corte',      'imagem'=>'https://images.unsplash.com/photo-1528127044085-fdef44dd867c?w=600&q=80&auto=format&fit=crop','desc'=>'Raça sul-africana especializada em carne: corpo largo e musculoso, pelagem branca com cabeça e pescoço marrom-avermelhados. F1 com SRD: abate com 30–35 kg em 6–8 meses, rendimento de carcaça 48–52%. Conversão alimentar 4–5:1. Principal ferramenta de melhoramento do rebanho caprino nordestino — usado exclusivamente em cruzamento terminal.'],
        ['emoji'=>'🐐','nome'=>'SRD (Comum)',   'tipo'=>'Misto',      'imagem'=>'https://images.unsplash.com/photo-1593750439808-958d28558592?w=600&q=80&auto=format&fit=crop','desc'=>'Caprinos sem padrão racial definido, descendentes de raças ibéricas introduzidas no século XVI, com séculos de seleção natural para o semiárido. Peso adulto 25–45 kg, prolificidade 1,4–1,7 crias/parto. Extremamente rústicos: toleram seca, calor extremo e pastagens de caatinga/cerrado maranhense. Custo de manutenção muito baixo — base da caprinocultura familiar do Maranhão.'],
    ],
    'topicos' => [

        [
            'icone'  => '💉',
            'titulo' => 'Sanidade e Vacinação',
            'porque' => 'Em caprino, a doença que mata quase nunca avisa. Clostridiose derruba '
                      . 'o cabrito mais gordo do lote da noite para o dia, sem sinal nenhum. '
                      . 'A dose da vacina custa centavos; o animal perdido custa o mês inteiro '
                      . 'de trabalho.',
            'passos' => [
                ['acao'    => 'Clostridioses: vacine o rebanho todo antes da chuva',
                 'detalhe' => 'A vacina polivalente protege contra enterotoxemia (mole-mole) e '
                            . 'gangrena gasosa. Aplique 30 dias antes do início do período '
                            . 'chuvoso, quando o pasto muda e o risco sobe. Animal que nunca '
                            . 'tomou precisa de duas doses com 30 dias de intervalo — só a '
                            . 'segunda protege de verdade.'],
                ['acao'    => 'Cabra prenhe: vacine 30 dias antes do parto',
                 'detalhe' => 'Assim ela passa a proteção para o cabrito pelo colostro, e ele '
                            . 'chega protegido até os 2 meses. É o jeito mais barato de proteger '
                            . 'a cria, porque você vacina um animal e protege dois.'],
                ['acao'    => 'Raiva: anual, onde há morcego hematófago',
                 'detalhe' => 'Se tem gruta, casa velha, ponte ou tronco oco na região, ou se '
                            . 'apareceu animal com mordida no pescoço, vacine. Raiva não tem '
                            . 'tratamento e é zoonose — pega em gente.'],
                ['acao'    => 'Mal do caroço (linfadenite): não existe vacina, existe manejo',
                 'detalhe' => 'Não há vacina comercial no Brasil. O controle é separar o animal '
                            . 'com caroço, nunca abrir o abscesso dentro do aprisco, e descartar '
                            . 'o animal que repete. Quem abre caroço no chão do curral contamina '
                            . 'o lugar por anos.'],
                ['acao'    => 'Uma agulha por animal, sempre',
                 'detalhe' => 'Agulha repetida espalha mal do caroço e artrite-encefalite pelo '
                            . 'rebanho inteiro. É o erro mais comum e o mais caro.'],
            ],
            'numeros' => [
                ['Clostridiose — 1ª vez',        'duas doses, 30 dias entre elas'],
                ['Clostridiose — depois',        'reforço 1 vez por ano'],
                ['Melhor época',                 '30 dias antes das chuvas'],
                ['Cabra prenhe',                 '30 dias antes do parto'],
                ['Conservação da vacina',        'entre 2°C e 8°C, sem congelar'],
                ['Vacina fora da geladeira',     'perde efeito — descarte, não aplique'],
            ],
            'alerta' => [
                'Cabrito gordo encontrado morto sem doença aparente (suspeita de enterotoxemia)',
                'Vários animais adoecendo ou morrendo na mesma semana',
                'Animal com salivação, dificuldade de engolir ou agressividade fora do normal (suspeita de raiva — não manipule sem proteção)',
                'Feridas na boca e no focinho que se espalham pelo lote (ectima — pega em gente que maneja)',
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
            'porque' => 'Cabra não é vaca pequena. Ela come de cima para baixo — prefere folha '
                      . 'de arbusto a capim rasteiro — e por isso aproveita a vegetação nativa '
                      . 'que o boi despreza. Quem entende isso gasta muito menos com ração.',
            'passos' => [
                ['acao'    => 'Deixe ela pastar folha, não só capim',
                 'detalhe' => 'Caprino é ramoneador: come folha de arbusto e árvore. Mantenha '
                            . 'no piquete leucena, gliricídia, sabiá, mororó. É proteína de graça '
                            . 'e ainda segura a alimentação na seca.'],
                ['acao'    => 'Sal mineral de caprino, não de boi',
                 'detalhe' => 'O mineral de bovino tem cobre em nível que pode intoxicar caprino '
                            . 'e é fatal em ovino. Compre o específico, deixe no cocho coberto, '
                            . 'disponível o dia todo.'],
                ['acao'    => 'Guarde comida para a seca ainda na chuva',
                 'detalhe' => 'Feno de capim ou de leucena, silagem, palma forrageira, raspa de '
                            . 'mandioca. Quem só pensa em comida quando a seca chegou já perdeu '
                            . 'peso do rebanho.'],
                ['acao'    => 'Cabra em lactação precisa de concentrado',
                 'detalhe' => 'Cerca de 200 a 300 g de concentrado para cada litro de leite '
                            . 'produzido. Sem isso ela tira do próprio corpo, emagrece e o '
                            . 'próximo parto vem ruim.'],
                ['acao'    => 'Mudança de dieta é sempre devagar',
                 'detalhe' => 'Aumentar concentrado de uma vez causa acidose e enterotoxemia. '
                            . 'Suba a quantidade ao longo de 10 a 14 dias.'],
            ],
            'numeros' => [
                ['Forragem por dia',            '3% a 5% do peso vivo'],
                ['Água — adulto',               '3 a 5 litros por dia'],
                ['Água — cabra em lactação',    'até 8 litros por dia'],
                ['Concentrado na lactação',     '200 a 300 g por litro de leite'],
                ['Sal mineral',                 'específico de caprino, à vontade'],
                ['Troca de dieta',              'gradual, em 10 a 14 dias'],
            ],
            'alerta' => [
                'Animal com barriga estufada do lado esquerdo, gemendo (timpanismo — é emergência)',
                'Cabras emagrecendo mesmo com pasto bom',
                'Queda súbita na produção de leite do rebanho todo',
                'Suspeita de planta tóxica no piquete',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Caprinos e Ovinos',
                 'url'   => 'https://www.embrapa.br/caprinos-e-ovinos'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🏠',
            'titulo' => 'Aprisco e Ambiência',
            'porque' => 'Caprino aguenta calor e seca, mas não aguenta chão molhado. Umidade '
                      . 'embaixo do animal é o que faz verme, casco podre e pneumonia. Aprisco '
                      . 'seco e ventilado resolve metade dos problemas sanitários do rebanho.',
            'passos' => [
                ['acao'    => 'Levante o piso do chão',
                 'detalhe' => 'Tablado ripado a 0,7–1 m do solo. As fezes e a urina caem, o '
                            . 'animal fica seco e a larva de verme não sobe. É a obra que mais '
                            . 'devolve dinheiro na caprinocultura.'],
                ['acao'    => 'Deixe o vento passar',
                 'detalhe' => 'Aprisco fechado e abafado dá doença respiratória. Laterais abertas '
                            . 'ou meia-parede, telhado com beiral largo para a chuva não entrar.'],
                ['acao'    => 'Não aperte os animais',
                 'detalhe' => 'Superlotação é briga, estresse, mais verme e menos ganho de peso. '
                            . 'Respeite a área por cabeça e separe os lotes por categoria.'],
                ['acao'    => 'Recolha o esterco de baixo do tablado',
                 'detalhe' => 'Na época da chuva, pelo menos toda semana. Esterco acumulado e '
                            . 'úmido é criadouro de mosca e de larva de verme.'],
                ['acao'    => 'Sombra no piquete',
                 'detalhe' => 'Árvore nativa é a melhor sombra: refresca e ainda serve de comida. '
                            . 'Sem sombra, o animal para de pastar nas horas quentes e come menos.'],
            ],
            'numeros' => [
                ['Área — adulto',        '1,5 m² por animal'],
                ['Área — cabrito',       '1,0 m² por animal'],
                ['Altura do tablado',    '0,7 a 1,0 m do chão'],
                ['Espaço de cocho',      '30 a 40 cm por animal adulto'],
                ['Limpeza na chuva',     'pelo menos 1 vez por semana'],
            ],
            'alerta' => [
                'Vários animais tossindo ou com secreção no nariz',
                'Animais mancando ou com cheiro forte no casco (podridão)',
                'Cabritos morrendo nos primeiros dias de vida',
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
            'porque' => 'A conta da caprinocultura fecha no número de cabritos desmamados por '
                      . 'cabra por ano. Tudo o que você faz na reprodução e na ordenha é para '
                      . 'aumentar esse número sem estragar a matriz.',
            'passos' => [
                ['acao'    => 'Cubra a cabrita pelo peso, não pela idade',
                 'detalhe' => 'Ela precisa ter cerca de 70% do peso adulto da raça — em geral 7 a '
                            . '10 meses. Cabrita coberta cedo demais para de crescer, tem parto '
                            . 'difícil e cria fraca.'],
                ['acao'    => 'Cuide do parto e do colostro',
                 'detalhe' => 'O cabrito precisa mamar o colostro nas primeiras 6 horas de vida — '
                            . 'depois disso o intestino já não absorve os anticorpos. Desinfete o '
                            . 'umbigo com iodo a 10% logo ao nascer.'],
                ['acao'    => 'Ordenhe em horário fixo e com higiene',
                 'detalhe' => 'Duas vezes ao dia, sempre na mesma hora. Antes: limpe o úbere e '
                            . 'descarte os primeiros jatos numa caneca de fundo escuro — é ali '
                            . 'que a mastite aparece primeiro.'],
                ['acao'    => 'Casqueie a cada 4 a 6 meses',
                 'detalhe' => 'Casco comprido faz o animal andar errado, pastar menos e '
                            . 'emperrar a reprodução. É serviço simples e evita animal manco.'],
                ['acao'    => 'Anote parto por parto',
                 'detalhe' => 'Sem registro você não sabe qual cabra dá lucro e qual só come. '
                            . 'A ficha do AgroAmigo já tem os campos de cobertura, parto e '
                            . 'desmame.'],
            ],
            'numeros' => [
                ['Gestação',                    '150 dias (cerca de 5 meses)'],
                ['Cio',                         'a cada 21 dias, dura 24 a 36 horas'],
                ['1ª cobertura',                'cerca de 70% do peso adulto'],
                ['Colostro',                    'nas primeiras 6 horas de vida'],
                ['Desmame',                     '60 a 90 dias'],
                ['Reprodutor',                  '1 bode para 30 a 40 cabras'],
            ],
            'alerta' => [
                'Cabra em trabalho de parto há mais de 1 hora sem sair a cria',
                'Úbere quente, duro, ou leite com grumos e sangue (mastite)',
                'Aborto em mais de uma cabra na mesma temporada',
                'Cabritos nascendo fracos ou mortos com frequência',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Caprinos e Ovinos',
                 'url'   => 'https://www.embrapa.br/caprinos-e-ovinos'],
            ],
            'revisado' => 'agosto/2026',
        ],

        [
            'icone'  => '🛡️',
            'titulo' => 'Verminose e Biosseguridade',
            'porque' => 'Verme é o que mais mata caprino no Nordeste — e vermifugar o rebanho '
                      . 'todo de mês em mês só piora, porque cria verme resistente que nenhum '
                      . 'remédio segura. O caminho é tratar quem precisa, quando precisa.',
            'passos' => [
                ['acao'    => 'Use o FAMACHA todo mês',
                 'detalhe' => 'É olhar a cor da mucosa do olho e comparar com o cartão. Escore 1 '
                            . 'e 2 não trata; 3 observa; 4 e 5 trata na hora. Peça o cartão e o '
                            . 'treinamento ao técnico — sem treino a leitura erra.'],
                ['acao'    => 'Revese o princípio ativo, não a marca',
                 'detalhe' => 'Duas marcas podem ter o mesmo princípio. Olhe a bula: albendazol, '
                            . 'ivermectina, levamisol, closantel. Só troque quando o produto '
                            . 'começar a falhar, e confirme com exame de fezes.'],
                ['acao'    => 'Rode os piquetes',
                 'detalhe' => 'Cerca de 30 dias de uso e 60 a 90 dias de descanso quebram o ciclo '
                            . 'do verme no pasto. Evite soltar os animais de manhã cedo com '
                            . 'orvalho — é quando a larva está na ponta do capim.'],
                ['acao'    => 'Faça quarentena de animal comprado',
                 'detalhe' => '21 dias separado, vermifugado e observado. Animal novo é a porta '
                            . 'de entrada de mal do caroço, verme resistente e podridão do casco.'],
                ['acao'    => 'Pedilúvio na entrada do aprisco',
                 'detalhe' => 'Sulfato de zinco a 10%, uma passagem por semana na época da chuva, '
                            . 'previne a podridão dos cascos no rebanho todo.'],
            ],
            'numeros' => [
                ['FAMACHA — frequência',   'todo mês, animal por animal'],
                ['FAMACHA — tratar',       'escore 4 e 5 (3 sob observação)'],
                ['Descanso do piquete',    '60 a 90 dias'],
                ['Quarentena',             '21 dias'],
                ['Pedilúvio',              'sulfato de zinco a 10%, semanal na chuva'],
            ],
            'alerta' => [
                'Animal com a mucosa do olho branca e papada embaixo do queixo (anemia grave)',
                'Vermífugo aplicado e o animal não melhora (suspeita de resistência)',
                'Diarreia no lote com perda rápida de peso',
                'Animal que precisa de vermífugo toda hora — em geral é caso de descarte',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Caprinos e Ovinos — controle de verminose',
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
