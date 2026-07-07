<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] < 2) {
    header("Location: ../../index.php"); exit();
}
include('../../config/conexion.php');

$query = $conexion->query("
    SELECT t.id_tickets, t.codigo_ticket, t.asunto, t.descripcion, t.fecha_creacion,
           p.nombre AS prioridad, p.nivel AS nivel_prioridad,
           ci.nombre AS categoria,
           d.nombre AS departamento,
           CONCAT(us.nombres, ' ', us.apellidos) AS solicitante
    FROM tickets t
    INNER JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    INNER JOIN categoria_incidencias ci ON t.id_categoria = ci.id_categoria
    INNER JOIN departamentos d ON t.id_departamento = d.id_departamento
    INNER JOIN usuarios us ON t.id_usuario_solicitante = us.id_usuario
    WHERE t.id_usuario_asignado IS NULL AND t.id_estado = 1
    ORDER BY p.nivel DESC, t.fecha_creacion ASC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets Disponibles — HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard2.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .page-wrapper { max-width: 1100px; margin: 0 auto; padding: 24px 20px; }
        .ticket-card {
            background: #fff; border-radius: 10px; border: 1.5px solid var(--borde);
            padding: 16px 18px; margin-bottom: 10px;
            transition: all 0.2s; display: flex; align-items: flex-start; gap: 14px;
        }
        .ticket-card:hover { border-color: var(--naranja); box-shadow: 0 2px 12px rgba(232,119,34,0.1); }
        .ticket-prioridad-bar {
            width: 4px; border-radius: 4px; align-self: stretch; flex-shrink: 0; min-height: 60px;
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <div class="hd-page-header" style="margin-bottom:16px;">
        <div>
            <div class="hd-page-title">
                <i class="fas fa-inbox" style="color:var(--naranja);margin-right:6px;"></i>
                Tickets Disponibles
            </div>
            <div class="hd-page-sub">Tickets sin técnico asignado — Toma los que puedas atender</div>
        </div>
        <span class="hd-rol-badge"><i class="fas fa-wrench"></i> <?= $query->num_rows ?> disponibles</span>
    </div>

    <?php if ($query->num_rows === 0): ?>
    <div class="hd-card">
        <div class="hd-card-body" style="text-align:center;padding:40px;color:var(--muted);">
            <i class="fas fa-check-circle" style="font-size:2rem;color:var(--success);margin-bottom:10px;display:block;"></i>
            <div style="font-weight:600;">¡Todo al día!</div>
            <div style="font-size:0.82rem;margin-top:4px;">No hay tickets sin atender en este momento.</div>
        </div>
    </div>
    <?php else: ?>

    <div id="lista-tickets">
        <?php while($row = $query->fetch_assoc()):
            $prio_color = match($row['nivel_prioridad']) {
                1 => '#0284c7', 2 => '#d97706', 3 => '#E87722', 4 => '#dc2626', default => '#94a3b8'
            };
            $prio_tag = match($row['nivel_prioridad']) {
                1 => 'azul', 2 => 'amarillo', 3 => 'naranja', 4 => 'rojo', default => 'gris'
            };
        ?>
        <div class="ticket-card" id="ticket-<?= $row['id_tickets'] ?>">
            <div class="ticket-prioridad-bar" style="background:<?= $prio_color ?>;"></div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div>
                        <span style="font-family:monospace;font-size:0.7rem;color:var(--muted);"><?= htmlspecialchars($row['codigo_ticket']) ?></span>
                        <div style="font-weight:600;font-size:0.88rem;color:var(--texto);margin-top:2px;">
                            <?= htmlspecialchars($row['asunto']) ?>
                        </div>
                        <div style="font-size:0.75rem;color:var(--muted);margin-top:4px;display:flex;gap:12px;flex-wrap:wrap;">
                            <span><i class="fas fa-building" style="margin-right:4px;"></i><?= htmlspecialchars($row['departamento']) ?></span>
                            <span><i class="fas fa-tag" style="margin-right:4px;"></i><?= htmlspecialchars($row['categoria']) ?></span>
                            <span><i class="fas fa-user" style="margin-right:4px;"></i><?= htmlspecialchars($row['solicitante']) ?></span>
                            <span><i class="fas fa-clock" style="margin-right:4px;"></i><?= date('d/m/Y H:i', strtotime($row['fecha_creacion'])) ?></span>
                        </div>
                        <div style="font-size:0.75rem;color:var(--muted);margin-top:6px;max-width:500px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars(substr($row['descripcion'], 0, 120)) ?>...
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;flex-shrink:0;">
                        <span class="hd-tag hd-tag-<?= $prio_tag ?>"><?= htmlspecialchars($row['prioridad']) ?></span>
                        <button onclick="tomarTicket(<?= $row['id_tickets'] ?>, this)"
                                class="hd-btn hd-btn-naranja hd-btn-sm">
                            <i class="fas fa-hand-pointer"></i> Tomar Ticket
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function tomarTicket(id, btn) {
    if (!confirm('¿Confirmas que tomarás este ticket? Quedará asignado a ti.')) return;

    $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Asignando...');

    $.ajax({
        url: '../procesos/tomar_ticket.php',
        type: 'POST',
        data: { id: id },
        success: function(r) {
            if (r.trim() === 'success') {
                $('#ticket-' + id).fadeOut(400, function() {
                    $(this).remove();
                    if ($('#lista-tickets .ticket-card').length === 0) {
                        $('#lista-tickets').html(
                            '<div class="hd-card"><div class="hd-card-body" style="text-align:center;padding:40px;color:var(--muted);">' +
                            '<i class="fas fa-check-circle" style="font-size:2rem;color:var(--success);margin-bottom:10px;display:block;"></i>' +
                            '<div style="font-weight:600;">¡Todo asignado!</div></div></div>'
                        );
                    }
                });
            } else {
                alert('Error: ' + r);
                $(btn).prop('disabled', false).html('<i class="fas fa-hand-pointer"></i> Tomar Ticket');
            }
        },
        error: function() {
            alert('Error de conexión.');
            $(btn).prop('disabled', false).html('<i class="fas fa-hand-pointer"></i> Tomar Ticket');
        }
    });
}
</script>
</body>
</html>
