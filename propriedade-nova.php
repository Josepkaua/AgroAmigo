<?php
declare(strict_types=1);
require_once 'includes/auth.php';
$usuario = require_login('login.php');

$erro  = '';
$dados = ['nome' => '', 'municipio' => '', 'uf' => '', 'area_ha' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $dados['nome']      = trim($_POST['nome']      ?? '');
    $dados['municipio'] = trim($_POST['municipio'] ?? '');
    $dados['uf']        = strtoupper(trim($_POST['uf'] ?? ''));
    $dados['area_ha']   = trim($_POST['area_ha']   ?? '');

    if (!$dados['nome']) {
        $erro = 'O nome da propriedade é obrigatório.';
    } elseif ($dados['uf'] && !preg_match('/^[A-Z]{2}$/', $dados['uf'])) {
        $erro = 'UF deve ter 2 letras (ex: MA).';
    } else {
        $pdo  = db();
        $stmt = $pdo->prepare("
            INSERT INTO propriedades (usuario_id, nome, municipio, uf, area_ha)
            VALUES (:uid, :nome, :municipio, :uf, :area_ha)
        ");
        $stmt->execute([
            'uid'       => $usuario['id'],
            'nome'      => $dados['nome'],
            'municipio' => $dados['municipio'] ?: null,
            'uf'        => $dados['uf']        ?: null,
            'area_ha'   => $dados['area_ha']   !== '' ? (float)$dados['area_ha'] : null,
        ]);

        flash('success', 'Propriedade "' . $dados['nome'] . '" cadastrada com sucesso!');
        header('Location: index.php');
        exit;
    }
}

$ufs = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Propriedade — AgroAmigo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',system-ui,sans-serif;min-height:100vh;background:#f0fdf4;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:20px;box-shadow:0 8px 40px rgba(0,0,0,.10);width:100%;max-width:480px;padding:40px 40px 36px}
        .logo{font-size:18px;font-weight:400;color:#166534;display:flex;align-items:center;gap:8px;margin-bottom:28px}
        .logo strong{font-weight:800}
        h1{font-size:22px;font-weight:800;color:#111827;margin-bottom:4px}
        .sub{font-size:13px;color:#6b7280;margin-bottom:28px}
        .form-group{margin-bottom:18px}
        label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#374151;margin-bottom:5px}
        .form-control{display:block;width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:11px 14px;font-size:14px;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;background:#fff}
        .form-control:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
        .hint{font-size:11px;color:#9ca3af;margin-top:4px}
        .row-2{display:grid;grid-template-columns:1fr auto;gap:12px}
        .btn-submit{width:100%;background:#166534;color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .2s;margin-top:6px}
        .btn-submit:hover{background:#14532d}
        .btn-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-top:16px}
        .btn-back:hover{color:#16a34a}
        .alert{border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:20px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;display:flex;gap:8px;align-items:flex-start}
    </style>
</head>
<body>
<div class="card">
    <div class="logo">🌱 Agro<strong>Amigo</strong></div>

    <h1>🏡 Nova Propriedade</h1>
    <p class="sub">Cadastre os dados básicos da sua propriedade rural.</p>

    <?php if ($erro): ?>
    <div class="alert">
        <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;margin-top:1px"></i>
        <span><?= h($erro) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="propriedade-nova.php" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="nome">Nome da Propriedade *</label>
            <input type="text" id="nome" name="nome" class="form-control"
                   value="<?= h($dados['nome']) ?>"
                   placeholder="Ex: Fazenda São João" required autofocus>
        </div>

        <div class="row-2">
            <div class="form-group" style="margin-bottom:0">
                <label for="municipio">Município</label>
                <input type="text" id="municipio" name="municipio" class="form-control"
                       value="<?= h($dados['municipio']) ?>"
                       placeholder="Ex: Bacabal">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label for="uf">UF</label>
                <select id="uf" name="uf" class="form-control" style="width:80px">
                    <option value="">—</option>
                    <?php foreach ($ufs as $u): ?>
                    <option value="<?= $u ?>" <?= $dados['uf'] === $u ? 'selected' : '' ?>><?= $u ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="margin-bottom:18px"></div>

        <div class="form-group">
            <label for="area_ha">Área (hectares)</label>
            <input type="number" id="area_ha" name="area_ha" class="form-control"
                   value="<?= h($dados['area_ha']) ?>"
                   placeholder="Ex: 25.5" step="0.01" min="0">
            <span class="hint">Opcional — deixe em branco se não souber</span>
        </div>

        <button type="submit" class="btn-submit">
            <i class="bi bi-plus-circle me-2"></i> Cadastrar Propriedade
        </button>
    </form>

    <a href="index.php" class="btn-back">
        <i class="bi bi-arrow-left"></i> Voltar ao painel
    </a>
</div>
</body>
</html>
