<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_SSL
        );
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ]);
        // Retorna timestamps já no fuso de Brasília
        $pdo->exec("SET TIMEZONE='America/Sao_Paulo'");
    } catch (PDOException $e) {
        error_log('[AgroAmigo] DB connection error: ' . $e->getMessage());
        // O die() aqui era o problema: por não ser exceção, NENHUM try/catch
        // conseguia segurar. Bastava o Supabase hibernar (o que acontece no
        // plano free e já aconteceu) para as páginas de conteúdo — que nem
        // precisam do banco — morrerem no meio do HTML. Agora lança exceção:
        // quem precisa do banco trata; quem não precisa segue com o conteúdo
        // que já está no código.
        throw new RuntimeException('Banco de dados indisponível', 0, $e);
    }

    return $pdo;
}

/**
 * Conexão para quem NÃO depende do banco para funcionar (páginas de conteúdo).
 * Devolve null em vez de estourar, para a página continuar de pé.
 */
function db_opcional(): ?PDO
{
    try {
        return db();
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * Para páginas que realmente precisam do banco (login, fichas, painel).
 * Mostra a mensagem de indisponibilidade e encerra, como era antes.
 */
function db_obrigatorio(): PDO
{
    try {
        return db();
    } catch (Throwable $e) {
        db_pagina_indisponivel();
    }
}

/** Página amigável de "banco fora do ar" */
function db_pagina_indisponivel(): never
{
    if (!headers_sent()) {
        http_response_code(503);
        header('Retry-After: 120');
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<title>Fora do ar — AgroAmigo</title><style>'
       . 'body{font-family:system-ui,sans-serif;background:#f0fdf4;display:flex;'
       . 'align-items:center;justify-content:center;min-height:100vh;margin:0;padding:24px}'
       . '.c{background:#fff;border-radius:16px;padding:38px 30px;max-width:420px;text-align:center;'
       . 'box-shadow:0 8px 32px rgba(0,0,0,.1)}h1{font-size:19px;color:#14532d;margin:0 0 10px}'
       . 'p{font-size:14px;color:#4b5563;line-height:1.6;margin:0 0 20px}'
       . 'a{display:inline-block;background:#166534;color:#fff;text-decoration:none;'
       . 'padding:11px 24px;border-radius:999px;font-weight:700;font-size:14px}</style></head><body>'
       . '<div class="c"><div style="font-size:44px">🌱</div>'
       . '<h1>Estamos voltando já já</h1>'
       . '<p>O sistema está fora do ar por alguns instantes. O conteúdo técnico '
       . 'continua disponível — é só voltar para o site.</p>'
       . '<a href="/home.php">Ver conteúdo técnico</a></div></body></html>';
    exit;
}

/**
 * Rede de segurança: se qualquer página esquecer de tratar a queda do banco,
 * o visitante vê a página amigável em vez de erro cru do PHP.
 * Sem isto, trocar o die() por exceção viraria tela branca no login.
 */
set_exception_handler(function (Throwable $e): void {
    if ($e instanceof RuntimeException && str_contains($e->getMessage(), 'Banco de dados indisponível')) {
        error_log('[AgroAmigo] ' . $e->getMessage());
        db_pagina_indisponivel();
    }
    error_log('[AgroAmigo] Erro não tratado: ' . $e->getMessage()
            . ' em ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) http_response_code(500);
    echo 'Ocorreu um erro. Tente novamente em instantes.';
});
