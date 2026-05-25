<?php
declare(strict_types=1);
require_once 'includes/auth.php';

$usuario = require_login('login.php');

$pdo = db();

// Propriedades do usuário
$props = $pdo->prepare("
    SELECT p.*, COUNT(a.id) AS total_animais
    FROM propriedades p
    LEFT JOIN animais a ON a.propriedade_id = p.id AND a.status = 'ativo'
    WHERE p.usuario_id = :uid
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$props->execute(['uid' => $usuario['id']]);
$propriedades = $props->fetchAll();

// Totais rápidos
$totais = $pdo->prepare("
    SELECT
        COUNT(DISTINCT p.id)                              AS total_props,
        COUNT(DISTINCT a.id)   FILTER (WHERE a.status='ativo')   AS total_animais,
        COUNT(DISTINCT ps.id)                             AS total_pesagens,
        COUNT(DISTINCT v.id)                              AS total_vacinas
    FROM propriedades p
    LEFT JOIN animais a  ON a.propriedade_id = p.id
    LEFT JOIN pesagens ps ON ps.animal_id = a.id
    LEFT JOIN vacinacoes v ON v.animal_id = a.id
    WHERE p.usuario_id = :uid
");
$totais->execute(['uid' => $usuario['id']]);
$t = $totais->fetch();

// Últimas 5 pesagens
$ultimas_pesagens = $pdo->prepare("
    SELECT ps.data_pesagem, ps.peso_kg, a.brinco, a.especie, a.raca
    FROM pesagens ps
    JOIN animais a ON a.id = ps.animal_id
    JOIN propriedades pr ON pr.id = a.propriedade_id
    WHERE pr.usuario_id = :uid
    ORDER BY ps.data_pesagem DESC, ps.created_at DESC
    LIMIT 5
");
$ultimas_pesagens->execute(['uid' => $usuario['id']]);
$pesagens_recentes = $ultimas_pesagens->fetchAll();

$pagina        = 'conta';
$titulo_pagina = 'Minha Conta';
require 'includes/header.php';
?>

<section class="aa-page-hero" style="padding: 56px 0 28px;">
    <div class="container">
        <p class="aa-page-emoji" style="font-size:32px;">👋</p>
        <h1 class="aa-page-title">Olá, <?= h(explode(' ', $usuario['nome'])[0]) ?>!</h1>
        <p class="aa-page-sub">Seu painel zootécnico · AgroAmigo ATERPEC</p>
    </div>
</section>

<div class="container py-4">

    <?php $f = get_flash(); if ($f): ?>
    <div class="alert alert-<?= $f['tipo'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <?= h($f['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Cards de totais -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['🏡', $t['total_props'],   'Propriedades'],
            ['🐄', $t['total_animais'], 'Animais ativos'],
            ['⚖️', $t['total_pesagens'],'Pesagens'],
            ['💉', $t['total_vacinas'], 'Vacinações'],
        ];
        foreach ($cards as [$ico, $val, $label]):
        ?>
        <div class="col-6 col-md-3">
            <div class="aa-stat-card">
                <div class="aa-stat-icon"><?= $ico ?></div>
                <div class="aa-stat-val"><?= (int)$val ?></div>
                <div class="aa-stat-label"><?= $label ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">

        <!-- Propriedades -->
        <div class="col-lg-7">
            <div class="aa-card">
                <div class="aa-card-head">
                    <div>
                        <h2 class="aa-card-title">🏡 Minhas Propriedades</h2>
                        <p class="aa-card-sub">Gerencie suas propriedades e rebanhos</p>
                    </div>
                    <a href="propriedade-nova.php" class="aa-btn-sm">
                        <i class="bi bi-plus-lg"></i> Nova
                    </a>
                </div>

                <?php if (!$propriedades): ?>
                <div class="aa-empty">
                    <div style="font-size:40px;margin-bottom:12px;">🏡</div>
                    <p>Você ainda não cadastrou nenhuma propriedade.</p>
                    <a href="propriedade-nova.php" class="aa-btn-sm mt-2">
                        <i class="bi bi-plus-lg"></i> Cadastrar propriedade
                    </a>
                </div>
                <?php else: ?>
                <div class="aa-prop-list">
                    <?php foreach ($propriedades as $p): ?>
                    <div class="aa-prop-item">
                        <div class="aa-prop-info">
                            <div class="aa-prop-nome"><?= h($p['nome']) ?></div>
                            <div class="aa-prop-meta">
                                <?= h($p['municipio'] ?? '—') ?><?= $p['uf'] ? ' / ' . h($p['uf']) : '' ?>
                                <?php if ($p['area_ha']): ?> · <?= number_format((float)$p['area_ha'], 1, ',', '.') ?> ha<?php endif; ?>
                            </div>
                        </div>
                        <div class="aa-prop-badges">
                            <span class="aa-badge-animal"><?= (int)$p['total_animais'] ?> animal(is)</span>
                            <a href="propriedade.php?id=<?= h($p['id']) ?>" class="aa-link-btn">
                                Ver <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Painel direito -->
        <div class="col-lg-5">

            <!-- Fichas -->
            <div class="aa-card mb-4">
                <div class="aa-card-head">
                    <div>
                        <h2 class="aa-card-title">📋 Fichas de Controle</h2>
                        <p class="aa-card-sub">Imprimir ou baixar fichas em PDF</p>
                    </div>
                    <a href="fichas.php" class="aa-btn-sm">Acessar</a>
                </div>
                <p style="font-size:13px;color:#6b7280;padding:0 0 4px;">
                    Ficha Zootécnica, Vacinação, Mortalidade e Ficha Única disponíveis para impressão ou download em PDF.
                </p>
            </div>

            <!-- Pesagens recentes -->
            <div class="aa-card">
                <div class="aa-card-head">
                    <div>
                        <h2 class="aa-card-title">⚖️ Pesagens Recentes</h2>
                    </div>
                </div>
                <?php if (!$pesagens_recentes): ?>
                <p style="font-size:13px;color:#9ca3af;text-align:center;padding:16px 0;">
                    Nenhuma pesagem registrada ainda.
                </p>
                <?php else: ?>
                <div class="aa-mini-table">
                    <?php foreach ($pesagens_recentes as $ps): ?>
                    <div class="aa-mini-row">
                        <div>
                            <div class="aa-mini-main"><?= h($ps['brinco'] ?: 'Sem ID') ?> — <?= h($ps['especie'] ?: '?') ?></div>
                            <div class="aa-mini-sub"><?= date('d/m/Y', strtotime($ps['data_pesagem'])) ?></div>
                        </div>
                        <div class="aa-mini-val"><?= number_format((float)$ps['peso_kg'], 1, ',', '.') ?> kg</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
