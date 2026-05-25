<?php
/**
 * SETUP INICIAL DO ADMIN
 * Use apenas uma vez para definir a senha do admin padrão.
 * APAGUE este arquivo após o uso.
 */
declare(strict_types=1);
require_once '../includes/db.php';

// Proteção simples — só funciona de localhost
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Acesso permitido apenas a partir de localhost.');
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha  = $_POST['senha']    ?? '';
    $email_admin = trim($_POST['email'] ?? 'admin@agroamigo.local');

    if (strlen($nova_senha) < 8) {
        $mensagem = '❌ Senha deve ter ao menos 8 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $nova_senha) || !preg_match('/[0-9]/', $nova_senha)) {
        $mensagem = '❌ Senha precisa de letra maiúscula e número.';
    } else {
        $hash = password_hash($nova_senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = db()->prepare("UPDATE usuarios SET senha_hash = :hash WHERE email = :email AND role = 'admin'");
        $stmt->execute(['hash' => $hash, 'email' => $email_admin]);

        if ($stmt->rowCount()) {
            $mensagem = '✅ Senha do admin atualizada! Apague este arquivo agora.';
        } else {
            $mensagem = '⚠️ Admin não encontrado. Verifique o e-mail.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Setup Admin — AgroAmigo</title>
<style>
body{font-family:system-ui,sans-serif;background:#f0fdf4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.box{background:#fff;border-radius:16px;padding:36px;max-width:400px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,.12)}
h1{font-size:20px;margin-bottom:6px;color:#166534}
p.sub{color:#6b7280;font-size:13px;margin-bottom:24px}
label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#374151;margin-bottom:5px}
input{display:block;width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:14px;margin-bottom:14px;font-family:inherit;outline:none}
input:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
button{display:block;width:100%;background:#166534;color:#fff;border:none;border-radius:8px;padding:11px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit}
button:hover{background:#14532d}
.msg{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:18px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.msg.ok{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
.warn{background:#fffbeb;border:1px solid #fcd34d;color:#92400e;border-radius:8px;padding:12px 16px;font-size:12px;margin-top:16px}
</style>
</head>
<body>
<div class="box">
    <h1>🌱 Setup Admin</h1>
    <p class="sub">Define a senha do administrador padrão.<br>Disponível apenas em localhost.</p>

    <?php if ($mensagem): ?>
    <div class="msg <?= str_starts_with($mensagem, '✅') ? 'ok' : '' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="email">E-mail do Admin</label>
        <input type="email" id="email" name="email" value="admin@agroamigo.local" required>

        <label for="senha">Nova Senha</label>
        <input type="password" id="senha" name="senha" placeholder="Mínimo 8 chars, 1 maiúscula, 1 número" required>

        <button type="submit">Definir Senha</button>
    </form>

    <div class="warn">
        ⚠️ <strong>APAGUE este arquivo</strong> após definir a senha.<br>
        Caminho: <code>gestao/setup_admin.php</code>
    </div>
</div>
</body>
</html>
