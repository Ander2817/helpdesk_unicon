<?php
// admin/gestion_estados.php — Gestión de estados de usuarios, equipos y departamentos (solo admin). Sin tablas nuevas.
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) { header("Location: ../index.php"); exit(); }
require_once '../includes/constants.php';
require_once '../includes/functions.php';
require_once '../includes/estados.php';
include '../config/conexion.php';
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$pagina_actual = 'gestion_estados.php';
$cat = est_catalogo();
$tipo = $_GET['tipo'] ?? 'usuarios'; if (!isset($cat[$tipo])) $tipo = 'usuarios';
$filtro = $_GET['estado'] ?? ''; $buscar = trim($_GET['q'] ?? '');
$info = $cat[$tipo]; $valores = est_valores($conexion, $info['tabla']);
$bloquean = estados_bloquean_login($conexion);

// conteo por estado
$conteo = [];
foreach ($conexion->query("SELECT estado, COUNT(*) n FROM `{$info['tabla']}` GROUP BY estado")->fetch_all(MYSQLI_ASSOC) as $r) $conteo[$r['estado']] = (int)$r['n'];

// listado
$sel = [
  'usuarios' => "SELECT id_usuario AS id, CONCAT(nombres,' ',apellidos) AS titulo, CONCAT(usuario_login,' · ',correo) AS detalle, estado FROM usuarios",
  'equipos' => "SELECT id_inventario AS id, CONCAT(codigo_equipo,' — ',tipo_equipo) AS titulo, CONCAT(marca,' ',modelo,IF(ubicacion IS NULL OR ubicacion='','',CONCAT(' · ',ubicacion))) AS detalle, estado FROM inventario_tecnologico",
  'departamentos' => "SELECT id_departamento AS id, nombre AS titulo, COALESCE(descripcion,'') AS detalle, estado FROM departamentos",
][$tipo];
$w = []; $t = ''; $p = [];
if ($filtro !== '' && in_array($filtro, $valores, true)) { $w[] = "estado = ?"; $t .= 's'; $p[] = $filtro; }
if ($buscar !== '') {
    $campo = ['usuarios' => "CONCAT(nombres,' ',apellidos,' ',usuario_login,' ',correo)", 'equipos' => "CONCAT(codigo_equipo,' ',tipo_equipo,' ',marca,' ',modelo,' ',serial)", 'departamentos' => "nombre"][$tipo];
    $w[] = "$campo LIKE ?"; $t .= 's'; $p[] = '%' . $buscar . '%';
}
$sql = "SELECT * FROM ($sel) x" . ($w ? " WHERE " . implode(' AND ', $w) : '') . " ORDER BY titulo LIMIT 300";
$st = $conexion->prepare($sql); if ($p) $st->bind_param($t, ...$p); $st->execute();
$filas = $st->get_result()->fetch_all(MYSQLI_ASSOC); $st->close();
$u_valores = est_valores($conexion, 'usuarios');
include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>
<main class="hd-main">
  <div class="hd-page-header">
    <div><h1 class="hd-page-title"><i class="fa-solid fa-toggle-on me-2"></i>Estados</h1>
    <p class="hd-page-sub">Cambia el estado de usuarios, equipos y departamentos. Todo cambio queda en la bitácora. Nada se borra: se desactiva.</p></div>
  </div>

  <div class="d-flex gap-2 flex-wrap mb-3">
    <?php foreach ($cat as $k => $c): ?>
      <a class="hd-btn <?= $k === $tipo ? 'hd-btn-primary' : 'hd-btn-outline' ?>" href="?tipo=<?= $k ?>"><i class="fa-solid <?= $c['icono'] ?> me-1"></i><?= $h($c['titulo']) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="hd-card mb-3"><div class="hd-card-body d-flex gap-2 flex-wrap align-items-center">
    <?php foreach ($valores as $v): ?>
      <a href="?tipo=<?= $tipo ?>&estado=<?= urlencode($v) ?>" class="text-decoration-none" style="border:1px solid <?= est_color($v) ?>;color:<?= est_color($v) ?>;border-radius:99px;padding:.2rem .7rem;font-size:.85rem;<?= $filtro === $v ? 'background:' . est_color($v) . '22;font-weight:600' : '' ?>">
        <?= $h(est_etiqueta($v)) ?> · <?= $conteo[$v] ?? 0 ?><?= ($tipo === 'usuarios' && in_array($v, $bloquean, true)) ? ' <i class="fa-solid fa-lock" title="Bloquea el inicio de sesión"></i>' : '' ?></a>
    <?php endforeach; ?>
    <?php if ($filtro !== '' || $buscar !== ''): ?><a class="ms-auto small" href="?tipo=<?= $tipo ?>">Quitar filtros</a><?php endif; ?>
  </div></div>

  <div class="hd-card mb-3">
    <div class="hd-card-header"><span class="hd-card-title"><?= $h($info['titulo']) ?> (<?= count($filas) ?>)</span>
      <form method="get" class="d-flex gap-2"><input type="hidden" name="tipo" value="<?= $tipo ?>"><?php if ($filtro !== ''): ?><input type="hidden" name="estado" value="<?= $h($filtro) ?>"><?php endif; ?>
        <input class="hd-input" name="q" value="<?= $h($buscar) ?>" placeholder="Buscar…"><button class="hd-btn hd-btn-outline">Buscar</button></form></div>
    <div class="hd-card-body" style="overflow:auto">
      <table class="hd-table"><thead><tr><th>Nombre</th><th>Detalle</th><th style="width:230px">Estado</th></tr></thead><tbody>
      <?php foreach ($filas as $f): ?>
        <tr><td><?= $h($f['titulo']) ?></td><td class="text-muted small"><?= $h($f['detalle']) ?></td>
          <td><select class="hd-input est-sel" data-id="<?= (int)$f['id'] ?>" data-prev="<?= $h($f['estado']) ?>" style="border-left:5px solid <?= est_color($f['estado']) ?>">
            <?php foreach ($valores as $v): ?><option value="<?= $h($v) ?>" <?= $v === $f['estado'] ? 'selected' : '' ?>><?= $h(est_etiqueta($v)) ?></option><?php endforeach; ?>
          </select></td></tr>
      <?php endforeach; if (!$filas): ?><tr><td colspan="3" class="text-center text-muted py-4">Sin resultados.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6"><div class="hd-card h-100">
      <div class="hd-card-header"><span class="hd-card-title">Agregar un estado nuevo a <?= $h($info['titulo']) ?></span></div>
      <div class="hd-card-body">
        <p class="small text-muted">Se agrega a la lista existente sin tocar los datos. Los estados actuales no se pueden quitar desde aquí (evita dejar registros huérfanos).</p>
        <div class="d-flex gap-2"><input id="nuevo-estado" class="hd-input" maxlength="20" placeholder="ej. licencia_medica"><button id="btn-nuevo" class="hd-btn hd-btn-primary">Agregar</button></div>
      </div></div></div>
    <div class="col-lg-6"><div class="hd-card h-100">
      <div class="hd-card-header"><span class="hd-card-title">Estados de usuario que bloquean el inicio de sesión</span></div>
      <div class="hd-card-body">
        <?php foreach ($u_valores as $v): $oblig = in_array($v, ['inactivo', 'suspendido'], true); ?>
          <label class="d-block"><input type="checkbox" class="chk-bloq" value="<?= $h($v) ?>" <?= in_array($v, $bloquean, true) ? 'checked' : '' ?> <?= $oblig ? 'disabled' : '' ?>> <?= $h(est_etiqueta($v)) ?><?= $oblig ? ' <small class="text-muted">(siempre)</small>' : '' ?></label>
        <?php endforeach; ?>
        <button id="btn-bloq" class="hd-btn hd-btn-outline mt-2">Guardar</button>
      </div></div></div>
  </div>
