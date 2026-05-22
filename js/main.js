/* AgroAmigo — ATERPEC | main.js */

// Navbar shadow ao rolar
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => {
    nav?.classList.toggle('scrolled', window.scrollY > 20);
}, { passive: true });

// Fechar menu mobile ao clicar em um link
document.querySelectorAll('.aa-nav-link:not(.dropdown-toggle)').forEach(link => {
    link.addEventListener('click', () => {
        const collapse = document.getElementById('navMenu');
        if (collapse?.classList.contains('show')) {
            bootstrap.Collapse.getInstance(collapse)?.hide();
        }
    });
});
