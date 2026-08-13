<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

// Escapa output HTML — use em todo conteúdo dinâmico
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * IP real do cliente, atrás do proxy do Render.
 *
 * ATENÇÃO — a versão anterior pegava o PRIMEIRO item de X-Forwarded-For, que é
 * justamente a parte que o atacante controla: basta mandar
 *     X-Forwarded-For: 1.2.3.4
 * e o proxy do Render acrescenta o IP verdadeiro DEPOIS, virando
 *     X-Forwarded-For: 1.2.3.4, <ip_real>
 * Lendo o primeiro, cada requisição parecia vir de um IP novo e o bloqueio por
 * tentativas (ip_bloqueado_login) era burlado com uma linha de header.
 *
 * O item confiável é o ÚLTIMO, porque foi o proxy que o acrescentou.
 */
function ip_real(): string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $partes = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        $ip     = end($partes);
        if ($ip !== false && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    $remoto = $_SERVER['REMOTE_ADDR'] ?? '';
    return filter_var($remoto, FILTER_VALIDATE_IP) ? $remoto : '0.0.0.0';
}

/**
 * Normaliza um celular brasileiro para dígitos com DDI: "(99) 98765-4321" -> "5599987654321".
 * Devolve null se não parecer um celular/telefone válido.
 * É esta forma que vai para usuarios.telefone_norm e é usada no login.
 */
function tel_normalizar(?string $tel): ?string
{
    $d = preg_replace('/\D/', '', (string) $tel);
    if ($d === '') return null;

    // Já veio com DDI 55
    if (strlen($d) === 12 || strlen($d) === 13) {
        return str_starts_with($d, '55') ? $d : null;
    }
    // DDD + número, sem DDI
    if (strlen($d) === 10 || strlen($d) === 11) {
        $ddd = (int) substr($d, 0, 2);
        if ($ddd < 11 || $ddd > 99) return null; // DDD brasileiro válido
        return '55' . $d;
    }
    return null;
}

/** Formata para exibição: "5599987654321" -> "(99) 98765-4321" */
function tel_exibir(?string $norm): string
{
    $d = preg_replace('/\D/', '', (string) $norm);
    if (str_starts_with($d, '55')) $d = substr($d, 2);
    if (strlen($d) === 11) return sprintf('(%s) %s-%s', substr($d,0,2), substr($d,2,5), substr($d,7));
    if (strlen($d) === 10) return sprintf('(%s) %s-%s', substr($d,0,2), substr($d,2,4), substr($d,6));
    return (string) $norm;
}

/** true se o texto digitado parece um telefone (só dígitos e pontuação), não um e-mail */
function parece_telefone(string $txt): bool
{
    return $txt !== '' && preg_match('/^[\d\s()\-+\.]+$/', $txt) === 1;
}

/**
 * Valida um destino de redirecionamento interno.
 * Aceita só caminho relativo do próprio site. Rejeita "//evil.com" e também
 * "/\evil.com" — o Chrome converte a barra invertida em barra, transformando
 * isso num redirect externo (bypass clássico de open redirect).
 */
function url_interna(?string $destino, string $padrao = 'index.php'): string
{
    $d = trim((string) $destino);
    if ($d === '') return $padrao;
    if (str_contains($d, "\\") || str_contains($d, "\n") || str_contains($d, "\r")) return $padrao;
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $d)) return $padrao; // http:, javascript:, data:...
    if (str_starts_with($d, '//')) return $padrao;
    if (!str_starts_with($d, '/'))  return $padrao;
    return $d;
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

// ─── Imagens ─────────────────────────────────────────────
/**
 * Resolve o caminho da imagem de uma raça.
 * Se for caminho local que ainda não foi baixado, devolve '' — assim o
 * template cai no emoji da raça em vez de mostrar ícone de imagem quebrada.
 * URLs externas (http/https) passam direto.
 */
function img_raca(?string $caminho): string
{
    $caminho = trim((string) $caminho);
    if ($caminho === '') return '';
    if (preg_match('#^https?://#i', $caminho)) return $caminho;

    return is_file(__DIR__ . '/../' . ltrim($caminho, '/')) ? $caminho : '';
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
