<?php
declare(strict_types=1);
require_once 'includes/auth.php';
$usuario = require_login('login.php');
$pdo = db();

$id = trim($_GET['id'] ?? '');
if (!$id) { header('Location: index.php'); exit; }

// Verifica pertencimento
$stmt = $pdo->prepare("SELECT * FROM propriedades WHERE id = :id AND usuario_id = :uid");
$stmt->execute(['id' => $id, 'uid' => $usuario['id']]);
$prop = $stmt->fetch();
if (!$prop) { http_response_code(404); include '404.php'; exit; }

// ── POST: editar propriedade ──────────────────────────────
$form_erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_acao'] ?? '') === 'editar_prop') {
    csrf_verify();
    $nome     = trim($_POST['nome']      ?? '');
    $municipio = trim($_POST['municipio'] ?? '');
    $uf       = strtoupper(trim($_POST['uf'] ?? ''));
    $area     = ($_POST['area_ha'] ?? '') !== '' ? (float)$_POST['area_ha'] : null;

    if (!$nome) {
        $form_erro = 'O nome da propriedade é obrigatório.';
    } else {
        $pdo->prepare("
            UPDATE propriedades SET nome = :n, municipio = :m, uf = :u, area_ha = :a WHERE id = :id
        ")->execute(['n' => $nome, 'm' => $municipio ?: null, 'u' => $uf ?: null, 'a' => $area, 'id' => $id]);
        flash('success', 'Propriedade atualizada com sucesso!');
        header("Location: propriedade.php?id={$id}");
        exit;
    }
}

