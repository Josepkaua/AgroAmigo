<?php
/**
 * Edição das raças pelo painel — para admin e técnico.
 * Feita para quem não mexe com programação: um card por raça, campos
 * com nome claro, e a foto trocada por um botão de "escolher arquivo".
 */
declare(strict_types=1);
require_once '../includes/auth.php';
require_once '../includes/conteudo.php';
require_once '../includes/upload.php';

$u = require_editor();

$ESPECIES = [
    'bovinos'  => '🐄 Bovinos',  'aves'     => '🐔 Aves',
    'suinos'   => '🐷 Suínos',   'caprinos' => '🐐 Caprinos',
    'ovinos'   => '🐑 Ovinos',   'peixes'   => '🐟 Peixes',
];

$especie = $_GET['especie'] ?? 'bovinos';
if (!isset($ESPECIES[$especie])) $especie = 'bovinos';

// ─── Salvar ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $id   = $_POST['id']   ?? '';
    $acao = $_POST['acao'] ?? 'salvar';

    try {
        if ($acao === 'salvar') {
            $nome = trim($_POST['nome'] ?? '');
            $tipo = trim($_POST['tipo'] ?? '');
            $desc = trim($_POST['descricao'] ?? '');

            if ($nome === '') {
                flash('erro', 'O nome da raça não pode ficar vazio.');
            } else {
                // Guarda o valor anterior para dar para desfazer depois
                $antes = db()->prepare("SELECT nome, tipo, descricao, imagem FROM racas WHERE id = :id");
                $antes->execute(['id' => $id]);
                $anterior = $antes->fetch();

                $nova_img = null;
                if (!empty($_FILES['foto']['name'])) {
                    $erro_up = null;
                    $uuid = salvar_imagem($_FILES['foto'], $erro_up, 900);
                    if ($uuid === null) {
                        flash('erro', $erro_up ?? 'Não consegui enviar a foto.');
                        header("Location: racas.php?especie=$especie"); exit;
                    }
                    $nova_img = ref_imagem($uuid);
                }

                $sql = "UPDATE racas SET nome=:n, tipo=:t, descricao=:d, updated_at=NOW()"
                     . ($nova_img ? ", imagem=:img" : "")
                     . " WHERE id=:id";
                $par = ['n'=>$nome, 't'=>$tipo, 'd'=>$desc, 'id'=>$id];
                if ($nova_img) $par['img'] = $nova_img;
                db()->prepare($sql)->execute($par);

                log_atividade('racas', $id, 'editar', $anterior,
                    ['nome'=>$nome,'tipo'=>$tipo,'descricao'=>$desc,'imagem'=>$nova_img ?? ($anterior['imagem'] ?? null)]);

                flash('success', "Raça \"{$nome}\" atualizada." . ($nova_img ? ' Foto trocada.' : ''));
            }
        }
    } catch (Throwable $e) {
        log_erro('gestao/racas: ' . $e->getMessage(), __FILE__, __LINE__);
        flash('erro', 'Não consegui salvar. Tente de novo.');
    }
    header("Location: racas.php?especie=$especie");
    exit;
}

$st = db()->prepare("SELECT * FROM racas WHERE especie = :e ORDER BY ordem, nome");
$st->execute(['e' => $especie]);
$racas = $st->fetchAll();

$g_pagina = 'racas';
$g_titulo = 'Raças do site';
$flash = get_flash();
require '_layout.php';
?>

