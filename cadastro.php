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

    if (!$nome)                                  $erros[] = 'Nome é obrigatório.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
                                                 $erros[] = 'E-mail inválido.';
    if (strlen($senha) < 8)                      $erros[] = 'Senha deve ter pelo menos 8 caracteres.';
    if ($senha !== $confirma)                    $erros[] = 'As senhas não conferem.';
    if (!preg_match('/[A-Z]/', $senha))          $erros[] = 'Senha precisa de ao menos uma letra maiúscula.';
    if (!preg_match('/[0-9]/', $senha))          $erros[] = 'Senha precisa de ao menos um número.';

    if (!$erros) {
        $pdo  = db();
        $existe = $pdo->prepare("SELECT id FROM usuarios WHERE email = :e LIMIT 1");
        $existe->execute(['e' => $email]);
        if ($existe->fetch()) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }
    }

    if (!$erros) {
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha_hash, telefone, role, status)
            VALUES (:nome, :email, :hash, :tel, 'produtor', 'ativo')
        ")->execute(['nome' => $nome, 'email' => $email, 'hash' => $hash, 'tel' => $tel ?: null]);

        $uid = $pdo->lastInsertId() ?: null;
        log_atividade('usuarios', null, 'criar', null, ['email' => $email, 'nome' => $nome]);

        flash('success', 'Conta criada! Faça login para continuar.');
        header('Location: login.php');
        exit;
    }
}

$pagina        = 'cadastro';
$titulo_pagina = 'Criar Conta';
require 'includes/header.php';
?>

<section class="aa-auth-wrap">
    <div class="aa-auth-card" style="max-width:480px;">

        <div class="aa-auth-logo">🌱 Agro<strong>Amigo</strong></div>
        <h1 class="aa-auth-title">Criar conta gratuita</h1>
        <p class="aa-auth-sub">Comece a registrar seus animais e fichas online</p>

        <?php if ($erros): ?>
        <div class="aa-alert aa-alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <ul class="mb-0 ps-3">
                <?php foreach ($erros as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="cadastro.php" novalidate>
            <?= csrf_field() ?>

            <div class="aa-form-group">
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome"
                       value="<?= h($campos['nome']) ?>"
                       class="aa-form-input" placeholder="João da Silva"
                       required autocomplete="name">
            </div>

            <div class="aa-form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       value="<?= h($campos['email']) ?>"
                       class="aa-form-input" placeholder="seu@email.com"
                       required autocomplete="email">
            </div>

            <div class="aa-form-group">
                <label for="telefone">WhatsApp <span style="font-weight:400;color:#9ca3af">(opcional)</span></label>
                <input type="tel" id="telefone" name="telefone"
                       value="<?= h($campos['telefone']) ?>"
                       class="aa-form-input" placeholder="(99) 9 9999-0000"
                       autocomplete="tel">
            </div>

            <div class="aa-form-group">
                <label for="senha">Senha</label>
                <div class="aa-input-eye">
                    <input type="password" id="senha" name="senha"
                           class="aa-form-input" placeholder="Mínimo 8 caracteres"
                           required autocomplete="new-password">
                    <button type="button" class="aa-eye-btn" onclick="toggleSenha('senha')">
                        <i class="bi bi-eye" id="eye-senha"></i>
                    </button>
                </div>
                <small class="aa-form-hint">Ao menos 8 caracteres, uma maiúscula e um número.</small>
            </div>

            <div class="aa-form-group">
                <label for="confirma">Confirmar senha</label>
                <div class="aa-input-eye">
                    <input type="password" id="confirma" name="confirma"
                           class="aa-form-input" placeholder="••••••••"
                           required autocomplete="new-password">
                    <button type="button" class="aa-eye-btn" onclick="toggleSenha('confirma')">
                        <i class="bi bi-eye" id="eye-confirma"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="aa-btn-submit">
                <i class="bi bi-person-plus-fill me-2"></i>Criar conta
            </button>
        </form>

        <p class="aa-auth-footer">
            Já tem conta? <a href="login.php">Entrar</a>
        </p>

    </div>
</section>

<script>
function toggleSenha(id) {
    var inp = document.getElementById(id);
    var ico = document.getElementById('eye-' + id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>

<?php require 'includes/footer.php'; ?>
