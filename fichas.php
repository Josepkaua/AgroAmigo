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
                'url'   => 'fichas/zootecnica.php',
            ],
            [
                'emoji' => '💉',
                'titulo'=> 'Ficha de Vacinação',
                'desc'  => 'Controle completo do calendário vacinal do rebanho. Anote o nome da vacina, data de aplicação, número do lote, fabricante e data de revacinação. Exigida em fiscalizações sanitárias.',
                'campos'=> ['Identificação do animal', 'Nome da vacina', 'Data de aplicação', 'Dose e via de aplicação', 'Lote e validade da vacina', 'Data do próximo reforço'],
                'url'   => 'fichas/vacinacao.php',
            ],
            [
                'emoji' => '📊',
                'titulo'=> 'Ficha de Mortalidade',
                'desc'  => 'Registre cada óbito no rebanho com data, causa provável ou diagnóstico, faixa etária e categoria animal. Permite identificar padrões e prevenir novas perdas.',
                'campos'=> ['Data do óbito', 'Identificação do animal', 'Categoria (bezerro, adulto, etc.)', 'Causa provável / diagnóstico', 'Providências tomadas', 'Responsável pelo registro'],
                'url'   => 'fichas/mortalidade.php',
            ],
            [
                'emoji' => '📝',
                'titulo'=> 'Ficha Única (Controle Integrado)',
                'desc'  => 'Reúne as informações mais importantes em uma única folha: identificação, vacinações, pesagens e ocorrências sanitárias. Ideal para pequenos produtores com rebanhos de até 30 animais.',
                'campos'=> ['Dados de identificação do animal', 'Histórico de pesagens mensais', 'Registro de vacinações', 'Ocorrências sanitárias', 'Tratamentos realizados', 'Destino final (venda/abate/morte)'],
                'url'   => 'fichas/controle.php',
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
                        <button class="btn aa-btn-primary flex-fill"
                                onclick="abrirFicha('<?= $f['url'] ?>', '<?= addslashes($f['titulo']) ?>')">
                            <i class="bi bi-file-earmark-text me-2"></i> Abrir Ficha
                        </button>
                        <button class="btn aa-btn-outline flex-fill"
                                onclick="abrirFichaImprimir('<?= $f['url'] ?>', '<?= addslashes($f['titulo']) ?>')">
                            <i class="bi bi-file-earmark-pdf me-2"></i> Baixar PDF
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

<!-- =========================================================
     MODAL DE FICHAS
========================================================= -->
<div class="modal fade" id="fichaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content aa-modal-ficha">
            <div class="modal-header">
                <h6 class="modal-title" id="fichaModalLabel"></h6>
                <div class="ms-auto me-3 d-flex gap-2">
                    <button class="btn aa-btn-modal-print" onclick="printFicha()">
                        <i class="bi bi-printer me-1"></i> Imprimir
                    </button>
                    <button class="btn aa-btn-modal-pdf" onclick="printFichaComoPDF()">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Baixar PDF
                    </button>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="fichaFrame" src="about:blank" style="width:100%;height:82vh;border:none;display:block;background:#f3f4f6;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function abrirFicha(url, titulo) {
    document.getElementById('fichaFrame').src = url;
    document.getElementById('fichaModalLabel').textContent = titulo;
    new bootstrap.Modal(document.getElementById('fichaModal')).show();
}

function abrirFichaImprimir(url, titulo) {
    var frame = document.getElementById('fichaFrame');
    document.getElementById('fichaModalLabel').textContent = titulo;
    new bootstrap.Modal(document.getElementById('fichaModal')).show();
    var nome = titulo.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-');
    frame.onload = function () {
        setTimeout(function () { frame.contentWindow.baixarPDF(nome); }, 600);
        frame.onload = null;
    };
    frame.src = url;
}

function printFicha() {
    document.getElementById('fichaFrame').contentWindow.print();
}

function printFichaComoPDF() {
    var frame = document.getElementById('fichaFrame');
    var titulo = document.getElementById('fichaModalLabel').textContent;
    var nome = titulo.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-');
    frame.contentWindow.baixarPDF(nome);
}

document.getElementById('fichaModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('fichaFrame').src = 'about:blank';
});
</script>

<?php require 'includes/footer.php'; ?>
