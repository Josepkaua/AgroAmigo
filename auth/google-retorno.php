<?php
/**
 * Retorno do Google (redirect_uri). Valida o state, troca o code por token,
 * busca o perfil e entra — criando a conta na primeira vez.
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/google_config.php';

session_init();
security_headers();

function volta_login(string $msg): never
{
    flash('erro', $msg);
    header('Location: ../login.php');
    exit;
}

if (usuario_logado()) { header('Location: ../index.php'); exit; }
if (!google_configurado()) volta_login('O login com Google não está configurado neste servidor.');

// O Google devolve error=access_denied quando o usuário clica em "Cancelar"
if (!empty($_GET['error'])) {
    volta_login($_GET['error'] === 'access_denied'
        ? 'Login com Google cancelado.'
        : 'O Google recusou o login. Tente novamente.');
}

$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? '';
if ($code === '' || $state === '' || !preg_match('/^[a-f0-9]{48}$/', $state)) {
    volta_login('Retorno inválido do Google. Tente novamente.');
}

// ── Valida o state: precisa existir, não ter sido usado e não estar vencido ──
$pdo = db();
$st  = $pdo->prepare("
    SELECT * FROM oauth_state
     WHERE state = :s
       AND usado_em IS NULL
       AND criado_em > NOW() - INTERVAL '15 minutes'
     LIMIT 1
");
$st->execute(['s' => $state]);
$linha = $st->fetch();

if (!$linha) volta_login('Sessão de login expirada. Tente entrar novamente.');

// Marca como usado ANTES de prosseguir: garante uso único (impede replay do callback)
$pdo->prepare("UPDATE oauth_state SET usado_em = NOW() WHERE state = :s")->execute(['s' => $state]);

// Reforço: o state também tem que bater com o da sessão deste navegador
if (!hash_equals((string) ($_SESSION['oauth_state'] ?? ''), $state)) {
    log_acesso('login_falhou', null, 'google:state_divergente');
    volta_login('Não foi possível validar o login. Tente novamente pelo site.');
}
unset($_SESSION['oauth_state']);

// ── Troca o code pelo token ──
$tok = google_post('https://oauth2.googleapis.com/token', [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => google_redirect_uri(),
    'grant_type'    => 'authorization_code',
]);
if (!$tok || empty($tok['access_token'])) {
    log_erro('Google: falha ao trocar code por token', __FILE__, __LINE__);
    volta_login('Não foi possível concluir o login com Google.');
}

// ── Busca o perfil ──
// Vem direto do Google por TLS, então é confiável sem precisar validar o JWT.
$perfil = google_get('https://openidconnect.googleapis.com/v1/userinfo', $tok['access_token']);
if (!$perfil || empty($perfil['sub']) || empty($perfil['email'])) {
    volta_login('O Google não retornou os dados da conta.');
}

// Só aceita e-mail confirmado pelo Google. Sem esta checagem alguém poderia
// criar uma conta Google com o e-mail de outra pessoa e assumir o cadastro dela.
if (empty($perfil['email_verified'])) {
    volta_login('Seu e-mail do Google não está verificado. Verifique-o e tente de novo.');
}

$sub    = (string) $perfil['sub'];
$email  = mb_strtolower(trim((string) $perfil['email']));
$nome   = trim((string) ($perfil['name'] ?? '')) ?: explode('@', $email)[0];
$avatar = (string) ($perfil['picture'] ?? '');

try {
    // 1) já vinculado ao Google?
    $q = $pdo->prepare("SELECT * FROM usuarios WHERE google_id = :g LIMIT 1");
    $q->execute(['g' => $sub]);
    $user = $q->fetch();

    // 2) senão, existe conta com este e-mail? vincula (e-mail já verificado pelo Google)
    if (!$user) {
        $q = $pdo->prepare("SELECT * FROM usuarios WHERE lower(email) = :e LIMIT 1");
        $q->execute(['e' => $email]);
        $user = $q->fetch();

        if ($user) {
            $pdo->prepare("
                UPDATE usuarios
                   SET google_id = :g, avatar_url = :a, email_verificado = TRUE, updated_at = NOW()
                 WHERE id = :id
            ")->execute(['g' => $sub, 'a' => $avatar, 'id' => $user['id']]);
            $user['google_id'] = $sub;
            log_atividade('usuarios', (string) $user['id'], 'editar', null, ['vinculou_google' => true]);
        }
    }

    // 3) senão, cria a conta
    if (!$user) {
        // Conta sem senha local. O '!' na frente faz password_verify() falhar
        // sempre — ninguém entra por senha numa conta criada pelo Google.
        $senhaImpossivel = '!google:' . bin2hex(random_bytes(16));

        $ins = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha_hash, google_id, avatar_url,
                                  email_verificado, role, status)
            VALUES (:n, :e, :s, :g, :a, TRUE, 'produtor', 'ativo')
            RETURNING *
        ");
        $ins->execute(['n' => mb_substr($nome, 0, 150), 'e' => $email,
                       's' => $senhaImpossivel, 'g' => $sub, 'a' => $avatar]);
        $user = $ins->fetch();
        log_atividade('usuarios', (string) $user['id'], 'criar', null,
                      ['via' => 'google', 'email' => $email]);
    }

    if (($user['status'] ?? '') !== 'ativo') {
        volta_login('Esta conta está inativa. Fale com a equipe ATERPEC.');
    }

    $pdo->prepare("
        UPDATE usuarios SET ultimo_login = NOW(), tentativas_login = 0, bloqueado_ate = NULL
        WHERE id = :id
    ")->execute(['id' => $user['id']]);

    login_usuario($user);
    log_acesso('login_ok', $user['id'], $user['email']);

} catch (Throwable $e) {
    log_erro('Google login: ' . $e->getMessage(), __FILE__, __LINE__);
    volta_login('Erro ao entrar com Google. Tente novamente.');
}

$destino = url_interna((string) ($linha['redirect_to'] ?? ''), '');
if ($destino === '') {
    $destino = ($user['role'] === 'admin') ? '../gestao/index.php' : '../index.php';
}
header('Location: ' . $destino);
exit;
