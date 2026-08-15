<?php
/**
 * Os e-mails que o site envia — AgroAmigo
 *
 * Todos saem do endereço oficial do projeto (SMTP_FROM) com o nome
 * "AgroAmigo ATERPEC". Texto simples de propósito: chega melhor na caixa de
 * entrada, abre em qualquer celular e não some se o cliente bloquear imagens.
 *
 * Nenhum envio pode derrubar a página. Se o Gmail estiver fora do ar, o e-mail
 * é guardado em emails_falhados e a ação do usuário continua normalmente —
 * ninguém deixa de se cadastrar porque o servidor de e-mail piscou.
 */
declare(strict_types=1);
require_once __DIR__ . '/mailer.php';

/** Envia e, se falhar, guarda para não perder. Nunca lança exceção. */
function enviar_ou_guardar(string $para, string $assunto, string $corpo): bool
{
    $erro = null;
    if (enviar_email($para, $assunto, $corpo, $erro)) return true;

    try {
        db()->prepare("
            INSERT INTO emails_falhados (destino, assunto, corpo, erro)
            VALUES (:d, :a, :c, :e)
        ")->execute(['d'=>$para, 'a'=>$assunto, 'c'=>$corpo, 'e'=>mb_substr((string)$erro,0,255)]);
    } catch (Throwable $e) {
        log_erro('emails_falhados: ' . $e->getMessage(), __FILE__, __LINE__);
    }
    return false;
}

function _url_base(): string
{
    if (defined('APP_URL') && APP_URL !== '') return rtrim(APP_URL, '/');
    $https = (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
          || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

function _assinatura(): string
{
    return "\n\n—\nAgroAmigo · Projeto ATERPEC\n"
         . "Assistência técnica para pequenos produtores rurais do Maranhão\n"
         . _url_base() . "\n";
}

/** Primeiro nome, limpo de qualquer coisa estranha */
function _primeiro_nome(string $nome): string
{
    $p = preg_replace('/[^\p{L}\s\-]/u', '', trim($nome));
    $p = explode(' ', trim((string) $p))[0] ?? '';
    return $p !== '' ? $p : 'produtor';
}

// ─────────────────────────────────────────────────────
// 1. Verificação de e-mail
// ─────────────────────────────────────────────────────

/** Gera o token, guarda o hash e manda o link. Devolve false se não enviou. */
function enviar_verificacao(array $usuario): bool
{
    try {
        // Invalida links anteriores: só o mais novo vale
        db()->prepare("UPDATE verificacao_email SET usado_em = NOW()
                        WHERE usuario_id = :u AND usado_em IS NULL")
            ->execute(['u' => $usuario['id']]);

        $token = bin2hex(random_bytes(32));
        db()->prepare("
            INSERT INTO verificacao_email (usuario_id, token_hash, expira_em)
            VALUES (:u, :h, NOW() + INTERVAL '48 hours')
        ")->execute(['u' => $usuario['id'], 'h' => hash('sha256', $token)]);
    } catch (Throwable $e) {
        log_erro('enviar_verificacao: ' . $e->getMessage(), __FILE__, __LINE__);
        return false;
    }

    $nome = _primeiro_nome((string) $usuario['nome']);
    $link = _url_base() . '/verificar-email.php?token=' . $token;

    $corpo = "Olá, {$nome}!\n\n"
           . "Confirme seu e-mail para garantir que você consiga recuperar sua senha\n"
           . "e receber avisos da equipe ATERPEC.\n\n"
           . "Clique no link abaixo (vale por 48 horas):\n{$link}\n\n"
           . "Se o link não abrir, copie e cole no navegador.\n\n"
           . "Você pode usar o site normalmente mesmo antes de confirmar.\n"
           . "Se não foi você quem criou esta conta, ignore este e-mail."
           . _assinatura();

    return enviar_ou_guardar((string) $usuario['email'],
        'Confirme seu e-mail — AgroAmigo', $corpo);
}

// ─────────────────────────────────────────────────────
// 2. Boas-vindas
// ─────────────────────────────────────────────────────
function enviar_boas_vindas(array $usuario): bool
{
    $nome = _primeiro_nome((string) $usuario['nome']);
    $base = _url_base();

    $corpo = "Olá, {$nome}! Sua conta no AgroAmigo está pronta.\n\n"
           . "O AgroAmigo é gratuito e foi feito para o pequeno produtor do Maranhão.\n"
           . "O que você já pode fazer:\n\n"
           . "• Cadastrar sua propriedade e seus animais\n"
           . "  {$base}/propriedade-nova.php\n\n"
           . "• Registrar pesagens, vacinações e ocorrências\n"
           . "  {$base}/fichas.php\n\n"
           . "• Consultar o guia técnico por espécie (bovinos, aves, suínos,\n"
           . "  caprinos, ovinos e peixes), com as raças criadas na região\n"
           . "  {$base}/bovinos.php\n\n"
           . "• Falar com um técnico da equipe quando precisar\n"
           . "  {$base}/contato.php\n\n"
           . "Dúvida sobre manejo, vacina ou alimentação? É só chamar a gente."
           . _assinatura();

    return enviar_ou_guardar((string) $usuario['email'],
        'Bem-vindo ao AgroAmigo', $corpo);
}

// ─────────────────────────────────────────────────────
// 3. Aviso de login de aparelho novo
// ─────────────────────────────────────────────────────

/** Marca que identifica o aparelho: navegador + faixa de rede, com segredo do app */
function _marca_dispositivo(): string
{
    $ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip  = ip_real();
    // Só os 3 primeiros octetos: trocar de torre de celular não gera alerta falso
    $rede = implode('.', array_slice(explode('.', $ip), 0, 3));
    $sal = defined('APP_SECRET') ? APP_SECRET : 'agroamigo';
    return substr(hash_hmac('sha256', $ua . '|' . $rede, $sal), 0, 64);
}

/**
 * Registra o aparelho. Se for a primeira vez nesta conta, avisa por e-mail.
 * O primeiro login depois do cadastro NÃO gera aviso (seria só barulho).
 */
function registrar_dispositivo(array $usuario, bool $avisar = true): void
{
    $marca = _marca_dispositivo();

    try {
        $st = db()->prepare("SELECT id FROM dispositivos_conhecidos
                              WHERE usuario_id = :u AND marca = :m LIMIT 1");
        $st->execute(['u' => $usuario['id'], 'm' => $marca]);
        $conhecido = (bool) $st->fetch();

        if ($conhecido) {
            db()->prepare("UPDATE dispositivos_conhecidos SET ultimo_uso = NOW()
                            WHERE usuario_id = :u AND marca = :m")
                ->execute(['u' => $usuario['id'], 'm' => $marca]);
            return;
        }

        // É o primeiro aparelho da conta? Então é o cadastro — não avisa.
        $n = db()->prepare("SELECT count(*) FROM dispositivos_conhecidos WHERE usuario_id = :u");
        $n->execute(['u' => $usuario['id']]);
        $primeiro = ((int) $n->fetchColumn()) === 0;

        db()->prepare("
            INSERT INTO dispositivos_conhecidos (usuario_id, marca, ip, user_agent)
            VALUES (:u, :m, :ip, :ua)
            ON CONFLICT (usuario_id, marca) DO UPDATE SET ultimo_uso = NOW()
        ")->execute([
            'u'  => $usuario['id'], 'm' => $marca, 'ip' => ip_real(),
            'ua' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);

        if ($primeiro || !$avisar) return;

    } catch (Throwable $e) {
        log_erro('registrar_dispositivo: ' . $e->getMessage(), __FILE__, __LINE__);
        return;
    }

    $nome    = _primeiro_nome((string) $usuario['nome']);
    $quando  = date('d/m/Y \à\s H:i');
    $aparelho = _descrever_navegador($_SERVER['HTTP_USER_AGENT'] ?? '');

    $corpo = "Olá, {$nome}.\n\n"
           . "Sua conta do AgroAmigo foi acessada de um aparelho novo:\n\n"
           . "  Quando:   {$quando}\n"
           . "  Aparelho: {$aparelho}\n\n"
           . "Se foi você, pode ignorar este e-mail — não vamos avisar de novo\n"
           . "quando entrar deste mesmo aparelho.\n\n"
           . "SE NÃO FOI VOCÊ, troque sua senha agora:\n"
           . _url_base() . "/esqueci-senha.php"
           . _assinatura();

    enviar_ou_guardar((string) $usuario['email'],
        'Novo acesso à sua conta — AgroAmigo', $corpo);
}

/** Descrição simples do navegador, para o produtor entender */
function _descrever_navegador(string $ua): string
{
    $sistema = match (true) {
        str_contains($ua, 'Android')            => 'celular Android',
        str_contains($ua, 'iPhone')             => 'iPhone',
        str_contains($ua, 'iPad')               => 'iPad',
        str_contains($ua, 'Windows')            => 'computador Windows',
        str_contains($ua, 'Mac OS')             => 'computador Mac',
        str_contains($ua, 'Linux')              => 'computador Linux',
        default                                 => 'aparelho desconhecido',
    };
    $navegador = match (true) {
        str_contains($ua, 'Edg/')     => 'Edge',
        str_contains($ua, 'OPR/')     => 'Opera',
        str_contains($ua, 'Chrome/')  => 'Chrome',
        str_contains($ua, 'Firefox/') => 'Firefox',
        str_contains($ua, 'Safari/')  => 'Safari',
        default                       => 'navegador',
    };
    return "{$navegador} em {$sistema}";
}

// ─────────────────────────────────────────────────────
// 4. Aviso de nova mensagem de contato (para a equipe)
// ─────────────────────────────────────────────────────
function avisar_nova_mensagem(array $msg): bool
{
    $destino = email_remetente();   // caixa oficial do projeto
    if ($destino === '') return false;

    $corpo = "Chegou uma mensagem pelo formulário do site.\n\n"
           . "  Nome:     " . ($msg['nome']     ?? '-') . "\n"
           . "  Telefone: " . ($msg['telefone'] ?? '-') . "\n"
           . "  Animal:   " . ($msg['animal']   ?? '-') . "\n"
           . "  Assunto:  " . ($msg['topico']   ?? '-') . "\n"
           . "  Quando:   " . date('d/m/Y H:i') . "\n\n"
           . "Mensagem:\n"
           . str_repeat('-', 50) . "\n"
           . ($msg['mensagem'] ?? '') . "\n"
           . str_repeat('-', 50) . "\n\n"
           . "Responder pelo WhatsApp costuma ser mais rápido para o produtor.\n"
           . "Ver todas as mensagens: " . _url_base() . "/gestao/index.php"
           . _assinatura();

    return enviar_ou_guardar($destino,
        'Nova mensagem de ' . mb_substr((string) ($msg['nome'] ?? 'produtor'), 0, 40) . ' — AgroAmigo',
        $corpo);
}
