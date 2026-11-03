<?php
// admin/estado_sistema.php — Panel de salud del sistema (solo admin, solo lectura salvo el botón "Revisar ahora").
// Muestra lo que antes solo se veía en phpMyAdmin: sistema_estado, sla_alertas, login_intentos, ia_consultas y la salud de las tablas.
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) { header("Location: ../index.php"); exit(); }
require_once '../includes/constants.php';
require_once '../includes/functions.php';
require_once '../includes/duracion.php';
include '../config/conexion.php';
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

/** Ejecuta una consulta y devuelve filas; si la tabla no existe devuelve null (la sección se muestra como "no instalada"). */
function es_q(mysqli $c, string $sql): ?array { try { return $c->query($sql)->fetch_all(MYSQLI_ASSOC); } catch (Throwable $e) { return null; } }

// ---- 1) revisiones automáticas (sistema_estado)
$estado = es_q($conexion, "SELECT clave, valor, actualizado FROM sistema_estado WHERE clave NOT LIKE 'mant_aviso_%' ORDER BY clave");
$n_avisos_mant = es_q($conexion, "SELECT COUNT(*) n FROM sistema_estado WHERE clave LIKE 'mant_aviso_%'")[0]['n'] ?? 0;
$nombres_clave = ['sla_revision' => 'Revisión de SLA (tickets por vencer / vencidos)', 'tareas_revision' => 'Autocierre de resueltos y avisos de mantenimiento'];
// ---- 2) SLA
$sla_hoy = es_q($conexion, "SELECT nivel, COUNT(*) n FROM sla_alertas WHERE fecha >= CURDATE() GROUP BY nivel");
$sla_ult = es_q($conexion, "SELECT a.nivel, a.fecha, a.id_ticket, t.codigo_ticket, t.asunto FROM sla_alertas a JOIN tickets t ON t.id_tickets = a.id_ticket ORDER BY a.fecha DESC LIMIT 8");
$sla_total = es_q($conexion, "SELECT nivel, COUNT(*) n FROM sla_alertas GROUP BY nivel");
// ---- 3) accesos
$ventana = defined('LOGIN_VENTANA_MIN') ? LOGIN_VENTANA_MIN : 15;
if (is_file(__DIR__ . '/../includes/login_guard.php')) require_once __DIR__ . '/../includes/login_guard.php';
$ventana = defined('LOGIN_VENTANA_MIN') ? LOGIN_VENTANA_MIN : 15; $bloq = defined('LOGIN_BLOQUEO_SEGUNDOS') ? LOGIN_BLOQUEO_SEGUNDOS : 60;
$fallos24 = es_q($conexion, "SELECT COUNT(*) n, COUNT(DISTINCT ip) ips, COUNT(DISTINCT usuario_login) usuarios FROM login_intentos WHERE fecha > (NOW() - INTERVAL 24 HOUR)");
$top_fallos = es_q($conexion, "SELECT usuario_login, ip, COUNT(*) n, MAX(fecha) ultimo FROM login_intentos WHERE fecha > (NOW() - INTERVAL 24 HOUR) GROUP BY usuario_login, ip ORDER BY n DESC, ultimo DESC LIMIT 6");
$bloqueados = es_q($conexion, "SELECT usuario_login, ip, COUNT(*) n, TIMESTAMPDIFF(SECOND, NOW(), MAX(fecha) + INTERVAL $bloq SECOND) resta
                            FROM login_intentos WHERE fecha > (NOW() - INTERVAL $ventana MINUTE) GROUP BY usuario_login, ip
                            HAVING n >= " . INTENTOS_FALLIDOS_MAX . " AND resta > 0");
// ---- 4) IA
$ia_cfg = is_file(__DIR__ . '/../includes/ia.php') ? (function () { require_once __DIR__ . '/../includes/ia.php'; return ia_config(); })() : null;
$ia_ok = $ia_cfg && function_exists('ia_disponible') && ia_disponible();
$ia_res = es_q($conexion, "SELECT SUM(fecha >= CURDATE()) hoy, SUM(fecha >= NOW() - INTERVAL 7 DAY) sem, SUM(fuente='ia' AND fecha >= NOW() - INTERVAL 7 DAY) con_ia,
                               SUM(fuente='local' AND fecha >= NOW() - INTERVAL 7 DAY) locales, SUM(ok=0 AND fecha >= NOW() - INTERVAL 7 DAY) fallos
                        FROM ia_consultas");
if ($fallos24 !== null && !$fallos24) $fallos24 = [['n' => 0, 'ips' => 0, 'usuarios' => 0]];
if ($ia_res !== null && !$ia_res) $ia_res = [['hoy' => 0, 'sem' => 0, 'con_ia' => 0, 'locales' => 0, 'fallos' => 0]];
$ia_roles = es_q($conexion, "SELECT id_rol, COUNT(*) n FROM ia_consultas WHERE fecha >= NOW() - INTERVAL 7 DAY GROUP BY id_rol");
$ia_ult = es_q($conexion, "SELECT c.accion, c.fuente, c.ok, c.fecha, c.pregunta, CONCAT(u.nombres,' ',u.apellidos) AS usuario FROM ia_consultas c LEFT JOIN usuarios u ON u.id_usuario = c.id_usuario ORDER BY c.fecha DESC LIMIT 6");
// ---- 5) tiempos de resolución (promedio y mediana)
function tiempos(mysqli $c, string $filtro): array {
    $f = es_q($c, "SELECT TIMESTAMPDIFF(MINUTE, fecha_creacion, fecha_cierre) / 60 AS h FROM tickets WHERE fecha_cierre IS NOT NULL $filtro LIMIT 5000") ?? [];
    $v = array_map(fn($r) => (float)$r['h'], $f);
    return ['n' => count($v), 'prom' => $v ? array_sum($v) / count($v) : null, 'med' => hd_mediana($v)];
}
$t30 = tiempos($conexion, "AND fecha_cierre > (NOW() - INTERVAL 30 DAY)"); $tAll = tiempos($conexion, '');
// ---- 6) salud de tablas
$esperadas = ['adjuntos','bitacora','categoria_incidencias','comentarios','departamentos','encuestas_ticket','estados_ticket','historial_ticket','ia_consultas','inventario_tecnologico','kb_articulos','login_intentos','mantenimientos_equipo','notificaciones','prioridades','recuperaciones','rol_permisos','roles','sistema_estado','sla_alertas','tickets','usuarios'];
$ex = []; foreach ((es_q($conexion, "SHOW TABLES") ?? []) as $fila) $ex[] = strtolower(array_values($fila)[0]);
$faltan = array_values(array_diff($esperadas, $ex));

function semaforo(?string $fecha, int $okMin): array {
    if (!$fecha || $fecha < '2001-01-01') return ['#94a3b8', 'Nunca ejecutada'];
    $min = (time() - strtotime($fecha)) / 60;
    return $min <= $okMin ? ['#16a34a', 'Al día'] : ($min <= $okMin * 4 ? ['#d97706', 'Atrasada'] : ['#dc2626', 'Detenida']);
}
$rolN = [1 => 'Usuarios', 2 => 'Técnicos', 3 => 'Admins'];

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>
<style>
    .es-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap:16px; margin-bottom:16px; }
    .es-kpi { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px; }
    .es-kpi > div { background:#f8fafc; border:1px solid var(--borde); border-radius:8px; padding:8px 12px; min-width:92px; }
    .es-kpi b { display:block; font-size:1.2rem; } .es-kpi span { font-size:.7rem; color:var(--muted); }
    .es-dot { display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:6px; }
    .es-mini td, .es-mini th { font-size:.76rem; padding:6px 10px; }
    .es-warn { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; border-radius:8px; padding:10px 14px; font-size:.8rem; margin-bottom:14px; }
    .es-nota { font-size:.72rem; color:var(--muted); margin-top:8px; }
</style>
<main class="hd-main">
    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-heart-pulse" style="color:var(--naranja);margin-right:6px;"></i> Estado del sistema</div>
            <div class="hd-page-sub">Revisiones automáticas, alertas de SLA, seguridad de accesos, uso de la IA y salud de la base de datos.</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button class="hd-btn hd-btn-outline" type="button" id="btn-resumen-ia"><i class="fas fa-wand-magic-sparkles"></i> Resumen semanal con IA</button>
            <button class="hd-btn hd-btn-primary" type="button" id="btn-forzar"><i class="fas fa-rotate"></i> Revisar ahora</button>
        </div>
    </div>

    <?php if ($faltan): ?>
    <div class="es-warn"><b>Faltan tablas en la base de datos:</b> <?= $h(implode(', ', $faltan)) ?>. Ejecuta las migraciones de los módulos correspondientes.</div>
    <?php endif; ?>

    <div class="es-grid">
        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-clock-rotate-left"></i> Revisiones automáticas</h5></div>
            <div class="hd-card-body" style="padding:0;">
            <?php if ($estado === null): ?><div style="padding:14px;" class="es-nota">La tabla <code>sistema_estado</code> no existe todavía.</div>
            <?php else: ?>
            <table class="hd-table es-mini"><thead><tr><th>Tarea</th><th>Última vez</th><th>Estado</th></tr></thead><tbody>
            <?php foreach ($nombres_clave as $k => $nom):
                $fila = null; foreach ($estado as $e) if ($e['clave'] === $k) $fila = $e;
                [$col, $txt] = semaforo($fila['actualizado'] ?? null, 15); ?>
                <tr><td><?= $h($nom) ?><?php if ($k === 'tareas_revision' && $fila && $fila['valor']): ?><div class="es-nota" style="margin:2px 0 0;"><?= $h(str_replace(['autocerrados', 'avisos_mantenimiento', ';', '='], ['autocerrados', 'avisos de mantenimiento', ' · ', ': '], $fila['valor'])) ?></div><?php endif; ?></td>
                    <td><?= $h(hd_hace($fila['actualizado'] ?? null)) ?></td><td><span class="es-dot" style="background:<?= $col ?>"></span><?= $h($txt) ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
            <div class="es-nota" style="padding:0 14px 12px;">Se ejecutan solas cada 10–15 min mientras alguien tenga el sistema abierto (la campana las dispara). Avisos de mantenimiento ya emitidos: <b><?= (int)$n_avisos_mant ?></b>.</div>
            <?php endif; ?></div></div>

        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-triangle-exclamation"></i> Alertas de SLA</h5></div>
            <div class="hd-card-body">
            <?php if ($sla_hoy === null): ?><div class="es-nota">La tabla <code>sla_alertas</code> no existe todavía.</div><?php else:
                $hoy = ['riesgo' => 0, 'vencido' => 0]; foreach ($sla_hoy as $r) $hoy[$r['nivel']] = (int)$r['n'];
                $tot = ['riesgo' => 0, 'vencido' => 0]; foreach ($sla_total ?? [] as $r) $tot[$r['nivel']] = (int)$r['n']; ?>
                <div class="es-kpi"><div><b style="color:#d97706;"><?= $hoy['riesgo'] ?></b><span>en riesgo hoy</span></div><div><b style="color:#dc2626;"><?= $hoy['vencido'] ?></b><span>vencidos hoy</span></div>
                    <div><b><?= $tot['riesgo'] + $tot['vencido'] ?></b><span>avisos en total</span></div></div>
                <table class="hd-table es-mini"><tbody>
                <?php foreach ($sla_ult as $a): ?>
                    <tr><td><a href="ver_ticket.php?id=<?= (int)$a['id_ticket'] ?>"><?= $h($a['codigo_ticket']) ?></a> <span style="color:var(--muted);"><?= $h(mb_substr($a['asunto'], 0, 34)) ?></span></td>
                        <td><span class="es-dot" style="background:<?= $a['nivel'] === 'vencido' ? '#dc2626' : '#d97706' ?>"></span><?= $a['nivel'] === 'vencido' ? 'Vencido' : 'En riesgo' ?></td><td><?= $h(hd_hace($a['fecha'])) ?></td></tr>
                <?php endforeach; if (!$sla_ult): ?><tr><td style="color:var(--muted);">Aún no se ha emitido ninguna alerta.</td></tr><?php endif; ?>
                </tbody></table>
            <?php endif; ?></div></div>

        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-shield-halved"></i> Seguridad de accesos</h5></div>
            <div class="hd-card-body">
            <?php if ($fallos24 === null): ?><div class="es-nota">La tabla <code>login_intentos</code> no existe todavía.</div><?php else: ?>
                <div class="es-kpi"><div><b><?= (int)$fallos24[0]['n'] ?></b><span>fallos 24 h</span></div><div><b><?= (int)$fallos24[0]['usuarios'] ?></b><span>usuarios distintos</span></div>
                    <div><b><?= (int)$fallos24[0]['ips'] ?></b><span>IP distintas</span></div><div><b style="color:<?= $bloqueados ? '#dc2626' : '#16a34a' ?>;"><?= count($bloqueados ?? []) ?></b><span>bloqueados ahora</span></div></div>
                <table class="hd-table es-mini"><thead><tr><th>Usuario intentado</th><th>IP</th><th>Fallos</th><th>Último</th></tr></thead><tbody>
                <?php foreach ($top_fallos as $f): ?><tr><td><?= $h($f['usuario_login']) ?></td><td><?= $h($f['ip']) ?></td><td><?= (int)$f['n'] ?></td><td><?= $h(hd_hace($f['ultimo'])) ?></td></tr>
                <?php endforeach; if (!$top_fallos): ?><tr><td colspan="4" style="color:var(--muted);">Sin intentos fallidos en las últimas 24 horas.</td></tr><?php endif; ?></tbody></table>
                <div class="es-nota">Bloqueo: <?= INTENTOS_FALLIDOS_MAX ?> fallos del mismo usuario e IP → <?= (int)$bloq ?> s de espera. Los registros se borran solos a los 2 días.</div>
            <?php endif; ?></div></div>

        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-wand-magic-sparkles"></i> Asistente de IA</h5></div>
            <div class="hd-card-body">
                <div style="margin-bottom:8px;font-size:.8rem;"><span class="es-dot" style="background:<?= $ia_ok ? '#16a34a' : '#dc2626' ?>"></span>
                    <?= $ia_ok ? 'Configurada: ' . $h($ia_cfg['proveedor']) . ' · ' . $h($ia_cfg['modelo'] ?: 'modelo por defecto') : 'No configurada (funciona en modo local). Revisa <code>config/ia.php</code>.' ?></div>
            <?php if ($ia_res === null): ?><div class="es-nota">La tabla <code>ia_consultas</code> no existe todavía.</div><?php else: $r = $ia_res[0]; ?>
                <div class="es-kpi"><div><b><?= (int)$r['hoy'] ?></b><span>consultas hoy</span></div><div><b><?= (int)$r['sem'] ?></b><span>últimos 7 días</span></div>
                    <div><b><?= (int)$r['con_ia'] ?></b><span>respondió la IA</span></div><div><b><?= (int)$r['locales'] ?></b><span>modo local</span></div>
                    <div><b style="color:<?= (int)$r['fallos'] ? '#dc2626' : 'inherit' ?>;"><?= (int)$r['fallos'] ?></b><span>con error</span></div></div>
                <div class="es-nota" style="margin:0 0 8px;">Por rol (7 días): <?php $p = []; foreach ($ia_roles as $x) $p[] = ($rolN[(int)$x['id_rol']] ?? 'Otro') . ' ' . (int)$x['n']; echo $h($p ? implode(' · ', $p) : 'sin consultas'); ?></div>
                <table class="hd-table es-mini"><tbody>
                <?php foreach ($ia_ult as $c): ?><tr><td><?= $h($c['usuario'] ?? '—') ?><div class="es-nota" style="margin:0;"><?= $h($c['accion']) ?> · <?= $h(mb_substr((string)$c['pregunta'], 0, 46)) ?></div></td>
                    <td><span class="es-dot" style="background:<?= !$c['ok'] ? '#dc2626' : ($c['fuente'] === 'ia' ? '#16a34a' : '#94a3b8') ?>"></span><?= !$c['ok'] ? 'Error' : ($c['fuente'] === 'ia' ? 'IA' : 'Local') ?></td><td><?= $h(hd_hace($c['fecha'])) ?></td></tr>
                <?php endforeach; if (!$ia_ult): ?><tr><td style="color:var(--muted);">Todavía no hay consultas.</td></tr><?php endif; ?></tbody></table>
            <?php endif; ?></div></div>

        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-stopwatch"></i> Tiempo de resolución</h5></div>
            <div class="hd-card-body">
                <table class="hd-table es-mini"><thead><tr><th></th><th>Tickets</th><th>Promedio</th><th>Mediana</th></tr></thead><tbody>
                    <tr><td>Últimos 30 días</td><td><?= $t30['n'] ?></td><td><?= $h(hd_duracion($t30['prom'])) ?></td><td><b><?= $h(hd_duracion($t30['med'])) ?></b></td></tr>
                    <tr><td>Histórico</td><td><?= $tAll['n'] ?></td><td><?= $h(hd_duracion($tAll['prom'])) ?></td><td><b><?= $h(hd_duracion($tAll['med'])) ?></b></td></tr></tbody></table>
                <div class="es-nota">La <b>mediana</b> es el tiempo del ticket «del medio»: no se distorsiona por unos pocos tickets que quedaron abiertos mucho tiempo.</div></div></div>

        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-file-export"></i> Exportar datos (CSV)</h5></div>
            <div class="hd-card-body">
                <div class="es-nota" style="margin:0 0 10px;">Se abren en Excel. Filtros opcionales en la dirección, por ejemplo <code>?tipo=tickets&amp;estado=3&amp;desde=2026-10-01</code>.</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a class="hd-btn hd-btn-outline" href="../procesos/exportar_csv.php?tipo=tickets"><i class="fas fa-ticket"></i> Tickets</a>
                    <a class="hd-btn hd-btn-outline" href="../procesos/exportar_csv.php?tipo=inventario"><i class="fas fa-server"></i> Inventario</a>
                    <a class="hd-btn hd-btn-outline" href="../procesos/exportar_csv.php?tipo=bitacora"><i class="fas fa-list-check"></i> Bitácora</a>
                    <a class="hd-btn hd-btn-outline" href="../procesos/exportar_csv.php?tipo=satisfaccion"><i class="fas fa-star"></i> Encuestas</a>
                </div></div></div>
    </div>
</main>
<script>
document.getElementById('btn-forzar').onclick = function () {
    const b = this; b.disabled = true; b.innerHTML = '<i class="fas fa-rotate fa-spin"></i> Revisando…';
    fetch('../procesos/forzar_revision.php', {method: 'POST', credentials: 'same-origin'}).then(r => r.json()).then(j => { if (!j.ok) alert(j.msg || 'Error'); location.reload(); })
        .catch(() => { alert('No se pudo ejecutar la revisión.'); b.disabled = false; });
};
document.getElementById('btn-resumen-ia').onclick = function () {
    if (window.iaAsistente) window.iaAsistente.preguntar('Resumen de la semana'); else alert('El asistente de IA no está instalado en esta página.');
};
</script>
<?php include_once '../includes/footer2.php'; ?>
