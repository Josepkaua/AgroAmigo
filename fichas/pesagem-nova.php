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

// CSRF manual (requisição AJAX)
$token = $_POST['_csrf'] ?? '';
if (!$token || $token !== ($_SESSION['_csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Token inválido']);
    exit;
}

$pdo       = db();
$animal_id = (int)($_POST['animal_id']   ?? 0);
$data      = trim($_POST['data_pesagem'] ?? '');
$peso      = trim($_POST['peso_kg']      ?? '');
$obs       = trim($_POST['observacoes']  ?? '');

if (!$animal_id || !$data || $peso === '') {
    echo json_encode(['ok' => false, 'erro' => 'Dados incompletos']);
    exit;
}

// Verifica pertencimento
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
    INSERT INTO pesagens (animal_id, data_pesagem, peso_kg, observacoes)
    VALUES (:aid, :d, :p, :o)
")->execute([
    'aid' => $animal_id,
    'd'   => $data,
    'p'   => (float)$peso,
    'o'   => $obs ?: null,
]);

echo json_encode(['ok' => true]);
