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
$filtro_sin_asignar = ($estado_slug === 'sin_asignar'); // tickets activos que todavía no tienen técnico

$sql = "
    SELECT t.id_tickets, t.codigo_ticket, t.asunto, t.fecha_creacion, t.id_usuario_asignado,
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

if ($filtro_sin_asignar) {
    $stmt = $conexion->prepare($sql . " WHERE t.id_usuario_asignado IS NULL AND t.id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ") ORDER BY p.nivel DESC, t.fecha_creacion ASC");
} elseif ($filtro_estado_id !== null) {
    $stmt = $conexion->prepare($sql . " WHERE t.id_estado = ? ORDER BY p.nivel DESC, t.fecha_creacion DESC");
    $stmt->bind_param("i", $filtro_estado_id);
} else {
    $stmt = $conexion->prepare($sql . " ORDER BY p.nivel DESC, t.fecha_creacion DESC");
}
$stmt->execute();
$tickets = $stmt->get_result();

// Técnicos activos con su carga de trabajo (para el selector del modal "Asignar")
$tecnicos = $conexion->query("
    SELECT u.id_usuario, u.nombres, u.apellidos, COUNT(t.id_tickets) AS activos
    FROM usuarios u
    LEFT JOIN tickets t ON t.id_usuario_asignado = u.id_usuario
                       AND t.id_estado IN (" . TICKET_PROCESO . "," . TICKET_PENDIENTE . ")
    WHERE u.id_rol = " . ROL_TECNICO . " AND u.estado = 'activo'
    GROUP BY u.id_usuario
    ORDER BY u.nombres ASC
")->fetch_all(MYSQLI_ASSOC);

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<style>
    .hd-table td.acciones { white-space: nowrap; width: 1%; }
    .hd-icon-btn {
        width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--borde);
        background: #fff; color: var(--muted); display: inline-flex; align-items: center;
        justify-content: center; cursor: pointer; font-size: 0.75rem; transition: all 0.15s; text-decoration: none;
    }
    .hd-icon-btn:hover { border-color: var(--naranja); color: var(--naranja); }
    .hd-icon-btn.asignar { border-color: var(--naranja); color: var(--naranja); }
    .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
    .hd-input {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--borde); border-radius: 7px;
        font-family: 'Poppins', sans-serif; font-size: 0.83rem; color: var(--texto); background: #fff;
    }
    .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); outline: none; }
    .modal-title { font-size: 0.95rem; font-weight: 700; color: var(--azul); }
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
            <div class="hd-page-sub"><?= $tickets->num_rows ?> ticket<?= $tickets->num_rows !== 1 ? 's' : '' ?><?= ($filtro_estado_id !== null || $filtro_sin_asignar) ? ' — filtrado' : '' ?></div>
        </div>
    </div>

    <div class="hd-filtros">
        <a href="tickets.php" class="hd-filtro-btn <?= $estado_slug === '' ? 'activo' : '' ?>">Todos</a>
        <a href="tickets.php?estado=sin_asignar" class="hd-filtro-btn <?= $filtro_sin_asignar ? 'activo' : '' ?>"><i class="fas fa-user-clock" style="margin-right:5px;"></i>Sin asignar</a>
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
                            <?php if (!in_array((int)$t['id_estado_ticket'], [TICKET_RESUELTO, TICKET_CERRADO], true)): ?>
                            <button type="button" class="hd-icon-btn <?= $t['id_usuario_asignado'] ? '' : 'asignar' ?>"
                                    title="<?= $t['id_usuario_asignado'] ? 'Reasignar técnico' : 'Asignar técnico' ?>"
                                    onclick="event.stopPropagation(); abrirAsignar(<?= (int)$t['id_tickets'] ?>, <?= htmlspecialchars(json_encode($t['codigo_ticket']), ENT_QUOTES) ?>, <?= (int)$t['id_usuario_asignado'] ?>)">
                                <i class="fas fa-user-plus"></i>
                            </button>
                            <?php endif; ?>
                            <a href="ver_ticket.php?id=<?= $t['id_tickets'] ?>" class="hd-icon-btn" title="Ver ticket" onclick="event.stopPropagation();">
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

<!-- ===== MODAL ASIGNAR TÉCNICO ===== -->
<div class="modal fade" id="modalAsignar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar técnico — <span id="asignar-codigo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-asignar-lista">
                <div class="modal-body">
                    <input type="hidden" name="id_ticket" id="asignar-id-ticket">
                    <label class="hd-label">Técnico</label>
                    <select name="id_usuario_asignado" id="asignar-tecnico" class="hd-input" required>
                        <option value="" disabled>Selecciona un técnico</option>
                        <?php foreach ($tecnicos as $tc): ?>
                        <option value="<?= $tc['id_usuario'] ?>"><?= htmlspecialchars($tc['nombres'] . ' ' . $tc['apellidos']) ?> — <?= (int)$tc['activos'] ?> activo<?= (int)$tc['activos'] !== 1 ? 's' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size:0.7rem;color:var(--muted);margin-top:6px;">
                        Un ticket sin técnico pasa a <strong>En Proceso</strong> al asignarlo.
                    </div>
                    <div id="resultado-asignar-lista" style="margin-top:10px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-asignar-lista" class="hd-btn hd-btn-naranja"><i class="fas fa-user-check"></i> Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirAsignar(idTicket, codigo, idTecnicoActual) {
    $('#asignar-id-ticket').val(idTicket);
    $('#asignar-codigo').text('#' + codigo);
    $('#asignar-tecnico').val(idTecnicoActual > 0 ? idTecnicoActual : '');
    $('#resultado-asignar-lista').html('');
    new bootstrap.Modal(document.getElementById('modalAsignar')).show();
}

$('#form-asignar-lista').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btn-asignar-lista');
    btn.prop('disabled', true);
    $.ajax({
        url: '../procesos/asignar_ticket.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            $('#resultado-asignar-lista').html(r);
            btn.prop('disabled', false);
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 900);
        },
        error: function() {
            $('#resultado-asignar-lista').html('<div class="alert alert-danger">Error de conexión.</div>');
            btn.prop('disabled', false);
        }
    });
});
</script>

<?php include_once '../includes/footer2.php'; ?>