</main>
<script>
const TIPO = <?= json_encode($tipo) ?>;
function post(url, data) { return fetch(url, {method: 'POST', credentials: 'same-origin', body: new URLSearchParams(data)}).then(r => r.json()); }
document.querySelectorAll('.est-sel').forEach(s => s.onchange = function () {
    const el = this;
    post('procesos/estados_cambiar.php', {tipo: TIPO, id: el.dataset.id, estado: el.value}).then(j => {
        if (j.ok) { el.dataset.prev = el.value; if (j.color) el.style.borderLeftColor = j.color; }
        else el.value = el.dataset.prev;
        (window.Swal ? Swal.fire({icon: j.ok ? 'success' : 'error', text: j.msg, timer: j.ok ? 2500 : undefined, showConfirmButton: !j.ok}) : alert(j.msg));
    }).catch(() => { el.value = el.dataset.prev; alert('Error de conexión.'); });
});
document.getElementById('btn-nuevo').onclick = () => {
    const v = document.getElementById('nuevo-estado').value;
    post('procesos/estados_catalogo.php', {accion: 'agregar', tipo: TIPO, valor: v}).then(j => { alert(j.msg); if (j.ok) location.reload(); });
};
document.getElementById('btn-bloq').onclick = () => {
    const d = new URLSearchParams({accion: 'bloqueo_login'});
    document.querySelectorAll('.chk-bloq:checked').forEach(c => d.append('bloquean[]', c.value));
    fetch('procesos/estados_catalogo.php', {method: 'POST', credentials: 'same-origin', body: d}).then(r => r.json()).then(j => alert(j.msg));
};
</script>
<?php include_once '../includes/footer2.php'; ?>
