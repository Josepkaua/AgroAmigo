<?php
/**
 * Enviar e-mail pelo painel — admin e técnico.
 * Quatro destinos: responder uma mensagem de contato, um produtor cadastrado,
 * todos os produtores, ou um endereço digitado na hora.
 */
declare(strict_types=1);
require_once '../includes/auth.php';
require_once '../includes/conteudo.php';
require_once '../includes/envio_admin.php';

$u = require_editor();

// Resposta a uma mensagem de contato: chega com ?msg=<uuid>
$resposta_a = null;
if (!empty($_GET['msg']) && preg_match('/^[0-9a-f-]{36}$/i', $_GET['msg'])) {
    try {
        $q = db()->prepare("SELECT * FROM mensagens_contato WHERE id = :id");
        $q->execute(['id' => $_GET['msg']]);
        $resposta_a = $q->fetch() ?: null;
    } catch (Throwable $e) { /* segue sem pré-preenchimento */ }
}

// ─── Envio ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $destino_tipo = $_POST['destino_tipo'] ?? '';
    $assunto      = trim($_POST['assunto'] ?? '');
    $corpo        = trim($_POST['corpo'] ?? '');
    $msg_id       = $_POST['msg_id'] ?? '';

    $erros = [];
    if ($assunto === '') $erros[] = 'Escreva um assunto.';
    if ($corpo === '')   $erros[] = 'Escreva a mensagem.';
    if (mb_strlen($assunto) > 200) $erros[] = 'Assunto muito longo.';

    // Monta a lista de destinatários conforme a opção escolhida
    $lista = [];
    $tipo  = 'individual';

    if ($destino_tipo === 'usuario') {
        $uid = $_POST['usuario_id'] ?? '';
        try {
            $q = db()->prepare("SELECT nome, email FROM usuarios WHERE id = :id AND status='ativo'");
            $q->execute(['id' => $uid]);
            if ($d = $q->fetch()) $lista[] = $d;
            else $erros[] = 'Selecione um produtor da lista.';
        } catch (Throwable $e) { $erros[] = 'Erro ao buscar o produtor.'; }

    } elseif ($destino_tipo === 'resposta') {
        $email_resp = trim($_POST['email_resposta'] ?? '');
        if (!filter_var($email_resp, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Esta mensagem não tem e-mail para responder. Use o telefone.';
        } else {
            $lista[] = ['nome' => trim($_POST['nome_resposta'] ?? ''), 'email' => $email_resp];
            $tipo = 'resposta';
        }

    } elseif ($destino_tipo === 'todos') {
        $incluir = !empty($_POST['incluir_nao_verificados']);
        $lista = destinatarios_produtores($incluir);
        $tipo  = 'massa';
        if (!$lista) $erros[] = 'Nenhum produtor elegível. '
            . 'Se ninguém confirmou o e-mail ainda, marque a caixa para incluir os não confirmados.';
        // Comunicado geral exige confirmação explícita
        if ($lista && empty($_POST['confirmo_massa'])) {
            $erros[] = 'Marque a confirmação antes de enviar para ' . count($lista) . ' pessoas.';
        }

    } elseif ($destino_tipo === 'avulso') {
        $e = trim($_POST['email_avulso'] ?? '');
        if (!filter_var($e, FILTER_VALIDATE_EMAIL)) $erros[] = 'Digite um e-mail válido.';
        else { $lista[] = ['nome' => '', 'email' => $e]; $tipo = 'avulso'; }

    } else {
        $erros[] = 'Escolha para quem enviar.';
    }

    // Travas de volume
    if (!$erros && !pode_enviar(count($lista), $motivo)) $erros[] = $motivo;

    if ($erros) {
        flash('erro', implode(' · ', $erros));
    } else {
        $ok = 0; $falhou = 0; $primeiro_erro = null;
        foreach ($lista as $d) {
            // "Olá, Fulano" só quando sabemos o nome
            $saud  = trim((string) ($d['nome'] ?? '')) !== ''
                   ? 'Olá, ' . explode(' ', trim((string) $d['nome']))[0] . "!\n\n" : '';
            $e = null;
            if (enviar_pelo_painel((string) $d['email'], $assunto, $saud . $corpo, $tipo, $e)) $ok++;
            else { $falhou++; $primeiro_erro = $primeiro_erro ?? $e; }
        }

        // Marca a mensagem de contato como respondida
        if ($tipo === 'resposta' && $ok && preg_match('/^[0-9a-f-]{36}$/i', $msg_id)) {
            try {
                db()->prepare("UPDATE mensagens_contato
                                  SET respondida_em = NOW(), respondida_por = :u, status = 'respondida'
                                WHERE id = :id")
                    ->execute(['u' => $u['id'], 'id' => $msg_id]);
            } catch (Throwable $e) { /* não impede o envio */ }
        }

        log_atividade('emails_enviados', null, 'criar', null,
            ['tipo' => $tipo, 'assunto' => $assunto, 'enviados' => $ok, 'falhas' => $falhou]);

        if ($falhou === 0)   flash('success', $ok . ($ok === 1 ? ' e-mail enviado.' : ' e-mails enviados.'));
        elseif ($ok === 0)   flash('erro', 'Nenhum e-mail saiu. Motivo: ' . ($primeiro_erro ?? 'desconhecido'));
        else                 flash('erro', "{$ok} enviados, {$falhou} falharam. Primeiro erro: " . ($primeiro_erro ?? '-'));
    }

    header('Location: email.php');
    exit;
}

// ─── Dados da tela ───────────────────────────────────
try {
    $produtores = db()->query("
        SELECT id, nome, email, email_verificado FROM usuarios
         WHERE status='ativo' AND email IS NOT NULL ORDER BY nome
    ")->fetchAll();
} catch (Throwable $e) { $produtores = []; }

$verificados = count(destinatarios_produtores(false));
$todos_prod  = count(destinatarios_produtores(true));
$saldo       = saldo_do_dia();

$g_pagina = 'email';
$g_titulo = 'Enviar e-mail';
$flash = get_flash();
require '_layout.php';
?>

<div class="g-page-head">
    <div>
        <h1 class="g-page-title">Enviar e-mail</h1>
        <p class="g-page-sub">
            A mensagem sai de <strong><?= h(email_remetente() ?: 'e-mail do projeto') ?></strong>,
            assinada como equipe AgroAmigo. Texto simples, sem formatação — chega melhor
            na caixa de entrada e abre em qualquer celular.
        </p>
    </div>
    <div class="text-end" style="font-size:12px;color:#64748b">
        Restam hoje<br><strong style="font-size:20px;color:<?= $saldo < 30 ? '#b91c1c' : '#166534' ?>">
        <?= $saldo ?></strong> de <?= LIMITE_DIARIO ?>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : 'success' ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<?php if (!email_configurado()): ?>
<div class="alert alert-warning">
    <strong>O envio ainda não está ligado.</strong> Falta a variável <code>BREVO_API_KEY</code>
    no Render. Enquanto isso, nada sai daqui.
</div>
<?php endif; ?>

<form method="POST" class="g-campo-grupo" id="formEmail">
    <?= csrf_field() ?>
    <input type="hidden" name="msg_id" value="<?= h($resposta_a['id'] ?? '') ?>">

    <h3><i class="bi bi-people"></i> Para quem</h3>

    <?php if ($resposta_a): ?>
    <div class="alert alert-light border mb-3" style="font-size:13px">
        Respondendo <strong><?= h($resposta_a['nome']) ?></strong>
        <?php if (!empty($resposta_a['telefone'])): ?>
            · tel. <?= h($resposta_a['telefone']) ?>
        <?php endif; ?>
        <div class="mt-2 p-2" style="background:#f8fafc;border-radius:6px;white-space:pre-wrap"><?= h($resposta_a['mensagem']) ?></div>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <?php $tem_email_resp = !empty($resposta_a['email']); ?>
        <?php if ($resposta_a): ?>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="destino_tipo" value="resposta"
                   id="d_resp" <?= $tem_email_resp ? 'checked' : 'disabled' ?>>
            <label class="form-check-label" for="d_resp">
                Responder <?= h($resposta_a['nome']) ?>
                <?php if ($tem_email_resp): ?>
                    (<?= h($resposta_a['email']) ?>)
                <?php else: ?>
                    <span class="text-danger">— esta mensagem não deixou e-mail, responda pelo telefone</span>
                <?php endif; ?>
            </label>
            <input type="hidden" name="email_resposta" value="<?= h($resposta_a['email'] ?? '') ?>">
            <input type="hidden" name="nome_resposta"  value="<?= h($resposta_a['nome'] ?? '') ?>">
        </div>
        <?php endif; ?>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="destino_tipo" value="usuario" id="d_user"
                   <?= $resposta_a ? '' : 'checked' ?>>
            <label class="form-check-label" for="d_user">Um produtor cadastrado</label>
        </div>
        <select name="usuario_id" class="form-select mt-1 mb-2" style="max-width:460px">
            <option value="">— escolha —</option>
            <?php foreach ($produtores as $p): ?>
            <option value="<?= h($p['id']) ?>">
                <?= h($p['nome']) ?> — <?= h($p['email']) ?><?= $p['email_verificado'] ? '' : ' (não confirmado)' ?>
            </option>
            <?php endforeach; ?>
        </select>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="destino_tipo" value="todos" id="d_todos">
            <label class="form-check-label" for="d_todos">
                Todos os produtores — <strong><?= $verificados ?></strong> com e-mail confirmado
            </label>
        </div>
        <div class="ms-4 mb-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="incluir_nao_verificados" id="inc_nv">
                <label class="form-check-label" for="inc_nv" style="font-size:12.5px">
                    Incluir quem ainda não confirmou o e-mail (total <?= $todos_prod ?>)
                </label>
            </div>
            <small class="g-campo-ajuda">
                E-mail não confirmado tem mais chance de não existir. Muita mensagem voltando
                com erro faz o Brevo e o Gmail passarem a jogar <em>todo</em> e-mail do projeto no spam.
            </small>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="destino_tipo" value="avulso" id="d_avulso">
            <label class="form-check-label" for="d_avulso">Outro endereço</label>
        </div>
        <input type="email" name="email_avulso" class="form-control mt-1"
               placeholder="pessoa@exemplo.com" style="max-width:460px">
    </div>

    <h3 class="mt-4"><i class="bi bi-envelope"></i> Mensagem</h3>

    <label class="form-label fw-semibold mb-1" style="font-size:13px">Assunto</label>
    <input name="assunto" class="form-control mb-3" maxlength="200" required
           value="<?= $resposta_a ? 'Re: sua mensagem para a equipe AgroAmigo' : '' ?>"
           placeholder="Ex.: Campanha de vacinação em setembro">

    <label class="form-label fw-semibold mb-1" style="font-size:13px">Texto</label>
    <textarea name="corpo" class="form-control mb-1" rows="9" required
              placeholder="Escreva como falaria com o produtor. Evite termos técnicos demais."></textarea>
    <small class="g-campo-ajuda mb-3 d-block">
        O nome da pessoa entra automaticamente no começo ("Olá, Fulano!") e a assinatura
        da equipe no fim. Não precisa escrever.
    </small>

    <div class="form-check mb-3 p-3" style="background:#fef9c3;border-radius:8px;border:1px solid #fde047">
        <input class="form-check-input" type="checkbox" name="confirmo_massa" id="conf">
        <label class="form-check-label" for="conf" style="font-size:13px">
            <strong>Só para o comunicado geral:</strong> confirmo que quero enviar para todos os
            produtores de uma vez. E-mail em massa não tem como ser cancelado depois de sair.
        </label>
    </div>

    <button class="btn btn-success px-4" <?= email_configurado() ? '' : 'disabled' ?>>
        <i class="bi bi-send"></i> Enviar
    </button>
    <a href="mensagens.php" class="btn btn-link">Ver mensagens recebidas</a>
</form>

<div class="alert alert-light border" style="font-size:12.5px">
    <i class="bi bi-shield-check text-success"></i>
    Todo envio fica registrado com quem mandou, para quem e o quê. Há limite de
    <?= LIMITE_DIARIO ?> por dia e <?= LIMITE_POR_MINUTO ?> por minuto — se uma conta de
    admin for invadida, o estrago é limitado.
</div>

<?php require '_layout_close.php'; ?>
