<?php
declare(strict_types=1);
require_once 'includes/auth.php';
$usuario = require_login('index.php');

$pagina        = 'home';
$titulo_pagina = 'Início';
require 'includes/header.php';
$_idx_user = $usuario;
?>

<!-- =========================================================
     HERO — fundo BRANCO (cores invertidas)
========================================================= -->
<section class="aa-hero">
    <div class="container position-relative">
        <div class="row align-items-center g-5 py-5">

            <!-- Texto -->
            <div class="col-lg-6">
                <span class="aa-hero-badge mb-3 d-inline-block">
                    Projeto ATERPEC — Verde Conecta
                </span>
                <h1 class="aa-hero-title">
                    Assistência Técnica<br>
                    <span class="aa-highlight">na Palma da Mão</span>
                </h1>
                <p class="aa-hero-desc mt-3 mb-4">
                    Informações práticas sobre criação animal para pequenos produtores
                    do Maranhão. Acesse pelo site ou pelo nosso chatbot no WhatsApp.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="contato.php" class="btn aa-btn-primary aa-btn-lg">
                        <i class="bi bi-whatsapp me-2"></i> Acessar Chatbot
                    </a>
                    <a href="bovinos.php" class="btn aa-btn-outline aa-btn-lg">
                        <i class="bi bi-book me-2"></i> Ver Conteúdo
                    </a>
                </div>

                <!-- CTA de conta — muda conforme estado de login -->
                <?php if ($_idx_user): ?>
                <a href="minha-conta.php" class="aa-hero-account-cta">
                    <i class="bi bi-grid-fill"></i>
                    Acessar painel de <?= h(explode(' ', $_idx_user['nome'])[0]) ?>
                    <i class="bi bi-arrow-right"></i>
                </a>
                <?php else: ?>
                <div class="aa-hero-account-box">
                    <span class="aa-hero-account-label">Produtor? Salve suas fichas online</span>
                    <div class="d-flex gap-2">
                        <a href="cadastro.php" class="btn aa-btn-account-create">
                            <i class="bi bi-person-plus me-1"></i> Criar conta grátis
                        </a>
                        <a href="login.php" class="btn aa-btn-account-login">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="d-flex flex-wrap gap-4">
                    <?php
                    $stats = [
                        ['3%',  'das propriedades\nrecebem ATER hoje'],
                        ['6',   'espécies animais\ncobertos'],
                        ['5',   'tópicos técnicos\npor espécie'],
                    ];
                    foreach ($stats as $i => $s): ?>
                        <?php if ($i > 0): ?><div class="aa-stat-divider"></div><?php endif; ?>
                        <div>
                            <div class="aa-stat-value"><?= $s[0] ?></div>
                            <div class="aa-stat-label"><?= nl2br($s[1]) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Visual (lado direito) -->
            <div class="col-lg-6 d-none d-lg-flex justify-content-center">
                <div class="aa-hero-visual">
                    <div class="aa-visual-circle"></div>
                    <div class="aa-visual-emoji">🌾</div>

                    <div class="aa-float-card aa-float-top">
                        <span class="aa-fc-icon">🤖</span>
                        <div>
                            <div class="aa-fc-title">Chatbot Ativo</div>
                            <div class="aa-fc-value">Tire dúvidas pelo WhatsApp</div>
                        </div>
                    </div>

                    <div class="aa-float-card aa-float-bottom">
                        <span class="aa-fc-icon">📋</span>
                        <div>
                            <div class="aa-fc-title">Fichas disponíveis</div>
                            <div class="aa-fc-value">Zootécnica · Vacinação · Mortalidade</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================
     SOBRE O PROJETO
========================================================= -->
<section class="aa-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <span class="aa-section-badge">Por que existe</span>
                <h2 class="aa-section-title mt-2 mb-3">
                    O Desafio da<br>
                    <span class="aa-highlight-section">Extensão Rural</span>
                </h2>
                <p class="aa-section-desc mb-4">
                    Apenas <strong class="text-white">3% das propriedades rurais</strong> do Maranhão
                    recebem algum tipo de Assistência Técnica e Extensão Rural (ATER).
                    A falta de técnicos e infraestrutura limita o alcance da informação.
                </p>
                <p class="aa-section-desc">
                    O projeto <strong class="text-white">ATERPEC</strong> usa tecnologia digital para
                    levar orientações práticas de ambiência, vacinação, nutrição, manejo
                    e biosseguridade diretamente ao produtor — pelo celular.
                </p>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <?php
                    $pilares = [
                        ['🏠', 'Ambiência',      'Espaço, temperatura e estrutura ideal para cada animal.'],
                        ['💉', 'Vacinação',       'Calendário vacinal adequado à realidade do Maranhão.'],
                        ['🌿', 'Nutrição',        'Alimentação balanceada e suplementação mineral.'],
                        ['🤝', 'Manejo',          'Reprodução, desmame, pesagem e rotinas de cuidado.'],
                        ['🛡️', 'Biosseguridade', 'Prevenção de doenças e controle sanitário da criação.'],
                        ['📋', 'Fichas',          'Controle zootécnico, vacinação e mortalidade.'],
                    ];
                    foreach ($pilares as $p): ?>
                    <div class="col-md-4">
                        <div class="aa-pilar-card">
                            <div class="aa-pilar-icon"><?= $p[0] ?></div>
                            <div class="aa-pilar-nome"><?= $p[1] ?></div>
                            <div class="aa-pilar-desc"><?= $p[2] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     ESCOLHA SEU ANIMAL
