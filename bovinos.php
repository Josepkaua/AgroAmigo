<?php
declare(strict_types=1);
require_once 'includes/auth.php';
session_init(); // conteúdo público — não exige login

$pagina        = 'bovinos';
$titulo_pagina = 'Bovinos';

$animal = [
    'nome'     => 'Bovinos',
    'emoji'    => '🐄',
    'imagem'   => 'https://images.unsplash.com/photo-1588152850700-c82ecb8ba9b1?w=1400&q=80&auto=format&fit=crop',
    'descricao'=> 'Orientações técnicas para criação de bovinos de corte e leite no Maranhão, com foco nas raças mais adaptadas ao clima quente e úmido da região.',
    'racas' => [
        ['emoji'=>'🐂','nome'=>'Nelore',    'tipo'=>'Corte',      'imagem'=>'assets/img/racas/bovinos/nelore.jpg', 'desc'=>'Zebuíno de origem indiana (raça Ongole), pelagem branco-acinzentada, pele escura e corcova proeminente. Resistência natural ao carrapato (Rhipicephalus microplus) e à tristeza parasitária. Ganho a pasto: 400–600 g/dia. Abate com 450–520 kg (rendimento de carcaça 52–54%). Padrão dominante de corte no Maranhão e Tocantins. Abate médio: 30–36 meses a pasto.'],
        ['emoji'=>'🐄','nome'=>'Girolando', 'tipo'=>'Leite/Misto','imagem'=>'assets/img/racas/bovinos/girolando.jpg', 'desc'=>'Síntese brasileira entre Gir (37,5–87,5%) e Holandês, desenvolvida pela Embrapa. Combina rusticidade tropical com produção leiteira: 10–20 litros/dia em sistema semi-intensivo no Maranhão. Gordura 3,5–4,2%. Resiste ao carrapato e ao calor (ITGU conforto até 79). Raça mais criada na bacia leiteira maranhense. Padrão recomendado: 5/8 Holandês × 3/8 Gir.'],
        ['emoji'=>'🐃','nome'=>'Gir',       'tipo'=>'Leite',      'imagem'=>'assets/img/racas/bovinos/gir.jpg', 'desc'=>'Zebuíno indiano (origem Gujarat/Rajastão), coloração variável de amarelo-claro a vermelho-escuro, orelhas longas tubuladas e chanfro convexo (perfil de "papagaio"). Machos 600–750 kg, fêmeas 350–450 kg. Produção de leite: 8–14 litros/dia. Transpira mais que taurinos — melhor adaptação ao calor. Base genética do Girolando e do Guzerá leiteiro. Alta resistência à mosca-dos-chifres e ao berne.'],
    ],
    'topicos' => [

        // ─────────────────────────────────────────────────────
        [
            'icone'  => '💉',
            'titulo' => 'Sanidade e Vacinação',
            'porque' => 'Vacina é o gasto mais barato da criação. Um bezerro que morre de '
                      . 'clostridiose custa o preço de dezenas de doses. E duas vacinas aqui '
                      . 'não são escolha sua: são exigidas por lei, e sem elas você não '
                      . 'consegue emitir a GTA para vender ou transportar o animal.',
            'passos' => [
                ['acao'    => 'Brucelose: vacine TODA fêmea entre 3 e 8 meses',
                 'detalhe' => 'Uma única vez na vida. É obrigatório por lei federal (PNCEBT). '
                            . 'Atenção: só pode ser aplicada por médico veterinário cadastrado '
                            . 'no serviço veterinário estadual — você não pode comprar e aplicar '
                            . 'sozinho. O animal recebe marca a fogo e certificado.'],
                ['acao'    => 'Não vacine brucelose e clostridiose no mesmo dia',
                 'detalhe' => 'Aplicar as duas juntas na mesma bezerra sobrecarrega o sistema '
                            . 'imune e reduz a resposta das duas. Separe por pelo menos 15 dias.'],
                ['acao'    => 'Clostridioses: bezerros a partir de 4 meses, com reforço',
                 'detalhe' => 'Protege contra carbúnculo sintomático (manqueira), gangrena e '
                            . 'enterotoxemia. Primeira dose, reforço 30 a 60 dias depois, e '
                            . 'revacinação anual. Não é obrigatória por lei, mas é a que mais '
                            . 'evita morte súbita de bezerro no pasto.'],
                ['acao'    => 'Raiva: anual, onde há morcego hematófago',
                 'detalhe' => 'Se tem gruta, casa velha, ponte ou tronco oco na região, ou se '
                            . 'apareceu animal com mordida no pescoço, vacine. A raiva não tem '
                            . 'tratamento — o animal que adoece morre.'],
                ['acao'    => 'Anote tudo: data, lote, fabricante e quem aplicou',
                 'detalhe' => 'Sem registro você não comprova nada na fiscalização nem na venda. '
                            . 'Use a ficha de vacinação do AgroAmigo — ela já guarda esses campos.'],
            ],
            'numeros' => [
                ['Brucelose (B19) — idade',        'entre 3 e 8 meses, só fêmeas, dose única'],
                ['Clostridioses — 1ª dose',        'a partir de 4 meses de idade'],
                ['Clostridioses — reforço',        '30 a 60 dias após a 1ª dose, depois anual'],
                ['Intervalo entre vacinas diferentes', 'mínimo 15 dias'],
                ['Conservação da vacina',          'entre 2°C e 8°C, sem congelar'],
                ['Vacina fora da geladeira',       'perde efeito — descarte, não aplique'],
            ],
            'alerta' => [
                'Vários animais adoecerem ou morrerem em poucos dias',
                'Animal com salivação, dificuldade de engolir ou agressividade fora do normal (suspeita de raiva — não manipule sem proteção)',
                'Aborto em mais de uma vaca na mesma temporada',
                'Ferida na boca, no casco ou entre os dedos, com salivação e manqueira — comunique IMEDIATAMENTE à AGED-MA, é obrigatório por lei',
            ],
            'fonte' => [
                ['texto' => 'MAPA — PNCEBT, medidas sanitárias',
                 'url'   => 'https://www.gov.br/agricultura/pt-br/assuntos/sanidade-animal-e-vegetal/saude-animal/programas-de-saude-animal/pncebt'],
                ['texto' => 'AGED-MA — Agência Estadual de Defesa Agropecuária do Maranhão',
                 'url'   => 'https://www.aged.ma.gov.br/'],
            ],
            'revisado' => 'agosto/2026',
        ],

        // ─────────────────────────────────────────────────────
        [
            'icone'  => '🌿',
            'titulo' => 'Nutrição e Pastagem',
            'porque' => 'No Maranhão o gado engorda de janeiro a junho e emagrece de julho a '
                      . 'dezembro. Quem não se prepara para a seca perde no segundo semestre '
                      . 'tudo o que ganhou no primeiro. Planejar a seca em março, quando o '
                      . 'capim ainda está sobrando, é o que separa quem cresce de quem só repõe.',
            'passos' => [
                ['acao'    => 'Água limpa e por perto, sempre',
                 'detalhe' => 'É o nutriente mais importante e o mais esquecido. Boi que anda '
                            . 'muito para beber gasta energia que era para virar carne. '
                            . 'Bebedouro a mais de 800 m do pasto já reduz o ganho.'],
                ['acao'    => 'Sal mineral o ano todo, não só na seca',
                 'detalhe' => 'Cocho coberto, protegido da chuva, e sal sempre disponível. '
                            . 'Se acabar e ficar dias vazio, o animal come demais quando volta '
                            . 'e desperdiça. Use mineral próprio para a estação: o de seca tem '
                            . 'mais fósforo, o das águas é diferente.'],
                ['acao'    => 'Divida o pasto e faça rodízio',
                 'detalhe' => 'Capim comido sem descanso morre e vira solo pelado, aí entra '
                            . 'invasora. Tire o gado quando o capim chegar na metade da altura '
                            . 'inicial e só volte quando rebrotar.'],
                ['acao'    => 'Planeje a seca em março, não em agosto',
                 'detalhe' => 'Silagem, feno, cana ou capim-elefante têm que ser preparados '
                            . 'enquanto ainda tem chuva. Conta simples: quantos animais × '
                            . 'quantos meses de seca = quanto guardar.'],
                ['acao'    => 'Ajuste a lotação à capacidade do pasto',
                 'detalhe' => 'Colocar animal demais é o erro mais caro e mais comum. '
                            . 'Todos emagrecem juntos e o pasto degrada.'],
            ],
            'numeros' => [
                ['Consumo de água por dia (adulto)', '40 a 60 litros, mais no calor'],
                ['Consumo de sal mineral',           '60 a 100 g por animal/dia'],
                ['Consumo de matéria seca',          '2% a 3% do peso vivo por dia'],
                ['Distância máxima até a água',      'até 800 m dentro do piquete'],
                ['Ganho a pasto bem manejado',       '400 a 600 g/dia'],
                ['Período de seca no Maranhão',      'julho a dezembro — planeje 6 meses'],
            ],
            'alerta' => [
                'Rebanho inteiro perdendo peso mesmo com pasto disponível',
                'Animais comendo terra, osso ou madeira (falta de mineral)',
                'Diarreia persistente em vários animais depois de troca de pasto',
                'Capim que não rebrota mais depois do pastejo',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Gado de Corte',
                 'url'   => 'https://www.embrapa.br/gado-de-corte'],
            ],
            'revisado' => 'agosto/2026',
        ],

        // ─────────────────────────────────────────────────────
        [
            'icone'  => '🏠',
            'titulo' => 'Ambiência e Conforto Térmico',
            'porque' => 'Calor derruba a produção antes de o animal parecer doente. Vaca com '
                      . 'calor come menos, dá menos leite e deixa de emprenhar — e o produtor '
                      . 'costuma culpar a ração ou o touro. Zebuíno aguenta bem mais que '
                      . 'europeu, mas mesmo o Nelore tem limite.',
            'passos' => [
                ['acao'    => 'Sombra é investimento, não luxo',
                 'detalhe' => 'Árvore no pasto ou sombrite. Sem sombra, o animal para de comer '
                            . 'no horário mais quente e passa o dia em pé se abanando.'],
                ['acao'    => 'Trabalhe o gado de manhã cedo ou no fim da tarde',
                 'detalhe' => 'Vacinar, pesar, apartar ou transportar no calor do meio-dia '
                            . 'soma estresse de manejo com estresse térmico. É quando morre '
                            . 'animal no curral.'],
                ['acao'    => 'Observe a respiração — é o termômetro do rebanho',
                 'detalhe' => 'Animal ofegante, de boca aberta e babando já passou do ponto. '
                            . 'Conte as respirações por minuto olhando o flanco.'],
                ['acao'    => 'Prefira genética adaptada ao trópico',
                 'detalhe' => 'Nelore e Gir transpiram mais e têm pelo curto e claro, que '
                            . 'reflete o sol. Cruzamento com europeu ganha produção mas perde '
                            . 'rusticidade — precisa de mais sombra e água.'],
            ],
            'numeros' => [
                ['Respiração normal (adulto, repouso)', '20 a 40 movimentos por minuto'],
                ['Sinal de estresse térmico',           'acima de 60 por minuto, boca aberta'],
                ['Temperatura corporal normal',         '38,0°C a 39,0°C'],
                ['Sombra recomendada',                  '3 a 5 m² por animal adulto'],
                ['Faixa de conforto do zebuíno',        'até cerca de 35°C com sombra e água'],
            ],
            'alerta' => [
                'Animal de boca aberta, babando e sem querer levantar',
                'Vacas parando de dar leite de repente em semana de calor forte',
                'Queda de prenhez sem mudança de touro ou de manejo',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Gado de Corte — ambiência e bem-estar',
                 'url'   => 'https://www.embrapa.br/gado-de-corte'],
            ],
            'revisado' => 'agosto/2026',
        ],

        // ─────────────────────────────────────────────────────
        [
            'icone'  => '🤝',
            'titulo' => 'Manejo e Reprodução',
            'porque' => 'A conta da pecuária se fecha no bezerro. Vaca que não emprenha come o '
                      . 'ano inteiro e não entrega nada. E boa parte das falhas de reprodução '
                      . 'não é do touro nem da vaca: é de manejo — vaca magra não emprenha, e '
                      . 'gado estressado no curral perde peso e machuca.',
            'passos' => [
                ['acao'    => 'Trabalhe o gado com calma e sem grito',
                 'detalhe' => 'Boi apressado, com cachorro e berrante, perde peso, escorrega e '
                            . 'machuca. Manejo racional dá menos trabalho e menos prejuízo.'],
                ['acao'    => 'Pese e avalie a condição corporal antes da estação de monta',
                 'detalhe' => 'Vaca magra não emprenha, por melhor que seja o touro. Olhe as '
                            . 'costelas e a garupa: se dá para contar costela de longe, ela '
                            . 'precisa recuperar antes.'],
                ['acao'    => 'Concentre a monta na época das chuvas',
                 'detalhe' => 'Estação de monta de 3 a 4 meses no período de capim bom faz os '
                            . 'bezerros nascerem todos juntos, o que facilita vacinação, '
                            . 'desmama e venda em lote.'],
                ['acao'    => 'Examine o touro antes da estação',
                 'detalhe' => 'Um touro com problema deixa dezenas de vacas vazias e você só '
                            . 'descobre um ano depois. Exame andrológico com veterinário.'],
                ['acao'    => 'Cuide do umbigo do bezerro nas primeiras horas',
                 'detalhe' => 'Desinfete e garanta que ele mamou o colostro. É o que define '
                            . 'a imunidade dele nos primeiros meses.'],
            ],
            'numeros' => [
                ['Idade ao primeiro parto (bem manejado)', '24 a 36 meses'],
                ['Estação de monta',                       '3 a 4 meses, na época das chuvas'],
                ['Relação touro : vacas (monta natural)',  '1 touro para 25 a 30 vacas'],
                ['Gestação da vaca',                       'cerca de 285 dias (9 meses e meio)'],
                ['Desmama',                                'entre 7 e 8 meses'],
                ['Colostro',                               'nas primeiras 6 horas de vida'],
            ],
            'alerta' => [
                'Muitas vacas vazias no fim da estação de monta',
                'Bezerro que não mamou nas primeiras horas ou nasceu fraco',
                'Parto que passa de 2 horas de esforço sem sair o bezerro',
                'Retenção de placenta por mais de 12 horas após o parto',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Gado de Corte — reprodução e manejo',
                 'url'   => 'https://www.embrapa.br/gado-de-corte'],
            ],
            'revisado' => 'agosto/2026',
        ],

        // ─────────────────────────────────────────────────────
        [
            'icone'  => '🛡️',
            'titulo' => 'Biosseguridade e Controle de Parasitas',
            'porque' => 'Quase toda doença nova entra na fazenda de duas formas: animal '
                      . 'comprado sem quarentena ou carrapato. E o erro mais caro no controle '
                      . 'de parasita é dosar todo mundo no mesmo dia, sempre com o mesmo '
                      . 'produto — é assim que o carrapato e o verme ficam resistentes e o '
                      . 'remédio para de funcionar na sua fazenda.',
            'passos' => [
                ['acao'    => 'Todo animal que chega fica em quarentena',
                 'detalhe' => 'Separado do rebanho por 21 a 30 dias, em pasto próprio. É o '
                            . 'passo que mais evita doença nova — e o mais pulado.'],
                ['acao'    => 'Rode o princípio ativo do carrapaticida',
                 'detalhe' => 'Usar o mesmo produto sempre cria carrapato resistente. Troque '
                            . 'de grupo químico e faça o teste de eficácia com o veterinário '
                            . 'antes de escolher.'],
                ['acao'    => 'Vermifugue pelo exame, não pelo calendário',
                 'detalhe' => 'Exame de fezes (OPG) mostra quem realmente precisa. Dosar todo '
                            . 'o rebanho sem necessidade gasta dinheiro e cria resistência.'],
                ['acao'    => 'Enterre ou queime animal morto no mesmo dia',
                 'detalhe' => 'Carcaça a céu aberto espalha clostridiose e atrai urubu e '
                            . 'cachorro, que levam a contaminação para outros pastos.'],
                ['acao'    => 'Controle quem entra: veículo, pessoa e animal',
                 'detalhe' => 'Caminhão de gado e visitante de outra fazenda trazem doença na '
                            . 'bota e no pneu. Um pedilúvio na porteira já ajuda muito.'],
            ],
            'numeros' => [
                ['Quarentena de animal novo',       '21 a 30 dias, isolado'],
                ['Troca de grupo químico',          'a cada 2 a 3 aplicações, com orientação'],
                ['Exame de fezes (OPG)',            'antes de vermifugar o lote'],
                ['Destino de carcaça',              'enterrar ou incinerar no mesmo dia'],
                ['Pior época para carrapato no MA', 'período chuvoso, janeiro a junho'],
            ],
            'alerta' => [
                'Infestação de carrapato que não cede depois do banho',
                'Animais pálidos (mucosa branca), fracos, com papeira embaixo do queixo',
                'Vários animais com diarreia e emagrecendo apesar de pasto bom',
                'Qualquer morte súbita sem causa aparente — não abra a carcaça sozinho',
            ],
            'fonte' => [
                ['texto' => 'Embrapa Gado de Corte — sanidade',
                 'url'   => 'https://www.embrapa.br/gado-de-corte'],
                ['texto' => 'AGED-MA',
                 'url'   => 'https://www.aged.ma.gov.br/'],
            ],
            'revisado' => 'agosto/2026',
        ],
    ],
];

require 'includes/header.php';
require 'includes/animal_page.php';
require 'includes/footer.php';
?>
