<?php
declare(strict_types=1);
require_once '../includes/auth.php';
session_init();

$_fc_user = usuario_logado();
if (!$_fc_user) {
    header('Location: ../index.php');
    exit;
}

$_fc_dados = null;
$_fc_salvo = null;
try {
    $pdo  = db();
    $stmt = $pdo->prepare(
        "SELECT dados, salvo_em FROM fichas_salvas WHERE usuario_id = :uid AND tipo = 'mortalidade'"
    );
    $stmt->execute(['uid' => $_fc_user['id']]);
    $rec = $stmt->fetch();
    if ($rec) {
        $_fc_dados = json_decode($rec['dados'], true);
        $_fc_salvo = date('d/m/Y H:i', strtotime($rec['salvo_em']));
    }
} catch (PDOException) { /* tabela pendente de migration */ }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Mortalidade — AgroAmigo ATERPEC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/fichas.css">
    <script src="../js/fichas.js"></script>
</head>
<body>

<div class="fc-topbar">
    <div class="fc-topbar-logo"><span>🌱</span> Agro<strong>Amigo</strong></div>
    <div class="fc-topbar-actions">
        <a href="../fichas.php" class="fc-btn-back"><i class="bi bi-arrow-left"></i> Voltar</a>
        <span class="fc-saved-at" id="fcSavedAt"><?= $_fc_salvo ? 'Salvo: ' . $_fc_salvo : '' ?></span>
        <button class="fc-btn-save" id="fcBtnSave" onclick="salvarFicha()">
            <i class="bi bi-floppy"></i> Salvar
        </button>
        <button class="fc-btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
    </div>
</div>