// Animais da propriedade
$anim = $pdo->prepare("
    SELECT id, brinco, especie, raca, status, data_nascimento
    FROM animais WHERE propriedade_id = :pid
    ORDER BY especie, brinco
");
$anim->execute(['pid' => $id]);
$animais = $anim->fetchAll();

$pagina        = 'conta';
$titulo_pagina = 'Propriedade';
require 'includes/header.php';

function espEmoji2(string $esp): string {
    $m = ['bovino'=>'🐄','ave'=>'🐔','suino'=>'🐷','caprino'=>'🐐','ovino'=>'🐑','peixe'=>'🐟'];
    return $m[mb_strtolower(trim($esp))] ?? '🐾';
}
?>

<section class="aa-page-hero">
    <div class="container position-relative">
        <nav class="aa-breadcrumb mb-3">
            <a href="index.php">Início</a><span>/</span>
            <span class="text-white">Propriedade</span>
        </nav>
        <span class="aa-page-emoji">🏡</span>
        <h1 class="aa-page-title"><?= h($prop['nome']) ?></h1>
        <?php if ($prop['municipio']): ?>
        <p class="aa-page-desc mt-1"><?= h($prop['municipio']) ?><?= $prop['uf'] ? ' / ' . h($prop['uf']) : '' ?>
            <?= $prop['area_ha'] ? ' · ' . number_format((float)$prop['area_ha'], 1, ',', '.') . ' ha' : '' ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="container py-4">

    <?php $f = get_flash(); if ($f): ?>
    <div class="alert alert-<?= $f['tipo'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4">
        <?= h($f['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Coluna esquerda: info + edição -->
        <div class="col-lg-4">
            <div class="aa-card mb-4">
                <div class="aa-card-head">
                    <div><h2 class="aa-card-title">📋 Informações</h2></div>
                    <button class="aa-btn-sm" id="btn-toggle-edit" type="button">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                </div>

                <!-- Dados exibição -->
                <div id="prop-info">
                    <div class="aa-info-row"><span class="aa-info-lbl">Nome</span><span><?= h($prop['nome']) ?></span></div>
                    <div class="aa-info-row"><span class="aa-info-lbl">Município</span><span><?= h($prop['municipio'] ?? '—') ?></span></div>
                    <div class="aa-info-row"><span class="aa-info-lbl">Estado</span><span><?= h($prop['uf'] ?? '—') ?></span></div>
                    <div class="aa-info-row"><span class="aa-info-lbl">Área</span><span><?= $prop['area_ha'] ? number_format((float)$prop['area_ha'], 1, ',', '.') . ' ha' : '—' ?></span></div>
                    <div class="aa-info-row"><span class="aa-info-lbl">Cadastro</span><span><?= date('d/m/Y', strtotime($prop['created_at'])) ?></span></div>
                </div>

                <!-- Formulário edição -->
                <div id="prop-form" style="display:none;padding-top:8px">
                    <?php if ($form_erro): ?>
                    <div class="alert alert-danger p-2 mb-3" style="font-size:13px"><?= h($form_erro) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="propriedade.php?id=<?= h($id) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_acao" value="editar_prop">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px">Nome *</label>
                            <input type="text" name="nome" class="form-control form-control-sm" value="<?= h($prop['nome']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px">Município</label>
                            <input type="text" name="municipio" class="form-control form-control-sm" value="<?= h($prop['municipio'] ?? '') ?>">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label fw-semibold" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px">UF</label>
                                <input type="text" name="uf" class="form-control form-control-sm" maxlength="2" value="<?= h($prop['uf'] ?? '') ?>" placeholder="MA">
                            </div>
                            <div class="col-8">
                                <label class="form-label fw-semibold" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px">Área (ha)</label>
                                <input type="number" name="area_ha" class="form-control form-control-sm" step="0.1" min="0" value="<?= h($prop['area_ha'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sm" style="background:#166534;color:#fff;font-weight:700">Salvar</button>
                            <button type="button" id="btn-cancel-edit" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <a href="index.php" class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-arrow-left me-1"></i> Voltar ao painel
            </a>
        </div>

        <!-- Coluna direita: animais -->
        <div class="col-lg-8">
            <div class="aa-card">
                <div class="aa-card-head">
                    <div>
                        <h2 class="aa-card-title">🐾 Animais (<?= count($animais) ?>)</h2>
                        <p class="aa-card-sub">Todos os animais cadastrados nessa propriedade</p>
                    </div>
                    <a href="fichas.php" class="aa-btn-sm"><i class="bi bi-plus-lg"></i> Novo animal</a>
                </div>

                <?php if (!$animais): ?>
                <div class="aa-empty">
                    <div style="font-size:36px;margin-bottom:10px">🐾</div>
                    <p>Nenhum animal cadastrado nesta propriedade.</p>
                    <a href="fichas.php" class="aa-btn-sm mt-2"><i class="bi bi-plus-lg"></i> Cadastrar animal</a>
                </div>
                <?php else: ?>
                <div>
                    <?php foreach ($animais as $a):
                        $ativo = $a['status'] === 'ativo';
                    ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f3f4f6">
                        <div style="font-size:26px;width:36px;text-align:center;flex-shrink:0"><?= espEmoji2($a['especie'] ?? '') ?></div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:14px;font-weight:700;color:#111827"><?= h($a['brinco'] ?: '—') ?><?= $a['raca'] ? ' · ' . h($a['raca']) : '' ?></div>
                            <div style="font-size:12px;color:#9ca3af"><?= h(ucfirst($a['especie'] ?? '')) ?>
                                <?php if ($a['data_nascimento']): ?> · Nasc. <?= date('d/m/Y', strtotime($a['data_nascimento'])) ?><?php endif; ?>
                            </div>
                        </div>
                        <?php if (!$ativo): ?>
                        <span style="font-size:11px;background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:20px;font-weight:700"><?= h(ucfirst($a['status'])) ?></span>
                        <?php endif; ?>
                        <a href="fichas.php?animal=<?= h($a['id']) ?>" style="font-size:12px;font-weight:600;color:#166534;text-decoration:none;white-space:nowrap">
                            Ver ficha <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<style>
.aa-info-row { display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
.aa-info-row:last-child { border-bottom:none; }
.aa-info-lbl { font-weight:600; color:#6b7280; }
</style>

<script>
var toggleBtn  = document.getElementById('btn-toggle-edit');
var cancelBtn  = document.getElementById('btn-cancel-edit');
var formDiv    = document.getElementById('prop-form');
var infoDiv    = document.getElementById('prop-info');

toggleBtn.addEventListener('click', function() {
    formDiv.style.display = formDiv.style.display === 'none' ? '' : 'none';
    infoDiv.style.display = infoDiv.style.display === 'none' ? '' : 'none';
});
cancelBtn.addEventListener('click', function() {
    formDiv.style.display = 'none';
    infoDiv.style.display = '';
});

<?php if ($form_erro): ?>
// Re-abre o formulário se houve erro
formDiv.style.display = '';
infoDiv.style.display = 'none';
<?php endif; ?>
</script>

<?php require 'includes/footer.php'; ?>
