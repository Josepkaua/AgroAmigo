<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

// ─── Sessão segura ───────────────────────────────────────
function session_init(): void
{
    if (session_status() !== PHP_SESSION_NONE) return;

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();

    // Verifica fingerprint do user-agent para detectar sequestro de sessão
    $ua_hash = md5($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (!empty($_SESSION['_ua']) && $_SESSION['_ua'] !== $ua_hash) {
        // Possível sequestro — destrói a sessão
        $_SESSION = [];
        session_destroy();
        session_start();
    }
    if (empty($_SESSION['_ua'])) {
        $_SESSION['_ua'] = $ua_hash;
    }
}

// ─── Getters ─────────────────────────────────────────────
function usuario_logado(): ?array
{
    session_init();
    return $_SESSION['usuario'] ?? null;
}

function is_admin(): bool
{
    return (usuario_logado()['role'] ?? '') === 'admin';
}

// ─── Guards ──────────────────────────────────────────────
function require_login(string $redirect = '/login.php'): array
{
    $u = usuario_logado();
    if (!$u) {
        $qs = http_build_query(['next' => $_SERVER['REQUEST_URI'] ?? '']);
        header('Location: ' . $redirect . '?' . $qs);
        exit;
    }
    return $u;
}

function require_admin(): array
{
    $u = require_login('/login.php');
    if ($u['role'] !== 'admin') {
        http_response_code(403);
        // Não revela que a página existe
        include __DIR__ . '/../404.php';
        exit;
    }
    return $u;
}

// ─── Login / Logout ──────────────────────────────────────
function login_usuario(array $usuario): void
{
    session_init();
    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id'    => $usuario['id'],
        'nome'  => $usuario['nome'],
        'email' => $usuario['email'],
        'role'  => $usuario['role'],
    ];
    $_SESSION['_ua'] = md5($_SERVER['HTTP_USER_AGENT'] ?? '');
}

function logout_usuario(): void
{
    session_init();
    $uid   = $_SESSION['usuario']['id']    ?? null;
    $email = $_SESSION['usuario']['email'] ?? null;

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();

    if ($uid) log_acesso('logout', $uid, $email);
}

// ─── CSRF ────────────────────────────────────────────────
function csrf_token(): string
{
    session_init();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_verify(): void
{
    session_init();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        die('Requisição inválida. Por favor, volte e tente novamente.');
    }
}
