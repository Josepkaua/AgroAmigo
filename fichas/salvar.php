<?php
declare(strict_types=1);
require_once '../includes/auth.php';
session_init();

header('Content-Type: application/json; charset=utf-8');

$usuario = usuario_logado();
if (!$usuario) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Não autenticado.']);
    exit;
}

// CSRF
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Token inválido.']);
    exit;
}

$tipos_validos = ['zootecnica', 'vacinacao', 'mortalidade', 'controle'];
$tipo = trim($_POST['tipo'] ?? '');
if (!in_array($tipo, $tipos_validos, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Tipo de ficha inválido.']);
    exit;
}

$dados_raw = $_POST['dados'] ?? '';
$dados = json_decode($dados_raw, true);
if (!is_array($dados)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Dados inválidos.']);
    exit;
}

$uid = $usuario['id'];
$pdo = db();

// Verifica se já existe uma ficha salva desse tipo para o usuário
$stmt = $pdo->prepare(
    "SELECT id, nome_arquivo, caminho_json FROM fichas_salvas WHERE usuario_id = :uid AND tipo = :tipo"
);
$stmt->execute(['uid' => $uid, 'tipo' => $tipo]);
$existente = $stmt->fetch();

if ($existente) {
    // Atualiza o arquivo existente (mesmo caminho/nome)
    $caminho    = $existente['caminho_json'];
    $arquivo    = __DIR__ . '/../' . $caminho;
    $arquivo_ok = file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if ($arquivo_ok === false) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'erro' => 'Falha ao gravar arquivo.']);
        exit;
    }

    $pdo->prepare(
        "UPDATE fichas_salvas SET dados = :d, salvo_em = NOW() WHERE id = :id"
    )->execute(['d' => json_encode($dados, JSON_UNESCAPED_UNICODE), 'id' => $existente['id']]);

} else {
    // Cria novo arquivo com nome aleatório dentro de pasta por usuário
    $user_hash  = substr(hash('sha256', $uid), 0, 16);
    $nome       = bin2hex(random_bytes(12)); // 24 chars hex
    $dir_rel    = 'uploads/fichas/' . $user_hash;
    $dir        = __DIR__ . '/../' . $dir_rel;

    if (!is_dir($dir) && !mkdir($dir, 0750, true)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'erro' => 'Falha ao criar diretório.']);
        exit;
    }

    $caminho    = $dir_rel . '/' . $nome . '.json';
    $arquivo    = __DIR__ . '/../' . $caminho;
    $arquivo_ok = file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if ($arquivo_ok === false) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'erro' => 'Falha ao gravar arquivo.']);
        exit;
    }

    $pdo->prepare(
        "INSERT INTO fichas_salvas (usuario_id, tipo, nome_arquivo, caminho_json, dados)
         VALUES (:uid, :tipo, :nome, :c, :d)"
    )->execute([
        'uid'  => $uid,
        'tipo' => $tipo,
        'nome' => $nome,
        'c'    => $caminho,
        'd'    => json_encode($dados, JSON_UNESCAPED_UNICODE),
    ]);
}

echo json_encode([
    'ok'   => true,
    'hora' => date('d/m H:i'),
    'msg'  => 'Ficha salva com sucesso!',
]);
