<?php
declare(strict_types=1);
require_once '../includes/auth.php';
session_init();
header('Content-Type: application/json; charset=utf-8');

$usuario = usuario_logado();
if (!$usuario || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Acesso negado']);
    exit;
}

$token = $_POST['_csrf'] ?? '';
if (!$token || $token !== ($_SESSION['_csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Token inválido']);
    exit;
}

$pdo       = db();
$animal_id = (int)($_POST['animal_id']      ?? 0);
$vacina    = trim($_POST['vacina']          ?? '');
$data      = trim($_POST['data_aplicacao']  ?? '');
$lote      = trim($_POST['lote']            ?? '');
$reforco   = trim($_POST['data_reforco']    ?? '');

if (!$animal_id || !$vacina || !$data) {
    echo json_encode(['ok' => false, 'erro' => 'Dados incompletos']);
    exit;
}

$chk = $pdo->prepare("
    SELECT a.id FROM animais a
    JOIN propriedades p ON p.id = a.propriedade_id
    WHERE a.id = :aid AND p.usuario_id = :uid AND a.status = 'ativo'
");
$chk->execute(['aid' => $animal_id, 'uid' => $usuario['id']]);
if (!$chk->fetch()) {
    echo json_encode(['ok' => false, 'erro' => 'Animal não encontrado']);
    exit;
}

$pdo->prepare("
    INSERT INTO vacinacoes (animal_id, vacina, data_aplicacao, lote, data_reforco)
    VALUES (:aid, :v, :d, :l, :r)
")->execute([
    'aid' => $animal_id,
    'v'   => $vacina,
    'd'   => $data,
    'l'   => $lote    ?: null,
    'r'   => $reforco ?: null,
]);

echo json_encode(['ok' => true]);
