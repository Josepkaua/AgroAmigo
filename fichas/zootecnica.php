<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Zootécnica Individual — AgroAmigo ATERPEC</title>
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
            <div class="fc-header-title">Ficha Zootécnica Individual</div>
            <div class="fc-header-subtitle">Controle de animal — Projeto Verde Conecta / UEMA</div>
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
            <div class="fc-row cols-2-1">
                <div class="fc-field">
                    <label>Nome da Propriedade</label>
                    <input type="text" class="fc-input" placeholder="Ex: Fazenda São João">
                </div>
                <div class="fc-field">
                    <label>Data de Abertura</label>
                    <input type="text" class="fc-input" data-mask="date">
                </div>
            </div>
            <div class="fc-row cols-3">
                <div class="fc-field">
                    <label>Município / UF</label>
                    <input type="text" class="fc-input" placeholder="Ex: Bacabal / MA">
                </div>
                <div class="fc-field">
                    <label>Responsável pela Criação</label>
                    <input type="text" class="fc-input">
                </div>
                <div class="fc-field">
                    <label>Contato (WhatsApp)</label>
                    <input type="text" class="fc-input" placeholder="(XX) 9 9999-0000">
                </div>
            </div>
        </div>

        <!-- 2. Identificação do Animal -->
        <div class="fc-section">
            <div class="fc-section-title">🐄 Identificação do Animal</div>
            <div class="fc-row cols-3">
                <div class="fc-field">
                    <label>Brinco / Tatuagem</label>
                    <input type="text" class="fc-input" placeholder="Nº de identificação">
                </div>
                <div class="fc-field">
                    <label>Espécie</label>
                    <input type="text" class="fc-input" placeholder="Ex: Bovino, Caprino...">
                </div>
                <div class="fc-field">
                    <label>Raça</label>
                    <input type="text" class="fc-input" placeholder="Ex: Nelore, Santa Inês...">
                </div>
            </div>
            <div class="fc-row cols-4">
                <div class="fc-field">
                    <label>Sexo</label>
                    <div class="fc-check-group">
                        <label class="fc-check-item"><input type="checkbox"> Macho</label>
                        <label class="fc-check-item"><input type="checkbox"> Fêmea</label>
                    </div>
                </div>
                <div class="fc-field">
                    <label>Data de Nascimento</label>
                    <input type="date" class="fc-input">
                </div>
                <div class="fc-field">
                    <label>Peso ao Nascer (kg)</label>
                    <input type="number" step="0.1" min="0" class="fc-input">
                </div>
                <div class="fc-field">
                    <label>Pelagem / Cor</label>
                    <input type="text" class="fc-input">
                </div>
            </div>
            <div class="fc-row cols-2">
                <div class="fc-field">
                    <label>ID / Nome da Mãe</label>
                    <input type="text" class="fc-input">
                </div>
                <div class="fc-field">
                    <label>ID / Nome do Pai (Touro / Reprodutor)</label>
                    <input type="text" class="fc-input">
                </div>
            </div>
        </div>

        <!-- 3. Histórico de Pesagens -->
        <div class="fc-section">
            <div class="fc-section-title">⚖️ Histórico de Pesagens</div>
            <p class="fc-hint">Registre o peso do animal a cada pesagem. Meta: 200–500 g de ganho por dia conforme a espécie.</p>
            <div class="fc-table-wrap">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th style="width:14%">Data</th>
                            <th style="width:14%">Idade</th>
                            <th style="width:14%">Peso (kg)</th>
                            <th style="width:18%">Ganho médio (g/dia)</th>
                            <th style="width:16%">Escore corporal (1–5)</th>
                            <th>Responsável</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < 10; $i++): ?>
                        <tr class="pesagem-row">
                            <td><input type="date" class="fc-td-input data-pesagem"></td>
                            <td><input type="text" class="fc-td-input" placeholder="ex: 3 meses"></td>
                            <td><input type="number" step="0.1" min="0" class="fc-td-input peso-pesagem"></td>
                            <td><input type="text" class="fc-td-input fc-calc ganho-pesagem" readonly placeholder="auto"></td>
                            <td><input type="number" step="0.5" min="1" max="5" class="fc-td-input" placeholder="1–5"></td>
                            <td><input type="text" class="fc-td-input"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Ocorrências e Tratamentos -->
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
                    <tbody>
                        <?php for ($i = 0; $i < 7; $i++): ?>
                        <tr>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
                            <td><input type="text" class="fc-td-input"></td>
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
                    <div class="fc-check-group col" style="margin-top: 4px;">
                        <label class="fc-check-item"><input type="checkbox"> Venda</label>
                        <label class="fc-check-item"><input type="checkbox"> Abate próprio</label>
                        <label class="fc-check-item"><input type="checkbox"> Morte</label>
                        <label class="fc-check-item"><input type="checkbox"> Transferência</label>
                    </div>
                </div>
                <div>
                    <div class="fc-row cols-2" style="margin-bottom: 12px;">
                        <div class="fc-field">
                            <label>Data</label>
                            <input type="date" class="fc-input">
                        </div>
                        <div class="fc-field">
                            <label>Peso final (kg)</label>
                            <input type="number" step="0.1" min="0" class="fc-input">
                        </div>
                    </div>
                    <div class="fc-field">
                        <label>Destino / Comprador / Causa da morte</label>
                        <textarea class="fc-textarea" rows="2" placeholder="Descreva o destino do animal..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Observações Gerais -->
        <div class="fc-section">
            <div class="fc-section-title">📝 Observações Gerais</div>
            <textarea class="fc-textarea" rows="3" placeholder="Anote informações relevantes não contempladas nos campos acima..."></textarea>
        </div>

    </div>

    <div class="fc-footer">
        <div class="fc-footer-text">AgroAmigo ATERPEC · Verde Conecta / UEMA · SAF Maranhão · <?= date('Y') ?></div>
        <div class="fc-footer-label">Ficha Zootécnica Individual</div>
    </div>

</div>
</body>
</html>
