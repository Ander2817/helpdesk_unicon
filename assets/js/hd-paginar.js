/* assets/js/hd-paginar.js
 * Paginación automática para cualquier <table class="hd-table"> con más de 15 filas.
 * - No toca los filtros que ya existen: las filas que un filtro oculta (style.display = 'none') se respetan.
 * - Para excluir una tabla:  <table class="hd-table" data-sin-paginar>
 * - Cambiar el tamaño por tabla: data-por-pagina="25"
 */
(function () {
    function iniciar(tabla) {
        if (tabla.hasAttribute('data-sin-paginar') || tabla.closest('.modal')) return;
        const cuerpo = tabla.tBodies[0]; if (!cuerpo) return;
        const filas = () => Array.from(cuerpo.rows).filter(f => f.cells.length > 1 || f.cells[0].colSpan <= 1);
        const UMBRAL = 15;
        if (filas().length <= UMBRAL) return;

        let porPagina = parseInt(tabla.getAttribute('data-por-pagina') || '15', 10), pagina = 1, ocupado = false;
        const barra = document.createElement('div');
        barra.className = 'hd-paginador';
        barra.style.cssText = 'display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid #e2e8f0;font-size:.76rem;color:#64748b;';
        tabla.parentNode.insertBefore(barra, tabla.nextSibling);

        function pintar() {
            ocupado = true;
            const visibles = filas().filter(f => f.style.display !== 'none');
            const paginas = Math.max(1, Math.ceil(visibles.length / porPagina));
            if (pagina > paginas) pagina = paginas;
            const ini = (pagina - 1) * porPagina, fin = ini + porPagina;
            filas().forEach(f => { f.hidden = false; });
            visibles.forEach((f, i) => { f.hidden = !(i >= ini && i < fin); });

            barra.textContent = '';
            const info = document.createElement('span');
            info.textContent = visibles.length ? ('Mostrando ' + (ini + 1) + '–' + Math.min(fin, visibles.length) + ' de ' + visibles.length) : 'Sin resultados';
            const sel = document.createElement('select');
            sel.style.cssText = 'border:1px solid #cbd5e1;border-radius:6px;padding:2px 6px;font-size:.74rem;';
            [10, 15, 25, 50, 100].forEach(n => { const o = document.createElement('option'); o.value = n; o.textContent = n + ' por página'; if (n === porPagina) o.selected = true; sel.appendChild(o); });
            sel.addEventListener('change', () => { porPagina = parseInt(sel.value, 10); pagina = 1; pintar(); });
            const nav = document.createElement('div'); nav.style.cssText = 'display:flex;gap:4px;align-items:center;';
            const boton = (txt, p, activo, off) => {
                const b = document.createElement('button'); b.type = 'button'; b.textContent = txt; b.disabled = !!off;
                b.style.cssText = 'border:1px solid #cbd5e1;background:' + (activo ? '#E87722' : '#fff') + ';color:' + (activo ? '#fff' : '#334155') + ';border-radius:6px;padding:3px 9px;font-size:.74rem;cursor:' + (off ? 'default' : 'pointer') + ';opacity:' + (off ? '.45' : '1');
                if (!off) b.addEventListener('click', () => { pagina = p; pintar(); });
                return b;
            };
            nav.appendChild(boton('‹', pagina - 1, false, pagina === 1));
            const mostrar = new Set([1, paginas, pagina - 1, pagina, pagina + 1]);
            let previo = 0;
            Array.from(mostrar).filter(p => p >= 1 && p <= paginas).sort((a, b) => a - b).forEach(p => {
                if (p - previo > 1) { const s = document.createElement('span'); s.textContent = '…'; nav.appendChild(s); }
                nav.appendChild(boton(String(p), p, p === pagina, false)); previo = p;
            });
            nav.appendChild(boton('›', pagina + 1, false, pagina === paginas));
            barra.append(info, sel, nav);
            ocupado = false;
        }
        // Si otro script filtra filas (style.display), se vuelve a paginar
        new MutationObserver(() => { if (!ocupado) { pagina = 1; pintar(); } })
            .observe(cuerpo, { subtree: true, attributes: true, attributeFilter: ['style'] });
        pintar();
    }
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('table.hd-table').forEach(iniciar));
})();
