<?php
session_start();
require_once '../includes/constants.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

$colores_prioridad = [1=>'gris', 2=>'amarillo', 3=>'naranja', 4=>'rojo'];
$colores_estado    = [1=>'azul', 2=>'naranja', 3=>'gris', 4=>'verde', 5=>'gris'];

// ID numérico del usuario en sesión
$id_usuario_actual = 0;
if (isset($_SESSION['usser'])) {
    $stmt_yo = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
    $stmt_yo->bind_param("s", $_SESSION['usser']);
    $stmt_yo->execute();
    $yo = $stmt_yo->get_result()->fetch_assoc();
    $stmt_yo->close();
    $id_usuario_actual = $yo['id_usuario'] ?? 0;
}

// ---- STATS ----
$total_mios_stmt = $conexion->prepare("SELECT COUNT(*) c FROM tickets WHERE id_usuario_solicitante = ?");
$total_mios_stmt->bind_param("i", $id_usuario_actual);
$total_mios_stmt->execute();
$total_mios = $total_mios_stmt->get_result()->fetch_assoc()['c'];

$proceso_stmt = $conexion->prepare("SELECT COUNT(*) c FROM tickets WHERE id_usuario_solicitante = ? AND id_estado = " . TICKET_PROCESO);
$proceso_stmt->bind_param("i", $id_usuario_actual);
$proceso_stmt->execute();
$proceso_n = $proceso_stmt->get_result()->fetch_assoc()['c'];

$resueltos_stmt = $conexion->prepare("SELECT COUNT(*) c FROM tickets WHERE id_usuario_solicitante = ? AND id_estado IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")");
$resueltos_stmt->bind_param("i", $id_usuario_actual);
$resueltos_stmt->execute();
$resueltos_n = $resueltos_stmt->get_result()->fetch_assoc()['c'];

