<?php
declare(strict_types=1);
require_once 'includes/auth.php';
require_once 'includes/emails.php';
session_init(); // conteúdo público — não exige login

$pagina        = 'contato';
$titulo_pagina = 'Falar com Técnico';

$sucesso     = false;
$erro_envio  = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nome     = trim($_POST['nome']     ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($nome !== '' && $mensagem !== '') {
        try {
            db()->prepare("
                INSERT INTO mensagens_contato (usuario_id, nome, telefone, email, animal, topico, mensagem, ip)
                VALUES (:uid, :nome, :tel, :email, :animal, :topico, :msg, :ip)
            ")->execute([
                'uid'    => usuario_logado()['id'] ?? null,
                'nome'   => $nome,
                'tel'    => trim($_POST['telefone'] ?? '') ?: null,
                'email'  => filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: null,
                'animal' => trim($_POST['animal']   ?? '') ?: null,
                'topico' => trim($_POST['topico']   ?? '') ?: null,
                'msg'    => $mensagem,
                'ip'     => ip_real(),
            ]);
            $sucesso = true;

            // Avisa a caixa oficial do projeto. A mensagem JÁ foi gravada acima,
            // então mesmo que o e-mail falhe nada se perde — por isso o aviso
            // vem depois de salvar, e nunca no lugar de salvar.
            avisar_nova_mensagem([
                'nome'     => $nome,
                'email'    => trim($_POST['email'] ?? ''),
                'telefone' => trim($_POST['telefone'] ?? ''),
                'animal'   => trim($_POST['animal']   ?? ''),
                'topico'   => trim($_POST['topico']   ?? ''),
                'mensagem' => $mensagem,
            ]);
        } catch (Throwable $e) {
            log_erro('Falha ao salvar mensagem de contato: ' . $e->getMessage(), __FILE__, __LINE__);
            $erro_envio = true;
        }
    }
}

// Chatbot no WhatsApp — só mostra o link quando o número estiver configurado
$whatsapp_numero = defined('WHATSAPP_NUMERO') ? trim(WHATSAPP_NUMERO) : '';
$whatsapp_pronto = $whatsapp_numero !== '';
$whatsapp_link   = $whatsapp_pronto
    ? 'https://wa.me/' . $whatsapp_numero . '?text=' . rawurlencode('Olá! Quero acessar o ATERPEC')
    : null;

require 'includes/header.php';
?>

<!-- HERO DA PÁGINA -->
<section class="aa-page-hero">
    <div class="container position-relative">
        <nav class="aa-breadcrumb mb-3">
            <a href="index.php">Início</a>
            <span>/</span>
            <span class="text-white">Falar com Técnico</span>
        </nav>
        <span class="aa-page-emoji">💬</span>
        <h1 class="aa-page-title">Falar com Técnico</h1>
        <p class="aa-page-desc mt-3">
            Tire suas dúvidas pelo chatbot no WhatsApp ou envie uma mensagem diretamente
            para a equipe técnica do ATERPEC.
        </p>
    </div>
</section>

