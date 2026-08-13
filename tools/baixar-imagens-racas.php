<?php
/**
 * AgroAmigo — Baixador de fotos de raças (Wikimedia Commons)
 * ---------------------------------------------------------
 * Rode na SUA máquina (tem internet), na raiz do projeto:
 *
 *     php tools/baixar-imagens-racas.php
 *
 * O que ele faz:
 *   1. Consulta a API do Wikimedia Commons pela CATEGORIA de cada raça
 *      (categoria é mais confiável que chutar nome de arquivo).
 *   2. Baixa até 4 CANDIDATOS por raça, já redimensionados a 600px pelo
 *      próprio Wikimedia (não precisa de GD/ImageMagick).
 *   3. Salva em assets/img/racas/_candidatos/<especie>/<slug>-N.jpg
 *   4. Escreve assets/img/racas/CREDITOS.md com autor + licença de cada um.
 *
 * IMPORTANTE: ele NÃO escolhe a foto final sozinho. Depois de rodar, olhe
 * os candidatos (ou mande a pasta pro Claude conferir) e copie o escolhido
 * para assets/img/racas/<especie>/<slug>.jpg — que é o caminho que os
 * arquivos PHP já esperam.
 */

declare(strict_types=1);

const API        = 'https://commons.wikimedia.org/w/api.php';
const UA         = 'AgroAmigo-ATERPEC/1.0 (projeto educacional; https://github.com/Josepkaua/AgroAmigo)';
const LARGURA    = 600;
const POR_RACA   = 4;

// ── Raças a buscar.
// Cada uma tem VÁRIAS categorias candidatas no Commons — o script tenta uma
// por uma até achar imagens (nomes de categoria mudam com o tempo).
$RACAS = [
    ['bovinos',  'nelore',          ['Category:Nelore', 'Category:Nelore cattle', 'Category:Ongole cattle'],
        'Zebu branco/cinza, cupim (corcova) nas costas, barbela e orelhas caídas. NÃO pode ser vaca malhada preta e branca.'],
    ['bovinos',  'girolando',       ['Category:Girolando', 'Category:Girolando cattle'],
        'Mestiço Gir x Holandês: malhado preto/branco COM traços zebuínos (cupim leve, orelha maior).'],
    ['bovinos',  'gir',             ['Category:Gir cattle', 'Category:Gir (cattle)', 'Category:Gyr cattle'],
        'Zebu indiano vermelho/amarelado, orelhas longas tubuladas caídas, testa abaulada, chifres pra trás. NÃO pode ser Highland peludo.'],
    ['aves',     'isa-brown',       ['Category:ISA Brown', 'Category:Brown chickens', 'Category:Laying hens'],
        'Galinha poedeira LISA marrom-avermelhada, sem rajado. NÃO pode ser galinha pintada/malhada.'],
    ['aves',     'pescoco-pelado',  ['Category:Naked Neck chicken', 'Category:Naked Neck chickens', 'Category:Transylvanian Naked Neck'],
        'Galinha/galo com o PESCOÇO SEM PENAS, pele vermelha exposta. Se o pescoço tiver penas, está errado.'],
    ['suinos',   'landrace',        ['Category:Landrace pig', 'Category:Danish Landrace (pig)', 'Category:German Landrace'],
        'Porco branco, corpo bem comprido, ORELHAS GRANDES CAÍDAS cobrindo os olhos. Orelha em pé = errado (aí é Large White).'],
    ['suinos',   'piau',            ['Category:Piau (pig)', 'Category:Pigs of Brazil', 'Category:Domestic pigs in Brazil'],
        'Porco DOMÉSTICO creme/claro com manchas pretas irregulares. NÃO pode ser javali cinza cerdoso com presas.'],
    ['caprinos', 'anglo-nubiano',   ['Category:Anglo-Nubian goat', 'Category:Anglo-Nubian goats', 'Category:Nubian goat'],
        'CABRA de orelhas longas e pendentes, perfil do nariz convexo (arqueado). NÃO pode ser rena, veado ou nada com galhada.'],
    ['ovinos',   'santa-ines',      ['Category:Santa Ines sheep', 'Category:Santa Inês sheep', 'Category:Sheep of Brazil'],
        'Ovelha DESLANADA (pelo curto, sem lã), corpo alto, geralmente marrom/preta. Se tiver lã fofa, está errado.'],
    ['ovinos',   'dorper',          ['Category:Dorper', 'Category:Dorper sheep'],
        'Ovelha corpo BRANCO com CABEÇA PRETA, pelo curto, SEM chifres. Chifre grande e lã = errado.'],
    ['peixes',   'tilapia-do-nilo', ['Category:Oreochromis niloticus'],
        'Tilápia cinza-esverdeada com listras verticais escuras na cauda. NÃO pode ser a tilápia vermelha ornamental.'],
    ['peixes',   'tambaqui',        ['Category:Colossoma macropomum', 'Category:Colossoma'],
        'Peixe redondo e alto, dorso escuro e ventre claro. Precisa ter PEIXE visível — tanque-rede vazio não serve.'],
    ['peixes',   'tambacu',         ['Category:Piaractus mesopotamicus', 'Category:Piaractus'],
        'Híbrido tambaqui x pacu — não existe foto rotulada no Commons. O pacu (Piaractus) é o parente mais próximo; se não servir, use foto real de piscicultura local.'],
];

