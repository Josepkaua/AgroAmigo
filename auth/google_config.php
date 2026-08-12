<?php
/**
 * Configuração do login com Google.
 * As chaves vêm das variáveis de ambiente (Render) via config.php.
 * NUNCA escreva client_id/secret aqui dentro — este arquivo vai para o Git.
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';

if (!defined('GOOGLE_CLIENT_ID'))     define('GOOGLE_CLIENT_ID', '');
if (!defined('GOOGLE_CLIENT_SECRET')) define('GOOGLE_CLIENT_SECRET', '');

function google_configurado(): bool
{
    return GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_SECRET !== '';
}

/**
 * URI de retorno registrada no Google Cloud Console.
 * Precisa bater EXATAMENTE com o que está lá, incluindo http/https e barra final.
 */
function google_redirect_uri(): string
{
    if (defined('APP_URL') && APP_URL !== '') {
        return rtrim(APP_URL, '/') . '/auth/google-retorno.php';
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return ($https ? 'https' : 'http') . '://' . $host . '/auth/google-retorno.php';
}

/** POST simples com cURL, usado na troca do code pelo token */
function google_post(string $url, array $dados): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($dados),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $code !== 200) return null;
    $json = json_decode((string) $body, true);
    return is_array($json) ? $json : null;
}

/** GET autenticado, usado para buscar os dados do perfil */
function google_get(string $url, string $accessToken): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $code !== 200) return null;
    $json = json_decode((string) $body, true);
    return is_array($json) ? $json : null;
}