<div class="g-page-head">
    <div>
        <h1 class="g-page-title">Raças do site</h1>
        <p class="g-page-sub">
            O que você mudar aqui aparece no site na hora, nas páginas de cada espécie.
            Não precisa mexer em código nem avisar ninguém.
        </p>
    </div>
    <a href="../<?= h($especie) ?>.php" target="_blank" class="btn btn-outline-success btn-sm">
        <i class="bi bi-box-arrow-up-right"></i> Ver a página no site
    </a>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : 'success' ?> d-flex align-items-center gap-2">
    <i class="bi bi-<?= $flash['tipo'] === 'erro' ? 'exclamation-triangle-fill' : 'check-circle-fill' ?>"></i>
    <?= h($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- Abas das espécies -->
<ul class="nav nav-pills gap-2 mb-4 flex-wrap">
    <?php foreach ($ESPECIES as $k => $rotulo): ?>
    <li class="nav-item">
        <a class="nav-link <?= $k === $especie ? 'active' : '' ?>"
           style="<?= $k === $especie ? 'background:#166534' : 'background:#f1f5f9;color:#334155' ?>"
           href="racas.php?especie=<?= $k ?>"><?= $rotulo ?></a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="row g-4">
<?php foreach ($racas as $r):
    $img = img_raca($r['imagem'] ?? '');
    // as páginas do site estão um nível acima do gestao/
    $img_src = $img !== '' ? '../' . $img : '';
?>
    <div class="col-lg-6">
        <form method="POST" enctype="multipart/form-data" class="g-card h-100">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= h($r['id']) ?>">

            <div class="d-flex gap-3 align-items-start mb-3">
                <div style="width:140px;flex:0 0 140px">
                    <?php if ($img_src): ?>
                        <img src="<?= h($img_src) ?>" alt=""
                             style="width:100%;aspect-ratio:3/2;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0">
                    <?php else: ?>
                        <div style="width:100%;aspect-ratio:3/2;border-radius:10px;border:1px dashed #cbd5e1;
                                    display:flex;align-items:center;justify-content:center;font-size:34px;background:#f8fafc">
                            <?= h($r['emoji']) ?>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size:11px">Sem foto — o site mostra o emoji</small>
                    <?php endif; ?>
                </div>

                <div class="flex-grow-1">
                    <label class="form-label fw-semibold mb-1" style="font-size:13px">Nome da raça</label>
                    <input name="nome" class="form-control mb-2" required maxlength="100"
                           value="<?= h($r['nome']) ?>">

                    <label class="form-label fw-semibold mb-1" style="font-size:13px">Aptidão</label>
                    <input name="tipo" class="form-control" maxlength="50"
                           placeholder="Ex.: Corte, Leite, Misto"
                           value="<?= h($r['tipo'] ?? '') ?>">
                </div>
            </div>

            <label class="form-label fw-semibold mb-1" style="font-size:13px">Descrição técnica</label>
            <textarea name="descricao" class="form-control mb-3" rows="5"
                      placeholder="Características da raça, desempenho, adaptação ao clima..."><?= h($r['descricao'] ?? '') ?></textarea>

            <label class="form-label fw-semibold mb-1" style="font-size:13px">Trocar a foto</label>
            <input type="file" name="foto" class="form-control mb-2" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted d-block mb-3" style="font-size:11.5px">
                Pode mandar foto tirada do celular — eu ajusto o tamanho sozinho.
                Aceita JPG, PNG ou WEBP, até 8 MB. Deixe em branco para manter a atual.
            </small>

            <button class="btn btn-success w-100">
                <i class="bi bi-check-lg"></i> Salvar <?= h($r['nome']) ?>
            </button>
        </form>
    </div>
<?php endforeach; ?>
</div>

<?php if (!$racas): ?>
<div class="alert alert-warning">
    Nenhuma raça cadastrada para esta espécie. Rode a migration
    <code>sql/migration_conteudo_editavel.sql</code> no Supabase.
</div>
<?php endif; ?>

<div class="alert alert-light border mt-4" style="font-size:13px">
    <i class="bi bi-info-circle text-success"></i>
    <strong>Se errar, dá para voltar.</strong> Toda alteração fica registrada em
    <a href="logs.php?aba=atividade">Logs de Atividade</a>, com o valor anterior guardado.
</div>

<?php require '_layout_close.php'; ?>
