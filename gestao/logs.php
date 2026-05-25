<?php
declare(strict_types=1);
require_once '../includes/auth.php';
$admin = require_admin();

$pdo   = db();
$aba   = in_array($_GET['aba'] ?? '', ['acesso', 'atividade', 'erros']) ? $_GET['aba'] : 'acesso';
$pag   = max(1, (int)($_GET['p'] ?? 1));
$limit = 30;

// Filtros
$ip_f  = trim($_GET['ip']    ?? '');
$acao_f = trim($_GET['acao'] ?? '');
$uid_f  = trim($_GET['uid']  ?? '');

// ── Acesso ───────────────────────────────────────────────
if ($aba === 'acesso') {
    $where  = '1=1';
    $params = [];
    if ($ip_f)   { $where .= ' AND la.ip    = :ip';   $params['ip']   = $ip_f; }
    if ($acao_f) { $where .= ' AND la.acao  = :acao'; $params['acao'] = $acao_f; }
    if ($uid_f)  { $where .= ' AND la.usuario_id = :uid'; $params['uid'] = $uid_f; }

    $cnt = $pdo->prepare("SELECT COUNT(*) FROM logs_acesso la WHERE $where");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();
    $pg    = paginar($total, $limit, $pag);

    $stmt = $pdo->prepare("
        SELECT la.*, u.nome
        FROM logs_acesso la
        LEFT JOIN usuarios u ON u.id = la.usuario_id
        WHERE $where
        ORDER BY la.created_at DESC
        LIMIT :lim OFFSET :off
    ");
    $stmt->execute($params + ['lim' => $limit, 'off' => $pg['offset']]);
    $rows = $stmt->fetchAll();
}

// ── Atividade ────────────────────────────────────────────
if ($aba === 'atividade') {
    $where  = '1=1';
    $params = [];
    if ($uid_f) { $where .= ' AND la.usuario_id = :uid'; $params['uid'] = $uid_f; }

    $cnt = $pdo->prepare("SELECT COUNT(*) FROM logs_atividade la WHERE $where");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();
    $pg    = paginar($total, $limit, $pag);

    $stmt = $pdo->prepare("
        SELECT la.*, u.nome, u.email
        FROM logs_atividade la
        LEFT JOIN usuarios u ON u.id = la.usuario_id
        WHERE $where
        ORDER BY la.created_at DESC
        LIMIT :lim OFFSET :off
    ");
    $stmt->execute($params + ['lim' => $limit, 'off' => $pg['offset']]);
    $rows = $stmt->fetchAll();
}

// ── Erros ────────────────────────────────────────────────
if ($aba === 'erros') {
    $cnt   = $pdo->query("SELECT COUNT(*) FROM logs_erros")->fetchColumn();
    $total = (int)$cnt;
    $pg    = paginar($total, $limit, $pag);

    $stmt  = $pdo->prepare("
        SELECT le.*, u.nome, u.email
        FROM logs_erros le
        LEFT JOIN usuarios u ON u.id = le.usuario_id
        ORDER BY le.created_at DESC
        LIMIT :lim OFFSET :off
    ");
    $stmt->execute(['lim' => $limit, 'off' => $pg['offset']]);
    $rows = $stmt->fetchAll();
}

$g_pagina = 'logs';
$g_titulo = 'Logs de ' . ucfirst($aba);
require '_layout.php';
?>

<!-- Abas -->
<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:2px solid #e2e8f0">
    <?php foreach (['acesso' => '🔐 Acesso', 'atividade' => '📋 Atividade', 'erros' => '🐛 Erros'] as $k => $label): ?>
    <a href="?aba=<?= $k ?>"
       style="padding:10px 18px;font-size:13px;font-weight:600;border-bottom:2px solid <?= $aba === $k ? '#16a34a' : 'transparent' ?>;color:<?= $aba === $k ? '#16a34a' : '#64748b' ?>;margin-bottom:-2px;display:inline-flex;align-items:center;gap:6px">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($aba === 'acesso'): ?>

<!-- Filtros acesso -->
<form method="GET" action="logs.php" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
    <input type="hidden" name="aba" value="acesso">
    <div class="g-search">
        <i class="bi bi-search" style="color:#94a3b8"></i>
        <input type="text" name="ip" value="<?= h($ip_f) ?>" placeholder="Filtrar por IP...">
    </div>
    <select name="acao" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;background:#fff;color:#334155;font-family:inherit">
        <option value="">Todas as ações</option>
        <?php foreach (['login_ok','login_falhou','logout','bloqueado'] as $a): ?>
        <option value="<?= $a ?>" <?= $acao_f === $a ? 'selected' : '' ?>><?= $a ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" style="padding:7px 14px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Filtrar</button>
    <?php if ($ip_f || $acao_f): ?>
    <a href="?aba=acesso" style="padding:7px 14px;border-radius:8px;font-size:13px;color:#dc2626;font-weight:600;border:1px solid #fca5a5">Limpar</a>
    <?php endif; ?>
</form>

<div class="g-card">
    <div class="g-card-head">
        <div class="g-card-title">Logs de Acesso — <?= $total ?> registros</div>
    </div>
    <?php if (!$rows): ?>
    <div class="g-empty">Nenhum registro.</div>
    <?php else: ?>
    <table class="g-table">
        <thead>
            <tr><th>Usuário</th><th>E-mail tentado</th><th>Ação</th><th>IP</th><th>User-Agent</th><th>Quando</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r['nome'] ? h($r['nome']) : '<span style="color:#94a3b8">—</span>' ?></td>
                <td style="font-size:12px"><?= h($r['email_tentado'] ?? '—') ?></td>
                <td><span class="g-badge <?= h($r['acao']) ?>"><?= h($r['acao']) ?></span></td>
                <td style="font-family:monospace;font-size:12px">
                    <a href="?aba=acesso&ip=<?= urlencode($r['ip'] ?? '') ?>" style="color:#16a34a"><?= h($r['ip'] ?? '—') ?></a>
                </td>
                <td style="font-size:11px;color:#94a3b8;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= h($r['user_agent'] ?? '') ?>">
                    <?= h(mb_substr($r['user_agent'] ?? '', 0, 60)) ?>
                </td>
                <td style="font-size:11px;color:#64748b;white-space:nowrap">
                    <?= date('d/m/Y H:i:s', strtotime($r['created_at'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if ($pg['total_paginas'] > 1): ?>
    <div class="g-pagination">
        <?php for ($i = 1; $i <= $pg['total_paginas']; $i++): ?>
        <a href="?aba=acesso&p=<?= $i ?>&ip=<?= urlencode($ip_f) ?>&acao=<?= urlencode($acao_f) ?>"
           class="g-page-btn <?= $i === $pg['pagina_atual'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php elseif ($aba === 'atividade'): ?>

<div class="g-card">
    <div class="g-card-head">
        <div class="g-card-title">Logs de Atividade — <?= $total ?> registros</div>
    </div>
    <?php if (!$rows): ?>
    <div class="g-empty">Nenhum registro.</div>
    <?php else: ?>
    <table class="g-table">
        <thead>
            <tr><th>Usuário</th><th>Entidade</th><th>ID</th><th>Ação</th><th>Dados</th><th>IP</th><th>Quando</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td>
                    <div style="font-weight:600"><?= h($r['nome'] ?? '—') ?></div>
                    <div style="font-size:11px;color:#94a3b8"><?= h($r['email'] ?? '') ?></div>
                </td>
                <td><code style="font-size:11px"><?= h($r['entidade']) ?></code></td>
                <td style="font-family:monospace;font-size:10px;color:#94a3b8">
                    <?= $r['entidade_id'] ? mb_substr(h($r['entidade_id']), 0, 8) . '…' : '—' ?>
                </td>
                <td>
                    <?php
                    $cores = ['criar' => '#16a34a', 'editar' => '#d97706', 'excluir' => '#dc2626', 'visualizar' => '#0369a1'];
                    $cor   = $cores[$r['acao']] ?? '#64748b';
                    ?>
                    <span style="font-size:11px;font-weight:700;color:<?= $cor ?>"><?= h($r['acao']) ?></span>
                </td>
                <td>
                    <?php if ($r['dados_depois']): ?>
                    <details style="font-size:11px">
                        <summary style="cursor:pointer;color:#0369a1">ver dados</summary>
                        <pre style="font-size:10px;max-width:200px;overflow:auto;background:#f8fafc;padding:4px;border-radius:4px;margin-top:4px"><?= h(json_encode(json_decode($r['dados_depois']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    </details>
                    <?php else: ?>
                    <span style="color:#94a3b8;font-size:11px">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-family:monospace;font-size:12px"><?= h($r['ip'] ?? '—') ?></td>
                <td style="font-size:11px;color:#64748b;white-space:nowrap">
                    <?= date('d/m/Y H:i:s', strtotime($r['created_at'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($pg['total_paginas'] > 1): ?>
    <div class="g-pagination">
        <?php for ($i = 1; $i <= $pg['total_paginas']; $i++): ?>
        <a href="?aba=atividade&p=<?= $i ?>" class="g-page-btn <?= $i === $pg['pagina_atual'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php else: // erros ?>

<div class="g-card">
    <div class="g-card-head">
        <div class="g-card-title">🐛 Log de Erros — <?= $total ?> registros</div>
    </div>
    <?php if (!$rows): ?>
    <div class="g-empty" style="color:#16a34a">✅ Nenhum erro registrado!</div>
    <?php else: ?>
    <table class="g-table">
        <thead>
            <tr><th>Mensagem</th><th>Arquivo</th><th>URL</th><th>Usuário</th><th>IP</th><th>Quando</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td style="max-width:280px;word-break:break-word;font-size:12px;color:#dc2626"><?= h(mb_substr($r['mensagem'], 0, 200)) ?></td>
                <td style="font-family:monospace;font-size:11px;color:#94a3b8">
                    <?= $r['arquivo'] ? h(basename($r['arquivo'])) . ':' . (int)$r['linha'] : '—' ?>
                </td>
                <td style="font-size:11px;color:#64748b;max-width:160px;overflow:hidden;text-overflow:ellipsis"><?= h(mb_substr($r['url'] ?? '', 0, 60)) ?></td>
                <td style="font-size:12px"><?= h($r['nome'] ?? '—') ?></td>
                <td style="font-family:monospace;font-size:12px"><?= h($r['ip'] ?? '—') ?></td>
                <td style="font-size:11px;color:#64748b;white-space:nowrap">
                    <?= date('d/m/Y H:i:s', strtotime($r['created_at'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($pg['total_paginas'] > 1): ?>
    <div class="g-pagination">
        <?php for ($i = 1; $i <= $pg['total_paginas']; $i++): ?>
        <a href="?aba=erros&p=<?= $i ?>" class="g-page-btn <?= $i === $pg['pagina_atual'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php require '_layout_close.php'; ?>
