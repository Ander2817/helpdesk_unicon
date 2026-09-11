<?php
session_start();
require_once '../includes/constants.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

$colores_prioridad = [1=>'gris', 2=>'amarillo', 3=>'naranja', 4=>'rojo'];
$colores_estado    = [1=>'azul', 2=>'naranja', 3=>'gris', 4=>'verde', 5=>'gris'];

// ID numérico del técnico en sesión
$id_tecnico = 0;
if (isset($_SESSION['usser'])) {
    $stmt_yo = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
    $stmt_yo->bind_param("s", $_SESSION['usser']);
    $stmt_yo->execute();
    $yo = $stmt_yo->get_result()->fetch_assoc();
    $stmt_yo->close();
    $id_tecnico = $yo['id_usuario'] ?? 0;
}

// ---- STATS ----
$criticos = $conexion->prepare("
    SELECT COUNT(*) c FROM tickets t JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    WHERE t.id_usuario_asignado = ? AND p.nivel = 4 AND t.id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")
");
$criticos->bind_param("i", $id_tecnico);
$criticos->execute();
$criticos_n = $criticos->get_result()->fetch_assoc()['c'];

$pendientes_stmt = $conexion->prepare("SELECT COUNT(*) c FROM tickets WHERE id_usuario_asignado = ? AND id_estado = " . TICKET_PENDIENTE);
$pendientes_stmt->bind_param("i", $id_tecnico);
$pendientes_stmt->execute();
$pendientes_n = $pendientes_stmt->get_result()->fetch_assoc()['c'];

$resueltos_stmt = $conexion->prepare("SELECT COUNT(*) c FROM tickets WHERE id_usuario_asignado = ? AND id_estado IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")");
$resueltos_stmt->bind_param("i", $id_tecnico);
$resueltos_stmt->execute();
$resueltos_n = $resueltos_stmt->get_result()->fetch_assoc()['c'];

$tprom_stmt = $conexion->prepare("
    SELECT ROUND(AVG(TIMESTAMPDIFF(HOUR, fecha_creacion, fecha_cierre)), 1) h
    FROM tickets WHERE id_usuario_asignado = ? AND fecha_cierre IS NOT NULL
");
$tprom_stmt->bind_param("i", $id_tecnico);
$tprom_stmt->execute();
$tprom_row = $tprom_stmt->get_result()->fetch_assoc();
$tiempo_prom = $tprom_row['h'] !== null ? $tprom_row['h'] . 'h' : '—';

// ---- ASIGNADOS A MÍ (activos, top 4) ----
$asignados_stmt = $conexion->prepare("
    SELECT t.id_tickets, t.codigo_ticket, t.asunto,
           sol.usuario_login AS sol_login,
           dep.nombre AS departamento_nombre,
           p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel,
           e.nombre AS estado_nombre, e.id_estado_ticket
    FROM tickets t
    JOIN usuarios sol ON t.id_usuario_solicitante = sol.id_usuario
    JOIN departamentos dep ON t.id_departamento = dep.id_departamento
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
    WHERE t.id_usuario_asignado = ? AND t.id_estado IN (" . TICKET_PROCESO . "," . TICKET_PENDIENTE . ")
    ORDER BY p.nivel DESC, t.fecha_creacion ASC
    LIMIT 4
");
$asignados_stmt->bind_param("i", $id_tecnico);
$asignados_stmt->execute();
$asignados = $asignados_stmt->get_result();

// ---- MI RENDIMIENTO ----
$resueltos_hoy_stmt = $conexion->prepare("
    SELECT COUNT(*) c FROM tickets WHERE id_usuario_asignado = ? AND DATE(fecha_cierre) = CURDATE()
");
$resueltos_hoy_stmt->bind_param("i", $id_tecnico);
$resueltos_hoy_stmt->execute();
$resueltos_hoy = $resueltos_hoy_stmt->get_result()->fetch_assoc()['c'];

$sla_stmt = $conexion->prepare("
    SELECT COUNT(*) total, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre) <= p.tiempo_respuesta_objetivo THEN 1 ELSE 0 END) dentro
    FROM tickets t JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    WHERE t.id_usuario_asignado = ? AND t.fecha_cierre IS NOT NULL
");
$sla_stmt->bind_param("i", $id_tecnico);
$sla_stmt->execute();
$sla_row = $sla_stmt->get_result()->fetch_assoc();
$sla_pct = ($sla_row['total'] > 0) ? round(($sla_row['dentro'] / $sla_row['total']) * 100) . '%' : '—';

$metricas = [
    ['Resueltos hoy', $resueltos_hoy, 'verde'],
    ['SLA cumplido', $sla_pct, 'verde'],
    ['En espera resp.', $pendientes_n, 'amarillo'],
    ['Promedio resolución', $tiempo_prom, 'azul'],
];

// ---- RECIENTE (mi actividad: cambios + comentarios) ----
$reciente = [];
$hist_stmt = $conexion->prepare("
    SELECT t.codigo_ticket, h.campo_modificado, h.valor_nuevo, h.fecha_cambio
    FROM historial_ticket h JOIN tickets t ON h.id_ticket = t.id_tickets
    WHERE h.id_usuario = ? ORDER BY h.fecha_cambio DESC LIMIT 3
");
$hist_stmt->bind_param("i", $id_tecnico);
$hist_stmt->execute();
$hist_res = $hist_stmt->get_result();
while ($h = $hist_res->fetch_assoc()) {
    $es_resuelto = stripos($h['valor_nuevo'], 'Resuelto') !== false;
    $reciente[] = [
        'icono' => $es_resuelto ? 'fas fa-check' : 'fas fa-exchange-alt',
        'bg' => $es_resuelto ? 'f0fdf4' : 'fff7ed',
        'color' => $es_resuelto ? 'success' : 'warning',
        'texto' => ($es_resuelto ? '#' . $h['codigo_ticket'] . ' resuelto' : 'Estado cambiado #' . $h['codigo_ticket']),
        'fecha' => $h['fecha_cambio'],
    ];
}
$coment_stmt = $conexion->prepare("
    SELECT t.codigo_ticket, c.fecha_comentario
    FROM comentarios c JOIN tickets t ON c.id_ticket = t.id_tickets
    WHERE c.id_usuario = ? ORDER BY c.fecha_comentario DESC LIMIT 3
");
$coment_stmt->bind_param("i", $id_tecnico);
$coment_stmt->execute();
$coment_res = $coment_stmt->get_result();
while ($c = $coment_res->fetch_assoc()) {
    $reciente[] = [
        'icono' => 'fas fa-comment', 'bg' => 'eff6ff', 'color' => 'info',
        'texto' => 'Comentario en #' . $c['codigo_ticket'],
        'fecha' => $c['fecha_comentario'],
    ];
}
usort($reciente, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));
$reciente = array_slice($reciente, 0, 3);

function hace_tiempo_tec($fecha) {
    $diff = time() - strtotime($fecha);
    if ($diff < 3600) return 'hace ' . round($diff / 60) . ' min';
    if ($diff < 86400) return 'hace ' . round($diff / 3600) . 'h';
    if ($diff < 172800) return 'ayer';
    return round($diff / 86400) . ' días';
}

// ---- MIS ÚLTIMAS ACCIONES (tabla inferior) ----
$acciones_stmt = $conexion->prepare("
    SELECT t.codigo_ticket, h.campo_modificado, h.valor_anterior, h.valor_nuevo, h.fecha_cambio
    FROM historial_ticket h JOIN tickets t ON h.id_ticket = t.id_tickets
    WHERE h.id_usuario = ? ORDER BY h.fecha_cambio DESC LIMIT 5
");
$acciones_stmt->bind_param("i", $id_tecnico);
$acciones_stmt->execute();
$acciones = $acciones_stmt->get_result();

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title">Mi Panel — Técnico TI</div>
            <div class="hd-page-sub">Tickets asignados y actividad · <?= date('d/m/Y') ?></div>
        </div>
        <a href="tickets_asignados.php" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-inbox"></i> Ver mis tickets</a>
    </div>

    <!-- STATS -->
    <div class="hd-stats hd-stats-4">
        <div class="hd-stat rojo">
            <div class="hd-stat-icon rojo"><i class="fas fa-fire"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($criticos_n, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">Críticos</div></div>
        </div>
        <div class="hd-stat naranja">
            <div class="hd-stat-icon naranja"><i class="fas fa-inbox"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($pendientes_n, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">Pendientes</div></div>
        </div>
        <div class="hd-stat verde">
            <div class="hd-stat-icon verde"><i class="fas fa-check-double"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($resueltos_n, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">Resueltos</div></div>
        </div>
        <div class="hd-stat amarillo">
            <div class="hd-stat-icon amarillo"><i class="fas fa-clock"></i></div>
            <div><div class="hd-stat-num"><?= $tiempo_prom ?></div><div class="hd-stat-label">Tiempo prom.</div></div>
        </div>
    </div>

    <div class="hd-grid-8-4" style="margin-bottom:12px;">

        <!-- MIS TICKETS -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-inbox"></i> Asignados a Mí</h5>
                <a href="tickets_asignados.php" class="hd-btn hd-btn-ghost hd-btn-sm">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr><th>ID</th><th>Asunto</th><th>Solicitante</th><th>Depto.</th><th>Prioridad</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if ($asignados->num_rows === 0): ?>
                        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:16px;">No tienes tickets activos.</td></tr>
                        <?php endif; ?>
                        <?php while ($t = $asignados->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-family:monospace;color:var(--muted);font-size:0.7rem;">#<?= htmlspecialchars($t['codigo_ticket']) ?></span></td>
                            <td>
                                <div style="font-weight:500;font-size:0.78rem;"><?= htmlspecialchars($t['asunto']) ?></div>
                                <div style="font-size:0.68rem;color:var(--muted);"><?= htmlspecialchars($t['departamento_nombre']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($t['sol_login']) ?></td>
                            <td style="color:var(--muted);"><?= htmlspecialchars($t['departamento_nombre']) ?></td>
                            <td><span class="hd-tag hd-tag-<?= $colores_prioridad[$t['prioridad_nivel']] ?? 'gris' ?>"><?= htmlspecialchars($t['prioridad_nombre']) ?></span></td>
                            <td><span class="hd-tag hd-tag-<?= $colores_estado[$t['id_estado_ticket']] ?? 'gris' ?>"><?= htmlspecialchars($t['estado_nombre']) ?></span></td>
                            <td><a href="ver_ticket.php?id=<?= $t['id_tickets'] ?>" class="hd-btn hd-btn-primary hd-btn-sm"><i class="fas fa-wrench"></i></a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DERECHA -->
        <div style="display:flex;flex-direction:column;gap:10px;">

            <div class="hd-card">
                <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-chart-line"></i> Mi Rendimiento</h5></div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php foreach($metricas as $m): ?>
                    <div class="hd-list-item">
                        <span style="color:var(--muted);flex:1;"><?= $m[0] ?></span>
                        <span class="hd-tag hd-tag-<?= $m[2] ?>" style="font-weight:700;"><?= $m[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hd-card">
                <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-history"></i> Reciente</h5></div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php if (empty($reciente)): ?>
                    <div style="color:var(--muted);font-size:0.78rem;padding:8px 0;">Sin actividad reciente.</div>
                    <?php endif; ?>
                    <?php foreach($reciente as $a): ?>
                    <div class="hd-activity">
                        <div class="hd-activity-dot" style="background:#<?= $a['bg'] ?>;color:var(--<?= $a['color'] ?>);"><i class="<?= $a['icono'] ?>"></i></div>
                        <div><div><?= htmlspecialchars($a['texto']) ?></div><div class="hd-activity-time"><?= hace_tiempo_tec($a['fecha']) ?></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- HISTORIAL -->
    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-history"></i> Mis Últimas Acciones</h5>
            <a href="historial.php" class="hd-btn hd-btn-ghost hd-btn-sm">Ver historial completo</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="hd-table">
                <thead><tr><th>Ticket</th><th>Acción</th><th>Antes</th><th>Después</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php if ($acciones->num_rows === 0): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:16px;">Aún no tienes acciones registradas.</td></tr>
                    <?php endif; ?>
                    <?php while ($ac = $acciones->fetch_assoc()): ?>
                    <tr>
                        <td><span style="font-family:monospace;font-size:0.7rem;">#<?= htmlspecialchars($ac['codigo_ticket']) ?></span></td>
                        <td><?= htmlspecialchars($ac['campo_modificado']) ?></td>
                        <td><?= $ac['valor_anterior'] ? '<span class="hd-tag hd-tag-gris">' . htmlspecialchars($ac['valor_anterior']) . '</span>' : '—' ?></td>
                        <td><span class="hd-tag hd-tag-verde"><?= htmlspecialchars($ac['valor_nuevo']) ?></span></td>
                        <td style="color:var(--muted);font-size:0.72rem;"><?= date('d/m/Y H:i', strtotime($ac['fecha_cambio'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php include_once '../includes/footer2.php'; ?>