========================================================= -->
<section class="aa-section-alt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="aa-section-badge">Conteúdo por Espécie</span>
            <h2 class="aa-section-title mt-2">
                Escolha o <span class="aa-highlight-section">Animal</span>
            </h2>
            <p class="aa-section-desc">
                Cada espécie tem sua própria página com raças, cuidados e orientações técnicas
            </p>
        </div>

        <?php
        $animais_home = [
            ['bovinos.php',  'Bovinos',  'Nelore · Girolando', 'https://images.unsplash.com/photo-1588152850700-c82ecb8ba9b1?w=400&q=70&auto=format&fit=crop&h=120'],
            ['aves.php',     'Aves',     'Caipira · ISA Brown', 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=400&q=70&auto=format&fit=crop&h=120'],
            ['suinos.php',   'Suínos',   'Large White · Landrace', 'https://images.unsplash.com/photo-1587213128862-80345e23a71a?w=400&q=70&auto=format&fit=crop&h=120'],
            ['caprinos.php', 'Caprinos', 'Anglo-nubiano · Boer', 'https://images.unsplash.com/photo-1560819400-434c188f63ef?w=400&q=70&auto=format&fit=crop&h=120'],
            ['ovinos.php',   'Ovinos',   'Santa Inês · Dorper', 'https://images.unsplash.com/photo-1494079218307-7fa091ab4df2?w=400&q=70&auto=format&fit=crop&h=120'],
            ['peixes.php',   'Peixes',   'Tilápia · Tambaqui', 'https://images.unsplash.com/photo-1628859742240-269783f56d17?w=400&q=70&auto=format&fit=crop&h=120'],
        ];
        ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($animais_home as [$href, $nome, $racas, $img]): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= $href ?>" class="aa-animal-card">
                    <img src="<?= $img ?>" alt="<?= $nome ?>" class="aa-animal-card-img" loading="lazy">
                    <div class="aa-animal-card-body">
                        <div class="aa-animal-name"><?= $nome ?></div>
                        <div class="aa-animal-sub"><?= $racas ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =========================================================
     COMO FUNCIONA
========================================================= -->
<section class="aa-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="aa-section-badge">Passo a passo</span>
            <h2 class="aa-section-title mt-2">Como <span class="aa-highlight-section">Funciona</span></h2>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $passos = [
                ['1', '📱', 'Acesse pelo celular', 'Entre no site ou abra o chatbot pelo WhatsApp sem precisar instalar nada.'],
                ['2', '🐄', 'Escolha seu animal', 'Selecione a espécie que você cria e navegue pelos tópicos de interesse.'],
                ['3', '💬', 'Tire suas dúvidas', 'O chatbot responde na hora. Para casos complexos, conectamos com um técnico.'],
                ['4', '📋', 'Registre e controle', 'Use as fichas para acompanhar sua produção com organização.'],
            ];
            foreach ($passos as $p): ?>
            <div class="col-md-6 col-lg-3">
                <div class="aa-passo-card">
                    <div class="aa-passo-num"><?= $p[0] ?></div>
                    <div class="aa-passo-icon"><?= $p[1] ?></div>
                    <div class="aa-passo-titulo"><?= $p[2] ?></div>
                    <div class="aa-passo-desc"><?= $p[3] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="contato.php" class="btn aa-btn-section-primary aa-btn-lg me-3">
                <i class="bi bi-whatsapp me-2"></i> Iniciar pelo WhatsApp
            </a>
            <a href="fichas.php" class="btn aa-btn-section-outline aa-btn-lg">
                <i class="bi bi-file-earmark-text me-2"></i> Ver Fichas
            </a>
        </div>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
