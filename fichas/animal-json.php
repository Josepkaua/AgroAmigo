<?php
declare(strict_types=1);
require_once '../includes/auth.php';
session_init();
header('Content-Type: application/json; charset=utf-8');

$usuario = usuario_logado();
if (!$usuario) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

$pdo = db();
$id  = trim($_GET['id'] ?? '');

// Verifica que o animal pertence ao usuário
$stmt = $pdo->prepare("
    SELECT a.*, p.nome AS prop_nome
    FROM animais a
    JOIN propriedades p ON p.id = a.propriedade_id
    WHERE a.id = :id AND p.usuario_id = :uid AND a.status = 'ativo'
");
$stmt->execute(['id' => $id, 'uid' => $usuario['id']]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    http_response_code(404);
    echo json_encode(['erro' => 'Não encontrado']);
    exit;
}

$pes = $pdo->prepare("
    SELECT id, data_pesagem, peso_kg, observacao
    FROM pesagens WHERE animal_id = :id ORDER BY data_pesagem DESC LIMIT 50
");
$pes->execute(['id' => $id]);

$vac = $pdo->prepare("
    SELECT id, nome_vacina, data_aplicacao, lote, proximo_reforco
    FROM vacinacoes WHERE animal_id = :id ORDER BY data_aplicacao DESC LIMIT 50
");
$vac->execute(['id' => $id]);

echo json_encode([
    'animal'     => $animal,
    'pesagens'   => $pes->fetchAll(PDO::FETCH_ASSOC),
    'vacinacoes' => $vac->fetchAll(PDO::FETCH_ASSOC),
], JSON_UNESCAPED_UNICODE);
