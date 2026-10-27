<?php
/**
 * includes/ticket_detalle.php
 * Núcleo compartido de la vista de detalle de ticket.
 * Lo incluyen admin/ver_ticket.php, tecnico/ver_ticket.php y usuario/ver_ticket.php
 * DESPUÉS de header2.php + sidebar.php, y ANTES de footer2.php.
 *
 * Variables que debe tener listas quien lo incluye:
 *   $conexion, $usuario_activo, $rol_id  (ya los pone header2.php)
 */

if (!defined('NOMBRE_SISTEMA')) require_once '../includes/constants.php';
require_once __DIR__ . '/flujo_tickets.php';
require_once __DIR__ . '/adjuntos.php';

$id_ticket = (int)($_GET['id'] ?? 0);

if ($id_ticket <= 0) {
    echo "<main class='hd-main'><div class='alert alert-danger'>Ticket inválido.</div></main>";
    include_once '../includes/footer2.php';
    exit();
}

// ID numérico del usuario actual (la sesión guarda el login, no el ID)
$stmt_yo = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt_yo->bind_param("s", $usuario_activo);
$stmt_yo->execute();
$fila_yo = $stmt_yo->get_result()->fetch_assoc();
$stmt_yo->close();
$id_usuario_actual = (int)($fila_yo['id_usuario'] ?? 0);

// Ticket completo con todos sus datos relacionados
$stmt = $conexion->prepare("
    SELECT t.*,
           c.nombre AS categoria_nombre,
           p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel,
           e.nombre AS estado_nombre,
           sol.nombres AS sol_nombres, sol.apellidos AS sol_apellidos, sol.correo AS sol_correo,
           dep.nombre AS departamento_nombre,
           asig.nombres AS asig_nombres, asig.apellidos AS asig_apellidos,
           inv.codigo_equipo, inv.tipo_equipo, inv.marca AS inv_marca, inv.modelo AS inv_modelo
    FROM tickets t
    JOIN categoria_incidencias c ON t.id_categoria = c.id_categoria
    JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
    JOIN usuarios sol ON t.id_usuario_solicitante = sol.id_usuario
    JOIN departamentos dep ON t.id_departamento = dep.id_departamento
    LEFT JOIN usuarios asig ON t.id_usuario_asignado = asig.id_usuario
    LEFT JOIN inventario_tecnologico inv ON t.id_inventario = inv.id_inventario
    WHERE t.id_tickets = ?
");
$stmt->bind_param("i", $id_ticket);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) {
    echo "<main class='hd-main'><div class='alert alert-danger'>El ticket solicitado no existe.</div></main>";
    include_once '../includes/footer2.php';
    exit();
}

// ---- Permisos según rol ----
$es_solicitante = ($id_usuario_actual > 0 && (int)$ticket['id_usuario_solicitante'] === $id_usuario_actual);
$es_asignado    = ($id_usuario_actual > 0 && (int)$ticket['id_usuario_asignado'] === $id_usuario_actual);
$sin_tecnico    = empty($ticket['id_usuario_asignado']);
$ticket_activo  = !in_array((int)$ticket['id_estado'], [TICKET_RESUELTO, TICKET_CERRADO], true);

$puede_ver = false;
$puede_gestionar = false; // cambiar estado / marcar solución
$puede_reasignar = false; // asignar técnico: solo admin

if ($rol_id === ROL_ADMIN) {
    $puede_ver = true;
    $puede_gestionar = true;
    $puede_reasignar = true;
} elseif ($rol_id === ROL_TECNICO) {
    // Ve los tickets que le asignaron y los que él mismo creó; solo gestiona los asignados.
    $puede_ver = ($es_asignado || $es_solicitante);
    $puede_gestionar = $es_asignado;
} elseif ($rol_id === ROL_USUARIO) {
    $puede_ver = $es_solicitante;
}

if (!$puede_ver) {
    echo "<main class='hd-main'><div class='alert alert-danger'><i class='fas fa-lock me-2'></i>No tienes permiso para ver este ticket.</div></main>";
    include_once '../includes/footer2.php';
    exit();
}