<!-- WHATSAPP CTA -->
<section class="aa-section">
    <div class="container">
        <div class="row g-5 align-items-center">

            <!-- WhatsApp -->
            <div class="col-lg-5">
                <div class="aa-whatsapp-card">
                    <div class="aa-wpp-logo">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h3 class="aa-wpp-titulo">Chatbot no WhatsApp</h3>
                    <p class="aa-wpp-desc">
                        Acesse o assistente virtual ATERPEC diretamente pelo WhatsApp.
                        Disponível 24 horas, com informações sobre ambiência, vacinação,
                        nutrição, manejo e biosseguridade.
                    </p>
                    <ul class="aa-wpp-features mb-4">
                        <li><i class="bi bi-check-circle-fill me-2"></i> Respostas instantâneas</li>
                        <li><i class="bi bi-check-circle-fill me-2"></i> Sem precisar instalar nada</li>
                        <li><i class="bi bi-check-circle-fill me-2"></i> Funciona mesmo com internet lenta</li>
                        <li><i class="bi bi-check-circle-fill me-2"></i> Informações sobre 6 espécies animais</li>
                    </ul>
                    <?php if ($whatsapp_pronto): ?>
                    <a href="<?= h($whatsapp_link) ?>"
                       target="_blank" rel="noopener" class="btn aa-btn-whatsapp w-100 aa-btn-lg">
                        <i class="bi bi-whatsapp me-2"></i> Abrir Chatbot no WhatsApp
                    </a>
                    <?php else: ?>
                    <button type="button" class="btn aa-btn-whatsapp w-100 aa-btn-lg" disabled
                            style="opacity:.55;cursor:not-allowed" title="Chatbot em fase final de configuração">
                        <i class="bi bi-whatsapp me-2"></i> Chatbot no WhatsApp — em breve
                    </button>
                    <p class="text-center mt-2 small" style="color: rgba(255,255,255,.6);">
                        Ainda estamos configurando o número oficial. Por enquanto, use o formulário ao lado.
                    </p>
                    <?php endif; ?>
                    <p class="text-center mt-3 small" style="color: rgba(255,255,255,.6);">
                        Projeto ATERPEC — Verde Conecta / UEMA
                    </p>
                </div>
            </div>

            <!-- Formulário -->
            <div class="col-lg-7">
                <div class="aa-contact-card">
                    <h4 class="mb-1" style="color:#166534; font-weight:800;">Enviar Mensagem ao Técnico</h4>
                    <p class="text-muted small mb-4">Para dúvidas mais complexas, nossa equipe responde em até 48 horas.</p>

                    <?php if ($sucesso): ?>
                    <div class="aa-alert-success mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Mensagem recebida! Nossa equipe técnica vai analisar e entrar em contato em breve.
                    </div>
                    <?php elseif ($erro_envio): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Não conseguimos salvar sua mensagem agora. Tente novamente ou use o WhatsApp ao lado.
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="contato.php#formulario" id="formulario">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="aa-label" for="nome">Nome completo *</label>
                                <input type="text" id="nome" name="nome" class="form-control aa-input mt-1"
                                       placeholder="Seu nome"
                                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="aa-label" for="telefone">WhatsApp</label>
                                <input type="tel" id="telefone" name="telefone" class="form-control aa-input mt-1"
                                       placeholder="(XX) 9 9999-0000"
                                       value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="aa-label" for="email">
                                    E-mail <span style="font-weight:400;color:#9ca3af;font-size:11px">(opcional)</span>
                                </label>
                                <input type="email" id="email" name="email" class="form-control aa-input mt-1"
                                       placeholder="seu@email.com"
                                       value="<?= htmlspecialchars($_POST['email'] ?? (usuario_logado()['email'] ?? '')) ?>">
                                <small style="font-size:11px;color:#9ca3af">
                                    Se informar, o técnico pode responder por e-mail. Senão, respondemos pelo telefone.
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="aa-label" for="animal">Animal</label>
                                <select id="animal" name="animal" class="form-select aa-input mt-1">
                                    <option value="">Selecione...</option>
                                    <?php
                                    $animais_opt = ['Bovinos','Aves','Suínos','Caprinos','Ovinos','Peixes','Outro'];
                                    foreach ($animais_opt as $opt): ?>
                                        <option value="<?= $opt ?>" <?= ($_POST['animal'] ?? '') === $opt ? 'selected' : '' ?>>
                                            <?= $opt ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="aa-label" for="topico">Tópico</label>
                                <select id="topico" name="topico" class="form-select aa-input mt-1">
                                    <option value="">Selecione...</option>
                                    <?php
                                    $topicos_opt = ['Ambiência','Vacinação','Nutrição','Manejo','Biosseguridade','Outro'];
                                    foreach ($topicos_opt as $opt): ?>
                                        <option value="<?= $opt ?>" <?= ($_POST['topico'] ?? '') === $opt ? 'selected' : '' ?>>
                                            <?= $opt ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="aa-label" for="mensagem">Mensagem *</label>
                                <textarea id="mensagem" name="mensagem" rows="4"
                                          class="form-control aa-input mt-1"
                                          placeholder="Descreva sua dúvida ou situação..." required><?= htmlspecialchars($_POST['mensagem'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn aa-btn-primary w-100 aa-btn-lg">
                                    <i class="bi bi-send-fill me-2"></i> Enviar Mensagem
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SOBRE O PROJETO -->
<section class="aa-section-alt">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <span class="aa-section-badge">Sobre o Projeto</span>
                <h2 class="aa-section-title mt-2 mb-3">
                    Projeto <span class="aa-highlight-section">ATERPEC</span>
                </h2>
                <p class="aa-section-desc mb-3">
                    O ATERPEC é um projeto desenvolvido pela equipe <strong class="text-white">Verde Conecta</strong>
                    para a 1° Jornada de Inovação da Agricultura Familiar — SAF Maranhão e Agência Marandu/UEMA.
                </p>
                <p class="aa-section-desc">
                    Nosso objetivo é tornar a assistência técnica mais acessível, moderna e contínua
                    para pequenos produtores do Maranhão, usando tecnologia digital e dados territoriais.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <?php
                    $info = [
                        ['🏛️','Realização','SAF Maranhão / Agência Marandu — UEMA'],
                        ['👥','Equipe','Verde Conecta'],
                        ['🎯','Eixo','VI — Assistência Técnica e Extensão Rural'],
                        ['📍','Foco','Pequenos produtores do Maranhão'],
                    ];
                    foreach ($info as $item): ?>
                    <div class="col-12">
                        <div class="aa-info-item">
                            <span class="aa-info-icon"><?= $item[0] ?></span>
                            <div>
                                <div class="aa-info-label"><?= $item[1] ?></div>
                                <div class="aa-info-value"><?= $item[2] ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
