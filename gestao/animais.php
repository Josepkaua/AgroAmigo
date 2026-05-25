<?php
declare(strict_types=1);
require_once '../includes/auth.php';
$admin = require_admin();

$pdo    = db();
$busca  = trim($_GET['q']      ?? '');
$status = trim($_GET['status'] ?? '');
$pag    = max(1, (int)($_GET['p'] ?? 1));
$limit  = 25;

$where  = '1=1';
$params = [];
if ($busca)  { $where .= ' AND (a.brinco ILIKE :q OR a.especie ILIKE :q OR a.raca ILIKE :q OR pr.nome ILIKE :q)'; $params['q'] = '%' . $busca . '%'; }
if ($status) { $where .= ' AND a.status = :status'; $params['status'] = $status; }

$cnt = $pdo->prepare("
    SELECT COUNT(*)
    FROM animais a
    JOIN propriedades pr ON pr.id = a.propriedade_id
    WHERE $where
");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pg    = paginar($total, $limit, $pag);

$stmt = $pdo->prepare("
    SELECT a.*, pr.nome AS prop_nome, pr.municipio, pr.uf,
           u.nome AS dono_nome, u.email AS dono_email,
           (SELECT COUNT(*) FROM pesagens WHERE animal_id = a.id) AS total_pesagens,
           (SELECT MAX(data_pesagem) FROM pesagens WHERE animal_id = a.id) AS ultima_pesagem,
           (SELECT peso_kg FROM pesagens WHERE animal_id = a.id ORDER BY data_pesagem DESC LIMIT 1) AS ultimo_peso
    FROM animais a
    JOIN propriedades pr ON pr.id = a.propriedade_id
    JOIN usuarios u ON u.id = pr.usuario_id
    WHERE $where
    ORDER BY a.created_at DESC
    LIMIT :lim OFFSET :off
");
$stmt->execute($params + ['lim' => $limit, 'off' => $pg['offset']]);
$animais = $stmt->fetchAll();

$g_pagina = 'animais';
$g_titulo = 'Animais';
require '_layout.php';
?>

<form method="GET" action="animais.php" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
    <div class="g-search">
        <i class="bi bi-search" style="color:#94a3b8"></i>
        <input type="text" name="q" value="<?= h($busca) ?>" placeholder="Brinco, espécie, raça, propriedade...">
    </div>
    <select name="status" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;background:#fff;color:#334155;font-family:inherit">
        <option value="">Todos os status</option>
        <?php foreach (['ativo','vendido','abatido','morto','transferido'] as $s): ?>
        <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" style="padding:7px 14px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Filtrar</button>
    <?php if ($busca || $status): ?>
    <a href="animais.php" style="padding:7px 14px;border-radius:8px;font-size:13px;color:#dc2626;font-weight:600;border:1px solid #fca5a5">Limpar</a>
    <?php endif; ?>
</form>

<div class="g-card">
    <div class="g-card-head">
        <div class="g-card-title">🐄 Todos os Animais — <?= $total ?> registros</div>
    </div>
    <?php if (!$animais): ?>
    <div class="g-empty">Nenhum animal encontrado.</div>
    <?php else: ?>
    <table class="g-table">
        <thead>
            <tr>
                <th>Brinco</th>
                <th>Espécie / Raça</th>
                <th>Status</th>
                <th>Propriedade</th>
                <th>Dono</th>
                <th>Último peso</th>
                <th>Pesagens</th>
                <th>Cadastro</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($animais as $a): ?>
            <tr>
                <td style="font-weight:700"><?= h($a['brinco'] ?: '—') ?></td>
                <td>
                    <div><?= h($a['especie'] ?: '—') ?></div>
                    <div style="font-size:11px;color:#94a3b8"><?= h($a['raca'] ?: '') ?></div>
                </td>
                <td><span class="g-badge <?= h($a['status']) ?>"><?= h($a['status']) ?></span></td>
                <td>
                    <div style="font-weight:600"><?= h($a['prop_nome']) ?></div>
                    <div style="font-size:11px;color:#94a3b8"><?= h($a['municipio'] ?? '') ?><?= $a['uf'] ? '/' . h($a['uf']) : '' ?></div>
                </td>
                <td>
                    <div><?= h($a['dono_nome']) ?></div>
                    <div style="font-size:11px;color:#94a3b8"><?= h($a['dono_email']) ?></div>
                </td>
                <td>
                    <?php if ($a['ultimo_peso']): ?>
                    <div style="font-weight:700"><?= number_format((float)$a['ultimo_peso'], 1, ',', '.') ?> kg</div>
                    <div style="font-size:11px;color:#94a3b8"><?= date('d/m/Y', strtotime($a['ultima_pesagem'])) ?></div>
                    <?php else: ?>
                    <span style="color:#94a3b8">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center"><?= (int)$a['total_pesagens'] ?></td>
                <td style="font-size:11px;color:#64748b"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($pg['total_paginas'] > 1): ?>
    <div class="g-pagination">
        <?php for ($i = 1; $i <= $pg['total_paginas']; $i++): ?>
        <a href="?p=<?= $i ?>&q=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>"
           class="g-page-btn <?= $i === $pg['pagina_atual'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require '_layout_close.php'; ?>