// Archivos adjuntos del ticket
$stmt_adj = $conexion->prepare("SELECT id_adjunto, nombre_archivo, ruta_archivo, tipo_archivo, fecha_subida FROM adjuntos WHERE id_ticket = ? ORDER BY id_adjunto ASC");
$stmt_adj->bind_param("i", $id_ticket);
$stmt_adj->execute();
$adjuntos_ticket = $stmt_adj->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_adj->close();

// Comentarios
$comentarios = $conexion->prepare("
    SELECT co.id_comentario, co.comentario, co.fecha_comentario, u.nombres, u.apellidos, u.id_rol
    FROM comentarios co
    JOIN usuarios u ON co.id_usuario = u.id_usuario
    WHERE co.id_ticket = ?
    ORDER BY co.fecha_comentario ASC
");
$comentarios->bind_param("i", $id_ticket);
$comentarios->execute();
$comentarios = $comentarios->get_result();

// Historial (auditoría de cambios)
$historial = $conexion->prepare("
    SELECT h.campo_modificado, h.valor_anterior, h.valor_nuevo, h.fecha_cambio, u.nombres, u.apellidos
    FROM historial_ticket h
    JOIN usuarios u ON h.id_usuario = u.id_usuario
    WHERE h.id_ticket = ?
    ORDER BY h.fecha_cambio DESC
");
$historial->bind_param("i", $id_ticket);
$historial->execute();
$historial = $historial->get_result();

// Técnicos activos con su carga de trabajo actual (solo el admin los necesita, para asignar)
$tecnicos = null;
if ($puede_reasignar && $ticket_activo) {
    $tecnicos = $conexion->query("
        SELECT u.id_usuario, u.nombres, u.apellidos, COUNT(t.id_tickets) AS activos
        FROM usuarios u
        LEFT JOIN tickets t ON t.id_usuario_asignado = u.id_usuario
                           AND t.id_estado IN (" . TICKET_PROCESO . "," . TICKET_PENDIENTE . ")
        WHERE u.id_rol = " . ROL_TECNICO . " AND u.estado = 'activo'
        GROUP BY u.id_usuario
        ORDER BY u.nombres ASC
    ");
}

// Estados a los que se puede pasar desde el actual (misma regla que valida el servidor).
// Sin técnico asignado no hay nada que gestionar: primero el admin debe asignarlo.
$opciones_estado = ($puede_gestionar && !$sin_tecnico)
    ? estados_siguientes((int)$ticket['id_estado'], $rol_id)
    : [];

$colores_estado    = [1=>'#0284c7', 2=>'#E87722', 3=>'#d97706', 4=>'#16a34a', 5=>'#64748b'];
$colores_nivel     = [1=>'#0284c7', 2=>'#d97706', 3=>'#E87722', 4=>'#dc2626'];
$color_estado_hoy  = $colores_estado[$ticket['id_estado']] ?? '#94a3b8';
$color_prio_hoy    = $colores_nivel[$ticket['prioridad_nivel']] ?? '#94a3b8';

$link_volver = match($rol_id) {
    ROL_ADMIN   => 'dashboard.php',
    ROL_TECNICO => 'dashboard.php',
    default     => 'dashboard.php',
};
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .hd-ticket-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        flex-wrap: wrap; gap: 14px; margin-bottom: 18px;
    }
    .hd-ticket-codigo { font-size: 0.78rem; color: var(--muted); font-weight: 600; letter-spacing: 0.5px; }
    .hd-ticket-asunto { font-size: 1.3rem; font-weight: 700; color: var(--azul); margin-top: 2px; }
    .hd-badge-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px; border-radius: 20px; font-size: 0.74rem; font-weight: 600;
    }
    .hd-badge-pill .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .hd-grid-detalle { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; align-items: start; }
    @media (max-width: 900px) { .hd-grid-detalle { grid-template-columns: 1fr; } }
    .hd-meta-list { display: flex; flex-direction: column; gap: 12px; }
    .hd-meta-item { display: flex; justify-content: space-between; font-size: 0.8rem; padding: 8px 0; border-bottom: 1px solid var(--borde); }
    .hd-meta-item:last-child { border-bottom: none; }
    .hd-meta-item span:first-child { color: var(--muted); }
    .hd-meta-item span:last-child { color: var(--texto); font-weight: 600; text-align: right; }
    .hd-descripcion-box {
        background: #f8fafc; border: 1px solid var(--borde); border-radius: 8px;
        padding: 14px 16px; font-size: 0.85rem; color: var(--texto); line-height: 1.6; white-space: pre-wrap;
    }
    .hd-comentario {
        display: flex; gap: 10px; padding: 12px 0; border-bottom: 1px solid var(--borde);
    }
    .hd-comentario:last-child { border-bottom: none; }
    .hd-comentario-avatar {
        width: 32px; height: 32px; border-radius: 50%; background: var(--azul); color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 700; flex-shrink: 0;
    }
    .hd-comentario-avatar.rol-2 { background: #d97706; }
    .hd-comentario-avatar.rol-3 { background: var(--naranja); }
    .hd-comentario-nombre { font-size: 0.8rem; font-weight: 600; color: var(--azul); }
    .hd-comentario-fecha { font-size: 0.68rem; color: var(--muted); margin-left: 8px; }
    .hd-comentario-texto { font-size: 0.82rem; color: var(--texto); margin-top: 2px; line-height: 1.5; }
    .hd-historial-item { display: flex; gap: 10px; font-size: 0.76rem; padding: 8px 0; border-bottom: 1px dashed var(--borde); }
    .hd-historial-item:last-child { border-bottom: none; }
    .hd-historial-icon { color: var(--muted); margin-top: 2px; }
    .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
    .hd-input {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--borde); border-radius: 7px;
        font-family: 'Poppins', sans-serif; font-size: 0.83rem; color: var(--texto); background: #fff;
    }
    .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); outline: none; }
    textarea.hd-input { resize: vertical; min-height: 70px; }
    .hd-adjuntos { margin-top: 16px; }
    .hd-adj-miniaturas { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
    .hd-adj-thumb {
        width: 110px; height: 82px; border-radius: 8px; border: 1px solid var(--borde); overflow: hidden;
        display: block; background: #f1f5f9; position: relative;
    }
    .hd-adj-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.2s; }
    .hd-adj-thumb:hover img { transform: scale(1.06); }
    .hd-adj-fila {
        display: flex; align-items: center; gap: 10px; padding: 8px 12px; margin-bottom: 6px;
        border: 1px solid var(--borde); border-radius: 8px; background: #fff; font-size: 0.8rem;
    }
    .hd-adj-fila > i { color: var(--naranja); font-size: 1rem; width: 18px; text-align: center; }
    .hd-adj-fila .nombre { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--texto); font-weight: 500; }
    .hd-adj-fila .peso { color: var(--muted); font-size: 0.72rem; flex-shrink: 0; }
    .hd-adj-fila a.descargar {
        color: var(--muted); border: 1px solid var(--borde); border-radius: 6px; padding: 3px 9px;
        font-size: 0.72rem; text-decoration: none; flex-shrink: 0;
    }
    .hd-adj-fila a.descargar:hover { color: var(--naranja); border-color: var(--naranja); }
