<?php
declare(strict_types=1);
require_once 'includes/auth.php';
session_init();

$_usuario = usuario_logado();

/* ══════════════════════════════════════════════════════
   MODO PAINEL — usuário logado
   URL permanece localhost/AgroAmigo/
══════════════════════════════════════════════════════ */
if ($_usuario) {
    $pdo = db();

    $props = $pdo->prepare("
        SELECT p.*, COUNT(a.id) AS total_animais
        FROM propriedades p
        LEFT JOIN animais a ON a.propriedade_id = p.id AND a.status = 'ativo'
        WHERE p.usuario_id = :uid
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $props->execute(['uid' => $_usuario['id']]);
    $propriedades = $props->fetchAll();

    $totais = $pdo->prepare("
        SELECT
            COUNT(DISTINCT p.id)                                      AS total_props,
            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'ativo')   AS total_animais,
            COUNT(DISTINCT ps.id)                                     AS total_pesagens,
            COUNT(DISTINCT v.id)                                      AS total_vacinas
        FROM propriedades p
        LEFT JOIN animais a   ON a.propriedade_id = p.id
        LEFT JOIN pesagens ps ON ps.animal_id = a.id
        LEFT JOIN vacinacoes v ON v.animal_id  = a.id
        WHERE p.usuario_id = :uid
    ");
    $totais->execute(['uid' => $_usuario['id']]);
    $t = $totais->fetch();

    $ultimas = $pdo->prepare("
        SELECT ps.data_pesagem, ps.peso_kg, a.brinco, a.especie, a.raca
        FROM pesagens ps
        JOIN animais a      ON a.id  = ps.animal_id
        JOIN propriedades pr ON pr.id = a.propriedade_id
        WHERE pr.usuario_id = :uid
        ORDER BY ps.data_pesagem DESC, ps.created_at DESC
        LIMIT 5
    ");
    $ultimas->execute(['uid' => $_usuario['id']]);
    $pesagens_recentes = $ultimas->fetchAll();

    $pagina        = 'conta';
    $titulo_pagina = 'Minha Conta';
    require 'includes/header.php';
?>

<?php
$_dias   = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
$_meses  = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$_hoje   = $_dias[date('w')] . ', ' . date('d') . ' de ' . $_meses[(int)date('n')];
$_nome1  = ucfirst(mb_strtolower(explode(' ', $_usuario['nome'])[0]));
$_ini    = mb_strtoupper(mb_substr($_usuario['nome'], 0, 1));
$_hora   = (int)date('H');
$_saud   = $_hora < 12 ? 'Bom dia' : ($_hora < 18 ? 'Boa tarde' : 'Boa noite');
?>
<section style="background:linear-gradient(135deg,#166534 0%,#15803d 55%,#14532d 100%);padding:32px 0 28px;">
    <div class="container">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">

            <!-- Avatar com inicial -->
            <div style="width:60px;height:60px;border-radius:50%;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;letter-spacing:0">
                <?= h($_ini) ?>
            </div>

            <!-- Texto -->
            <div style="flex:1;min-width:0">
                <p style="color:rgba(255,255,255,.6);font-size:12px;font-weight:500;margin:0 0 3px;letter-spacing:.4px;text-transform:uppercase">
                    <?= h($_hoje) ?>
                </p>
                <h1 style="font-size:clamp(20px,4vw,28px);font-weight:800;color:#fff;margin:0 0 4px;line-height:1.2">
                    <?= h($_saud) ?>, <?= h($_nome1) ?>! 👋
                </h1>
                <p style="color:rgba(255,255,255,.65);font-size:13px;margin:0">
                    Seu painel zootécnico · AgroAmigo ATERPEC
                </p>
            </div>

            <!-- Botão rápido -->
            <a href="fichas.php" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:10px;padding:10px 18px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;transition:background .2s"
               onmouseover="this.style.background='rgba(255,255,255,.22)'"
               onmouseout="this.style.background='rgba(255,255,255,.15)'">
                <i class="bi bi-file-earmark-text"></i> Fichas
            </a>
        </div>
    </div>
</section>

<div class="container py-4">

    <?php $f = get_flash(); if ($f): ?>
    <div class="alert alert-<?= $f['tipo'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <?= h($f['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Totais -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['🏡', $t['total_props'],    'Propriedades'],
            ['🐄', $t['total_animais'],  'Animais ativos'],
            ['⚖️', $t['total_pesagens'], 'Pesagens'],
            ['💉', $t['total_vacinas'],  'Vacinações'],
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

        <!-- Coluna direita -->
        <div class="col-lg-5">

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

            <div class="aa-card">
                <div class="aa-card-head">
                    <div><h2 class="aa-card-title">⚖️ Pesagens Recentes</h2></div>
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

<?php
    require 'includes/footer.php';
    exit;
}
/* ══════════════════════════════════════════════════════
   MODO LANDING — visitante não logado
══════════════════════════════════════════════════════ */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroAmigo ATERPEC — Assistência Técnica Rural Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --g50:#f0fdf4;--g100:#dcfce7;--g200:#bbf7d0;--g600:#16a34a;
            --g700:#15803d;--g800:#166534;--g900:#14532d;
            --font:'Inter',system-ui,sans-serif;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{font-family:var(--font);color:#1f2937;-webkit-font-smoothing:antialiased}
        a{text-decoration:none;color:inherit}

        /* NAV */
        .lp-nav{
            position:fixed;top:0;left:0;right:0;z-index:100;
            background:var(--g800);
            padding:0 32px;height:64px;
            display:flex;align-items:center;justify-content:space-between;gap:16px;
        }
        .lp-logo{font-size:20px;font-weight:400;color:#fff;display:flex;align-items:center;gap:8px}
        .lp-logo strong{font-weight:800}
        .lp-nav-links{display:flex;align-items:center;gap:4px}
        .lp-nav-link{font-size:13px;font-weight:500;color:rgba(255,255,255,.78);padding:6px 12px;border-radius:8px;transition:all .2s}
        .lp-nav-link:hover{color:#fff;background:rgba(255,255,255,.12)}
        .lp-btn-login{
            background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);
            border-radius:8px;font-size:13px;font-weight:700;padding:7px 18px;
            transition:all .2s;white-space:nowrap;
        }
        .lp-btn-login:hover{background:rgba(255,255,255,.25);color:#fff}
        .lp-btn-cta{
            background:#fff;color:var(--g800);border:none;
            border-radius:8px;font-size:13px;font-weight:700;padding:8px 20px;
            transition:all .2s;white-space:nowrap;
        }
        .lp-btn-cta:hover{background:var(--g50);color:var(--g900)}

        /* HERO */
        .lp-hero{
            min-height:100vh;
            background-image:url('https://images.unsplash.com/photo-1605000797499-95a51c5269ae?w=1600&q=80&auto=format&fit=crop');
            background-size:cover;background-position:center;
            display:flex;align-items:center;
            padding:80px 0 60px;
            position:relative;overflow:hidden;
        }
        .lp-hero::before{
            content:'';position:absolute;inset:0;
            background:linear-gradient(135deg,rgba(14,52,27,.92) 0%,rgba(20,83,45,.85) 50%,rgba(14,52,27,.90) 100%);
        }
        .lp-hero::after{
            content:'';position:absolute;right:-200px;bottom:-200px;
            width:600px;height:600px;border-radius:50%;
            background:radial-gradient(circle,rgba(255,255,255,.04) 0%,transparent 70%);
        }
        .lp-badge{
            display:inline-flex;align-items:center;gap:6px;
            background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);
            border-radius:999px;padding:6px 14px;
            font-size:12px;font-weight:700;color:#fff;margin-bottom:20px;
        }
        .lp-hero-title{
            font-size:clamp(2.2rem,5vw,3.6rem);
            font-weight:900;line-height:1.1;
            color:#fff;letter-spacing:-1.5px;margin-bottom:20px;
        }
        .lp-hero-title span{color:var(--g200)}
        .lp-hero-desc{font-size:17px;color:rgba(255,255,255,.8);line-height:1.7;max-width:500px;margin-bottom:36px}
        .lp-hero-btns{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:40px}
        .lp-btn-hero-primary{
            background:#fff;color:var(--g800);border:none;border-radius:12px;
            font-size:16px;font-weight:700;padding:14px 32px;
            transition:all .2s;display:inline-flex;align-items:center;gap:8px;
        }
        .lp-btn-hero-primary:hover{background:var(--g50);color:var(--g900);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2)}
        .lp-btn-hero-secondary{
            background:rgba(255,255,255,.12);color:#fff;
            border:1.5px solid rgba(255,255,255,.35);border-radius:12px;
            font-size:16px;font-weight:700;padding:13px 32px;
            transition:all .2s;display:inline-flex;align-items:center;gap:8px;
        }
        .lp-btn-hero-secondary:hover{background:rgba(255,255,255,.22);color:#fff;transform:translateY(-2px)}
        .lp-hero-meta{display:flex;flex-wrap:wrap;gap:20px;align-items:center;font-size:13px;color:rgba(255,255,255,.65)}
        .lp-hero-meta-item{display:flex;align-items:center;gap:6px}
        .lp-hero-meta-item i{color:var(--g200);font-size:16px}

        /* Garante que o conteúdo fique acima do overlay ::before */
        .lp-hero > .container{position:relative;z-index:1}

        /* Hero visual card */
        .lp-hero-visual{
            background:#fff;border-radius:20px;padding:28px;
            box-shadow:0 24px 64px rgba(0,0,0,.3);
            position:relative;max-width:380px;margin:0 auto;
        }
        .lp-visual-header{display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f3f4f6}
        .lp-visual-logo{font-size:18px;font-weight:400;color:var(--g800)}
        .lp-visual-logo strong{font-weight:800}
        .lp-visual-badge{background:var(--g50);color:var(--g700);font-size:10px;font-weight:700;border-radius:999px;padding:3px 10px;letter-spacing:.5px;text-transform:uppercase}
        .lp-animal-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px}
        .lp-animal-item{background:var(--g50);border-radius:12px;padding:12px 8px;text-align:center;font-size:12px;font-weight:600;color:var(--g800);border:1px solid var(--g100)}
        .lp-animal-item .em{font-size:24px;display:block;margin-bottom:4px}
        .lp-stat-row{display:flex;gap:8px}
        .lp-stat-box{flex:1;background:var(--g50);border-radius:10px;padding:12px;text-align:center;border:1px solid var(--g100)}
        .lp-stat-box .val{font-size:20px;font-weight:800;color:var(--g800)}
        .lp-stat-box .lbl{font-size:10px;color:var(--g700);margin-top:2px;font-weight:600}

        /* Sections */
        .lp-section{padding:88px 0;background:#fff}
        .lp-section-alt{padding:88px 0;background:var(--g50)}
        .lp-section-badge{
            display:inline-block;background:var(--g100);border:1px solid var(--g200);
            border-radius:999px;padding:5px 14px;font-size:12px;font-weight:700;
            color:var(--g800);margin-bottom:14px;
        }
        .lp-section-title{font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;letter-spacing:-.5px;color:#111827;margin-bottom:10px}
        .lp-section-desc{font-size:15px;color:#6b7280;max-width:520px;line-height:1.7}
        .lp-feature-card{
            background:#fff;border-radius:16px;padding:28px;
            border:1.5px solid var(--g100);height:100%;
            transition:box-shadow .2s,transform .2s,border-color .2s;
        }
        .lp-feature-card:hover{box-shadow:0 8px 32px rgba(22,163,74,.12);transform:translateY(-3px);border-color:var(--g200)}
        .lp-feature-icon{
            width:48px;height:48px;border-radius:12px;
            background:linear-gradient(135deg,var(--g100),var(--g50));
            border:1px solid var(--g200);
            display:flex;align-items:center;justify-content:center;
            font-size:22px;margin-bottom:16px;
        }
        .lp-feature-title{font-size:16px;font-weight:700;color:var(--g900);margin-bottom:8px}
        .lp-feature-desc{font-size:14px;color:#6b7280;line-height:1.65}

        /* Sobre */
        .lp-about-box{
            background:linear-gradient(135deg,var(--g700) 0%,var(--g900) 100%);
            border-radius:24px;padding:56px;color:#fff;
            position:relative;overflow:hidden;
        }
        .lp-about-box::before{content:'🌱';font-size:200px;position:absolute;right:-20px;bottom:-40px;opacity:.07;line-height:1}
        .lp-about-label{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--g200);margin-bottom:10px}
        .lp-about-title{font-size:clamp(1.5rem,3vw,2rem);font-weight:800;margin-bottom:16px}
        .lp-about-desc{font-size:15px;color:rgba(255,255,255,.82);line-height:1.75;margin-bottom:24px}
        .lp-about-pills{display:flex;flex-wrap:wrap;gap:10px}
        .lp-pill{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:7px 16px;font-size:13px;font-weight:600;color:#fff}

        /* Equipe */
        .lp-team-card{background:#fff;border-radius:16px;padding:28px 24px;border:1.5px solid var(--g100);text-align:center;transition:box-shadow .2s,border-color .2s}
        .lp-team-card:hover{box-shadow:0 6px 24px rgba(22,163,74,.12);border-color:var(--g200)}
        .lp-team-avatar{
            width:72px;height:72px;border-radius:50%;
            background:linear-gradient(135deg,var(--g600),var(--g800));
            color:#fff;font-size:26px;font-weight:800;
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 14px;
            box-shadow:0 4px 16px rgba(22,163,74,.35);
        }
        .lp-team-nome{font-size:15px;font-weight:700;color:#111827;margin-bottom:4px}
        .lp-team-cargo{font-size:12px;color:#9ca3af;font-weight:500}
        .lp-team-cargo strong{color:var(--g700);font-weight:700}

        /* CTA final */
        .lp-cta{background:linear-gradient(150deg,var(--g700) 0%,var(--g800) 50%,var(--g900) 100%);padding:88px 0;text-align:center;color:#fff;position:relative;overflow:hidden}
        .lp-cta::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E")}
        .lp-cta-title{font-size:clamp(1.8rem,3.5vw,2.6rem);font-weight:800;margin-bottom:12px;position:relative}
        .lp-cta-desc{font-size:16px;color:rgba(255,255,255,.78);margin-bottom:36px;max-width:480px;margin-left:auto;margin-right:auto;position:relative}

        /* Footer */
        .lp-footer{background:var(--g900);padding:36px 0;text-align:center;border-top:1px solid rgba(255,255,255,.08)}
        .lp-footer-text{font-size:13px;color:rgba(255,255,255,.45)}
        .lp-footer-logo{font-size:18px;font-weight:400;color:#fff;margin-bottom:8px}
        .lp-footer-logo strong{font-weight:800}

        /* Hamburger */
        .lp-ham-btn{display:none;background:none;border:none;color:#fff;font-size:26px;line-height:1;cursor:pointer;padding:4px;align-items:center;flex-shrink:0}
        /* Mobile menu */
        .lp-mob-menu{display:none;flex-direction:column;position:absolute;top:64px;left:0;right:0;background:var(--g900);border-top:1px solid rgba(255,255,255,.1);padding:10px 16px 18px;z-index:102}
        .lp-mob-menu.open{display:flex}
        .lp-mob-link{font-size:15px;font-weight:500;color:rgba(255,255,255,.85);padding:11px 4px;border-bottom:1px solid rgba(255,255,255,.08);transition:color .15s}
        .lp-mob-link:last-of-type{border-bottom:none}
        .lp-mob-link:hover{color:#fff}
        .lp-mob-sep{height:1px;background:rgba(255,255,255,.12);margin:8px 0}
        .lp-mob-btn-outline{display:block;text-align:center;padding:11px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.25);border-radius:8px;color:#fff;font-size:14px;font-weight:700;margin-top:8px;transition:background .2s}
        .lp-mob-btn-solid{display:block;text-align:center;padding:11px;background:#fff;border-radius:8px;color:var(--g800);font-size:14px;font-weight:700;margin-top:8px;transition:background .2s}
        .lp-mob-btn-outline:hover{background:rgba(255,255,255,.2);color:#fff}
        .lp-mob-btn-solid:hover{background:var(--g50);color:var(--g900)}

        @media(max-width:768px){
            .lp-nav{padding:0 16px;position:relative}
            .lp-nav-links,.lp-btn-login,.lp-btn-cta{display:none}
            .lp-ham-btn{display:flex}
            .lp-hero{padding:80px 0 40px}
            .lp-about-box{padding:32px 24px}
            .lp-section,.lp-section-alt{padding:60px 0}
        }
        @media(max-width:480px){
            .lp-hero-title{font-size:2rem;letter-spacing:-1px}
            .lp-hero-desc{font-size:15px}
            .lp-btn-hero-primary,.lp-btn-hero-secondary{font-size:14px;padding:11px 22px}
            .lp-about-box{padding:24px 18px}
            .lp-section,.lp-section-alt{padding:48px 0}
            .lp-feature-card{padding:20px}
            .lp-team-card{padding:20px 16px}
            .lp-cta{padding:60px 0}
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="lp-nav" style="position:relative">
    <div class="lp-logo">🌱 Agro<strong>Amigo</strong></div>
    <div class="lp-nav-links">
        <a href="#sobre"    class="lp-nav-link">Sobre</a>
        <a href="#funciona" class="lp-nav-link">Como funciona</a>
        <a href="#equipe"   class="lp-nav-link">Equipe</a>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="login.php"    class="lp-btn-login">Entrar</a>
        <a href="cadastro.php" class="lp-btn-cta">Criar conta grátis</a>
        <button class="lp-ham-btn" id="lp-ham-btn" aria-label="Abrir menu" aria-expanded="false">
            <i class="bi bi-list" id="lp-ham-ico"></i>
        </button>
    </div>

    <!-- Menu mobile -->
    <div class="lp-mob-menu" id="lp-mob-menu" aria-hidden="true">
        <a href="#sobre"    class="lp-mob-link">Sobre</a>
        <a href="#funciona" class="lp-mob-link">Como funciona</a>
        <a href="#equipe"   class="lp-mob-link">Equipe</a>
        <div class="lp-mob-sep"></div>
        <a href="login.php"    class="lp-mob-btn-outline">Entrar</a>
        <a href="cadastro.php" class="lp-mob-btn-solid">Criar conta grátis</a>
    </div>
</nav>

<!-- HERO -->
<section class="lp-hero">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6">
                <div class="lp-badge">
                    <i class="bi bi-geo-alt-fill"></i> Projeto ATERPEC · Verde Conecta · UEMA
                </div>
                <h1 class="lp-hero-title">
                    Assistência técnica<br>
                    <span>na palma da mão</span>
                </h1>
                <p class="lp-hero-desc">
                    Orientações práticas de criação animal para pequenos produtores rurais
                    do Maranhão. Registre, controle e acompanhe seu rebanho — direto do celular,
                    totalmente gratuito.
                </p>
                <div class="lp-hero-btns">
                    <a href="cadastro.php" class="lp-btn-hero-primary">
                        <i class="bi bi-person-plus-fill"></i> Criar conta gratuita
                    </a>
                    <a href="login.php" class="lp-btn-hero-secondary">
                        <i class="bi bi-box-arrow-in-right"></i> Já tenho conta
                    </a>
                </div>
                <div class="lp-hero-meta">
                    <div class="lp-hero-meta-item"><i class="bi bi-check-circle-fill"></i> 100% gratuito</div>
                    <div class="lp-hero-meta-item"><i class="bi bi-shield-fill-check"></i> Dados seguros</div>
                    <div class="lp-hero-meta-item"><i class="bi bi-phone-fill"></i> Funciona no celular</div>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="lp-hero-visual">
                    <div class="lp-visual-header">
                        <div class="lp-visual-logo">🌱 Agro<strong>Amigo</strong></div>
                        <span class="lp-visual-badge">Painel</span>
                    </div>
                    <div class="lp-animal-grid">
                        <div class="lp-animal-item"><span class="em">🐄</span>Bovinos</div>
                        <div class="lp-animal-item"><span class="em">🐐</span>Caprinos</div>
                        <div class="lp-animal-item"><span class="em">🐑</span>Ovinos</div>
                        <div class="lp-animal-item"><span class="em">🐔</span>Aves</div>
                        <div class="lp-animal-item"><span class="em">🐷</span>Suínos</div>
                        <div class="lp-animal-item"><span class="em">🐟</span>Peixes</div>
                    </div>
                    <div class="lp-stat-row">
                        <div class="lp-stat-box"><div class="val">📋</div><div class="lbl">Fichas PDF</div></div>
                        <div class="lp-stat-box"><div class="val">⚖️</div><div class="lbl">Pesagens</div></div>
                        <div class="lp-stat-box"><div class="val">💉</div><div class="lbl">Vacinações</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FUNCIONA -->
<section class="lp-section" id="funciona">
    <div class="container">
        <div class="text-center mb-5">
            <span class="lp-section-badge">Como funciona</span>
            <h2 class="lp-section-title">Tudo que você precisa para<br>gerenciar seu rebanho</h2>
            <p class="lp-section-desc mx-auto mt-2">
                Ferramentas simples e práticas desenvolvidas especialmente para a realidade do produtor rural do Maranhão.
            </p>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['📋', 'Fichas Zootécnicas',     'Registre cada animal com identificação, peso ao nascer, raça, genealogia e histórico completo de tratamentos.'],
                ['⚖️', 'Controle de Pesagens',   'Acompanhe o ganho de peso mês a mês e veja automaticamente o ganho médio diário de cada animal.'],
                ['💉', 'Calendário Vacinal',      'Controle as vacinações obrigatórias e opcionais, com datas de reforço e registro de lote e fabricante.'],
                ['🩺', 'Ocorrências Sanitárias',  'Registre diagnósticos, medicamentos e tratamentos. Mantenha o histórico de saúde de cada animal.'],
                ['📊', 'Relatórios de Mortalidade','Documente perdas com causa e medidas corretivas para identificar padrões e reduzir riscos.'],
                ['📄', 'Download em PDF',          'Baixe qualquer ficha em PDF para imprimir ou guardar no celular, sem precisar de internet depois.'],
            ];
            foreach ($features as $f):
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="lp-feature-card">
                    <div class="lp-feature-icon"><?= $f[0] ?></div>
                    <div class="lp-feature-title"><?= $f[1] ?></div>
                    <p class="lp-feature-desc"><?= $f[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SOBRE -->
<section class="lp-section-alt" id="sobre">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <span class="lp-section-badge">Sobre o projeto</span>
                <h2 class="lp-section-title">Por que o<br>AgroAmigo existe?</h2>
                <p class="lp-section-desc mt-3">
                    Apenas <strong>3% das propriedades rurais</strong> do Maranhão recebem algum tipo de
                    Assistência Técnica e Extensão Rural (ATER). A falta de técnicos e infraestrutura
                    limita o alcance da informação e compromete a produtividade do pequeno produtor.
                </p>
                <p class="lp-section-desc mt-3">
                    O projeto <strong>ATERPEC</strong>, desenvolvido pela equipe <strong>Verde Conecta</strong>
                    da UEMA, usa tecnologia digital para levar orientações práticas de ambiência, vacinação,
                    nutrição, manejo e biosseguridade diretamente ao produtor — pelo celular.
                </p>
            </div>
            <div class="col-lg-7">
                <div class="lp-about-box">
                    <div class="lp-about-label">Missão do projeto</div>
                    <div class="lp-about-title">Democratizar o acesso à informação técnica rural no Maranhão</div>
                    <p class="lp-about-desc">
                        Combinando um chatbot no WhatsApp com este painel web, o AgroAmigo leva assistência
                        técnica de qualidade para onde os técnicos não conseguem chegar presencialmente.
                    </p>
                    <div class="lp-about-pills">
                        <span class="lp-pill">🤖 Chatbot WhatsApp</span>
                        <span class="lp-pill">📱 Painel web</span>
                        <span class="lp-pill">🐄 6 espécies</span>
                        <span class="lp-pill">📋 Fichas PDF</span>
                        <span class="lp-pill">🆓 100% gratuito</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- EQUIPE -->
<section class="lp-section" id="equipe">
    <div class="container">
        <div class="text-center mb-5">
            <span class="lp-section-badge">Equipe</span>
            <h2 class="lp-section-title">Quem faz o<br>AgroAmigo acontecer</h2>
            <p class="lp-section-desc mx-auto mt-2">
                Projeto desenvolvido pela equipe Verde Conecta da Universidade Estadual do Maranhão (UEMA),
                no âmbito do Projeto ATERPEC e do SAF Maranhão.
            </p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $equipe = [
                ['MA', 'Milena Andréa',   'Extensão Rural',    'Equipe Verde Conecta · UEMA'],
                ['LJ', 'Laurine de Jesus','Extensão Rural',    'Equipe Verde Conecta · UEMA'],
                ['JR', 'Jéssica Rios',    'Extensão Rural',    'Equipe Verde Conecta · UEMA'],
                ['TR', 'Tiago Rocha',     'Extensão Rural',    'Equipe Verde Conecta · UEMA'],
                ['HM', 'Heloisa Simas',   'Extensão Rural',    'Equipe Verde Conecta · UEMA'],
                ['JK', 'José Kauã',       'Desenvolvedor Web', 'Equipe Verde Conecta · UEMA'],
            ];
            foreach ($equipe as $m):
            ?>
            <div class="col-sm-6 col-lg-4">
                <div class="lp-team-card">
                    <div class="lp-team-avatar"><?= $m[0] ?></div>
                    <div class="lp-team-nome"><?= $m[1] ?></div>
                    <div class="lp-team-cargo"><?= $m[2] ?><br><strong><?= $m[3] ?></strong></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5 pt-3" style="border-top:1px solid #e5e7eb">
            <p style="font-size:13px;color:#9ca3af;margin-bottom:14px">Projeto realizado em parceria com</p>
            <div style="display:flex;flex-wrap:wrap;gap:16px;justify-content:center;align-items:center">
                <?php foreach (['🎓 UEMA', '🌿 SAF Maranhão', '🤝 Verde Conecta', '🔬 ATERPEC'] as $i): ?>
                <span style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:8px 18px;font-size:13px;font-weight:700;color:#374151"><?= $i ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="lp-cta">
    <div class="container">
        <h2 class="lp-cta-title">Comece agora, é gratuito</h2>
        <p class="lp-cta-desc">
            Crie sua conta em menos de 1 minuto e comece a registrar
            seus animais e fichas de controle ainda hoje.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center">
            <a href="cadastro.php" class="lp-btn-hero-primary">
                <i class="bi bi-person-plus-fill"></i> Criar conta gratuita
            </a>
            <a href="login.php" class="lp-btn-hero-secondary">
                <i class="bi bi-box-arrow-in-right"></i> Já tenho conta
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="lp-footer">
    <div class="container">
        <div class="lp-footer-logo">🌱 Agro<strong>Amigo</strong></div>
        <p class="lp-footer-text">
            AgroAmigo ATERPEC · Verde Conecta · UEMA · SAF Maranhão · <?= date('Y') ?><br>
            Desenvolvido para pequenos produtores rurais do Maranhão.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    var btn  = document.getElementById('lp-ham-btn');
    var menu = document.getElementById('lp-mob-menu');
    var ico  = document.getElementById('lp-ham-ico');
    if (!btn) return;
    function toggle() {
        var open = menu.classList.toggle('open');
        ico.className = open ? 'bi bi-x' : 'bi bi-list';
        btn.setAttribute('aria-expanded', open);
        menu.setAttribute('aria-hidden', !open);
    }
    btn.addEventListener('click', toggle);
    menu.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', function() {
            menu.classList.remove('open');
            ico.className = 'bi bi-list';
            btn.setAttribute('aria-expanded', 'false');
            menu.setAttribute('aria-hidden', 'true');
        });
    });
})();
</script>
</body>
</html>