// ─────────────────────────────────────────────────────────────
/** GET simples — usa cURL se existir, senão file_get_contents. */
function http_get(string $url, int $timeout = 45): string|false
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => UA,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($body !== false && $code === 200) ? $body : false;
    }

    if (!ini_get('allow_url_fopen')) {
        fwrite(STDERR, "ERRO: este PHP não tem cURL nem allow_url_fopen. Habilite um dos dois no php.ini.\n");
        exit(1);
    }
    $ctx = stream_context_create(['http' => [
        'header'  => "User-Agent: " . UA . "\r\n",
        'timeout' => $timeout,
    ]]);
    return @file_get_contents($url, false, $ctx);
}

function api(array $params): array
{
    $params['format'] = 'json';
    $params['formatversion'] = '2';
    $url = API . '?' . http_build_query($params);
    $raw = http_get($url, 40);
    if ($raw === false) {
        throw new RuntimeException("Falha ao consultar a API do Commons.");
    }
    return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
}

function limpa(string $html): string
{
    return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
}

function baixar(string $url, string $destino): bool
{
    $bin = http_get($url, 60);
    if ($bin === false || strlen($bin) < 2000) return false;
    @mkdir(dirname($destino), 0755, true);
    return file_put_contents($destino, $bin) !== false;
}

// ─────────────────────────────────────────────────────────────
$raiz      = dirname(__DIR__);
$baseCand  = $raiz . '/assets/img/racas/_candidatos';
$creditos  = [];
$falhas    = [];

echo "AgroAmigo — baixando candidatos de fotos de raças\n";
echo str_repeat('=', 62), "\n\n";

