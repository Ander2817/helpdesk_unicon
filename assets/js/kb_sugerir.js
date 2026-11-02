/* assets/js/kb_sugerir.js — mientras se escribe el asunto del ticket, sugiere artículos de la base de conocimiento. */
(function () {
    const inp = document.getElementById('asunto'), caja = document.getElementById('kb-sugerencias');
    if (!inp || !caja) return;
    const base = (document.currentScript && document.currentScript.getAttribute('data-base')) || '';
    let t = null, ultimo = '';
    inp.addEventListener('input', function () {
        clearTimeout(t);
        const q = inp.value.trim();
        if (q.length < 4) { caja.textContent = ''; return; }
        t = setTimeout(async function () {
            ultimo = q;
            try {
                const r = await fetch(base + 'procesos/kb_buscar.php?q=' + encodeURIComponent(q), { credentials: 'same-origin' });
                if (!r.ok || q !== ultimo) return;
                const d = await r.json();
                caja.textContent = '';
                if (!d.items || !d.items.length) return;
                const w = document.createElement('div');
                w.style.cssText = 'border:1px solid #fde68a;background:#fffbeb;border-radius:8px;padding:8px 12px;font-size:.78rem;margin-top:6px;';
                const h = document.createElement('div'); h.style.cssText = 'font-weight:600;color:#92400e;margin-bottom:4px;';
                h.textContent = '💡 Quizá esto te ayude antes de enviar el ticket:'; w.appendChild(h);
                d.items.forEach(function (it) {
                    const a = document.createElement('a'); a.href = it.u; a.target = '_blank'; a.rel = 'noopener';
                    a.textContent = it.t; a.style.cssText = 'display:block;color:#b45309;padding:2px 0;'; w.appendChild(a);
                });
                caja.appendChild(w);
            } catch (e) {}
        }, 400);
    });
})();
