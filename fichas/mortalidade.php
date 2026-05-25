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
            <div class="fc-header-badge-label">Nº da Ficha</div>
            <div class="fc-header-badge-value">___________</div>
        </div>
    </div>

    <div class="fc-body">

        <!-- 1. Dados da Propriedade -->
        <div class="fc-section">
            <div class="fc-section-title">🏡 Dados da Propriedade</div>
            <div class="fc-row cols-3">
                <div class="fc-field">
                    <label>Nome da Propriedade</label>
                    <input type="text" class="fc-input">
                </div>
                <div class="fc-field">
                    <label>Município / UF</label>
                    <input type="text" class="fc-input" placeholder="Ex: Bacabal / MA">
                </div>
                <div class="fc-field">
                    <label>Período de Referência</label>
                    <input type="text" class="fc-input" placeholder="Ex: Jan 2025">
                </div>
            </div>
            <div class="fc-row cols-3">
                <div class="fc-field">
                    <label>Responsável</label>
                    <input type="text" class="fc-input">
                </div>
                <div class="fc-field">
                    <label>Espécie</label>
                    <input type="text" class="fc-input" placeholder="Ex: Bovinos, Caprinos...">
                </div>
                <div class="fc-field">
                    <label>Total do rebanho no início do período</label>
                    <input type="text" class="fc-input" placeholder="Nº de animais">
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
                    <tbody>
                        <?php for ($i = 0; $i < 12; $i++): ?>
                        <tr>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input" placeholder="Bezerro, adulto..."></td>
                            <td><input type="text" class="fc-td-input" placeholder="dias/meses"></td>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
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
                    <input type="text" class="fc-input">
                </div>
                <div class="fc-field">
                    <label>Taxa de Mortalidade (%)</label>
                    <input type="text" class="fc-input" placeholder="óbitos ÷ rebanho × 100">
                </div>
                <div class="fc-field">
                    <label>Categoria Mais Afetada</label>
                    <input type="text" class="fc-input">
                </div>
                <div class="fc-field">
                    <label>Causa Mais Frequente</label>
                    <input type="text" class="fc-input">
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
                    <tbody>
                        <?php
                        $categorias = ['Crias / Filhotes', 'Jovens', 'Adultos', 'Fêmeas adultas', 'Machos adultos'];
                        foreach ($categorias as $cat):
                        ?>
                        <tr>
                            <td><input type="text" class="fc-td-input" value="<?= $cat ?>" readonly></td>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
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
                    <textarea class="fc-textarea" rows="2" placeholder="Descreva a causa provável, se identificada..."></textarea>
                </div>
            </div>
            <div class="fc-row cols-1">
                <div class="fc-field">
                    <label>Providências Adotadas / Plano de Ação</label>
                    <textarea class="fc-textarea" rows="2" placeholder="O que foi feito para evitar novas mortes? Vacinação, vermifugação, mudança de manejo..."></textarea>
                </div>
            </div>
        </div>

        <!-- 5. Assinatura -->
        <div class="fc-section">
            <div class="fc-row cols-2">
                <div class="fc-field">
                    <label>Assinatura do Produtor</label>
                    <input type="text" class="fc-input" style="margin-top: 20px;">
                </div>
                <div class="fc-field">
                    <label>Assinatura do Técnico Responsável</label>
                    <input type="text" class="fc-input" style="margin-top: 20px;">
                </div>
            </div>
        </div>

    </div>

    <div class="fc-footer">
        <div class="fc-footer-text">AgroAmigo ATERPEC · Verde Conecta / UEMA · SAF Maranhão · <?= date('Y') ?></div>
        <div class="fc-footer-label">Ficha de Mortalidade</div>
    </div>

</div>
</body>
</html>
