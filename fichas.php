<?php
declare(strict_types=1);
require_once 'includes/auth.php';
$usuario = require_login('login.php');
$pdo = db();

$form_erro    = '';
$auto_open_id = $_GET['animal'] ?? '';

// ── POST: criar novo animal ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_acao'] ?? '') === 'novo_animal') {
    csrf_verify();
    $prop_id   = $_POST['propriedade_id'] ?? '';
    $brinco    = trim($_POST['brinco']    ?? '');
    $especie   = strtolower(trim($_POST['especie'] ?? ''));
    $raca      = trim($_POST['raca']      ?? '');
    $data_nasc = ($_POST['data_nascimento'] ?? '') !== '' ? $_POST['data_nascimento'] : null;
    $peso_nasc = ($_POST['peso_nascimento'] ?? '') !== '' ? (float)$_POST['peso_nascimento'] : null;

    $chk = $pdo->prepare("SELECT id FROM propriedades WHERE id = :id AND usuario_id = :uid");
    $chk->execute(['id' => $prop_id, 'uid' => $usuario['id']]);

    if (!$brinco || !$especie) {
        $form_erro = 'Brinco/ID e espécie são obrigatórios.';
    } elseif (!$chk->fetch()) {
        $form_erro = 'Propriedade inválida.';
    } else {
        $stmt_ins = $pdo->prepare("
            INSERT INTO animais (propriedade_id, brinco, especie, raca, data_nascimento, peso_nascimento_kg, status)
            VALUES (:p, :b, :e, :r, :dn, :pn, 'ativo')
            RETURNING id
        ");
        $stmt_ins->execute([
            'p' => $prop_id, 'b' => $brinco, 'e' => $especie,
            'r' => $raca ?: null, 'dn' => $data_nasc, 'pn' => $peso_nasc,
        ]);
        $novo_id = $stmt_ins->fetchColumn();
        flash('success', "Animal \"{$brinco}\" cadastrado com sucesso!");
        header("Location: fichas.php?animal={$novo_id}");
        exit;
    }
}

// ── Animais do usuário ──────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT a.*, p.nome AS prop_nome
    FROM animais a
    JOIN propriedades p ON p.id = a.propriedade_id
    WHERE p.usuario_id = :uid AND a.status = 'ativo'
    ORDER BY a.especie, a.brinco
");
$stmt->execute(['uid' => $usuario['id']]);
$animais = $stmt->fetchAll();

// ── Propriedades para o form ────────────────────────────────
$stmt2 = $pdo->prepare("SELECT id, nome FROM propriedades WHERE usuario_id = :uid ORDER BY nome");
$stmt2->execute(['uid' => $usuario['id']]);
$propriedades = $stmt2->fetchAll();

$pagina        = 'fichas';
$titulo_pagina = 'Meus Animais';
require 'includes/header.php';

function espEmoji(string $esp): string {
    $m = [
        'bovino'  => '🐄','bovinos'  => '🐄',
        'ave'     => '🐔','aves'     => '🐔',
        'suino'   => '🐷','suinos'   => '🐷',
        'caprino' => '🐐','caprinos' => '🐐',
        'ovino'   => '🐑','ovinos'   => '🐑',
        'peixe'   => '🐟','peixes'   => '🐟',
    ];
    return $m[mb_strtolower(trim($esp))] ?? '🐾';
}
?>

<style>
/* ── Screens ── */
.fa-screen        { display:none }
.fa-screen.fa-on  { display:block }

