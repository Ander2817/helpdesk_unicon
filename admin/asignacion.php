<?php
// admin/asignacion.php — Asignación de tickets: interruptor y reglas de la asignación AUTOMÁTICA, carga de los técnicos,
// cola de tickets sin asignar con asignación MANUAL (o automática ticket por ticket) y últimas asignaciones. Solo admin.
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) { header("Location: ../index.php"); exit(); }
require_once '../includes/constants.php';
require_once '../includes/functions.php';
include '../config/conexion.php';
require_once '../includes/asignacion.php';
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$pagina_actual = 'asignacion.php';
$cfg = asig_config($conexion);
$tecs = asig_tecnicos($conexion);
$disp = array_values(array_filter($tecs, fn($t) => $t['disponible']));
$cola = $conexion->query("SELECT t.id_tickets, t.codigo_ticket, t.asunto, t.fecha_creacion, p.nombre AS prioridad, p.nivel, COALESCE(d.nombre,'') AS depto,
                                 CONCAT(u.nombres,' ',u.apellidos) AS solicitante
                          FROM tickets t JOIN prioridades p ON p.id_prioridad = t.id_prioridad
                          LEFT JOIN departamentos d ON d.id_departamento = t.id_departamento
                          JOIN usuarios u ON u.id_usuario = t.id_usuario_solicitante
                          WHERE t.id_usuario_asignado IS NULL AND t.id_estado = " . TICKET_ABIERTO . "
                          ORDER BY p.nivel DESC, t.fecha_creacion ASC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
$ult = $conexion->query("SELECT h.fecha_cambio, h.valor_nuevo, t.codigo_ticket, t.id_tickets FROM historial_ticket h JOIN tickets t ON t.id_tickets = h.id_ticket
                         WHERE h.campo_modificado = 'Técnico asignado' ORDER BY h.id_historial DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
function asig_motivo(array $t): string {
    return match ($t['estado']) { 'activo' => '', 'vacaciones' => 'de vacaciones', 'reposo' => 'en reposo', 'suspendido' => 'suspendido', 'inactivo' => 'inactivo', default => 'estado: ' . $t['estado'] };
}
include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<main class="hd-main">
  <div class="hd-page-header">
    <div><div class="hd-page-title"><i class="fas fa-user-check" style="color:#E87722;margin-right:6px"></i>Asignación de tickets</div>
    <div class="hd-page-sub">Los tickets nuevos se asignan solos; tú puedes asignar o reasignar manualmente cuando quieras.</div></div>
    <span class="hd-tag hd-tag-<?= $cfg['activa'] ? 'verde' : 'gris' ?>" id="tag-estado"><?= $cfg['activa'] ? 'Automática ACTIVA' : 'Automática apagada' ?></span>
  </div>

  <div class="hd-grid-6-6">
    <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-sliders"></i> Reglas de la asignación automática</h5></div>
      <div class="hd-card-body">
        <label class="d-flex align-items-center gap-2 mb-3" style="cursor:pointer"><input type="checkbox" id="cfg-activa" <?= $cfg['activa'] ? 'checked' : '' ?> style="width:18px;height:18px">
          <span><b>Asignar automáticamente</b> al crear un ticket</span></label>
        <div class="mb-3"><label class="d-block small text-muted mb-1">Criterio</label>
          <select id="cfg-modo" class="hd-input" style="width:100%">
            <option value="departamento" <?= $cfg['modo'] === 'departamento' ? 'selected' : '' ?>>Mismo departamento primero, luego menor carga</option>
            <option value="carga" <?= $cfg['modo'] === 'carga' ? 'selected' : '' ?>>Solo menor carga (ignora el departamento)</option>
          </select></div>
        <div class="mb-3"><label class="d-block small text-muted mb-1">Máximo de tickets abiertos por técnico (0 = sin límite)</label>
          <input type="number" id="cfg-max" min="0" max="999" value="<?= (int)$cfg['max'] ?>" class="hd-input" style="width:120px"></div>
        <button class="hd-btn hd-btn-naranja" id="btn-guardar"><i class="fas fa-save"></i> Guardar</button>
        <ul class="small text-muted mt-3 mb-0" style="padding-left:18px">
          <li>Solo reciben tickets los técnicos en estado <b>activo</b> (no vacaciones, reposo, suspendido ni inactivo).</li>
          <li>Entre los candidatos se elige al de <b>menos tickets abiertos</b>; si hay empate, al que lleva más tiempo sin recibir uno.</li>
          <li>Si nadie está disponible, el ticket queda sin asignar para ti.</li>
        </ul>
      </div></div>

    <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-people-group"></i> Técnicos y carga actual</h5></div>
      <div style="overflow-x:auto"><table class="hd-table"><thead><tr><th>Técnico</th><th>Departamento</th><th>Abiertos</th><th>Recibe tickets</th></tr></thead><tbody>
        <?php foreach ($tecs as $t): ?>
          <tr><td><?= $h($t['nombres'] . ' ' . $t['apellidos']) ?></td><td><?= $h($t['departamento']) ?></td><td><b><?= $t['abiertos'] ?></b></td>
            <td><?php if ($t['disponible'] && ($cfg['max'] === 0 || $t['abiertos'] < $cfg['max'])): ?><span class="hd-tag hd-tag-verde">Sí</span>
                <?php elseif ($t['disponible']): ?><span class="hd-tag hd-tag-amarillo">En el tope</span>
                <?php else: ?><span class="hd-tag hd-tag-gris">No · <?= $h(asig_motivo($t)) ?></span><?php endif; ?></td></tr>
        <?php endforeach; if (!$tecs): ?><tr><td colspan="4" class="text-center text-muted py-3">No hay técnicos registrados.</td></tr><?php endif; ?>
      </tbody></table></div></div>
  </div>

  <div class="hd-card" style="margin-top:12px">
    <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-inbox"></i> Tickets sin asignar (<?= count($cola) ?>)</h5>
      <?php if ($cola): ?><button class="hd-btn hd-btn-outline hd-btn-sm" id="btn-todos"><i class="fas fa-wand-magic-sparkles"></i> Asignar todos automáticamente</button><?php endif; ?></div>
    <div style="overflow-x:auto"><table class="hd-table"><thead><tr><th>Ticket</th><th>Asunto</th><th>Prioridad</th><th>Solicitante</th><th>Espera</th><th>Asignar</th></tr></thead><tbody>
      <?php foreach ($cola as $t): $min = max(0, (int)((time() - strtotime($t['fecha_creacion'])) / 60)); ?>
        <tr data-id="<?= (int)$t['id_tickets'] ?>">
          <td><a href="ver_ticket.php?id=<?= (int)$t['id_tickets'] ?>"><code><?= $h($t['codigo_ticket']) ?></code></a></td>
          <td><?= $h(mb_strimwidth($t['asunto'], 0, 60, '…')) ?></td>
          <td><span class="hd-tag hd-tag-<?= (int)$t['nivel'] >= 3 ? 'rojo' : ((int)$t['nivel'] === 2 ? 'amarillo' : 'azul') ?>"><?= $h($t['prioridad']) ?></span></td>
          <td><?= $h($t['solicitante']) ?><div class="small text-muted"><?= $h($t['depto']) ?></div></td>
          <td style="white-space:nowrap"><?= $min < 60 ? $min . ' min' : ($min < 1440 ? floor($min / 60) . ' h' : floor($min / 1440) . ' d') ?></td>
          <td><div style="display:flex;gap:4px;flex-wrap:wrap">
            <select class="hd-input sel-tec" style="min-width:150px"><option value="">Técnico…</option>
              <?php foreach ($disp as $d): ?><option value="<?= (int)$d['id_usuario'] ?>"><?= $h($d['nombres'] . ' ' . $d['apellidos']) ?> (<?= $d['abiertos'] ?>)</option><?php endforeach; ?></select>
            <button class="hd-btn hd-btn-naranja hd-btn-sm btn-manual" title="Asignar al técnico elegido"><i class="fas fa-user-check"></i></button>
            <button class="hd-btn hd-btn-outline hd-btn-sm btn-auto" title="Asignar automáticamente"><i class="fas fa-wand-magic-sparkles"></i></button></div></td></tr>
      <?php endforeach; if (!$cola): ?><tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-check-circle" style="color:#16a34a"></i> No hay tickets esperando técnico.</td></tr><?php endif; ?>
    </tbody></table></div></div>

  <div class="hd-card" style="margin-top:12px"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-clock-rotate-left"></i> Últimas asignaciones</h5></div>
    <div style="overflow-x:auto"><table class="hd-table"><thead><tr><th>Fecha</th><th>Ticket</th><th>Técnico</th></tr></thead><tbody>
      <?php foreach ($ult as $u): ?><tr><td style="white-space:nowrap"><?= $h(date('d/m/Y H:i', strtotime($u['fecha_cambio']))) ?></td>
        <td><a href="ver_ticket.php?id=<?= (int)$u['id_tickets'] ?>"><code><?= $h($u['codigo_ticket']) ?></code></a></td><td><?= $h($u['valor_nuevo']) ?></td></tr>
      <?php endforeach; if (!$ult) echo '<tr><td colspan="3" class="text-center text-muted py-3">Aún no hay asignaciones.</td></tr>'; ?>
    </tbody></table></div></div>
</main>
<script>
const post = (url, d) => fetch(url, {method: 'POST', credentials: 'same-origin', body: new URLSearchParams(d)}).then(r => r.text()).then(t => { try { return JSON.parse(t); } catch (e) { return {ok: t.includes('alert-success'), msg: t.replace(/<[^>]*>/g, '').trim()}; } });
const aviso = (j) => window.Swal ? Swal.fire({icon: j.ok ? 'success' : 'warning', text: j.msg, timer: j.ok ? 1800 : undefined, showConfirmButton: !j.ok}) : alert(j.msg);
document.getElementById('btn-guardar').onclick = () => post('procesos/asignacion.php', {accion: 'config', activa: document.getElementById('cfg-activa').checked ? '1' : '0',
    modo: document.getElementById('cfg-modo').value, max: document.getElementById('cfg-max').value}).then(j => { aviso(j); if (j.ok) setTimeout(() => location.reload(), 1200); });
const bt = document.getElementById('btn-todos');
if (bt) bt.onclick = () => post('procesos/asignacion.php', {accion: 'auto_pendientes'}).then(j => { aviso(j); setTimeout(() => location.reload(), 1200); });
document.querySelectorAll('.btn-auto').forEach(b => b.onclick = () => post('procesos/asignacion.php', {accion: 'auto_uno', id_ticket: b.closest('tr').dataset.id}).then(j => { aviso(j); if (j.ok) setTimeout(() => location.reload(), 1200); }));
document.querySelectorAll('.btn-manual').forEach(b => b.onclick = () => {
    const tr = b.closest('tr'), v = tr.querySelector('.sel-tec').value;
    if (!v) return aviso({ok: false, msg: 'Elige primero un técnico.'});
    post('../procesos/asignar_ticket.php', {id_ticket: tr.dataset.id, id_usuario_asignado: v}).then(j => { aviso(j); if (j.ok) setTimeout(() => location.reload(), 1200); });
});
</script>
<?php include_once '../includes/footer2.php'; ?>
