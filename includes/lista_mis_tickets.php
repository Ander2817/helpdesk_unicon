<?php
/**
 * includes/lista_mis_tickets.php
 * "Mis Solicitudes": los tickets que la persona en sesión CREÓ (no confundir con los que
 * le asignaron para atender). Sirve para admin y técnico; el usuario tiene su mis_tickets.php.
 * Se incluye DESPUÉS de header2.php + sidebar.php y ANTES de footer2.php.
 */
if (!defined('NOMBRE_SISTEMA')) require_once '../includes/constants.php';

$stmt_yo = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt_yo->bind_param("s", $usuario_activo);
$stmt_yo->execute();
$id_yo = (int)($stmt_yo->get_result()->fetch_assoc()['id_usuario'] ?? 0);
$stmt_yo->close();

$mapa_estado_slug = [
    'abierto'   => TICKET_ABIERTO,
    'proceso'   => TICKET_PROCESO,
    'pendiente' => TICKET_PENDIENTE,
    'resuelto'  => TICKET_RESUELTO,
    'cerrado'   => TICKET_CERRADO,
];
$estado_slug = $_GET['estado'] ?? '';
$filtro_estado_id = $mapa_estado_slug[$estado_slug] ?? null;

$sql = "
    SELECT t.id_tickets, t.codigo_ticket, t.asunto, t.fecha_creacion,
           c.nombre AS categoria_nombre,
           p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel,
           e.nombre AS estado_nombre, e.id_estado_ticket,
           asig.nombres AS asig_nombres, asig.apellidos AS asig_apellidos
    FROM tickets t
    JOIN categoria_incidencias c ON t.id_categoria = c.id_categoria
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
    LEFT JOIN usuarios asig ON t.id_usuario_asignado = asig.id_usuario
    WHERE t.id_usuario_solicitante = ?
";
if ($filtro_estado_id !== null) {
    $stmt = $conexion->prepare($sql . " AND t.id_estado = ? ORDER BY t.fecha_creacion DESC");
    $stmt->bind_param("ii", $id_yo, $filtro_estado_id);
} else {
    $stmt = $conexion->prepare($sql . " ORDER BY t.fecha_creacion DESC");
    $stmt->bind_param("i", $id_yo);
}
$stmt->execute();
$tickets = $stmt->get_result();

$colores_estado = [1=>'#0284c7', 2=>'#E87722', 3=>'#d97706', 4=>'#16a34a', 5=>'#64748b'];
$colores_nivel  = [1=>'#0284c7', 2=>'#d97706', 3=>'#E87722', 4=>'#dc2626'];
?>

<style>
    .hd-badge-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px; border-radius: 20px; font-size: 0.74rem; font-weight: 600;
    }
    .hd-badge-pill .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .hd-fila-ticket { cursor: pointer; }
    .hd-fila-ticket:hover { background: #f8fafc; }
    .hd-filtros { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .hd-filtro-btn {
        padding: 6px 14px; border-radius: 20px; border: 1.5px solid var(--borde);
        background: #fff; font-size: 0.76rem; font-weight: 500; color: var(--muted);
        cursor: pointer; transition: all 0.15s; text-decoration: none; display: inline-block;
    }
    .hd-filtro-btn.activo { border-color: var(--naranja); color: var(--naranja); background: rgba(232,119,34,0.06); }
    .hd-sin-asignar { color: #cbd5e1; font-style: italic; }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-ticket-alt" style="color:var(--naranja);margin-right:6px;"></i> Mis Solicitudes</div>
            <div class="hd-page-sub">Tickets que tú reportaste · <?= $tickets->num_rows ?> ticket<?= $tickets->num_rows !== 1 ? 's' : '' ?></div>
        </div>
        <a href="tickets_crear.php" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-plus"></i> Crear Ticket</a>
    </div>

    <div class="hd-filtros">
        <a href="mis_tickets.php" class="hd-filtro-btn <?= $estado_slug === '' ? 'activo' : '' ?>">Todos</a>
        <a href="mis_tickets.php?estado=abierto" class="hd-filtro-btn <?= $estado_slug === 'abierto' ? 'activo' : '' ?>">Abiertos</a>
        <a href="mis_tickets.php?estado=proceso" class="hd-filtro-btn <?= $estado_slug === 'proceso' ? 'activo' : '' ?>">En Proceso</a>
        <a href="mis_tickets.php?estado=pendiente" class="hd-filtro-btn <?= $estado_slug === 'pendiente' ? 'activo' : '' ?>">Pendientes</a>
        <a href="mis_tickets.php?estado=resuelto" class="hd-filtro-btn <?= $estado_slug === 'resuelto' ? 'activo' : '' ?>">Resueltos</a>
        <a href="mis_tickets.php?estado=cerrado" class="hd-filtro-btn <?= $estado_slug === 'cerrado' ? 'activo' : '' ?>">Cerrados</a>
    </div>

    <div class="hd-card">
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Asunto</th>
                        <th>Categoría</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Técnico</th>
                        <th>Creado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tickets->num_rows === 0): ?>
                    <tr><td colspan="7" style="text-align:center;padding:26px;color:var(--muted);">
                        <?= $filtro_estado_id !== null ? 'No tienes solicitudes con este estado.' : 'Aún no has creado ningún ticket.' ?>
                    </td></tr>
                    <?php endif; ?>
                    <?php while ($t = $tickets->fetch_assoc()):
                        $ce = $colores_estado[$t['id_estado_ticket']] ?? '#94a3b8';
                        $cp = $colores_nivel[$t['prioridad_nivel']] ?? '#94a3b8';
                    ?>
                    <tr class="hd-fila-ticket" onclick="window.location='ver_ticket.php?id=<?= $t['id_tickets'] ?>'">
                        <td style="font-weight:600;color:var(--azul);">#<?= htmlspecialchars($t['codigo_ticket']) ?></td>
                        <td><?= htmlspecialchars($t['asunto']) ?></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($t['categoria_nombre']) ?></td>
                        <td>
                            <span class="hd-badge-pill" style="background:color-mix(in srgb, <?= $cp ?> 12%, white); color:<?= $cp ?>;">
                                <span class="dot" style="background:<?= $cp ?>;"></span> <?= htmlspecialchars($t['prioridad_nombre']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="hd-badge-pill" style="background:color-mix(in srgb, <?= $ce ?> 12%, white); color:<?= $ce ?>;">
                                <span class="dot" style="background:<?= $ce ?>;"></span> <?= htmlspecialchars($t['estado_nombre']) ?>
                            </span>
                        </td>
                        <td style="color:var(--muted);">
                            <?= $t['asig_nombres'] ? htmlspecialchars($t['asig_nombres'] . ' ' . $t['asig_apellidos']) : '<span class="hd-sin-asignar">Esperando asignación</span>' ?>
                        </td>
                        <td style="color:var(--muted);font-size:0.78rem;"><?= date('d/m/Y', strtotime($t['fecha_creacion'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>
