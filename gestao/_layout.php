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
    <style>
        :root{--g800:#166534;--g700:#15803d;--g600:#16a34a;--g100:#dcfce7;--g50:#f0fdf4;--sidebar:240px}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#f1f5f9;color:#1e293b;font-size:14px;display:flex;min-height:100vh}
        a{text-decoration:none;color:inherit}

        /* Sidebar */
        .g-sidebar{width:var(--sidebar);background:var(--g800);color:#fff;display:flex;flex-direction:column;flex-shrink:0;position:fixed;height:100vh;overflow-y:auto;z-index:200}
        .g-brand{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);font-size:17px;font-weight:400}
        .g-brand strong{font-weight:800}
        .g-brand small{display:block;font-size:10px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-top:2px}
        .g-nav{padding:12px 0;flex:1}
        .g-nav-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.4);padding:14px 18px 6px}
        .g-nav-link{display:flex;align-items:center;gap:10px;padding:9px 18px;font-size:13px;font-weight:500;color:rgba(255,255,255,.78);transition:background .15s,color .15s;border-radius:0;cursor:pointer}
        .g-nav-link:hover{background:rgba(255,255,255,.1);color:#fff}
        .g-nav-link.active{background:rgba(255,255,255,.18);color:#fff;font-weight:700}
        .g-nav-link .bi{font-size:15px;flex-shrink:0}
        .g-nav-divider{border-top:1px solid rgba(255,255,255,.1);margin:8px 0}
        .g-user-bar{padding:14px 18px;border-top:1px solid rgba(255,255,255,.12);font-size:12px}
        .g-user-name{font-weight:700;color:#fff;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .g-user-role{color:rgba(255,255,255,.5);font-size:10px;text-transform:uppercase;letter-spacing:.5px}
        .g-logout{display:inline-flex;align-items:center;gap:5px;margin-top:8px;font-size:12px;color:rgba(255,255,255,.6);transition:color .15s}
        .g-logout:hover{color:#fca5a5}

        /* Main */
        .g-main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column;min-height:100vh}
        .g-topbar{background:#fff;border-bottom:1px solid #e2e8f0;padding:14px 28px;display:flex;align-items:center;justify-content:space-between;gap:16px;position:sticky;top:0;z-index:100}
        .g-page-title{font-size:16px;font-weight:700;color:#1e293b}
        .g-content{padding:24px 28px;flex:1}

        /* Cards stats */
        .g-stat-card{background:#fff;border-radius:12px;padding:20px 22px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:16px}
        .g-stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
        .g-stat-icon.green{background:var(--g50)}
        .g-stat-icon.blue{background:#eff6ff}
        .g-stat-icon.amber{background:#fffbeb}
        .g-stat-icon.red{background:#fef2f2}
        .g-stat-val{font-size:24px;font-weight:800;line-height:1}
        .g-stat-label{font-size:11px;color:#64748b;margin-top:3px}

        /* Table */
        .g-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:20px}
        .g-card-head{padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:12px}
        .g-card-title{font-size:14px;font-weight:700;color:#1e293b}
        .g-table{width:100%;border-collapse:collapse;font-size:13px}
        .g-table th{background:#f8fafc;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:10px 16px;text-align:left;border-bottom:1px solid #e2e8f0;white-space:nowrap}
        .g-table td{padding:11px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle;color:#334155}
        .g-table tr:last-child td{border-bottom:none}
        .g-table tr:hover td{background:#f8fafc}
        .g-badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600}
        .g-badge.ativo{background:#dcfce7;color:#166534}
        .g-badge.suspenso{background:#fee2e2;color:#991b1b}
        .g-badge.inativo{background:#f1f5f9;color:#64748b}
        .g-badge.pendente{background:#fef9c3;color:#854d0e}
        .g-badge.admin{background:#ede9fe;color:#6d28d9}
        .g-badge.tecnico{background:#dbeafe;color:#1e40af}
        .g-badge.produtor{background:var(--g50);color:var(--g800)}
        .g-badge.login_ok{background:#dcfce7;color:#166534}
        .g-badge.login_falhou{background:#fee2e2;color:#991b1b}
        .g-badge.logout{background:#f1f5f9;color:#64748b}
        .g-badge.bloqueado{background:#fef3c7;color:#92400e}
        .g-action-btn{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid;cursor:pointer;transition:all .15s;background:transparent;font-family:inherit}
        .g-action-btn.danger{color:#dc2626;border-color:#fca5a5}
        .g-action-btn.danger:hover{background:#fee2e2}
        .g-action-btn.warn{color:#d97706;border-color:#fcd34d}
        .g-action-btn.warn:hover{background:#fffbeb}
        .g-action-btn.success{color:var(--g700);border-color:var(--g200,#bbf7d0)}
        .g-action-btn.success:hover{background:var(--g50)}
        .g-action-btn.info{color:#0369a1;border-color:#bae6fd}
        .g-action-btn.info:hover{background:#eff6ff}
        .g-empty{padding:40px;text-align:center;color:#94a3b8;font-size:13px}
        .g-search{display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:7px 12px}
        .g-search input{border:none;background:none;outline:none;font-size:13px;color:#334155;width:200px;font-family:inherit}
        .g-search input::placeholder{color:#94a3b8}
        .g-pagination{display:flex;align-items:center;gap:6px;padding:14px 20px;border-top:1px solid #f1f5f9}
        .g-page-btn{padding:5px 11px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#334155;cursor:pointer;transition:all .15s}
        .g-page-btn:hover:not(:disabled){background:#f8fafc}
        .g-page-btn.active{background:var(--g700);color:#fff;border-color:var(--g700)}
        .g-page-btn:disabled{opacity:.4;cursor:default}
    </style>
</head>
<body>

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
        <div class="g-page-title"><?= h($g_titulo) ?></div>
        <div style="font-size:12px;color:#64748b;">
            <?= date('d/m/Y H:i') ?>
        </div>
    </div>
    <div class="g-content">
