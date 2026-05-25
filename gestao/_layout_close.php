    </div><!-- /g-content -->
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Scroll horizontal em tabelas no mobile ────────────────
document.querySelectorAll('.g-table').forEach(function(t) {
    var wrap = document.createElement('div');
    wrap.className = 'g-table-wrap';
    t.parentNode.insertBefore(wrap, t);
    wrap.appendChild(t);
});

// ── Sidebar mobile ────────────────────────────────────────
(function(){
    var sidebar = document.querySelector('.g-sidebar');
    var overlay = document.getElementById('g-sidebar-overlay');
    var toggleBtn = document.getElementById('g-mob-toggle');
    if (!toggleBtn) return;
    function open()  { sidebar.classList.add('g-open');    overlay.classList.add('g-open'); }
    function close() { sidebar.classList.remove('g-open'); overlay.classList.remove('g-open'); }
    toggleBtn.addEventListener('click', open);
    overlay.addEventListener('click', close);
    // Fecha ao clicar num link do menu no mobile
    sidebar.querySelectorAll('.g-nav-link').forEach(function(l) {
        l.addEventListener('click', function() {
            if (window.innerWidth <= 1024) close();
        });
    });
})();

// ── Auto-refresh em tempo real ────────────────────────────
(function(){
    var INTERVAL = 30; // segundos
    var count = INTERVAL;
    var paused = false;
    var countEl = document.getElementById('g-refresh-count');
    var labelEl = document.getElementById('g-refresh-label');
    var nowBtn  = document.getElementById('g-refresh-now');
    var dot     = document.querySelector('.g-refresh-dot');
    if (!countEl) return;

    var timer = setInterval(function() {
        if (paused) return;
        count--;
        countEl.textContent = count;
        if (count <= 0) { location.reload(); }
    }, 1000);

    // Atualizar agora
    nowBtn && nowBtn.addEventListener('click', function() {
        location.reload();
    });

    // Pausa ao mover o mouse sobre um formulário ou input
    document.addEventListener('focusin', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
            paused = true;
            dot.style.background = '#94a3b8';
            labelEl.innerHTML = 'Pausado <strong id="g-refresh-count">' + count + '</strong>s';
            countEl = document.getElementById('g-refresh-count');
        }
    });
    document.addEventListener('focusout', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
            paused = false;
            dot.style.background = '#22c55e';
            labelEl.innerHTML = 'Atualiza em <strong id="g-refresh-count">' + count + '</strong>s';
            countEl = document.getElementById('g-refresh-count');
        }
    });
})();
</script>
</body>
</html>
