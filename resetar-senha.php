<?php
declare(strict_types=1);
require_once 'includes/auth.php';

session_init();
security_headers();

if (usuario_logado()) {
    header('Location: index.php');
    exit;
}

$token_raw = trim($_GET['token'] ?? '');
$erro      = '';
$token_ok  = false;
$usuario   = null;

if ($token_raw) {
    $token_hash = hash('sha256', $token_raw);
    $pdo        = db();

    $stmt = $pdo->prepare("
        SELECT r.*, u.email, u.nome
        FROM reset_senha r
        JOIN usuarios u ON u.id = r.usuario_id
        WHERE r.token_hash = :hash
          AND r.usado_em   IS NULL
          AND r.expira_em  > NOW()
        LIMIT 1
    ");
    $stmt->execute(['hash' => $token_hash]);
    $registro = $stmt->fetch();

    if ($registro) {
        $token_ok = true;
        $usuario  = $registro;
    } else {
        $erro = 'Link inválido ou expirado. Solicite um novo link de redefinição.';
    }
} else {
    $erro = 'Nenhum token fornecido.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_ok) {
    csrf_verify();

    $nova_senha = $_POST['nova_senha'] ?? '';
    $conf_senha = $_POST['conf_senha'] ?? '';

    if (strlen($nova_senha) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($nova_senha !== $conf_senha) {
        $erro = 'As senhas não coincidem. Verifique e tente novamente.';
    } else {
        $pdo  = db();
        $hash = password_hash($nova_senha, PASSWORD_BCRYPT, ['cost' => 12]);

        $pdo->prepare("
            UPDATE usuarios
            SET senha_hash = :hash, tentativas_login = 0, bloqueado_ate = NULL
            WHERE id = :id
        ")->execute(['hash' => $hash, 'id' => $usuario['usuario_id']]);

        $pdo->prepare("
            UPDATE reset_senha SET usado_em = NOW() WHERE token_hash = :hash
        ")->execute(['hash' => hash('sha256', $token_raw)]);

        log_acesso('senha_redefinida', $usuario['usuario_id'], $usuario['email']);

        flash('success', 'Senha redefinida com sucesso! Entre com sua nova senha.');
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
    <title>Nova senha — AgroAmigo ATERPEC</title>
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
            padding:24px;position:relative;overflow:hidden;
        }
        body::before{
            content:'';position:fixed;inset:0;
            background-image:url('https://images.unsplash.com/photo-1605000797499-95a51c5269ae?w=1600&q=60&auto=format&fit=crop');
            background-size:cover;background-position:center;
            opacity:.18;
        }

        .card{
            position:relative;z-index:1;
            background:#fff;border-radius:28px;
            width:100%;max-width:460px;
            padding:48px 44px 40px;
            box-shadow:0 32px 80px rgba(0,0,0,.35);
        }

        .card-top{text-align:center;margin-bottom:36px}
        .icon-ring{
            width:76px;height:76px;border-radius:50%;margin:0 auto 20px;
            display:flex;align-items:center;justify-content:center;font-size:34px;
            box-shadow:0 8px 24px rgba(22,163,74,.18);
        }
        .icon-ring.ok{background:linear-gradient(135deg,var(--g100),var(--g50));border:2px solid var(--g200)}
        .icon-ring.err{background:#fef2f2;border:2px solid #fecaca}

        .card-title{font-size:22px;font-weight:800;color:#111827;margin-bottom:6px;letter-spacing:-.3px}
        .card-sub{font-size:14px;color:#6b7280;line-height:1.65;max-width:320px;margin:0 auto}

        .user-badge{
            display:inline-flex;align-items:center;gap:8px;
            background:var(--g50);border:1.5px solid var(--g200);
            border-radius:999px;padding:6px 16px;
            font-size:13px;font-weight:700;color:var(--g800);
            margin-top:12px;
        }

        /* Form */
        .form-group{margin-bottom:20px}
        .form-row-label{
            display:flex;justify-content:space-between;align-items:center;margin-bottom:7px;
        }
        .form-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#374151}
        .form-input{
            display:block;width:100%;border:2px solid #e5e7eb;border-radius:12px;
            padding:13px 48px 13px 16px;font-size:15px;color:#1f2937;
            font-family:inherit;background:#fafafa;outline:none;
            transition:border-color .2s,box-shadow .2s,background .2s;
        }
        .form-input:focus{border-color:var(--g600);box-shadow:0 0 0 4px rgba(22,163,74,.1);background:#fff}
        .form-input.match {border-color:var(--g600)}
        .form-input.no-match{border-color:#ef4444}
        .form-input::placeholder{color:#d1d5db}
        .input-wrap{position:relative}
        .eye-btn{
            position:absolute;right:14px;top:50%;transform:translateY(-50%);
            background:none;border:none;cursor:pointer;color:#9ca3af;font-size:17px;padding:4px;
            line-height:1;
        }
        .eye-btn:hover{color:var(--g600)}

        /* Força da senha */
        .strength-wrap{margin-top:10px}
        .strength-bars{display:flex;gap:5px;margin-bottom:6px}
        .s-bar{height:5px;flex:1;border-radius:3px;background:#e5e7eb;transition:background .3s}
        .s-bar.fraca {background:#ef4444}
        .s-bar.media {background:#f59e0b}
        .s-bar.forte {background:var(--g600)}
        .strength-label{font-size:11px;font-weight:600;color:#9ca3af;transition:color .3s}
        .strength-label.fraca {color:#ef4444}
        .strength-label.media {color:#f59e0b}
        .strength-label.forte {color:var(--g600)}

        /* Match indicator */
        .match-hint{font-size:11px;font-weight:600;margin-top:6px;display:flex;align-items:center;gap:4px}
        .match-hint.ok {color:var(--g600)}
        .match-hint.err{color:#ef4444}

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
        .btn-primary:hover:not(:disabled){opacity:.92;transform:translateY(-1px)}
        .btn-primary:disabled{opacity:.5;cursor:not-allowed}
        .btn-outline{
            display:flex;align-items:center;justify-content:center;gap:8px;
            width:100%;background:transparent;color:var(--g800);
            border:2px solid var(--g200);border-radius:12px;
            padding:13px;font-size:15px;font-weight:700;
            font-family:inherit;cursor:pointer;text-decoration:none;
            transition:all .2s;
        }
        .btn-outline:hover{background:var(--g50)}

        /* Alert */
        .alert{
            border-radius:12px;padding:14px 16px;font-size:14px;
            margin-bottom:24px;display:flex;align-items:flex-start;gap:10px;line-height:1.5;
        }
        .alert-danger{background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b}
        .alert-info  {background:#eff6ff;border:1.5px solid #bfdbfe;color:#1d4ed8}
        .alert i{flex-shrink:0;margin-top:2px;font-size:16px}

        /* Checklist de requisitos */
        .req-list{list-style:none;display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
        .req-item{
            font-size:11px;font-weight:600;padding:4px 10px;border-radius:999px;
            display:flex;align-items:center;gap:4px;
            background:#f3f4f6;color:#9ca3af;transition:all .25s;
        }
        .req-item.ok{background:var(--g50);color:var(--g700);border:1px solid var(--g200)}

        .card-footer{
            display:flex;align-items:center;justify-content:center;gap:16px;
            margin-top:28px;padding-top:24px;border-top:1px solid #f3f4f6;
        }
        .link-muted{font-size:13px;color:#9ca3af;display:flex;align-items:center;gap:5px;text-decoration:none}
        .link-muted:hover{color:#374151}
        .link-green{font-size:13px;color:var(--g600);font-weight:600;text-decoration:none}
        .link-green:hover{text-decoration:underline}
        .sep{width:1px;height:16px;background:#e5e7eb}

        @media(max-width:500px){.card{padding:36px 24px 32px}}
    </style>
</head>
<body>

<div style="display:flex;flex-direction:column;align-items:center;width:100%">

    <a href="index.php" style="position:relative;z-index:1;color:#fff;text-decoration:none;font-size:20px;font-weight:400;margin-bottom:24px;display:flex;align-items:center;gap:8px">
        🌱 Agro<strong>Amigo</strong>
    </a>

    <div class="card">

        <?php if ($token_ok): ?>

        <div class="card-top">
            <div class="icon-ring ok">🔐</div>
            <div class="card-title">Criar nova senha</div>
            <p class="card-sub">Escolha uma senha forte para proteger sua conta.</p>
            <div class="user-badge">
                <i class="bi bi-person-fill" style="color:var(--g600)"></i>
                <?= h($usuario['email']) ?>
            </div>
        </div>

        <?php if ($erro): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= h($erro) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="resetar-senha.php?token=<?= h($token_raw) ?>" novalidate id="form-reset">
            <?= csrf_field() ?>

            <div class="form-group">
                <div class="form-row-label">
                    <label class="form-label" for="nova_senha">Nova senha</label>
                </div>
                <div class="input-wrap">
                    <input type="password" id="nova_senha" name="nova_senha"
                           class="form-input" placeholder="Mínimo 8 caracteres"
                           required autocomplete="new-password" autofocus
                           oninput="avaliar()">
                    <button type="button" class="eye-btn" onclick="toggle('nova_senha','ico1')">
                        <i class="bi bi-eye" id="ico1"></i>
                    </button>
                </div>

                <div class="strength-wrap">
                    <div class="strength-bars">
                        <div class="s-bar" id="sb1"></div>
                        <div class="s-bar" id="sb2"></div>
                        <div class="s-bar" id="sb3"></div>
                    </div>
                    <div class="strength-label" id="slabel"> </div>
                </div>

                <ul class="req-list" id="req-list">
                    <li class="req-item" id="r-len"><i class="bi bi-check-lg"></i> 8+ caracteres</li>
                    <li class="req-item" id="r-case"><i class="bi bi-check-lg"></i> Maiúscula e minúscula</li>
                    <li class="req-item" id="r-num"><i class="bi bi-check-lg"></i> Número ou símbolo</li>
                </ul>
            </div>

            <div class="form-group">
                <div class="form-row-label">
                    <label class="form-label" for="conf_senha">Confirmar senha</label>
                </div>
                <div class="input-wrap">
                    <input type="password" id="conf_senha" name="conf_senha"
                           class="form-input" placeholder="Repita a nova senha"
                           required autocomplete="new-password"
                           oninput="checarMatch()">
                    <button type="button" class="eye-btn" onclick="toggle('conf_senha','ico2')">
                        <i class="bi bi-eye" id="ico2"></i>
                    </button>
                </div>
                <div class="match-hint" id="match-hint" style="display:none"></div>
            </div>

            <button type="submit" class="btn-primary" id="btn-salvar">
                <i class="bi bi-shield-lock-fill"></i>
                Salvar nova senha
            </button>
        </form>

        <?php else: ?>

        <div class="card-top">
            <div class="icon-ring err">⚠️</div>
            <div class="card-title">Link inválido</div>
            <p class="card-sub"><?= h($erro) ?></p>
        </div>

        <a href="esqueci-senha.php" class="btn-outline">
            <i class="bi bi-arrow-repeat"></i>
            Solicitar novo link
        </a>

        <?php endif; ?>

        <div class="card-footer">
            <a href="index.php" class="link-muted"><i class="bi bi-arrow-left"></i> Início</a>
            <div class="sep"></div>
            <a href="login.php" class="link-green">Entrar na conta</a>
        </div>
    </div>
</div>

<script>
function toggle(inputId, iconId) {
    var inp = document.getElementById(inputId);
    var ico = document.getElementById(iconId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function avaliar() {
    var v = document.getElementById('nova_senha').value;
    var bars   = [document.getElementById('sb1'), document.getElementById('sb2'), document.getElementById('sb3')];
    var label  = document.getElementById('slabel');
    var rLen   = document.getElementById('r-len');
    var rCase  = document.getElementById('r-case');
    var rNum   = document.getElementById('r-num');

    var okLen  = v.length >= 8;
    var okCase = /[A-Z]/.test(v) && /[a-z]/.test(v);
    var okNum  = /[0-9]/.test(v) || /[^A-Za-z0-9]/.test(v);

    rLen.className  = 'req-item' + (okLen  ? ' ok' : '');
    rCase.className = 'req-item' + (okCase ? ' ok' : '');
    rNum.className  = 'req-item' + (okNum  ? ' ok' : '');

    var score = (okLen ? 1 : 0) + (okCase ? 1 : 0) + (okNum ? 1 : 0);
    var cls   = ['', 'fraca', 'media', 'forte'][score] || '';
    var txt   = ['', 'Senha fraca', 'Senha razoável', 'Senha forte'][score] || '';

    bars.forEach(function(b, i) {
        b.className = 's-bar' + (i < score ? ' ' + cls : '');
    });
    label.className = 'strength-label' + (cls ? ' ' + cls : '');
    label.textContent = v.length ? txt : '';

    checarMatch();
}

function checarMatch() {
    var v1   = document.getElementById('nova_senha').value;
    var v2   = document.getElementById('conf_senha').value;
    var hint = document.getElementById('match-hint');
    var btn  = document.getElementById('btn-salvar');
    var inp2 = document.getElementById('conf_senha');

    if (!v2) { hint.style.display = 'none'; inp2.className = 'form-input'; btn.disabled = false; return; }

    var ok = v1 === v2;
    hint.style.display = 'flex';
    hint.className = 'match-hint ' + (ok ? 'ok' : 'err');
    hint.innerHTML = ok
        ? '<i class="bi bi-check-circle-fill"></i> Senhas coincidem'
        : '<i class="bi bi-x-circle-fill"></i> Senhas não coincidem';
    inp2.className = 'form-input ' + (ok ? 'match' : 'no-match');
}
</script>
</body>
</html>
