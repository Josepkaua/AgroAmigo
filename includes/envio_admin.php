<?php
/**
 * Envio de e-mail pelo painel — regras e travas
 *
 * Esta é a funcionalidade mais perigosa do sistema: uma conta de admin
 * invadida vira máquina de spam assinando "AgroAmigo ATERPEC". As travas
 * abaixo existem para limitar o estrago, não para atrapalhar o uso normal.
 */
declare(strict_types=1);
require_once __DIR__ . '/emails.php';

// Brevo grátis entrega 300/dia. Paramos antes para nunca estourar no meio de
// um comunicado e deixar metade dos produtores sem receber.
const LIMITE_DIARIO      = 250;
// Freio contra loop ou conta invadida: rajada máxima por minuto.
const LIMITE_POR_MINUTO  = 30;

/** Quantos e-mails já saíram hoje (todos os admins somados) */
function enviados_hoje(): int
{
    try {
        return (int) db()->query("
            SELECT count(*) FROM emails_enviados
             WHERE sucesso AND criado_em >= date_trunc('day', NOW())
        ")->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

function enviados_ultimo_minuto(): int
{
    try {
        return (int) db()->query("
            SELECT count(*) FROM emails_enviados
             WHERE criado_em > NOW() - INTERVAL '1 minute'
        ")->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

/** Sobra do dia */
function saldo_do_dia(): int
{
    return max(0, LIMITE_DIARIO - enviados_hoje());
}

/**
 * Envia um e-mail pelo painel, registrando tudo.
 * Devolve true/false e preenche $erro.
 */
function enviar_pelo_painel(
    string $para, string $assunto, string $corpo,
    string $tipo = 'individual', ?string &$erro = null
): bool {
    $erro = null;
    $uid  = $_SESSION['usuario']['id'] ?? null;

    if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Endereço inválido: ' . $para;
        return false;
    }
    // Quebra de linha em assunto = injeção de cabeçalho de e-mail
    if (preg_match('/[\r\n]/', $assunto)) {
        $erro = 'Assunto inválido.';
        return false;
    }

    // Assinatura da equipe, para o produtor saber de onde veio
    $corpoFinal = $corpo . "\n\n—\nEquipe AgroAmigo · Projeto ATERPEC\n"
                . "Este e-mail foi enviado por um técnico da equipe.\n";

    $ok  = enviar_email($para, $assunto, $corpoFinal, $erroEnvio);
    $erro = $ok ? null : $erroEnvio;

    try {
        db()->prepare("
            INSERT INTO emails_enviados (enviado_por, destino, assunto, corpo, tipo, sucesso, erro, ip)
            VALUES (:u, :d, :a, :c, :t, :s, :e, :ip)
        ")->execute([
            'u' => $uid, 'd' => $para, 'a' => $assunto, 'c' => $corpo,
            't' => $tipo, 's' => $ok ? 't' : 'f',
            'e' => $erro ? mb_substr($erro, 0, 255) : null, 'ip' => ip_real(),
        ]);
    } catch (Throwable $e) {
        log_erro('registro de envio: ' . $e->getMessage(), __FILE__, __LINE__);
    }

    return $ok;
}

/** Checa as travas antes de deixar enviar N e-mails */
function pode_enviar(int $quantidade, ?string &$motivo = null): bool
{
    $motivo = null;

    if ($quantidade < 1) { $motivo = 'Nenhum destinatário selecionado.'; return false; }

    if (enviados_ultimo_minuto() + $quantidade > LIMITE_POR_MINUTO) {
        $motivo = 'Muitos envios em pouco tempo. Espere um minuto e tente de novo.';
        return false;
    }
    $saldo = saldo_do_dia();
    if ($quantidade > $saldo) {
        $motivo = "O limite diário é de " . LIMITE_DIARIO . " e-mails e só restam {$saldo} hoje. "
                . "Tente amanhã ou envie para menos pessoas.";
        return false;
    }
    return true;
}

/**
 * Lista de produtores para o comunicado geral.
 * Por padrão só quem confirmou o e-mail: mandar para endereço não confirmado
 * gera retorno de erro (bounce), e bounce demais faz o Brevo e o Gmail
 * passarem a jogar tudo do projeto no spam.
 */
function destinatarios_produtores(bool $incluir_nao_verificados = false): array
{
    $sql = "SELECT id, nome, email FROM usuarios
             WHERE status = 'ativo' AND email IS NOT NULL AND role = 'produtor'";
    if (!$incluir_nao_verificados) $sql .= " AND email_verificado";
    $sql .= " ORDER BY nome";
    try {
        return db()->query($sql)->fetchAll();
    } catch (Throwable $e) {
        log_erro('destinatarios_produtores: ' . $e->getMessage(), __FILE__, __LINE__);
        return [];
    }
}
