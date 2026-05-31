<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

// Escapa output HTML — use em todo conteúdo dinâmico
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// IP real mesmo atrás de proxy
function ip_real(): string
{
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            return trim(explode(',', $_SERVER[$key])[0]);
        }
    }
    return '0.0.0.0';
}

// ─── Flash messages ───────────────────────────────────────
function flash(string $tipo, string $mensagem): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $mensagem];
}

function get_flash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// ─── Logs ────────────────────────────────────────────────
function log_acesso(string $acao, ?string $usuario_id = null, ?string $email = null): void
{
    try {
        db()->prepare("
            INSERT INTO logs_acesso (usuario_id, email_tentado, ip, user_agent, acao)
            VALUES (:uid, :email, :ip, :ua, :acao)
        ")->execute([
            'uid'   => $usuario_id,
            'email' => $email,
            'ip'    => ip_real(),
            'ua'    => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
            'acao'  => $acao,
        ]);
    } catch (Throwable) {
        // Log não pode derrubar a aplicação
    }
}

function log_atividade(
    string  $entidade,
    ?string $entidade_id,
    string  $acao,
    mixed   $antes  = null,
    mixed   $depois = null
): void {
    $uid = $_SESSION['usuario']['id'] ?? null;
    try {
        db()->prepare("
            INSERT INTO logs_atividade (usuario_id, entidade, entidade_id, acao, dados_antes, dados_depois, ip)
            VALUES (:uid, :ent, :eid, :acao, :antes, :depois, :ip)
        ")->execute([
            'uid'    => $uid,
            'ent'    => $entidade,
            'eid'    => $entidade_id,
            'acao'   => $acao,
            'antes'  => $antes  !== null ? json_encode($antes)  : null,
            'depois' => $depois !== null ? json_encode($depois) : null,
            'ip'     => ip_real(),
        ]);
    } catch (Throwable) {}
}

function log_erro(string $mensagem, ?string $arquivo = null, ?int $linha = null): void
{
    $uid = $_SESSION['usuario']['id'] ?? null;
    try {
        db()->prepare("
            INSERT INTO logs_erros (usuario_id, mensagem, arquivo, linha, url, ip)
            VALUES (:uid, :msg, :arq, :lin, :url, :ip)
        ")->execute([
            'uid' => $uid,
            'msg' => $mensagem,
            'arq' => $arquivo,
            'lin' => $linha,
            'url' => mb_substr($_SERVER['REQUEST_URI'] ?? '', 0, 500),
            'ip'  => ip_real(),
        ]);
    } catch (Throwable) {}
}

// ─── Rate limiting por IP (login) ────────────────────
function ip_bloqueado_login(): bool
{
    // Bloqueia IP com >= 10 falhas nos últimos 15 minutos
    try {
        $stmt = db()->prepare("
            SELECT COUNT(*) FROM logs_acesso
            WHERE ip = :ip
              AND acao IN ('login_falhou', 'bloqueado')
              AND created_at > NOW() - INTERVAL '15 minutes'
        ");
        $stmt->execute(['ip' => ip_real()]);
        return (int)$stmt->fetchColumn() >= 10;
    } catch (Throwable) {
        return false; // em caso de erro no DB, não bloqueia
    }
}

// ─── Headers de segurança (páginas sem header.php) ───
function security_headers(): void
{
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// ─── Paginação ───────────────────────────────────────────
function paginar(int $total, int $por_pagina, int $pagina_atual): array
{
    $total_paginas = max(1, (int) ceil($total / $por_pagina));
    $pagina_atual  = max(1, min($pagina_atual, $total_paginas));
    $offset        = ($pagina_atual - 1) * $por_pagina;

    return [
        'total'         => $total,
        'por_pagina'    => $por_pagina,
        'pagina_atual'  => $pagina_atual,
        'total_paginas' => $total_paginas,
        'offset'        => $offset,
    ];
}
