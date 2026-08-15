<?php
/**
 * Serve as imagens guardadas no banco (enviadas pelo painel).
 * Público: são fotos que aparecem no site aberto.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$id = $_GET['id'] ?? '';

// Só aceita UUID. Sem isto, o parâmetro viraria porta de entrada para injeção.
if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
    http_response_code(400);
    exit;
}

try {
    $st = db()->prepare("SELECT mime, conteudo, tamanho, criado_em FROM imagens WHERE id = :id");
    $st->execute(['id' => $id]);
    $img = $st->fetch();
} catch (Throwable $e) {
    log_erro('imagem.php: ' . $e->getMessage(), __FILE__, __LINE__);
    http_response_code(500);
    exit;
}

if (!$img) { http_response_code(404); exit; }

// PDO devolve BYTEA como stream ou como string, depende do driver
$dados = is_resource($img['conteudo']) ? stream_get_contents($img['conteudo']) : $img['conteudo'];
if ($dados === false || $dados === null) { http_response_code(500); exit; }

// ETag permite o navegador reusar do cache em vez de baixar de novo
$etag = '"' . md5($id . '|' . $img['criado_em']) . '"';
if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}

header('Content-Type: ' . $img['mime']);
header('Content-Length: ' . strlen($dados));
header('ETag: ' . $etag);
header('Cache-Control: public, max-age=604800');   // 7 dias
header('X-Content-Type-Options: nosniff');
// Impede que a imagem seja interpretada como página caso algo dê errado
header('Content-Disposition: inline; filename="imagem.jpg"');

echo $dados;
