/* Carregar html2pdf.js para geração de PDF client-side */
(function () {
    var s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
    document.head.appendChild(s);
})();

/* Esconde a topbar quando a ficha está dentro de um iframe (modal) */
if (window !== window.top) {
    document.addEventListener('DOMContentLoaded', function () {
        var topbar = document.querySelector('.fc-topbar');
        if (topbar) topbar.style.display = 'none';
    });
}

/* Máscara automática de data (dd/mm/aaaa) */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-mask="date"]').forEach(function (input) {
        input.placeholder = 'dd/mm/aaaa';
        input.maxLength   = 10;

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') return;
            if (!/\d/.test(e.key)) e.preventDefault();
        });

        input.addEventListener('input', function () {
            var d = this.value.replace(/\D/g, '').substring(0, 8);
            if (d.length > 4)      this.value = d.slice(0,2) + '/' + d.slice(2,4) + '/' + d.slice(4);
            else if (d.length > 2) this.value = d.slice(0,2) + '/' + d.slice(2);
            else                   this.value = d;
        });
    });
});

/* Cálculo automático de ganho de peso nas tabelas de pesagem */
document.addEventListener('DOMContentLoaded', function () {
    var rows = Array.from(document.querySelectorAll('tr.pesagem-row'));
    if (!rows.length) return;

    function calcular() {
        rows.forEach(function (row, i) {
            var ganhoInput = row.querySelector('.ganho-pesagem');
            if (!ganhoInput || i === 0) return;
            var prev  = rows[i - 1];
            var p1    = parseFloat(prev.querySelector('.peso-pesagem')?.value);
            var d1    = prev.querySelector('.data-pesagem')?.value;
            var p2    = parseFloat(row.querySelector('.peso-pesagem')?.value);
            var d2    = row.querySelector('.data-pesagem')?.value;
            if (p1 >= 0 && p2 >= 0 && d1 && d2) {
                var dias = (new Date(d2) - new Date(d1)) / 86400000;
                if (dias > 0) {
                    var ganho = Math.round((p2 - p1) * 1000 / dias);
                    ganhoInput.value = ganho + ' g/dia';
                    ganhoInput.style.color = ganho >= 0 ? 'var(--g700)' : '#dc2626';
                    return;
                }
            }
            ganhoInput.value = '';
            ganhoInput.style.color = '';
        });
    }

    document.addEventListener('input', function (e) {
        if (e.target.matches('.peso-pesagem, .data-pesagem')) calcular();
    });
    document.addEventListener('change', function (e) {
        if (e.target.matches('.peso-pesagem, .data-pesagem')) calcular();
    });
});

/* Exportar PDF — chamada pelo modal pai via contentWindow.baixarPDF() */
window.baixarPDF = function (nomeArquivo) {
    if (typeof html2pdf === 'undefined') {
        setTimeout(function () { window.baixarPDF(nomeArquivo); }, 400);
        return;
    }
    var element = document.querySelector('.fc-page');
    html2pdf().set({
        margin:      [8, 8],
        filename:    (nomeArquivo || 'ficha-agroamigo') + '.pdf',
        image:       { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, logging: false },
        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(element).save();
};

/* =====================================================
   SAVE / PREFILL ENGINE
   ===================================================== */

/* Preenche o formulário com dados salvos */
window.preencherFicha = function (d) {
    if (!d || typeof d !== 'object') return;

    // Campos simples
    document.querySelectorAll('[data-fk]').forEach(function (el) {
        var v = d[el.dataset.fk];
        if (v !== undefined && v !== null) el.value = v;
    });

    // Checkboxes individuais (booleano)
    document.querySelectorAll('[data-fk-bool]').forEach(function (el) {
        var v = d[el.dataset.fkBool];
        if (v !== undefined) el.checked = !!v;
    });

    // Tabelas: <tbody data-table="nome"><tr><td><input data-cell="col">
    document.querySelectorAll('[data-table]').forEach(function (tbody) {
        var tname = tbody.dataset.table;
        var rows_data = d[tname];
        if (!Array.isArray(rows_data)) return;
        var rows = tbody.querySelectorAll('tr');
        rows_data.forEach(function (rowData, i) {
            if (!rows[i]) return;
            Object.keys(rowData).forEach(function (key) {
                var cell = rows[i].querySelector('[data-cell="' + key + '"]');
                if (cell) cell.value = rowData[key];
            });
        });
    });
};

/* Coleta todos os dados do formulário */
window.coletarFicha = function () {
    var dados = {};

    document.querySelectorAll('[data-fk]').forEach(function (el) {
        dados[el.dataset.fk] = el.value;
    });

    document.querySelectorAll('[data-fk-bool]').forEach(function (el) {
        dados[el.dataset.fkBool] = el.checked;
    });

    document.querySelectorAll('[data-table]').forEach(function (tbody) {
        var tname = tbody.dataset.table;
        dados[tname] = [];
        tbody.querySelectorAll('tr').forEach(function (tr) {
            var row = {};
            tr.querySelectorAll('[data-cell]').forEach(function (cell) {
                row[cell.dataset.cell] = cell.value;
            });
            dados[tname].push(row);
        });
    });

    return dados;
};

/* Salva a ficha via AJAX */
window.salvarFicha = function () {
    if (!window.FICHA_TIPO || !window.FICHA_CSRF) {
        fcToast('Ficha não configurada para salvamento.', 'danger');
        return;
    }

    var btn = document.getElementById('fcBtnSave');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Salvando...'; }

    var dados = window.coletarFicha();

    fetch('salvar.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    'csrf='  + encodeURIComponent(window.FICHA_CSRF)
               + '&tipo=' + encodeURIComponent(window.FICHA_TIPO)
               + '&dados='+ encodeURIComponent(JSON.stringify(dados))
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-floppy"></i> Salvar'; }
        if (res.ok) {
            fcToast('Ficha salva! ' + res.hora, 'success');
            var ts = document.getElementById('fcSavedAt');
            if (ts) ts.textContent = 'Salvo: ' + res.hora;
        } else {
            fcToast('Erro: ' + (res.erro || 'Tente novamente'), 'danger');
        }
    })
    .catch(function () {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-floppy"></i> Salvar'; }
        fcToast('Sem conexão. Tente novamente.', 'danger');
    });
};

/* Toast de notificação */
function fcToast(msg, tipo) {
    var t = document.getElementById('fcToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'fcToast';
        document.body.appendChild(t);
    }
    t.className = 'fc-toast fc-toast-' + (tipo || 'success');
    t.textContent = msg;
    t.style.opacity = '1';
    clearTimeout(t._timer);
    t._timer = setTimeout(function () { t.style.opacity = '0'; }, 2800);
}

/* Preenche automaticamente na carga se houver dados salvos */
document.addEventListener('DOMContentLoaded', function () {
    if (window.FICHA_DADOS) {
        preencherFicha(window.FICHA_DADOS);
    }
});
