<?php
// includes/equipo_historial.php — Ficha de un equipo: datos, historial de mantenimiento y tickets relacionados.
// Lo incluyen admin/inventario_historial.php y tecnico/inventario_historial.php (?id=ID_EQUIPO).
require_once '../includes/permisos.php';
require_once '../includes/notificaciones.php';

if (!rol_tiene_permiso($conexion, $rol_id, 'inventario.ver')) { echo "<main class='hd-main'><div class='alert alert-warning'>No tienes acceso al inventario.</div></main>"; return; }
$puede_registrar = rol_tiene_permiso($conexion, $rol_id, 'inventario.mantenimiento');
$yo_id = noti_id_actual($conexion);
$id_eq = (int)($_GET['id'] ?? 0);

$q = $conexion->prepare("SELECT i.*, d.nombre AS departamento FROM inventario_tecnologico i LEFT JOIN departamentos d ON i.id_departamento = d.id_departamento WHERE i.id_inventario = ?");
$q->bind_param("i", $id_eq); $q->execute();
$eq = $q->get_result()->fetch_assoc(); $q->close();
if (!$eq) { echo "<main class='hd-main'><div class='alert alert-danger'>El equipo no existe.</div><a href='inventario.php' class='hd-btn hd-btn-outline hd-btn-sm'>Volver</a></main>"; return; }

$mants = []; $falta_migracion = false;
try {
    $q = $conexion->prepare("SELECT m.*, CONCAT(u.nombres, ' ', u.apellidos) AS autor FROM mantenimientos_equipo m LEFT JOIN usuarios u ON m.id_usuario = u.id_usuario WHERE m.id_inventario = ? ORDER BY m.fecha DESC, m.id_mantenimiento DESC");
    $q->bind_param("i", $id_eq); $q->execute();
    $mants = $q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close();
} catch (Throwable $e) { $falta_migracion = true; }

