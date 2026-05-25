<?php
declare(strict_types=1);
require_once 'includes/auth.php';

session_init();

if (usuario_logado()) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipo_msg = '';
$email_v  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email   = trim($_POST['email'] ?? '');
    $email_v = $email;

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Informe um e-mail válido.';
        $tipo_msg = 'danger';
    } else {
        $pdo  = db();
        $user = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = :e AND status = 'ativo' LIMIT 1");
        $user->execute(['e' => $email]);
        $user = $user->fetch();

        if ($user) {
            // Impede spam: só permite novo token a cada 5 minutos por usuário
            $recente = $pdo->prepare("
                SELECT id FROM reset_senha
                WHERE usuario_id = :uid AND criado_em > NOW() - INTERVAL '5 minutes' AND usado_em IS NULL
                LIMIT 1
            ");
            $recente->execute(['uid' => $user['id']]);

            if (!$recente->fetch()) {
                // Invalida tokens anteriores não usados
                $pdo->prepare("
                    DELETE FROM reset_senha WHERE usuario_id = :uid AND usado_em IS NULL
                ")->execute(['uid' => $user['id']]);

                // Gera token — 32 bytes aleatórios, armazena só o hash
                $token_raw  = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token_raw);
                $expira_em  = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $pdo->prepare("
                    INSERT INTO reset_senha (usuario_id, token_hash, expira_em, ip_solicitou)
                    VALUES (:uid, :hash, :exp, :ip)
                ")->execute([
                    'uid'  => $user['id'],
                    'hash' => $token_hash,
                    'exp'  => $expira_em,
                    'ip'   => ip_real(),
                ]);

                $link = APP_URL . '/resetar-senha.php?token=' . $token_raw;
                enviar_email_reset($email, $user['nome'], $link);

                log_acesso('reset_senha_solicitado', $user['id'], $email);
            }
        }

        // Sempre a mesma mensagem (não revela se o e-mail existe)
        $mensagem = 'Se este e-mail estiver cadastrado, você receberá as instruções em breve.';
        $tipo_msg = 'success';
        $email_v  = '';
    }
}

/**
 * Envia o e-mail de reset.
 * Em produção, substitua pela integração com PHPMailer + SMTP.
 */
