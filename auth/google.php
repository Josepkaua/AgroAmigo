<?php
/**
 * Início do login com Google (OAuth 2.0 / OpenID Connect).
 * O usuário clica em "Entrar com Google" e cai aqui; daqui vai para o Google.
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/google_config.php';

session_init();
security_headers();

if (usuario_logado()) {
    header('Location: ../index.php');
    exit;
}

if (!google_configurado()) {
    flash('erro', 'O login com Google ainda não foi configurado neste servidor.');
    header('Location: ../login.php');
    exit;
}

// state: valor aleatório que amarra este início de login ao retorno.
// Sem ele, um atacante monta uma URL de callback com o código DELE e faz a
// vítima logar na conta dele (login CSRF) — aí tudo que a vítima cadastrar
// vai parar na conta do atacante.
$state = bin2hex(random_bytes(24));
$nonce = bin2hex(random_bytes(24));

// Guarda no banco (e não só na sessão) para o state sobreviver caso o cookie
// não volte, e para conseguirmos expirar/auditar tentativas.
try {
    db()->prepare("
        INSERT INTO oauth_state (state, nonce, redirect_to, ip)
        VALUES (:s, :n, :r, :ip)
    ")->execute([
        's'  => $state,
        'n'  => $nonce,
        'r'  => url_interna($_SESSION['login_next'] ?? '', ''),
        'ip' => ip_real(),
    ]);

    // Limpeza de states velhos (mais de 15 min) — evita a tabela crescer sem fim
    db()->exec("DELETE FROM oauth_state WHERE criado_em < NOW() - INTERVAL '15 minutes'");
} catch (Throwable $e) {
    log_erro('Falha ao gravar oauth_state: ' . $e->getMessage(), __FILE__, __LINE__);
    flash('erro', 'Não foi possível iniciar o login com Google. Tente novamente.');
    header('Location: ../login.php');
    exit;
}

$_SESSION['oauth_state'] = $state;

$url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => google_redirect_uri(),
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $state,
    'nonce'         => $nonce,
    'prompt'        => 'select_account',
    // Pede ao Google que já venha o e-mail verificado
    'include_granted_scopes' => 'true',
]);

header('Location: ' . $url);
exit;
