<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php"); exit();
}
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

$usuario_activo = $_SESSION['usser'];
$rol_id         = (int)$_SESSION['id_rol'];
$inicial        = strtoupper(substr($usuario_activo, 0, 1));
$rol_nombre     = match($rol_id) { 3=>'Administrador', 2=>'Técnico TI', 1=>'Usuario', default=>'—' };
// El nombre visible sale de la tabla roles (se puede renombrar desde Admin → Roles)
try {
    $q_rn = $conexion->prepare("SELECT nombre FROM roles WHERE id_rol = ?");
    $q_rn->bind_param("i", $rol_id); $q_rn->execute();
    if ($f_rn = $q_rn->get_result()->fetch_assoc()) $rol_nombre = $f_rn['nombre'];
    $q_rn->close();
} catch (Throwable $e) { /* se queda el nombre por defecto */ }
$pagina_actual  = basename($_SERVER['PHP_SELF']);
if (empty($_SESSION['csrf_notif'])) $_SESSION['csrf_notif'] = bin2hex(random_bytes(16));

require_once __DIR__ . '/../includes/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
$csrf_token_pagina = csrf_token();

// ---- CIERRE POR INACTIVIDAD (TIEMPO_SESION_MINUTOS en constants.php) ----
// Solo cuenta la actividad real del usuario (cargar páginas); el refresco automático de la campana no la prolonga.
if (isset($_SESSION['ultimo_acceso']) && (time() - (int)$_SESSION['ultimo_acceso']) > TIEMPO_SESION_MINUTOS * 60) {
    $_SESSION = [];
    session_destroy();
    session_start();
    $_SESSION['alerta_flash'] = ['tipo' => 'warning', 'mensaje' => 'Tu sesión se cerró por inactividad. Inicia sesión de nuevo.'];
    header('Location: ' . URL_BASE . 'index.php');
    exit();
}
$_SESSION['ultimo_acceso'] = time();

// ---- GUARDA DE ROL POR CARPETA ----
// Cada carpeta del sistema pertenece a un rol. Si alguien entra a una carpeta que no es
// la suya (por ejemplo un técnico escribiendo admin/dashboard.php en la URL), se le
// devuelve a su propio panel. Cubre todas las páginas que incluyen este header.
$rol_de_carpeta   = ['admin' => ROL_ADMIN, 'tecnico' => ROL_TECNICO, 'usuario' => ROL_USUARIO];
$carpeta_de_rol   = [ROL_ADMIN => 'admin', ROL_TECNICO => 'tecnico', ROL_USUARIO => 'usuario'];
$ruta_actual      = str_replace('\\', '/', $_SERVER['PHP_SELF']);

