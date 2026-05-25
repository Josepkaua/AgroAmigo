<?php
declare(strict_types=1);
require_once '../includes/auth.php';
$admin = require_admin();

$pdo = db();

// ── Ações POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $acao  = $_POST['acao'] ?? '';
    $uid   = $_POST['uid']  ?? '';

    // Não permite agir sobre si mesmo
    if ($uid === $admin['id']) {
        flash('danger', 'Você não pode alterar sua própria conta por aqui.');
        header('Location: usuarios.php');
        exit;
    }

    $alvo = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
    $alvo->execute(['id' => $uid]);
    $alvo = $alvo->fetch();

    if (!$alvo) {
        flash('danger', 'Usuário não encontrado.');
        header('Location: usuarios.php');
        exit;
    }

    switch ($acao) {
        case 'suspender':
            $pdo->prepare("UPDATE usuarios SET status = 'suspenso' WHERE id = :id")
                ->execute(['id' => $uid]);
            log_atividade('usuarios', $uid, 'editar', ['status' => $alvo['status']], ['status' => 'suspenso']);
            flash('success', "Usuário {$alvo['nome']} suspenso.");
            break;

        case 'ativar':
            $pdo->prepare("UPDATE usuarios SET status = 'ativo', tentativas_login = 0, bloqueado_ate = NULL WHERE id = :id")
                ->execute(['id' => $uid]);
            log_atividade('usuarios', $uid, 'editar', ['status' => $alvo['status']], ['status' => 'ativo']);
            flash('success', "Usuário {$alvo['nome']} ativado.");
            break;

        case 'promover_admin':
            $pdo->prepare("UPDATE usuarios SET role = 'admin' WHERE id = :id")
                ->execute(['id' => $uid]);
            log_atividade('usuarios', $uid, 'editar', ['role' => $alvo['role']], ['role' => 'admin']);
            flash('success', "Usuário {$alvo['nome']} promovido a admin.");
            break;

        case 'rebaixar':
            $pdo->prepare("UPDATE usuarios SET role = 'produtor' WHERE id = :id")
                ->execute(['id' => $uid]);
            log_atividade('usuarios', $uid, 'editar', ['role' => $alvo['role']], ['role' => 'produtor']);
            flash('success', "Usuário {$alvo['nome']} rebaixado para produtor.");
            break;

        case 'excluir':
            $pdo->prepare("DELETE FROM usuarios WHERE id = :id")->execute(['id' => $uid]);
            log_atividade('usuarios', $uid, 'excluir', $alvo, null);
            flash('success', "Usuário {$alvo['nome']} excluído.");
            break;

        default:
            flash('danger', 'Ação inválida.');
    }

    header('Location: usuarios.php');
    exit;
}

// ── Listagem ──────────────────────────────────────────────
$busca  = trim($_GET['q'] ?? '');
$pagina = max(1, (int)($_GET['p'] ?? 1));
$limit  = 20;

$where  = '';
$params = [];
if ($busca) {
    $where    = "WHERE nome ILIKE :q OR email ILIKE :q";
    $params['q'] = '%' . $busca . '%';
}

$total = $pdo->prepare("SELECT COUNT(*) FROM usuarios $where");
$total->execute($params);
$total = (int)$total->fetchColumn();

$paginacao = paginar($total, $limit, $pagina);

$stmt = $pdo->prepare("
    SELECT u.*,
           (SELECT COUNT(*) FROM propriedades WHERE usuario_id = u.id) AS total_props,
           (SELECT COUNT(*) FROM animais a JOIN propriedades p ON p.id = a.propriedade_id WHERE p.usuario_id = u.id) AS total_animais
    FROM usuarios u
    $where
    ORDER BY u.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params + ['limit' => $limit, 'offset' => $paginacao['offset']]);
$usuarios = $stmt->fetchAll();

$g_pagina = 'usuarios';
$g_titulo = 'Usuários';
require '_layout.php';
?>

