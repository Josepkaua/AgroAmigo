<?php
/**
 * Envio de e-mail via SMTP — AgroAmigo
 *
 * POR QUE ISTO EXISTE:
 * o site usava mail() do PHP, mas a imagem Docker (php:8.2-apache) não tem
 * nenhum servidor de e-mail instalado e o php.ini não define sendmail_path.
 * Em produção mail() SEMPRE devolvia false: quem pedia "esqueci minha senha"
 * via a mensagem de sucesso e não recebia nada. Falha 100% silenciosa.
 *
 * Aqui falamos SMTP direto, sem dependência externa (nada de composer/PHPMailer),
 * para não mudar o processo de build do Render.
 */
declare(strict_types=1);
require_once __DIR__ . '/functions.php';   // traz db.php e log_erro()

if (!defined('SMTP_HOST'))  define('SMTP_HOST',  '');
if (!defined('SMTP_PORT'))  define('SMTP_PORT',  '587');
if (!defined('SMTP_USER'))  define('SMTP_USER',  '');
if (!defined('SMTP_PASS'))  define('SMTP_PASS',  '');
if (!defined('SMTP_FROM'))  define('SMTP_FROM',  '');
if (!defined('SMTP_NOME'))  define('SMTP_NOME',  'AgroAmigo ATERPEC');

// Brevo (API HTTP) — o caminho que realmente funciona no Render.
//
// POR QUE NÃO DÁ PRA USAR SMTP AQUI:
// o Render bloqueia tráfego de saída nas portas 25, 465 e 587 nos serviços do
// plano free (mudança oficial deles, para conter spam). O sintoma é justamente
// "Connection timed out" ao falar com smtp.gmail.com:587 — o pacote nem sai da
// máquina, então nem chega a testar a senha.
// A API do Brevo fala HTTPS na porta 443, a mesma do site, que nunca é bloqueada.
if (!defined('BREVO_API_KEY')) define('BREVO_API_KEY', '');

function email_configurado(): bool
{
    return BREVO_API_KEY !== ''
        || (SMTP_HOST !== '' && SMTP_USER !== '' && SMTP_PASS !== '');
}

/** Qual caminho de envio está ativo — usado nas mensagens de diagnóstico */
function email_transporte(): string
{
    if (BREVO_API_KEY !== '') return 'brevo';
    if (SMTP_HOST !== '' && SMTP_USER !== '' && SMTP_PASS !== '') return 'smtp';
    return 'nenhum';
}

function email_remetente(): string
{
    return SMTP_FROM !== '' ? SMTP_FROM : SMTP_USER;
}

/**
 * Envia um e-mail de texto simples.
 * Devolve true em sucesso; em falha devolve false e grava o motivo em logs_erros.
 * NUNCA lança exceção — não pode derrubar a página de recuperação de senha.
 */
function enviar_email(string $para, string $assunto, string $corpo, ?string &$erro = null): bool
{
    $erro = null;

    if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
        $erro = 'destinatário inválido';
        return false;
    }
    if (!email_configurado()) {
        $erro = 'SMTP não configurado neste servidor';
        log_erro('E-mail não enviado: SMTP não configurado', __FILE__, __LINE__);
        return false;
    }
    // Defesa contra injeção de cabeçalho: assunto e destinatário não podem ter quebra de linha
    if (preg_match('/[\r\n]/', $para . $assunto)) {
        $erro = 'cabeçalho inválido';
        return false;
    }

    try {
        // Brevo primeiro: é o que funciona no Render free.
        // O SMTP fica como alternativa caso um dia o serviço vire plano pago.
        if (BREVO_API_KEY !== '') {
            return brevo_enviar($para, $assunto, $corpo, $erro);
        }
        return smtp_enviar($para, $assunto, $corpo, $erro);
    } catch (Throwable $e) {
        $erro = $e->getMessage();
        log_erro('Envio de e-mail: ' . $e->getMessage(), __FILE__, __LINE__);
        return false;
    }
}

