<?php
/**
 * Confirma o e-mail a partir do link enviado.
 * O token só existe no e-mail — no banco fica apenas o hash SHA-256.
 */
declare(strict_types=1);
require_once 'includes/auth.php';
require_once 'includes/emails.php';

session_init();
security_headers();

$ok = false;
$titulo = 'Não foi possível confirmar';
$msg = 'Este link não vale mais. Peça um novo pelo aviso no topo do site.';

$token = $_GET['token'] ?? '';

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    try {
        $pdo = db();
        $st = $pdo->prepare("
            SELECT v.id, v.usuario_id, u.nome, u.email
              FROM verificacao_email v
              JOIN usuarios u ON u.id = v.usuario_id
             WHERE v.token_hash = :h
               AND v.usado_em IS NULL
               AND v.expira_em > NOW()
             LIMIT 1
        ");
        $st->execute(['h' => hash('sha256', $token)]);
        $v = $st->fetch();

        if ($v) {
            $pdo->prepare("UPDATE verificacao_email SET usado_em = NOW() WHERE id = :id")
                ->execute(['id' => $v['id']]);
            $pdo->prepare("UPDATE usuarios SET email_verificado = TRUE, updated_at = NOW() WHERE id = :u")
                ->execute(['u' => $v['usuario_id']]);

            // Se a pessoa já está logada nesta sessão, atualiza na hora
            if (!empty($_SESSION['usuario']['id']) && $_SESSION['usuario']['id'] === $v['usuario_id']) {
                $_SESSION['usuario']['email_verificado'] = true;
            }

            log_atividade('usuarios', (string) $v['usuario_id'], 'editar', null, ['email_verificado' => true]);

            $ok = true;
            $titulo = 'E-mail confirmado!';
            $msg = 'Pronto, ' . _primeiro_nome((string) $v['nome'])
                 . '. Seu e-mail está confirmado e você já pode recuperar sua senha por ele.';
        }
    } catch (Throwable $e) {
        log_erro('verificar-email: ' . $e->getMessage(), __FILE__, __LINE__);
        $msg = 'Tivemos um problema ao confirmar. Tente novamente em alguns minutos.';
    }
}

$logado = usuario_logado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($titulo) ?> — AgroAmigo</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',system-ui,sans-serif;background:#f0fdf4;min-height:100vh;
       display:flex;align-items:center;justify-content:center;padding:24px}
  .cx{background:#fff;border-radius:18px;padding:40px 34px;max-width:440px;width:100%;
      box-shadow:0 10px 40px rgba(0,0,0,.10);text-align:center}
  .ic{font-size:52px;margin-bottom:14px;line-height:1}
  h1{font-size:21px;color:#14532d;margin-bottom:10px;letter-spacing:-.4px}
  p{font-size:14.5px;color:#4b5563;line-height:1.65;margin-bottom:24px}
  .bt{display:inline-block;background:#166534;color:#fff;text-decoration:none;
      padding:12px 28px;border-radius:999px;font-weight:700;font-size:14.5px;
      transition:background .18s}
  .bt:hover{background:#14532d}
  .bt2{display:block;margin-top:14px;color:#6b7280;font-size:13px;text-decoration:none}
  .bt2:hover{color:#166534}
</style>
</head>
<body>
  <div class="cx">
    <div class="ic"><?= $ok ? '✅' : '⚠️' ?></div>
    <h1><?= h($titulo) ?></h1>
    <p><?= h($msg) ?></p>
    <a class="bt" href="<?= $logado ? 'index.php' : 'login.php' ?>">
      <?= $logado ? 'Ir para o painel' : 'Entrar no AgroAmigo' ?>
    </a>
    <a class="bt2" href="home.php">Voltar para o site</a>
  </div>
</body>
</html>
