<?php
session_start();
require_once '../includes/constants.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

$colores_prioridad = [1=>'gris', 2=>'amarillo', 3=>'naranja', 4=>'rojo'];
$colores_estado    = [1=>'azul', 2=>'naranja', 3=>'gris', 4=>'verde', 5=>'gris'];

// ---- STATS ----
$criticos_abiertos = $conexion->query("
    SELECT COUNT(*) c FROM tickets t
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    WHERE p.nivel = 4 AND t.id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")
")->fetch_assoc()['c'];

$en_proceso = $conexion->query("SELECT COUNT(*) c FROM tickets WHERE id_estado = " . TICKET_PROCESO)->fetch_assoc()['c'];

$resueltos_mes = $conexion->query("
    SELECT COUNT(*) c FROM tickets
    WHERE id_estado IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")
    AND MONTH(fecha_cierre) = MONTH(CURDATE()) AND YEAR(fecha_cierre) = YEAR(CURDATE())
")->fetch_assoc()['c'];

$usuarios_activos = $conexion->query("SELECT COUNT(*) c FROM usuarios WHERE estado = 'activo'")->fetch_assoc()['c'];

// ---- TICKETS RECIENTES (últimos 5) ----
$recientes = $conexion->query("
    SELECT t.id_tickets, t.codigo_ticket, t.asunto,
           dep.nombre AS departamento_nombre,
           asig.usuario_login AS tec_login,
           p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel,
           e.nombre AS estado_nombre, e.id_estado_ticket
    FROM tickets t
    JOIN departamentos dep ON t.id_departamento = dep.id_departamento
    LEFT JOIN usuarios asig ON t.id_usuario_asignado = asig.id_usuario
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
    ORDER BY t.fecha_creacion DESC
    LIMIT 5
");

// ---- RESUMEN ----
$total_tickets = $conexion->query("SELECT COUNT(*) c FROM tickets")->fetch_assoc()['c'];

$tiempo_prom_row = $conexion->query("
    SELECT ROUND(AVG(TIMESTAMPDIFF(HOUR, fecha_creacion, fecha_cierre)), 1) h
    FROM tickets WHERE fecha_cierre IS NOT NULL
")->fetch_assoc();
$tiempo_prom = $tiempo_prom_row['h'] !== null ? $tiempo_prom_row['h'] . 'h' : '—';

$sla_row = $conexion->query("
    SELECT COUNT(*) total, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre) <= p.tiempo_respuesta_objetivo THEN 1 ELSE 0 END) dentro
    FROM tickets t JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    WHERE t.fecha_cierre IS NOT NULL
")->fetch_assoc();
$sla_pct = ($sla_row['total'] > 0) ? round(($sla_row['dentro'] / $sla_row['total']) * 100) . '%' : '—';

$tecnicos_activos_n = $conexion->query("SELECT COUNT(*) c FROM usuarios WHERE id_rol = " . ROL_TECNICO . " AND estado='activo'")->fetch_assoc()['c'];
$equipos_inv = $conexion->query("SELECT COUNT(*) c FROM inventario_tecnologico")->fetch_assoc()['c'];

$resumen = [
    ['Total Tickets', $total_tickets, 'azul'],
    ['Tiempo prom.', $tiempo_prom, 'naranja'],
    ['SLA cumplido', $sla_pct, 'verde'],
    ['Técnicos activos', $tecnicos_activos_n, 'gris'],
    ['Equipos inv.', $equipos_inv, 'gris'],
];

// ---- ACTIVIDAD (últimos cambios de estado + comentarios, combinados) ----
$actividad = [];
$hist = $conexion->query("
    SELECT t.codigo_ticket, h.campo_modificado, h.valor_nuevo, h.fecha_cambio
    FROM historial_ticket h JOIN tickets t ON h.id_ticket = t.id_tickets
    ORDER BY h.fecha_cambio DESC LIMIT 4
");
while ($h = $hist->fetch_assoc()) {
    $es_resuelto = stripos($h['valor_nuevo'], 'Resuelto') !== false;
    $actividad[] = [
        'icono' => $es_resuelto ? 'fas fa-check' : 'fas fa-exchange-alt',
        'bg' => $es_resuelto ? 'eff6ff' : 'fff7ed',
        'color' => $es_resuelto ? 'info' : 'warning',
        'texto' => '#' . $h['codigo_ticket'] . ' → ' . $h['campo_modificado'] . ': ' . $h['valor_nuevo'],
        'fecha' => $h['fecha_cambio'],
    ];
}
$coment = $conexion->query("
    SELECT t.codigo_ticket, c.fecha_comentario
    FROM comentarios c JOIN tickets t ON c.id_ticket = t.id_tickets
    ORDER BY c.fecha_comentario DESC LIMIT 4
");
while ($c = $coment->fetch_assoc()) {
    $actividad[] = [
        'icono' => 'fas fa-comment', 'bg' => 'f0fdf4', 'color' => 'success',
        'texto' => 'Comentario en #' . $c['codigo_ticket'],
        'fecha' => $c['fecha_comentario'],
    ];
}
usort($actividad, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));
$actividad = array_slice($actividad, 0, 4);

function hace_tiempo_admin($fecha) {
    $diff = time() - strtotime($fecha);
    if ($diff < 3600) return round($diff / 60) . ' min';
    if ($diff < 86400) return round($diff / 3600) . ' horas';
    return round($diff / 86400) . ' días';
}

// ---- POR CATEGORÍA ----
$cats_raw = $conexion->query("
    SELECT c.nombre, COUNT(t.id_tickets) total
    FROM categoria_incidencias c LEFT JOIN tickets t ON t.id_categoria = c.id_categoria
    GROUP BY c.id_categoria ORDER BY total DESC
")->fetch_all(MYSQLI_ASSOC);
$max_cat = max(array_column($cats_raw, 'total')) ?: 1;
$cats = array_map(fn($c) => [$c['nombre'], $c['total'], $max_cat > 0 ? round(($c['total'] / $max_cat) * 100) : 0], $cats_raw);

// ---- POR DEPARTAMENTO ----
$deps_raw = $conexion->query("
    SELECT d.nombre, COUNT(t.id_tickets) total
    FROM departamentos d LEFT JOIN tickets t ON t.id_departamento = d.id_departamento
    GROUP BY d.id_departamento ORDER BY total DESC
")->fetch_all(MYSQLI_ASSOC);
$max_dep = max(array_column($deps_raw, 'total')) ?: 1;
$deptos = array_map(fn($d) => [$d['nombre'], $d['total'], $max_dep > 0 ? round(($d['total'] / $max_dep) * 100) : 0], $deps_raw);

// ---- TÉCNICOS ----
$tecs_raw = $conexion->query("
    SELECT u.usuario_login, u.nombres, u.apellidos,
           COUNT(CASE WHEN t.id_estado IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ") THEN 1 END) resueltos
    FROM usuarios u LEFT JOIN tickets t ON t.id_usuario_asignado = u.id_usuario
    WHERE u.id_rol = " . ROL_TECNICO . " AND u.estado = 'activo'
    GROUP BY u.id_usuario ORDER BY resueltos DESC
")->fetch_all(MYSQLI_ASSOC);
$paleta_tecs = ['verde', 'naranja', 'azul', 'amarillo', 'gris'];
$tecs = [];
foreach ($tecs_raw as $i => $t) {
    $tecs[] = [$t['usuario_login'], $t['nombres'] . ' ' . $t['apellidos'], $t['resueltos'], $paleta_tecs[$i] ?? 'gris'];
}

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<main class="hd-main">

    <!-- HEADER -->
    <div class="hd-page-header">
        <div>
            <div class="hd-page-title">Panel General — Administrador</div>
            <div class="hd-page-sub">Vista completa del sistema · <?= date('d/m/Y') ?></div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="reportes.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-download"></i> Exportar</a>
            <a href="tickets_crear.php" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-plus"></i> Crear Ticket</a>
        </div>
    </div>

    <!-- STATS -->
    <div class="hd-stats hd-stats-4">
        <div class="hd-stat rojo">
            <div class="hd-stat-icon rojo"><i class="fas fa-fire"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($criticos_abiertos, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">Críticos Abiertos</div></div>
        </div>
        <div class="hd-stat naranja">
            <div class="hd-stat-icon naranja"><i class="fas fa-spinner"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($en_proceso, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">En Proceso</div></div>
        </div>
        <div class="hd-stat verde">
            <div class="hd-stat-icon verde"><i class="fas fa-check-circle"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($resueltos_mes, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">Resueltos mes</div></div>
        </div>
        <div class="hd-stat azul">
            <div class="hd-stat-icon azul"><i class="fas fa-users"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($usuarios_activos, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">Usuarios Activos</div></div>
        </div>
    </div>

    <!-- FILA PRINCIPAL -->
    <div class="hd-grid-8-4" style="margin-bottom:12px;">

        <!-- TICKETS RECIENTES -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-list"></i> Tickets Recientes</h5>
                <a href="tickets.php" class="hd-btn hd-btn-ghost hd-btn-sm">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asunto</th>
                            <th>Área</th>
                            <th>Técnico</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recientes->num_rows === 0): ?>
                        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:16px;">Aún no hay tickets.</td></tr>
                        <?php endif; ?>
                        <?php while ($t = $recientes->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-family:monospace;color:var(--muted);font-size:0.7rem;">#<?= htmlspecialchars($t['codigo_ticket']) ?></span></td>
                            <td>
                                <div style="font-weight:500;font-size:0.78rem;"><?= htmlspecialchars($t['asunto']) ?></div>
                                <div style="font-size:0.68rem;color:var(--muted);"><?= htmlspecialchars($t['departamento_nombre']) ?></div>
                            </td>
                            <td style="color:var(--muted);"><?= htmlspecialchars($t['departamento_nombre']) ?></td>
                            <td>
                                <?php if ($t['tec_login']): ?>
                                <div style="display:flex;align-items:center;gap:5px;">
                                    <div class="hd-avatar" style="width:20px;height:20px;font-size:0.6rem;"><?= strtoupper(substr($t['tec_login'],0,1)) ?></div>
                                    <span><?= htmlspecialchars($t['tec_login']) ?></span>
                                </div>
                                <?php else: ?><span style="color:var(--muted);">Sin asignar</span><?php endif; ?>
                            </td>
                            <td><span class="hd-tag hd-tag-<?= $colores_prioridad[$t['prioridad_nivel']] ?? 'gris' ?>"><?= htmlspecialchars($t['prioridad_nombre']) ?></span></td>
                            <td><span class="hd-tag hd-tag-<?= $colores_estado[$t['id_estado_ticket']] ?? 'gris' ?>"><?= htmlspecialchars($t['estado_nombre']) ?></span></td>
                            <td><a href="ver_ticket.php?id=<?= $t['id_tickets'] ?>" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div style="display:flex;flex-direction:column;gap:10px;">

            <!-- Resumen -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-chart-pie"></i> Resumen</h5>
                </div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php foreach($resumen as $r): ?>
                    <div class="hd-list-item">
                        <span style="color:var(--muted);flex:1;"><?= $r[0] ?></span>
                        <span class="hd-tag hd-tag-<?= $r[2] ?>" style="font-weight:700;"><?= $r[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Actividad -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-history"></i> Actividad</h5>
                </div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php if (empty($actividad)): ?>
                    <div style="color:var(--muted);font-size:0.78rem;padding:8px 0;">Sin actividad reciente.</div>
                    <?php endif; ?>
                    <?php foreach($actividad as $a): ?>
                    <div class="hd-activity">
                        <div class="hd-activity-dot" style="background:#<?= $a['bg'] ?>;color:var(--<?= $a['color'] ?>);"><i class="<?= $a['icono'] ?>"></i></div>
                        <div>
                            <div><?= htmlspecialchars($a['texto']) ?></div>
                            <div class="hd-activity-time"><?= hace_tiempo_admin($a['fecha']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- FILA INFERIOR -->
    <div class="hd-grid-3">

        <!-- Por categoría -->
        <div class="hd-card">
            <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-tags"></i> Por Categoría</h5></div>
            <div class="hd-card-body" style="padding:8px 12px;">
                <?php if (empty($cats)): ?>
                <div style="color:var(--muted);font-size:0.78rem;">Sin datos.</div>
                <?php endif; ?>
                <?php foreach($cats as $c): ?>
                <div class="hd-list-item">
                    <span style="flex:1;"><?= htmlspecialchars($c[0]) ?></span>
                    <div class="hd-progress"><div class="hd-progress-fill" style="width:<?= $c[2] ?>%;"></div></div>
                    <span style="color:var(--muted);min-width:20px;text-align:right;"><?= $c[1] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Por departamento -->
        <div class="hd-card">
            <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-building"></i> Por Departamento</h5></div>
            <div class="hd-card-body" style="padding:8px 12px;">
                <?php if (empty($deptos)): ?>
                <div style="color:var(--muted);font-size:0.78rem;">Sin datos.</div>
                <?php endif; ?>
                <?php foreach($deptos as $d): ?>
                <div class="hd-list-item">
                    <span style="flex:1;"><?= htmlspecialchars($d[0]) ?></span>
                    <div class="hd-progress"><div class="hd-progress-fill" style="width:<?= $d[2] ?>%;"></div></div>
                    <span style="color:var(--muted);min-width:20px;text-align:right;"><?= $d[1] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Técnicos -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-user-cog"></i> Técnicos</h5>
                <a href="procesos/usuarios.php" class="hd-btn hd-btn-ghost hd-btn-sm">Gestionar</a>
            </div>
            <div class="hd-card-body" style="padding:8px 12px;">
                <?php if (empty($tecs)): ?>
                <div style="color:var(--muted);font-size:0.78rem;">No hay técnicos activos.</div>
                <?php endif; ?>
                <?php foreach($tecs as $t): ?>
                <div class="hd-list-item">
                    <div class="hd-avatar" style="width:24px;height:24px;font-size:0.65rem;"><?= strtoupper(substr($t[0],0,1)) ?></div>
                    <div style="flex:1;">
                        <div style="font-weight:500;"><?= htmlspecialchars($t[1]) ?></div>
                        <div style="font-size:0.68rem;color:var(--muted);"><?= $t[2] ?> tickets</div>
                    </div>
                    <span class="hd-tag hd-tag-<?= $t[3] ?>"><?= $t[2] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</main>

<?php include_once '../includes/footer2.php'; ?>