// ─────────────────────────────────────────────────────────────
// Envio pela API HTTP do Brevo
// ─────────────────────────────────────────────────────────────
function brevo_enviar(string $para, string $assunto, string $corpo, ?string &$erro): bool
{
    $de = email_remetente();
    if ($de === '') {
        $erro = 'remetente não configurado (SMTP_FROM)';
        return false;
    }

    $payload = json_encode([
        'sender'      => ['name' => SMTP_NOME, 'email' => $de],
        'to'          => [['email' => $para]],
        'subject'     => $assunto,
        'textContent' => $corpo,
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . BREVO_API_KEY,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        $erro = 'falha de rede ao falar com o Brevo: ' . $cerr;
        return false;
    }
    // 201 = aceito para entrega
    if ($code === 201) return true;

    // Erros mais comuns, traduzidos para quem for ler o log depois
    $json = json_decode((string) $body, true);
    $msg  = is_array($json) ? ($json['message'] ?? '') : '';
    $erro = match ($code) {
        401     => 'chave do Brevo inválida (confira BREVO_API_KEY)',
        400     => 'Brevo recusou: ' . $msg
                 . ' — se falar em sender, verifique ' . $de . ' no painel do Brevo',
        402     => 'cota do Brevo esgotada (limite diário atingido)',
        default => "Brevo respondeu HTTP {$code}: {$msg}",
    };
    return false;
}

// ─────────────────────────────────────────────────────────────
// Implementação SMTP (STARTTLS + AUTH LOGIN)
// ─────────────────────────────────────────────────────────────

function smtp_ler($fp, string $esperado, string $etapa): void
{
    $resposta = '';
    while (($linha = fgets($fp, 515)) !== false) {
        $resposta .= $linha;
        // Última linha da resposta: "250 texto" (com espaço), não "250-texto"
        if (strlen($linha) >= 4 && $linha[3] === ' ') break;
    }
    if (!str_starts_with($resposta, $esperado)) {
        throw new RuntimeException("$etapa falhou: " . trim($resposta));
    }
}

function smtp_escrever($fp, string $cmd): void
{
    fwrite($fp, $cmd . "\r\n");
}

function smtp_enviar(string $para, string $assunto, string $corpo, ?string &$erro): bool
{
    $host    = SMTP_HOST;
    $porta   = (int) SMTP_PORT;
    $timeout = 20;

    // 465 = TLS implícito; 587 = texto puro e depois STARTTLS
    $endereco = ($porta === 465 ? 'ssl://' : 'tcp://') . $host . ':' . $porta;

    $ctx = stream_context_create(['ssl' => [
        'verify_peer'       => true,
        'verify_peer_name'  => true,
        'SNI_enabled'       => true,
    ]]);

    $fp = @stream_socket_client($endereco, $errno, $errstr, $timeout,
                                STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        throw new RuntimeException("conexão com $host:$porta falhou ($errno $errstr)");
    }
    stream_set_timeout($fp, $timeout);

    $eu = 'agroamigo.onrender.com';

    smtp_ler($fp, '220', 'saudação');
    smtp_escrever($fp, "EHLO $eu");
    smtp_ler($fp, '250', 'EHLO');

    if ($porta !== 465) {
        smtp_escrever($fp, 'STARTTLS');
        smtp_ler($fp, '220', 'STARTTLS');
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($fp);
            throw new RuntimeException('não foi possível ativar o TLS');
        }
        // Depois do STARTTLS o EHLO precisa ser repetido
        smtp_escrever($fp, "EHLO $eu");
        smtp_ler($fp, '250', 'EHLO pós-TLS');
    }

    smtp_escrever($fp, 'AUTH LOGIN');
    smtp_ler($fp, '334', 'AUTH');
    smtp_escrever($fp, base64_encode(SMTP_USER));
    smtp_ler($fp, '334', 'usuário');
    smtp_escrever($fp, base64_encode(SMTP_PASS));
    smtp_ler($fp, '235', 'senha');   // 535 aqui = senha de app errada

    $de = email_remetente();
    smtp_escrever($fp, "MAIL FROM:<$de>");
    smtp_ler($fp, '250', 'MAIL FROM');
    smtp_escrever($fp, "RCPT TO:<$para>");
    smtp_ler($fp, '250', 'RCPT TO');
    smtp_escrever($fp, 'DATA');
    smtp_ler($fp, '354', 'DATA');

    $nomeDe   = '=?UTF-8?B?' . base64_encode(SMTP_NOME) . '?=';
    $assB64   = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
    $dataHdr  = date('r');
    $msgId    = '<' . bin2hex(random_bytes(12)) . '@agroamigo>';

    $cab = [
        "Date: $dataHdr",
        "From: $nomeDe <$de>",
        "To: <$para>",
        "Subject: $assB64",
        "Message-ID: $msgId",
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        // Evita que resposta automática de férias volte pro sistema
        'Auto-Submitted: auto-generated',
    ];

    // Normaliza quebras de linha e faz dot-stuffing (linha que começa com "."
    // encerraria o DATA antes da hora)
    $corpoNorm = preg_replace("/\r\n|\r|\n/", "\r\n", $corpo);
    $corpoNorm = preg_replace('/^\./m', '..', $corpoNorm);

    fwrite($fp, implode("\r\n", $cab) . "\r\n\r\n" . $corpoNorm . "\r\n.\r\n");
    smtp_ler($fp, '250', 'envio');

    smtp_escrever($fp, 'QUIT');
    fclose($fp);
    return true;
}