foreach ($RACAS as [$especie, $slug, $categorias, $criterio]) {
    echo "▶ {$especie}/{$slug}\n";

    // Tenta cada categoria candidata até uma devolver arquivos
    $paginas = [];
    $usada   = '';
    foreach ($categorias as $categoria) {
        try {
            $r = api([
                'action'     => 'query',
                'generator'  => 'categorymembers',
                'gcmtitle'   => $categoria,
                'gcmtype'    => 'file',
                'gcmlimit'   => '30',
                'prop'       => 'imageinfo',
                'iiprop'     => 'url|extmetadata|mime',
                'iiurlwidth' => (string) LARGURA,
            ]);
        } catch (Throwable $e) {
            echo "   ! erro consultando {$categoria}: ", $e->getMessage(), "\n";
            continue;
        }
        $p = $r['query']['pages'] ?? [];
        if ($p) { $paginas = $p; $usada = $categoria; break; }
        echo "   · {$categoria} — vazia, tentando próxima\n";
        usleep(300000);
    }

    if (!$paginas) {
        echo "   ✗ nenhuma categoria devolveu arquivos\n\n";
        $falhas[] = "$especie/$slug — categorias testadas: " . implode(', ', $categorias);
        continue;
    }
    echo "   usando {$usada}\n";

    $n = 0;
    foreach ($paginas as $pg) {
        if ($n >= POR_RACA) break;
        $ii = $pg['imageinfo'][0] ?? null;
        if (!$ii) continue;
        // só fotos rasterizadas
        if (!in_array($ii['mime'] ?? '', ['image/jpeg', 'image/png'], true)) continue;

        $thumb = $ii['thumburl'] ?? $ii['url'];
        $n++;
        $destino = "$baseCand/$especie/$slug-$n.jpg";

        if (!baixar($thumb, $destino)) {
            echo "   ✗ candidato $n: download falhou\n";
            $n--;
            continue;
        }

        $em      = $ii['extmetadata'] ?? [];
        $autor   = limpa($em['Artist']['value']           ?? 'não informado');
        $licenca = limpa($em['LicenseShortName']['value'] ?? 'ver página do arquivo');

        $creditos[] = [
            'arquivo' => "assets/img/racas/_candidatos/$especie/$slug-$n.jpg",
            'raca'    => "$especie / $slug (candidato $n)",
            'origem'  => $ii['descriptionurl'] ?? ('https://commons.wikimedia.org/wiki/' . rawurlencode($pg['title'])),
            'autor'   => mb_substr($autor, 0, 120),
            'licenca' => $licenca,
        ];

        printf("   ✓ candidato %d  (%s)  %s\n", $n, $licenca, basename($pg['title']));
    }

    if ($n === 0) {
        $falhas[] = "$especie/$slug — nenhum arquivo de imagem utilizável na categoria";
        echo "   ✗ nenhum candidato baixado\n";
    }
    echo "   → conferir: o que a foto PRECISA mostrar: $criterio\n\n";
    usleep(400000); // gentileza com a API do Commons
}

// ── CREDITOS.md ─────────────────────────────────────────────
$md  = "# Créditos das imagens de raças\n\n";
$md .= "Gerado por `tools/baixar-imagens-racas.php` em " . date('d/m/Y H:i') . ".\n\n";
$md .= "Todas as imagens vêm do Wikimedia Commons. Ao publicar, mantenha o crédito\n";
$md .= "do autor e a licença. Confira a licença exata na página de origem.\n\n";
$md .= "| Arquivo | Raça | Autor | Licença | Origem |\n|---|---|---|---|---|\n";
foreach ($creditos as $c) {
    $md .= sprintf("| `%s` | %s | %s | %s | [Commons](%s) |\n",
        $c['arquivo'], $c['raca'], str_replace('|', '/', $c['autor']), $c['licenca'], $c['origem']);
}
@mkdir("$raiz/assets/img/racas", 0755, true);
$destinoMd = "$raiz/assets/img/racas/CREDITOS.md";
// Preserva um CREDITOS.md que já exista (ex.: o checklist manual antigo)
if (is_file($destinoMd)) {
    $backup = "$raiz/assets/img/racas/CREDITOS-anterior.md";
    @copy($destinoMd, $backup);
    echo "\n(CREDITOS.md antigo preservado em assets/img/racas/CREDITOS-anterior.md)\n";
}
file_put_contents($destinoMd, $md);

// ── Resumo ──────────────────────────────────────────────────
echo str_repeat('=', 62), "\n";
printf("Baixados: %d candidatos.  Créditos em assets/img/racas/CREDITOS.md\n", count($creditos));
if ($falhas) {
    echo "\nSem resultado (resolver manualmente):\n";
    foreach ($falhas as $f) echo "  • $f\n";
}
echo "\nPRÓXIMO PASSO — escolher a foto final de cada raça:\n";
echo "  Olhe os candidatos em assets/img/racas/_candidatos/ e copie o bom para\n";
echo "  assets/img/racas/<especie>/<slug>.jpg  (sem o -N no fim).\n";
echo "  Ex.:  cp assets/img/racas/_candidatos/bovinos/nelore-2.jpg \\\n";
echo "            assets/img/racas/bovinos/nelore.jpg\n";
echo "\n  Enquanto o arquivo final não existir, o site mostra o emoji da raça\n";
echo "  no lugar — nada quebra.\n";
