<?php
session_start();
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php"); exit();
}
include('../config/conexion.php');

// Obtener id del usuario
$stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt->bind_param("s", $_SESSION['usser']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();
$id_usuario = $usuario['id_usuario'];

// Filtro por estado
$filtro_estado = $_GET['estado'] ?? 'todos';
$where_estado = $filtro_estado !== 'todos' ? "AND et.nombre = '$filtro_estado'" : '';

$query = $conexion->query("
    SELECT t.id_tickets, t.codigo_ticket, t.asunto, t.fecha_creacion, t.fecha_cierre,
           et.nombre AS estado, p.nombre AS prioridad, p.nivel AS nivel_prioridad,
           ci.nombre AS categoria,
           CONCAT(u.nombres, ' ', u.apellidos) AS tecnico_asignado
    FROM tickets t
    INNER JOIN estados_ticket et ON t.id_estado = et.id_estado_ticket
    INNER JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    INNER JOIN categoria_incidencias ci ON t.id_categoria = ci.id_categoria
    LEFT JOIN usuarios u ON t.id_usuario_asignado = u.id_usuario
    WHERE t.id_usuario_solicitante = $id_usuario
    $where_estado
    ORDER BY t.fecha_creacion DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tickets — HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard2.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .page-wrapper { max-width: 1100px; margin: 0 auto; padding: 24px 20px; }
        .filtro-btn {
            padding: 5px 14px; border-radius: 20px; font-size: 0.75rem;
            font-weight: 500; border: 1.5px solid var(--borde);
            background: #fff; color: var(--muted); cursor: pointer;
            transition: all 0.15s; text-decoration: none; display: inline-block;
        }
        .filtro-btn:hover, .filtro-btn.active {
            border-color: var(--naranja); color: var(--naranja);
            background: #fff7ed;
        }
        .filtro-btn.active { font-weight: 600; }
    </style>
</head>
<body>

<div class="page-wrapper">

    <div class="hd-page-header" style="margin-bottom:16px;">
        <div>
            <div class="hd-page-title">
                <i class="fas fa-ticket-alt" style="color:var(--naranja);margin-right:6px;"></i>
                Mis Tickets
            </div>
            <div class="hd-page-sub">Historial de todas tus solicitudes de soporte</div>
        </div>
        <a href="ticket_nuevo.php" class="hd-btn hd-btn-naranja hd-btn-sm">
            <i class="fas fa-plus"></i> Nuevo Ticket
        </a>
    </div>

    <!-- FILTROS -->
    <div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
        <?php
        $estados_filtro = ['todos'=>'Todos', 'Abierto'=>'Abiertos', 'En Proceso'=>'En Proceso', 'Pendiente'=>'Pendientes', 'Resuelto'=>'Resueltos', 'Cerrado'=>'Cerrados'];
        foreach($estados_filtro as $val => $label):
            $active = $filtro_estado === $val ? 'active' : '';
        ?>
        <a href="?estado=<?= urlencode($val) ?>" class="filtro-btn <?= $active ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Tickets</h5>
            <span style="font-size:0.72rem;color:var(--muted);"><?= $query->num_rows ?> registros</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Asunto</th>
                        <th>Categoría</th>
                        <th>Técnico</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:30px;color:var(--muted);">
                            <i class="fas fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                            No tienes tickets <?= $filtro_estado !== 'todos' ? 'con estado "' . htmlspecialchars($filtro_estado) . '"' : 'registrados' ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php while($row = $query->fetch_assoc()): ?>
                    <tr>
                        <td><span style="font-family:monospace;color:var(--muted);font-size:0.7rem;"><?= htmlspecialchars($row['codigo_ticket']) ?></span></td>
                        <td>
                            <div style="font-weight:500;font-size:0.8rem;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= htmlspecialchars($row['asunto']) ?>
                            </div>
                            <div style="font-size:0.68rem;color:var(--muted);"><?= htmlspecialchars($row['categoria']) ?></div>
                        </td>
                        <td style="color:var(--muted);font-size:0.78rem;"><?= htmlspecialchars($row['categoria']) ?></td>
                        <td>
                            <?php if ($row['tecnico_asignado']): ?>
                            <div style="display:flex;align-items:center;gap:5px;">
                                <div class="hd-avatar" style="width:20px;height:20px;font-size:0.6rem;">
                                    <?= strtoupper(substr($row['tecnico_asignado'], 0, 1)) ?>
                                </div>
                                <span style="font-size:0.78rem;"><?= htmlspecialchars($row['tecnico_asignado']) ?></span>
                            </div>
                            <?php else: ?>
                            <span style="font-size:0.72rem;color:var(--muted);">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $prio_color = match($row['nivel_prioridad']) {
                                1 => 'azul', 2 => 'amarillo', 3 => 'naranja', 4 => 'rojo', default => 'gris'
                            };
                            ?>
                            <span class="hd-tag hd-tag-<?= $prio_color ?>"><?= htmlspecialchars($row['prioridad']) ?></span>
                        </td>
                        <td>
                            <?php
                            $estado_color = match($row['estado']) {
                                'Abierto'    => 'azul',
                                'En Proceso' => 'naranja',
                                'Pendiente'  => 'amarillo',
                                'Resuelto'   => 'verde',
                                'Cerrado'    => 'gris',
                                default      => 'gris'
                            };
                            ?>
                            <span class="hd-tag hd-tag-<?= $estado_color ?>"><?= htmlspecialchars($row['estado']) ?></span>
                        </td>
                        <td style="font-size:0.72rem;color:var(--muted);white-space:nowrap;">
                            <?= date('d/m/Y', strtotime($row['fecha_creacion'])) ?>
                        </td>
                        <td>
                            <a href="ver_ticket.php?id=<?= $row['id_tickets'] ?>" class="hd-btn hd-btn-outline hd-btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
