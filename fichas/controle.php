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
        "SELECT dados, salvo_em FROM fichas_salvas WHERE usuario_id = :uid AND tipo = 'controle'"
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
    <title>Ficha Única — AgroAmigo ATERPEC</title>
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
            <div class="fc-header-title">Ficha Única — Controle Integrado</div>
            <div class="fc-header-subtitle">Resumo completo do animal · Ideal para rebanhos até 30 animais · Projeto Verde Conecta / UEMA</div>
        </div>
        <div class="fc-header-badge">
            <div class="fc-header-badge-label">Produtor</div>
            <div class="fc-header-badge-value" style="font-size:12px">
                <?= h(explode(' ', $_fc_user['nome'])[0]) ?>
            </div>
        </div>
    </div>

    <div class="fc-body">

        <!-- 1. Identificação -->
        <div class="fc-section">
            <div class="fc-section-title">🐄 Identificação do Animal e Propriedade</div>
            <div class="fc-row cols-4">
                <div class="fc-field">
                    <label>Brinco / Tatuagem</label>
                    <input type="text" class="fc-input" data-fk="animal_brinco">
                </div>
                <div class="fc-field">
                    <label>Espécie / Raça</label>
                    <input type="text" class="fc-input" data-fk="animal_especie_raca" placeholder="Ex: Caprino / Anglo">
                </div>
                <div class="fc-field">
                    <label>Sexo</label>
                    <div class="fc-check-group" style="padding-top:6px;">
                        <label class="fc-check-item"><input type="checkbox" data-fk-bool="sexo_macho"> Macho</label>
                        <label class="fc-check-item"><input type="checkbox" data-fk-bool="sexo_femea"> Fêmea</label>
                    </div>
                </div>
                <div class="fc-field">
                    <label>Data de Nasc.</label>
                    <input type="date" class="fc-input" data-fk="animal_nascimento">
                </div>
            </div>
            <div class="fc-row cols-3">
                <div class="fc-field">
                    <label>Propriedade</label>
                    <input type="text" class="fc-input" data-fk="prop_nome">
                </div>
                <div class="fc-field">
                    <label>Município / UF</label>
                    <input type="text" class="fc-input" data-fk="prop_municipio">
                </div>
                <div class="fc-field">
                    <label>Produtor / Responsável</label>
                    <input type="text" class="fc-input" data-fk="prop_responsavel">
                </div>
            </div>
        </div>

        <!-- 2. Pesagens mensais -->
        <div class="fc-section">
            <div class="fc-section-title">⚖️ Histórico de Pesagens Mensais</div>
            <div class="fc-table-wrap">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th style="width:8%">Mês</th>
                            <th style="width:14%">Data</th>
                            <th style="width:14%">Peso (kg)</th>
                            <th style="width:18%">Ganho médio (g/dia)</th>
                            <th style="width:14%">Escore (1–5)</th>
                            <th>Obs.</th>
                        </tr>
                    </thead>
                    <tbody data-table="pesagens_mensais">
                        <?php
                        $meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
                        foreach ($meses as $mes):
                        ?>
                        <tr class="pesagem-row">
                            <td><input type="text" class="fc-td-input" value="<?= $mes ?>" readonly></td>
                            <td><input type="date" class="fc-td-input data-pesagem" data-cell="data"></td>
                            <td><input type="number" step="0.1" min="0" class="fc-td-input peso-pesagem" data-cell="peso"></td>
                            <td><input type="text" class="fc-td-input fc-calc ganho-pesagem" data-cell="ganho" readonly placeholder="auto"></td>
                            <td><input type="number" step="0.5" min="1" max="5" class="fc-td-input" data-cell="escore" placeholder="1–5"></td>
                            <td><input type="text" class="fc-td-input" data-cell="obs"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Registro de Vacinações -->
        <div class="fc-section">
            <div class="fc-section-title">💉 Registro de Vacinações</div>
            <div class="fc-table-wrap">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th style="width:22%">Nome da Vacina</th>
                            <th style="width:14%">Data Aplic.</th>
                            <th style="width:10%">Dose (mL)</th>
                            <th style="width:10%">Via</th>
                            <th style="width:14%">Lote / Validade</th>
                            <th style="width:14%">Próx. Reforço</th>
                            <th>Aplicador</th>
                        </tr>
                    </thead>
                    <tbody data-table="vacinacoes">
                        <?php for ($i = 0; $i < 8; $i++): ?>
                        <tr>
                            <td><input type="text" class="fc-td-input" data-cell="nome"></td>
                            <td><input type="text" class="fc-td-input" data-cell="data"></td>
                            <td><input type="text" class="fc-td-input" data-cell="dose"></td>
                            <td><input type="text" class="fc-td-input" data-cell="via"></td>
                            <td><input type="text" class="fc-td-input" data-cell="lote"></td>
                            <td><input type="text" class="fc-td-input" data-cell="reforca"></td>
                            <td><input type="text" class="fc-td-input" data-cell="aplicador"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Ocorrências Sanitárias -->
        <div class="fc-section">
            <div class="fc-section-title">🩺 Ocorrências Sanitárias e Tratamentos</div>
            <div class="fc-table-wrap">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th style="width:12%">Data</th>
                            <th style="width:28%">Diagnóstico / Problema</th>
                            <th style="width:28%">Medicamento / Tratamento</th>
                            <th style="width:12%">Dose</th>
                            <th>Responsável</th>
                        </tr>
                    </thead>
                    <tbody data-table="ocorrencias">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                        <tr>
                            <td><input type="text" class="fc-td-input" data-cell="data"></td>
                            <td><input type="text" class="fc-td-input" data-cell="diagnostico"></td>
                            <td><input type="text" class="fc-td-input" data-cell="medicamento"></td>
                            <td><input type="text" class="fc-td-input" data-cell="dose"></td>
                            <td><input type="text" class="fc-td-input" data-cell="responsavel"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Destino Final -->
        <div class="fc-section">
            <div class="fc-section-title">🎯 Destino Final</div>
            <div class="fc-row cols-1-3">
                <div class="fc-field">
                    <label>Destino</label>
                    <div class="fc-check-group col" style="margin-top:4px;">
                        <label class="fc-check-item"><input type="checkbox" data-fk-bool="destino_venda"> Venda</label>
                        <label class="fc-check-item"><input type="checkbox" data-fk-bool="destino_abate"> Abate</label>
                        <label class="fc-check-item"><input type="checkbox" data-fk-bool="destino_morte"> Morte</label>
                        <label class="fc-check-item"><input type="checkbox" data-fk-bool="destino_transf"> Transferência</label>
                    </div>
                </div>
                <div>
                    <div class="fc-row cols-3" style="margin-bottom: 10px;">
                        <div class="fc-field">
                            <label>Data</label>
                            <input type="text" class="fc-input" data-fk="destino_data" placeholder="dd/mm/aaaa">
                        </div>
                        <div class="fc-field">
                            <label>Peso final (kg)</label>
                            <input type="text" class="fc-input" data-fk="destino_peso">
                        </div>
                        <div class="fc-field">
                            <label>Valor obtido (R$)</label>
                            <input type="text" class="fc-input" data-fk="destino_valor">
                        </div>
                    </div>
                    <div class="fc-field">
                        <label>Destino / Comprador / Causa</label>
                        <textarea class="fc-textarea" rows="2" data-fk="destino_obs"></textarea>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="fc-footer">
        <div class="fc-footer-text">AgroAmigo ATERPEC · Verde Conecta / UEMA · SAF Maranhão · <?= date('Y') ?></div>
        <div class="fc-footer-label">Ficha Única — Controle Integrado</div>
    </div>

</div>

<script>
window.FICHA_TIPO  = 'controle';
window.FICHA_CSRF  = '<?= csrf_token() ?>';
window.FICHA_DADOS = <?= json_encode($_fc_dados ?? (object)[], JSON_UNESCAPED_UNICODE) ?>;
</script>
</body>
</html>