// ---- MIS TICKETS (últimos 3) ----
$mis_tickets_stmt = $conexion->prepare("
    SELECT t.id_tickets, t.codigo_ticket, t.asunto,
           c.nombre AS categoria_nombre,
           asig.usuario_login AS tec_login,
           p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel,
           e.nombre AS estado_nombre, e.id_estado_ticket
    FROM tickets t
    JOIN categoria_incidencias c ON t.id_categoria = c.id_categoria
    LEFT JOIN usuarios asig ON t.id_usuario_asignado = asig.id_usuario
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
    WHERE t.id_usuario_solicitante = ?
    ORDER BY t.fecha_creacion DESC
    LIMIT 3
");
$mis_tickets_stmt->bind_param("i", $id_usuario_actual);
$mis_tickets_stmt->execute();
$mis_tickets = $mis_tickets_stmt->get_result();

// ---- TIEMPO DE RESPUESTA PROMEDIO (global, informativo) ----
$tprom_row = $conexion->query("
    SELECT ROUND(AVG(TIMESTAMPDIFF(HOUR, fecha_creacion, fecha_cierre)), 1) h
    FROM tickets WHERE fecha_cierre IS NOT NULL
")->fetch_assoc();
$tiempo_prom_global = $tprom_row['h'] !== null ? $tprom_row['h'] . 'h' : '—';

// ---- ESTADO GENERAL (mis tickets por estado) ----
$estados_def = [
    [TICKET_ABIERTO, 'Abiertos', 'azul'],
    [TICKET_PROCESO, 'En Proceso', 'naranja'],
    [TICKET_PENDIENTE, 'Pendientes', 'gris'],
    [TICKET_RESUELTO, 'Resueltos', 'verde'],
    [TICKET_CERRADO, 'Cerrados', 'gris'],
];
$estados = [];
foreach ($estados_def as $ed) {
    $stmt = $conexion->prepare("SELECT COUNT(*) c FROM tickets WHERE id_usuario_solicitante = ? AND id_estado = ?");
    $stmt->bind_param("ii", $id_usuario_actual, $ed[0]);
    $stmt->execute();
    $cnt = $stmt->get_result()->fetch_assoc()['c'];
    $pct = $total_mios > 0 ? round(($cnt / $total_mios) * 100) : 0;
    $estados[] = [$ed[1], $cnt, $pct, $ed[2]];
}

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title">Bienvenido, <?= htmlspecialchars($usuario_activo) ?></div>
            <div class="hd-page-sub">Centro de soporte técnico · <?= date('d/m/Y') ?></div>
        </div>
        <a href="ticket_nuevo.php" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-plus"></i> Nuevo Ticket</a>
    </div>

    <!-- STATS -->
    <div class="hd-stats hd-stats-3">
        <div class="hd-stat azul">
            <div class="hd-stat-icon azul"><i class="fas fa-ticket-alt"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($total_mios, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">Mis Tickets</div></div>
        </div>
        <div class="hd-stat naranja">
            <div class="hd-stat-icon naranja"><i class="fas fa-spinner"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($proceso_n, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">En Proceso</div></div>
        </div>
        <div class="hd-stat verde">
            <div class="hd-stat-icon verde"><i class="fas fa-check-circle"></i></div>
            <div><div class="hd-stat-num"><?= str_pad($resueltos_n, 2, '0', STR_PAD_LEFT) ?></div><div class="hd-stat-label">Resueltos</div></div>
        </div>
    </div>

    <div class="hd-grid-8-4" style="margin-bottom:12px;">

        <!-- MIS TICKETS -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-ticket-alt"></i> Mis Tickets</h5>
                <a href="mis_tickets.php" class="hd-btn hd-btn-ghost hd-btn-sm">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr><th>ID</th><th>Asunto</th><th>Categoría</th><th>Técnico</th><th>Prioridad</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if ($mis_tickets->num_rows === 0): ?>
                        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:16px;">Aún no has creado ningún ticket.</td></tr>
                        <?php endif; ?>
                        <?php while ($t = $mis_tickets->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-family:monospace;color:var(--muted);font-size:0.7rem;">#<?= htmlspecialchars($t['codigo_ticket']) ?></span></td>
                            <td>
                                <div style="font-weight:500;font-size:0.78rem;"><?= htmlspecialchars($t['asunto']) ?></div>
                                <div style="font-size:0.68rem;color:var(--muted);"><?= htmlspecialchars($t['categoria_nombre']) ?></div>
                            </td>
                            <td style="color:var(--muted);"><?= htmlspecialchars($t['categoria_nombre']) ?></td>
                            <td>
                                <?php if($t['tec_login']): ?>
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

        <!-- DERECHA -->
        <div style="display:flex;flex-direction:column;gap:10px;">

            <!-- Crear ticket -->
            <div class="hd-card" style="border-top:3px solid var(--naranja);">
                <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-plus-circle"></i> Reportar Problema</h5></div>
                <div class="hd-card-body">
                    <p style="font-size:0.75rem;color:var(--muted);margin-bottom:10px;">¿Tienes un problema técnico? El equipo de TI lo atenderá.</p>
                    <a href="ticket_nuevo.php" class="hd-btn hd-btn-naranja" style="width:100%;justify-content:center;"><i class="fas fa-plus"></i> Crear Ticket</a>
                    <div style="margin-top:8px;padding:8px;background:#f8fafc;border-radius:6px;text-align:center;font-size:0.7rem;color:var(--muted);">
                        <i class="fas fa-clock" style="color:var(--naranja);"></i> Respuesta prom.: <strong><?= $tiempo_prom_global ?></strong>
                    </div>
                </div>
            </div>

            <!-- Estado -->
            <div class="hd-card">
                <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-chart-pie"></i> Estado General</h5></div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php foreach($estados as $e): ?>
                    <div class="hd-list-item">
                        <span style="flex:1;"><?= $e[0] ?></span>
                        <div class="hd-progress"><div class="hd-progress-fill" style="width:<?= $e[2] ?>%;"></div></div>
                        <span class="hd-tag hd-tag-<?= $e[3] ?>"><?= $e[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- GUÍA -->
    <div class="hd-card">
        <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-question-circle"></i> ¿Cómo funciona?</h5></div>
        <div class="hd-card-body">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;text-align:center;">
                <?php
                $pasos = [
                    ['fas fa-plus-circle','naranja','1. Crea el ticket','Describe el problema con el mayor detalle posible.'],
                    ['fas fa-search','azul','2. Es revisado','TI asigna al técnico disponible.'],
                    ['fas fa-wrench','amarillo','3. Se trabaja','El técnico atiende y actualiza el estado.'],
                    ['fas fa-check-circle','verde','4. Resuelto','Confirmas la solución y se cierra el ticket.'],
                ];
                foreach($pasos as $p): ?>
                <div style="padding:8px;">
                    <div class="hd-stat-icon <?= $p[1] ?>" style="margin:0 auto 8px;width:36px;height:36px;"><i class="<?= $p[0] ?>"></i></div>
                    <div style="font-weight:600;font-size:0.78rem;color:var(--azul);margin-bottom:4px;"><?= $p[2] ?></div>
                    <div style="font-size:0.72rem;color:var(--muted);line-height:1.4;"><?= $p[3] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</main>

<?php include_once '../includes/footer2.php'; ?>
