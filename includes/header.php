<?php
// Vars esperadas: $pagina (string), $titulo_pagina (string)
$pagina        = $pagina        ?? 'index';
$titulo_pagina = $titulo_pagina ?? 'AgroAmigo';
$animais_pages = ['bovinos','aves','suinos','caprinos','ovinos','peixes'];

// Estado de auth para a navbar
require_once __DIR__ . '/auth.php';
session_init();
$_nav_user = usuario_logado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?> — AgroAmigo ATERPEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg aa-navbar fixed-top" id="mainNav">
    <div class="container">

        <a class="navbar-brand aa-logo" href="index.php">
            <span>🌱</span> Agro<strong>Amigo</strong>
        </a>

        <button class="navbar-toggler aa-toggler border-0 shadow-none"
                type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-1">

                <li class="nav-item">
                    <a class="nav-link aa-nav-link <?= $pagina === 'index' ? 'active' : '' ?>"
                       href="index.php">Início</a>
                </li>

                <!-- Dropdown Animais -->
                <li class="nav-item dropdown">
                    <a class="nav-link aa-nav-link dropdown-toggle <?= in_array($pagina, $animais_pages) ? 'active' : '' ?>"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Animais
                    </a>
                    <ul class="dropdown-menu aa-dropdown">
                        <?php
                        $links_animais = [
                            'bovinos'  => ['🐄', 'Bovinos'],
                            'aves'     => ['🐔', 'Aves'],
                            'suinos'   => ['🐷', 'Suínos'],
                            'caprinos' => ['🐐', 'Caprinos'],
                            'ovinos'   => ['🐑', 'Ovinos'],
                            'peixes'   => ['🐟', 'Peixes'],
                        ];
                        foreach ($links_animais as $key => [$emoji, $label]): ?>
                            <li>
                                <a class="dropdown-item aa-dropdown-item <?= $pagina === $key ? 'active' : '' ?>"
                                   href="<?= $key ?>.php">
                                    <?= $emoji ?> <?= $label ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link aa-nav-link <?= $pagina === 'fichas' ? 'active' : '' ?>"
                       href="fichas.php">Fichas</a>
                </li>

                <?php if ($_nav_user): ?>
                <li class="nav-item">
                    <a class="nav-link aa-nav-link <?= $pagina === 'conta' ? 'active' : '' ?>"
                       href="minha-conta.php">Minha Conta</a>
                </li>
                <?php endif; ?>

            </ul>

            <div class="d-flex gap-2 mt-3 mt-lg-0 align-items-center">

                <?php if ($_nav_user): ?>
                    <!-- Logado -->
                    <div class="dropdown">
                        <button class="btn aa-btn-user dropdown-toggle d-flex align-items-center gap-2"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span class="d-none d-md-inline"><?= h(explode(' ', $_nav_user['nome'])[0]) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end aa-dropdown">
                            <li class="dropdown-header" style="font-size:11px;color:#9ca3af;padding:6px 16px 2px">
                                <?= h($_nav_user['email']) ?>
                            </li>
                            <li><a class="dropdown-item aa-dropdown-item" href="minha-conta.php">
                                <i class="bi bi-grid me-2"></i>Painel
                            </a></li>
                            <?php if ($_nav_user['role'] === 'admin'): ?>
                            <li><a class="dropdown-item aa-dropdown-item" href="gestao/index.php">
                                <i class="bi bi-shield-lock me-2"></i>Gestão (Admin)
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item aa-dropdown-item" href="logout.php"
                                   onclick="return confirm('Deseja sair?')">
                                <i class="bi bi-box-arrow-right me-2"></i>Sair
                            </a></li>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- Não logado -->
                    <a href="login.php" class="btn aa-btn-nav-secondary">
                        <i class="bi bi-person me-1"></i> Entrar
                    </a>
                    <a href="cadastro.php" class="btn aa-btn-nav-primary">
                        <i class="bi bi-person-plus me-1"></i> Criar Conta
                    </a>
                <?php endif; ?>

                <a href="contato.php" class="btn aa-btn-nav-primary">
                    <i class="bi bi-whatsapp me-1"></i> Falar com Técnico
                </a>

            </div>
        </div>

    </div>
</nav>
