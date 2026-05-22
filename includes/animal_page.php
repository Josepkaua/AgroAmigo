<?php
// Template para páginas de animais.
// Vars esperadas: $animal (array com nome, emoji, descricao, racas, topicos)
?>

<!-- HERO DA PÁGINA -->
<section class="aa-page-hero">
    <div class="container position-relative">
        <nav class="aa-breadcrumb mb-3" aria-label="breadcrumb">
            <a href="index.php">Início</a>
            <span>/</span>
            <span class="text-white"><?= htmlspecialchars($animal['nome']) ?></span>
        </nav>
        <span class="aa-page-emoji"><?= $animal['emoji'] ?></span>
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
            <?php foreach ($animal['racas'] as $raca): ?>
            <div class="col-md-6 col-lg-4">
                <div class="aa-raca-card h-100">
                    <div class="aa-raca-emoji"><?= $raca['emoji'] ?></div>
                    <div class="aa-raca-nome"><?= htmlspecialchars($raca['nome']) ?></div>
                    <div class="aa-raca-tipo"><?= htmlspecialchars($raca['tipo']) ?></div>
                    <p class="aa-raca-desc mt-2"><?= htmlspecialchars($raca['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- TÓPICOS TÉCNICOS -->
<section class="aa-section-alt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="aa-section-badge">Guia Técnico</span>
            <h2 class="aa-section-title mt-2">
                Orientações de <span class="aa-highlight-section">Criação</span>
            </h2>
            <p class="aa-section-desc">Clique em cada tópico para expandir as informações</p>
        </div>

        <div class="accordion aa-accordion" id="topicosAccordion">
            <?php foreach ($animal['topicos'] as $i => $topico): ?>
            <div class="accordion-item aa-accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button aa-accordion-btn <?= $i > 0 ? 'collapsed' : '' ?>"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#topico<?= $i ?>"
                            aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                        <span class="aa-topic-icon"><?= $topico['icone'] ?></span>
                        <?= htmlspecialchars($topico['titulo']) ?>
                    </button>
                </h2>
                <div id="topico<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                     data-bs-parent="#topicosAccordion">
                    <div class="accordion-body aa-accordion-body">
                        <p><?= nl2br(htmlspecialchars($topico['intro'])) ?></p>
                        <?php if (!empty($topico['dicas'])): ?>
                        <ul class="aa-dicas mt-3">
                            <?php foreach ($topico['dicas'] as $dica): ?>
                                <li><?= htmlspecialchars($dica) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
