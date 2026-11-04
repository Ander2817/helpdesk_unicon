/* assets/js/responsive.js — comportamiento móvil (módulo 12): botón ☰ del menú, cierre al tocar fuera/enlace, y etiquetas para tablas apiladas. */
(function () {
  'use strict';
  function ready(fn) { if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function () {
    var side = document.getElementById('sidebar');
    var bar = document.querySelector('.hd-topbar');

    // ---- Botón ☰ (se crea solo si el header no lo trae ya) y fondo oscuro
    if (side && bar) {
      var burger = document.getElementById('hdBurger');
      if (!burger) {
        burger = document.createElement('button');
        burger.type = 'button'; burger.id = 'hdBurger'; burger.className = 'hd-burger';
        burger.setAttribute('aria-label', 'Abrir menú'); burger.innerHTML = '<span style="font-size:1.35rem;line-height:1">&#9776;</span>';
        bar.insertBefore(burger, bar.firstChild);
      }
      var back = document.querySelector('.hd-backdrop');
      if (!back) { back = document.createElement('div'); back.className = 'hd-backdrop'; document.body.appendChild(back); }
      var abrir = function (v) {
        side.classList.toggle('open', v); back.classList.toggle('show', v);
        document.body.classList.toggle('hd-menu-open', v);
        burger.setAttribute('aria-expanded', v ? 'true' : 'false');
      };
      burger.addEventListener('click', function () { abrir(!side.classList.contains('open')); });
      back.addEventListener('click', function () { abrir(false); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') abrir(false); });
      side.addEventListener('click', function (e) { var a = e.target.closest('a[href]'); if (a && a.getAttribute('href') !== '#') abrir(false); });
      window.addEventListener('resize', function () { if (window.innerWidth > 900) abrir(false); });
    }

    // ---- Tablas: copia el texto de cada encabezado como data-label de su celda (para la vista de tarjetas en móvil)
    function etiquetar(t) {
      if (t.classList.contains('no-stack') || t.dataset.hdLabeled) return;
      var ths = t.querySelectorAll('thead th'); if (!ths.length) return;
      var labels = Array.prototype.map.call(ths, function (th) { return th.textContent.trim(); });
      t.querySelectorAll('tbody tr').forEach(function (tr) {
        Array.prototype.forEach.call(tr.children, function (td, i) { if (td.tagName === 'TD' && !td.hasAttribute('data-label')) td.setAttribute('data-label', labels[i] || ''); });
      });
      t.classList.add('hd-stack'); t.dataset.hdLabeled = '1';
    }
    var tablas = function () { document.querySelectorAll('table.hd-table').forEach(etiquetar); };
    tablas();
    // Tablas que se rellenan después por AJAX
    if (window.MutationObserver) new MutationObserver(function () { tablas(); }).observe(document.body, {childList: true, subtree: true});
  });
})();
