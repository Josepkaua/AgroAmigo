<?php
declare(strict_types=1);
require_once 'includes/auth.php';

session_init();

if (usuario_logado()) {
    header('Location: minha-conta.php');
    exit;
}

$erros  = [];
$campos = ['nome' => '', 'email' => '', 'telefone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $nome     = trim($_POST['nome']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $tel      = trim($_POST['telefone'] ?? '');
    $senha    = $_POST['senha']         ?? '';
    $confirma = $_POST['confirma']      ?? '';

    $campos = compact('nome', 'email') + ['telefone' => $tel];

    if (!$nome)                                             $erros[] = 'Nome é obrigatório.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (strlen($senha) < 8)                                 $erros[] = 'Senha deve ter pelo menos 8 caracteres.';
    if ($senha !== $confirma)                               $erros[] = 'As senhas não conferem.';
    if (!preg_match('/[A-Z]/', $senha))                     $erros[] = 'Senha precisa de ao menos uma letra maiúscula.';
    if (!preg_match('/[0-9]/', $senha))                     $erros[] = 'Senha precisa de ao menos um número.';

    if (!$erros) {
        $pdo   = db();
        $exist = $pdo->prepare("SELECT id FROM usuarios WHERE email = :e LIMIT 1");
        $exist->execute(['e' => $email]);
        if ($exist->fetch()) $erros[] = 'Este e-mail já está cadastrado.';
    }

    if (!$erros) {
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        db()->prepare("
            INSERT INTO usuarios (nome, email, senha_hash, telefone, role, status)
            VALUES (:nome, :email, :hash, :tel, 'produtor', 'ativo')
        ")->execute(['nome' => $nome, 'email' => $email, 'hash' => $hash, 'tel' => $tel ?: null]);

        log_atividade('usuarios', null, 'criar', null, ['email' => $email, 'nome' => $nome]);
        flash('success', 'Conta criada com sucesso! Faça login para continuar.');
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta — AgroAmigo ATERPEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;min-height:100vh;display:flex;background:#f0fdf4}
        a{text-decoration:none;color:inherit}

        .auth-left{
            width:460px;flex-shrink:0;
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
            padding:48px;position:relative;overflow:hidden;
        }
        .auth-right::before{
            content:'';position:absolute;inset:0;
            background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .auth-logo{font-size:22px;font-weight:400;color:#166534;margin-bottom:32px;display:flex;align-items:center;gap:8px}
        .auth-logo strong{font-weight:800}
        .auth-title{font-size:24px;font-weight:800;color:#111827;margin-bottom:6px}
        .auth-sub{font-size:14px;color:#6b7280;margin-bottom:24px}

        .form-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#374151;margin-bottom:5px}
        .form-group{margin-bottom:15px}
        .form-input{
            display:block;width:100%;
            border:1.5px solid #e5e7eb;border-radius:10px;
            padding:10px 14px;font-size:14px;color:#1f2937;
            font-family:inherit;background:#fff;outline:none;
            transition:border-color .2s,box-shadow .2s;
        }
        .form-input:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
        .form-input::placeholder{color:#c4c9d1}
        .input-wrap{position:relative}
        .input-wrap .form-input{padding-right:42px}
        .eye-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;padding:4px}
        .eye-btn:hover{color:#16a34a}
        .form-hint{font-size:11px;color:#9ca3af;margin-top:3px;display:block}
        .optional{font-weight:400;color:#9ca3af;font-size:10px;text-transform:none;letter-spacing:0}

        .btn-submit{
            display:block;width:100%;
            background:#166534;color:#fff;border:none;border-radius:10px;
            padding:12px;font-size:15px;font-weight:700;
            font-family:inherit;cursor:pointer;
            transition:background .2s,transform .15s;margin-top:4px;
        }
        .btn-submit:hover{background:#14532d;transform:translateY(-1px)}

        .alert{border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px}
        .alert-danger{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
        .alert ul{margin:4px 0 0;padding-left:18px}

        .auth-footer{font-size:13px;color:#6b7280;text-align:center;margin-top:20px}
        .auth-footer a{color:#16a34a;font-weight:600}
        .auth-footer a:hover{text-decoration:underline}
        .auth-back{display:inline-flex;align-items:center;gap:5px;font-size:13px;color:#9ca3af;margin-bottom:auto;margin-top:8px}
        .auth-back:hover{color:#374151}

        .right-headline{font-size:28px;font-weight:800;color:#fff;line-height:1.25;text-align:center;position:relative;z-index:1}
        .right-list{list-style:none;margin-top:24px;display:flex;flex-direction:column;gap:12px;position:relative;z-index:1}
        .right-list li{display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(255,255,255,.85)}
        .right-list li i{font-size:18px;color:rgba(255,255,255,.65)}

        @media(max-width:900px){.auth-right{display:none}.auth-left{width:100%;border:none}}
        @media(max-width:480px){.auth-left{padding:28px 24px}}
    </style>
</head>
<body>

<div class="auth-left">

    <div class="auth-logo">🌱 Agro<strong>Amigo</strong></div>

    <div style="flex:1;display:flex;flex-direction:column;justify-content:center">

        <h1 class="auth-title">Criar conta gratuita</h1>
        <p class="auth-sub">Comece a registrar seus animais e fichas online</p>

        <?php if ($erros): ?>
        <div class="alert alert-danger">
            <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Corrija os erros:</strong>
            <ul>
                <?php foreach ($erros as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="cadastro.php" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome"
                       value="<?= h($campos['nome']) ?>"
                       class="form-input" placeholder="João da Silva"
                       required autocomplete="name" autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       value="<?= h($campos['email']) ?>"
                       class="form-input" placeholder="seu@email.com"
                       required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="telefone">
                    WhatsApp <span class="optional">(opcional)</span>
                </label>
                <input type="tel" id="telefone" name="telefone"
                       value="<?= h($campos['telefone']) ?>"
                       class="form-input" placeholder="(99) 9 9999-0000"
                       autocomplete="tel">
            </div>

            <div class="form-group">
                <label class="form-label" for="senha">Senha</label>
                <div class="input-wrap">
                    <input type="password" id="senha" name="senha"
                           class="form-input" placeholder="Mínimo 8 caracteres"
                           required autocomplete="new-password">
                    <button type="button" class="eye-btn" onclick="toggleSenha('senha','eye-s')">
                        <i class="bi bi-eye" id="eye-s"></i>
                    </button>
                </div>
                <span class="form-hint">Ao menos 8 caracteres, 1 maiúscula e 1 número.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirma">Confirmar senha</label>
                <div class="input-wrap">
                    <input type="password" id="confirma" name="confirma"
                           class="form-input" placeholder="••••••••"
                           required autocomplete="new-password">
                    <button type="button" class="eye-btn" onclick="toggleSenha('confirma','eye-c')">
                        <i class="bi bi-eye" id="eye-c"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-person-plus-fill" style="margin-right:8px"></i>Criar conta
            </button>
        </form>

        <div class="auth-footer">
            Já tem conta? <a href="login.php">Fazer login</a>
        </div>

    </div>

    <a href="index.php" class="auth-back">
        <i class="bi bi-arrow-left"></i> Voltar ao site
    </a>

</div>

<div class="auth-right">
    <div class="right-headline">Por que criar<br>uma conta?</div>
    <ul class="right-list">
        <li><i class="bi bi-cloud-check-fill"></i> Seus dados salvos na nuvem</li>
        <li><i class="bi bi-phone-fill"></i> Acesse de qualquer celular</li>
        <li><i class="bi bi-shield-check-fill"></i> 100% gratuito e seguro</li>
        <li><i class="bi bi-file-earmark-pdf-fill"></i> Baixe fichas em PDF</li>
        <li><i class="bi bi-graph-up-arrow"></i> Acompanhe o crescimento do rebanho</li>
        <li><i class="bi bi-bell-fill"></i> Controle vacinas e tratamentos</li>
    </ul>
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
