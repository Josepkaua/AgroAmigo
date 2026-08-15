<?php
/**
 * Upload de imagem pelo painel — AgroAmigo
 *
 * Esta é a primeira funcionalidade do site que aceita arquivo de fora, e é
 * exatamente por aí que sites desse tipo costumam ser invadidos. As defesas:
 *
 *  1. Só admin/técnico logado chega aqui (checado na página que chama).
 *  2. Tamanho limitado antes de qualquer processamento.
 *  3. O tipo NÃO é decidido pela extensão nem pelo que o navegador diz —
 *     ambos são controlados por quem envia. Vale o que getimagesize() detecta.
 *  4. A imagem é REDESENHADA pelo GD e salva de novo. Isso é o mais importante:
 *     um arquivo "polyglot" (JPEG válido com PHP escondido dentro) não
 *     sobrevive a ser decodificado e recodificado — o código embutido some.
 *  5. Guardada no banco, não em disco, então nem existe caminho de arquivo
 *     para alguém tentar executar.
 *  6. Redimensionada sozinha, para o técnico poder mandar foto do celular
 *     sem se preocupar com tamanho.
 */
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

const UPLOAD_MAX_BYTES = 8 * 1024 * 1024;   // 8 MB de entrada
const UPLOAD_MAX_LARG  = 1600;              // reduz o que passar disso

/**
 * Processa um arquivo de $_FILES e guarda no banco.
 * Devolve o UUID da imagem, ou null (com $erro preenchido) em caso de falha.
 */
function salvar_imagem(array $arquivo, ?string &$erro = null, int $largura_alvo = 900): ?string
{
    $erro = null;

    // ── 1. O upload chegou inteiro? ──
    if (!isset($arquivo['error']) || is_array($arquivo['error'])) {
        $erro = 'Envio inválido.'; return null;
    }
    switch ($arquivo['error']) {
        case UPLOAD_ERR_OK: break;
        case UPLOAD_ERR_NO_FILE:
            $erro = 'Nenhum arquivo foi escolhido.'; return null;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $erro = 'A imagem é grande demais (máximo 8 MB).'; return null;
        default:
            $erro = 'Falha no envio do arquivo. Tente de novo.'; return null;
    }

    if ($arquivo['size'] > UPLOAD_MAX_BYTES) {
        $erro = 'A imagem passa de 8 MB. Reduza ou tire outra foto.'; return null;
    }
    // Garante que veio mesmo de um upload HTTP, e não é um caminho forjado
    if (!is_uploaded_file($arquivo['tmp_name'])) {
        $erro = 'Arquivo inválido.'; return null;
    }

    // ── 2. É imagem de verdade? ──
    // getimagesize lê o cabeçalho binário. Renomear vírus.php para foto.jpg
    // não engana esta checagem.
    $info = @getimagesize($arquivo['tmp_name']);
    if ($info === false) {
        $erro = 'Esse arquivo não é uma imagem. Envie JPG, PNG ou WEBP.'; return null;
    }
    [$larg, $alt] = $info;
    $mime = $info['mime'] ?? '';

    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($permitidos[$mime])) {
        $erro = 'Formato não aceito. Use JPG, PNG ou WEBP.'; return null;
    }
    if ($larg < 200 || $alt < 150) {
        $erro = "Imagem pequena demais ({$larg}x{$alt}). Use pelo menos 600x400."; return null;
    }
    // Proteção contra "decompression bomb": imagem pequena no arquivo mas gigante
    // ao abrir, que estoura a memória do servidor.
    if ($larg * $alt > 50_000_000) {
        $erro = 'Imagem com resolução alta demais.'; return null;
    }

    // ── 3. Redesenha a imagem (é aqui que qualquer payload embutido morre) ──
    if (!extension_loaded('gd')) {
        $erro = 'O servidor está sem a biblioteca de imagens (GD). Avise o responsável técnico.';
        log_erro('Upload sem GD instalado', __FILE__, __LINE__);
        return null;
    }

    $origem = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($arquivo['tmp_name']),
        'image/png'  => @imagecreatefrompng($arquivo['tmp_name']),
        'image/webp' => @imagecreatefromwebp($arquivo['tmp_name']),
    };
    if (!$origem) {
        $erro = 'Não consegui ler essa imagem. Tente salvar como JPG e enviar de novo.';
        return null;
    }

    // ── 4. Redimensiona ──
    $alvo = min($largura_alvo, UPLOAD_MAX_LARG);
    if ($larg > $alvo) {
        $nova_alt = (int) round($alt * $alvo / $larg);
        $destino  = imagecreatetruecolor($alvo, $nova_alt);
        // Fundo branco: PNG/WEBP com transparência viraria preto no JPG
        imagefill($destino, 0, 0, imagecolorallocate($destino, 255, 255, 255));
        imagecopyresampled($destino, $origem, 0, 0, 0, 0, $alvo, $nova_alt, $larg, $alt);
        imagedestroy($origem);
        $origem = $destino;
        $larg = $alvo; $alt = $nova_alt;
    }

    // ── 5. Salva sempre como JPEG (formato único, sem metadados do original) ──
    ob_start();
    imagejpeg($origem, null, 84);
    $bytes = (string) ob_get_clean();
    imagedestroy($origem);

    if ($bytes === '' || strlen($bytes) < 500) {
        $erro = 'Falha ao processar a imagem.'; return null;
    }

    // ── 6. Guarda no banco ──
    try {
        $nome = preg_replace('/[^\w.\- ]/u', '', (string) ($arquivo['name'] ?? 'imagem'));
        $st = db()->prepare("
            INSERT INTO imagens (nome, mime, largura, altura, tamanho, conteudo, enviado_por)
            VALUES (:n, 'image/jpeg', :l, :a, :t, :c, :u)
            RETURNING id
        ");
        $st->bindValue(':n', mb_substr($nome ?: 'imagem', 0, 150));
        $st->bindValue(':l', $larg, PDO::PARAM_INT);
        $st->bindValue(':a', $alt,  PDO::PARAM_INT);
        $st->bindValue(':t', strlen($bytes), PDO::PARAM_INT);
        $st->bindValue(':c', $bytes, PDO::PARAM_LOB);
        $st->bindValue(':u', $_SESSION['usuario']['id'] ?? null);
        $st->execute();
        return (string) $st->fetchColumn();
    } catch (Throwable $e) {
        log_erro('salvar_imagem: ' . $e->getMessage(), __FILE__, __LINE__);
        $erro = 'Não consegui guardar a imagem. Tente novamente.';
        return null;
    }
}

/** Referência gravada em racas.imagem / conteudo_site.valor para imagem do banco */
function ref_imagem(string $uuid): string
{
    return 'db:' . $uuid;
}

/** true se o valor aponta para uma imagem guardada no banco */
function e_imagem_do_banco(?string $valor): bool
{
    return is_string($valor) && str_starts_with($valor, 'db:');
}

/** Converte a referência do banco na URL pública que serve a imagem */
function url_imagem_banco(string $valor, string $prefixo = ''): string
{
    return $prefixo . 'imagem.php?id=' . rawurlencode(substr($valor, 3));
}
