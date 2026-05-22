<?php
$pagina        = 'fichas';
$titulo_pagina = 'Fichas de Controle';
require 'includes/header.php';
?>

<!-- HERO DA PÁGINA -->
<section class="aa-page-hero">
    <div class="container position-relative">
        <nav class="aa-breadcrumb mb-3">
            <a href="index.php">Início</a>
            <span>/</span>
            <span class="text-white">Fichas</span>
        </nav>
        <span class="aa-page-emoji">📋</span>
        <h1 class="aa-page-title">Fichas de Controle</h1>
        <p class="aa-page-desc mt-3">
            Ferramentas de registro para acompanhar sua produção com organização.
            Imprima, preencha e mantenha o controle do seu rebanho.
        </p>
    </div>
</section>

<!-- FICHAS DISPONÍVEIS -->
<section class="aa-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="aa-section-badge">Controle da Produção</span>
            <h2 class="aa-section-title mt-2">
                Escolha a <span class="aa-highlight-section">Ficha</span>
            </h2>
            <p class="aa-section-desc">Abra no navegador e imprima ou preencha pelo celular</p>
        </div>

        <?php
        $fichas = [
            [
                'emoji' => '🐄',
                'titulo'=> 'Ficha Zootécnica Individual',
                'desc'  => 'Registre os dados de cada animal: identificação, raça, data de nascimento, peso ao nascer, peso ao desmame e histórico de tratamentos. Fundamental para acompanhar o desenvolvimento do rebanho.',
                'campos'=> ['Identificação (brinco/tatuagem)', 'Espécie e raça', 'Data de nascimento', 'Peso ao nascer / desmame / abate', 'Nome da mãe e do pai', 'Observações gerais'],
                'cor'   => 'green',
            ],
            [
                'emoji' => '💉',
                'titulo'=> 'Ficha de Vacinação',
                'desc'  => 'Controle completo do calendário vacinal do rebanho. Anote o nome da vacina, data de aplicação, número do lote, fabricante e data de revacinação. Exigida em fiscalizações sanitárias.',
                'campos'=> ['Identificação do animal', 'Nome da vacina', 'Data de aplicação', 'Dose e via de aplicação', 'Lote e validade da vacina', 'Data do próximo reforço'],
                'cor'   => 'blue',
            ],
            [
                'emoji' => '📊',
                'titulo'=> 'Ficha de Mortalidade',
                'desc'  => 'Registre cada óbito no rebanho com data, causa provável ou diagnóstico, faixa etária e categoria animal. Permite identificar padrões e prevenir novas perdas.',
                'campos'=> ['Data do óbito', 'Identificação do animal', 'Categoria (bezerro, adulto, etc.)', 'Causa provável / diagnóstico', 'Providências tomadas', 'Responsável pelo registro'],
                'cor'   => 'red',
            ],
            [
                'emoji' => '📝',
                'titulo'=> 'Ficha Única (Controle Integrado)',
                'desc'  => 'Reúne as informações mais importantes em uma única folha: identificação, vacinações, pesagens e ocorrências sanitárias. Ideal para pequenos produtores com rebanhos de até 30 animais.',
                'campos'=> ['Dados de identificação do animal', 'Histórico de pesagens mensais', 'Registro de vacinações', 'Ocorrências sanitárias', 'Tratamentos realizados', 'Destino final (venda/abate/morte)'],
                'cor'   => 'purple',
            ],
        ];
        ?>

        <div class="row g-4">
            <?php foreach ($fichas as $f): ?>
            <div class="col-md-6">
                <div class="aa-ficha-card h-100">
                    <div class="aa-ficha-emoji"><?= $f['emoji'] ?></div>
                    <h4 class="aa-ficha-titulo"><?= $f['titulo'] ?></h4>
                    <p class="aa-ficha-desc"><?= $f['desc'] ?></p>

                    <div class="mb-4">
                        <div class="aa-ficha-campos-titulo">Campos incluídos:</div>
                        <ul class="aa-ficha-campos">
                            <?php foreach ($f['campos'] as $campo): ?>
                                <li><?= $campo ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <button class="btn aa-btn-primary flex-fill" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i> Imprimir
                        </button>
                        <button class="btn aa-btn-outline flex-fill" onclick="alert('Em breve disponível para download em PDF!')">
                            <i class="bi bi-download me-2"></i> Baixar PDF
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ORIENTAÇÕES DE USO -->
<section class="aa-section-alt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="aa-section-badge">Como usar</span>
            <h2 class="aa-section-title mt-2">
                Por que <span class="aa-highlight-section">Registrar</span>?
            </h2>
        </div>
        <div class="row g-4">
            <?php
            $motivos = [
                ['📈','Acompanhe o crescimento','Pesagens mensais revelam se o animal está crescendo bem e se a nutrição está adequada.'],
                ['🏥','Prevenção de doenças','O registro de vacinações evita que animais fiquem descobertos e reduz perdas por doenças preveníveis.'],
                ['💰','Controle financeiro','Saber o histórico de gastos por animal permite calcular o custo real de produção e o lucro.'],
                ['📋','Exigência legal','Algumas vacinações são obrigatórias por lei. Ter o registro protege o produtor em fiscalizações.'],
            ];
            foreach ($motivos as $m): ?>
            <div class="col-md-6 col-lg-3">
                <div class="aa-pilar-card text-center">
                    <div class="aa-pilar-icon"><?= $m[0] ?></div>
                    <div class="aa-pilar-nome"><?= $m[1] ?></div>
                    <div class="aa-pilar-desc"><?= $m[2] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="contato.php" class="btn aa-btn-section-primary aa-btn-lg">
                <i class="bi bi-whatsapp me-2"></i> Precisa de ajuda? Fale com o Técnico
            </a>
        </div>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
