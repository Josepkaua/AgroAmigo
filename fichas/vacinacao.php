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
        "SELECT dados, salvo_em FROM fichas_salvas WHERE usuario_id = :uid AND tipo = 'vacinacao'"
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
    <title>Ficha de Vacinação — AgroAmigo ATERPEC</title>
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
            <div class="fc-header-title">Ficha de Vacinação</div>
            <div class="fc-header-subtitle">Calendário vacinal do rebanho — Projeto Verde Conecta / UEMA</div>
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
            <div class="fc-row cols-2-1">
                <div class="fc-field">
                    <label>Nome da Propriedade</label>
                    <input type="text" class="fc-input" data-fk="prop_nome">
                </div>
                <div class="fc-field">
                    <label>Período de Referência</label>
                    <input type="text" class="fc-input" data-fk="prop_periodo" placeholder="Ex: Jan–Dez 2025">
                </div>
            </div>
            <div class="fc-row cols-3">
                <div class="fc-field">
                    <label>Município / UF</label>
                    <input type="text" class="fc-input" data-fk="prop_municipio" placeholder="Ex: Bacabal / MA">
                </div>
                <div class="fc-field">
                    <label>Responsável</label>
                    <input type="text" class="fc-input" data-fk="prop_responsavel">
                </div>
                <div class="fc-field">
                    <label>Espécie Principal</label>
                    <input type="text" class="fc-input" data-fk="prop_especie" placeholder="Ex: Bovinos, Aves...">
                </div>
            </div>
        </div>

        <!-- 2. Identificação do Animal -->
        <div class="fc-section">
            <div class="fc-section-title">🐄 Identificação do Animal (quando individual)</div>
            <p class="fc-hint">Preencha apenas se for uma ficha individual. Deixe em branco para controle de rebanho.</p>
            <div class="fc-row cols-4">
                <div class="fc-field">
                    <label>Brinco / Tatuagem</label>
                    <input type="text" class="fc-input" data-fk="animal_brinco">
                </div>
                <div class="fc-field">
                    <label>Espécie</label>
                    <input type="text" class="fc-input" data-fk="animal_especie">
                </div>
                <div class="fc-field">
                    <label>Raça</label>
                    <input type="text" class="fc-input" data-fk="animal_raca">
                </div>
                <div class="fc-field">
                    <label>Data de Nascimento</label>
                    <input type="text" class="fc-input" data-fk="animal_nascimento" placeholder="dd/mm/aaaa">
                </div>
            </div>
        </div>

        <!-- 3. Registro de Vacinações -->
        <div class="fc-section">
            <div class="fc-section-title">💉 Registro de Vacinações</div>
            <p class="fc-hint">Guarde as embalagens das vacinas para confirmar lote e validade. Exigidas em fiscalizações sanitárias.</p>
            <div class="fc-table-wrap">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th style="width:18%">Nome da Vacina</th>
                            <th style="width:11%">Data Aplic.</th>
                            <th style="width:9%">Dose (mL)</th>
                            <th style="width:9%">Via</th>
                            <th style="width:10%">Lote</th>
                            <th style="width:11%">Validade</th>
                            <th style="width:14%">Próx. Reforço</th>
                            <th>Aplicador</th>
                        </tr>
                    </thead>
                    <tbody data-table="vacinacoes">
                        <?php for ($i = 0; $i < 15; $i++): ?>
                        <tr>
                            <td><input type="text" class="fc-td-input" data-cell="nome"></td>
                            <td><input type="text" class="fc-td-input" data-cell="data"></td>
                            <td><input type="text" class="fc-td-input" data-cell="dose"></td>
                            <td><input type="text" class="fc-td-input" data-cell="via"></td>
                            <td><input type="text" class="fc-td-input" data-cell="lote"></td>
                            <td><input type="text" class="fc-td-input" data-cell="validade"></td>
                            <td><input type="text" class="fc-td-input" data-cell="reforca"></td>
                            <td><input type="text" class="fc-td-input" data-cell="aplicador"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Calendário de Referência (Maranhão) — somente leitura, sem data-table -->
        <div class="fc-section">
            <div class="fc-section-title">📌 Calendário de Referência (Maranhão)</div>
            <p class="fc-hint">Use como guia para planejar as vacinações ao longo do ano. Consulte sempre o médico-veterinário.</p>
            <div class="fc-table-wrap">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th style="width:20%">Vacina</th>
                            <th style="width:20%">Espécie</th>
                            <th style="width:30%">Período / Frequência</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" class="fc-td-input" value="Febre Aftosa" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Bovinos" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Abril e Outubro (obrigatória)" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Lei estadual/federal" readonly></td>
                        </tr>
                        <tr>
                            <td><input type="text" class="fc-td-input" value="Brucelose" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Bovinos (fêmeas)" readonly></td>
                            <td><input type="text" class="fc-td-input" value="3 a 8 meses de vida (dose única)" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Apenas fêmeas" readonly></td>
                        </tr>
                        <tr>
                            <td><input type="text" class="fc-td-input" value="Newcastle" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Aves" readonly></td>
                            <td><input type="text" class="fc-td-input" value="7 dias (colírio), reforço 28 dias" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Reforço anual" readonly></td>
                        </tr>
                        <tr>
                            <td><input type="text" class="fc-td-input" value="Clostridioses" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Caprinos / Ovinos" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Anual, 30 dias antes da chuva" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Mortes súbitas" readonly></td>
                        </tr>
                        <tr>
                            <td><input type="text" class="fc-td-input" value="Circovirose (PCV2)" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Suínos" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Leitões a partir de 3 semanas" readonly></td>
                            <td><input type="text" class="fc-td-input" value="Dose única ou dupla" readonly></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Observações -->
        <div class="fc-section">
            <div class="fc-section-title">📝 Observações</div>
            <textarea class="fc-textarea" rows="3" data-fk="observacoes" placeholder="Anote reações vacinais, lotes com problema, observações gerais..."></textarea>
        </div>

        <!-- Assinatura -->
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
        <div class="fc-footer-label">Ficha de Vacinação</div>
    </div>

</div>

<script>
window.FICHA_TIPO  = 'vacinacao';
window.FICHA_CSRF  = '<?= csrf_token() ?>';
window.FICHA_DADOS = <?= json_encode($_fc_dados ?? (object)[], JSON_UNESCAPED_UNICODE) ?>;
</script>
</body>
</html>
