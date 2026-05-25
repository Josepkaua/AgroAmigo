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
            $recente = $pdo->prepare("
                SELECT id FROM reset_senha
                WHERE usuario_id = :uid AND criado_em > NOW() - INTERVAL '5 minutes' AND usado_em IS NULL
                LIMIT 1
            ");
            $recente->execute(['uid' => $user['id']]);

            if (!$recente->fetch()) {
                $pdo->prepare("
                    DELETE FROM reset_senha WHERE usuario_id = :uid AND usado_em IS NULL
                ")->execute(['uid' => $user['id']]);

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

        $mensagem = 'Se este e-mail estiver cadastrado, você receberá as instruções em breve.';
        $tipo_msg = 'success';
        $email_v  = '';
    }
}

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root{
            --g50:#f0fdf4;--g100:#dcfce7;--g200:#bbf7d0;
            --g600:#16a34a;--g700:#15803d;--g800:#166534;--g900:#14532d;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Inter',system-ui,sans-serif;min-height:100vh;
            background:var(--g900);
            display:flex;align-items:center;justify-content:center;
            padding:24px;
            position:relative;overflow:hidden;
        }
        body::before{
            content:'';position:fixed;inset:0;
            background-image:url('https://images.unsplash.com/photo-1605000797499-95a51c5269ae?w=1600&q=60&auto=format&fit=crop');
            background-size:cover;background-position:center;
            opacity:.18;
        }

        /* Card principal */
        .card{
            position:relative;z-index:1;
            background:#fff;border-radius:28px;
            width:100%;max-width:460px;
            padding:48px 44px 40px;
            box-shadow:0 32px 80px rgba(0,0,0,.35);
        }

        /* Topo */
        .card-top{text-align:center;margin-bottom:36px}
        .icon-ring{
            width:76px;height:76px;border-radius:50%;margin:0 auto 20px;
            background:linear-gradient(135deg,var(--g100),var(--g50));
            border:2px solid var(--g200);
            display:flex;align-items:center;justify-content:center;
            font-size:34px;
            box-shadow:0 8px 24px rgba(22,163,74,.18);
        }
        .card-title{font-size:22px;font-weight:800;color:#111827;margin-bottom:6px;letter-spacing:-.3px}
        .card-sub{font-size:14px;color:#6b7280;line-height:1.65;max-width:320px;margin:0 auto}

        /* Form */
        .form-group{margin-bottom:20px}
        .form-label{
            display:block;font-size:11px;font-weight:700;
            text-transform:uppercase;letter-spacing:.6px;color:#374151;margin-bottom:7px;
        }
        .form-input{
            display:block;width:100%;
            border:2px solid #e5e7eb;border-radius:12px;
            padding:13px 16px;font-size:15px;color:#1f2937;
            font-family:inherit;background:#fafafa;outline:none;
            transition:border-color .2s,box-shadow .2s,background .2s;
        }
        .form-input:focus{
            border-color:var(--g600);
            box-shadow:0 0 0 4px rgba(22,163,74,.1);
            background:#fff;
        }
        .form-input::placeholder{color:#d1d5db}

        /* Botão */
        .btn-primary{
            display:flex;align-items:center;justify-content:center;gap:8px;
            width:100%;background:linear-gradient(135deg,var(--g700),var(--g900));
            color:#fff;border:none;border-radius:12px;
            padding:14px;font-size:15px;font-weight:700;
            font-family:inherit;cursor:pointer;
            transition:opacity .2s,transform .15s;
            box-shadow:0 4px 16px rgba(20,83,45,.35);
        }
        .btn-primary:hover{opacity:.92;transform:translateY(-1px)}

        .btn-outline{
            display:flex;align-items:center;justify-content:center;gap:8px;
            width:100%;background:transparent;
            color:var(--g800);border:2px solid var(--g200);border-radius:12px;
            padding:13px;font-size:15px;font-weight:700;
            font-family:inherit;cursor:pointer;text-decoration:none;
            transition:all .2s;
        }
        .btn-outline:hover{background:var(--g50);border-color:var(--g300)}

        /* Alert */
        .alert{
            border-radius:12px;padding:14px 16px;font-size:14px;
            margin-bottom:24px;display:flex;align-items:flex-start;gap:10px;
            line-height:1.5;
        }
        .alert-danger {background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b}
        .alert-success{background:#f0fdf4;border:1.5px solid var(--g200);color:var(--g800)}
        .alert i{flex-shrink:0;margin-top:2px;font-size:16px}

        /* Rodapé do card */
        .card-footer{
            display:flex;align-items:center;justify-content:center;gap:16px;
            margin-top:28px;padding-top:24px;
            border-top:1px solid #f3f4f6;
        }
        .link-muted{font-size:13px;color:#9ca3af;display:flex;align-items:center;gap:5px;text-decoration:none}
        .link-muted:hover{color:#374151}
        .link-green{font-size:13px;color:var(--g600);font-weight:600;text-decoration:none}
        .link-green:hover{text-decoration:underline}
        .sep{width:1px;height:16px;background:#e5e7eb}

        /* Passos informativos (abaixo do card) */
        .steps{
            position:relative;z-index:1;
            display:flex;gap:20px;justify-content:center;flex-wrap:wrap;
            margin-top:24px;
        }
        .step{
            background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);
            border-radius:12px;padding:12px 16px;
            display:flex;align-items:center;gap:10px;
            backdrop-filter:blur(8px);
        }
        .step-num{
            width:28px;height:28px;border-radius:50%;
            background:rgba(255,255,255,.2);color:#fff;
            font-size:12px;font-weight:800;
            display:flex;align-items:center;justify-content:center;flex-shrink:0;
        }
        .step-txt{font-size:12px;color:rgba(255,255,255,.85);font-weight:500;line-height:1.4}

        @media(max-width:500px){
            .card{padding:36px 24px 32px}
            .steps{display:none}
        }
    </style>
</head>
<body>

<div style="display:flex;flex-direction:column;align-items:center;width:100%">

    <!-- Logo acima do card -->
    <a href="index.php" style="position:relative;z-index:1;color:#fff;text-decoration:none;font-size:20px;font-weight:400;margin-bottom:24px;display:flex;align-items:center;gap:8px">
        🌱 Agro<strong>Amigo</strong>
    </a>

    <div class="card">
        <div class="card-top">
            <div class="icon-ring">📧</div>
            <div class="card-title">Recuperar acesso</div>
            <p class="card-sub">
                Digite seu e-mail e enviaremos um link seguro para criar uma nova senha.
            </p>
        </div>

        <?php if ($mensagem): ?>
        <div class="alert alert-<?= $tipo_msg ?>">
            <i class="bi bi-<?= $tipo_msg === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
            <span><?= h($mensagem) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($tipo_msg !== 'success'): ?>
        <form method="POST" action="esqueci-senha.php" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="email">E-mail cadastrado</label>
                <input type="email" id="email" name="email"
                       value="<?= h($email_v) ?>"
                       class="form-input" placeholder="seu@email.com"
                       required autocomplete="email" autofocus>
            </div>

            <button type="submit" class="btn-primary">
                <i class="bi bi-send-fill"></i>
                Enviar link de redefinição
            </button>
        </form>
        <?php else: ?>
        <a href="login.php" class="btn-outline">
            <i class="bi bi-box-arrow-in-right"></i>
            Ir para o login
        </a>
        <?php endif; ?>

        <div class="card-footer">
            <a href="index.php" class="link-muted"><i class="bi bi-arrow-left"></i> Início</a>
            <div class="sep"></div>
            <a href="login.php" class="link-green">Entrar na conta</a>
            <div class="sep"></div>
            <a href="cadastro.php" class="link-green">Criar conta</a>
        </div>
    </div>

    <!-- Passos -->
    <div class="steps">
        <div class="step">
            <div class="step-num">1</div>
            <div class="step-txt">Digite seu e-mail</div>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <div class="step-txt">Acesse o link enviado</div>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <div class="step-txt">Crie uma nova senha</div>
        </div>
    </div>

</div>
</body>
</html>