<div class="fc-page">

    <div class="fc-header">
        <div>
            <div class="fc-header-logo">🌱 Agro<strong>Amigo</strong> · ATERPEC</div>
            <div class="fc-header-title">Ficha de Mortalidade</div>
            <div class="fc-header-subtitle">Registro de óbitos no rebanho — Projeto Verde Conecta / UEMA</div>
        </div>
        <div class="fc-header-badge">
            <div class="fc-header-badge-label">Produtor</div>
            <div class="fc-header-badge-value" style="font-size:12px">
                <?= h(explode(' ', $_fc_user['nome'])[0]) ?>
            </div>
        </div>
    </div>

    <div class="fc-body">

        <!-- 1. Dados da Propriedade -->
        <div class="fc-section">
            <div class="fc-section-title">🏡 Dados da Propriedade</div>
            <div class="fc-row cols-3">
                <div class="fc-field">
                    <label>Nome da Propriedade</label>
                    <input type="text" class="fc-input" data-fk="prop_nome">
                </div>
                <div class="fc-field">
                    <label>Município / UF</label>
                    <input type="text" class="fc-input" data-fk="prop_municipio" placeholder="Ex: Bacabal / MA">
                </div>
                <div class="fc-field">
                    <label>Período de Referência</label>
                    <input type="text" class="fc-input" data-fk="prop_periodo" placeholder="Ex: Jan 2025">
                </div>
            </div>
            <div class="fc-row cols-3">
                <div class="fc-field">
                    <label>Responsável</label>
                    <input type="text" class="fc-input" data-fk="prop_responsavel">
                </div>
                <div class="fc-field">
                    <label>Espécie</label>
                    <input type="text" class="fc-input" data-fk="prop_especie" placeholder="Ex: Bovinos, Caprinos...">
                </div>
                <div class="fc-field">
                    <label>Total do rebanho no início do período</label>
                    <input type="text" class="fc-input" data-fk="prop_total_inicio" placeholder="Nº de animais">
                </div>
            </div>
        </div>

        <!-- 2. Registro de Óbitos -->
        <div class="fc-section">
            <div class="fc-section-title">📊 Registro de Óbitos</div>
            <p class="fc-hint">Registre cada morte assim que ocorrer. Identificar padrões ajuda a prevenir novas perdas.</p>
            <div class="fc-table-wrap">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th style="width:10%">Data</th>
                            <th style="width:12%">ID / Brinco</th>
                            <th style="width:13%">Categoria</th>
                            <th style="width:10%">Faixa Etária</th>
                            <th style="width:22%">Causa Provável / Diagnóstico</th>
                            <th style="width:20%">Providências Tomadas</th>
                            <th>Responsável</th>
                        </tr>
                    </thead>
                    <tbody data-table="obitos">
                        <?php for ($i = 0; $i < 12; $i++): ?>
                        <tr>
                            <td><input type="text" class="fc-td-input" data-cell="data"></td>
                            <td><input type="text" class="fc-td-input" data-cell="brinco"></td>
                            <td><input type="text" class="fc-td-input" data-cell="categoria" placeholder="Bezerro, adulto..."></td>
                            <td><input type="text" class="fc-td-input" data-cell="faixa_etaria" placeholder="dias/meses"></td>
                            <td><input type="text" class="fc-td-input" data-cell="causa"></td>
                            <td><input type="text" class="fc-td-input" data-cell="providencias"></td>
                            <td><input type="text" class="fc-td-input" data-cell="responsavel"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Resumo do Período -->
        <div class="fc-section">
            <div class="fc-section-title">📈 Resumo do Período</div>
            <div class="fc-row cols-4">
                <div class="fc-field">
                    <label>Total de Óbitos</label>
                    <input type="text" class="fc-input" data-fk="resumo_total">
                </div>
                <div class="fc-field">
                    <label>Taxa de Mortalidade (%)</label>
                    <input type="text" class="fc-input" data-fk="resumo_taxa" placeholder="óbitos ÷ rebanho × 100">
                </div>
                <div class="fc-field">
                    <label>Categoria Mais Afetada</label>
                    <input type="text" class="fc-input" data-fk="resumo_categoria">
                </div>
                <div class="fc-field">
                    <label>Causa Mais Frequente</label>
                    <input type="text" class="fc-input" data-fk="resumo_causa">
                </div>
            </div>
            <div class="fc-table-wrap" style="margin-top: 12px;">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th style="width:20%">Qtd. Início</th>
                            <th style="width:20%">Qtd. Óbitos</th>
                            <th style="width:20%">% Mortalidade</th>
                        </tr>
                    </thead>
                    <tbody data-table="resumo_categorias">
                        <?php
                        $categorias = ['Crias / Filhotes', 'Jovens', 'Adultos', 'Fêmeas adultas', 'Machos adultos'];
                        foreach ($categorias as $cat):
                        ?>
                        <tr>
                            <td><input type="text" class="fc-td-input" value="<?= $cat ?>" readonly></td>
                            <td><input type="text" class="fc-td-input" data-cell="qty_inicio"></td>
                            <td><input type="text" class="fc-td-input" data-cell="qty_obitos"></td>
                            <td><input type="text" class="fc-td-input" data-cell="pct_mortalidade"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Análise e Medidas Corretivas -->
        <div class="fc-section">
            <div class="fc-section-title">🔍 Análise e Medidas Corretivas</div>
            <div class="fc-row cols-1">
                <div class="fc-field">
                    <label>Hipótese para as Mortes / Diagnóstico geral</label>
                    <textarea class="fc-textarea" rows="2" data-fk="analise_hipotese" placeholder="Descreva a causa provável, se identificada..."></textarea>
                </div>
            </div>
            <div class="fc-row cols-1">
                <div class="fc-field">
                    <label>Providências Adotadas / Plano de Ação</label>
                    <textarea class="fc-textarea" rows="2" data-fk="analise_providencias" placeholder="O que foi feito para evitar novas mortes? Vacinação, vermifugação, mudança de manejo..."></textarea>
                </div>
            </div>
        </div>

        <!-- 5. Assinatura -->
        <div class="fc-section">
            <div class="fc-row cols-2">
                <div class="fc-field">
                    <label>Assinatura do Produtor</label>
                    <input type="text" class="fc-input" data-fk="ass_produtor" style="margin-top: 20px;">
                </div>
                <div class="fc-field">
                    <label>Assinatura do Técnico Responsável</label>
                    <input type="text" class="fc-input" data-fk="ass_tecnico" style="margin-top: 20px;">
                </div>
            </div>
        </div>

    </div>

    <div class="fc-footer">
        <div class="fc-footer-text">AgroAmigo ATERPEC · Verde Conecta / UEMA · SAF Maranhão · <?= date('Y') ?></div>
        <div class="fc-footer-label">Ficha de Mortalidade</div>
    </div>

</div>

<script>
window.FICHA_TIPO  = 'mortalidade';
window.FICHA_CSRF  = '<?= csrf_token() ?>';
window.FICHA_DADOS = <?= json_encode($_fc_dados ?? (object)[], JSON_UNESCAPED_UNICODE) ?>;
</script>
</body>
</html>
