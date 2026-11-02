<?php
// tecnico/tickets_sin_asignar.php — Cola de tickets sin técnico. Solo si el admin activó «Tomar tickets sin asignar».
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 2) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/constants.php';
require_once '../includes/permisos.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

if (!rol_tiene_permiso($conexion, ROL_TECNICO, 'tickets.tomar')) {
    header('Location: dashboard.php'); exit();   // permiso apagado: se comporta como antes
}

$tickets = $conexion->query("
    SELECT t.id_tickets, t.codigo_ticket, t.asunto, t.fecha_creacion,
           c.nombre AS categoria, p.nombre AS prioridad, p.nivel AS nivel,
           CONCAT(s.nombres, ' ', s.apellidos) AS solicitante, d.nombre AS departamento
    FROM tickets t
    JOIN categoria_incidencias c ON t.id_categoria = c.id_categoria
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    JOIN usuarios s ON t.id_usuario_solicitante = s.id_usuario
    JOIN departamentos d ON t.id_departamento = d.id_departamento
    WHERE t.id_usuario_asignado IS NULL AND t.id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")
    ORDER BY p.nivel DESC, t.fecha_creacion ASC
");

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
$colores = [1=>'#0284c7', 2=>'#d97706', 3=>'#E87722', 4=>'#dc2626'];
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<style>
    .hd-prio { display:inline-flex; align-items:center; gap:6px; font-size:.74rem; font-weight:600; }
    .hd-prio .dot { width:8px; height:8px; border-radius:50%; }
</style>

<main class="hd-main">
    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-inbox" style="color:var(--naranja);margin-right:6px;"></i> Tickets sin asignar</div>
            <div class="hd-page-sub"><?= $tickets->num_rows ?> ticket<?= $tickets->num_rows !== 1 ? 's' : '' ?> esperando técnico — los más urgentes primero</div>
        </div>
    </div>

    <div id="resultado-tomar"></div>

    <div class="hd-card">
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead><tr><th>Ticket</th><th>Asunto</th><th>Categoría</th><th>Prioridad</th><th>Solicitante</th><th>Creado</th><th></th></tr></thead>
                <tbody>
                <?php if ($tickets->num_rows === 0): ?>
                    <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--muted);">No hay tickets pendientes de asignar. ¡Todo atendido!</td></tr>
                <?php endif; ?>
                <?php while ($t = $tickets->fetch_assoc()): $col = $colores[(int)$t['nivel']] ?? '#94a3b8'; ?>
                    <tr>
                        <td style="font-weight:600;color:var(--azul);"><?= htmlspecialchars($t['codigo_ticket']) ?></td>
                        <td><?= htmlspecialchars($t['asunto']) ?></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($t['categoria']) ?></td>
                        <td><span class="hd-prio" style="color:<?= $col ?>;"><span class="dot" style="background:<?= $col ?>;"></span><?= htmlspecialchars($t['prioridad']) ?></span></td>
                        <td><?= htmlspecialchars($t['solicitante']) ?><div style="font-size:.7rem;color:var(--muted);"><?= htmlspecialchars($t['departamento']) ?></div></td>
                        <td style="color:var(--muted);"><?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></td>
                        <td style="white-space:nowrap;">
                            <a href="ver_ticket.php?id=<?= (int)$t['id_tickets'] ?>" class="hd-btn hd-btn-outline hd-btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                            <button class="hd-btn hd-btn-naranja hd-btn-sm btn-tomar" data-id="<?= (int)$t['id_tickets'] ?>"><i class="fas fa-hand-paper"></i> Tomar</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
$(document).on('click', '.btn-tomar', function () {
    const btn = $(this);
    if (!confirm('¿Tomar este ticket? Quedará asignado a ti.')) return;
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $.post('../procesos/tomar_ticket.php', { id_ticket: btn.data('id') })
        .done(function (r) {
            $('#resultado-tomar').html(r);
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 1200);
            else btn.prop('disabled', false).html('<i class="fas fa-hand-paper"></i> Tomar');
        })
        .fail(function (x) {
            $('#resultado-tomar').html(x.responseText || '<div class="alert alert-danger">Error de conexión.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-hand-paper"></i> Tomar');
        });
});
</script>
<?php include_once '../includes/footer2.php'; ?>
