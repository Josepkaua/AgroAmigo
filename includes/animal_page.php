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
?>

<!-- HERO DA PÁGINA -->
<section class="aa-page-hero<?= $_img_hero ? ' has-bg-img' : '' ?>"
         <?= $_img_hero ? 'style="background-image:url(\'' . htmlspecialchars($_img_hero) . '\')"' : '' ?>>
    <div class="container position-relative">
        <nav class="aa-breadcrumb mb-3" aria-label="breadcrumb">
            <a href="index.php">Início</a>
            <span>/</span>
            <span class="text-white"><?= htmlspecialchars($animal['nome']) ?></span>
        </nav>
        <?php if (!$_img_hero): ?>
        <span class="aa-page-emoji"><?= $animal['emoji'] ?></span>
        <?php endif; ?>
        <h1 class="aa-page-title"><?= htmlspecialchars($animal['nome']) ?></h1>
        <p class="aa-page-desc mt-3"><?= htmlspecialchars($animal['descricao']) ?></p>
    </div>
</section>

<!-- RAÇAS -->
<section class="aa-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="aa-section-badge">Raças no Maranhão</span>
            <h2 class="aa-section-title mt-2">
                Principais Raças e <span class="aa-highlight-section">Espécies</span>
            </h2>
        </div>
        <div class="row g-4 justify-content-center">
            <?php foreach ($_racas as $raca): ?>
            <?php $_raca_img = img_raca($raca['imagem'] ?? ''); ?>
            <div class="col-md-6 col-lg-4">
                <div class="aa-raca-card h-100">
                    <?php if ($_raca_img !== ''): ?>
                    <img src="<?= htmlspecialchars($_raca_img) ?>"
                         alt="<?= htmlspecialchars($raca['nome']) ?>"
                         class="aa-raca-card-img"
                         loading="lazy">
                    <?php endif; ?>
                    <div class="aa-raca-body">
                        <?php if ($_raca_img === ''): ?>
                        <div class="aa-raca-emoji"><?= $raca['emoji'] ?></div>
                        <?php endif; ?>
                        <div class="aa-raca-nome"><?= htmlspecialchars($raca['nome']) ?></div>
                        <div class="aa-raca-tipo"><?= htmlspecialchars($raca['tipo']) ?></div>
                        <p class="aa-raca-desc mt-2"><?= htmlspecialchars($raca['desc']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- GUIA TÉCNICO -->
<section class="aa-guia">
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
                <?php foreach ($animal['topicos'] as $i => $t): ?>
                <button class="aa-guia-item <?= $i === 0 ? 'active' : '' ?>"
                        id="aba-<?= $i ?>"
                        data-bs-toggle="pill"
                        data-bs-target="#painel-<?= $i ?>"
                        type="button" role="tab"
                        aria-controls="painel-<?= $i ?>"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
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
                <?php foreach ($animal['topicos'] as $i => $topico): ?>
                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                     id="painel-<?= $i ?>" role="tabpanel" aria-labelledby="aba-<?= $i ?>" tabindex="0">

                    <div class="aa-painel">
                        <div class="aa-painel-topo">
                            <span class="aa-painel-ico"><?= $topico['icone'] ?></span>
                            <h3><?= htmlspecialchars($topico['titulo']) ?></h3>
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
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>

<!-- CTA WHATSAPP -->
<section class="aa-section">
    <div class="container">
        <div class="aa-cta-box text-center">
            <div class="aa-cta-icon">💬</div>
            <h3 class="aa-cta-title">Ficou com alguma dúvida?</h3>
            <p class="aa-cta-desc">
                Acesse o chatbot no WhatsApp ou fale diretamente com um técnico da equipe ATERPEC.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="contato.php" class="btn aa-btn-whatsapp aa-btn-lg">
                    <i class="bi bi-whatsapp me-2"></i> Chatbot no WhatsApp
                </a>
                <a href="fichas.php" class="btn aa-btn-section-outline aa-btn-lg">
                    <i class="bi bi-file-earmark-text me-2"></i> Baixar Fichas de Controle
                </a>
            </div>
        </div>
    </div>
</section>
