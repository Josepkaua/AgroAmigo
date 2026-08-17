<?php
/**
 * Conteúdo editável pelo painel — AgroAmigo
 *
 * Regra de ouro deste arquivo: NUNCA deixar a página em branco.
 * Se o banco estiver fora do ar, se a migration ainda não tiver rodado ou se
 * ninguém tiver editado nada, tudo cai no conteúdo original que já está no
 * código. O site continua igual ao que é hoje.
 */
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

/** Cache por requisição — evita bater no banco várias vezes na mesma página */
function _cache_conteudo(): array
{
    static $c = ['racas' => [], 'blocos' => null];
    return $c;
}

/**
 * Raças de uma espécie, vindas do banco.
 * $padrao é o array que já existe no arquivo PHP da espécie e é usado como
 * rede de segurança.
 */
function racas_da_especie(string $especie, array $padrao = []): array
{
    static $cache = [];
    if (isset($cache[$especie])) return $cache[$especie];

    try {
        $pdo = db_opcional();
        if (!$pdo) return $cache[$especie] = $padrao;
        $st = $pdo->prepare("
            SELECT emoji, nome, tipo, descricao, imagem
              FROM racas
             WHERE especie = :e AND ativo
             ORDER BY ordem, nome
        ");
        $st->execute(['e' => $especie]);
        $linhas = $st->fetchAll();

        if ($linhas) {
            $cache[$especie] = array_map(fn($r) => [
                'emoji'  => $r['emoji']     ?: '🐾',
                'nome'   => $r['nome'],
                'tipo'   => $r['tipo']      ?? '',
                'desc'   => $r['descricao'] ?? '',
                'imagem' => $r['imagem']    ?? '',
            ], $linhas);
            return $cache[$especie];
        }
    } catch (Throwable $e) {
        // Tabela ainda não existe (migration não rodou) ou banco fora — segue no padrão
        log_erro('racas_da_especie: ' . $e->getMessage(), __FILE__, __LINE__);
    }

    return $cache[$especie] = $padrao;
}

/** Carrega todos os blocos de uma vez (uma consulta por requisição) */
function _blocos_conteudo(): array
{
    static $blocos = null;
    if ($blocos !== null) return $blocos;

    $blocos = [];
    try {
        $pdo = db_opcional();
        if (!$pdo) return $blocos;
        foreach ($pdo->query("SELECT chave, valor FROM conteudo_site")->fetchAll() as $b) {
            $blocos[$b['chave']] = $b['valor'];
        }
    } catch (Throwable $e) {
        log_erro('_blocos_conteudo: ' . $e->getMessage(), __FILE__, __LINE__);
    }
    return $blocos;
}

/**
 * Valor de um bloco editável.
 * Se ninguém editou ainda (ou deu erro), devolve $padrao — o texto que já
 * está escrito no site hoje.
 */
function conteudo(string $chave, string $padrao = ''): string
{
    $b = _blocos_conteudo();
    $v = $b[$chave] ?? null;
    return ($v === null || trim((string) $v) === '') ? $padrao : (string) $v;
}

/** Lista completa para a tela de edição, agrupada */
function conteudo_para_editar(): array
{
    try {
        $r = db()->query("
            SELECT chave, grupo, rotulo, ajuda, tipo, valor, ordem
              FROM conteudo_site
             ORDER BY grupo, ordem, rotulo
        ")->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
    $out = [];
    foreach ($r as $linha) $out[$linha['grupo']][] = $linha;
    return $out;
}

/** Quem pode editar conteúdo: admin e técnico */
function pode_editar_conteudo(): bool
{
    $u = usuario_logado();
    return in_array($u['role'] ?? '', ['admin', 'tecnico'], true);
}

/** Guard para as telas de edição */
function require_editor(): array
{
    $u = require_login('/login.php');
    if (!in_array($u['role'] ?? '', ['admin', 'tecnico'], true)) {
        http_response_code(403);
        include __DIR__ . '/../404.php';
        exit;
    }
    return $u;
}
