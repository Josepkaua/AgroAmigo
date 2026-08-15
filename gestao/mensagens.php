<?php
/**
 * Mensagens recebidas pelo formulário "Falar com Técnico".
 * Antes ficavam só no banco e ninguém via. Agora aparecem aqui, com botão
 * de responder por e-mail e marcação de já respondida.
 */
declare(strict_types=1);
require_once '../includes/auth.php';
require_once '../includes/conteudo.php';
require_once '../includes/envio_admin.php';

$u = require_editor();

// Marcar como arquivada / pendente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id  = $_POST['id'] ?? '';
    $novo = $_POST['status'] ?? '';
    if (preg_match('/^[0-9a-f-]{36}$/i', $id) && in_array($novo, ['pendente','respondida','arquivada'], true)) {
        try {
            db()->prepare("UPDATE mensagens_contato SET status = :s WHERE id = :id")
                ->execute(['s' => $novo, 'id' => $id]);
            flash('success', 'Mensagem marcada como ' . $novo . '.');
        } catch (Throwable $e) {
            log_erro('mensagens: ' . $e->getMessage(), __FILE__, __LINE__);
            flash('erro', 'Não consegui atualizar.');
        }
    }
    header('Location: mensagens.php' . (!empty($_POST['filtro']) ? '?f=' . urlencode($_POST['filtro']) : ''));
    exit;
}

$filtro = $_GET['f'] ?? 'pendente';
if (!in_array($filtro, ['pendente','respondida','arquivada','todas'], true)) $filtro = 'pendente';

try {
    $sql = "SELECT m.*, u.nome AS nome_usuario, u.email AS email_usuario
              FROM mensagens_contato m
              LEFT JOIN usuarios u ON u.id = m.usuario_id";
    if ($filtro !== 'todas') $sql .= " WHERE m.status = :s";
    $sql .= " ORDER BY m.criado_em DESC LIMIT 100";
    $st = db()->prepare($sql);
    $st->execute($filtro !== 'todas' ? ['s' => $filtro] : []);
    $msgs = $st->fetchAll();

    $contagem = [];
    foreach (db()->query("SELECT status, count(*) c FROM mensagens_contato GROUP BY status")->fetchAll() as $r) {
        $contagem[$r['status']] = (int) $r['c'];
    }
} catch (Throwable $e) {
    $msgs = []; $contagem = [];
    $erro_tabela = 'Rode sql/migration_mensagens_contato.sql e sql/migration_email_admin.sql no Supabase.';
}

$g_pagina = 'mensagens';
$g_titulo = 'Mensagens recebidas';
$flash = get_flash();
require '_layout.php';
?>

<div class="g-page-head">
    <div>
        <h1 class="g-page-title">Mensagens recebidas</h1>
        <p class="g-page-sub">
            O que os produtores enviaram pelo "Falar com Técnico" do site.
        </p>
    </div>
    <a href="email.php" class="btn btn-success btn-sm"><i class="bi bi-send"></i> Enviar e-mail</a>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : 'success' ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<?php if (!empty($erro_tabela)): ?>
<div class="alert alert-warning"><?= h($erro_tabela) ?></div>
<?php endif; ?>

<ul class="nav nav-pills gap-2 mb-4 flex-wrap">
    <?php foreach (['pendente'=>'Pendentes','respondida'=>'Respondidas','arquivada'=>'Arquivadas','todas'=>'Todas'] as $k=>$rot): ?>
    <li class="nav-item">
        <a class="nav-link <?= $k === $filtro ? 'active' : '' ?>"
           style="<?= $k === $filtro ? 'background:#166534' : 'background:#f1f5f9;color:#334155' ?>"
           href="mensagens.php?f=<?= $k ?>">
            <?= $rot ?><?= isset($contagem[$k]) ? ' (' . $contagem[$k] . ')' : '' ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<?php if (!$msgs): ?>
<div class="g-empty">Nenhuma mensagem <?= $filtro === 'todas' ? '' : h($filtro) ?> por aqui.</div>
<?php endif; ?>

<?php foreach ($msgs as $m):
    $email_contato = $m['email'] ?: $m['email_usuario'];
?>
<div class="g-campo-grupo">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
        <div>
            <strong style="color:#166534"><?= h($m['nome']) ?></strong>
            <?php if ($m['usuario_id']): ?>
                <span class="badge bg-success-subtle text-success-emphasis">cadastrado</span>
            <?php endif; ?>
            <div style="font-size:12px;color:#64748b">
                <?= date('d/m/Y H:i', strtotime((string) $m['criado_em'])) ?>
                <?php if (!empty($m['telefone'])): ?> · 📞 <?= h($m['telefone']) ?><?php endif; ?>
                <?php if ($email_contato): ?> · ✉️ <?= h($email_contato) ?><?php endif; ?>
                <?php if (!empty($m['animal'])): ?> · <?= h($m['animal']) ?><?php endif; ?>
                <?php if (!empty($m['topico'])): ?> · <?= h($m['topico']) ?><?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-1">
            <?php if ($email_contato): ?>
                <a href="email.php?msg=<?= h($m['id']) ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-reply"></i> Responder
                </a>
            <?php elseif (!empty($m['telefone'])): ?>
                <a href="https://wa.me/55<?= preg_replace('/\D/','',(string)$m['telefone']) ?>"
                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            <?php endif; ?>
            <form method="POST" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= h($m['id']) ?>">
                <input type="hidden" name="filtro" value="<?= h($filtro) ?>">
                <input type="hidden" name="status" value="<?= $m['status'] === 'arquivada' ? 'pendente' : 'arquivada' ?>">
                <button class="btn btn-sm btn-outline-secondary">
                    <?= $m['status'] === 'arquivada' ? 'Reabrir' : 'Arquivar' ?>
                </button>
            </form>
        </div>
    </div>

    <div style="white-space:pre-wrap;font-size:14px;color:#334155;background:#f8fafc;
                padding:12px 14px;border-radius:8px"><?= h($m['mensagem']) ?></div>

    <?php if (!$email_contato): ?>
    <small class="g-campo-ajuda mt-2 d-block">
        Esta mensagem não deixou e-mail — só dá para responder por telefone.
        Quem escrever a partir de agora pode informar o e-mail no formulário.
    </small>
    <?php endif; ?>

    <?php if (!empty($m['respondida_em'])): ?>
    <small class="d-block mt-2" style="font-size:11.5px;color:#166534">
        <i class="bi bi-check-circle-fill"></i>
        Respondida em <?= date('d/m/Y H:i', strtotime((string) $m['respondida_em'])) ?>
    </small>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<?php require '_layout_close.php'; ?>