<?php $f = get_flash(); if ($f): ?>
<div class="alert alert-<?= $f['tipo'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4">
    <?= h($f['msg']) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="g-card">
    <div class="g-card-head">
        <div class="g-card-title">
            👥 Usuários
            <span style="font-size:12px;font-weight:400;color:#64748b;margin-left:6px"><?= $total ?> no total</span>
        </div>
        <form method="GET" action="usuarios.php" style="display:flex;gap:8px;align-items:center">
            <div class="g-search">
                <i class="bi bi-search" style="color:#94a3b8"></i>
                <input type="text" name="q" value="<?= h($busca) ?>" placeholder="Buscar por nome ou e-mail...">
            </div>
            <button type="submit" style="padding:8px 14px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
                Buscar
            </button>
        </form>
    </div>

    <?php if (!$usuarios): ?>
    <div class="g-empty">Nenhum usuário encontrado.</div>
    <?php else: ?>
    <table class="g-table">
        <thead>
            <tr>
                <th>Nome / E-mail</th>
                <th>Role</th>
                <th>Status</th>
                <th>Propriedades</th>
                <th>Animais</th>
                <th>Último login</th>
                <th>Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td>
                    <div style="font-weight:600"><?= h($u['nome']) ?></div>
                    <div style="font-size:11px;color:#94a3b8"><?= h($u['email']) ?></div>
                    <?php if ($u['telefone']): ?>
                    <div style="font-size:11px;color:#94a3b8"><?= h($u['telefone']) ?></div>
                    <?php endif; ?>
                </td>
                <td><span class="g-badge <?= h($u['role']) ?>"><?= h($u['role']) ?></span></td>
                <td>
                    <span class="g-badge <?= h($u['status']) ?>"><?= h($u['status']) ?></span>
                    <?php if ($u['bloqueado_ate'] && strtotime($u['bloqueado_ate']) > time()): ?>
                    <br><small style="color:#f59e0b;font-size:10px">bloqueado até <?= date('H:i', strtotime($u['bloqueado_ate'])) ?></small>
                    <?php endif; ?>
                </td>
                <td style="text-align:center"><?= (int)$u['total_props'] ?></td>
                <td style="text-align:center"><?= (int)$u['total_animais'] ?></td>
                <td style="font-size:11px;color:#64748b;white-space:nowrap">
                    <?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : '—' ?>
                </td>
                <td style="font-size:11px;color:#64748b;white-space:nowrap">
                    <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                </td>
                <td>
                    <?php if ($u['id'] !== $admin['id']): ?>
                    <div style="display:flex;gap:4px;flex-wrap:wrap">
                        <?php if ($u['status'] === 'suspenso' || $u['status'] === 'inativo'): ?>
                        <form method="POST" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="acao" value="ativar">
                            <input type="hidden" name="uid"  value="<?= h($u['id']) ?>">
                            <button class="g-action-btn success" type="submit">✅ Ativar</button>
                        </form>
                        <?php elseif ($u['status'] === 'ativo'): ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Suspender este usuário?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="acao" value="suspender">
                            <input type="hidden" name="uid"  value="<?= h($u['id']) ?>">
                            <button class="g-action-btn warn" type="submit">🔒 Suspender</button>
                        </form>
                        <?php endif; ?>

                        <?php if ($u['role'] !== 'admin'): ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Promover a admin?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="acao" value="promover_admin">
                            <input type="hidden" name="uid"  value="<?= h($u['id']) ?>">
                            <button class="g-action-btn info" type="submit">⬆️ Admin</button>
                        </form>
                        <?php else: ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Rebaixar para produtor?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="acao" value="rebaixar">
                            <input type="hidden" name="uid"  value="<?= h($u['id']) ?>">
                            <button class="g-action-btn warn" type="submit">⬇️ Produtor</button>
                        </form>
                        <?php endif; ?>

                        <form method="POST" style="display:inline" onsubmit="return confirm('EXCLUIR este usuário e todos os seus dados? Esta ação é irreversível!')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="uid"  value="<?= h($u['id']) ?>">
                            <button class="g-action-btn danger" type="submit">🗑️</button>
                        </form>
                    </div>
                    <?php else: ?>
                    <span style="font-size:11px;color:#94a3b8">você</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <?php if ($paginacao['total_paginas'] > 1): ?>
    <div class="g-pagination">
        <?php for ($i = 1; $i <= $paginacao['total_paginas']; $i++): ?>
        <a href="?p=<?= $i ?>&q=<?= urlencode($busca) ?>"
           class="g-page-btn <?= $i === $paginacao['pagina_atual'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require '_layout_close.php'; ?>