/* ── Toolbar ── */
.fa-toolbar { display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:20px }
.fa-search  {
    flex:1;min-width:150px;border:1.5px solid #e5e7eb;border-radius:10px;
    padding:9px 14px;font-size:14px;font-family:inherit;outline:none;
    transition:border-color .2s;
}
.fa-search:focus { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.1) }
.fa-filter {
    border:1.5px solid #e5e7eb;border-radius:10px;padding:9px 12px;
    font-size:14px;font-family:inherit;background:#fff;outline:none;cursor:pointer;
}
.fa-btn-new {
    display:inline-flex;align-items:center;gap:7px;background:#166534;color:#fff;
    border:none;border-radius:10px;padding:9px 18px;font-size:14px;font-weight:700;
    font-family:inherit;cursor:pointer;transition:background .2s;white-space:nowrap;
}
.fa-btn-new:hover { background:#14532d }

/* ── Painel novo animal ── */
.fa-form-panel {
    background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:14px;
    padding:22px 24px;margin-bottom:20px;
}
.fa-form-title { font-size:15px;font-weight:800;color:#166534;margin-bottom:16px }
.fa-form-grid  { display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px }
.fa-form-group label {
    display:block;font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.5px;color:#374151;margin-bottom:5px;
}
.fa-form-input {
    display:block;width:100%;border:1.5px solid #d1fae5;border-radius:8px;
    padding:9px 12px;font-size:14px;font-family:inherit;background:#fff;outline:none;
    transition:border-color .2s;
}
.fa-form-input:focus { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.1) }
.req::after { content:' *';color:#dc2626 }
.fa-form-actions { display:flex;gap:10px;margin-top:16px;align-items:center }
.fa-btn-save {
    background:#166534;color:#fff;border:none;border-radius:8px;padding:9px 20px;
    font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .2s;
}
.fa-btn-save:hover { background:#14532d }
.fa-btn-cancel-form {
    background:none;color:#6b7280;border:none;font-size:13px;font-weight:600;
    cursor:pointer;padding:9px 14px;font-family:inherit;
}
.fa-btn-cancel-form:hover { color:#374151 }

/* ── Lista ── */
.fa-row {
    display:flex;align-items:center;gap:14px;padding:14px 16px;
    background:#fff;border:1px solid #f3f4f6;border-radius:12px;margin-bottom:8px;
    cursor:pointer;transition:border-color .2s,box-shadow .2s;
}
.fa-row:hover { border-color:#bbf7d0;box-shadow:0 4px 16px rgba(22,163,74,.1) }
.fa-row:focus { outline:2px solid #16a34a;outline-offset:2px }
.fa-row-emoji { font-size:28px;flex-shrink:0;width:40px;text-align:center }
.fa-row-info  { flex:1;min-width:0 }
.fa-row-nome  { font-size:15px;font-weight:700;color:#111827 }
.fa-row-meta  { font-size:12px;color:#9ca3af;margin-top:2px }
.fa-row-date  { font-size:12px;color:#6b7280;flex-shrink:0 }
.fa-row-arrow { color:#d1d5db;flex-shrink:0 }

/* ── Empty ── */
.fa-empty {
    text-align:center;padding:48px 24px;background:#fff;
    border-radius:14px;border:1.5px dashed #e5e7eb;color:#9ca3af;
}
.fa-empty-ico   { font-size:48px;margin-bottom:12px }
.fa-empty-title { font-size:16px;font-weight:700;color:#374151;margin-bottom:6px }

/* ── Screen 2: Detalhe ── */
.fa-back-bar { padding:12px 0 }
.fa-btn-back {
    display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:600;
    color:#6b7280;background:none;border:none;cursor:pointer;padding:6px 0;
    font-family:inherit;transition:color .2s;
}
.fa-btn-back:hover { color:#166534 }

/* Hero do animal */
.fa-hero-animal {
    background:linear-gradient(135deg,#166534 0%,#15803d 60%,#14532d 100%);
    padding:24px 0 22px;
}
.fa-hero-inner  { display:flex;align-items:center;gap:18px;flex-wrap:wrap }
.fa-hero-avatar {
    width:64px;height:64px;border-radius:50%;
    background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.25);
    display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;
}
.fa-hero-nome { font-size:22px;font-weight:800;color:#fff;margin:0 0 3px }
.fa-hero-meta { font-size:13px;color:rgba(255,255,255,.7);margin:0 }

/* Seções */
.fa-sections  { padding:22px 0 56px }
.fa-section   {
    background:#fff;border:1px solid #e5e7eb;border-radius:14px;
    margin-bottom:14px;overflow:hidden;
}
.fa-sec-head  {
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    padding:15px 20px;border-bottom:1px solid #f3f4f6;
}
.fa-sec-title { font-size:14px;font-weight:800;color:#111827;margin:0 }
.fa-btn-add   {
    display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#166534;
    border:1.5px solid #bbf7d0;border-radius:8px;padding:5px 12px;font-size:12px;
    font-weight:700;cursor:pointer;transition:all .2s;white-space:nowrap;font-family:inherit;
}
.fa-btn-add:hover { background:#dcfce7;border-color:#86efac }

/* Info grid */
.fa-info-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr)) }
.fa-info-item { padding:12px 20px;border-bottom:1px solid #f9fafb }
.fa-info-lbl  { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:3px }
.fa-info-val  { font-size:14px;font-weight:600;color:#111827 }

/* Records */
.fa-rec-list  { display:flex;flex-direction:column }
.fa-rec-row   {
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    padding:10px 20px;border-bottom:1px solid #f9fafb;font-size:13px;
}
.fa-rec-row:last-child { border-bottom:none }
.fa-rec-main  { font-weight:600;color:#111827 }
.fa-rec-sub   { font-size:11px;color:#9ca3af;margin-top:2px }
.fa-rec-val   { font-size:15px;font-weight:800;color:#166534;flex-shrink:0 }
.fa-rec-empty { padding:20px;text-align:center;font-size:13px;color:#9ca3af }

/* Formulários inline */
.fa-inline    { background:#f8fafc;border-top:1px solid #f1f5f9;padding:16px 20px }
.fa-i-grid    { display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px }
.fa-i-lbl     { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#374151;margin-bottom:5px;display:block }
.fa-i-inp     {
    display:block;width:100%;border:1.5px solid #e2e8f0;border-radius:8px;
    padding:8px 10px;font-size:13px;font-family:inherit;background:#fff;outline:none;
    transition:border-color .2s;
}
.fa-i-inp:focus { border-color:#16a34a }
.fa-i-actions { display:flex;gap:8px;margin-top:12px;align-items:center }
.fa-i-submit  {
    background:#166534;color:#fff;border:none;border-radius:7px;padding:7px 16px;
    font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .2s;
}
.fa-i-submit:hover { background:#14532d }
.fa-i-cancel  { background:none;color:#9ca3af;border:none;font-size:12px;cursor:pointer;padding:7px 8px;font-family:inherit }
.fa-i-cancel:hover { color:#374151 }

/* Loading */
.fa-loading   { text-align:center;padding:60px 20px;color:#9ca3af }
.fa-spinner   {
    width:36px;height:36px;border:3px solid #e5e7eb;border-top-color:#16a34a;
    border-radius:50%;animation:fa-spin .8s linear infinite;margin:0 auto 14px;
}
@keyframes fa-spin { to { transform:rotate(360deg) } }

/* Alert */
.fa-err {
    background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;
    padding:12px 16px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;
}

@media(max-width:576px){
    .fa-row { padding:12px;gap:10px }
    .fa-row-emoji { font-size:22px;width:32px }
    .fa-hero-avatar { width:52px;height:52px;font-size:22px }
    .fa-hero-nome { font-size:18px }
    .fa-form-grid { grid-template-columns:1fr 1fr }
    .fa-i-grid { grid-template-columns:1fr 1fr }
    .fa-rec-row { padding:9px 14px }
    .fa-info-item { padding:10px 14px }
    .fa-sec-head { padding:12px 14px }
}
</style>

<!-- HERO -->
<section class="aa-page-hero">
    <div class="container position-relative">
        <nav class="aa-breadcrumb mb-3">
            <a href="index.php">Início</a><span>/</span>
            <span class="text-white">Fichas</span>
        </nav>
        <span class="aa-page-emoji">🐾</span>
        <h1 class="aa-page-title">Meus Animais</h1>
        <p class="aa-page-desc mt-2">Cadastre seus animais e registre pesagens, vacinações e ocorrências.</p>
    </div>
</section>

<!-- ══════════════ SCREEN 1: LISTA ══════════════ -->
<div id="s-list" class="fa-screen fa-on">
<div class="container py-4">

    <?php $f = get_flash(); if ($f): ?>
    <div class="alert alert-<?= $f['tipo'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <?= h($f['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="fa-toolbar">
        <input type="search" id="fa-search" class="fa-search" placeholder="Buscar por brinco ou raça…" aria-label="Buscar">
        <select id="fa-filter" class="fa-filter">
            <option value="">Todas espécies</option>
            <option value="bovino">🐄 Bovinos</option>
            <option value="ave">🐔 Aves</option>
            <option value="suino">🐷 Suínos</option>
            <option value="caprino">🐐 Caprinos</option>
            <option value="ovino">🐑 Ovinos</option>
            <option value="peixe">🐟 Peixes</option>
        </select>
        <button id="btn-novo" class="fa-btn-new" type="button">
            <i class="bi bi-plus-circle"></i> Novo Animal
        </button>
    </div>

    <!-- Form novo animal -->
    <div id="fa-form-novo" style="display:none">
        <div class="fa-form-panel">
            <div class="fa-form-title">🐾 Cadastrar novo animal</div>

            <?php if ($form_erro): ?>
            <div class="fa-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= h($form_erro) ?></div>
            <?php endif; ?>

            <?php if (!$propriedades): ?>
            <div class="fa-err">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Você precisa <a href="propriedade-nova.php" style="color:#991b1b;font-weight:700">cadastrar uma propriedade</a> antes de adicionar animais.</span>
            </div>
            <?php else: ?>
            <form method="POST" action="fichas.php" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="_acao" value="novo_animal">
                <div class="fa-form-grid">
                    <div class="fa-form-group">
                        <label class="req">Propriedade</label>
                        <select name="propriedade_id" class="fa-form-input" required>
                            <option value="">— Selecione —</option>
                            <?php foreach ($propriedades as $p): ?>
                            <option value="<?= h($p['id']) ?>"><?= h($p['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fa-form-group">
                        <label class="req">Brinco / ID</label>
                        <input type="text" name="brinco" class="fa-form-input" placeholder="Ex: #001 ou Mimosa" required>
                    </div>
                    <div class="fa-form-group">
                        <label class="req">Espécie</label>
                        <select name="especie" class="fa-form-input" required>
                            <option value="">— Selecione —</option>
                            <option value="bovino">🐄 Bovino</option>
                            <option value="ave">🐔 Ave</option>
                            <option value="suino">🐷 Suíno</option>
                            <option value="caprino">🐐 Caprino</option>
                            <option value="ovino">🐑 Ovino</option>
                            <option value="peixe">🐟 Peixe</option>
                        </select>
                    </div>
                    <div class="fa-form-group">
                        <label>Raça <span style="color:#9ca3af;font-weight:400;text-transform:none">(opcional)</span></label>
                        <input type="text" name="raca" class="fa-form-input" placeholder="Ex: Nelore">
                    </div>
                    <div class="fa-form-group">
                        <label>Data de nascimento</label>
                        <input type="date" name="data_nascimento" class="fa-form-input">
                    </div>
                    <div class="fa-form-group">
                        <label>Peso ao nascer (kg)</label>
                        <input type="number" name="peso_nascimento" class="fa-form-input" step="0.1" min="0" placeholder="Ex: 32">
                    </div>
                </div>
                <div class="fa-form-actions">
                    <button type="submit" class="fa-btn-save"><i class="bi bi-check-circle me-1"></i> Cadastrar</button>
                    <button type="button" class="fa-btn-cancel-form" id="btn-cancel-novo">Cancelar</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lista -->
    <div id="fa-items">
        <?php if (!$animais): ?>
        <div class="fa-empty">
            <div class="fa-empty-ico">🐾</div>
            <div class="fa-empty-title">Nenhum animal cadastrado ainda</div>
            <p style="font-size:13px">Clique em <strong>Novo Animal</strong> para começar.</p>
        </div>
        <?php else: ?>
        <?php foreach ($animais as $a): ?>
        <div class="fa-row" tabindex="0" role="button"
             data-id="<?= h($a['id']) ?>"
             data-esp="<?= h(strtolower($a['especie'])) ?>"
             data-q="<?= h(strtolower($a['brinco'] . ' ' . ($a['raca'] ?? ''))) ?>"
             aria-label="Ver ficha de <?= h($a['brinco']) ?>">
            <div class="fa-row-emoji"><?= espEmoji($a['especie']) ?></div>
            <div class="fa-row-info">
                <div class="fa-row-nome"><?= h($a['brinco']) ?><?= $a['raca'] ? ' · ' . h($a['raca']) : '' ?></div>
                <div class="fa-row-meta"><?= h(ucfirst($a['especie'])) ?> · <?= h($a['prop_nome']) ?></div>
            </div>
            <?php if ($a['data_nascimento']): ?>
            <div class="fa-row-date d-none d-sm-block">Nasc. <?= date('d/m/Y', strtotime($a['data_nascimento'])) ?></div>
            <?php endif; ?>
            <div class="fa-row-arrow"><i class="bi bi-chevron-right"></i></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="fa-no-results" class="fa-empty" style="display:none">
        <div class="fa-empty-ico">🔍</div>
        <div class="fa-empty-title">Nenhum resultado encontrado</div>
        <p style="font-size:13px">Tente outro brinco, raça ou espécie.</p>
    </div>

</div>
</div>

<!-- ══════════════ SCREEN 2: DETALHE ══════════════ -->
<div id="s-detail" class="fa-screen">

    <div class="container fa-back-bar">
        <button id="btn-voltar" class="fa-btn-back" type="button">
            <i class="bi bi-arrow-left"></i> Voltar aos animais
        </button>
    </div>

    <!-- Loading -->
    <div id="fa-loading">
        <div class="fa-loading"><div class="fa-spinner"></div><p>Carregando…</p></div>
    </div>

    <!-- Conteúdo -->
    <div id="fa-content" style="display:none">

        <div class="fa-hero-animal">
            <div class="container">
                <div class="fa-hero-inner">
                    <div class="fa-hero-avatar" id="det-emoji">🐄</div>
                    <div>
                        <h2 class="fa-hero-nome" id="det-nome">—</h2>
                        <p class="fa-hero-meta" id="det-meta">—</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="fa-sections">
            <div class="container">

                <!-- Informações básicas -->
                <div class="fa-section">
                    <div class="fa-sec-head">
                        <h3 class="fa-sec-title">📋 Informações Básicas</h3>
                    </div>
                    <div id="det-info" class="fa-info-grid"></div>
                </div>

                <!-- Pesagens -->
                <div class="fa-section">
                    <div class="fa-sec-head">
                        <h3 class="fa-sec-title">⚖️ Pesagens</h3>
                        <button class="fa-btn-add" id="btn-add-pes" type="button"><i class="bi bi-plus-sm"></i> Adicionar</button>
                    </div>
                    <div class="fa-inline" id="form-pes" style="display:none">
                        <div class="fa-i-grid">
                            <div>
                                <label class="fa-i-lbl">Data <span style="color:#dc2626">*</span></label>
                                <input type="date" id="pes-data" class="fa-i-inp">
                            </div>
                            <div>
                                <label class="fa-i-lbl">Peso (kg) <span style="color:#dc2626">*</span></label>
                                <input type="number" id="pes-peso" class="fa-i-inp" step="0.1" min="0" placeholder="280">
                            </div>
                            <div style="grid-column:1/-1">
                                <label class="fa-i-lbl">Observações</label>
                                <input type="text" id="pes-obs" class="fa-i-inp" placeholder="Opcional">
                            </div>
                        </div>
                        <div class="fa-i-actions">
                            <button class="fa-i-submit" id="btn-ok-pes" type="button">Salvar</button>
                            <button class="fa-i-cancel" id="btn-cx-pes" type="button">Cancelar</button>
                        </div>
                    </div>
                    <div id="det-pes" class="fa-rec-list"></div>
                </div>

                <!-- Vacinações -->
                <div class="fa-section">
                    <div class="fa-sec-head">
                        <h3 class="fa-sec-title">💉 Vacinações</h3>
                        <button class="fa-btn-add" id="btn-add-vac" type="button"><i class="bi bi-plus-sm"></i> Adicionar</button>
                    </div>
                    <div class="fa-inline" id="form-vac" style="display:none">
                        <div class="fa-i-grid">
                            <div>
                                <label class="fa-i-lbl">Vacina <span style="color:#dc2626">*</span></label>
                                <input type="text" id="vac-nome" class="fa-i-inp" placeholder="Ex: Febre Aftosa">
                            </div>
                            <div>
                                <label class="fa-i-lbl">Data aplicação <span style="color:#dc2626">*</span></label>
                                <input type="date" id="vac-data" class="fa-i-inp">
                            </div>
                            <div>
                                <label class="fa-i-lbl">Lote</label>
                                <input type="text" id="vac-lote" class="fa-i-inp" placeholder="Opcional">
                            </div>
                            <div>
                                <label class="fa-i-lbl">Próximo reforço</label>
                                <input type="date" id="vac-reforco" class="fa-i-inp">
                            </div>
                        </div>
                        <div class="fa-i-actions">
                            <button class="fa-i-submit" id="btn-ok-vac" type="button">Salvar</button>
                            <button class="fa-i-cancel" id="btn-cx-vac" type="button">Cancelar</button>
                        </div>
                    </div>
                    <div id="det-vac" class="fa-rec-list"></div>
                </div>

            </div>
        </div>
    </div><!-- /fa-content -->
</div><!-- /s-detail -->

<?php require 'includes/footer.php'; ?>

<script>
const CSRF       = <?= json_encode(csrf_token()) ?>;
const AUTO_OPEN  = <?= json_encode($auto_open_id) ?>;
let   CUR_ANIMAL = null;

/* ── Utils ─────────────────────────────────────────────── */
const $  = id => document.getElementById(id);
const sh = el => { el.style.display = '' };
const hd = el => { el.style.display = 'none' };
function esc(s) {
    return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtDate(d) {
    if (!d) return '—';
    const p = d.split('-');
    return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : d;
}
function capitalize(s) { return s ? s.charAt(0).toUpperCase()+s.slice(1) : s; }
function espEmoji(e) {
    const m={bovino:'🐄',bovinos:'🐄',ave:'🐔',aves:'🐔',suino:'🐷',suinos:'🐷',
             caprino:'🐐',caprinos:'🐐',ovino:'🐑',ovinos:'🐑',peixe:'🐟',peixes:'🐟'};
    return m[(e||'').toLowerCase().trim()]||'🐾';
}

/* ── Navegação ──────────────────────────────────────────── */
function goList() {
    $('s-list').classList.add('fa-on');
    $('s-detail').classList.remove('fa-on');
    window.scrollTo({top:0, behavior:'smooth'});
    history.replaceState(null,'','fichas.php');
}
function goDetail(id) {
    CUR_ANIMAL = id;
    $('s-list').classList.remove('fa-on');
    $('s-detail').classList.add('fa-on');
    hd($('fa-content'));
    sh($('fa-loading'));
    hd($('form-pes'));
    hd($('form-vac'));
    window.scrollTo({top:0, behavior:'smooth'});
    history.replaceState(null,'',`fichas.php?animal=${id}`);

    fetch(`fichas/animal-json.php?id=${id}`)
        .then(r => { if(!r.ok) throw new Error(r.status); return r.json(); })
        .then(renderDetail)
        .catch(() => alert('Não foi possível carregar. Tente novamente.'));
}

/* ── Render detalhe ─────────────────────────────────────── */
function renderDetail(d) {
    const a = d.animal;
    hd($('fa-loading'));

    $('det-emoji').textContent = espEmoji(a.especie);
    $('det-nome').textContent  = a.brinco + (a.raca ? ' · '+a.raca : '');
    $('det-meta').textContent  = capitalize(a.especie) + ' · ' + a.prop_nome;

    $('det-info').innerHTML = [
        ['Espécie',    capitalize(a.especie)],
        ['Raça',       a.raca||'—'],
        ['Propriedade',a.prop_nome],
        ['Nascimento', fmtDate(a.data_nascimento)],
        ['Peso nasc.', a.peso_nascimento_kg ? a.peso_nascimento_kg+' kg' : '—'],
    ].map(([l,v]) =>
        `<div class="fa-info-item"><div class="fa-info-lbl">${l}</div><div class="fa-info-val">${esc(v)}</div></div>`
    ).join('');

    renderPes(d.pesagens);
    renderVac(d.vacinacoes);
    sh($('fa-content'));
}

function renderPes(rows) {
    $('det-pes').innerHTML = rows.length
        ? rows.map(r =>
            `<div class="fa-rec-row">
                <div>
                    <div class="fa-rec-main">${fmtDate(r.data_pesagem)}</div>
                    ${r.observacao?`<div class="fa-rec-sub">${esc(r.observacao)}</div>`:''}
                </div>
                <div class="fa-rec-val">${parseFloat(r.peso_kg).toFixed(1).replace('.',',')} kg</div>
            </div>`).join('')
        : '<div class="fa-rec-empty">Nenhuma pesagem registrada ainda.</div>';
}

function renderVac(rows) {
    $('det-vac').innerHTML = rows.length
        ? rows.map(r =>
            `<div class="fa-rec-row">
                <div>
                    <div class="fa-rec-main">${esc(r.nome_vacina)}</div>
                    <div class="fa-rec-sub">
                        ${fmtDate(r.data_aplicacao)}
                        ${r.proximo_reforco?' · Reforço: '+fmtDate(r.proximo_reforco):''}
                        ${r.lote?' · Lote: '+esc(r.lote):''}
                    </div>
                </div>
            </div>`).join('')
        : '<div class="fa-rec-empty">Nenhuma vacinação registrada ainda.</div>';
}

/* ── Pesagem: salvar ────────────────────────────────────── */
$('btn-add-pes').onclick = () => { $('form-pes').style.display = $('form-pes').style.display?'':'none'; hd($('form-vac')); };
$('btn-cx-pes').onclick  = () => hd($('form-pes'));
$('btn-ok-pes').onclick  = async function() {
    const data = $('pes-data').value, peso = $('pes-peso').value.trim();
    if (!data || !peso) { alert('Data e peso são obrigatórios.'); return; }
    this.disabled = true; this.textContent = 'Salvando…';
    try {
        const r = await fetch('fichas/pesagem-nova.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({_csrf:CSRF, animal_id:CUR_ANIMAL, data_pesagem:data, peso_kg:peso, observacoes:$('pes-obs').value}),
        });
        const j = await r.json();
        if (j.ok) {
            [$('pes-data'),$('pes-peso'),$('pes-obs')].forEach(el => el.value='');
            hd($('form-pes'));
            fetch(`fichas/animal-json.php?id=${CUR_ANIMAL}`).then(r=>r.json()).then(d=>renderPes(d.pesagens));
        } else alert(j.erro||'Erro ao salvar.');
    } finally { this.disabled=false; this.textContent='Salvar'; }
};

/* ── Vacinação: salvar ──────────────────────────────────── */
$('btn-add-vac').onclick = () => { $('form-vac').style.display = $('form-vac').style.display?'':'none'; hd($('form-pes')); };
$('btn-cx-vac').onclick  = () => hd($('form-vac'));
$('btn-ok-vac').onclick  = async function() {
    const nome = $('vac-nome').value.trim(), data = $('vac-data').value;
    if (!nome || !data) { alert('Vacina e data são obrigatórios.'); return; }
    this.disabled = true; this.textContent = 'Salvando…';
    try {
        const r = await fetch('fichas/vacinacao-nova.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({_csrf:CSRF, animal_id:CUR_ANIMAL, vacina:nome, data_aplicacao:data, lote:$('vac-lote').value, data_reforco:$('vac-reforco').value}),
        });
        const j = await r.json();
        if (j.ok) {
            [$('vac-nome'),$('vac-data'),$('vac-lote'),$('vac-reforco')].forEach(el=>el.value='');
            hd($('form-vac'));
            fetch(`fichas/animal-json.php?id=${CUR_ANIMAL}`).then(r=>r.json()).then(d=>renderVac(d.vacinacoes));
        } else alert(j.erro||'Erro ao salvar.');
    } finally { this.disabled=false; this.textContent='Salvar'; }
};

/* ── Botões nav ─────────────────────────────────────────── */
$('btn-voltar').onclick = goList;
$('btn-novo').onclick   = () => {
    const f = $('fa-form-novo');
    f.style.display = f.style.display ? '' : 'none';
    if (!f.style.display) f.scrollIntoView({behavior:'smooth',block:'nearest'});
};
$('btn-cancel-novo')?.addEventListener('click', () => hd($('fa-form-novo')));

/* ── Filtro / busca ─────────────────────────────────────── */
function filtrar() {
    const q   = $('fa-search').value.toLowerCase();
    const esp = $('fa-filter').value.toLowerCase().replace(/s$/, '');
    const rows = document.querySelectorAll('#fa-items .fa-row');
    let vis = 0;
    rows.forEach(r => {
        const ok = (!q || r.dataset.q.includes(q)) && (!esp || r.dataset.esp.startsWith(esp));
        r.style.display = ok ? '' : 'none';
        if (ok) vis++;
    });
    $('fa-no-results').style.display = (vis === 0 && rows.length) ? '' : 'none';
}
$('fa-search').addEventListener('input', filtrar);
$('fa-filter').addEventListener('change', filtrar);

/* ── Click nas rows ─────────────────────────────────────── */
document.querySelectorAll('#fa-items .fa-row').forEach(r => {
    r.addEventListener('click',   () => goDetail(r.dataset.id));
    r.addEventListener('keydown', e => { if (e.key==='Enter'||e.key===' ') goDetail(r.dataset.id); });
});

/* ── Auto-abrir via URL ─────────────────────────────────── */
if (AUTO_OPEN) goDetail(AUTO_OPEN);
</script>