</style>

<main class="hd-main">

    <div class="hd-page-header" style="margin-bottom:14px;">
        <a href="<?= $link_volver ?>" class="hd-btn hd-btn-outline hd-btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="hd-card" style="padding:20px 22px;margin-bottom:18px;">
        <div class="hd-ticket-header">
            <div>
                <div class="hd-ticket-codigo">#<?= htmlspecialchars($ticket['codigo_ticket']) ?></div>
                <div class="hd-ticket-asunto"><?= htmlspecialchars($ticket['asunto']) ?></div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <span class="hd-badge-pill" style="background:color-mix(in srgb, <?= $color_estado_hoy ?> 12%, white); color:<?= $color_estado_hoy ?>;">
                    <span class="dot" style="background:<?= $color_estado_hoy ?>;"></span> <?= htmlspecialchars($ticket['estado_nombre']) ?>
                </span>
                <span class="hd-badge-pill" style="background:color-mix(in srgb, <?= $color_prio_hoy ?> 12%, white); color:<?= $color_prio_hoy ?>;">
                    <span class="dot" style="background:<?= $color_prio_hoy ?>;"></span> <?= htmlspecialchars($ticket['prioridad_nombre']) ?>
                </span>
            </div>
        </div>

        <div class="hd-grid-detalle">
            <div>
                <div class="hd-label" style="margin-bottom:8px;">Descripción</div>
                <div class="hd-descripcion-box"><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></div>

                <?php if ($ticket['codigo_equipo']): ?>
                <div style="margin-top:14px;font-size:0.8rem;color:var(--muted);">
                    <i class="fas fa-server" style="color:var(--naranja);margin-right:5px;"></i>
                    Equipo relacionado: <strong style="color:var(--texto);"><?= htmlspecialchars($ticket['codigo_equipo']) ?></strong>
                    — <?= htmlspecialchars($ticket['tipo_equipo']) ?> <?= htmlspecialchars($ticket['inv_marca']) ?> <?= htmlspecialchars($ticket['inv_modelo']) ?>
                </div>
                <?php endif; ?>

                <?php if ($adjuntos_ticket): ?>
                <div class="hd-adjuntos">
                    <div class="hd-label" style="margin-bottom:8px;"><i class="fas fa-paperclip" style="color:var(--naranja);"></i> Archivos adjuntos (<?= count($adjuntos_ticket) ?>)</div>

                    <?php $imagenes = array_filter($adjuntos_ticket, fn($a) => adjunto_es_imagen($a['tipo_archivo'])); ?>
                    <?php if ($imagenes): ?>
                    <div class="hd-adj-miniaturas">
                        <?php foreach ($imagenes as $a): ?>
                        <a class="hd-adj-thumb" href="../procesos/descargar_adjunto.php?id=<?= (int)$a['id_adjunto'] ?>&ver=1" target="_blank" rel="noopener" title="<?= htmlspecialchars($a['nombre_archivo']) ?>">
                            <img src="../procesos/descargar_adjunto.php?id=<?= (int)$a['id_adjunto'] ?>&ver=1" alt="<?= htmlspecialchars($a['nombre_archivo']) ?>" loading="lazy">
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php foreach ($adjuntos_ticket as $a):
                        $ruta_disco = adjuntos_directorio() . '/' . $a['ruta_archivo'];
                        $peso = is_file($ruta_disco) ? formato_bytes((int)filesize($ruta_disco)) : 'no disponible';
                    ?>
                    <div class="hd-adj-fila">
                        <i class="fas <?= adjunto_icono($a['tipo_archivo']) ?>"></i>
                        <span class="nombre" title="<?= htmlspecialchars($a['nombre_archivo']) ?>"><?= htmlspecialchars($a['nombre_archivo']) ?></span>
                        <span class="peso"><?= $peso ?></span>
                        <a class="descargar" href="../procesos/descargar_adjunto.php?id=<?= (int)$a['id_adjunto'] ?>"><i class="fas fa-download"></i> Descargar</a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($ticket['solucion']): ?>
                <div style="margin-top:16px;">
                    <div class="hd-label" style="color:var(--success);">✓ Solución aplicada</div>
                    <div class="hd-descripcion-box" style="background:rgba(22,163,74,0.05);border-color:rgba(22,163,74,0.2);"><?= nl2br(htmlspecialchars($ticket['solucion'])) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="hd-meta-list">
                <div class="hd-meta-item"><span>Categoría</span><span><?= htmlspecialchars($ticket['categoria_nombre']) ?></span></div>
                <div class="hd-meta-item"><span>Solicitante</span><span><?= htmlspecialchars($ticket['sol_nombres'] . ' ' . $ticket['sol_apellidos']) ?></span></div>
                <div class="hd-meta-item"><span>Departamento</span><span><?= htmlspecialchars($ticket['departamento_nombre']) ?></span></div>
                <div class="hd-meta-item"><span>Técnico asignado</span><span><?= $ticket['asig_nombres'] ? htmlspecialchars($ticket['asig_nombres'] . ' ' . $ticket['asig_apellidos']) : 'Sin asignar' ?></span></div>
                <div class="hd-meta-item"><span>Creado</span><span><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></span></div>
                <?php if ($ticket['fecha_cierre']): ?>
                <div class="hd-meta-item"><span>Cerrado</span><span><?= date('d/m/Y H:i', strtotime($ticket['fecha_cierre'])) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($puede_reasignar && $ticket_activo): ?>
    <!-- ASIGNACIÓN (solo administrador): es quien decide qué técnico atiende cada ticket -->
    <div class="hd-card" style="padding:18px 22px;margin-bottom:18px;<?= $sin_tecnico ? 'border-left:4px solid var(--naranja);' : '' ?>">
        <h5 class="hd-card-title" style="margin-bottom:6px;"><i class="fas fa-user-cog"></i> Asignación</h5>
        <?php if ($sin_tecnico): ?>
        <div style="font-size:0.78rem;color:var(--muted);margin-bottom:12px;">
            Este ticket aún no tiene técnico. Al asignarlo pasará automáticamente a <strong>En Proceso</strong>.
        </div>
        <?php else: ?>
        <div style="font-size:0.78rem;color:var(--muted);margin-bottom:12px;">
            Técnico actual: <strong><?= htmlspecialchars($ticket['asig_nombres'] . ' ' . $ticket['asig_apellidos']) ?></strong>. Puedes reasignarlo a otro técnico.
        </div>
        <?php endif; ?>
        <form id="form-asignar">
            <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
            <div style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end;">
                <div>
                    <label class="hd-label">Técnico</label>
                    <select name="id_usuario_asignado" class="hd-input" required>
                        <option value="" disabled <?= $sin_tecnico ? 'selected' : '' ?>>Selecciona un técnico</option>
                        <?php while ($t = $tecnicos->fetch_assoc()): ?>
                        <option value="<?= $t['id_usuario'] ?>" <?= (int)$t['id_usuario'] === (int)$ticket['id_usuario_asignado'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nombres'] . ' ' . $t['apellidos']) ?> — <?= (int)$t['activos'] ?> activo<?= (int)$t['activos'] !== 1 ? 's' : '' ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="hd-btn hd-btn-naranja"><i class="fas fa-user-check"></i> <?= $sin_tecnico ? 'Asignar' : 'Reasignar' ?></button>
            </div>
            <div id="resultado-asignar" style="margin-top:10px;"></div>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($puede_gestionar && !empty($opciones_estado)): ?>
    <div class="hd-card" style="padding:18px 22px;margin-bottom:18px;">
        <h5 class="hd-card-title" style="margin-bottom:12px;"><i class="fas fa-tasks"></i> Gestionar Ticket</h5>
        <form id="form-estado-ticket">
            <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
            <div>
                <label class="hd-label">Cambiar estado a</label>
                <select name="nuevo_estado" id="nuevo-estado" class="hd-input" required onchange="toggleSolucion()">
                    <option value="" disabled selected>Selecciona un estado</option>
                    <?php foreach ($opciones_estado as $op): ?>
                    <option value="<?= $op ?>"><?= htmlspecialchars(nombre_estado($op)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="wrap-solucion" style="display:none;margin-top:12px;">
                <label class="hd-label">Descripción de la solución <span style="color:var(--danger);">*</span></label>
                <textarea name="solucion" id="solucion-texto" class="hd-input" maxlength="2000" placeholder="Explica cómo se resolvió el problema..."></textarea>
            </div>

            <div id="resultado-estado" style="margin-top:10px;"></div>

            <div style="margin-top:12px;">
                <button type="submit" class="hd-btn hd-btn-naranja"><i class="fas fa-check"></i> Aplicar Cambios</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="hd-grid-detalle">
        <!-- COMENTARIOS -->
        <div class="hd-card" style="padding:18px 22px;">
            <h5 class="hd-card-title" style="margin-bottom:10px;"><i class="fas fa-comments"></i> Comentarios</h5>

            <div id="lista-comentarios" style="margin-bottom:14px;">
                <?php if ($comentarios->num_rows === 0): ?>
                <div style="color:var(--muted);font-size:0.8rem;padding:10px 0;">Aún no hay comentarios.</div>
                <?php endif; ?>
                <?php while ($c = $comentarios->fetch_assoc()): ?>
                <div class="hd-comentario">
                    <div class="hd-comentario-avatar rol-<?= $c['id_rol'] ?>"><?= strtoupper(substr($c['nombres'], 0, 1)) ?></div>
                    <div>
                        <span class="hd-comentario-nombre"><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></span>
                        <span class="hd-comentario-fecha"><?= date('d/m/Y H:i', strtotime($c['fecha_comentario'])) ?></span>
                        <div class="hd-comentario-texto"><?= nl2br(htmlspecialchars($c['comentario'])) ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <form id="form-comentario">
                <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                <textarea name="comentario" id="nuevo-comentario" class="hd-input" maxlength="1000" placeholder="Escribe un comentario..." required></textarea>
                <div id="resultado-comentario" style="margin-top:8px;"></div>
                <div style="margin-top:8px;text-align:right;">
                    <button type="submit" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-paper-plane"></i> Comentar</button>
                </div>
            </form>
        </div>

        <!-- HISTORIAL -->
        <div class="hd-card" style="padding:18px 22px;">
            <h5 class="hd-card-title" style="margin-bottom:10px;"><i class="fas fa-history"></i> Historial</h5>
            <?php if ($historial->num_rows === 0): ?>
            <div style="color:var(--muted);font-size:0.8rem;">Sin cambios registrados aún.</div>
            <?php endif; ?>
            <?php while ($h = $historial->fetch_assoc()): ?>
            <div class="hd-historial-item">
                <i class="fas fa-circle hd-historial-icon" style="font-size:0.4rem;margin-top:6px;"></i>
                <div>
                    <div><strong><?= htmlspecialchars($h['nombres']) ?></strong> cambió <strong><?= htmlspecialchars($h['campo_modificado']) ?></strong>
                    <?php if ($h['valor_anterior']): ?> de "<?= htmlspecialchars($h['valor_anterior']) ?>"<?php endif; ?> a "<?= htmlspecialchars($h['valor_nuevo']) ?>"</div>
                    <div style="color:var(--muted);font-size:0.68rem;"><?= date('d/m/Y H:i', strtotime($h['fecha_cambio'])) ?></div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

</main>

<script>
function toggleSolucion() {
    const val = document.getElementById('nuevo-estado').value;
    document.getElementById('wrap-solucion').style.display = (val == '<?= TICKET_RESUELTO ?>') ? 'block' : 'none';
    document.getElementById('solucion-texto').required = (val == '<?= TICKET_RESUELTO ?>');
}

$('#form-estado-ticket').on('submit', function(e) {
    e.preventDefault();
    const resultado = $('#resultado-estado');

    $.ajax({
        url: '../procesos/cambiar_estado_ticket.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            resultado.html(r);
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 1200);
        },
        error: function() {
            resultado.html('<div class="alert alert-danger">Error de conexión.</div>');
        }
    });
});

// Asignar / reasignar técnico (solo existe el formulario para el administrador)
$('#form-asignar').on('submit', function(e) {
    e.preventDefault();
    const resultado = $('#resultado-asignar');

    $.ajax({
        url: '../procesos/asignar_ticket.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            resultado.html(r);
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 1000);
        },
        error: function() {
            resultado.html('<div class="alert alert-danger">Error de conexión.</div>');
        }
    });
});

$('#form-comentario').on('submit', function(e) {
    e.preventDefault();
    const resultado = $('#resultado-comentario');

    $.ajax({
        url: '../procesos/comentar_ticket.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            resultado.html(r);
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 800);
        },
        error: function() {
            resultado.html('<div class="alert alert-danger">Error de conexión.</div>');
        }
    });
});
</script>
