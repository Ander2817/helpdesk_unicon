// assets/js/ia_widget.js — burbuja de chat del asistente (usuario / técnico / admin) + ayudas en tickets.
(function () {
  const s = document.currentScript; if (!s) return;
  const ROL = parseInt(s.dataset.rol, 10) || 0, URL = s.dataset.url; if (!ROL || !URL) return;
  const NOMBRE = {1: 'usuario', 2: 'tecnico', 3: 'admin'}[ROL];
  const SALUDO = {
    usuario: '¡Hola! Cuéntame qué problema tienes y te digo qué puede ser y qué puedes probar antes de crear el ticket.',
    tecnico: 'Hola. Pregúntame por un fallo, comando o procedimiento, o cómo usar el sistema. En un ticket puedo darte diagnóstico, redactar la solución o resumirlo.',
    admin: 'Hola. Pregúntame por cifras (tickets de la semana, SLA, carga por técnico, equipos con más fallas) o por cómo hacer algo en el sistema (usuarios, estados, reportes, QR).'
  }[NOMBRE];
  const RAPIDAS = {
    usuario: ['Mi computadora está muy lenta', 'No tengo internet', '¿Cómo creo un ticket?', '¿Qué significa Pendiente?'],
    tecnico: ['Impresora en red no imprime', '¿Cómo funciona la pausa del SLA?', 'Usuario sin acceso a carpeta compartida', '¿Cómo registro un mantenimiento?'],
    admin: ['Resumen de la semana', 'SLA vencidos y en riesgo', '¿Quién tiene más carga?', '¿Cómo desactivo un usuario?', '¿Cómo imprimo las etiquetas QR?']
  }[NOMBRE];
  const accionChat = ROL === 3 ? 'admin_chat' : 'chat';
  let hist = [], ocupado = false;

  const el = (t, c, txt) => { const e = document.createElement(t); if (c) e.className = c; if (txt != null) e.textContent = txt; return e; };
  function post(datos) {
    const fd = new FormData(); for (const k in datos) fd.append(k, datos[k]);
    return fetch(URL, {method: 'POST', body: fd, credentials: 'same-origin'})   // csrf_script() añade X-CSRF-Token
      .then(r => r.json().catch(() => ({ok: false, texto: 'Respuesta no válida del servidor.'})))
      .catch(() => ({ok: false, texto: 'No se pudo conectar con el servidor.'}));
  }

  // ---- estilos
  const st = el('style'); st.textContent = `
  #iaFab{position:fixed;right:22px;bottom:22px;z-index:1080;display:flex;align-items:center;gap:9px;height:56px;padding:0 20px 0 14px;border:0;border-radius:28px;color:#fff;font-weight:600;font-size:15px;cursor:pointer;
    background:linear-gradient(135deg,#0d6efd 0%,#6f42c1 100%);box-shadow:0 6px 20px rgba(13,110,253,.45);transition:transform .15s,box-shadow .15s}
  #iaFab:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(111,66,193,.5)}
  #iaFab svg{width:26px;height:26px;flex:none}
  #iaFab::after{content:"";position:absolute;inset:0;border-radius:28px;border:2px solid rgba(13,110,253,.55);animation:iaPulso 2.4s ease-out infinite;pointer-events:none}
  @keyframes iaPulso{0%{transform:scale(1);opacity:.8}100%{transform:scale(1.25,1.45);opacity:0}}
  #iaBox{position:fixed;right:22px;bottom:90px;z-index:1080;width:460px;max-width:calc(100vw - 24px);height:640px;max-height:calc(100vh - 110px);background:#fff;color:#212529;border-radius:16px;
    box-shadow:0 14px 44px rgba(0,0,0,.32);display:none;flex-direction:column;overflow:hidden;font-size:15px}
  #iaBox.open{display:flex;animation:iaEntra .18s ease-out}
  #iaBox.grande{width:min(760px,calc(100vw - 24px));height:calc(100vh - 110px)}
  @keyframes iaEntra{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
  #iaBox header{background:linear-gradient(135deg,#0d6efd,#6f42c1);color:#fff;padding:12px 14px;display:flex;align-items:center;gap:10px}
  #iaBox header .iaAv{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex:none}
  #iaBox header .iaAv svg{width:20px;height:20px}
  #iaBox header .iaT{flex:1;line-height:1.15}#iaBox header .iaT b{display:block;font-size:15px}#iaBox header .iaT span{font-size:11.5px;opacity:.85}
  #iaBox header button{background:rgba(255,255,255,.15);border:0;color:#fff;width:30px;height:30px;border-radius:8px;font-size:16px;cursor:pointer;line-height:1}
  #iaBox header button:hover{background:rgba(255,255,255,.3)}
  #iaMsgs{flex:1;overflow-y:auto;padding:14px;background:#f4f6fa}
  .iaM{max-width:92%;padding:10px 13px;border-radius:14px;margin-bottom:10px;word-wrap:break-word;line-height:1.5}
  .iaM.u{background:#0d6efd;color:#fff;margin-left:auto;border-bottom-right-radius:4px;white-space:pre-wrap}
  .iaM.a{background:#fff;border:1px solid #e0e5ec;border-bottom-left-radius:4px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
  .iaM.a p{margin:0 0 8px}.iaM.a p:last-of-type{margin-bottom:0}
  .iaM.a ol,.iaM.a ul{margin:4px 0 8px;padding-left:22px}.iaM.a li{margin-bottom:4px}
  .iaM.a code{background:#eef1f6;border-radius:4px;padding:1px 5px;font-size:13px;font-family:Consolas,monospace}
  .iaM.a h4{font-size:15px;margin:8px 0 4px;font-weight:700}
  .iaM small{display:block;opacity:.6;margin-top:8px;font-size:11.5px}
  .iaPunt i{display:inline-block;width:7px;height:7px;border-radius:50%;background:#8a94a6;margin-right:4px;animation:iaP 1.2s infinite}.iaPunt i:nth-child(2){animation-delay:.2s}.iaPunt i:nth-child(3){animation-delay:.4s}
  @keyframes iaP{0%,80%,100%{opacity:.25}40%{opacity:1}}
  #iaQ{padding:8px 12px;display:flex;gap:6px;flex-wrap:wrap;background:#f4f6fa;border-top:1px solid #e6eaf0}
  #iaQ button{font-size:12.5px;border:1px solid #b6cffb;color:#0d6efd;background:#fff;border-radius:16px;padding:3px 11px;cursor:pointer}#iaQ button:hover{background:#eaf2ff}
  #iaBox form{display:flex;gap:8px;padding:10px;border-top:1px solid #e0e5ec;background:#fff}
  #iaBox input{flex:1;border:1px solid #ced4da;border-radius:10px;padding:9px 12px;font-size:14.5px}#iaBox input:focus{outline:2px solid #b6cffb;border-color:#0d6efd}
  #iaBox form button{border:0;background:linear-gradient(135deg,#0d6efd,#6f42c1);color:#fff;border-radius:10px;padding:0 18px;cursor:pointer;font-weight:600}
  .iaPanel{border:1px solid #b6cffb;border-left:4px solid #0d6efd;border-radius:8px;padding:12px 14px;margin-top:10px;background:#f4f8ff;font-size:14.5px;line-height:1.5}
  .iaPanel p{margin:0 0 8px}.iaPanel ol,.iaPanel ul{margin:4px 0 8px;padding-left:22px}.iaPanel code{background:#e4ecfb;border-radius:4px;padding:1px 5px}
  .iaBtns{display:flex;gap:6px;flex-wrap:wrap;margin:10px 0}
  @media(max-width:520px){#iaFab span{display:none}#iaFab{padding:0 14px}}`;
  document.head.appendChild(st);

  // ---- texto con formato básico (negritas, `código`, listas, títulos) construido con nodos: nunca se inserta HTML del modelo
  function inline(par, t) {
    t.split(/(\*\*[^*]+\*\*|`[^`]+`)/).forEach(x => {
      if (!x) return;
      if (x.startsWith('**') && x.endsWith('**') && x.length > 4) par.appendChild(el('b', '', x.slice(2, -2)));
      else if (x.startsWith('`') && x.endsWith('`') && x.length > 2) par.appendChild(el('code', '', x.slice(1, -1)));
      else par.appendChild(document.createTextNode(x));
    });
  }
  function formatear(cont, texto) {
    cont.textContent = ''; let lista = null, tipo = '';
    String(texto).split('\n').forEach(l => {
      const o = l.match(/^\s*(\d+)[.)]\s+(.*)$/), v = l.match(/^\s*[-*•]\s+(.*)$/), h = l.match(/^\s*#{1,4}\s+(.*)$/);
      if (o || v) {
        const t = o ? 'ol' : 'ul';
        if (!lista || tipo !== t) { lista = el(t); tipo = t; cont.appendChild(lista); }
        const li = el('li'); inline(li, o ? o[2] : v[1]); lista.appendChild(li); return;
      }
      lista = null;
      if (!l.trim()) return;
      if (h) { const e = el('h4'); inline(e, h[1]); cont.appendChild(e); return; }
      const p = el('p'); inline(p, l); cont.appendChild(p);
    });
  }

  // ---- burbuja
  const ICONO = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.6L18.5 9l-4.7 1.4L12 15l-1.8-4.6L5.5 9l4.7-1.4z" fill="currentColor" stroke="none"/><path d="M18.5 15l.9 2.1 2.1.9-2.1.9-.9 2.1-.9-2.1-2.1-.9 2.1-.9z" fill="currentColor" stroke="none"/><path d="M5 15.5l.7 1.6 1.6.7-1.6.7L5 20l-.7-1.5L2.7 17.8l1.6-.7z" fill="currentColor" stroke="none"/></svg>';
  const fab = el('button'); fab.id = 'iaFab'; fab.type = 'button'; fab.title = 'Asistente IA'; fab.setAttribute('aria-label', 'Abrir asistente de IA');
  fab.innerHTML = ICONO; fab.appendChild(el('span', '', 'Asistente IA'));   // ICONO es una constante propia, no viene de datos
  const box = el('div'); box.id = 'iaBox';
  const hd = el('header'); const av = el('div', 'iaAv'); av.innerHTML = ICONO;
  const tt = el('div', 'iaT'); tt.appendChild(el('b', '', 'Asistente HelpDesk')); tt.appendChild(el('span', '', {usuario: 'Te ayudo con tu problema antes de crear el ticket', tecnico: 'Diagnóstico y soluciones para soporte', admin: 'Reportes y análisis del sistema'}[NOMBRE]));
  const big = el('button', '', '⤢'); big.type = 'button'; big.title = 'Agrandar / reducir'; big.setAttribute('aria-label', 'Agrandar');
  const x = el('button', '', '×'); x.type = 'button'; x.title = 'Cerrar'; x.setAttribute('aria-label', 'Cerrar');
  hd.append(av, tt, big, x);
  const msgs = el('div'); msgs.id = 'iaMsgs';
  const rap = el('div'); rap.id = 'iaQ';
  const form = el('form'); const inp = el('input'); inp.maxLength = 500; inp.placeholder = 'Escribe tu consulta…'; inp.autocomplete = 'off';
  const env = el('button', '', 'Enviar'); env.type = 'submit'; form.append(inp, env);
  box.append(hd, msgs, rap, form); document.body.append(fab, box);

  function burbuja(t, c, nota) {
    const m = el('div', 'iaM ' + c);
    if (c === 'a') formatear(m, t); else m.textContent = t;
    if (nota) m.appendChild(el('small', '', nota));
    msgs.appendChild(m); msgs.scrollTop = msgs.scrollHeight; return m;
  }
  function pensando() { const m = el('div', 'iaM a iaPunt'); m.append(el('i'), el('i'), el('i')); msgs.appendChild(m); msgs.scrollTop = msgs.scrollHeight; return m; }
  function enviar(txt) {
    txt = (txt || '').trim(); if (!txt || ocupado) return;
    ocupado = true; inp.value = ''; burbuja(txt, 'u'); const w = pensando();
    post({accion: accionChat, mensaje: txt, hist: JSON.stringify(hist)}).then(r => {
      w.remove(); const m = burbuja(r.texto || 'Sin respuesta.', 'a', r.ok ? (r.fuente === 'ia' ? 'Generado por IA: verifícalo antes de aplicarlo' : 'Respuesta del sistema (sin IA)') : null);
      m.scrollIntoView({block: 'start', behavior: 'smooth'});   // se lee desde el inicio de la respuesta
      if (r.ok) { hist.push({r: 'u', t: txt}, {r: 'a', t: r.texto}); hist = hist.slice(-6); }
      ocupado = false;
    });
  }
  burbuja(SALUDO, 'a');
  RAPIDAS.forEach(q => { const b = el('button', '', q); b.type = 'button'; b.onclick = () => enviar(q); rap.appendChild(b); });
  fab.onclick = () => { box.classList.toggle('open'); if (box.classList.contains('open')) inp.focus(); };
  x.onclick = () => box.classList.remove('open');
  big.onclick = () => box.classList.toggle('grande');
  form.onsubmit = e => { e.preventDefault(); enviar(inp.value); };
  window.iaAsistente = {abrir: () => { box.classList.add('open'); inp.focus(); }, preguntar: t => { box.classList.add('open'); enviar(t); }};   // lo usan otras pantallas (p. ej. «Resumen semanal con IA»)

  // ---- botones dentro de la página de un ticket (técnico y admin)
  const params = new URLSearchParams(location.search), idT = parseInt(params.get('id'), 10) || 0;
  const sol = document.getElementById('solucion-texto'), com = document.getElementById('nuevo-comentario');
  if (ROL >= 2 && idT && (sol || com)) {
    const anchor = (sol || com).closest('form') || (sol || com).parentNode;
    const barra = el('div', 'iaBtns'); const panel = el('div', 'iaPanel'); panel.style.display = 'none';
    const acciones = [['ticket_diagnostico', '🔍 Diagnóstico IA'], ['ticket_resumen', '📝 Resumir'], ['ticket_solucion', '✅ Redactar solución'], ['ticket_respuesta', '💬 Redactar respuesta']];
    acciones.forEach(([a, t]) => {
      const b = el('button', 'btn btn-sm btn-outline-primary', t); b.type = 'button';
      b.onclick = () => {
        panel.style.display = 'block'; panel.textContent = 'Pensando…'; panel.querySelectorAll && 0;
        post({accion: a, id_ticket: idT}).then(r => {
          formatear(panel, r.texto || '');
          if (r.ok && (a === 'ticket_solucion' || a === 'ticket_respuesta')) {
            const dest = a === 'ticket_solucion' ? sol : com;
            if (dest) { const u = el('button', 'btn btn-sm btn-primary d-block mt-2', a === 'ticket_solucion' ? 'Usar como solución (podrás editarla)' : 'Usar como comentario (podrás editarlo)'); u.type = 'button'; u.onclick = () => { dest.value = r.texto.replace(/\*\*|`/g, ''); dest.focus(); }; panel.appendChild(u); }
          }
          if (r.ok) panel.appendChild(el('small', 'd-block text-muted mt-2', r.fuente === 'ia' ? 'Generado por IA: revísalo antes de usarlo.' : 'Respuesta del sistema (sin IA).'));
        });
      };
      barra.appendChild(b);
    });
    anchor.parentNode.insertBefore(barra, anchor); anchor.parentNode.insertBefore(panel, anchor);
  }

  // ---- ayuda al crear un ticket (usuario/técnico/admin): panel bajo la descripción
  const desc = document.getElementById('descripcion'), asunto = document.getElementById('asunto'), selCat = document.getElementById('select-categoria');
  if (desc && asunto && document.querySelector('form') && !idT) {
    const b = el('button', 'btn btn-sm btn-outline-primary mt-2', '✨ Sugerencias con IA'); b.type = 'button';
    const p = el('div', 'iaPanel'); p.style.display = 'none';
    desc.parentNode.append(b, p);
    b.onclick = () => {
      const t = (asunto.value + '. ' + desc.value).trim(); if (t.length < 8) { p.style.display = 'block'; p.textContent = 'Escribe primero el asunto y una breve descripción.'; return; }
      b.disabled = true; p.style.display = 'block'; p.textContent = 'Pensando…';
      post({accion: 'ayuda_ticket', mensaje: t}).then(r => {
        b.disabled = false; formatear(p, r.texto || '');
        if (r.ok && r.categoria_id && selCat) { const o = [...selCat.options].find(o => o.value == r.categoria_id); if (o) { const u = el('button', 'btn btn-sm btn-light border d-block mt-2', 'Elegir categoría sugerida: ' + r.categoria); u.type = 'button'; u.onclick = () => { selCat.value = r.categoria_id; selCat.dispatchEvent(new Event('change')); }; p.appendChild(u); } }
        if (r.ok) p.appendChild(el('small', 'd-block text-muted mt-2', r.fuente === 'ia' ? 'Generado por IA: son sugerencias, no una garantía.' : 'Respuesta del sistema (sin IA).'));
      });
    };
  }
})();
