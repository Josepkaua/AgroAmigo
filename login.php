<?php
declare(strict_types=1);
require_once 'includes/auth.php';

session_init();

if (usuario_logado()) {
    header('Location: ' . (is_admin() ? 'gestao/index.php' : 'minha-conta.php'));
    exit;
}

$erro    = '';
$email_v = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email   = trim($_POST['email'] ?? '');
    $senha   = $_POST['senha']      ?? '';
    $email_v = $email;

    if (!$email || !$senha) {
        $erro = 'Preencha e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } else {
        $pdo  = db();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $erro = 'E-mail ou senha incorretos.';
            log_acesso('login_falhou', null, $email);
        } elseif ($user['status'] === 'suspenso') {
            $erro = 'Conta suspensa. Entre em contato com o suporte.';
        } elseif ($user['status'] === 'inativo') {
            $erro = 'Conta inativa.';
        } elseif (!empty($user['bloqueado_ate']) && strtotime($user['bloqueado_ate']) > time()) {
            $min  = (int) ceil((strtotime($user['bloqueado_ate']) - time()) / 60);
            $erro = "Muitas tentativas incorretas. Tente novamente em {$min} minuto(s).";
            log_acesso('bloqueado', $user['id'], $email);
        } elseif (!password_verify($senha, $user['senha_hash'])) {
            $tentativas = (int)$user['tentativas_login'] + 1;
            $bloquear   = null;
            if ($tentativas >= 5) {
                $bloquear   = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $tentativas = 0;
            }
            $pdo->prepare("
                UPDATE usuarios SET tentativas_login = :t, bloqueado_ate = :b WHERE id = :id
            ")->execute(['t' => $tentativas, 'b' => $bloquear, 'id' => $user['id']]);

            $restam = max(0, 5 - $tentativas);
            $erro   = $tentativas === 0
                ? 'Conta bloqueada por 15 minutos após múltiplas tentativas.'
                : "E-mail ou senha incorretos. ({$restam} tentativa(s) restante(s))";
            log_acesso('login_falhou', $user['id'], $email);
        } else {
            $pdo->prepare("
                UPDATE usuarios SET tentativas_login = 0, bloqueado_ate = NULL, ultimo_login = NOW()
                WHERE id = :id
            ")->execute(['id' => $user['id']]);

            login_usuario($user);
            log_acesso('login_ok', $user['id'], $email);

            $dest = ($user['role'] === 'admin') ? 'gestao/index.php' : 'index.php';

            // Redireciona para onde o usuário queria ir antes de ser barrado
            if (!empty($_SESSION['login_next'])) {
                $dest = $_SESSION['login_next'];
                unset($_SESSION['login_next']);
            }

            header('Location: ' . $dest);
            exit;
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — AgroAmigo ATERPEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;min-height:100vh;display:flex;background:#f0fdf4}
        a{text-decoration:none;color:inherit}

        /* Split layout */
        .auth-left{
            width:420px;flex-shrink:0;
            background:#fff;
            display:flex;flex-direction:column;
            padding:40px 48px;
            min-height:100vh;
            border-right:1px solid #e5e7eb;
        }
        .auth-right{
            flex:1;
            background:linear-gradient(135deg,#166534 0%,#15803d 50%,#14532d 100%);
            display:flex;flex-direction:column;align-items:center;justify-content:center;
            padding:48px;
            position:relative;overflow:hidden;
        }
        .auth-right::before{
            content:'';position:absolute;inset:0;
            background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Logo */
        .auth-logo{
            font-size:22px;font-weight:400;color:#166534;margin-bottom:40px;
            display:flex;align-items:center;gap:8px;
        }
        .auth-logo strong{font-weight:800}

        .auth-title{font-size:26px;font-weight:800;color:#111827;margin-bottom:6px}
        .auth-sub{font-size:14px;color:#6b7280;margin-bottom:28px}

        /* Form */
        .form-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#374151;margin-bottom:5px}
        .form-group{margin-bottom:18px}
        .form-input{
            display:block;width:100%;
            border:1.5px solid #e5e7eb;border-radius:10px;
            padding:11px 14px;font-size:14px;color:#1f2937;
            font-family:inherit;background:#fff;outline:none;
            transition:border-color .2s,box-shadow .2s;
        }
        .form-input:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
        .form-input::placeholder{color:#c4c9d1}
        .input-wrap{position:relative}
        .input-wrap .form-input{padding-right:42px}
        .eye-btn{
            position:absolute;right:12px;top:50%;transform:translateY(-50%);
            background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;padding:4px;
        }
        .eye-btn:hover{color:#16a34a}
        .form-hint{font-size:11px;color:#9ca3af;margin-top:4px;display:block}

        .btn-submit{
            display:block;width:100%;
            background:#166534;color:#fff;border:none;border-radius:10px;
            padding:13px;font-size:15px;font-weight:700;
            font-family:inherit;cursor:pointer;
            transition:background .2s,transform .15s;margin-top:4px;
        }
        .btn-submit:hover{background:#14532d;transform:translateY(-1px)}

        /* Alert */
        .alert{border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:20px;display:flex;align-items:flex-start;gap:8px}
        .alert-danger {background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
        .alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}

        /* Bottom links */
        .auth-footer{font-size:13px;color:#6b7280;text-align:center;margin-top:24px}
        .auth-footer a{color:#16a34a;font-weight:600}
        .auth-footer a:hover{text-decoration:underline}
        .auth-back{display:inline-flex;align-items:center;gap:5px;font-size:13px;color:#9ca3af;margin-bottom:auto;margin-top:8px}
        .auth-back:hover{color:#374151}

        /* Lado direito */
        .right-headline{font-size:32px;font-weight:800;color:#fff;line-height:1.2;text-align:center;position:relative;z-index:1}
        .right-sub{font-size:15px;color:rgba(255,255,255,.7);text-align:center;margin-top:12px;max-width:340px;line-height:1.6;position:relative;z-index:1}
        .right-badges{display:flex;flex-wrap:wrap;gap:10px;margin-top:32px;justify-content:center;position:relative;z-index:1}
        .right-badge{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:8px 16px;font-size:13px;font-weight:600;color:#fff;display:flex;align-items:center;gap:6px}

        /* Responsive */
        @media(max-width:800px){
            .auth-right{display:none}
            .auth-left{width:100%;border:none}
        }
        @media(max-width:480px){
            .auth-left{padding:28px 24px}
        }
    </style>
</head>
<body>

<!-- LADO ESQUERDO — FORMULÁRIO -->
<div class="auth-left">

    <div class="auth-logo">🌱 Agro<strong>Amigo</strong></div>

    <div style="flex:1;display:flex;flex-direction:column;justify-content:center;max-width:340px;width:100%">

        <h1 class="auth-title">Bem-vindo de volta</h1>
        <p class="auth-sub">Entre na sua conta para gerenciar seus animais</p>

        <?php if ($erro): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;margin-top:1px"></i>
            <span><?= h($erro) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['tipo'] === 'success' ? 'success' : 'danger' ?>">
            <i class="bi bi-check-circle-fill" style="flex-shrink:0;margin-top:1px"></i>
            <span><?= h($flash['msg']) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       value="<?= h($email_v) ?>"
                       class="form-input" placeholder="seu@email.com"
                       required autocomplete="email" autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="senha">Senha</label>
                <div class="input-wrap">
                    <input type="password" id="senha" name="senha"
                           class="form-input" placeholder="••••••••"
                           required autocomplete="current-password">
                    <button type="button" class="eye-btn" onclick="toggleSenha('senha','eye-senha')">
                        <i class="bi bi-eye" id="eye-senha"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-box-arrow-in-right" style="margin-right:8px"></i>Entrar
            </button>
        </form>

        <div class="auth-footer">
            Não tem conta? <a href="cadastro.php">Criar conta gratuita</a>
        </div>

    </div>

    <a href="index.php" class="auth-back">
        <i class="bi bi-arrow-left"></i> Voltar ao site
    </a>

</div>

<!-- LADO DIREITO — VISUAL -->
<div class="auth-right">
    <div class="right-headline">
        🌱 Controle seu rebanho<br>de qualquer lugar
    </div>
    <p class="right-sub">
        Registre pesagens, vacinações, ocorrências sanitárias e muito mais.
        Acesse suas fichas a qualquer hora, direto do celular.
    </p>
    <div class="right-badges">
        <div class="right-badge">🐄 Bovinos</div>
        <div class="right-badge">🐐 Caprinos</div>
        <div class="right-badge">🐑 Ovinos</div>
        <div class="right-badge">🐔 Aves</div>
        <div class="right-badge">🐷 Suínos</div>
        <div class="right-badge">📋 Fichas PDF</div>
    </div>
</div>

<script>
function toggleSenha(inputId, iconId) {
    var inp = document.getElementById(inputId);
    var ico = document.getElementById(iconId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
