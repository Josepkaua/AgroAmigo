<?php
/**
 * Edição dos textos e banners do site — admin e técnico.
 * Cada bloco tem um rótulo em português e uma explicação de onde aparece,
 * para quem não conhece o código saber o que está mudando.
 */
declare(strict_types=1);
require_once '../includes/auth.php';
require_once '../includes/conteudo.php';
require_once '../includes/upload.php';

$u = require_editor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $salvos = 0; $erros = [];

    try {
        $atuais = [];
        foreach (db()->query("SELECT chave, tipo, valor FROM conteudo_site")->fetchAll() as $c) {
            $atuais[$c['chave']] = $c;
        }

        foreach ($atuais as $chave => $info) {
            $novo = null;

            if ($info['tipo'] === 'imagem') {
                if (!empty($_FILES['img_' . $chave]['name'])) {
                    $erro_up = null;
                    // banner é largo: 1400px
                    $uuid = salvar_imagem($_FILES['img_' . $chave], $erro_up, 1400);
                    if ($uuid === null) { $erros[] = $info['rotulo'] ?? $chave . ': ' . $erro_up; continue; }
                    $novo = ref_imagem($uuid);
                }
            } elseif (isset($_POST['c_' . $chave])) {
                $v = trim((string) $_POST['c_' . $chave]);
                if ($info['tipo'] === 'telefone') $v = preg_replace('/\D/', '', $v);
                if ($v !== (string) ($info['valor'] ?? '')) $novo = $v;
            }

            if ($novo !== null) {
                db()->prepare("
                    UPDATE conteudo_site
                       SET valor = :v, atualizado_em = NOW(), atualizado_por = :u
                     WHERE chave = :c
                ")->execute(['v' => $novo, 'u' => $u['id'], 'c' => $chave]);

                log_atividade('conteudo_site', $chave, 'editar',
                    ['valor' => $info['valor']], ['valor' => $novo]);
                $salvos++;
            }
        }
    } catch (Throwable $e) {
        log_erro('gestao/conteudo: ' . $e->getMessage(), __FILE__, __LINE__);
        $erros[] = 'Erro ao salvar.';
    }

    if ($erros)       flash('erro', implode(' · ', $erros));
    elseif ($salvos)  flash('success', $salvos . ($salvos === 1 ? ' alteração salva.' : ' alterações salvas.'));
    else              flash('success', 'Nada foi alterado.');

    header('Location: conteudo.php');
    exit;
}

$grupos = conteudo_para_editar();

$g_pagina = 'conteudo';
$g_titulo = 'Textos e Banners';
$flash = get_flash();
require '_layout.php';
?>

<div class="g-page-head">
    <div>
        <h1 class="g-page-title">Textos e Banners</h1>
        <p class="g-page-sub">
            Os textos que aparecem no site. Mude o que quiser e clique em salvar no final —
            a alteração vale na hora, sem precisar publicar nada.
        </p>
    </div>
    <a href="../index.php" target="_blank" class="btn btn-outline-success btn-sm">
        <i class="bi bi-box-arrow-up-right"></i> Ver o site
    </a>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : 'success' ?>">
    <?= h($flash['msg']) ?>
</div>
<?php endif; ?>

<?php if (!$grupos): ?>
<div class="alert alert-warning">
    Ainda não há blocos cadastrados. Rode <code>sql/migration_conteudo_editavel.sql</code> no Supabase.
</div>
<?php else: ?>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <?php foreach ($grupos as $nome_grupo => $itens): ?>
    <div class="g-campo-grupo">
        <h3><i class="bi bi-folder2-open"></i> <?= h($nome_grupo) ?></h3>

        <?php foreach ($itens as $it):
            $id = 'c_' . $it['chave'];
        ?>
        <div class="mt-3">
            <label class="form-label fw-semibold mb-1" style="font-size:13px" for="<?= h($id) ?>">
                <?= h($it['rotulo']) ?>
            </label>

            <?php if ($it['tipo'] === 'texto_longo'): ?>
                <textarea class="form-control" id="<?= h($id) ?>" name="<?= h($id) ?>"
                          rows="3"><?= h((string) $it['valor']) ?></textarea>

            <?php elseif ($it['tipo'] === 'imagem'):
                $atual = img_raca((string) $it['valor']);
            ?>
                <?php if ($atual !== ''): ?>
                    <img src="../<?= h($atual) ?>" alt=""
                         style="width:220px;aspect-ratio:16/6;object-fit:cover;border-radius:8px;
                                border:1px solid #e2e8f0;display:block;margin-bottom:6px">
                <?php endif; ?>
                <input type="file" class="form-control" name="img_<?= h($it['chave']) ?>"
                       accept="image/jpeg,image/png,image/webp">

            <?php elseif ($it['tipo'] === 'telefone'): ?>
                <input class="form-control" id="<?= h($id) ?>" name="<?= h($id) ?>"
                       inputmode="numeric" placeholder="98991538604"
                       value="<?= h((string) $it['valor']) ?>">

            <?php else: ?>
                <input class="form-control" id="<?= h($id) ?>" name="<?= h($id) ?>"
                       maxlength="255" value="<?= h((string) $it['valor']) ?>">
            <?php endif; ?>

            <?php if (!empty($it['ajuda'])): ?>
                <small class="g-campo-ajuda"><?= h($it['ajuda']) ?></small>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-success px-4"><i class="bi bi-check-lg"></i> Salvar alterações</button>
        <span class="text-muted" style="font-size:12px">
            Campo em branco = o site usa o texto original que já vinha escrito.
        </span>
    </div>
</form>
<?php endif; ?>

<?php require '_layout_close.php'; ?>
