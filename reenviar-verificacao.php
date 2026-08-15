<?php
/**
 * Reenvia o e-mail de confirmação (botão da faixa no topo do site).
 * Limitado a 1 envio a cada 2 minutos para não virar ferramenta de spam:
 * sem isso, alguém poderia clicar sem parar e inundar a caixa da pessoa.
 */
declare(strict_types=1);
require_once 'includes/auth.php';
require_once 'includes/emails.php';

$u = require_login('login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}
csrf_verify();

try {
    $st = db()->prepare("
        SELECT criado_em FROM verificacao_email
         WHERE usuario_id = :u
         ORDER BY criado_em DESC LIMIT 1
    ");
    $st->execute(['u' => $u['id']]);
    $ultimo = $st->fetchColumn();

    if ($ultimo && (time() - strtotime((string) $ultimo)) < 120) {
        flash('erro', 'Já enviamos um e-mail agora há pouco. Verifique sua caixa de entrada e o spam. Se precisar, tente de novo em 2 minutos.');
        header('Location: ' . url_interna($_POST['voltar'] ?? '', 'index.php')); exit;
    }

    $dados = db()->prepare("SELECT id, nome, email, email_verificado FROM usuarios WHERE id = :u");
    $dados->execute(['u' => $u['id']]);
    $user = $dados->fetch();

    if (!$user) {
        flash('erro', 'Conta não encontrada.');
    } elseif (!empty($user['email_verificado'])) {
        flash('success', 'Seu e-mail já está confirmado.');
    } elseif (enviar_verificacao($user)) {
        flash('success', 'Enviamos o link para ' . $user['email'] . '. Olhe também na caixa de spam.');
    } else {
        flash('erro', 'Não consegui enviar agora. Tente de novo daqui a pouco.');
    }
} catch (Throwable $e) {
    log_erro('reenviar-verificacao: ' . $e->getMessage(), __FILE__, __LINE__);
    flash('erro', 'Erro ao reenviar. Tente novamente.');
}

header('Location: ' . url_interna($_POST['voltar'] ?? '', 'index.php'));
exit;
