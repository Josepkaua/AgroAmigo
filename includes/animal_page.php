<?php
// Template para páginas de animais.
// Vars esperadas: $animal (array com nome, emoji, imagem, descricao, racas, topicos)
require_once __DIR__ . '/conteudo.php';

// As raças vêm do banco (editáveis pelo painel). Se a migration ainda não
// rodou ou o banco falhar, cai no array que está no arquivo da espécie —
// o site nunca fica sem conteúdo.
$_racas = racas_da_especie($pagina ?? '', $animal['racas'] ?? []);

// Banner da página, também editável (chave banner_bovinos, banner_aves, ...)
$_img_hero = $animal['imagem'] ?? '';
if (!empty($pagina)) {
    $_hero_editado = conteudo('banner_' . $pagina, '');
    if ($_hero_editado !== '') {
        $_img_hero = img_raca($_hero_editado) ?: $_img_hero;
    }
}

$_topicos   = $animal['topicos'] ?? [];
$_qtd_top   = count($_topicos);
$_qtd_racas = count($_racas);

// Data de revisão mais recente entre os tópicos (para o selo do hero)
$_revisado = '';
foreach ($_topicos as $_t) {
    if (!empty($_t['revisado'])) { $_revisado = $_t['revisado']; break; }
}
?>

<!-- ══════════════════ HERO DA ESPÉCIE ══════════════════ -->
<section class="aa-hero-esp<?= $_img_hero ? ' com-foto' : '' ?>"
         <?= $_img_hero ? 'style="--foto:url(\'' . htmlspecialchars($_img_hero) . '\')"' : '' ?>>

    <div class="container aa-hero-esp-inner">
        <nav class="aa-breadcrumb" aria-label="breadcrumb">
            <a href="index.php">Início</a>
            <span>/</span>
            <span class="text-white"><?= htmlspecialchars($animal['nome']) ?></span>
        </nav>

        <div class="aa-hero-esp-corpo">
            <span class="aa-hero-esp-emoji" aria-hidden="true"><?= $animal['emoji'] ?></span>
            <h1 class="aa-hero-esp-titulo"><?= htmlspecialchars($animal['nome']) ?></h1>
            <p class="aa-hero-esp-desc"><?= htmlspecialchars($animal['descricao']) ?></p>

            <ul class="aa-hero-chips">
                <?php if ($_qtd_racas): ?>
                <li><i class="bi bi-collection"></i> <?= $_qtd_racas ?> raças da região</li>
                <?php endif; ?>
                <?php if ($_qtd_top): ?>
                <li><i class="bi bi-list-check"></i> <?= $_qtd_top ?> assuntos no guia</li>
                <?php endif; ?>
                <?php if ($_revisado !== ''): ?>
                <li><i class="bi bi-patch-check-fill"></i> revisado em <?= htmlspecialchars($_revisado) ?></li>
                <?php endif; ?>
            </ul>

            <div class="aa-hero-esp-acoes">
                <a href="#guia" class="aa-btn-hero-1">
                    <i class="bi bi-book-half"></i> Ver o guia técnico
                </a>
                <a href="contato.php" class="aa-btn-hero-2">
                    <i class="bi bi-whatsapp"></i> Falar com um técnico
                </a>
            </div>
        </div>
    </div>

    <div class="aa-hero-curva" aria-hidden="true"></div>
</section>

