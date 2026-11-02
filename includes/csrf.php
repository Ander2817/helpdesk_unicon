<?php
// includes/csrf.php
// Protección contra CSRF (que otro sitio haga que tu navegador ejecute acciones en el sistema).
// - header2.php pone el token en la sesión y un pequeño script lo añade solo a TODO envío POST
//   (jQuery/AJAX, fetch y formularios normales).
// - Cada proceso que modifica datos llama csrf_verificar() justo después de session_start().
//   (herramientas/aplicar_csrf.php lo inserta automáticamente en todos los procesos.)

function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function csrf_verificar(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
    $esperado = (string)($_SESSION['csrf_token'] ?? '');
    $recibido = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if ($esperado !== '' && is_string($recibido) && hash_equals($esperado, $recibido)) return;

    http_response_code(403);
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>La sesión caducó o la petición no es válida. Recarga la página e inténtalo de nuevo.</div>";
    exit();
}

/** <script> que añade el token a TODO envío POST del mismo sitio (AJAX/jQuery, fetch y formularios). Imprímelo en el <head> o al inicio del <body>. */
function csrf_script(): string {
    $T = json_encode(csrf_token());
    $js = <<<JS
<script>
// Anti-CSRF: añade el token a TODO envío POST del mismo sitio
(function () {
    const T = $T;
    const mismo = function (u) { try { return new URL(u || location.href, location.href).origin === location.origin; } catch (e) { return true; } };
    const abrir = XMLHttpRequest.prototype.open, enviar = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (m, u) { this._hdM = String(m || 'GET').toUpperCase(); this._hdU = u; return abrir.apply(this, arguments); };
    XMLHttpRequest.prototype.send = function () {
        if (this._hdM === 'POST' && mismo(this._hdU)) { try { this.setRequestHeader('X-CSRF-Token', T); } catch (e) {} }
        return enviar.apply(this, arguments);
    };
    if (window.fetch) {
        const f = window.fetch;
        window.fetch = function (i, init) {
            init = init || {};
            const m = String(init.method || (i && i.method) || 'GET').toUpperCase();
            const u = typeof i === 'string' ? i : ((i && i.url) || '');
            if (m === 'POST' && mismo(u)) {
                const h = new Headers(init.headers || (i && i.headers) || {});
                if (!h.has('X-CSRF-Token')) h.set('X-CSRF-Token', T);
                init.headers = h;
            }
            return f.call(this, i, init);
        };
    }
    document.addEventListener('submit', function (e) {
        const fm = e.target;
        if (fm && fm.tagName === 'FORM' && String(fm.method).toLowerCase() === 'post' && mismo(fm.action) && !fm.querySelector('input[name=csrf_token]')) {
            const i = document.createElement('input'); i.type = 'hidden'; i.name = 'csrf_token'; i.value = T; fm.appendChild(i);
        }
    }, true);
})();
</script>
JS;
    return $js . sesion_inactividad_script();
}

/**
 * Cierra la sesión sola tras TIEMPO_SESION_MINUTOS sin que el usuario haga nada (mueva el mouse, escriba, haga clic o scroll),
 * aunque no pulse nada. Un minuto antes avisa con un cartel y botón "Seguir conectado".
 * Mientras hay actividad, avisa al servidor (procesos/keepalive.php) cada 2 minutos para que su reloj no caduque.
 * Solo se imprime si hay sesión iniciada. Se incluye automáticamente dentro de csrf_script().
 */
function sesion_inactividad_script(): string {
    require_once __DIR__ . '/constants.php';
    if (empty($_SESSION['usser'])) return '';
    $lim   = (int)TIEMPO_SESION_MINUTOS * 60;
    $aviso = min(60, max(10, intdiv($lim, 4)));
    $base  = json_encode(URL_BASE);
    return <<<JS
<script>
(function () {
    const LIM = $lim * 1000, AVISO = $aviso * 1000, BASE = $base, CLAVE = 'hd_actividad';
    let ultima = Date.now(), pingEn = Date.now(), hayActividad = false, cartel = null, salida = false;
    const guardar = function (t) { try { localStorage.setItem(CLAVE, String(t)); } catch (e) {} };
    const leer = function () { try { return parseInt(localStorage.getItem(CLAVE) || '0', 10) || 0; } catch (e) { return 0; } };
    let ultimoGuardado = 0;
    function actividad() {
        ultima = Date.now(); hayActividad = true;
        if (ultima - ultimoGuardado > 5000) { ultimoGuardado = ultima; guardar(ultima); }   // otras pestañas también cuentan
        if (cartel) ocultar();
    }
    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (ev) {
        document.addEventListener(ev, actividad, { passive: true, capture: true });
    });
    function salir() {
        if (salida) return; salida = true;
        window.location.href = BASE + 'auth/logout.php?motivo=inactividad';
    }
    function keepalive() {
        pingEn = Date.now(); hayActividad = false;
        fetch(BASE + 'procesos/keepalive.php', { method: 'POST', credentials: 'same-origin' })
            .then(function (r) { if (r.status === 401) salir(); })
            .catch(function () {});
    }
    function mostrar(seg) {
        if (!cartel) {
            cartel = document.createElement('div');
            cartel.style.cssText = 'position:fixed;right:20px;bottom:20px;z-index:99999;max-width:340px;background:#fff7ed;border:1px solid #fb923c;border-left:5px solid #f97316;border-radius:10px;padding:14px 16px;box-shadow:0 8px 24px rgba(0,0,0,.18);font:14px/1.4 system-ui,sans-serif;color:#7c2d12';
            cartel.innerHTML = '<div style="font-weight:700;margin-bottom:4px">¿Sigues ahí?</div><div>Tu sesión se cerrará por inactividad en <b id="hd-inact-seg"></b> s.</div>'
                + '<button type="button" style="margin-top:10px;background:#f97316;color:#fff;border:0;border-radius:6px;padding:6px 12px;cursor:pointer;font-weight:600">Seguir conectado</button>';
            cartel.querySelector('button').addEventListener('click', function () { actividad(); keepalive(); });
            document.body.appendChild(cartel);
        }
        cartel.querySelector('#hd-inact-seg').textContent = seg;
    }
    function ocultar() { if (cartel) { cartel.remove(); cartel = null; } }
    setInterval(function () {
        const otra = leer(); if (otra > ultima) ultima = otra;            // actividad en otra pestaña
        const inactivo = Date.now() - ultima;
        if (inactivo >= LIM) { salir(); return; }
        if (inactivo >= LIM - AVISO) { if (document.body) mostrar(Math.max(0, Math.ceil((LIM - inactivo) / 1000))); return; }
        if (cartel) ocultar();
        if (hayActividad && Date.now() - pingEn > 120000) keepalive();
    }, 1000);
    guardar(ultima);
})();
</script>
JS;
}
