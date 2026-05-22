<?php
// Vars esperadas: $pagina (string), $titulo_pagina (string)
$pagina       = $pagina       ?? 'index';
$titulo_pagina = $titulo_pagina ?? 'AgroAmigo';
$animais_pages = ['bovinos','aves','suinos','caprinos','ovinos','peixes'];
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

            </ul>

            <div class="d-flex gap-2 mt-3 mt-lg-0">
                <a href="contato.php" class="btn aa-btn-nav-primary">
                    <i class="bi bi-whatsapp me-1"></i> Falar com Técnico
                </a>
            </div>
        </div>

    </div>
</nav>