foreach ($rol_de_carpeta as $carpeta => $rol_requerido) {
    if (strpos($ruta_actual, '/' . $carpeta . '/') !== false && $rol_id !== $rol_requerido) {
        $destino = isset($carpeta_de_rol[$rol_id]) ? $carpeta_de_rol[$rol_id] . '/dashboard.php' : 'index.php';
        header('Location: ' . URL_BASE . $destino);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard2.css">
</head>
<body>

<!-- ===== TOPBAR ===== -->
<header class="hd-topbar">
    <a href="dashboard.php" class="hd-topbar-brand">
        <i class="fas fa-headset"></i>
        Help<span>Desk</span> <small>Unicon</small>
    </a>

    <div class="hd-topbar-search" style="position:relative;">
        <i class="fas fa-search"></i>
        <input type="search" id="hdBuscar" autocomplete="off" maxlength="60"
               placeholder="<?= $rol_id === 3 ? 'Buscar tickets, usuarios, equipos...' : ($rol_id === 2 ? 'Buscar tickets, equipos...' : 'Buscar en mis tickets...') ?>">
        <div class="hd-resultados" id="hdResultados"></div>
    </div>

    <div class="hd-topbar-right">

        <div class="hd-notif-wrap">
            <button type="button" class="hd-topbar-icon" id="hdBell" title="Notificaciones" style="background:none;border:0;position:relative;">
                <i class="fas fa-bell"></i>
                <span class="hd-notif-badge" id="hdNotifBadge">0</span>
            </button>
            <div class="hd-notif-panel" id="hdNotifPanel">
                <div class="hd-notif-head"><span>Notificaciones</span><button type="button" id="hdNotifTodas">Marcar todas como leídas</button></div>
                <div class="hd-notif-lista" id="hdNotifLista"><div class="hd-notif-vacio">Cargando…</div></div>
            </div>
        </div>

        <?php if ($rol_id >= 2): ?>
        <a href="<?= $rol_id === ROL_ADMIN ? 'tickets.php' : 'tickets_asignados.php' ?>" class="hd-topbar-icon" title="Tickets">
            <i class="fas fa-ticket-alt"></i>
        </a>
        <?php endif; ?>

        <?php if ($rol_id == 3): ?>
        <a href="procesos/usuarios.php" class="hd-topbar-icon" title="Usuarios">
            <i class="fas fa-users"></i>
        </a>
        <?php endif; ?>

        <div class="hd-topbar-sep"></div>

        <div style="position:relative;">
            <button class="hd-topbar-user" onclick="toggleUserMenu()" id="userBtn">
                <div class="hd-avatar"><?= $inicial ?></div>
                <div class="hd-topbar-user-info">
                    <span class="hd-topbar-user-name"><?= htmlspecialchars($usuario_activo) ?></span>
                    <span class="hd-topbar-user-role"><?= $rol_nombre ?></span>
                </div>
                <i class="fas fa-chevron-down" style="color:rgba(255,255,255,0.4);font-size:0.6rem;margin-left:4px;"></i>
            </button>
            <div class="hd-dropdown-menu" id="userMenu">
                <a href="perfil.php" class="hd-dropdown-item"><i class="fas fa-user-edit" style="color:var(--muted);width:14px;"></i> Mi Perfil</a>
                <a href="cambiar_clave.php" class="hd-dropdown-item"><i class="fas fa-key" style="color:var(--muted);width:14px;"></i> Contraseña</a>
                <div class="hd-dropdown-sep"></div>
                <a href="../auth/logout.php" class="hd-dropdown-item danger"><i class="fas fa-power-off" style="width:14px;"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>
</header>
<!-- FIN TOPBAR -->
<?= csrf_script() ?>
<style>
    .hd-resultados { position:absolute; top:calc(100% + 8px); left:0; right:0; min-width:340px; background:#fff; border:1px solid var(--borde);
        border-radius:10px; box-shadow:0 12px 32px rgba(15,23,42,.22); max-height:70vh; overflow:auto; display:none; z-index:3000; }
    .hd-resultados.show { display:block; }
    .hd-res-grupo { padding:9px 14px 4px; font-size:.64rem; font-weight:700; letter-spacing:1.2px; text-transform:uppercase; color:var(--muted); }
    .hd-res-item { display:block; padding:8px 14px; text-decoration:none; color:var(--texto); font-size:.8rem; border-top:1px solid #f1f5f9; }
    .hd-res-item:hover { background:#f8fafc; }
    .hd-res-item small { display:block; color:var(--muted); font-size:.7rem; }
    .hd-res-vacio { padding:16px; font-size:.78rem; color:var(--muted); }
    .hd-notif-wrap { position:relative; }
    .hd-notif-badge { position:absolute; top:-5px; right:-5px; min-width:17px; height:17px; padding:0 4px; border-radius:9px; background:#dc2626; color:#fff;
        font-size:.62rem; font-weight:700; display:none; align-items:center; justify-content:center; line-height:1; font-family:'Poppins',sans-serif; }
    .hd-notif-badge.show { display:flex; }
    .hd-notif-panel { position:absolute; top:calc(100% + 12px); right:-70px; width:370px; max-width:92vw; background:#fff; border:1px solid var(--borde);
        border-radius:10px; box-shadow:0 12px 32px rgba(15,23,42,.22); display:none; z-index:3000; overflow:hidden; }
    .hd-notif-panel.show { display:block; }
    .hd-notif-head { display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid var(--borde); font-size:.8rem; font-weight:700; color:var(--azul); }
    .hd-notif-head button { border:0; background:none; color:var(--naranja); font-size:.7rem; font-weight:600; cursor:pointer; }
    .hd-notif-lista { max-height:60vh; overflow:auto; }
    .hd-notif-item { display:flex; gap:10px; padding:10px 14px; border-bottom:1px solid #f1f5f9; cursor:pointer; font-size:.78rem; color:var(--texto); line-height:1.35; }
    .hd-notif-item.nueva { background:rgba(232,119,34,.07); }
    .hd-notif-item:hover { background:#f8fafc; }
    .hd-notif-item .pt { width:8px; height:8px; border-radius:50%; margin-top:5px; flex-shrink:0; }
    .hd-notif-item.nueva .pt { background:var(--naranja); }
    .hd-notif-item time { display:block; color:var(--muted); font-size:.68rem; margin-top:2px; }
    .hd-notif-vacio { padding:24px; text-align:center; color:var(--muted); font-size:.8rem; }
</style>
<script>
(function () {
    const BASE = <?= json_encode(URL_BASE) ?>;
    const CSRF = <?= json_encode($_SESSION['csrf_notif']) ?>;
    const $id = (x) => document.getElementById(x);

    // ---------------- Notificaciones ----------------
    const bell = $id('hdBell'), panel = $id('hdNotifPanel'), badge = $id('hdNotifBadge'), lista = $id('hdNotifLista');
    function pintar(d) {
        const n = d.no_leidas || 0;
        badge.textContent = n > 99 ? '99+' : n;
        badge.classList.toggle('show', n > 0);
        lista.textContent = '';
        if (!d.items || !d.items.length) {
            const v = document.createElement('div'); v.className = 'hd-notif-vacio'; v.textContent = 'No tienes notificaciones.';
            lista.appendChild(v); return;
        }
        d.items.forEach(function (it) {
            const el = document.createElement('div'); el.className = 'hd-notif-item' + (it.leida ? '' : ' nueva');
            const pt = document.createElement('span'); pt.className = 'pt';
            const cuerpo = document.createElement('div');
            const tx = document.createElement('div'); tx.textContent = it.mensaje;
            const tm = document.createElement('time'); tm.textContent = it.cuando;
            cuerpo.append(tx, tm); el.append(pt, cuerpo);
            el.addEventListener('click', async function () {
                if (!it.leida) await accion('leer', it.id);
                if (it.url) window.location = it.url; else cargar();
            });
            lista.appendChild(el);
        });
    }
    async function cargar() {
        try {
            const r = await fetch(BASE + 'procesos/notificaciones.php?accion=lista', { credentials: 'same-origin' });
            if (r.ok) pintar(await r.json());
        } catch (e) {}
    }
    async function accion(a, id) {
        const body = new URLSearchParams({ accion: a }); if (id) body.set('id', id);
        try { await fetch(BASE + 'procesos/notificaciones.php', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': CSRF }, body: body }); } catch (e) {}
    }
    bell.addEventListener('click', function (e) {
        e.stopPropagation();
        panel.classList.toggle('show'); $id('hdResultados').classList.remove('show');
        if (panel.classList.contains('show')) cargar();
    });
    $id('hdNotifTodas').addEventListener('click', async function () { await accion('leer_todas'); cargar(); });
    document.addEventListener('click', function (e) { if (!panel.contains(e.target)) panel.classList.remove('show'); });
    cargar();
    setInterval(function () { if (!document.hidden) cargar(); }, 45000);

    // ---------------- Buscador ----------------
    const inp = $id('hdBuscar'), box = $id('hdResultados'); let t = null, ultimo = '';
    function pintarRes(d, q) {
        box.textContent = '';
        if (!d.grupos || !d.grupos.length) {
            const v = document.createElement('div'); v.className = 'hd-res-vacio'; v.textContent = 'Sin resultados para «' + q + '».'; box.appendChild(v);
        } else {
            d.grupos.forEach(function (g) {
                const h = document.createElement('div'); h.className = 'hd-res-grupo'; h.textContent = g.titulo; box.appendChild(h);
                g.items.forEach(function (it) {
                    const a = document.createElement('a'); a.className = 'hd-res-item'; a.href = it.u;
                    const b = document.createElement('div'); b.textContent = it.t;
                    const s = document.createElement('small'); s.textContent = it.s;
                    a.append(b, s); box.appendChild(a);
                });
            });
        }
        box.classList.add('show');
    }
    inp.addEventListener('input', function () {
        clearTimeout(t);
        const q = inp.value.trim();
        if (q.length < 2) { box.classList.remove('show'); return; }
        t = setTimeout(async function () {
            ultimo = q;
            try {
                const r = await fetch(BASE + 'procesos/buscar.php?q=' + encodeURIComponent(q), { credentials: 'same-origin' });
                if (r.ok) { const d = await r.json(); if (q === ultimo) pintarRes(d, q); }
            } catch (e) {}
        }, 250);
    });
    inp.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { box.classList.remove('show'); inp.blur(); }
        if (e.key === 'Enter') { const a = box.querySelector('a.hd-res-item'); if (a) { e.preventDefault(); window.location = a.href; } }
    });
    inp.addEventListener('focus', function () { if (box.childNodes.length) box.classList.add('show'); panel.classList.remove('show'); });
    document.addEventListener('click', function (e) { if (!box.contains(e.target) && e.target !== inp) box.classList.remove('show'); });
})();
</script>