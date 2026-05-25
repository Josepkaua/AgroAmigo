<?php
declare(strict_types=1);
require_once 'includes/auth.php';

session_init();

if (usuario_logado()) {
    header('Location: ' . (is_admin() ? 'gestao/index.php' : 'minha-conta.php'));
    exit;
}

$erro     = '';
$email_v  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
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
                UPDATE usuarios
                SET tentativas_login = :t, bloqueado_ate = :b
                WHERE id = :id
            ")->execute(['t' => $tentativas, 'b' => $bloquear, 'id' => $user['id']]);

            $restam = 5 - $tentativas;
            $erro   = $tentativas === 0
                ? 'Conta bloqueada por 15 minutos após múltiplas tentativas incorretas.'
                : "E-mail ou senha incorretos. ({$restam} tentativa(s) restante(s))";
            log_acesso('login_falhou', $user['id'], $email);
        } else {
            // ✅ Login bem-sucedido
            $pdo->prepare("
                UPDATE usuarios
                SET tentativas_login = 0, bloqueado_ate = NULL, ultimo_login = NOW()
                WHERE id = :id
            ")->execute(['id' => $user['id']]);

            login_usuario($user);
            log_acesso('login_ok', $user['id'], $email);

            $next = filter_var($_GET['next'] ?? '', FILTER_SANITIZE_URL);
            $dest = ($user['role'] === 'admin') ? 'gestao/index.php' : 'minha-conta.php';
            if ($next && str_starts_with($next, '/')) $dest = $next;

            header('Location: ' . $dest);
            exit;
        }
    }
}

$pagina        = 'login';
$titulo_pagina = 'Entrar';
require 'includes/header.php';
?>

<section class="aa-auth-wrap">
    <div class="aa-auth-card">

        <div class="aa-auth-logo">🌱 Agro<strong>Amigo</strong></div>
        <h1 class="aa-auth-title">Entrar na sua conta</h1>
        <p class="aa-auth-sub">Acesse seu painel de controle zootécnico</p>

        <?php if ($erro): ?>
        <div class="aa-alert aa-alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= h($erro) ?>
        </div>
        <?php endif; ?>

        <?php $f = get_flash(); if ($f): ?>
        <div class="aa-alert aa-alert-<?= h($f['tipo']) ?>">
            <i class="bi bi-check-circle-fill me-2"></i><?= h($f['msg']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <?= csrf_field() ?>

            <div class="aa-form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       value="<?= h($email_v) ?>"
                       class="aa-form-input" placeholder="seu@email.com"
                       required autocomplete="email">
            </div>

            <div class="aa-form-group">
                <label for="senha">Senha</label>
                <div class="aa-input-eye">
                    <input type="password" id="senha" name="senha"
                           class="aa-form-input" placeholder="••••••••"
                           required autocomplete="current-password">
                    <button type="button" class="aa-eye-btn" onclick="toggleSenha('senha')">
                        <i class="bi bi-eye" id="eye-senha"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="aa-btn-submit">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
            </button>
        </form>

        <p class="aa-auth-footer">
            Não tem conta?
            <a href="cadastro.php">Cadastre-se gratuitamente</a>
        </p>

    </div>
</section>

<script>
function toggleSenha(id) {
    var inp = document.getElementById(id);
    var ico = document.getElementById('eye-' + id);
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        ico.className = 'bi bi-eye';
    }
}
</script>

<?php require 'includes/footer.php'; ?>