<!-- ══════════════════ RAÇAS ══════════════════ -->
<section class="aa-sec-clara">
    <div class="container">
        <header class="aa-sec-cab">
            <span class="aa-sec-tag">Raças no Maranhão</span>
            <h2 class="aa-sec-tit">As raças que dão certo <span>aqui</span></h2>
            <p class="aa-sec-sub">
                Adaptação ao calor, ao pasto e à água da região pesa mais que o nome da raça.
            </p>
        </header>

        <div class="aa-racas-grid">
            <?php foreach ($_racas as $i => $raca): ?>
            <?php $_raca_img = img_raca($raca['imagem'] ?? ''); ?>
            <article class="aa-raca2">
                <div class="aa-raca2-foto<?= $_raca_img === '' ? ' sem-foto' : '' ?>">
                    <?php if ($_raca_img !== ''): ?>
                        <img src="<?= htmlspecialchars($_raca_img) ?>"
                             alt="<?= htmlspecialchars($raca['nome']) ?>" loading="lazy">
                    <?php else: ?>
                        <span class="aa-raca2-emoji"><?= $raca['emoji'] ?></span>
                    <?php endif; ?>
                    <?php if (!empty($raca['tipo'])): ?>
                        <span class="aa-raca2-badge"><?= htmlspecialchars($raca['tipo']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="aa-raca2-corpo">
                    <h3 class="aa-raca2-nome"><?= htmlspecialchars($raca['nome']) ?></h3>
                    <?php if (!empty($raca['desc'])): ?>
                    <details class="aa-raca2-det">
                        <summary>
                            <p class="aa-raca2-desc"><?= htmlspecialchars($raca['desc']) ?></p>
                            <span class="aa-raca2-toggle">
                                <span class="txt-mais">Ler a ficha completa</span>
                                <span class="txt-menos">Mostrar menos</span>
                                <i class="bi bi-chevron-down"></i>
                            </span>
                        </summary>
                    </details>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════ GUIA TÉCNICO ══════════════════ -->
<section class="aa-guia" id="guia">
    <span class="aa-guia-marca" aria-hidden="true"><?= $animal['emoji'] ?></span>

    <div class="container">

        <header class="aa-guia-head">
            <span class="aa-guia-tag">Guia Técnico</span>
            <h2 class="aa-guia-titulo">
                Como criar <span><?= htmlspecialchars(mb_strtolower($animal['nome'])) ?></span> do jeito certo
            </h2>
            <p class="aa-guia-sub">
                Orientação prática para o pequeno produtor do Maranhão.
                Escolha o assunto e veja o passo a passo.
            </p>
        </header>

        <div class="aa-guia-grid">

            <!-- Navegação dos assuntos -->
            <nav class="aa-guia-nav" role="tablist" aria-label="Assuntos do guia técnico">
                <?php foreach ($_topicos as $i => $t): ?>
                <button class="aa-guia-item <?= $i === 0 ? 'active' : '' ?>"
                        id="aba-<?= $i ?>"
                        data-bs-toggle="pill"
                        data-bs-target="#painel-<?= $i ?>"
                        type="button" role="tab"
                        aria-controls="painel-<?= $i ?>"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
                    <span class="aa-guia-item-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="aa-guia-item-ico"><?= $t['icone'] ?></span>
                    <span class="aa-guia-item-txt">
                        <strong><?= htmlspecialchars($t['titulo']) ?></strong>
                        <?php if (!empty($t['passos'])): ?>
                        <small><?= count($t['passos']) ?> passos</small>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-right"></i>
                </button>
                <?php endforeach; ?>
            </nav>

            <!-- Painéis -->
            <div class="tab-content aa-guia-conteudo">
                <?php foreach ($_topicos as $i => $topico): ?>
                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                     id="painel-<?= $i ?>" role="tabpanel" aria-labelledby="aba-<?= $i ?>" tabindex="0">

                    <div class="aa-painel">
                        <div class="aa-painel-barra" aria-hidden="true">
                            <span style="width:<?= $_qtd_top ? round(($i + 1) / $_qtd_top * 100) : 0 ?>%"></span>
                        </div>
                        <div class="aa-painel-topo">
                            <span class="aa-painel-ico"><?= $topico['icone'] ?></span>
                            <div class="aa-painel-tit">
                                <span class="aa-painel-conta">Assunto <?= $i + 1 ?> de <?= $_qtd_top ?></span>
                                <h3><?= htmlspecialchars($topico['titulo']) ?></h3>
                            </div>
                        </div>

                        <?php if (!empty($topico['porque'])): ?>
                            <p class="aa-lead"><?= nl2br(htmlspecialchars($topico['porque'])) ?></p>
                        <?php elseif (!empty($topico['intro'])): ?>
                            <p class="aa-lead"><?= nl2br(htmlspecialchars($topico['intro'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($topico['passos'])): ?>
                        <div class="aa-secao">
                            <h4 class="aa-secao-tit"><span>01</span> O que fazer</h4>
                            <ol class="aa-linha-tempo">
                                <?php foreach ($topico['passos'] as $p): ?>
                                <li>
                                    <strong><?= htmlspecialchars($p['acao']) ?></strong>
                                    <?php if (!empty($p['detalhe'])): ?>
                                        <p><?= htmlspecialchars($p['detalhe']) ?></p>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($topico['numeros'])): ?>
                        <div class="aa-secao">
                            <h4 class="aa-secao-tit"><span>02</span> Números de referência</h4>
                            <div class="aa-tiles">
                                <?php foreach ($topico['numeros'] as $n): ?>
                                <div class="aa-tile">
                                    <span class="aa-tile-valor"><?= htmlspecialchars($n[1]) ?></span>
                                    <span class="aa-tile-rot"><?= htmlspecialchars($n[0]) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($topico['alerta'])): ?>
                        <div class="aa-secao">
                            <div class="aa-chamado">
                                <div class="aa-chamado-cab">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>Pare e chame o técnico se</span>
                                </div>
                                <ul>
                                    <?php foreach ($topico['alerta'] as $a): ?>
                                        <li><?= htmlspecialchars($a) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="contato.php" class="aa-chamado-btn">
                                    <i class="bi bi-whatsapp"></i> Falar com um técnico agora
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($topico['dicas'])): ?>
                        <div class="aa-secao">
                            <h4 class="aa-secao-tit"><span>01</span> Pontos de atenção</h4>
                            <ul class="aa-dicas"><?php foreach ($topico['dicas'] as $d): ?>
                                <li><?= htmlspecialchars($d) ?></li>
                            <?php endforeach; ?></ul>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($topico['fonte'])): ?>
                        <footer class="aa-painel-fonte">
                            <i class="bi bi-patch-check-fill"></i>
                            <span>
                                Baseado em
                                <?php foreach ($topico['fonte'] as $k => $f): ?>
                                    <?= $k > 0 ? ' e ' : '' ?>
                                    <?php if (!empty($f['url'])): ?>
                                        <a href="<?= htmlspecialchars($f['url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($f['texto']) ?></a>
                                    <?php else: ?><?= htmlspecialchars($f['texto']) ?><?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!empty($topico['revisado'])): ?>
                                    · revisado em <?= htmlspecialchars($topico['revisado']) ?>
                                <?php endif; ?>
                            </span>
                        </footer>
                        <?php endif; ?>

                        <?php if ($_qtd_top > 1): ?>
                        <nav class="aa-painel-passa">
                            <?php if ($i > 0): ?>
                            <button type="button" class="aa-passa-btn"
                                    data-bs-toggle="pill" data-bs-target="#painel-<?= $i - 1 ?>">
                                <i class="bi bi-arrow-left"></i>
                                <span><small>Anterior</small><?= htmlspecialchars($_topicos[$i - 1]['titulo']) ?></span>
                            </button>
                            <?php else: ?><span></span><?php endif; ?>

                            <?php if ($i < $_qtd_top - 1): ?>
                            <button type="button" class="aa-passa-btn fim"
                                    data-bs-toggle="pill" data-bs-target="#painel-<?= $i + 1 ?>">
                                <span><small>Próximo</small><?= htmlspecialchars($_topicos[$i + 1]['titulo']) ?></span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                            <?php endif; ?>
                        </nav>
                        <?php endif; ?>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════ CTA ══════════════════ -->
<section class="aa-sec-clara aa-sec-cta">
    <div class="container">
        <div class="aa-cta2">
            <div class="aa-cta2-txt">
                <span class="aa-cta2-tag"><i class="bi bi-chat-dots-fill"></i> Ficou com dúvida?</span>
                <h3>A gente responde — de graça, no WhatsApp</h3>
                <p>
                    Fale com um técnico da equipe ATERPEC ou baixe as fichas de controle
                    para anotar vacina, peso e produção da sua criação.
                </p>
                <div class="aa-cta2-acoes">
                    <a href="contato.php" class="aa-btn-hero-1">
                        <i class="bi bi-whatsapp"></i> Chamar no WhatsApp
                    </a>
                    <a href="fichas.php" class="aa-btn-cta-linha">
                        <i class="bi bi-file-earmark-text"></i> Baixar fichas de controle
                    </a>
                </div>
            </div>
            <div class="aa-cta2-arte" aria-hidden="true">
                <span><?= $animal['emoji'] ?></span>
            </div>
        </div>
    </div>
</section>
