<?php
// Toda página do gestao/ deve chamar require_admin() antes de incluir este layout.
// Variáveis esperadas: $g_pagina (string), $g_titulo (string)
$g_pagina = $g_pagina ?? '';
$g_titulo = $g_titulo ?? 'Gestão';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($g_titulo) ?> — AgroAmigo Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/gestao.css">
</head>
<body>

<!-- Overlay mobile para fechar sidebar -->
<div class="g-sidebar-overlay" id="g-sidebar-overlay"></div>

<!-- SIDEBAR -->
<aside class="g-sidebar">
    <div class="g-brand">
        🌱 Agro<strong>Amigo</strong>
        <small>Painel de Gestão</small>
    </div>

    <nav class="g-nav">
        <div class="g-nav-label">Principal</div>
        <a href="index.php"    class="g-nav-link <?= $g_pagina === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="usuarios.php" class="g-nav-link <?= $g_pagina === 'usuarios' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Usuários
        </a>
        <a href="animais.php"  class="g-nav-link <?= $g_pagina === 'animais' ? 'active' : '' ?>">
            <i class="bi bi-heart-pulse-fill"></i> Animais
        </a>

        <div class="g-nav-divider"></div>
        <div class="g-nav-label">Monitoramento</div>
        <a href="logs.php?aba=acesso"    class="g-nav-link <?= ($g_pagina === 'logs' && ($_GET['aba'] ?? '') === 'acesso')   ? 'active' : '' ?>">
            <i class="bi bi-shield-lock-fill"></i> Logs de Acesso
        </a>
        <a href="logs.php?aba=atividade" class="g-nav-link <?= ($g_pagina === 'logs' && ($_GET['aba'] ?? '') === 'atividade') ? 'active' : '' ?>">
            <i class="bi bi-activity"></i> Logs de Atividade
        </a>
        <a href="logs.php?aba=erros"     class="g-nav-link <?= ($g_pagina === 'logs' && ($_GET['aba'] ?? '') === 'erros')     ? 'active' : '' ?>">
            <i class="bi bi-bug-fill"></i> Erros
        </a>

        <div class="g-nav-divider"></div>
        <a href="../index.php" class="g-nav-link" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> Ver site público
        </a>
    </nav>

    <div class="g-user-bar">
        <div class="g-user-name"><?= h($admin['nome']) ?></div>
        <div class="g-user-role">Administrador</div>
        <a href="../logout.php" class="g-logout">
            <i class="bi bi-box-arrow-left"></i> Sair
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="g-main">
    <div class="g-topbar">
        <div style="display:flex;align-items:center;gap:10px">
            <button class="g-mob-toggle" id="g-mob-toggle" aria-label="Abrir menu">
                <i class="bi bi-list"></i>
            </button>
            <div class="g-page-title"><?= h($g_titulo) ?></div>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <div class="g-refresh-badge" id="g-refresh-badge" title="Atualização automática">
                <span class="g-refresh-dot"></span>
                <span id="g-refresh-label">Atualiza em <strong id="g-refresh-count">30</strong>s</span>
                <button class="g-refresh-btn" id="g-refresh-now" title="Atualizar agora">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            <div style="font-size:12px;color:#64748b;white-space:nowrap">
                <?= date('d/m/Y H:i') ?>
            </div>
        </div>
    </div>
    <div class="g-content">
