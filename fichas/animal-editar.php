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
if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Token inválido']);
    exit;
}

$pdo       = db();
$animal_id = trim($_POST['animal_id']         ?? '');
$brinco    = trim($_POST['brinco']            ?? '');
$especie   = strtolower(trim($_POST['especie'] ?? ''));
$raca      = trim($_POST['raca']              ?? '');
$data_nasc = ($_POST['data_nascimento']       ?? '') !== '' ? $_POST['data_nascimento'] : null;
$peso_nasc = ($_POST['peso_nascimento_kg']    ?? '') !== '' ? (float)$_POST['peso_nascimento_kg'] : null;

if (!$animal_id || !$brinco || !$especie) {
    echo json_encode(['ok' => false, 'erro' => 'Brinco e espécie são obrigatórios']);
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
    UPDATE animais
    SET brinco = :b, especie = :e, raca = :r, data_nascimento = :dn, peso_nascimento_kg = :pn
    WHERE id = :aid
")->execute([
    'b'   => $brinco,
    'e'   => $especie,
    'r'   => $raca ?: null,
    'dn'  => $data_nasc,
    'pn'  => $peso_nasc,
    'aid' => $animal_id,
]);

echo json_encode(['ok' => true]);
