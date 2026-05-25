<?php
declare(strict_types=1);
require_once '../includes/auth.php';
$admin = require_admin();

$pdo = db();

// ── Estatísticas gerais ──────────────────────────────────
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM usuarios)                                         AS total_usuarios,
        (SELECT COUNT(*) FROM usuarios WHERE status = 'ativo')                  AS usuarios_ativos,
        (SELECT COUNT(*) FROM usuarios WHERE status = 'suspenso')               AS usuarios_suspensos,
        (SELECT COUNT(*) FROM animais)                                           AS total_animais,
        (SELECT COUNT(*) FROM animais WHERE status = 'ativo')                   AS animais_ativos,
        (SELECT COUNT(*) FROM propriedades)                                     AS total_props,
        (SELECT COUNT(*) FROM pesagens)                                         AS total_pesagens,
        (SELECT COUNT(*) FROM vacinacoes)                                       AS total_vacinas,
        (SELECT COUNT(*) FROM logs_acesso WHERE acao = 'login_ok'
            AND created_at >= NOW() - INTERVAL '24 hours')                      AS logins_hoje,
        (SELECT COUNT(*) FROM logs_acesso WHERE acao = 'login_falhou'
            AND created_at >= NOW() - INTERVAL '24 hours')                      AS falhas_hoje,
        (SELECT COUNT(*) FROM logs_erros
            WHERE created_at >= NOW() - INTERVAL '24 hours')                    AS erros_hoje
")->fetch();

// ── Últimos acessos ──────────────────────────────────────
$ultimos_acessos = $pdo->query("
    SELECT la.*, u.nome
    FROM logs_acesso la
    LEFT JOIN usuarios u ON u.id = la.usuario_id
    ORDER BY la.created_at DESC
    LIMIT 10
")->fetchAll();

// ── Últimos usuários cadastrados ─────────────────────────
$novos_usuarios = $pdo->query("
    SELECT id, nome, email, role, status, created_at
    FROM usuarios
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

$g_pagina = 'dashboard';
$g_titulo = 'Dashboard';
require '_layout.php';
?>

<!-- Stats principais -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="g-stat-card">
            <div class="g-stat-icon green">👥</div>
            <div>
                <div class="g-stat-val"><?= (int)$stats['total_usuarios'] ?></div>
                <div class="g-stat-label">Usuários cadastrados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="g-stat-card">
            <div class="g-stat-icon green">🐄</div>
            <div>
                <div class="g-stat-val"><?= (int)$stats['animais_ativos'] ?></div>
                <div class="g-stat-label">Animais ativos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="g-stat-card">
            <div class="g-stat-icon blue">🏡</div>
            <div>
                <div class="g-stat-val"><?= (int)$stats['total_props'] ?></div>
                <div class="g-stat-label">Propriedades</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="g-stat-card">
            <div class="g-stat-icon amber">⚖️</div>
            <div>
                <div class="g-stat-val"><?= (int)$stats['total_pesagens'] ?></div>
                <div class="g-stat-label">Pesagens registradas</div>
            </div>
        </div>
    </div>
</div>

<!-- Stats de segurança (últimas 24h) -->
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="g-stat-card">
            <div class="g-stat-icon green">✅</div>
            <div>
                <div class="g-stat-val"><?= (int)$stats['logins_hoje'] ?></div>
                <div class="g-stat-label">Logins (24h)</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="g-stat-card">
            <div class="g-stat-icon red">🔒</div>
            <div>
                <div class="g-stat-val"><?= (int)$stats['falhas_hoje'] ?></div>
                <div class="g-stat-label">Tentativas falhas (24h)</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="g-stat-card">
            <div class="g-stat-icon red">🐛</div>
            <div>
                <div class="g-stat-val"><?= (int)$stats['erros_hoje'] ?></div>
                <div class="g-stat-label">Erros (24h)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Últimos acessos -->
    <div class="col-lg-7">
        <div class="g-card">
            <div class="g-card-head">
                <div class="g-card-title">🔐 Últimos Acessos</div>
                <a href="logs.php?aba=acesso" style="font-size:12px;color:#16a34a;font-weight:600;">Ver todos →</a>
            </div>
            <?php if (!$ultimos_acessos): ?>
            <div class="g-empty">Nenhum registro.</div>
            <?php else: ?>
            <table class="g-table">
                <thead>
                    <tr>
                        <th>Usuário / E-mail</th>
                        <th>Ação</th>
                        <th>IP</th>
                        <th>Quando</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimos_acessos as $la): ?>
                    <tr>
                        <td>
                            <?php if ($la['nome']): ?>
                                <div style="font-weight:600"><?= h($la['nome']) ?></div>
                                <div style="font-size:11px;color:#94a3b8"><?= h($la['email_tentado'] ?? '') ?></div>
                            <?php else: ?>
                                <span style="color:#94a3b8"><?= h($la['email_tentado'] ?? '—') ?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="g-badge <?= h($la['acao']) ?>"><?= h($la['acao']) ?></span></td>
                        <td style="font-family:monospace;font-size:12px"><?= h($la['ip'] ?? '—') ?></td>
                        <td style="font-size:11px;color:#64748b;white-space:nowrap">
                            <?= date('d/m H:i', strtotime($la['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Novos usuários -->
    <div class="col-lg-5">
        <div class="g-card">
            <div class="g-card-head">
                <div class="g-card-title">👤 Usuários Recentes</div>
                <a href="usuarios.php" style="font-size:12px;color:#16a34a;font-weight:600;">Ver todos →</a>
            </div>
            <?php if (!$novos_usuarios): ?>
            <div class="g-empty">Nenhum usuário.</div>
            <?php else: ?>
            <table class="g-table">
                <thead>
                    <tr><th>Nome / E-mail</th><th>Role</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($novos_usuarios as $u): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600"><?= h($u['nome']) ?></div>
                            <div style="font-size:11px;color:#94a3b8"><?= h($u['email']) ?></div>
                        </td>
                        <td><span class="g-badge <?= h($u['role']) ?>"><?= h($u['role']) ?></span></td>
                        <td><span class="g-badge <?= h($u['status']) ?>"><?= h($u['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require '_layout_close.php'; ?>
