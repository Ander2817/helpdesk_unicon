<?php
/**
 * includes/lista_tickets_tecnico.php
 * Núcleo compartido para las 4 vistas de listado del técnico.
 * Quien lo incluye debe definir ANTES:
 *   $titulo_pagina      (string)  ej. "Tickets Pendientes"
 *   $icono_pagina       (string)  clase de Font Awesome, ej. "fa-clock"
 *   $filtro_estados     (array|null) IDs de estados_ticket a mostrar, o null = todos
 *   $mensaje_vacio      (string)  texto cuando no hay resultados
 */
if (!defined('NOMBRE_SISTEMA')) require_once '../includes/constants.php';

$stmt_yo = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt_yo->bind_param("s", $usuario_activo);
$stmt_yo->execute();
$yo = $stmt_yo->get_result()->fetch_assoc();
$stmt_yo->close();
$id_tecnico = $yo['id_usuario'] ?? 0;

$sql = "
    SELECT t.id_tickets, t.codigo_ticket, t.asunto, t.fecha_creacion, t.fecha_cierre,
           c.nombre AS categoria_nombre,
           p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel,
           e.nombre AS estado_nombre, e.id_estado_ticket,
           sol.nombres AS sol_nombres, sol.apellidos AS sol_apellidos,
           dep.nombre AS departamento_nombre
    FROM tickets t
    JOIN categoria_incidencias c ON t.id_categoria = c.id_categoria
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
    JOIN usuarios sol ON t.id_usuario_solicitante = sol.id_usuario
    JOIN departamentos dep ON t.id_departamento = dep.id_departamento
    WHERE t.id_usuario_asignado = ?
";

$tipos = "i";
$valores = [$id_tecnico];

if (is_array($filtro_estados) && count($filtro_estados) > 0) {
    $placeholders = implode(',', array_fill(0, count($filtro_estados), '?'));
    $sql .= " AND t.id_estado IN ($placeholders)";
    $tipos .= str_repeat('i', count($filtro_estados));
    $valores = array_merge($valores, $filtro_estados);
}

$sql .= " ORDER BY p.nivel DESC, t.fecha_creacion ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($tipos, ...$valores);
$stmt->execute();
$tickets = $stmt->get_result();

$colores_estado = [1=>'#0284c7', 2=>'#E87722', 3=>'#d97706', 4=>'#16a34a', 5=>'#64748b'];
$colores_nivel  = [1=>'#0284c7', 2=>'#d97706', 3=>'#E87722', 4=>'#dc2626'];
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
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas <?= $icono_pagina ?>" style="color:var(--naranja);margin-right:6px;"></i> <?= htmlspecialchars($titulo_pagina) ?></div>
            <div class="hd-page-sub"><?= $tickets->num_rows ?> ticket<?= $tickets->num_rows !== 1 ? 's' : '' ?></div>
        </div>
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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tickets->num_rows === 0): ?>
                    <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--muted);"><?= htmlspecialchars($mensaje_vacio) ?></td></tr>
                    <?php endif; ?>
                    <?php while ($t = $tickets->fetch_assoc()):
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
                        <td class="acciones">
                            <a href="ver_ticket.php?id=<?= $t['id_tickets'] ?>" class="hd-icon-btn" style="display:inline-flex;width:28px;height:28px;border-radius:6px;border:1px solid var(--borde);align-items:center;justify-content:center;color:var(--muted);text-decoration:none;">
                                <i class="fas fa-wrench"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>