$q = $conexion->prepare("SELECT t.id_tickets, t.codigo_ticket, t.asunto, t.fecha_creacion, t.id_usuario_asignado, e.nombre AS estado
                         FROM tickets t JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
                         WHERE t.id_inventario = ? ORDER BY t.fecha_creacion DESC LIMIT 50");
$q->bind_param("i", $id_eq); $q->execute();
$tickets_eq = $q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close();

$proximo = null;
foreach ($mants as $m) { if ($m['proximo'] && ($proximo === null || $m['proximo'] > $proximo)) $proximo = $m['proximo']; }
$tipos = ['preventivo' => 'Preventivo', 'correctivo' => 'Correctivo', 'actualizacion' => 'Actualización', 'otro' => 'Otro'];
$col_tipo = ['preventivo' => '#0284c7', 'correctivo' => '#dc2626', 'actualizacion' => '#7c3aed', 'otro' => '#64748b'];
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<main class="hd-main">
    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-tools" style="color:var(--naranja);margin-right:6px;"></i> <?= htmlspecialchars($eq['codigo_equipo']) ?> — <?= htmlspecialchars($eq['tipo_equipo']) ?></div>
            <div class="hd-page-sub"><?= htmlspecialchars(trim($eq['marca'] . ' ' . $eq['modelo'])) ?> · Serial <?= htmlspecialchars($eq['serial'] ?? '—') ?> · <?= htmlspecialchars($eq['departamento'] ?? '—') ?> · <?= htmlspecialchars($eq['ubicacion'] ?? '—') ?></div>
        </div>
        <a href="inventario.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-arrow-left"></i> Volver al inventario</a>
    </div>

    <?php if ($proximo): ?>
    <div class="alert <?= $proximo < date('Y-m-d') ? 'alert-danger' : 'alert-info' ?>" style="font-size:.82rem;">
        <i class="fas fa-calendar-check me-2"></i>Próximo mantenimiento programado: <strong><?= date('d/m/Y', strtotime($proximo)) ?></strong><?= $proximo < date('Y-m-d') ? ' — <strong>vencido</strong>' : '' ?>
    </div>
    <?php endif; ?>
    <?php if ($falta_migracion): ?>
    <div class="alert alert-warning">Falta crear la tabla del historial. Ejecuta <code>DB/migracion_entrega4.sql</code> en phpMyAdmin.</div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:minmax(0,1.5fr) minmax(0,1fr);gap:18px;align-items:start;">
        <div class="hd-card" style="padding:18px 22px;">
            <h5 class="hd-card-title" style="margin-bottom:10px;"><i class="fas fa-wrench"></i> Historial de mantenimiento (<?= count($mants) ?>)</h5>
            <?php if (!$mants): ?><div style="color:var(--muted);font-size:.8rem;">Todavía no hay mantenimientos registrados para este equipo.</div><?php endif; ?>
            <?php foreach ($mants as $m): $c = $col_tipo[$m['tipo']] ?? '#64748b'; ?>
            <div style="border-left:3px solid <?= $c ?>;padding:6px 0 6px 12px;margin-bottom:12px;">
                <div style="font-size:.78rem;"><strong style="color:<?= $c ?>;"><?= $tipos[$m['tipo']] ?? $m['tipo'] ?></strong>
                    · <?= date('d/m/Y', strtotime($m['fecha'])) ?> · <span style="color:var(--muted);"><?= htmlspecialchars($m['autor'] ?? '—') ?></span></div>
                <div style="font-size:.82rem;margin-top:2px;"><?= nl2br(htmlspecialchars($m['descripcion'])) ?></div>
                <?php if ($m['proximo']): ?><div style="font-size:.7rem;color:var(--muted);margin-top:2px;">Próximo: <?= date('d/m/Y', strtotime($m['proximo'])) ?></div><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div>
            <?php if ($puede_registrar && !$falta_migracion): ?>
            <div class="hd-card" style="padding:18px 22px;margin-bottom:18px;">
                <h5 class="hd-card-title" style="margin-bottom:10px;"><i class="fas fa-plus-circle"></i> Registrar mantenimiento</h5>
                <form id="form-mant">
                    <input type="hidden" name="id_inventario" value="<?= $id_eq ?>">
                    <label class="hd-label">Tipo</label>
                    <select name="tipo" class="hd-input" required><?php foreach ($tipos as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select>
                    <label class="hd-label" style="margin-top:10px;">Fecha</label>
                    <input type="date" name="fecha" class="hd-input" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                    <label class="hd-label" style="margin-top:10px;">¿Qué se hizo?</label>
                    <textarea name="descripcion" class="hd-input" minlength="5" maxlength="1000" required placeholder="Ej. Limpieza interna, cambio de pasta térmica y actualización de BIOS"></textarea>
                    <label class="hd-label" style="margin-top:10px;">Próximo mantenimiento <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                    <input type="date" name="proximo" class="hd-input">
                    <div id="res-mant" style="margin-top:10px;"></div>
                    <button type="submit" class="hd-btn hd-btn-naranja hd-btn-sm" style="margin-top:8px;"><i class="fas fa-save"></i> Guardar</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="hd-card" style="padding:18px 22px;">
                <h5 class="hd-card-title" style="margin-bottom:10px;"><i class="fas fa-ticket-alt"></i> Tickets de este equipo (<?= count($tickets_eq) ?>)</h5>
                <?php if (!$tickets_eq): ?><div style="color:var(--muted);font-size:.8rem;">Ningún ticket está vinculado a este equipo.</div><?php endif; ?>
                <?php foreach ($tickets_eq as $t): $ver = ($rol_id === ROL_ADMIN) || ((int)$t['id_usuario_asignado'] === (int)$yo_id); ?>
                <div style="padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:.78rem;">
                    <?php if ($ver): ?><a href="ver_ticket.php?id=<?= (int)$t['id_tickets'] ?>" style="font-weight:600;color:var(--azul);"><?= htmlspecialchars($t['codigo_ticket']) ?></a>
                    <?php else: ?><strong><?= htmlspecialchars($t['codigo_ticket']) ?></strong><?php endif; ?>
                    · <?= htmlspecialchars($t['estado']) ?>
                    <div style="color:var(--muted);"><?= htmlspecialchars($t['asunto']) ?> · <?= date('d/m/Y', strtotime($t['fecha_creacion'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>
<script>
$('#form-mant').on('submit', function (e) {
    e.preventDefault();
    $.post('../procesos/create_mantenimiento.php', $(this).serialize())
        .done(function (r) { $('#res-mant').html(r); if (r.includes('alert-success')) setTimeout(() => location.reload(), 900); })
        .fail(function (x) { $('#res-mant').html(x.responseText || '<div class="alert alert-danger">Error de conexión.</div>'); });
});
</script>
