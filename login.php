<?php
declare(strict_types=1);
require_once 'includes/auth.php';
require_once 'auth/google_config.php';
require_once 'includes/emails.php';

session_init();

if (usuario_logado()) {
    header('Location: ' . (is_admin() ? 'gestao/index.php' : 'minha-conta.php'));
    exit;
}

$erro    = '';
$email_v = '';

security_headers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // Honeypot: bots costumam preencher campos ocultos
    if (!empty($_POST['website'])) {
        // Simula delay e erro genérico — não revela a detecção
        sleep(2);
        $erro = 'E-mail ou senha incorretos.';
        goto fim_post;
    }

    // O campo aceita e-mail OU celular — o produtor rural muitas vezes não tem e-mail
    $ident   = trim($_POST['email'] ?? '');
    $senha   = $_POST['senha']      ?? '';
    $email_v = $ident;

    // MENSAGEM ÚNICA para qualquer falha de autenticação.
    // Antes o site respondia coisas diferentes para "conta suspensa", "conta
    // inativa" e "restam N tentativas" — isso confirmava para qualquer pessoa
    // quais e-mails existem no sistema (enumeração de usuários) e ainda entregava
    // o estado do bloqueio. Agora tudo cai na mesma frase.
    $ERRO_GENERICO = 'E-mail/celular ou senha incorretos.';

    // Hash descartável usado quando o usuário não existe, só para gastar o mesmo
    // tempo de CPU do password_verify real. Sem isso, "usuário inexistente"
    // respondia na hora e "senha errada" demorava ~100ms do bcrypt — a diferença
    // de tempo denunciava quais contas existem.
    $HASH_FALSO = '$2y$12$usuarioInexistenteUsuarioInexistenteUsuarioInexistente.aa';

    if ($ident === '' || $senha === '') {
        $erro = 'Preencha e-mail ou celular, e a senha.';
        goto fim_post;
    }

    $por_telefone = parece_telefone($ident);
    $tel_norm     = $por_telefone ? tel_normalizar($ident) : null;

    if (!$por_telefone && !filter_var($ident, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido ou um celular com DDD.';
        goto fim_post;
    }
    if ($por_telefone && $tel_norm === null) {
        $erro = 'Celular inválido. Use DDD + número, ex.: (99) 98765-4321.';
        goto fim_post;
    }

    if (ip_bloqueado_login()) {
        $erro = 'Muitas tentativas a partir deste dispositivo. Aguarde alguns minutos.';
        log_acesso('bloqueado', null, mb_substr($ident, 0, 190));
        goto fim_post;
    }

    $pdo = db();
    if ($por_telefone) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE telefone_norm = :v LIMIT 1");
        $stmt->execute(['v' => $tel_norm]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE lower(email) = lower(:v) LIMIT 1");
        $stmt->execute(['v' => $ident]);
    }
    $user = $stmt->fetch();

    // Conta criada pelo Google não tem senha local: senha_hash começa com '!'.
    $so_google = $user && str_starts_with((string) $user['senha_hash'], '!');

    $bloqueado = $user
        && !empty($user['bloqueado_ate'])
        && strtotime((string) $user['bloqueado_ate']) > time();

    // password_verify roda SEMPRE, mesmo sem usuário, para o tempo de resposta
    // não diferenciar os casos.
    $senha_ok = password_verify($senha, $user && !$so_google ? $user['senha_hash'] : $HASH_FALSO);

    if (!$user || $so_google || $bloqueado || $user['status'] !== 'ativo' || !$senha_ok) {

        // Só conta tentativa quando a conta existe, está ativa e a senha errou —
        // assim o contador não é usado para descobrir se a conta existe.
        if ($user && !$bloqueado && $user['status'] === 'ativo' && !$so_google) {
            $tentativas = (int) $user['tentativas_login'] + 1;
            $bloquear   = null;
            if ($tentativas >= 5) {
                $bloquear   = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $tentativas = 0;
            }
            $pdo->prepare("UPDATE usuarios SET tentativas_login = :t, bloqueado_ate = :b WHERE id = :id")
                ->execute(['t' => $tentativas, 'b' => $bloquear, 'id' => $user['id']]);
        }

        $erro = $ERRO_GENERICO;
        // Dica útil e que não vaza nada: quem tem conta só-Google não sabe por quê falha.
        if ($so_google) {
            $erro = 'Esta conta entra pelo Google. Use o botão "Entrar com Google" acima.';
        }
        log_acesso('login_falhou', $user['id'] ?? null, mb_substr($ident, 0, 190));
        goto fim_post;
    }

    // ── Autenticado ──
    $pdo->prepare("
        UPDATE usuarios SET tentativas_login = 0, bloqueado_ate = NULL, ultimo_login = NOW()
        WHERE id = :id
    ")->execute(['id' => $user['id']]);

    login_usuario($user);
    log_acesso('login_ok', $user['id'], $user['email']);

    // Aparelho novo nesta conta? Manda o aviso de segurança.
    // Não avisa no primeiro aparelho (seria o próprio cadastro) nem quando já
    // é conhecido — assim o produtor não recebe e-mail toda vez que entra.
    registrar_dispositivo($user);

    $dest = ($user['role'] === 'admin') ? 'gestao/index.php' : 'index.php';
    if (!empty($_SESSION['login_next'])) {
        $next = $_SESSION['login_next'];
        unset($_SESSION['login_next']);
        $seguro = url_interna($next, '');   // rejeita //evil.com, /\evil.com, javascript:
        if ($seguro !== '') $dest = $seguro;
    }

    header('Location: ' . $dest);
    exit;

    fim_post:
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

        /* Entrar com Google */
        .btn-google{
            display:flex;align-items:center;justify-content:center;gap:10px;
            width:100%;padding:11px 14px;margin-bottom:20px;
            border:1.5px solid #e5e7eb;border-radius:10px;background:#fff;
            font-size:14px;font-weight:600;color:#3c4043;font-family:inherit;
            cursor:pointer;transition:background .15s,border-color .15s,box-shadow .15s;
        }
        .btn-google:hover{background:#f8fafc;border-color:#d1d5db;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .btn-google:focus-visible{outline:none;border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.18)}

        .auth-divisor{
            display:flex;align-items:center;gap:12px;
            margin:0 0 20px;color:#9ca3af;font-size:12px;
        }
        .auth-divisor::before,.auth-divisor::after{
            content:'';flex:1;height:1px;background:#e5e7eb;
        }

        /* Form */
        .form-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#374151;margin-bottom:5px}
        .form-group{margin-bottom:18px}
        .form-hint{display:block;margin-top:6px;font-size:11.5px;color:#9ca3af;line-height:1.45}
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

        <?php if (google_configurado()): ?>
        <a href="auth/google.php" class="btn-google">
            <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#4285F4" d="M45.1 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h11.8c-.5 2.7-2 5-4.4 6.6v5.5h7.1c4.2-3.8 6.6-9.5 6.6-16.1z"/>
                <path fill="#34A853" d="M24 46c6 0 11-2 14.6-5.4l-7.1-5.5c-2 1.3-4.5 2.1-7.5 2.1-5.8 0-10.6-3.9-12.4-9.1H4.3v5.7C7.9 41 15.4 46 24 46z"/>
                <path fill="#FBBC05" d="M11.6 28.1c-.5-1.3-.7-2.7-.7-4.1s.3-2.8.7-4.1v-5.7H4.3C2.8 17.1 2 20.4 2 24s.8 6.9 2.3 9.8l7.3-5.7z"/>
                <path fill="#EA4335" d="M24 10.8c3.3 0 6.2 1.1 8.5 3.3l6.3-6.3C35 4.3 30 2 24 2 15.4 2 7.9 7 4.3 14.2l7.3 5.7c1.8-5.2 6.6-9.1 12.4-9.1z"/>
            </svg>
            Entrar com Google
        </a>

        <div class="auth-divisor"><span>ou entre com seus dados</span></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <?= csrf_field() ?>
            <!-- Honeypot: bots preenchem, humanos não veem -->
            <div style="display:none" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">E-mail ou celular</label>
                <input type="text" id="email" name="email"
                       value="<?= h($email_v) ?>"
                       class="form-input" placeholder="seu@email.com ou (99) 98765-4321"
                       required autocomplete="username" autofocus
                       inputmode="email" autocapitalize="none" spellcheck="false">
                <small class="form-hint">Pode entrar com o e-mail ou com o número do seu celular.</small>
            </div>

            <div class="form-group">
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:5px">
                    <label class="form-label" for="senha" style="margin-bottom:0">Senha</label>
                    <a href="esqueci-senha.php" style="font-size:12px;color:#16a34a;font-weight:600;text-decoration:none">
                        Esqueci minha senha
                    </a>
                </div>
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
