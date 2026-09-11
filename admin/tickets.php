<?php
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
require_once '../includes/constants.php';
include '../config/conexion.php';

// Mapeo de los slugs que ya usa el sidebar (?estado=abierto, etc.) a IDs reales
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
           sol.nombres AS sol_nombres, sol.apellidos AS sol_apellidos,
           dep.nombre AS departamento_nombre,
           asig.nombres AS asig_nombres, asig.apellidos AS asig_apellidos
    FROM tickets t
    JOIN categoria_incidencias c ON t.id_categoria = c.id_categoria
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
    JOIN usuarios sol ON t.id_usuario_solicitante = sol.id_usuario
    JOIN departamentos dep ON t.id_departamento = dep.id_departamento
    LEFT JOIN usuarios asig ON t.id_usuario_asignado = asig.id_usuario
";

if ($filtro_estado_id !== null) {
    $stmt = $conexion->prepare($sql . " WHERE t.id_estado = ? ORDER BY p.nivel DESC, t.fecha_creacion DESC");
    $stmt->bind_param("i", $filtro_estado_id);
} else {
    $stmt = $conexion->prepare($sql . " ORDER BY p.nivel DESC, t.fecha_creacion DESC");
}
$stmt->execute();
$tickets = $stmt->get_result();

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<style>
    .hd-table td.acciones { white-space: nowrap; width: 1%; }
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
            <div class="hd-page-title"><i class="fas fa-inbox" style="color:var(--naranja);margin-right:6px;"></i> Todos los Tickets</div>
            <div class="hd-page-sub"><?= $tickets->num_rows ?> ticket<?= $tickets->num_rows !== 1 ? 's' : '' ?><?= $filtro_estado_id !== null ? ' — filtrado' : '' ?></div>
        </div>
    </div>

    <div class="hd-filtros">
        <a href="tickets.php" class="hd-filtro-btn <?= $estado_slug === '' ? 'activo' : '' ?>">Todos</a>
        <a href="tickets.php?estado=abierto" class="hd-filtro-btn <?= $estado_slug === 'abierto' ? 'activo' : '' ?>">Abiertos</a>
        <a href="tickets.php?estado=proceso" class="hd-filtro-btn <?= $estado_slug === 'proceso' ? 'activo' : '' ?>">En Proceso</a>
        <a href="tickets.php?estado=pendiente" class="hd-filtro-btn <?= $estado_slug === 'pendiente' ? 'activo' : '' ?>">Pendientes</a>
        <a href="tickets.php?estado=resuelto" class="hd-filtro-btn <?= $estado_slug === 'resuelto' ? 'activo' : '' ?>">Resueltos</a>
        <a href="tickets.php?estado=cerrado" class="hd-filtro-btn <?= $estado_slug === 'cerrado' ? 'activo' : '' ?>">Cerrados</a>
    </div>

    <div class="hd-card">
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Asunto</th>
                        <th>Solicitante</th>
                        <th>Depto.</th>
                        <th>Categoría</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Técnico</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tickets->num_rows === 0): ?>
                    <tr><td colspan="9" style="text-align:center;padding:24px;color:var(--muted);">No hay tickets que coincidan con este filtro.</td></tr>
                    <?php endif; ?>
                    <?php
                    $colores_estado = [1=>'#0284c7', 2=>'#E87722', 3=>'#d97706', 4=>'#16a34a', 5=>'#64748b'];
                    $colores_nivel  = [1=>'#0284c7', 2=>'#d97706', 3=>'#E87722', 4=>'#dc2626'];
                    while ($t = $tickets->fetch_assoc()):
                        $ce = $colores_estado[$t['id_estado_ticket']] ?? '#94a3b8';
                        $cp = $colores_nivel[$t['prioridad_nivel']] ?? '#94a3b8';
                    ?>
                    <tr class="hd-fila-ticket" onclick="window.location='ver_ticket.php?id=<?= $t['id_tickets'] ?>'">
                        <td style="font-weight:600;color:var(--azul);">#<?= htmlspecialchars($t['codigo_ticket']) ?></td>
                        <td><?= htmlspecialchars($t['asunto']) ?></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($t['sol_nombres'] . ' ' . $t['sol_apellidos']) ?></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($t['departamento_nombre']) ?></td>
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
                            <?= $t['asig_nombres'] ? htmlspecialchars($t['asig_nombres'] . ' ' . $t['asig_apellidos']) : '<span class="hd-sin-asignar">Sin asignar</span>' ?>
                        </td>
                        <td class="acciones">
                            <a href="ver_ticket.php?id=<?= $t['id_tickets'] ?>" class="hd-icon-btn" style="display:inline-flex;width:28px;height:28px;border-radius:6px;border:1px solid var(--borde);align-items:center;justify-content:center;color:var(--muted);text-decoration:none;">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php include_once '../includes/footer2.php'; ?>