function enviar_email_reset(string $para, string $nome, string $link): void
{
    $primeiro = explode(' ', $nome)[0];
    $assunto  = 'Redefinição de senha — AgroAmigo ATERPEC';
    $corpo    = "Olá, {$primeiro}!\n\n"
              . "Recebemos um pedido para redefinir a senha da sua conta no AgroAmigo ATERPEC.\n\n"
              . "Clique no link abaixo (válido por 1 hora):\n"
              . "{$link}\n\n"
              . "Se não foi você, ignore este e-mail. Sua senha não será alterada.\n\n"
              . "— Equipe AgroAmigo ATERPEC";

    $headers  = "From: AgroAmigo ATERPEC <noreply@agroamigo.com.br>\r\n";
    $headers .= "Reply-To: noreply@agroamigo.com.br\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($para, $assunto, $corpo, $headers);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha — AgroAmigo ATERPEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;min-height:100vh;display:flex;background:#f0fdf4}
        a{text-decoration:none;color:inherit}

        .auth-left{
            width:420px;flex-shrink:0;background:#fff;
            display:flex;flex-direction:column;padding:40px 48px;
            min-height:100vh;border-right:1px solid #e5e7eb;
        }
        .auth-right{
            flex:1;
            background-image:url('https://images.unsplash.com/photo-1605000797499-95a51c5269ae?w=1400&q=80&auto=format&fit=crop');
            background-size:cover;background-position:center;
            display:flex;flex-direction:column;align-items:center;justify-content:center;
            padding:48px;position:relative;overflow:hidden;
        }
        .auth-right::before{
            content:'';position:absolute;inset:0;
            background:linear-gradient(135deg,rgba(22,101,52,.92) 0%,rgba(21,128,61,.85) 50%,rgba(20,83,45,.90) 100%);
        }

        .auth-logo{font-size:22px;font-weight:400;color:#166534;margin-bottom:40px;display:flex;align-items:center;gap:8px}
        .auth-logo strong{font-weight:800}
        .auth-title{font-size:26px;font-weight:800;color:#111827;margin-bottom:6px}
        .auth-sub{font-size:14px;color:#6b7280;margin-bottom:28px;line-height:1.6}

        .form-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#374151;margin-bottom:5px}
        .form-group{margin-bottom:18px}
        .form-input{
            display:block;width:100%;border:1.5px solid #e5e7eb;border-radius:10px;
            padding:11px 14px;font-size:14px;color:#1f2937;font-family:inherit;
            background:#fff;outline:none;transition:border-color .2s,box-shadow .2s;
        }
        .form-input:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
        .form-input::placeholder{color:#c4c9d1}

        .btn-submit{
            display:block;width:100%;background:#166534;color:#fff;border:none;border-radius:10px;
            padding:13px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;
            transition:background .2s,transform .15s;margin-top:4px;
        }
        .btn-submit:hover{background:#14532d;transform:translateY(-1px)}

        .alert{border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:20px;display:flex;align-items:flex-start;gap:8px}
        .alert-danger {background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
        .alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}

        .auth-footer{font-size:13px;color:#6b7280;text-align:center;margin-top:24px}
        .auth-footer a{color:#16a34a;font-weight:600}
        .auth-footer a:hover{text-decoration:underline}
        .auth-back{display:inline-flex;align-items:center;gap:5px;font-size:13px;color:#9ca3af;margin-bottom:auto;margin-top:8px}
        .auth-back:hover{color:#374151}

        .right-headline{font-size:28px;font-weight:800;color:#fff;line-height:1.3;text-align:center;position:relative;z-index:1}
        .right-sub{font-size:15px;color:rgba(255,255,255,.75);text-align:center;margin-top:14px;max-width:340px;line-height:1.6;position:relative;z-index:1}
        .right-icon{font-size:72px;margin-bottom:20px;position:relative;z-index:1}

        @media(max-width:800px){.auth-right{display:none}.auth-left{width:100%;border:none}}
        @media(max-width:480px){.auth-left{padding:28px 24px}}
    </style>
</head>
<body>

<div class="auth-left">
    <div class="auth-logo">🌱 Agro<strong>Amigo</strong></div>

    <div style="flex:1;display:flex;flex-direction:column;justify-content:center;max-width:340px;width:100%">

        <h1 class="auth-title">Esqueci minha senha</h1>
        <p class="auth-sub">
            Digite seu e-mail cadastrado e enviaremos um link para criar uma nova senha.
        </p>

        <?php if ($mensagem): ?>
        <div class="alert alert-<?= $tipo_msg ?>">
            <i class="bi bi-<?= $tipo_msg === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>" style="flex-shrink:0;margin-top:1px"></i>
            <span><?= h($mensagem) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($tipo_msg !== 'success'): ?>
        <form method="POST" action="esqueci-senha.php" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       value="<?= h($email_v) ?>"
                       class="form-input" placeholder="seu@email.com"
                       required autocomplete="email" autofocus>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-send me-2"></i>Enviar link de redefinição
            </button>
        </form>
        <?php else: ?>
        <a href="login.php" class="btn-submit" style="text-align:center;display:block">
            <i class="bi bi-box-arrow-in-right me-2"></i>Voltar ao login
        </a>
        <?php endif; ?>

        <div class="auth-footer">
            Lembrou a senha? <a href="login.php">Entrar</a>
        </div>

    </div>

    <a href="index.php" class="auth-back">
        <i class="bi bi-arrow-left"></i> Voltar ao site
    </a>
</div>

<div class="auth-right">
    <div class="right-icon">🔑</div>
    <div class="right-headline">Recupere o acesso<br>à sua conta</div>
    <p class="right-sub">
        Enviaremos um link seguro para o seu e-mail.
        O link expira em 1 hora e só pode ser usado uma vez.
    </p>
</div>

</body>
</html>
