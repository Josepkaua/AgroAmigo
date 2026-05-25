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
        http_response_code(503);
        die('Serviço temporariamente indisponível. Tente novamente.');
    }

    return $pdo;
}
