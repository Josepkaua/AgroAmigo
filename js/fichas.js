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
